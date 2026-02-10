/**
 * MEP Executive Dashboard - Visual KPIs
 */

const { useState, useEffect } = wp.element;

const KPICard = ({ label, value, color }) => {
    return wp.element.createElement('div', {
        className: 'mep-kpi-card',
        style: { borderTop: `4px solid ${color}`, padding: '20px', background: '#fff', textAlign: 'center' }
    },
        wp.element.createElement('h3', { style: { margin: 0, fontSize: '14px', color: '#666' } }, label),
        wp.element.createElement('p', { style: { fontSize: '24px', fontWeight: 'bold', margin: '10px 0' } }, value)
    );
};

const Dashboard = () => {
    const [kpis, setKpis] = useState(null);
    const [capacity, setCapacity] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        Promise.all([
            wp.apiFetch({ path: '/mep/v1/reports/kpis' }),
            wp.apiFetch({ path: '/mep/v1/equipment/capacity' })
        ]).then(([kpiData, capData]) => {
            setKpis(kpiData);
            setCapacity(capData);
            setLoading(false);
        });
    }, []);

    if (loading) return wp.element.createElement('p', null, 'Loading ERP Dashboard...');

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    return wp.element.createElement('div', { className: 'mep-dashboard-grid' },
        wp.element.createElement('div', {
            title: helpMode ? 'KPI Tiles: Real-time snapshots of factory performance, stock value, and output.' : '',
            style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '20px' }
        },
            wp.element.createElement(KPICard, { label: 'Production Output', value: kpis.production_output, color: '#2271b1' }),
            wp.element.createElement(KPICard, { label: 'Scrap Rate', value: kpis.scrap_rate, color: '#d63638' }),
            wp.element.createElement(KPICard, { label: 'Inventory Value', value: kpis.inventory_value, color: '#dba617' }),
            wp.element.createElement(KPICard, { label: 'On-Time Delivery', value: kpis.on_time_delivery, color: '#673ab7' })
        ),
        wp.element.createElement('div', { style: { marginTop: '30px', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '30px' } },
            wp.element.createElement('div', {
                title: helpMode ? 'Capacity Planner: Monitor machine load. Red bars indicate resources that are over-capacity.' : '',
                style: { padding: '20px', background: '#fff', border: '1px solid #ccc' }
            },
                wp.element.createElement('h3', null, 'Resource Capacity (Load vs. Capacity)'),
                capacity.length > 0 ? capacity.map(item => wp.element.createElement('div', { key: item.id, style: { marginBottom: '15px' } },
                    wp.element.createElement('div', { style: { display: 'flex', justifyContent: 'space-between', marginBottom: '5px' } },
                        wp.element.createElement('span', null, item.name),
                        wp.element.createElement('span', null, `${item.load} / ${item.capacity} mins (${item.percent}%)`)
                    ),
                    wp.element.createElement('div', { style: { height: '10px', background: '#eee', borderRadius: '5px', overflow: 'hidden' } },
                        wp.element.createElement('div', { style: { height: '100%', width: `${Math.min(item.percent, 100)}%`, background: item.percent > 90 ? '#d63638' : '#2271b1' } })
                    )
                )) : wp.element.createElement('p', null, 'No equipment data available.')
            ),
            wp.element.createElement('div', { style: { padding: '20px', background: '#fff', border: '1px solid #ccc' } },
                wp.element.createElement('h3', null, 'Cost Variance (Est vs Actual)'),
                kpis.cost_variance && kpis.cost_variance.length > 0 ?
                    wp.element.createElement('table', { style: { width: '100%', fontSize: '12px', borderCollapse: 'collapse' } },
                        wp.element.createElement('thead', null,
                            wp.element.createElement('tr', null,
                                wp.element.createElement('th', { style: { textAlign: 'left' } }, 'WO'),
                                wp.element.createElement('th', { style: { textAlign: 'left' } }, 'Est'),
                                wp.element.createElement('th', { style: { textAlign: 'left' } }, 'Act'),
                                wp.element.createElement('th', { style: { textAlign: 'left' } }, 'Var')
                            )
                        ),
                        wp.element.createElement('tbody', null,
                            kpis.cost_variance.map((v, i) => wp.element.createElement('tr', { key: i },
                                wp.element.createElement('td', null, `#${v.wo_id}`),
                                wp.element.createElement('td', null, `$${v.estimated}`),
                                wp.element.createElement('td', null, `$${v.actual}`),
                                wp.element.createElement('td', { style: { color: v.variance > 0 ? '#d63638' : '#46b450', fontWeight: 'bold' } },
                                    (v.variance > 0 ? '+' : '') + `$${v.variance}`
                                )
                            ))
                        )
                    ) : wp.element.createElement('p', null, 'No production history yet for cost variance analysis.')
            )
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-dashboard-root');
    if (container) {
        wp.element.render(wp.element.createElement(Dashboard, null), container);
    }
});
