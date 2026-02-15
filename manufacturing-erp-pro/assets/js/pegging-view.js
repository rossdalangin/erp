(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const PeggingNode = ({ label, children }) => {
    return wp.element.createElement('div', {
        className: 'mep-pegging-node',
        style: { marginLeft: '20px', borderLeft: '1px solid var(--mep-border)', padding: '5px 15px' }
    },
        wp.element.createElement('span', { style: { fontSize: '13px' } }, label),
        (children || []).length > 0 && children.map((child, i) => wp.element.createElement(PeggingNode, { key: i, label: child.label, children: child.children }))
    );
};

const PeggingView = () => {
    const [peggingData, setPeggingData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/mrp/pegging' })
            .then(data => {
                setPeggingData(data || []);
                setLoading(false);
            })
            .catch(err => {
                setError(__('Failed to load pegging data.', 'manufacturing-erp-pro'));
                setLoading(false);
            });
    }, []);

    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));
    if (loading) return wp.element.createElement('p', null, __('Calculating demand chain...', 'manufacturing-erp-pro'));

    return wp.element.createElement('div', { className: 'mep-pegging-container mep-admin-style mep-animate-fade-in' },
        wp.element.createElement('h3', { style: { marginBottom: '20px' } }, '🌳 ' + __('Demand Pegging (Forecast → Materials)', 'manufacturing-erp-pro')),
        wp.element.createElement('div', { className: 'mep-wo-card', style: { padding: '30px' } },
            peggingData.length > 0 ?
                peggingData.map((node, i) => wp.element.createElement(PeggingNode, { key: i, label: node.label, children: node.children })) :
                wp.element.createElement('p', { style: { textAlign: 'center', color: '#94a3b8' } }, __('No active forecasts found.', 'manufacturing-erp-pro'))
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-pegging-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(null), container).render(wp.element.createElement(PeggingView); } else { wp.element.render(wp.element.createElement(PeggingView, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
