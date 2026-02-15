(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

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

    if (loading) return wp.element.createElement('p', null, __('Loading Scores...', 'manufacturing-erp-pro'));

    return wp.element.createElement('div', { style: { background: '#fff', padding: '20px', border: '1px solid #ccc' } },
        wp.element.createElement('h2', null, __('Supplier Performance', 'manufacturing-erp-pro')),
        (suppliers || []).map(s => wp.element.createElement('div', { key: s.id, style: { borderBottom: '1px solid #eee', padding: '10px 0' } },
            wp.element.createElement('strong', null, s.name),
            wp.element.createElement('span', { style: { marginLeft: '20px' } }, `Score: ${s.score}/100`)
        ))
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
