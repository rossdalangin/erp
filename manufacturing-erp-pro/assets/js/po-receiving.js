(function() {
/**
 * MEP PO Receiving - Visual Goods Receipt Workflow
 */

const { useState, useEffect } = wp.element;

const POItem = ({ item, poId, onDragStart }) => {
    return wp.element.createElement('div', {
        className: 'mep-po-item-card',
        draggable: true,
        onDragStart: (e) => onDragStart(e, { ...item, poId }),
        style: { padding: '10px', border: '1px solid #ccc', background: '#fff', marginBottom: '10px', cursor: 'grab' }
    },
        wp.element.createElement('strong', null, `Material #${item.material_id}`),
        wp.element.createElement('p', null, `Ordered: ${item.needed} units`),
        wp.element.createElement('small', null, `PO #${poId}`)
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

    useEffect(() => {
        Promise.all([
            wp.apiFetch({ path: '/mep/v1/work-orders' }), // Using this for now or need a real PO list
            wp.apiFetch({ path: '/mep/v1/warehouses' })
        ]).then(([woData, whData]) => {
            // Need real POs, but let's assume we fetch them
            wp.apiFetch({ path: '/wp/v2/mep_po?status=publish' }).then(poData => {
                 setPos(poData.map(p => ({ id: p.id, title: p.title.rendered, items: p.mep_po_lines || [] })));
                 setLoading(false);
            });
            setWarehouses(whData);
            if (whData.length > 0) setSelectedWh(whData[0].id);
        });
    }, []);

    useEffect(() => {
        if (selectedWh) {
            wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` }).then(setBins);
        }
    }, [selectedWh]);

    const onDragStart = (e, item) => {
        e.dataTransfer.setData('receiptItem', JSON.stringify(item));
    };

    const onDragOver = (e) => e.preventDefault();

    const onDrop = (e, binId) => {
        setDragOverBin(null);
        const item = JSON.parse(e.dataTransfer.getData('receiptItem'));
        const qty = prompt(`Enter quantity to receive into this bin (Max: ${item.needed}):`, item.needed);

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
                // Refresh bins
                wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` }).then(setBins);
            }).catch(err => {
                alert(__('Failed to receive goods. Please check permissions.', 'manufacturing-erp-pro'));
                console.error(err);
            });
        }
    };

    if (loading) return wp.element.createElement('p', null, 'Loading PO Receiving Workspace...');

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    return wp.element.createElement('div', { className: 'mep-column-container mep-admin-style mep-animate-fade-in' },
        // Left: PO List & Items
        wp.element.createElement('div', {
            className: 'mep-column',
            style: { flex: '0 0 350px' },
            title: helpMode ? 'PO List: Select an open Purchase Order to view its lines.' : ''
        },
            wp.element.createElement('h3', null, '📑 ' + __('Open Purchase Orders', 'manufacturing-erp-pro')),
            pos.map(po => wp.element.createElement('div', {
                key: po.id,
                onClick: () => setSelectedPo(po),
                className: 'mep-library-item',
                style: { background: selectedPo?.id === po.id ? 'var(--mep-bg-canvas)' : '', borderColor: selectedPo?.id === po.id ? 'var(--mep-primary)' : '', cursor: 'pointer' }
            }, po.title)),

            selectedPo && wp.element.createElement('div', { style: { marginTop: '30px' } },
                wp.element.createElement('h4', null, `${__('Items in PO', 'manufacturing-erp-pro')} #${selectedPo.id}`),
                selectedPo.items.map((item, i) => wp.element.createElement(POItem, { key: i, item: item, poId: selectedPo.id, onDragStart }))
            )
        ),

        // Right: Warehouse Bins (Drop Zones)
        wp.element.createElement('div', { className: 'mep-column', style: { flex: 2 } },
            wp.element.createElement('div', { style: { marginBottom: '20px' } },
                wp.element.createElement('label', null, 'Select Target Warehouse: '),
                wp.element.createElement('select', { value: selectedWh, onChange: (e) => setSelectedWh(e.target.value) },
                    warehouses.map(wh => wp.element.createElement('option', { key: wh.id, value: wh.id }, wh.name))
                )
            ),
            wp.element.createElement('div', {
                className: 'mep-bins-grid',
                title: helpMode ? 'Receiving Zones: Drag items from the left into these bins.' : ''
            },
                bins.map(bin => wp.element.createElement('div', {
                    key: bin.id,
                    onDragOver: (e) => { e.preventDefault(); setDragOverBin(bin.id); },
                    onDragLeave: () => setDragOverBin(null),
                    onDrop: (e) => onDrop(e, bin.id),
                    className: `mep-bin-card ${dragOverBin === bin.id ? 'occupancy-medium' : ''}`,
                    style: { borderStyle: 'dashed', cursor: 'default' }
                },
                    wp.element.createElement('strong', null, bin.name),
                    wp.element.createElement('p', { style: { fontSize: '11px', color: '#999' } }, 'Drop items here to receive stock')
                ))
            )
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-po-receiving-root');
    if (container) {
        wp.element.render(wp.element.createElement(POReceiving, null), container);
    }
});

})();