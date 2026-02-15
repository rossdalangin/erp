(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const BOMNode = ({ item, index, depth, onRemove, onMarkSubstitute, onUpdate, onReorder }) => {
    const isOp = item.type === 'operation';
    const icon = item.type === 'material' ? '📦 ' : (isOp ? '⚡ ' : '⚙️ ');

    return wp.element.createElement('div', {
        className: 'mep-bom-node',
        draggable: true,
        onDragStart: (e) => {
            e.stopPropagation();
            e.dataTransfer.setData('reorderIndex', index);
        },
        onDragOver: (e) => {
            e.preventDefault();
            e.stopPropagation();
        },
        onDrop: (e) => {
            e.stopPropagation();
            const fromIndex = e.dataTransfer.getData('reorderIndex');
            if (fromIndex !== "") {
                onReorder(parseInt(fromIndex), index);
            }
        },
        style: { marginLeft: `${depth * 20}px` }
    },
        wp.element.createElement('span', null, icon),
        wp.element.createElement('div', { style: { flex: 1 } },
            wp.element.createElement('strong', null, item.name || `Item #${item.id}`),
            wp.element.createElement('div', { style: { display: 'flex', gap: '15px', marginTop: '5px' } },
                wp.element.createElement('span', {
                    style: { cursor: 'pointer', borderBottom: '1px dashed #64748b', fontSize: '12px' },
                    onClick: () => {
                        const newVal = prompt(`Enter ${isOp ? 'Time (mins)' : 'Quantity'}:`, item.qty);
                        if (newVal !== null) onUpdate(item.id, { qty: parseFloat(newVal) });
                    }
                }, ` ${isOp ? __('Time', 'manufacturing-erp-pro') : __('Qty', 'manufacturing-erp-pro')}: ${item.qty}`),
                wp.element.createElement('span', {
                    style: { cursor: 'pointer', borderBottom: '1px dashed #64748b', fontSize: '12px' },
                    onClick: () => {
                        const newVal = prompt(`${isOp ? __('Yield Loss %', 'manufacturing-erp-pro') : __('Scrap %', 'manufacturing-erp-pro')}:`, (item.scrap || 0) * 100);
                        if (newVal !== null) onUpdate(item.id, { scrap: parseFloat(newVal) / 100 });
                    }
                }, ` ${isOp ? __('Loss', 'manufacturing-erp-pro') : __('Scrap', 'manufacturing-erp-pro')}: ${(item.scrap || 0) * 100}%`)
            )
        ),
        wp.element.createElement('div', null,
            wp.element.createElement('button', {
                className: 'button button-link-delete',
                style: { fontSize: '11px' },
                onClick: () => onRemove(item.id)
            }, __('Remove', 'manufacturing-erp-pro'))
        )
    );
};

const BOMBuilder = ({ productId }) => {
    const [bom, setBom] = useState({ bom: [], total_cost: 0, version: 1 });
    const [materials, setMaterials] = useState([]);
    const [equipment, setEquipment] = useState([]);
    const [newVersion, setNewVersion] = useState(false);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        Promise.all([
            wp.apiFetch({ path: `/mep/v1/bom/${productId}` }),
            wp.apiFetch({ path: '/mep/v1/materials' }),
            wp.apiFetch({ path: '/mep/v1/equipment' })
        ]).then(([bomData, matData, eqData]) => {
            setBom(Array.isArray(bomData) ? { bom: [], total_cost: 0, version: 1 } : bomData);
            setMaterials(matData || []);
            setEquipment(eqData || []);
            setLoading(false);
        }).catch((err) => {
            setError(__('Failed to load BOM editor data.', 'manufacturing-erp-pro'));
            setLoading(false);
        });
    }, [productId]);

    const onDragStart = (e, item, type) => {
        e.dataTransfer.setData('mepItem', JSON.stringify({ ...item, mepType: type }));
    };

    const addMaterial = (material) => {
        const newComponent = { id: material.id, name: material.name, type: 'material', qty: 1, scrap: 0 };
        setBom(prev => ({ ...prev, bom: [...(prev.bom || []), newComponent] }));
    };

    const addOperation = (eq) => {
        const newOp = { id: eq.id, name: `Op: ${eq.name}`, type: 'operation', qty: 30, scrap: 0 };
        setBom(prev => ({ ...prev, bom: [...(prev.bom || []), newOp] }));
    };

    const onDrop = (e) => {
        const itemStr = e.dataTransfer.getData('mepItem');
        if (!itemStr) return;
        const data = JSON.parse(itemStr);
        if (data.mepType === 'material') addMaterial(data);
        else if (data.mepType === 'operation') addOperation(data);
    };

    const saveBom = () => {
        wp.apiFetch({
            path: `/mep/v1/bom/${productId}`,
            method: 'POST',
            data: { components: bom.bom, create_new_version: newVersion }
        }).then(() => {
            alert(__('BOM Saved!', 'manufacturing-erp-pro'));
            setNewVersion(false);
            wp.apiFetch({ path: `/mep/v1/bom/${productId}` }).then(res => setBom(Array.isArray(res) ? { bom: [], total_cost: 0, version: 1 } : res));
        });
    };

    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));
    if (loading) return wp.element.createElement('p', null, __('Loading visual editor...', 'manufacturing-erp-pro'));

    return wp.element.createElement('div', { className: 'mep-bom-layout mep-admin-style mep-animate-fade-in' },
        wp.element.createElement('div', { className: 'mep-bom-sidebar' },
            wp.element.createElement('h3', null, __('Materials', 'manufacturing-erp-pro')),
            (materials || []).map(mat => wp.element.createElement('div', {
                key: mat.id, className: 'mep-library-item', draggable: true,
                onDragStart: (e) => onDragStart(e, mat, 'material'),
            }, wp.element.createElement('span', null, '📦 '), mat.name)),
            wp.element.createElement('h3', { style: { marginTop: '30px' } }, __('Operations', 'manufacturing-erp-pro')),
            (equipment || []).map(eq => wp.element.createElement('div', {
                key: eq.id, className: 'mep-library-item', draggable: true,
                onDragStart: (e) => onDragStart(e, eq, 'operation'),
            }, wp.element.createElement('span', null, '⚡ '), eq.name))
        ),
        wp.element.createElement('div', { className: 'mep-bom-main' },
            wp.element.createElement('header', { style: { padding: '20px', borderBottom: '1px solid #e2e8f0', display: 'flex', justifyContent: 'space-between', alignItems: 'center' } },
                wp.element.createElement('h2', { style: { margin: 0 } }, `${__('BOM Editor: Product', 'manufacturing-erp-pro')} #${productId}`),
                wp.element.createElement('div', { className: 'mep-cost-roll-up' },
                    wp.element.createElement('strong', null, __('Roll-up Cost: ', 'manufacturing-erp-pro')),
                    wp.element.createElement('span', { style: { color: 'var(--mep-primary)', fontSize: '1.2rem', fontWeight: '700' } }, `$${bom.total_cost || '0.00'}`)
                )
            ),
            wp.element.createElement('div', {
                className: 'mep-bom-canvas',
                onDragOver: (e) => e.preventDefault(),
                onDrop: onDrop,
            },
                ((bom && bom.bom) || []).length > 0 ?
                    bom.bom.map((item, index) => wp.element.createElement(BOMNode, {
                        key: index, item, index, depth: 0,
                        onRemove: (id) => setBom(prev => ({ ...prev, bom: prev.bom.filter(i => i.id !== id) })),
                        onUpdate: (id, up) => setBom(prev => ({ ...prev, bom: prev.bom.map(i => i.id === id ? { ...i, ...up } : i) })),
                        onReorder: (f, t) => {
                            const nb = [...bom.bom];
                            const [m] = nb.splice(f, 1);
                            nb.splice(t, 0, m);
                            setBom({ ...bom, bom: nb });
                        }
                    })) :
                    wp.element.createElement('p', { style: { textAlign: 'center', marginTop: '100px', color: '#94a3b8' } }, __('Drag elements here to build product structure.', 'manufacturing-erp-pro'))
            ),
            wp.element.createElement('footer', { style: { padding: '20px', background: '#f8fafc', borderTop: '1px solid #e2e8f0', display: 'flex', gap: '20px', alignItems: 'center' } },
                wp.element.createElement('button', { className: 'button button-primary', onClick: saveBom }, __('Save BOM', 'manufacturing-erp-pro')),
                wp.element.createElement('label', null,
                    wp.element.createElement('input', { type: 'checkbox', checked: newVersion, onChange: (e) => setNewVersion(e.target.checked) }),
                    ` ${__('Save New Version', 'manufacturing-erp-pro')}`
                ),
                wp.element.createElement('button', {
                    className: 'button',
                    style: { marginLeft: 'auto' },
                    onClick: () => {
                        const q = prompt(__('Produce qty:', 'manufacturing-erp-pro'), '100');
                        if (q) wp.apiFetch({ path: '/mep/v1/production/release', method: 'POST', data: { product_id: productId, qty: parseFloat(q), due_date: new Date().toISOString().split('T')[0] } }).then(res => alert(__('Work Order Released!', 'manufacturing-erp-pro')));
                    }
                }, `🚀 ${__('Release Work Order', 'manufacturing-erp-pro')}`)
            )
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-bom-builder-root');
    if (container) {
        const productId = container.dataset.productId;
        if (wp.element.createRoot) { wp.element.createRoot({ productId: productId }), container).render(wp.element.createElement(BOMBuilder); } else { wp.element.render(wp.element.createElement(BOMBuilder, { productId: productId }), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
