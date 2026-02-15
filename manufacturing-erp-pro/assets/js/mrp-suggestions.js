(function() {
const { useState, useEffect } = wp.element;
const { __, sprintf } = wp.i18n;

const SuggestionItem = ({ item, onDragStart }) => {
    return wp.element.createElement('div', {
        className: 'mep-suggestion-item',
        draggable: true,
        onDragStart: (e) => onDragStart(e, item),
    },
        wp.element.createElement('strong', null, `Mat #${item.material_id}`),
        wp.element.createElement('p', { style: { margin: '5px 0' } }, `Needed: ${item.needed} units`),
        wp.element.createElement('div', { className: 'mep-badge mep-badge-info' }, item.type)
    );
};

const MRPSuggestions = () => {
    const [status, setStatus] = useState('idle');
    const [lastRun, setLastRun] = useState('');
    const [suggestions, setSuggestions] = useState([]);
    const [basket, setBasket] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [isDraggingOverBasket, setIsDraggingOverBasket] = useState(false);

    const fetchStatus = () => {
        wp.apiFetch({ path: '/mep/v1/mrp/status' })
            .then(data => {
                setStatus(data.status || 'idle');
                setLastRun(data.last_run || 'Never');
                setSuggestions(data.results || []);
                setLoading(false);
            })
            .catch(err => {
                setError(__('Failed to fetch MRP Status.', 'manufacturing-erp-pro'));
                setLoading(false);
            });
    };

    useEffect(() => {
        fetchStatus();
    }, []);

    useEffect(() => {
        let interval;
        if (status === 'processing') {
            interval = setInterval(fetchStatus, 3000);
        }
        return () => clearInterval(interval);
    }, [status]);

    const runMRP = () => {
        setLoading(true);
        wp.apiFetch({ path: '/mep/v1/mrp/run', method: 'POST' })
            .then(() => fetchStatus())
            .catch(() => setLoading(false));
    };

    const onDragStart = (e, item) => {
        e.dataTransfer.setData('suggestion', JSON.stringify(item));
    };

    const onDragOver = (e) => e.preventDefault();

    const onDrop = (e) => {
        setIsDraggingOverBasket(false);
        const itemStr = e.dataTransfer.getData('suggestion');
        if (!itemStr) return;
        const item = JSON.parse(itemStr);
        if (!basket.find(i => i.material_id === item.material_id)) {
            setBasket([...basket, item]);
            setSuggestions(suggestions.filter(i => i.material_id !== item.material_id));
        }
    };

    const createPOs = () => {
        if (basket.length === 0) return;
        wp.apiFetch({
            path: '/mep/v1/procurement/po-from-items',
            method: 'POST',
            data: { items: basket }
        }).then(data => {
            alert(sprintf(__('Successfully created %d Purchase Orders!', 'manufacturing-erp-pro'), (data.po_ids || []).length));
            setBasket([]);
        }).catch(err => alert(__('Failed to create POs.', 'manufacturing-erp-pro')));
    };

    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));
    if (loading && status !== 'processing') return wp.element.createElement('p', null, __('Loading MRP suggestions...', 'manufacturing-erp-pro'));

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    return wp.element.createElement('div', { className: 'mep-mrp-planner' },
        wp.element.createElement('header', { style: { marginBottom: '20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', background: '#fff', padding: '20px', borderRadius: '8px', border: '1px solid #cbd5e1' } },
            wp.element.createElement('div', null,
                wp.element.createElement('strong', null, __('Engine Status:', 'manufacturing-erp-pro')),
                wp.element.createElement('span', { style: { marginLeft: '10px', color: status === 'processing' ? 'var(--mep-warning)' : 'var(--mep-success)', fontWeight: 'bold' } }, status.toUpperCase()),
                wp.element.createElement('span', { style: { marginLeft: '20px', color: '#64748b', fontSize: '12px' } }, `${__('Last Run:', 'manufacturing-erp-pro')} ${lastRun}`)
            ),
            wp.element.createElement('button', {
                className: 'button button-primary',
                onClick: runMRP,
                disabled: status === 'processing'
            }, status === 'processing' ? __('Calculating...', 'manufacturing-erp-pro') : __('Run MRP Engine', 'manufacturing-erp-pro'))
        ),

        wp.element.createElement('div', { className: 'mep-column-container mep-admin-style mep-animate-fade-in' },
            wp.element.createElement('div', { className: 'mep-column', title: helpMode ? __('Items the engine thinks you should buy.', 'manufacturing-erp-pro') : '' },
                wp.element.createElement('h3', null, '🔍 ' + __('MRP Suggestions', 'manufacturing-erp-pro')),
                (suggestions || []).length > 0 ?
                    suggestions.map((item, i) => wp.element.createElement(SuggestionItem, { key: i, item, onDragStart })) :
                    wp.element.createElement('p', { style: { color: '#94a3b8', fontStyle: 'italic', padding: '20px', textAlign: 'center' } }, __('No shortages detected.', 'manufacturing-erp-pro'))
            ),
            wp.element.createElement('div', {
                className: `mep-column ${isDraggingOverBasket ? 'is-dragging-over' : ''}`,
                onDragOver: (e) => { e.preventDefault(); setIsDraggingOverBasket(true); },
                onDragLeave: () => setIsDraggingOverBasket(false),
                onDrop: onDrop,
                title: helpMode ? __('Drag suggestions here to prepare POs.', 'manufacturing-erp-pro') : ''
            },
                wp.element.createElement('h3', null, '🛒 ' + __('Draft PO Basket', 'manufacturing-erp-pro')),
                (basket || []).map((item, i) => wp.element.createElement('div', { key: i, className: 'mep-library-item' },
                    `Mat #${item.material_id} - ${item.needed} units`
                )),
                basket.length === 0 && wp.element.createElement('p', { style: { color: '#94a3b8', fontStyle: 'italic', padding: '20px', textAlign: 'center' } }, __('Drag items here...', 'manufacturing-erp-pro')),
                basket.length > 0 && wp.element.createElement('button', {
                    className: 'button button-primary',
                    style: { width: '100%', marginTop: '20px' },
                    onClick: createPOs
                }, __('Generate Purchase Orders', 'manufacturing-erp-pro'))
            )
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-mrp-suggestions-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(null), container).render(wp.element.createElement(MRPSuggestions); } else { wp.element.render(wp.element.createElement(MRPSuggestions, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
