(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const PeggingNode = ({ label, children }) => {
    return wp.element.createElement('div', {
        className: 'mep-pegging-node',
        style: { marginLeft: '20px', borderLeft: '1px solid #ccc', padding: '5px 10px' }
    },
        wp.element.createElement('span', null, label),
        (children || []).length > 0 && children.map((child, i) => wp.element.createElement(PeggingNode, { key: i, label: child.label, children: child.children }))
    );
};

const PeggingView = () => {
    const [peggingData, setPeggingData] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/mrp/pegging' })
            .then(data => { setPeggingData(data || []); setLoading(false); })
            .catch(err => setLoading(false));
    }, []);

    if (loading) return wp.element.createElement('p', null, __('Calculating pegging relationships...', 'manufacturing-erp-pro'));

    return wp.element.createElement('div', { className: 'mep-pegging-container', style: { background: '#fff', padding: '20px', border: '1px solid #ccc' } },
        wp.element.createElement('h2', null, __('Demand Pegging View', 'manufacturing-erp-pro')),
        (peggingData || []).map((node, i) => wp.element.createElement(PeggingNode, { key: i, label: node.label, children: node.children }))
    );
};

const init = () => {
    const container = document.getElementById('mep-pegging-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(container).render(wp.element.createElement(PeggingView, null)); }
        else { wp.element.render(wp.element.createElement(PeggingView, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
