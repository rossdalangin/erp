/**
 * MEP Production Kanban Board
 */

const { useState, useEffect } = wp.element;

const WorkOrderCard = ({ wo, onMove }) => {
    return wp.element.createElement('div', {
        className: 'mep-wo-card',
        style: { border: '1px solid #ccc', padding: '10px', background: '#fff', marginBottom: '10px' }
    },
        wp.element.createElement('h4', null, wo.title),
        wp.element.createElement('p', { style: { fontSize: '12px' } }, `ID: #${wo.id}`),
        wp.element.createElement('div', { className: 'mep-wo-actions' },
            wo.status !== 'completed' && wp.element.createElement('button', {
                className: 'button button-small',
                onClick: () => onMove(wo.id, 'next')
            }, 'Advance →')
        )
    );
};

const KanbanBoard = () => {
    const [workOrders, setWorkOrders] = useState([]);
    const [loading, setLoading] = useState(true);

    const columns = [
        { id: 'publish', label: 'Backlog' },
        { id: 'in-progress', label: 'In Progress' },
        { id: 'completed', label: 'Completed' }
    ];

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/work-orders' })
            .then(data => {
                setWorkOrders(data);
                setLoading(false);
            });
    }, []);

    const moveOrder = (id, direction) => {
        const wo = workOrders.find(o => o.id === id);
        let nextStatus = wo.status;
        if (wo.status === 'publish') nextStatus = 'in-progress';
        else if (wo.status === 'in-progress') nextStatus = 'completed';

        wp.apiFetch({
            path: `/mep/v1/work-orders/${id}/status`,
            method: 'POST',
            data: { status: nextStatus }
        }).then(() => {
            setWorkOrders(workOrders.map(o => o.id === id ? { ...o, status: nextStatus } : o));
        });
    };

    if (loading) return wp.element.createElement('p', null, 'Loading Kanban Board...');

    return wp.element.createElement('div', {
        className: 'mep-kanban-board',
        style: { display: 'flex', gap: '20px', alignItems: 'flex-start' }
    },
        columns.map(col => wp.element.createElement('div', {
            key: col.id,
            className: 'mep-kanban-column',
            style: { flex: 1, background: '#f0f0f1', padding: '15px', minHeight: '400px' }
        },
            wp.element.createElement('h3', null, col.label),
            workOrders.filter(wo => wo.status === col.id).map(wo =>
                wp.element.createElement(WorkOrderCard, { key: wo.id, wo: wo, onMove: moveOrder })
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
