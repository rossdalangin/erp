(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const LoadingUI = ({ message = __('Loading...', 'manufacturing-erp-pro') }) => (
    wp.element.createElement('div', { className: 'mep-loading-container' },
        wp.element.createElement('div', { className: 'mep-spinner' }),
        wp.element.createElement('p', null, message)
    )
);

const SupplierScorecard = () => {
    const [suppliers, setSuppliers] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/suppliers' }).then(async data => {
            const enriched = await Promise.all((data || []).map(async s => {
                const score = await wp.apiFetch({ path: `/mep/v1/procurement/supplier-score/${s.id}` });
                return { ...s, ...score };
            }));
            setSuppliers(enriched);
            setLoading(false);
        }).catch(() => setLoading(false));
    }, []);

    if (loading) return wp.element.createElement(LoadingUI, { message: __('Loading Scores...', 'manufacturing-erp-pro') });

    return wp.element.createElement('div', { className: 'mep-module-container' },
        wp.element.createElement('h2', { className: 'mep-card-title' }, __('Supplier Performance Scorecard', 'manufacturing-erp-pro')),
        wp.element.createElement('div', { className: 'mep-kpi-grid' },
            (suppliers || []).map(s => wp.element.createElement('div', { key: s.id, className: 'mep-kpi-card' },
                wp.element.createElement('span', { className: 'mep-kpi-label' }, s.name),
                wp.element.createElement('span', { className: 'mep-kpi-value' }, `${s.score}%`),
                wp.element.createElement('div', { className: 'mep-progress-bg', style: { marginTop: '10px' } },
                    wp.element.createElement('div', { className: 'mep-progress-fill', style: { width: `${s.score}%`, background: s.score > 80 ? 'var(--mep-success)' : (s.score > 50 ? 'var(--mep-warning)' : 'var(--mep-danger)') } })
                )
            ))
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-supplier-scorecard-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(container).render(wp.element.createElement(SupplierScorecard, null)); }
        else { wp.element.render(wp.element.createElement(SupplierScorecard, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();