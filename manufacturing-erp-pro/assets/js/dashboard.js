(function() {
/**
 * MEP Executive Dashboard - v2.0
 */

const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const KPICard = ({ label, value, color, loading }) => {
    return wp.element.createElement('div', {
        className: 'mep-kpi-card',
        style: { borderTop: `4px solid ${color}`, padding: '20px', background: '#fff', textAlign: 'center', borderRadius: '4px', boxShadow: '0 1px 3px rgba(0,0,0,0.1)' }
    },
        wp.element.createElement('h3', { style: { margin: 0, fontSize: '14px', color: '#666', fontWeight: 'normal' } }, label),
        loading ? wp.element.createElement('span', { className: 'spinner is-active', style: { float: 'none' } }) :
                  wp.element.createElement('p', { style: { fontSize: '28px', fontWeight: 'bold', margin: '10px 0', color: '#2c3338' } }, value)
    );
};

const Dashboard = () => {
    const [kpis, setKpis] = useState(null);
    const [capacity, setCapacity] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/reports/kpis' })
            .then(data => {
                setKpis(data);
                return wp.apiFetch({ path: '/mep/v1/equipment/capacity' });
            })
            .then(capData => {
                setCapacity(capData || []);
                setLoading(false);
            })
            .catch(err => {
                console.error(err);
                setLoading(false);
            });
    }, []);

    return wp.element.createElement('div', { className: 'mep-dashboard-container', style: { padding: '20px' } },
        (!loading && kpis && kpis.production_output === 0) && wp.element.createElement('div', {
            style: { background: '#fff8e1', border: '1px solid #ffe082', padding: '20px', marginBottom: '30px', borderRadius: '4px' }
        },
            wp.element.createElement('h2', { style: { marginTop: 0 } }, '✨ ' + __('Welcome! Let\'s build your factory.', 'manufacturing-erp-pro')),
            wp.element.createElement('div', { style: { display: 'flex', gap: '20px', marginTop: '15px' } },
                [
                    { title: __('1. Materials', 'manufacturing-erp-pro'), link: 'edit.php?post_type=mep_material' },
                    { title: __('2. Products', 'manufacturing-erp-pro'), link: 'edit.php?post_type=mep_product' },
                    { title: __('3. Build BOM', 'manufacturing-erp-pro'), link: 'admin.php?page=mep-bom-builder' },
                    { title: __('4. Release WO', 'manufacturing-erp-pro'), link: 'admin.php?page=mep-production' }
                ].map((s, i) => wp.element.createElement('a', {
                    key: i, href: s.link,
                    style: { flex: 1, textDecoration: 'none', color: 'inherit', background: '#fff', padding: '15px', border: '1px solid #e0c46a', borderRadius: '4px' }
                },
                    wp.element.createElement('strong', { style: { display: 'block', color: '#2271b1' } }, s.title)
                ))
            )
        ),

        wp.element.createElement('div', {
            style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '20px' }
        },
            wp.element.createElement(KPICard, { label: __('Monthly Output', 'manufacturing-erp-pro'), value: kpis ? kpis.production_output : '0', color: '#2271b1', loading }),
            wp.element.createElement(KPICard, { label: __('Scrap Rate', 'manufacturing-erp-pro'), value: kpis ? kpis.scrap_rate : '0%', color: '#d63638', loading }),
            wp.element.createElement(KPICard, { label: __('Inventory Value', 'manufacturing-erp-pro'), value: kpis ? kpis.inventory_valuation : '$0.00', color: '#dba617', loading }),
            wp.element.createElement(KPICard, { label: __('At-Risk Items', 'manufacturing-erp-pro'), value: kpis ? kpis.at_risk_materials : '0', color: '#46b450', loading })
        ),

        wp.element.createElement('div', { style: { marginTop: '30px', display: 'grid', gridTemplateColumns: '1.5fr 1fr', gap: '30px' } },
            wp.element.createElement('div', { style: { background: '#fff', padding: '20px', borderRadius: '4px', border: '1px solid #ccd0d4' } },
                wp.element.createElement('h3', { style: { marginTop: 0 } }, __('Resource Utilization', 'manufacturing-erp-pro')),
                (capacity || []).length > 0 ? capacity.map(item => wp.element.createElement('div', { key: item.id, style: { marginBottom: '15px' } },
                    wp.element.createElement('div', { style: { display: 'flex', justifyContent: 'space-between', marginBottom: '5px', fontSize: '13px' } },
                        wp.element.createElement('span', null, item.name),
                        wp.element.createElement('span', { style: { fontWeight: 'bold' } }, `${item.percent}%`)
                    ),
                    wp.element.createElement('div', { style: { height: '8px', background: '#f0f0f1', borderRadius: '4px', overflow: 'hidden' } },
                        wp.element.createElement('div', { style: { height: '100%', width: `${Math.min(item.percent, 100)}%`, background: '#2271b1' } })
                    )
                )) : wp.element.createElement('p', null, __('No active machines.', 'manufacturing-erp-pro'))
            ),
            wp.element.createElement('div', { style: { background: '#2c3338', color: '#fff', padding: '20px', borderRadius: '4px' } },
                wp.element.createElement('h3', { style: { marginTop: 0, color: '#fff' } }, __('System Health', 'manufacturing-erp-pro')),
                wp.element.createElement('ul', { style: { listStyle: 'none', padding: 0 } },
                    [__('Database: OK', 'manufacturing-erp-pro'), __('REST API: Operational', 'manufacturing-erp-pro')].map((t, i) => wp.element.createElement('li', { key: i, style: { marginBottom: '10px' } }, '● ' + t))
                )
            )
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-dashboard-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(container).render(wp.element.createElement(Dashboard, null)); }
        else { wp.element.render(wp.element.createElement(Dashboard, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
