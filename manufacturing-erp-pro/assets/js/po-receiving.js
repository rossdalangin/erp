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
                alert('Goods Received Successfully!');
                // Refresh bins
                wp.apiFetch({ path: `/mep/v1/warehouses/${selectedWh}/bins` }).then(setBins);
            });
        }
    };

    if (loading) return wp.element.createElement('p', null, 'Loading PO Receiving Workspace...');

    return wp.element.createElement('div', { className: 'mep-receiving-layout', style: { display: 'flex', gap: '30px' } },
        // Left: PO List & Items
        wp.element.createElement('div', { style: { width: '300px' } },
            wp.element.createElement('h3', null, 'Open Purchase Orders'),
            pos.map(po => wp.element.createElement('div', {
                key: po.id,
                onClick: () => setSelectedPo(po),
                style: { padding: '10px', border: '1px solid #ccc', marginBottom: '5px', background: selectedPo?.id === po.id ? '#e7f1f9' : '#fff', cursor: 'pointer' }
            }, po.title)),

            selectedPo && wp.element.createElement('div', { style: { marginTop: '20px' } },
                wp.element.createElement('h4', null, `Items in PO #${selectedPo.id}`),
                selectedPo.items.map((item, i) => wp.element.createElement(POItem, { key: i, item: item, poId: selectedPo.id, onDragStart }))
            )
        ),

        // Right: Warehouse Bins (Drop Zones)
        wp.element.createElement('div', { style: { flex: 1 } },
            wp.element.createElement('div', { style: { marginBottom: '20px' } },
                wp.element.createElement('label', null, 'Select Target Warehouse: '),
                wp.element.createElement('select', { value: selectedWh, onChange: (e) => setSelectedWh(e.target.value) },
                    warehouses.map(wh => wp.element.createElement('option', { key: wh.id, value: wh.id }, wh.name))
                )
            ),
            wp.element.createElement('div', { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px' } },
                bins.map(bin => wp.element.createElement('div', {
                    key: bin.id,
                    onDragOver: onDragOver,
                    onDrop: (e) => onDrop(e, bin.id),
                    style: { padding: '20px', border: '2px dashed #ccc', background: '#fcfcfc', borderRadius: '8px', textAlign: 'center' }
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
