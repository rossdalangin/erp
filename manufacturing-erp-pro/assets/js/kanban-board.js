(function() {
/**
 * MEP Production Kanban Board - with HTML5 Drag and Drop
 */

const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const WorkOrderCard = ({ wo, onDragStart, helpMode }) => {
    return wp.element.createElement('div', {
        className: 'mep-wo-card',
        draggable: true,
        title: helpMode ? __('Work Order: Drag this card to a new column to update the manufacturing status of this order. Example: Drag to "In Progress" when the machinist starts cutting.', 'manufacturing-erp-pro') : '',
        onDragStart: (e) => onDragStart(e, wo.id),
        style: { border: '1px solid #ccc', padding: '10px', background: '#fff', marginBottom: '10px', cursor: 'grab' }
    },
        wp.element.createElement('h4', null, wo.title),
        wp.element.createElement('p', { style: { fontSize: '12px', margin: '2px 0' } }, `ID: #${wo.id} | ${__('Qty', 'manufacturing-erp-pro')}: ${wo.qty || 1}`),
        wo.due_date && wp.element.createElement('p', { style: { fontSize: '11px', color: '#d63638', fontWeight: 'bold' } }, `${__('Due', 'manufacturing-erp-pro')}: ${wo.due_date}`),
        wp.element.createElement('p', { style: { fontSize: '11px', color: '#666' } }, `${__('Operator', 'manufacturing-erp-pro')}: ${wo.operator_name || __('Unassigned', 'manufacturing-erp-pro')}`),
        wo.equipment_name && wp.element.createElement('p', { style: { fontSize: '11px', color: '#2271b1' } }, `${__('Machine', 'manufacturing-erp-pro')}: ${wo.equipment_name}`),
        wp.element.createElement('span', { className: 'mep-badge', style: { fontSize: '10px', background: '#eee', padding: '2px 5px' } }, wo.status)
    );
};

const KanbanBoard = () => {
    const [workOrders, setWorkOrders] = useState([]);
    const [operators, setOperators] = useState([]);
    const [equipment, setEquipment] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [dragOverCol, setDragOverCol] = useState(null);

    const columns = [
        { id: 'publish', label: __('Backlog', 'manufacturing-erp-pro') },
        { id: 'in-progress', label: __('In Progress', 'manufacturing-erp-pro') },
        { id: 'completed', label: __('Completed', 'manufacturing-erp-pro') }
    ];

    useEffect(() => {
        Promise.all([
            wp.apiFetch({ path: '/mep/v1/work-orders' }),
            wp.apiFetch({ path: '/mep/v1/operators' }),
            wp.apiFetch({ path: '/mep/v1/equipment' })
        ]).then(([woData, opData, eqData]) => {
                setWorkOrders(woData);
                setOperators(opData);
                setEquipment(eqData);
                setLoading(false);
            })
            .catch(err => {
                setError(__('Failed to load Work Orders or Operators.', 'manufacturing-erp-pro'));
                setLoading(false);
                console.error(err);
            });
    }, []);

    const onDragStart = (e, id) => {
        e.dataTransfer.setData('woId', id);
    };

    const onDragOver = (e) => {
        e.preventDefault(); // Allow drop
    };

    const onDrop = (e, nextStatus) => {
        setDragOverCol(null);
        const id = e.dataTransfer.getData('woId');
        updateOrderStatus(id, nextStatus);
    };

    const updateOrderStatus = (id, nextStatus) => {
        let extraData = {};
        if (nextStatus === 'in-progress') {
            const opId = prompt(__('Enter Operator ID (optional):', 'manufacturing-erp-pro'));
            if (opId) extraData.operator_id = opId;
            const eqId = prompt(__('Enter Machine ID (optional):', 'manufacturing-erp-pro'));
            if (eqId) extraData.equipment_id = eqId;
        }
        if (nextStatus === 'completed') {
            const lotId = prompt(__('Enter or Confirm Lot Number:', 'manufacturing-erp-pro'), `LOT-${id}-${new Date().getMonth()+1}${new Date().getDate()}`);
            const scrap = prompt(__('Enter scrap quantity (if any):', 'manufacturing-erp-pro'), "0");
            const labor = prompt(__('Enter total labor minutes spent:', 'manufacturing-erp-pro'), "60");
            extraData = { ...extraData, lot_number: lotId, scrap_qty: scrap, labor_mins: labor };
        }

        wp.apiFetch({
            path: `/mep/v1/work-orders/${id}/status`,
            method: 'POST',
            data: { status: nextStatus, ...extraData }
        }).then((res) => {
            if (nextStatus === 'completed' && res.qc_id) {
                alert(`${__('Production Complete!', 'manufacturing-erp-pro')}\n${__('Lot Assigned:', 'manufacturing-erp-pro')} ${res.lot_number}\n${__('QC Check Created:', 'manufacturing-erp-pro')} #${res.qc_id}`);
            }

            setWorkOrders(workOrders.map(o => {
                if (o.id == id) {
                    const updated = { ...o, status: nextStatus };
                    if (extraData.operator_id) {
                        const op = operators.find(u => u.id == extraData.operator_id);
                        updated.operator_name = op ? op.name : `User #${extraData.operator_id}`;
                    }
                    if (extraData.equipment_id) {
                        const eq = equipment.find(e => e.id == extraData.equipment_id);
                        updated.equipment_name = eq ? eq.name : `Machine #${extraData.equipment_id}`;
                    }
                    return updated;
                }
                return o;
            }));
        }).catch(err => {
            alert(__('Failed to update Work Order status. Please check your permissions.', 'manufacturing-erp-pro'));
            console.error(err);
        });
    };

    if (loading) return wp.element.createElement('p', null, 'Loading Kanban Board...');
    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    return wp.element.createElement('div', {
        className: 'mep-kanban-board mep-admin-style mep-animate-fade-in',
        title: helpMode ? 'Production Board: Drag Work Order cards between columns.' : '',
    },
        columns.map(col => wp.element.createElement('div', {
            key: col.id,
            className: `mep-kanban-column ${dragOverCol === col.id ? 'is-dragging-over' : ''}`,
            title: helpMode ? `Column (${col.label}): Drop Work Orders here.` : '',
            onDragOver: (e) => { e.preventDefault(); setDragOverCol(col.id); },
            onDragLeave: () => setDragOverCol(null),
            onDrop: (e) => onDrop(e, col.id),
        },
            wp.element.createElement('h3', null, col.label),
            workOrders.filter(wo => wo.status === col.id).map(wo =>
                wp.element.createElement(WorkOrderCard, { key: wo.id, wo: wo, onDragStart: onDragStart, helpMode: helpMode })
            )
        ))
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-kanban-root');
    if (container) {
        wp.element.render(wp.element.createElement(KanbanBoard, null), container);
    }
});

})();