(function() {
/**
 * MEP BOM Builder - Interactive React Component with Real DnD
 */

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
        style: { marginLeft: `${depth * 20}px`, borderLeft: '2px solid #ccc', padding: '10px', marginBottom: '5px', background: isOp ? '#f0f8ff' : '#f9f9f9', cursor: 'move', display: 'flex', alignItems: 'center', gap: '10px' }
    },
        wp.element.createElement('span', { className: 'mep-node-type' }, icon),
        wp.element.createElement('strong', null, item.name || `Item #${item.id}`),

        wp.element.createElement('span', {
            style: { cursor: 'pointer', marginLeft: '10px', borderBottom: '1px dashed #999' },
            onClick: () => {
                const newVal = prompt(`Enter ${isOp ? 'Time (mins)' : 'Quantity'}:`, item.qty);
                if (newVal !== null) onUpdate(item.id, { qty: parseFloat(newVal) });
            }
        }, ` ${isOp ? __('Time', 'manufacturing-erp-pro') : __('Qty', 'manufacturing-erp-pro')}: ${item.qty}`),

        wp.element.createElement('span', {
            style: { cursor: 'pointer', marginLeft: '10px', borderBottom: '1px dashed #999', fontSize: '11px' },
            onClick: () => {
                const newVal = prompt(`${isOp ? __('Enter Yield Loss %', 'manufacturing-erp-pro') : __('Enter Scrap %', 'manufacturing-erp-pro')}:`, (item.scrap || 0) * 100);
                if (newVal !== null) onUpdate(item.id, { scrap: parseFloat(newVal) / 100 });
            }
        }, ` ${isOp ? __('Loss', 'manufacturing-erp-pro') : __('Scrap', 'manufacturing-erp-pro')}: ${(item.scrap || 0) * 100}%`),

        wp.element.createElement('button', {
            className: 'button-link-delete',
            style: { marginLeft: '10px', fontSize: '11px' },
            onClick: () => onRemove(item.id)
        }, __('Remove', 'manufacturing-erp-pro')),

        item.sub_bom && item.sub_bom.length > 0 &&
            item.sub_bom.map((child, i) => wp.element.createElement(BOMNode, { key: i, item: child, index: i, depth: depth + 1, onRemove, onMarkSubstitute, onUpdate, onReorder }))
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
            setError(__('Failed to load BOM or Material data.', 'manufacturing-erp-pro'));
            setLoading(false);
        });
    }, [productId]);

    const onDragStart = (e, item, type) => {
        e.dataTransfer.setData('mepItem', JSON.stringify({ ...item, mepType: type }));
    };

    const onDrop = (e) => {
        const itemStr = e.dataTransfer.getData('mepItem');
        if (!itemStr) return;
        const data = JSON.parse(itemStr);
        if (data.mepType === 'material') {
            const newItem = { id: data.id, name: data.name, type: 'material', qty: 1, scrap: 0 };
            setBom(prev => ({ ...prev, bom: [...(prev.bom || []), newItem] }));
        } else if (data.mepType === 'operation') {
            const newOp = { id: data.id, name: `Op: ${data.name}`, type: 'operation', qty: 30, scrap: 0 };
            setBom(prev => ({ ...prev, bom: [...(prev.bom || []), newOp] }));
        }
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

    if (loading) return wp.element.createElement('p', null, __('Loading Interactive BOM Builder...', 'manufacturing-erp-pro'));
    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));

    return wp.element.createElement('div', { className: 'mep-bom-editor-layout', style: { display: 'flex', gap: '20px', background: '#fff', padding: '20px', border: '1px solid #ccc' } },
        wp.element.createElement('div', {
            className: 'mep-material-library',
            style: { width: '250px', border: '1px solid #eee', padding: '15px', background: '#f9f9f9' }
        },
            wp.element.createElement('h3', null, __('Materials', 'manufacturing-erp-pro')),
            (materials || []).map(mat => wp.element.createElement('div', {
                key: mat.id,
                draggable: true,
                onDragStart: (e) => onDragStart(e, mat, 'material'),
                style: { padding: '8px', border: '1px solid #ddd', marginBottom: '5px', cursor: 'grab', background: '#fff' }
            }, mat.name)),

            wp.element.createElement('h3', { style: { marginTop: '20px' } }, __('Operations', 'manufacturing-erp-pro')),
            (equipment || []).map(eq => wp.element.createElement('div', {
                key: eq.id,
                draggable: true,
                onDragStart: (e) => onDragStart(e, eq, 'operation'),
                style: { padding: '8px', border: '1px solid #ddd', marginBottom: '5px', cursor: 'grab', background: '#fff' }
            }, eq.name))
        ),
        wp.element.createElement('div', { style: { flex: 1 } },
            wp.element.createElement('header', { style: { marginBottom: '20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' } },
                wp.element.createElement('h2', null, `${__('Visual BOM Editor: Product', 'manufacturing-erp-pro')} #${productId}`),
                wp.element.createElement('div', null,
                    wp.element.createElement('strong', null, __('Roll-up Cost: ', 'manufacturing-erp-pro')),
                    wp.element.createElement('span', { style: { color: '#2271b1', fontSize: '1.2em', fontWeight: 'bold' } }, `$${bom.total_cost || '0.00'}`)
                )
            ),
            wp.element.createElement('div', {
                className: 'mep-bom-canvas',
                onDragOver: (e) => e.preventDefault(),
                onDrop: onDrop,
                style: { minHeight: '400px', border: '2px dashed #ccc', padding: '20px', background: '#fcfcfc' }
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
                        },
                        onMarkSubstitute: () => {}
                    })) :
                    wp.element.createElement('p', { style: { textAlign: 'center', marginTop: '100px', color: '#999' } }, __('Drag materials or operations here to start building...', 'manufacturing-erp-pro'))
            ),
            wp.element.createElement('footer', { style: { marginTop: '20px', display: 'flex', gap: '20px', alignItems: 'center' } },
                wp.element.createElement('button', { className: 'button button-primary', onClick: saveBom }, __('Save BOM Structure', 'manufacturing-erp-pro')),
                wp.element.createElement('label', null,
                    wp.element.createElement('input', { type: 'checkbox', checked: newVersion, onChange: (e) => setNewVersion(e.target.checked) }),
                    ` ${__('Save as New Version', 'manufacturing-erp-pro')}`
                ),
                wp.element.createElement('button', {
                    className: 'button button-secondary',
                    style: { marginLeft: 'auto' },
                    onClick: () => {
                        const q = prompt(__('Qty:', 'manufacturing-erp-pro'), '100');
                        if (q) wp.apiFetch({ path: '/mep/v1/production/release', method: 'POST', data: { product_id: productId, qty: parseFloat(q), due_date: new Date().toISOString().split('T')[0] } }).then(() => alert(__('Released!', 'manufacturing-erp-pro')));
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
        const root = (wp.element.createRoot) ? wp.element.createRoot(container) : null;
        if (root) root.render(wp.element.createElement(BOMBuilder, { productId: productId }));
        else wp.element.render(wp.element.createElement(BOMBuilder, { productId: productId }), container);
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
