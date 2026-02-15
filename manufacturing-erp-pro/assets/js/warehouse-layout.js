(function() {
/**
 * MEP Warehouse Layout - Visual Inventory Management
 */

const { useState, useEffect } = wp.element;

const Bin = ({ bin, onTransfer, onDragStart, onDragOver, onDrop, helpMode }) => {
    const occupancyClass = bin.occupancy > 90 ? 'occupancy-high' : (bin.occupancy > 70 ? 'occupancy-medium' : '');

    return wp.element.createElement('div', {
        className: `mep-bin-card ${occupancyClass}`,
        onDragOver: onDragOver,
        onDrop: (e) => onDrop(e, bin.id),
        title: helpMode ? `Bin (${bin.name}): Shows current material levels.` : '',
    },
        wp.element.createElement('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' } },
            wp.element.createElement('h4', { style: { margin: 0 } }, bin.name),
            wp.element.createElement('span', {
                style: { fontSize: '10px', padding: '2px 5px', borderRadius: '3px', background: borderColor, color: bin.occupancy > 70 ? '#fff' : '#333' }
            }, `${bin.occupancy}% Full`)
        ),
        wp.element.createElement('div', { className: 'mep-bin-contents' },
            bin.items.length > 0 ?
                bin.items.map((item, i) => wp.element.createElement('div', {
                    key: i,
                    draggable: true,
                    onDragStart: (e) => onDragStart(e, bin.id, item.material_id, item.qty),
                    style: { fontSize: '12px', padding: '5px', background: '#fff', border: '1px solid #eee', marginBottom: '2px', cursor: 'grab' }
                },
                    `Mat #${item.material_id}: ${item.qty} units`
                )) :
                wp.element.createElement('em', { style: { color: '#999' } }, 'Empty')
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

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/warehouses' })
            .then(data => {
                setWarehouses(data);
                if (data.length > 0) setSelectedWh(data[0].id);
                setLoading(false);
            })
            .catch(err => {
                setError('Failed to load Warehouses.');
                setLoading(false);
                console.error(err);
            });
    }, []);

    useEffect(() => {
        if (selectedWh) {
            wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` })
                .then(data => setBins(data));
        }
    }, [selectedWh]);

    if (loading) return wp.element.createElement('p', null, 'Loading Warehouse View...');
    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));

    const onDragStart = (e, sourceBinId, materialId, qty) => {
        e.dataTransfer.setData('transferData', JSON.stringify({ sourceBinId, materialId, qty }));
    };

    const onDragOver = (e) => {
        e.preventDefault();
    };

    const onDrop = (e, targetBinId) => {
        const dataStr = e.dataTransfer.getData('transferData');
        if (!dataStr) return;
        const data = JSON.parse(dataStr);
        if (data.sourceBinId === targetBinId) return;

        const transferQty = prompt(`Transfer quantity (Max: ${data.qty}):`, data.qty);
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
                // Reload bins
                wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` }).then(setBins);
            }).catch(err => {
                alert(__('Inventory transfer failed. Check stock levels and permissions.', 'manufacturing-erp-pro'));
                console.error(err);
            });
        }
    };

    const onBasketDrop = (e) => {
        const dataStr = e.dataTransfer.getData('transferData');
        if (!dataStr) return;
        const data = JSON.parse(dataStr);

        if (!reorderBasket.find(id => id === data.materialId)) {
            setReorderBasket([...reorderBasket, data.materialId]);
        }
    };

    const triggerReorder = () => {
        if (reorderBasket.length === 0) return;
        alert(`${__('Triggering MRP checks for:', 'manufacturing-erp-pro')} ${reorderBasket.length} ${__('items. Redirecting to MRP Planning...', 'manufacturing-erp-pro')}`);
        window.location.href = 'admin.php?page=mep-mrp-planning';
    };

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    return wp.element.createElement('div', { className: 'mep-warehouse-layout mep-admin-style mep-animate-fade-in', style: { display: 'flex', gap: '30px' } },
        wp.element.createElement('div', { style: { flex: 1 } },
        wp.element.createElement('div', { className: 'mep-wh-toolbar', style: { marginBottom: '20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' } },
            wp.element.createElement('div', {
                className: 'mep-wh-selector',
                title: helpMode ? 'Warehouse Selector: Choose a location to view its current bin levels.' : ''
            },
                wp.element.createElement('label', null, 'Select Warehouse: '),
                wp.element.createElement('select', {
                    value: selectedWh,
                    onChange: (e) => setSelectedWh(e.target.value)
                },
                    warehouses.map(wh => wp.element.createElement('option', { key: wh.id, value: wh.id }, wh.name))
                )
            ),
            wp.element.createElement('button', {
                className: 'button button-secondary',
                title: helpMode ? 'Export CSV: Generates a CSV report of all items in inventory across all warehouses.' : '',
                onClick: () => window.location.href = wpApiSettings.root + 'mep/v1/reports/inventory-csv?_wpnonce=' + wpApiSettings.nonce
            }, 'Export Inventory CSV')
        ),
            wp.element.createElement('div', {
                className: 'mep-bins-grid',
                title: helpMode ? 'Warehouse Grid: Shows bin occupancy. Drag materials between cards to perform a visual bin transfer.' : '',
                style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: '15px' }
            },
                bins.map(bin => wp.element.createElement(Bin, {
                    key: bin.id,
                    bin: bin,
                    onDragStart,
                    onDragOver,
                    onDrop,
                    helpMode
                }))
            )
        ),
        // Sidebar: Reorder Basket
        wp.element.createElement('div', {
            className: 'mep-reorder-basket-sidebar',
            onDragOver: (e) => e.preventDefault(),
            onDrop: onBasketDrop,
            title: helpMode ? 'Reorder Basket: Drag materials here from any bin to flag them for reordering. Example: Drag "Thread" if you notice physical stock is low.' : '',
            style: { width: '250px', background: '#f6f7f7', border: '2px dashed #ccd0d4', padding: '20px', borderRadius: '4px' }
        },
            wp.element.createElement('h3', null, '🛒 Reorder Basket'),
            wp.element.createElement('div', { style: { minHeight: '100px', marginBottom: '20px' } },
                reorderBasket.length > 0 ?
                    reorderBasket.map((id, i) => wp.element.createElement('div', { key: i, style: { padding: '5px', borderBottom: '1px solid #ddd', fontSize: '12px' } }, `Mat #${id}`)) :
                    wp.element.createElement('p', { style: { fontSize: '11px', color: '#999' } }, 'Drag materials here to reorder...')
            ),
            reorderBasket.length > 0 && wp.element.createElement('button', {
                className: 'button button-primary',
                style: { width: '100%' },
                onClick: triggerReorder
            }, 'Trigger Reorder Check')
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-warehouse-root');
    if (container) {
        wp.element.render(wp.element.createElement(WarehouseLayout, null), container);
    }
});

})();