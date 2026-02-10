/**
 * MEP Warehouse Layout - Visual Inventory Management
 */

const { useState, useEffect } = wp.element;

const Bin = ({ bin, onTransfer, onDragStart, onDragOver, onDrop }) => {
    return wp.element.createElement('div', {
        className: 'mep-bin-card',
        onDragOver: onDragOver,
        onDrop: (e) => onDrop(e, bin.id),
        style: { border: '1px solid #ccc', padding: '10px', minWidth: '150px', background: '#fcfcfc' }
    },
        wp.element.createElement('h4', null, bin.name),
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
        const data = JSON.parse(e.dataTransfer.getData('transferData'));
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
            });
        }
    };

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    return wp.element.createElement('div', { className: 'mep-warehouse-layout' },
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
                onDrop
            }))
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-warehouse-root');
    if (container) {
        wp.element.render(wp.element.createElement(WarehouseLayout, null), container);
    }
});
