(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const Bin = ({ bin, onDragStart, onDragOver, onDrop, helpMode }) => {
    const occupancyClass = bin.occupancy > 90 ? 'occupancy-high' : (bin.occupancy > 70 ? 'occupancy-medium' : '');

    return wp.element.createElement('div', {
        className: `mep-bin-card ${occupancyClass}`,
        onDragOver: onDragOver,
        onDrop: (e) => onDrop(e, bin.id),
        title: helpMode ? `Bin (${bin.name}): Shows current material levels.` : '',
    },
        wp.element.createElement('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' } },
            wp.element.createElement('h4', { style: { margin: 0, fontSize: '14px' } }, bin.name),
            wp.element.createElement('span', {
                style: { fontSize: '10px', padding: '2px 5px', borderRadius: '3px', background: '#eee' }
            }, `${bin.occupancy}% Full`)
        ),
        wp.element.createElement('div', { className: 'mep-bin-contents' },
            (bin.items || []).length > 0 ?
                bin.items.map((item, i) => wp.element.createElement('div', {
                    key: i,
                    draggable: true,
                    onDragStart: (e) => onDragStart(e, bin.id, item.material_id, item.qty),
                    style: { fontSize: '11px', padding: '5px', background: '#f8fafc', border: '1px solid #e2e8f0', marginBottom: '4px', cursor: 'grab', borderRadius: '4px' }
                },
                    `Mat #${item.material_id}: ${item.qty} units`
                )) :
                wp.element.createElement('em', { style: { color: '#94a3b8', fontSize: '11px' } }, __('Empty', 'manufacturing-erp-pro'))
        )
    );
};

const WarehouseLayout = () => {
    const [warehouses, setWarehouses] = useState([]);
    const [selectedWh, setSelectedWh] = useState(null);
    const [bins, setBins] = useState([]);
    const [reorderBasket, setReorderBasket] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [isDraggingOverBasket, setIsDraggingOverBasket] = useState(false);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/warehouses' })
            .then(data => {
                setWarehouses(data);
                if (data && data.length > 0) {
                    setSelectedWh(data[0].id);
                } else {
                    setLoading(false);
                }
            })
            .catch(err => {
                setError(__('Failed to load Warehouses.', 'manufacturing-erp-pro'));
                setLoading(false);
            });
    }, []);

    useEffect(() => {
        if (selectedWh) {
            setLoading(true);
            wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` })
                .then(data => {
                    setBins(data || []);
                    setLoading(false);
                })
                .catch(err => {
                    setError(__('Failed to load Bins.', 'manufacturing-erp-pro'));
                    setLoading(false);
                });
        }
    }, [selectedWh]);

    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));
    if (loading && warehouses.length === 0) return wp.element.createElement('p', null, __('Initializing Warehouse View...', 'manufacturing-erp-pro'));

    const onDragStart = (e, sourceBinId, materialId, qty) => {
        e.dataTransfer.setData('transferData', JSON.stringify({ sourceBinId, materialId, qty }));
    };

    const onDragOver = (e) => e.preventDefault();

    const onDrop = (e, targetBinId) => {
        const dataStr = e.dataTransfer.getData('transferData');
        if (!dataStr) return;
        const data = JSON.parse(dataStr);
        if (data.sourceBinId === targetBinId) return;

        const transferQty = prompt(__('Transfer quantity:', 'manufacturing-erp-pro'), data.qty);
        if (transferQty && parseFloat(transferQty) > 0) {
            wp.apiFetch({
                path: '/mep/v1/inventory/transfer',
                method: 'POST',
                data: {
                    material_id: data.materialId,
                    source_bin_id: data.sourceBinId,
                    target_bin_id: targetBinId,
                    quantity: parseFloat(transferQty)
                }
            }).then(() => {
                wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` }).then(setBins);
            }).catch(err => {
                alert(__('Inventory transfer failed.', 'manufacturing-erp-pro'));
            });
        }
    };

    const onBasketDrop = (e) => {
        setIsDraggingOverBasket(false);
        const dataStr = e.dataTransfer.getData('transferData');
        if (!dataStr) return;
        const data = JSON.parse(dataStr);

        if (!reorderBasket.find(id => id === data.materialId)) {
            setReorderBasket([...reorderBasket, data.materialId]);
        }
    };

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    return wp.element.createElement('div', { className: 'mep-column-container mep-admin-style mep-animate-fade-in' },
        wp.element.createElement('div', { className: 'mep-column', style: { flex: 3 } },
            wp.element.createElement('h3', null, '📦 ' + __('Bins & Inventory', 'manufacturing-erp-pro')),
            wp.element.createElement('div', { style: { marginBottom: '20px', display: 'flex', gap: '10px', alignItems: 'center' } },
                wp.element.createElement('label', null, __('Warehouse:', 'manufacturing-erp-pro')),
                wp.element.createElement('select', {
                    value: selectedWh,
                    onChange: (e) => setSelectedWh(e.target.value),
                    style: { padding: '5px' }
                },
                    (warehouses || []).map(wh => wp.element.createElement('option', { key: wh.id, value: wh.id }, wh.name))
                ),
                wp.element.createElement('button', {
                    className: 'button',
                    onClick: () => window.location.href = wpApiSettings.root + 'mep/v1/reports/inventory-csv?_wpnonce=' + wpApiSettings.nonce
                }, __('Export CSV', 'manufacturing-erp-pro'))
            ),
            loading ? wp.element.createElement('p', null, __('Loading bins...', 'manufacturing-erp-pro')) :
            wp.element.createElement('div', { className: 'mep-bins-grid' },
                (bins || []).length > 0 ?
                    bins.map(bin => wp.element.createElement(Bin, {
                        key: bin.id, bin, onDragStart, onDragOver, onDrop, helpMode
                    })) :
                    wp.element.createElement('p', null, __('No bins found in this warehouse.', 'manufacturing-erp-pro'))
            )
        ),
        wp.element.createElement('div', {
            className: `mep-column ${isDraggingOverBasket ? 'is-dragging-over' : ''}`,
            onDragOver: (e) => { e.preventDefault(); setIsDraggingOverBasket(true); },
            onDragLeave: () => setIsDraggingOverBasket(false),
            onDrop: onBasketDrop,
            style: { flex: '0 0 300px' }
        },
            wp.element.createElement('h3', null, '🛒 ' + __('Reorder Basket', 'manufacturing-erp-pro')),
            wp.element.createElement('div', { style: { minHeight: '150px' } },
                reorderBasket.length > 0 ?
                    reorderBasket.map((id, i) => wp.element.createElement('div', { key: i, className: 'mep-library-item' }, `Mat #${id}`)) :
                    wp.element.createElement('p', { style: { color: '#94a3b8', fontSize: '12px' } }, __('Drag materials here to reorder...', 'manufacturing-erp-pro'))
            ),
            reorderBasket.length > 0 && wp.element.createElement('button', {
                className: 'button button-primary',
                style: { width: '100%', marginTop: '20px' },
                onClick: () => window.location.href = 'admin.php?page=mep-mrp-planning'
            }, __('Trigger Reorder Check', 'manufacturing-erp-pro'))
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-warehouse-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(null), container).render(wp.element.createElement(WarehouseLayout); } else { wp.element.render(wp.element.createElement(WarehouseLayout, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
