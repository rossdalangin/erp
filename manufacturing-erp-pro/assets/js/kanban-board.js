/**
 * MEP Production Kanban Board - with HTML5 Drag and Drop
 */

const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const WorkOrderCard = ({ wo, onDragStart, helpMode }) => {
    return wp.element.createElement('div', {
        className: 'mep-wo-card',
        draggable: true,
        title: helpMode ? 'Work Order: Drag this card to a new column to update the manufacturing status of this order.' : '',
        onDragStart: (e) => onDragStart(e, wo.id),
        style: { border: '1px solid #ccc', padding: '10px', background: '#fff', marginBottom: '10px', cursor: 'grab' }
    },
        wp.element.createElement('h4', null, wo.title),
        wp.element.createElement('p', { style: { fontSize: '12px' } }, `ID: #${wo.id}`),
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

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/work-orders' })
            .then(data => {
                setWorkOrders(data);
                setLoading(false);
            })
            .catch(err => {
                setError('Failed to load Work Orders.');
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
        const id = e.dataTransfer.getData('woId');
        updateOrderStatus(id, nextStatus);
    };

    const updateOrderStatus = (id, nextStatus) => {
        let extraData = {};
        if (nextStatus === 'completed') {
            const scrap = prompt(__('Enter scrap quantity (if any):', 'manufacturing-erp-pro'), "0");
            const labor = prompt(__('Enter total labor minutes spent:', 'manufacturing-erp-pro'), "60");
            extraData = { scrap_qty: scrap, labor_mins: labor };
        }

        wp.apiFetch({
            path: `/mep/v1/work-orders/${id}/status`,
            method: 'POST',
            data: { status: nextStatus, ...extraData }
        }).then(() => {
            setWorkOrders(workOrders.map(o => o.id == id ? { ...o, status: nextStatus } : o));
        });
    };

    if (loading) return wp.element.createElement('p', null, 'Loading Kanban Board...');
    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    return wp.element.createElement('div', {
        className: 'mep-kanban-board',
        title: helpMode ? 'Production Board: Drag Work Order cards between columns to update their manufacturing status.' : '',
        style: { display: 'flex', gap: '20px', alignItems: 'flex-start' }
    },
        columns.map(col => wp.element.createElement('div', {
            key: col.id,
            className: 'mep-kanban-column',
            title: helpMode ? `Column (${col.label}): Drop Work Orders here to set them to ${col.label} status.` : '',
            onDragOver: onDragOver,
            onDrop: (e) => onDrop(e, col.id),
            style: { flex: 1, background: '#f0f0f1', padding: '15px', minHeight: '500px', border: '2px dashed transparent' }
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
