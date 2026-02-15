(function() {
/**
 * MEP Production Kanban Board - with HTML5 Drag and Drop
 */

const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const LoadingUI = ({ message = __('Loading...', 'manufacturing-erp-pro') }) => (
    wp.element.createElement('div', { className: 'mep-loading-container' },
        wp.element.createElement('div', { className: 'mep-spinner' }),
        wp.element.createElement('p', null, message)
    )
);

const WorkOrderCard = ({ wo, onDragStart }) => {
    return wp.element.createElement('div', {
        className: 'mep-wo-card',
        draggable: true,
        onDragStart: (e) => onDragStart(e, wo.id),
        style: { border: '1px solid #ccc', padding: '10px', background: '#fff', marginBottom: '10px', cursor: 'grab', borderRadius: '4px' }
    },
        wp.element.createElement('h4', { style: { margin: '0 0 5px 0' } }, wo.title),
        wp.element.createElement('p', { style: { fontSize: '12px', margin: '2px 0' } }, `ID: #${wo.id} | ${__('Qty', 'manufacturing-erp-pro')}: ${wo.qty}`),
        wo.due_date && wp.element.createElement('p', { style: { fontSize: '11px', color: '#d63638', fontWeight: 'bold' } }, `${__('Due', 'manufacturing-erp-pro')}: ${wo.due_date}`),
        wp.element.createElement('span', { className: 'mep-badge', style: { fontSize: '10px', background: '#eee', padding: '2px 5px' } }, wo.status)
    );
};

const KanbanBoard = () => {
    const [workOrders, setWorkOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const columns = [
        { id: 'publish', label: __('Backlog', 'manufacturing-erp-pro') },
        { id: 'in-progress', label: __('In Progress', 'manufacturing-erp-pro') },
        { id: 'completed', label: __('Completed', 'manufacturing-erp-pro') }
    ];

    const loadData = () => {
        wp.apiFetch({ path: '/mep/v1/work-orders' })
            .then(data => { setWorkOrders(data || []); setLoading(false); })
            .catch(err => { setError(__('Failed to load Work Orders.', 'manufacturing-erp-pro')); setLoading(false); });
    };

    useEffect(() => { loadData(); }, []);

    const onDragStart = (e, id) => { e.dataTransfer.setData('woId', id); };
    const onDragOver = (e) => { e.preventDefault(); };

    const onDrop = (e, nextStatus) => {
        const id = e.dataTransfer.getData('woId');
        let extra = {};
        if (nextStatus === 'completed') extra = { lot_number: `LOT-${id}`, scrap_qty: 0, labor_mins: 60 };

        wp.apiFetch({
            path: `/mep/v1/work-orders/${id}/status`,
            method: 'POST',
            data: { status: nextStatus, ...extra }
        }).then(() => loadData());
    };

    if (loading && workOrders.length === 0) return wp.element.createElement(LoadingUI, { message: __('Loading Production Board...', 'manufacturing-erp-pro') });

    return wp.element.createElement('div', {
        className: 'mep-kanban-board',
        style: { display: 'flex', gap: '20px', alignItems: 'flex-start', overflowX: 'auto', padding: '10px 0' }
    },
        columns.map(col => wp.element.createElement('div', {
            key: col.id,
            className: 'mep-kanban-column',
            onDragOver: onDragOver,
            onDrop: (e) => onDrop(e, col.id),
            style: { flex: '1 0 300px', background: '#f0f0f1', padding: '15px', minHeight: '500px', borderRadius: '6px' }
        },
            wp.element.createElement('h3', { style: { marginTop: 0, borderBottom: '2px solid #2271b1', paddingBottom: '10px' } }, col.label),
            (workOrders || []).filter(wo => wo.status === col.id).map(wo =>
                wp.element.createElement(WorkOrderCard, { key: wo.id, wo, onDragStart })
            )
        ))
    );
};

const init = () => {
    const container = document.getElementById('mep-kanban-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(container).render(wp.element.createElement(KanbanBoard, null)); }
        else { wp.element.render(wp.element.createElement(KanbanBoard, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
