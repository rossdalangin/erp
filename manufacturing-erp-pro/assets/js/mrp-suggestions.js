(function() {
const { useState, useEffect } = wp.element;
const { __, sprintf } = wp.i18n;

const LoadingUI = ({ message = __('Loading...', 'manufacturing-erp-pro') }) => (
    wp.element.createElement('div', { className: 'mep-loading-container' },
        wp.element.createElement('div', { className: 'mep-spinner' }),
        wp.element.createElement('p', null, message)
    )
);

const SuggestionItem = ({ item, onDragStart }) => {
    return wp.element.createElement('div', {
        className: 'mep-wo-card',
        draggable: true,
        onDragStart: (e) => onDragStart(e, item),
        style: { cursor: 'grab' }
    },
        wp.element.createElement('strong', { style: { display: 'block', marginBottom: '5px', color: 'var(--mep-primary)' } }, `Material #${item.material_id}`),
        wp.element.createElement('div', { style: { display: 'flex', justifyContent: 'space-between', fontSize: '13px' } },
            wp.element.createElement('span', null, sprintf(__('Needed: %d units', 'manufacturing-erp-pro'), item.needed)),
            wp.element.createElement('span', { className: 'mep-badge mep-badge-warning' }, item.type)
        )
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

    if (loading && status !== 'processing') return wp.element.createElement(LoadingUI, { message: __('Loading MRP Planning...', 'manufacturing-erp-pro') });

    return wp.element.createElement('div', { className: 'mep-module-container' },
        wp.element.createElement('header', { className: 'mep-card', style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '30px' } },
            wp.element.createElement('div', null,
                wp.element.createElement('h2', { style: { margin: 0 } }, __('MRP Engine Status'),
                    wp.element.createElement('span', {
                        className: `mep-badge ${status === 'processing' ? 'mep-badge-warning' : 'mep-badge-success'}`,
                        style: { marginLeft: '15px', verticalAlign: 'middle' }
                    }, status.toUpperCase())
                )
            ),
            wp.element.createElement('button', { className: 'button button-primary', onClick: () => { setLoading(true); wp.apiFetch({ path: '/mep/v1/mrp/run', method: 'POST' }).then(fetchStatus); } }, __('Run Material Requirements Planning', 'manufacturing-erp-pro'))
        ),

        wp.element.createElement('div', { style: { display: 'flex', gap: '30px' } },
            wp.element.createElement('div', { style: { flex: 1 } },
                wp.element.createElement('h3', { className: 'mep-card-title' }, __('Critical Shortages', 'manufacturing-erp-pro')),
                wp.element.createElement('div', { style: { background: '#f8fafc', padding: '20px', borderRadius: '8px', border: '1px solid #e2e8f0', minHeight: '500px' } },
                    (suggestions || []).length > 0 ? suggestions.map((item, i) => wp.element.createElement(SuggestionItem, { key: i, item, onDragStart: (e, it) => e.dataTransfer.setData('suggestion', JSON.stringify(it)) })) : wp.element.createElement('p', { style: { textAlign: 'center', color: '#64748b', marginTop: '40px' } }, __('✅ All material levels are healthy.', 'manufacturing-erp-pro'))
                )
            ),
            wp.element.createElement('div', {
                onDragOver: (e) => e.preventDefault(),
                onDrop: onDrop,
                style: { flex: 1 }
            },
                wp.element.createElement('h3', { className: 'mep-card-title' }, __('Purchase Order Workspace', 'manufacturing-erp-pro')),
                wp.element.createElement('div', { style: { background: '#f0f9ff', border: '2px dashed #0369a1', padding: '20px', borderRadius: '8px', minHeight: '500px', position: 'relative' } },
                    basket.length === 0 && wp.element.createElement('div', { style: { position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)', textAlign: 'center', color: '#0369a1' } },
                        wp.element.createElement('div', { style: { fontSize: '40px', marginBottom: '10px' } }, '🛒'),
                        wp.element.createElement('p', null, __('Drag materials here to create a PO', 'manufacturing-erp-pro'))
                    ),
                    (basket || []).map((item, i) => wp.element.createElement('div', { key: i, className: 'mep-card', style: { padding: '10px 15px', marginBottom: '10px', display: 'flex', justifyContent: 'space-between' } },
                        wp.element.createElement('span', null, `Mat #${item.material_id}`),
                        wp.element.createElement('strong', null, `${item.needed} Units`)
                    )),
                    basket.length > 0 && wp.element.createElement('button', { className: 'button button-primary', style: { width: '100%', marginTop: '20px' }, onClick: () => wp.apiFetch({ path: '/mep/v1/procurement/po-from-items', method: 'POST', data: { items: basket } }).then(() => setBasket([])) }, __('Generate Bulk Purchase Orders', 'manufacturing-erp-pro'))
                )
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