/**
 * MEP Warehouse Layout - Visual Inventory Management
 */

const { useState, useEffect } = wp.element;

const Bin = ({ bin, onTransfer }) => {
    return wp.element.createElement('div', {
        className: 'mep-bin-card',
        style: { border: '1px solid #ccc', padding: '10px', minWidth: '150px', background: '#fcfcfc' }
    },
        wp.element.createElement('h4', null, bin.name),
        wp.element.createElement('div', { className: 'mep-bin-contents' },
            bin.items.length > 0 ?
                bin.items.map((item, i) => wp.element.createElement('div', { key: i, style: { fontSize: '12px' } },
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

    return wp.element.createElement('div', { className: 'mep-warehouse-layout' },
        wp.element.createElement('div', { className: 'mep-wh-toolbar', style: { marginBottom: '20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' } },
            wp.element.createElement('div', { className: 'mep-wh-selector' },
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
            style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: '15px' }
        },
            bins.map(bin => wp.element.createElement(Bin, { key: bin.id, bin: bin }))
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-warehouse-root');
    if (container) {
        wp.element.render(wp.element.createElement(WarehouseLayout, null), container);
    }
});
