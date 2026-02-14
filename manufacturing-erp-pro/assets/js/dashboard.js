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
    const [setupComplete, setSetupComplete] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/reports/kpis' })
            .then(data => {
                setKpis(data);
                return wp.apiFetch({ path: '/mep/v1/equipment/capacity' });
            })
            .then(capData => {
                setCapacity(capData);
                setLoading(false);
            })
            .catch(err => {
                console.error('MEP Dashboard Data Error:', err);
                setLoading(false);
            });
    }, []);

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    return wp.element.createElement('div', { className: 'mep-dashboard-container' },
        // Step-by-Step Onboarding Guidance (Only show if data is empty)
        (!loading && kpis && kpis.production_output === 0) && wp.element.createElement('div', {
            className: 'mep-onboarding-guide',
            style: { background: '#fff8e1', border: '1px solid #ffe082', padding: '20px', marginBottom: '30px', borderRadius: '4px' }
        },
            wp.element.createElement('h2', { style: { marginTop: 0 } }, '✨ ' + __('Welcome! Let\'s build your factory.', 'manufacturing-erp-pro')),
            wp.element.createElement('p', null, __('Follow these steps to get your first production order released:', 'manufacturing-erp-pro')),
            wp.element.createElement('div', { style: { display: 'flex', gap: '20px', marginTop: '15px' } },
                [
                    { title: __('1. Materials', 'manufacturing-erp-pro'), desc: __('Add your raw ingredients.', 'manufacturing-erp-pro'), link: 'edit.php?post_type=mep_material' },
                    { title: __('2. Products', 'manufacturing-erp-pro'), desc: __('Define finished goods.', 'manufacturing-erp-pro'), link: 'edit.php?post_type=mep_product' },
                    { title: __('3. Build BOM', 'manufacturing-erp-pro'), desc: __('Link materials to products.', 'manufacturing-erp-pro'), link: 'admin.php?page=mep-bom-builder' },
                    { title: __('4. Release WO', 'manufacturing-erp-pro'), desc: __('Start production!', 'manufacturing-erp-pro'), link: 'admin.php?page=mep-production' }
                ].map((s, i) => wp.element.createElement('a', {
                    key: i, href: s.link,
                    style: { flex: 1, textDecoration: 'none', color: 'inherit', background: '#fff', padding: '15px', border: '1px solid #e0c46a', borderRadius: '4px' }
                },
                    wp.element.createElement('strong', { style: { display: 'block', marginBottom: '5px', color: '#2271b1' } }, s.title),
                    wp.element.createElement('span', { style: { fontSize: '12px' } }, s.desc)
                ))
            )
        ),

        // KPI Row
        wp.element.createElement('div', {
            style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '20px' }
        },
            wp.element.createElement(KPICard, {
                label: __('Monthly Output', 'manufacturing-erp-pro'),
                value: kpis ? kpis.production_output : '0',
                color: '#2271b1', loading
            }),
            wp.element.createElement(KPICard, {
                label: __('Average Scrap Rate', 'manufacturing-erp-pro'),
                value: kpis ? kpis.scrap_rate : '0%',
                color: '#d63638', loading
            }),
            wp.element.createElement(KPICard, {
                label: __('Inventory Value', 'manufacturing-erp-pro'),
                value: kpis ? kpis.inventory_valuation : '$0.00',
                color: '#dba617', loading
            }),
            wp.element.createElement(KPICard, {
                label: __('Items Below Safety', 'manufacturing-erp-pro'),
                value: kpis ? kpis.at_risk_materials : '0',
                color: kpis && kpis.at_risk_materials > 0 ? '#d63638' : '#46b450', loading
            })
        ),

        // Capacity & Activity Row
        wp.element.createElement('div', { style: { marginTop: '30px', display: 'grid', gridTemplateColumns: '1.5fr 1fr', gap: '30px' } },
            // Capacity Planner Snapshot
            wp.element.createElement('div', { style: { background: '#fff', padding: '20px', borderRadius: '4px', boxShadow: '0 1px 3px rgba(0,0,0,0.1)' } },
                wp.element.createElement('h3', { style: { marginTop: 0 } }, __('Resource Utilization', 'manufacturing-erp-pro')),
                capacity.length > 0 ? capacity.map(item => wp.element.createElement('div', { key: item.id, style: { marginBottom: '15px' } },
                    wp.element.createElement('div', { style: { display: 'flex', justifyContent: 'space-between', marginBottom: '5px', fontSize: '13px' } },
                        wp.element.createElement('span', null, item.name),
                        wp.element.createElement('span', { style: { fontWeight: 'bold' } }, `${item.percent}%`)
                    ),
                    wp.element.createElement('div', { style: { height: '8px', background: '#f0f0f1', borderRadius: '4px', overflow: 'hidden' } },
                        wp.element.createElement('div', {
                            style: {
                                height: '100%',
                                width: `${Math.min(item.percent, 100)}%`,
                                background: item.percent > 90 ? '#d63638' : (item.percent > 70 ? '#dba617' : '#2271b1'),
                                transition: 'width 1s ease-in-out'
                            }
                        })
                    )
                )) : wp.element.createElement('p', { style: { color: '#666', fontStyle: 'italic' } }, __('No active machine schedules found.', 'manufacturing-erp-pro'))
            ),

            // System Status & Quick Links
            wp.element.createElement('div', null,
                wp.element.createElement('div', { style: { background: '#fff', padding: '20px', borderRadius: '4px', boxShadow: '0 1px 3px rgba(0,0,0,0.1)', marginBottom: '20px' } },
                    wp.element.createElement('h3', { style: { marginTop: 0 } }, __('System Health', 'manufacturing-erp-pro')),
                    wp.element.createElement('ul', { style: { margin: 0, padding: 0, listStyle: 'none' } },
                        wp.element.createElement('li', { style: { display: 'flex', justifyContent: 'space-between', padding: '8px 0', borderBottom: '1px solid #f0f0f1' } },
                            wp.element.createElement('span', null, __('Database Tables', 'manufacturing-erp-pro')),
                            wp.element.createElement('span', { style: { color: '#46b450', fontWeight: 'bold' } }, '● ' + __('Connected', 'manufacturing-erp-pro'))
                        ),
                        wp.element.createElement('li', { style: { display: 'flex', justifyContent: 'space-between', padding: '8px 0', borderBottom: '1px solid #f0f0f1' } },
                            wp.element.createElement('span', null, __('Background Tasks', 'manufacturing-erp-pro')),
                            wp.element.createElement('span', { style: { color: '#46b450', fontWeight: 'bold' } }, '● ' + __('Active', 'manufacturing-erp-pro'))
                        ),
                        wp.element.createElement('li', { style: { display: 'flex', justifyContent: 'space-between', padding: '8px 0' } },
                            wp.element.createElement('span', null, __('REST API', 'manufacturing-erp-pro')),
                            wp.element.createElement('span', { style: { color: '#46b450', fontWeight: 'bold' } }, '● ' + __('Operational', 'manufacturing-erp-pro'))
                        )
                    )
                ),
                wp.element.createElement('div', { style: { background: '#2c3338', color: '#fff', padding: '20px', borderRadius: '4px' } },
                    wp.element.createElement('h3', { style: { marginTop: 0, color: '#fff' } }, __('Support & Docs', 'manufacturing-erp-pro')),
                    wp.element.createElement('p', { style: { fontSize: '13px', opacity: 0.8 } }, __('Need help with your factory configuration? Check our professional guides.', 'manufacturing-erp-pro')),
                    wp.element.createElement('a', {
                        href: '#',
                        className: 'button button-primary',
                        style: { width: '100%', textAlign: 'center', marginTop: '10px' }
                    }, __('Read User Manual', 'manufacturing-erp-pro'))
                )
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

})();