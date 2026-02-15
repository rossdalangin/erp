(function() {
/**
 * MEP Pegging View - Demand to Material Chain Visualization
 */

const { useState, useEffect } = wp.element;

const PeggingNode = ({ label, children }) => {
    return wp.element.createElement('div', {
        className: 'mep-pegging-node',
        style: { marginLeft: '20px', borderLeft: '1px solid #ccc', padding: '5px 10px' }
    },
        wp.element.createElement('span', null, label),
        children && children.length > 0 && children.map((child, i) => wp.element.createElement(PeggingNode, { key: i, label: child.label, children: child.children }))
    );
};

const PeggingView = () => {
    const [peggingData, setPeggingData] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/mrp/pegging' })
            .then(data => {
                setPeggingData(data);
                setLoading(false);
            })
            .catch(err => {
                console.error(err);
                setLoading(false);
            });
    }, []);

    if (loading) return wp.element.createElement('p', null, 'Calculating pegging relationships...');

    return wp.element.createElement('div', { className: 'mep-pegging-container' },
        wp.element.createElement('h2', null, 'Demand Pegging View (Demand → BOM → Materials)'),
        wp.element.createElement('div', { style: { background: '#fff', padding: '20px', border: '1px solid #ccc' } },
            (peggingData || []).map((node, i) => wp.element.createElement(PeggingNode, { key: i, label: node.label, children: node.children }))
        )
    );
};


const init = () => {
    const container = document.getElementById('mep-pegging-root');
    if (container) {
        wp.element.render(wp.element.createElement(PeggingView, null), container);
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}

})();