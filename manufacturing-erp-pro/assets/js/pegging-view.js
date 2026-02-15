(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const LoadingUI = ({ message = __('Loading...', 'manufacturing-erp-pro') }) => (
    wp.element.createElement('div', { className: 'mep-loading-container' },
        wp.element.createElement('div', { className: 'mep-spinner' }),
        wp.element.createElement('p', null, message)
    )
);

const PeggingNode = ({ label, children, level = 0 }) => {
    return wp.element.createElement('div', {
        className: 'mep-pegging-node',
        style: {
            marginLeft: level > 0 ? '30px' : '0',
            borderLeft: level > 0 ? '2px solid #e2e8f0' : 'none',
            padding: '12px 15px',
            marginBottom: '8px',
            background: level === 0 ? '#fff' : 'transparent',
            borderRadius: '6px',
            border: level === 0 ? '1px solid #cbd5e1' : 'none'
        }
    },
        wp.element.createElement('div', { style: { display: 'flex', alignItems: 'center', gap: '8px' } },
            wp.element.createElement('span', { style: { color: level === 0 ? 'var(--mep-primary)' : 'inherit', fontWeight: level === 0 ? 'bold' : 'normal' } }, (level > 0 ? '↳ ' : '📦 ') + label)
        ),
        (children || []).length > 0 && children.map((child, i) => wp.element.createElement(PeggingNode, { key: i, label: child.label, children: child.children, level: level + 1 }))
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

    if (loading) return wp.element.createElement(LoadingUI, { message: __('Calculating pegging relationships...', 'manufacturing-erp-pro') });

    return wp.element.createElement('div', { className: 'mep-module-container' },
        wp.element.createElement('div', { className: 'mep-card' },
            wp.element.createElement('h2', { className: 'mep-card-title' }, __('Demand Pegging View', 'manufacturing-erp-pro')),
            wp.element.createElement('p', { style: { color: '#64748b', marginBottom: '20px' } }, __('Visualize how customer demand flows down to individual raw materials.', 'manufacturing-erp-pro')),
            (peggingData || []).map((node, i) => wp.element.createElement(PeggingNode, { key: i, label: node.label, children: node.children }))
        )
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