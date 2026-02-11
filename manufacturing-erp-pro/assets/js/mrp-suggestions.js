/**
 * MEP MRP Suggestions - Interactive Procurement Planning
 */

const { useState, useEffect } = wp.element;

const SuggestionItem = ({ item, onDragStart }) => {
    return wp.element.createElement('div', {
        className: 'mep-suggestion-item',
        draggable: true,
        onDragStart: (e) => onDragStart(e, item),
        style: { padding: '10px', border: '1px solid #ccc', background: '#fff', marginBottom: '10px', cursor: 'grab' }
    },
        wp.element.createElement('strong', null, `Material #${item.material_id}`),
        wp.element.createElement('p', null, `Needed: ${item.needed} units`),
        wp.element.createElement('small', null, `Type: ${item.type}`)
    );
};

const MRPSuggestions = () => {
    const [status, setStatus] = useState('idle');
    const [lastRun, setLastRun] = useState('');
    const [suggestions, setSuggestions] = useState([]);
    const [basket, setBasket] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    const fetchStatus = () => {
        wp.apiFetch({ path: '/mep/v1/mrp/status' })
            .then(data => {
                setStatus(data.status);
                setLastRun(data.last_run);
                setSuggestions(data.results);
                setLoading(false);
            })
            .catch(err => {
                setError('Failed to fetch MRP Status.');
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
            .then(() => fetchStatus());
    };

    const onDragStart = (e, item) => {
        e.dataTransfer.setData('suggestion', JSON.stringify(item));
    };

    const onDragOver = (e) => {
        e.preventDefault();
    };

    const onDrop = (e) => {
        const item = JSON.parse(e.dataTransfer.getData('suggestion'));
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
            alert(`Successfully created ${data.po_ids.length} Purchase Orders!`);
            setBasket([]);
        });
    };

    if (loading && status !== 'processing') return wp.element.createElement('p', null, 'Loading MRP Planning...');
    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));

    return wp.element.createElement('div', { className: 'mep-mrp-planner' },
        wp.element.createElement('header', { style: { marginBottom: '20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', background: '#fff', padding: '15px', border: '1px solid #ccc' } },
            wp.element.createElement('div', null,
                wp.element.createElement('strong', null, 'MRP Engine Status: '),
                wp.element.createElement('span', { style: { color: status === 'processing' ? '#dba617' : '#46b450', fontWeight: 'bold' } }, status.toUpperCase()),
                wp.element.createElement('span', { style: { marginLeft: '20px', color: '#666', fontSize: '12px' } }, `Last Run: ${lastRun}`)
            ),
            wp.element.createElement('button', {
                className: 'button button-primary',
                onClick: runMRP,
                disabled: status === 'processing'
            }, status === 'processing' ? 'Calculating...' : 'Recalculate MRP Results')
        ),

        status === 'processing' && wp.element.createElement('div', { className: 'notice notice-info' }, wp.element.createElement('p', null, 'The MRP engine is exploding BOMs and netting inventory in the background. Results will refresh automatically.')),

        wp.element.createElement('div', { style: { display: 'flex', gap: '30px' } },
        wp.element.createElement('div', {
            className: 'mep-suggestions-list',
            style: { flex: 1 },
            title: helpMode ? 'Suggestions: These are items the MRP engine thinks you should buy based on demand.' : ''
        },
            wp.element.createElement('h2', null, 'MRP Suggestions'),
            suggestions.length > 0 ?
                suggestions.map((item, i) => wp.element.createElement(SuggestionItem, { key: i, item: item, onDragStart })) :
                wp.element.createElement('p', null, 'No suggestions found. Your inventory levels meet current demand.')
        ),
        wp.element.createElement('div', {
            className: 'mep-po-basket',
            onDragOver: onDragOver,
            onDrop: onDrop,
            style: { flex: 1, background: '#f6f7f7', border: '2px dashed #ccc', padding: '20px', minHeight: '400px' },
            title: helpMode ? 'Basket: Drag suggestions here to prepare them for Purchase Order generation.' : ''
        },
            wp.element.createElement('h2', null, 'Draft PO Basket'),
            basket.map((item, i) => wp.element.createElement('div', { key: i, style: { padding: '5px', borderBottom: '1px solid #ddd' } },
                `Material #${item.material_id} - ${item.needed} units`
            )),
            basket.length > 0 && wp.element.createElement('button', {
                className: 'button button-primary',
                style: { marginTop: '20px' },
                onClick: createPOs
            }, 'Generate Purchase Orders')
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-mrp-suggestions-root');
    if (container) {
        wp.element.render(wp.element.createElement(MRPSuggestions, null), container);
    }
});
