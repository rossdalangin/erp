(function() {
/**
 * MEP Quality & Compliance Dashboard
 */

const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const QualityDashboard = () => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/reports/quality' })
            .then(res => {
                setData(res);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, []);

    if (loading) return wp.element.createElement('p', null, __('Loading Quality Analytics...', 'manufacturing-erp-pro'));
    if (!data) return wp.element.createElement('p', null, __('No quality data available.', 'manufacturing-erp-pro'));

    return wp.element.createElement('div', { className: 'mep-quality-dashboard' },
        // KPI Row
        wp.element.createElement('div', { style: { display: 'flex', gap: '20px', marginBottom: '30px' } },
            wp.element.createElement('div', { className: 'mep-kpi-card', style: { flex: 1, background: '#fff', padding: '20px', border: '1px solid #ccd0d4' } },
                wp.element.createElement('h3', null, __('QC Pass Rate', 'manufacturing-erp-pro')),
                wp.element.createElement('div', { style: { fontSize: '32px', fontWeight: 'bold', color: '#46b450' } }, data.pass_rate)
            ),
            wp.element.createElement('div', { className: 'mep-kpi-card', style: { flex: 1, background: '#fff', padding: '20px', border: '1px solid #ccd0d4' } },
                wp.element.createElement('h3', null, __('Active NCRs', 'manufacturing-erp-pro')),
                wp.element.createElement('div', { style: { fontSize: '32px', fontWeight: 'bold', color: '#d63638' } }, data.active_ncrs)
            ),
            wp.element.createElement('div', { className: 'mep-kpi-card', style: { flex: 1, background: '#fff', padding: '20px', border: '1px solid #ccd0d4' } },
                wp.element.createElement('h3', null, __('Active CAPAs', 'manufacturing-erp-pro')),
                wp.element.createElement('div', { style: { fontSize: '32px', fontWeight: 'bold', color: '#f56e28' } }, data.active_capas)
            ),
            wp.element.createElement('div', { className: 'mep-kpi-card', style: { flex: 1, background: '#fff', padding: '20px', border: '1px solid #ccd0d4' } },
                wp.element.createElement('h3', null, __('Total Inspections', 'manufacturing-erp-pro')),
                wp.element.createElement('div', { style: { fontSize: '32px', fontWeight: 'bold' } }, data.total_checks)
            )
        ),

        // Detail Row
        wp.element.createElement('div', { style: { display: 'flex', gap: '20px' } },
            wp.element.createElement('div', { style: { flex: 1, background: '#fff', padding: '20px', border: '1px solid #ccd0d4' } },
                wp.element.createElement('h3', null, __('Pass vs Fail Distribution', 'manufacturing-erp-pro')),
                wp.element.createElement('div', { style: { height: '20px', background: '#eee', borderRadius: '10px', overflow: 'hidden', display: 'flex', marginTop: '20px' } },
                    wp.element.createElement('div', { style: { width: data.pass_rate, background: '#46b450', height: '100%' } }),
                    wp.element.createElement('div', { style: { flex: 1, background: '#d63638', height: '100%' } })
                ),
                wp.element.createElement('p', { style: { textAlign: 'center', fontSize: '12px', marginTop: '10px' } },
                    `${__('Pass', 'manufacturing-erp-pro')}: ${data.pass_count} | ${__('Fail', 'manufacturing-erp-pro')}: ${data.fail_count}`
                )
            ),
            wp.element.createElement('div', { style: { flex: 1, background: '#fff', padding: '20px', border: '1px solid #ccd0d4' } },
                wp.element.createElement('h3', null, __('Recent Defect Pareto', 'manufacturing-erp-pro')),
                wp.element.createElement('ul', { style: { listStyle: 'disc', paddingLeft: '20px' } },
                    data.defects_log.map((d, i) => wp.element.createElement('li', { key: i }, d))
                ),
                data.defects_log.length === 0 && wp.element.createElement('p', null, __('No defects recorded.', 'manufacturing-erp-pro'))
            )
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-quality-dashboard-root');
    if (container) {
        wp.element.render(wp.element.createElement(QualityDashboard, null), container);
    }
});

})();