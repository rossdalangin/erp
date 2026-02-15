(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const LoadingUI = ({ message = __('Loading...', 'manufacturing-erp-pro') }) => (
    wp.element.createElement('div', { className: 'mep-loading-container' },
        wp.element.createElement('div', { className: 'mep-spinner' }),
        wp.element.createElement('p', null, message)
    )
);

const POReceiving = () => {
    const [pos, setPos] = useState([]);
    const [selectedPo, setSelectedPo] = useState(null);
    const [warehouses, setWarehouses] = useState([]);
    const [selectedWh, setSelectedWh] = useState(null);
    const [bins, setBins] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        Promise.all([
            wp.apiFetch({ path: '/mep/v1/purchase-orders' }),
            wp.apiFetch({ path: '/mep/v1/warehouses' })
        ]).then(([poData, whData]) => {
            setPos(poData || []);
            setWarehouses(whData || []);
            if (whData && whData.length > 0) setSelectedWh(whData[0].id);
            setLoading(false);
        });
    }, []);

    useEffect(() => {
        if (selectedWh) {
            wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` }).then(data => setBins(data || []));
        }
    }, [selectedWh]);

    const onDrop = (e, binId) => {
        const item = JSON.parse(e.dataTransfer.getData('receiptItem'));
        const qty = prompt(__('Quantity to receive:', 'manufacturing-erp-pro'), item.needed);
        if (qty) {
            wp.apiFetch({ path: '/mep/v1/inventory/receive', method: 'POST', data: { material_id: item.material_id, bin_id: binId, warehouse_id: selectedWh, quantity: parseFloat(qty), reference_id: item.poId, type: 'RECEIVE' } })
                .then(() => wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` }).then(data => setBins(data || [])));
        }
    };

    if (loading) return wp.element.createElement(LoadingUI, { message: __('Loading PO Receiving Workspace...', 'manufacturing-erp-pro') });

    return wp.element.createElement('div', { className: 'mep-receiving-layout', style: { display: 'flex', gap: '30px', background: '#fff', padding: '20px', border: '1px solid #ccc' } },
        wp.element.createElement('div', { style: { width: '300px' } },
            wp.element.createElement('h3', null, __('Open Purchase Orders', 'manufacturing-erp-pro')),
            (pos || []).map(po => wp.element.createElement('div', { key: po.id, onClick: () => setSelectedPo(po), style: { padding: '10px', border: '1px solid #ccc', marginBottom: '5px', background: selectedPo?.id === po.id ? '#e7f1f9' : '#fff', cursor: 'pointer' } }, po.title)),
            selectedPo && wp.element.createElement('div', null,
                wp.element.createElement('h4', null, `${__('Items in PO', 'manufacturing-erp-pro')} #${selectedPo.id}`),
                (selectedPo.items || []).map((item, i) => wp.element.createElement('div', { key: i, draggable: true, onDragStart: (e) => e.dataTransfer.setData('receiptItem', JSON.stringify({ ...item, poId: selectedPo.id })), style: { padding: '10px', border: '1px solid #eee', background: '#f9f9f9', marginBottom: '5px', cursor: 'grab' } }, `Mat #${item.material_id}: ${item.needed}`))
            )
        ),
        wp.element.createElement('div', { style: { flex: 1 } },
            wp.element.createElement('div', { style: { marginBottom: '20px' } },
                wp.element.createElement('label', null, __('Target Warehouse: ', 'manufacturing-erp-pro')),
                wp.element.createElement('select', { value: selectedWh, onChange: (e) => setSelectedWh(e.target.value) }, (warehouses || []).map(wh => wp.element.createElement('option', { key: wh.id, value: wh.id }, wh.name)))
            ),
            wp.element.createElement('div', { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px' } },
                (bins || []).map(bin => wp.element.createElement('div', { key: bin.id, onDragOver: (e) => e.preventDefault(), onDrop: (e) => onDrop(e, bin.id), style: { padding: '20px', border: '2px dashed #ccc', background: '#fcfcfc', borderRadius: '8px', textAlign: 'center' } }, wp.element.createElement('strong', null, bin.name)))
            )
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-po-receiving-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(container).render(wp.element.createElement(POReceiving, null)); }
        else { wp.element.render(wp.element.createElement(POReceiving, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
