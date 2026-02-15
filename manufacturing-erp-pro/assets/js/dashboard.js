(function() {
/**
 * MEP Executive Dashboard - v2.0
 */

const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const LoadingUI = ({ message = __('Loading...', 'manufacturing-erp-pro') }) => (
    wp.element.createElement('div', { className: 'mep-loading-container' },
        wp.element.createElement('div', { className: 'mep-spinner' }),
        wp.element.createElement('p', null, message)
    )
);

const KPICard = ({ label, value, color, loading }) => {
    return wp.element.createElement('div', {
        className: 'mep-kpi-card',
        style: { borderTopColor: color }
    },
        wp.element.createElement('span', { className: 'mep-kpi-label' }, label),
        loading ? wp.element.createElement('span', { className: 'spinner is-active', style: { float: 'none' } }) :
                  wp.element.createElement('span', { className: 'mep-kpi-value' }, value)
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

    return wp.element.createElement('div', { className: 'mep-dashboard-container' },
        (!loading && kpis && kpis.production_output === 0) && wp.element.createElement('div', {
            className: 'mep-welcome-banner'
        },
            wp.element.createElement('h2', { style: { marginTop: 0 } }, '✨ ' + __('Welcome! Let\'s build your factory.', 'manufacturing-erp-pro')),
            wp.element.createElement('div', { className: 'mep-step-links' },
                [
                    { title: __('1. Materials', 'manufacturing-erp-pro'), link: 'edit.php?post_type=mep_material' },
                    { title: __('2. Products', 'manufacturing-erp-pro'), link: 'edit.php?post_type=mep_product' },
                    { title: __('3. Build BOM', 'manufacturing-erp-pro'), link: 'admin.php?page=mep-bom-builder' },
                    { title: __('4. Release WO', 'manufacturing-erp-pro'), link: 'admin.php?page=mep-production' }
                ].map((s, i) => wp.element.createElement('a', {
                    key: i, href: s.link,
                    className: 'mep-step-link'
                },
                    wp.element.createElement('strong', { style: { display: 'block', color: '#2271b1' } }, s.title)
                ))
            )
        ),

        wp.element.createElement('div', {
            className: 'mep-kpi-grid'
        },
            wp.element.createElement(KPICard, { label: __('Monthly Output', 'manufacturing-erp-pro'), value: kpis ? kpis.production_output : '0', color: '#2271b1', loading }),
            wp.element.createElement(KPICard, { label: __('Scrap Rate', 'manufacturing-erp-pro'), value: kpis ? kpis.scrap_rate : '0%', color: '#d63638', loading }),
            wp.element.createElement(KPICard, { label: __('Inventory Value', 'manufacturing-erp-pro'), value: kpis ? kpis.inventory_valuation : '$0.00', color: '#dba617', loading }),
            wp.element.createElement(KPICard, { label: __('At-Risk Items', 'manufacturing-erp-pro'), value: kpis ? kpis.at_risk_materials : '0', color: '#46b450', loading })
        ),

        wp.element.createElement('div', { className: 'mep-dashboard-grid', style: { display: 'grid', gridTemplateColumns: '1.5fr 1fr', gap: '30px' } },
            wp.element.createElement('div', { className: 'mep-card' },
                wp.element.createElement('h3', { style: { marginTop: 0 } }, __('Resource Utilization', 'manufacturing-erp-pro')),
                (capacity || []).length > 0 ? capacity.map(item => wp.element.createElement('div', { key: item.id, className: 'mep-resource-item' },
                    wp.element.createElement('div', { className: 'mep-resource-header' },
                        wp.element.createElement('span', null, item.name),
                        wp.element.createElement('span', { style: { fontWeight: 'bold' } }, `${item.percent}%`)
                    ),
                    wp.element.createElement('div', { className: 'mep-progress-bg' },
                        wp.element.createElement('div', { className: 'mep-progress-fill', style: { width: `${Math.min(item.percent, 100)}%` } })
                    )
                )) : wp.element.createElement('p', null, __('No active machines.', 'manufacturing-erp-pro'))
            ),
            wp.element.createElement('div', { className: 'mep-dark-card' },
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
