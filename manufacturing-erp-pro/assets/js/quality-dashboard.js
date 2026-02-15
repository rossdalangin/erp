(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const LoadingUI = ({ message = __('Loading...', 'manufacturing-erp-pro') }) => (
    wp.element.createElement('div', { className: 'mep-loading-container' },
        wp.element.createElement('div', { className: 'mep-spinner' }),
        wp.element.createElement('p', null, message)
    )
);

const QualityDashboard = () => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/reports/quality' }).then(res => { setData(res); setLoading(false); }).catch(() => setLoading(false));
    }, []);

    if (loading) return wp.element.createElement(LoadingUI, { message: __('Loading Quality...', 'manufacturing-erp-pro') });
    if (!data) return wp.element.createElement('p', null, __('No data.', 'manufacturing-erp-pro'));

    return wp.element.createElement('div', { className: 'mep-card' },
        wp.element.createElement('h2', null, __('Quality Analytics', 'manufacturing-erp-pro')),
        wp.element.createElement('div', { className: 'mep-kpi-grid' },
            wp.element.createElement('div', { className: 'mep-kpi-card' }, wp.element.createElement('h3', null, __('Pass Rate'), wp.element.createElement('p', { className: 'mep-kpi-value' }, data.pass_rate))),
            wp.element.createElement('div', { className: 'mep-kpi-card' }, wp.element.createElement('h3', null, __('Active NCRs'), wp.element.createElement('p', { className: 'mep-kpi-value', style: { color: 'var(--mep-danger)' } }, data.active_ncrs)))
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-quality-dashboard-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(container).render(wp.element.createElement(QualityDashboard, null)); }
        else { wp.element.render(wp.element.createElement(QualityDashboard, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
