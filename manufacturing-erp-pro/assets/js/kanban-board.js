(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const WorkOrderCard = ({ wo, onDragStart }) => {
    return wp.element.createElement('div', {
        className: 'mep-wo-card',
        draggable: true,
        onDragStart: (e) => onDragStart(e, wo.id),
    },
        wp.element.createElement('h4', null, wo.title),
        wp.element.createElement('div', { style: { fontSize: '12px', color: '#64748b' } },
            `#${wo.id} | ${__('Qty', 'manufacturing-erp-pro')}: ${wo.qty}`
        ),
        wo.due_date && wp.element.createElement('div', { style: { fontSize: '11px', color: 'var(--mep-danger)', fontWeight: 'bold', marginTop: '5px' } },
            `${__('Due', 'manufacturing-erp-pro')}: ${wo.due_date}`
        ),
        wp.element.createElement('div', { style: { marginTop: '10px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' } },
            wp.element.createElement('span', { className: 'mep-badge' }, wo.status),
            wp.element.createElement('span', { style: { fontSize: '10px', color: '#94a3b8' } }, wo.operator_name || __('Unassigned', 'manufacturing-erp-pro'))
        )
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

    const fetchData = () => {
        setLoading(true);
        Promise.all([
            wp.apiFetch({ path: '/mep/v1/work-orders' }),
            wp.apiFetch({ path: '/mep/v1/operators' }),
            wp.apiFetch({ path: '/mep/v1/equipment' })
        ]).then(([woData, opData, eqData]) => {
                setWorkOrders(woData || []);
                setOperators(opData || []);
                setEquipment(eqData || []);
                setLoading(false);
            })
            .catch(err => {
                setError(__('Failed to load shop floor data.', 'manufacturing-erp-pro'));
                setLoading(false);
            });
    };

    useEffect(() => {
        fetchData();
    }, []);

    const onDragStart = (e, id) => {
        e.dataTransfer.setData('woId', id);
    };

    const updateOrderStatus = (id, nextStatus) => {
        let extraData = {};
        if (nextStatus === 'in-progress') {
            const opId = prompt(__('Operator ID:', 'manufacturing-erp-pro'));
            if (opId) extraData.operator_id = opId;
        }
        if (nextStatus === 'completed') {
            const lotId = prompt(__('Lot #:', 'manufacturing-erp-pro'), `LOT-${id}`);
            if (!lotId) return;
            extraData = { lot_number: lotId, scrap_qty: 0, labor_mins: 60 };
        }

        wp.apiFetch({
            path: `/mep/v1/work-orders/${id}/status`,
            method: 'POST',
            data: { status: nextStatus, ...extraData }
        }).then(() => fetchData()).catch(err => alert(__('Update failed.', 'manufacturing-erp-pro')));
    };

    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));
    if (loading && workOrders.length === 0) return wp.element.createElement('p', null, __('Loading Kanban...', 'manufacturing-erp-pro'));

    return wp.element.createElement('div', { className: 'mep-kanban-board mep-admin-style mep-animate-fade-in' },
        columns.map(col => wp.element.createElement('div', {
            key: col.id,
            className: `mep-kanban-column ${dragOverCol === col.id ? 'is-dragging-over' : ''}`,
            onDragOver: (e) => { e.preventDefault(); setDragOverCol(col.id); },
            onDragLeave: () => setDragOverCol(null),
            onDrop: (e) => {
                setDragOverCol(null);
                const id = e.dataTransfer.getData('woId');
                updateOrderStatus(id, col.id);
            },
        },
            wp.element.createElement('h3', null, col.label),
            (workOrders || []).filter(wo => wo.status === col.id).map(wo =>
                wp.element.createElement(WorkOrderCard, { key: wo.id, wo, onDragStart })
            )
        ))
    );
};

const init = () => {
    const container = document.getElementById('mep-kanban-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(null), container).render(wp.element.createElement(KanbanBoard); } else { wp.element.render(wp.element.createElement(KanbanBoard, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
