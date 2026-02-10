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
    const [suggestions, setSuggestions] = useState([]);
    const [basket, setBasket] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/mrp/run', method: 'POST' })
            .then(data => {
                setSuggestions(data);
                setLoading(false);
            })
            .catch(err => {
                setError('Failed to run MRP Engine.');
                setLoading(false);
                console.error(err);
            });
    }, []);

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

    if (loading) return wp.element.createElement('p', null, 'Running MRP engine and calculating suggestions...');
    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));

    return wp.element.createElement('div', { className: 'mep-mrp-planner', style: { display: 'flex', gap: '30px' } },
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
