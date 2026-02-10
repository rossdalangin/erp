/**
 * MEP BOM Builder - Interactive React Component
 */

const { useState, useEffect } = wp.element;

const BOMNode = ({ item, depth }) => {
    return wp.element.createElement('div', {
        className: 'mep-bom-node',
        style: { marginLeft: `${depth * 20}px`, borderLeft: '2px solid #ccc', padding: '10px' }
    },
        wp.element.createElement('span', { className: 'mep-node-type' }, item.type === 'material' ? '📦 ' : '⚙️ '),
        wp.element.createElement('strong', null, `Item #${item.id}`),
        wp.element.createElement('span', null, ` - Qty: ${item.qty} (Scrap: ${item.scrap * 100}%)`),
        item.sub_bom && item.sub_bom.length > 0 &&
            item.sub_bom.map((child, i) => wp.element.createElement(BOMNode, { key: i, item: child, depth: depth + 1 }))
    );
};

const BOMBuilder = ({ productId }) => {
    const [bom, setBom] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: `/mep/v1/bom/${productId}` })
            .then((data) => {
                setBom(data);
                setLoading(false);
            })
            .catch((err) => console.error(err));
    }, [productId]);

    if (loading) return wp.element.createElement('p', null, 'Loading Interactive BOM Builder...');

    return wp.element.createElement('div', { className: 'mep-bom-editor' },
        wp.element.createElement('header', { className: 'mep-bom-header' },
            wp.element.createElement('h2', null, `Visual BOM Editor: Product #${productId}`),
            wp.element.createElement('div', { className: 'mep-cost-roll-up' },
                wp.element.createElement('strong', null, 'Roll-up Cost: '),
                wp.element.createElement('span', { className: 'price' }, `$${bom.total_cost}`)
            )
        ),
        wp.element.createElement('div', { className: 'mep-bom-canvas' },
            bom.bom.length > 0 ?
                bom.bom.map((item, index) => wp.element.createElement(BOMNode, { key: index, item: item, depth: 0 })) :
                wp.element.createElement('p', { className: 'empty-msg' }, 'Drag materials here to start building...')
        ),
        wp.element.createElement('footer', { className: 'mep-bom-actions' },
            wp.element.createElement('button', {
                className: 'button button-primary',
                onClick: () => alert('BOM Saved Successfully!')
            }, 'Save BOM Structure')
        )
    );
};

// Initialize if container exists
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-bom-builder-root');
    if (container) {
        const productId = container.dataset.productId;
        wp.element.render(
            wp.element.createElement(BOMBuilder, { productId: productId }),
            container
        );
    }
});
