(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const LoadingUI = ({ message = __('Loading...', 'manufacturing-erp-pro') }) => (
    wp.element.createElement('div', { className: 'mep-loading-container' },
        wp.element.createElement('div', { className: 'mep-spinner' }),
        wp.element.createElement('p', null, message)
    )
);

const Bin = ({ bin, onDragStart, onDragOver, onDrop }) => {
    const bgColor = bin.occupancy > 90 ? '#fff0f0' : (bin.occupancy > 70 ? '#fffcf0' : '#fcfcfc');

    return wp.element.createElement('div', {
        className: 'mep-bin-card',
        onDragOver: onDragOver,
        onDrop: (e) => onDrop(e, bin.id),
        style: { border: '1px solid #ccc', padding: '15px', minWidth: '180px', background: bgColor, borderRadius: '4px' }
    },
        wp.element.createElement('h4', { style: { margin: 0, fontSize: '14px' } }, bin.name),
        wp.element.createElement('p', { style: { fontSize: '10px' } }, `${bin.occupancy}% Full`),
        wp.element.createElement('div', null,
            (bin.items || []).map((item, i) => wp.element.createElement('div', {
                key: i,
                draggable: true,
                onDragStart: (e) => onDragStart(e, bin.id, item.material_id, item.qty),
                style: { fontSize: '11px', padding: '3px', background: '#fff', border: '1px solid #eee', marginBottom: '2px', cursor: 'grab' }
            }, `Mat #${item.material_id}: ${item.qty}`))
        )
    );
};

const WarehouseLayout = () => {
    const [warehouses, setWarehouses] = useState([]);
    const [selectedWh, setSelectedWh] = useState(null);
    const [bins, setBins] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/warehouses' }).then(data => {
            setWarehouses(data || []);
            if (data && data.length > 0) setSelectedWh(data[0].id);
            setLoading(false);
        });
    }, []);

    useEffect(() => {
        if (selectedWh) {
            wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` }).then(data => setBins(data || []));
        }
    }, [selectedWh]);

    const onDrop = (e, targetBinId) => {
        const data = JSON.parse(e.dataTransfer.getData('transferData'));
        const qty = prompt(__('Qty to transfer:', 'manufacturing-erp-pro'), data.qty);
        if (qty) {
            wp.apiFetch({ path: '/mep/v1/inventory/transfer', method: 'POST', data: { material_id: data.materialId, source_bin_id: data.sourceBinId, target_bin_id: targetBinId, quantity: parseFloat(qty) } })
                .then(() => wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` }).then(setBins));
        }
    };

    if (loading && warehouses.length === 0) return wp.element.createElement(LoadingUI, { message: __('Loading Warehouse...', 'manufacturing-erp-pro') });

    return wp.element.createElement('div', { className: 'mep-warehouse-layout', style: { display: 'flex', gap: '20px', background: '#fff', padding: '20px', border: '1px solid #ccc' } },
        wp.element.createElement('div', { style: { flex: 1 } },
            wp.element.createElement('div', { style: { marginBottom: '20px' } },
                wp.element.createElement('label', null, __('Select Warehouse: ', 'manufacturing-erp-pro')),
                wp.element.createElement('select', { value: selectedWh, onChange: (e) => setSelectedWh(e.target.value) }, (warehouses || []).map(wh => wp.element.createElement('option', { key: wh.id, value: wh.id }, wh.name)))
            ),
            wp.element.createElement('div', { style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: '15px' } },
                (bins || []).map(bin => wp.element.createElement(Bin, { key: bin.id, bin, onDragStart: (e, s, m, q) => e.dataTransfer.setData('transferData', JSON.stringify({ sourceBinId: s, materialId: m, qty: q })), onDragOver: (e) => e.preventDefault(), onDrop }))
            )
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-warehouse-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(container).render(wp.element.createElement(WarehouseLayout, null)); }
        else { wp.element.render(wp.element.createElement(WarehouseLayout, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
