(function() {
const { useState, useEffect } = wp.element;
const { __, sprintf } = wp.i18n;

const SuggestionItem = ({ item, onDragStart }) => {
    return wp.element.createElement('div', {
        className: 'mep-suggestion-item',
        draggable: true,
        onDragStart: (e) => onDragStart(e, item),
        style: { padding: '10px', border: '1px solid #ccc', background: '#fff', marginBottom: '10px', cursor: 'grab' }
    },
        wp.element.createElement('strong', null, `Mat #${item.material_id}`),
        wp.element.createElement('p', null, `Needed: ${item.needed} units`),
        wp.element.createElement('small', { style: { background: '#eee', padding: '2px 5px' } }, item.type)
    );
};

const MRPSuggestions = () => {
    const [status, setStatus] = useState('idle');
    const [suggestions, setSuggestions] = useState([]);
    const [basket, setBasket] = useState([]);
    const [loading, setLoading] = useState(true);

    const fetchStatus = () => {
        wp.apiFetch({ path: '/mep/v1/mrp/status' })
            .then(data => {
                setStatus(data.status);
                setSuggestions(data.results || []);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    };

    useEffect(() => { fetchStatus(); }, []);

    const onDrop = (e) => {
        const itemStr = e.dataTransfer.getData('suggestion');
        if (!itemStr) return;
        const item = JSON.parse(itemStr);
        if (!basket.find(i => i.material_id === item.material_id)) {
            setBasket([...basket, item]);
            setSuggestions(suggestions.filter(i => i.material_id !== item.material_id));
        }
    };

    if (loading && status !== 'processing') return wp.element.createElement('p', null, __('Loading MRP Planning...', 'manufacturing-erp-pro'));

    return wp.element.createElement('div', { className: 'mep-mrp-planner' },
        wp.element.createElement('header', { style: { marginBottom: '20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', background: '#fff', padding: '15px', border: '1px solid #ccc' } },
            wp.element.createElement('div', null,
                wp.element.createElement('strong', null, __('Engine Status: ', 'manufacturing-erp-pro')),
                wp.element.createElement('span', { style: { color: status === 'processing' ? '#dba617' : '#46b450' } }, status.toUpperCase())
            ),
            wp.element.createElement('button', { className: 'button button-primary', onClick: () => { setLoading(true); wp.apiFetch({ path: '/mep/v1/mrp/run', method: 'POST' }).then(fetchStatus); } }, __('Run MRP Engine', 'manufacturing-erp-pro'))
        ),

        wp.element.createElement('div', { style: { display: 'flex', gap: '30px' } },
            wp.element.createElement('div', { style: { flex: 1, background: '#f9f9f9', padding: '20px', border: '1px solid #eee' } },
                wp.element.createElement('h3', null, __('MRP Suggestions', 'manufacturing-erp-pro')),
                (suggestions || []).length > 0 ? suggestions.map((item, i) => wp.element.createElement(SuggestionItem, { key: i, item, onDragStart: (e, it) => e.dataTransfer.setData('suggestion', JSON.stringify(it)) })) : wp.element.createElement('p', null, __('No shortages detected.', 'manufacturing-erp-pro'))
            ),
            wp.element.createElement('div', {
                onDragOver: (e) => e.preventDefault(),
                onDrop: onDrop,
                style: { flex: 1, background: '#f0f6fb', border: '2px dashed #2271b1', padding: '20px', minHeight: '400px' }
            },
                wp.element.createElement('h3', null, __('Draft PO Basket', 'manufacturing-erp-pro')),
                (basket || []).map((item, i) => wp.element.createElement('div', { key: i, style: { padding: '5px', borderBottom: '1px solid #ddd' } }, `Mat #${item.material_id} - ${item.needed} units`)),
                basket.length > 0 && wp.element.createElement('button', { className: 'button button-primary', style: { marginTop: '20px' }, onClick: () => wp.apiFetch({ path: '/mep/v1/procurement/po-from-items', method: 'POST', data: { items: basket } }).then(() => setBasket([])) }, __('Generate Purchase Orders', 'manufacturing-erp-pro'))
            )
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-mrp-suggestions-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(container).render(wp.element.createElement(MRPSuggestions, null)); }
        else { wp.element.render(wp.element.createElement(MRPSuggestions, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
