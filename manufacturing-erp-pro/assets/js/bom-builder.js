/**
 * MEP BOM Builder - Interactive React Component with Real DnD
 */

const { useState, useEffect } = wp.element;

const BOMNode = ({ item, depth, onRemove }) => {
    return wp.element.createElement('div', {
        className: 'mep-bom-node',
        style: { marginLeft: `${depth * 20}px`, borderLeft: '2px solid #ccc', padding: '10px', marginBottom: '5px', background: '#f9f9f9' }
    },
        wp.element.createElement('span', { className: 'mep-node-type' }, item.type === 'material' ? '📦 ' : '⚙️ '),
        wp.element.createElement('strong', null, item.name || `Item #${item.id}`),
        wp.element.createElement('span', null, ` - Qty: ${item.qty}`),
        wp.element.createElement('button', {
            className: 'button-link-delete',
            style: { marginLeft: '10px', fontSize: '11px' },
            onClick: () => onRemove(item.id)
        }, 'Remove'),
        item.sub_bom && item.sub_bom.length > 0 &&
            item.sub_bom.map((child, i) => wp.element.createElement(BOMNode, { key: i, item: child, depth: depth + 1, onRemove }))
    );
};

const BOMBuilder = ({ productId }) => {
    const [bom, setBom] = useState({ bom: [], total_cost: 0 });
    const [materials, setMaterials] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: `/mep/v1/bom/${productId}` })
            .then((data) => {
                setBom(data);
                setLoading(false);
            })
            .catch((err) => console.error(err));

        wp.apiFetch({ path: '/mep/v1/materials' })
            .then((data) => setMaterials(data))
            .catch((err) => console.error(err));
    }, [productId]);

    const onDragStart = (e, material) => {
        e.dataTransfer.setData('material', JSON.stringify(material));
    };

    const onDragOver = (e) => {
        e.preventDefault();
    };

    const onDrop = (e) => {
        const material = JSON.parse(e.dataTransfer.getData('material'));
        addMaterial(material);
    };

    const addMaterial = (material) => {
        const newComponent = {
            id: material.id,
            name: material.name,
            type: 'material',
            qty: 1,
            scrap: 0
        };
        setBom(prev => ({ ...prev, bom: [...prev.bom, newComponent] }));
    };

    const removeComponent = (id) => {
        setBom(prev => ({ ...prev, bom: prev.bom.filter(item => item.id !== id) }));
    };

    const saveBom = () => {
        wp.apiFetch({
            path: `/mep/v1/bom/${productId}`,
            method: 'POST',
            data: { components: bom.bom }
        }).then(() => {
            alert('BOM Saved Successfully!');
            wp.apiFetch({ path: `/mep/v1/bom/${productId}` }).then(setBom);
        });
    };

    if (loading) return wp.element.createElement('p', null, 'Loading Interactive BOM Builder...');

    return wp.element.createElement('div', { className: 'mep-bom-editor-layout', style: { display: 'flex', gap: '20px' } },
        // Left Sidebar: Material Library
        wp.element.createElement('div', { className: 'mep-material-library', style: { width: '250px', border: '1px solid #ccc', padding: '10px' } },
            wp.element.createElement('h3', null, 'Material Library'),
            wp.element.createElement('p', { style: { fontSize: '11px', color: '#666' } }, 'Drag materials to the canvas'),
            materials.map(mat => wp.element.createElement('div', {
                key: mat.id,
                className: 'mep-library-item',
                draggable: true,
                onDragStart: (e) => onDragStart(e, mat),
                style: { padding: '8px', border: '1px solid #eee', marginBottom: '5px', cursor: 'grab', background: '#fff' }
            }, mat.name))
        ),
        // Central Canvas
        wp.element.createElement('div', { className: 'mep-bom-main', style: { flex: 1 } },
            wp.element.createElement('header', { className: 'mep-bom-header', style: { marginBottom: '20px' } },
                wp.element.createElement('h2', null, `Visual BOM Editor: Product #${productId}`),
                wp.element.createElement('div', { className: 'mep-cost-roll-up' },
                    wp.element.createElement('strong', null, 'Estimated Roll-up Cost: '),
                    wp.element.createElement('span', { className: 'price', style: { color: '#2271b1', fontSize: '1.2em' } }, `$${bom.total_cost}`)
                )
            ),
            wp.element.createElement('div', {
                className: 'mep-bom-canvas',
                onDragOver: onDragOver,
                onDrop: onDrop,
                style: { minHeight: '300px', border: '2px dashed #ccc', padding: '20px', background: '#fff' }
            },
                bom.bom.length > 0 ?
                    bom.bom.map((item, index) => wp.element.createElement(BOMNode, { key: index, item: item, depth: 0, onRemove: removeComponent })) :
                    wp.element.createElement('p', { className: 'empty-msg' }, 'Drag materials here to start building...')
            ),
            wp.element.createElement('footer', { className: 'mep-bom-actions', style: { marginTop: '20px' } },
                wp.element.createElement('button', { className: 'button button-primary', onClick: saveBom }, 'Save BOM Structure')
            )
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-bom-builder-root');
    if (container) {
        const productId = container.dataset.productId;
        wp.element.render(wp.element.createElement(BOMBuilder, { productId: productId }), container);
    }
});
