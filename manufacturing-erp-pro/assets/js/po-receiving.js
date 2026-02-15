(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const POItem = ({ item, poId, onDragStart }) => {
    return wp.element.createElement('div', {
        className: 'mep-po-item-card mep-library-item',
        draggable: true,
        onDragStart: (e) => onDragStart(e, { ...item, poId }),
    },
        wp.element.createElement('strong', null, `Mat #${item.material_id}`),
        wp.element.createElement('p', { style: { margin: '5px 0', fontSize: '12px' } }, `Qty: ${item.needed} units`),
        wp.element.createElement('small', { style: { opacity: 0.7 } }, `PO #${poId}`)
    );
};

const POReceiving = () => {
    const [pos, setPos] = useState([]);
    const [selectedPo, setSelectedPo] = useState(null);
    const [warehouses, setWarehouses] = useState([]);
    const [selectedWh, setSelectedWh] = useState(null);
    const [bins, setBins] = useState([]);
    const [loading, setLoading] = useState(true);
    const [dragOverBin, setDragOverBin] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        Promise.all([
            wp.apiFetch({ path: '/mep/v1/purchase-orders' }),
            wp.apiFetch({ path: '/mep/v1/warehouses' })
        ]).then(([poData, whData]) => {
            setPos(poData || []);
            setWarehouses(whData || []);
            if (whData && whData.length > 0) setSelectedWh(whData[0].id);
            setLoading(false);
        }).catch(err => {
            setError(__('Failed to initialize Goods Receipt Workspace.', 'manufacturing-erp-pro'));
            setLoading(false);
        });
    }, []);

    useEffect(() => {
        if (selectedWh) {
            wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` }).then(data => setBins(data || []));
        }
    }, [selectedWh]);

    const onDragStart = (e, item) => {
        e.dataTransfer.setData('receiptItem', JSON.stringify(item));
    };

    const onDragOver = (e) => e.preventDefault();

    const onDrop = (e, binId) => {
        setDragOverBin(null);
        const item = JSON.parse(e.dataTransfer.getData('receiptItem'));
        const qty = prompt(__('Quantity to receive:', 'manufacturing-erp-pro'), item.needed);

        if (qty && parseFloat(qty) > 0) {
            wp.apiFetch({
                path: '/mep/v1/inventory/receive',
                method: 'POST',
                data: {
                    material_id: item.material_id,
                    bin_id: binId,
                    warehouse_id: selectedWh,
                    quantity: parseFloat(qty),
                    reference_id: item.poId,
                    type: 'RECEIVE'
                }
            }).then(() => {
                alert(__('Goods Received Successfully!', 'manufacturing-erp-pro'));
                wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` }).then(data => setBins(data || []));
            }).catch(err => alert(__('Failed to receive goods.', 'manufacturing-erp-pro')));
        }
    };

    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));
    if (loading) return wp.element.createElement('p', null, __('Loading Receipt Workspace...', 'manufacturing-erp-pro'));

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    return wp.element.createElement('div', { className: 'mep-column-container mep-admin-style mep-animate-fade-in' },
        wp.element.createElement('div', { className: 'mep-column', style: { flex: '0 0 350px' } },
            wp.element.createElement('h3', null, '📑 ' + __('Open Purchase Orders', 'manufacturing-erp-pro')),
            (pos || []).length > 0 ? pos.map(po => wp.element.createElement('div', {
                key: po.id,
                onClick: () => setSelectedPo(po),
                className: 'mep-library-item',
                style: { background: selectedPo?.id === po.id ? '#fff' : '', borderColor: selectedPo?.id === po.id ? 'var(--mep-primary)' : '', cursor: 'pointer', borderLeft: selectedPo?.id === po.id ? '4px solid var(--mep-primary)' : '' }
            }, po.title)) : wp.element.createElement('p', { style: { color: '#94a3b8' } }, __('No open POs found.', 'manufacturing-erp-pro')),

            selectedPo && wp.element.createElement('div', { style: { marginTop: '30px' } },
                wp.element.createElement('h4', null, `${__('Items in PO', 'manufacturing-erp-pro')} #${selectedPo.id}`),
                (selectedPo.items || []).map((item, i) => wp.element.createElement(POItem, { key: i, item: item, poId: selectedPo.id, onDragStart }))
            )
        ),

        wp.element.createElement('div', { className: 'mep-column', style: { flex: 2 } },
            wp.element.createElement('h3', null, '📥 ' + __('Bin Drop Zones', 'manufacturing-erp-pro')),
            wp.element.createElement('div', { style: { marginBottom: '20px' } },
                wp.element.createElement('label', null, __('Target Warehouse:', 'manufacturing-erp-pro')),
                wp.element.createElement('select', { value: selectedWh, onChange: (e) => setSelectedWh(e.target.value), style: { marginLeft: '10px' } },
                    (warehouses || []).map(wh => wp.element.createElement('option', { key: wh.id, value: wh.id }, wh.name))
                )
            ),
            wp.element.createElement('div', { className: 'mep-bins-grid' },
                (bins || []).map(bin => wp.element.createElement('div', {
                    key: bin.id,
                    onDragOver: (e) => { e.preventDefault(); setDragOverBin(bin.id); },
                    onDragLeave: () => setDragOverBin(null),
                    onDrop: (e) => onDrop(e, bin.id),
                    className: `mep-bin-card ${dragOverBin === bin.id ? 'is-dragging-over' : ''}`,
                    style: { borderStyle: 'dashed', cursor: 'default', padding: '30px' }
                },
                    wp.element.createElement('strong', null, bin.name),
                    wp.element.createElement('p', { style: { fontSize: '11px', color: '#94a3b8', marginTop: '10px' } }, __('Drop here to receive', 'manufacturing-erp-pro'))
                ))
            )
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-po-receiving-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(null), container).render(wp.element.createElement(POReceiving); } else { wp.element.render(wp.element.createElement(POReceiving, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
