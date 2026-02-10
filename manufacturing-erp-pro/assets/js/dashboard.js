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
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/reports/kpis' })
            .then(data => {
                setKpis(data);
                setLoading(false);
            });
    }, []);

    if (loading) return wp.element.createElement('p', null, 'Loading ERP Dashboard...');

    return wp.element.createElement('div', { className: 'mep-dashboard-grid' },
        wp.element.createElement('div', {
            style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '20px' }
        },
            wp.element.createElement(KPICard, { label: 'Production Output', value: kpis.production_output, color: '#2271b1' }),
            wp.element.createElement(KPICard, { label: 'Scrap Rate', value: kpis.scrap_rate, color: '#d63638' }),
            wp.element.createElement(KPICard, { label: 'Inventory Value', value: kpis.inventory_value, color: '#dba617' }),
            wp.element.createElement(KPICard, { label: 'On-Time Delivery', value: kpis.on_time_delivery, color: '#673ab7' })
        ),
        wp.element.createElement('div', { style: { marginTop: '30px', padding: '20px', background: '#fff', border: '1px solid #ccc' } },
            wp.element.createElement('h3', null, 'Active Production Insights'),
            wp.element.createElement('p', null, `Currently tracking ${kpis.active_orders} live work orders on the production floor.`)
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-dashboard-root');
    if (container) {
        wp.element.render(wp.element.createElement(Dashboard, null), container);
    }
});
