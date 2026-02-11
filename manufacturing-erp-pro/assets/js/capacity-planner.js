/**
 * MEP Detailed Capacity & Maintenance Planner
 */

const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const CapacityPlanner = () => {
    const [equipment, setEquipment] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/equipment/detailed' })
            .then(res => {
                setEquipment(res);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, []);

    if (loading) return wp.element.createElement('p', null, __('Loading Capacity Data...', 'manufacturing-erp-pro'));

    return wp.element.createElement('div', { className: 'mep-capacity-planner' },
        equipment.map(item => wp.element.createElement('div', {
            key: item.id,
            style: { background: '#fff', padding: '20px', border: '1px solid #ccd0d4', marginBottom: '20px' }
        },
            wp.element.createElement('div', { style: { display: 'flex', justifyContent: 'space-between', borderBottom: '1px solid #eee', paddingBottom: '10px' } },
                wp.element.createElement('h2', { style: { margin: 0 } }, item.name),
                wp.element.createElement('span', { className: 'mep-badge' }, `${__('Capacity', 'manufacturing-erp-pro')}: ${item.capacity} ${__('mins/day', 'manufacturing-erp-pro')}`)
            ),

            wp.element.createElement('div', { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '40px', marginTop: '20px' } },
                // Left side: Schedule/Load (simplified placeholder)
                wp.element.createElement('div', null,
                    wp.element.createElement('h3', null, __('Operational Load', 'manufacturing-erp-pro')),
                    wp.element.createElement('p', { style: { color: '#666' } }, __('Machine schedule for the current week:', 'manufacturing-erp-pro')),
                    wp.element.createElement('div', { style: { display: 'flex', gap: '5px' } },
                        ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].map(day => wp.element.createElement('div', {
                            key: day,
                            style: { flex: 1, textAlign: 'center', background: '#f0f0f1', padding: '10px' }
                        },
                            wp.element.createElement('div', { style: { fontSize: '10px' } }, day),
                            wp.element.createElement('div', { style: { height: '50px', background: '#2271b1', marginTop: '5px', opacity: Math.random() + 0.2 } })
                        ))
                    )
                ),

                // Right side: Maintenance
                wp.element.createElement('div', null,
                    wp.element.createElement('h3', null, __('Maintenance History', 'manufacturing-erp-pro')),
                    item.maintenance_logs.length > 0 ?
                        wp.element.createElement('ul', { style: { fontSize: '12px' } },
                            item.maintenance_logs.map((log, i) => wp.element.createElement('li', { key: i, style: { marginBottom: '5px' } },
                                wp.element.createElement('strong', null, log.date.split(' ')[0]),
                                ': ',
                                log.description,
                                log.cost > 0 && ` ($${log.cost})`
                            ))
                        ) : wp.element.createElement('p', { style: { color: '#999', fontSize: '12px' } }, __('No maintenance records found.', 'manufacturing-erp-pro'))
                )
            )
        ))
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-capacity-root');
    if (container) {
        wp.element.render(wp.element.createElement(CapacityPlanner, null), container);
    }
});
