/**
 * MEP BOM Builder - Interactive React Component with Real DnD
 */

const { useState, useEffect } = wp.element;

const BOMNode = ({ item, index, depth, onRemove, onMarkSubstitute, onUpdate, onReorder }) => {
    const isOp = item.type === 'operation';
    const icon = item.type === 'material' ? '📦 ' : (isOp ? '⚡ ' : '⚙️ ');

    return wp.element.createElement('div', {
        className: 'mep-bom-node',
        draggable: true,
        onDragStart: (e) => {
            e.stopPropagation();
            e.dataTransfer.setData('reorderIndex', index);
        },
        onDragOver: (e) => {
            e.preventDefault();
            e.stopPropagation();
        },
        onDrop: (e) => {
            e.stopPropagation();
            const fromIndex = e.dataTransfer.getData('reorderIndex');
            if (fromIndex !== "") {
                onReorder(parseInt(fromIndex), index);
            }
        },
        style: { marginLeft: `${depth * 20}px`, borderLeft: '2px solid #ccc', padding: '10px', marginBottom: '5px', background: isOp ? '#f0f8ff' : '#f9f9f9', cursor: 'move' }
    },
        wp.element.createElement('span', { className: 'mep-node-type' }, icon),
        wp.element.createElement('strong', null, item.name || `Item #${item.id}`),

        // Editable Qty/Time
        wp.element.createElement('span', {
            style: { cursor: 'pointer', marginLeft: '10px', borderBottom: '1px dashed #999' },
            onClick: () => {
                const newVal = prompt(`Enter ${isOp ? 'Time (mins)' : 'Quantity'}:`, item.qty);
                if (newVal !== null) onUpdate(item.id, { qty: parseFloat(newVal) });
            }
        }, ` ${isOp ? 'Time' : 'Qty'}: ${item.qty}`),

        // Editable Scrap/Yield
        wp.element.createElement('span', {
            style: { cursor: 'pointer', marginLeft: '10px', borderBottom: '1px dashed #999', fontSize: '11px' },
            onClick: () => {
                const newVal = prompt(`Enter ${isOp ? 'Yield Loss %' : 'Scrap %'}:`, (item.scrap || 0) * 100);
                if (newVal !== null) onUpdate(item.id, { scrap: parseFloat(newVal) / 100 });
            }
        }, ` ${isOp ? 'Loss' : 'Scrap'}: ${(item.scrap || 0) * 100}%`),

        item.substitute_name && wp.element.createElement('span', { style: { color: 'green', marginLeft: '10px', fontSize: '11px' } }, `(Alt: ${item.substitute_name})`),

        wp.element.createElement('button', {
            className: 'button-link-delete',
            style: { marginLeft: '10px', fontSize: '11px' },
            onClick: () => onRemove(item.id)
        }, 'Remove'),

        item.type === 'material' && !item.substitute_id && wp.element.createElement('button', {
            className: 'button-secondary',
            style: { marginLeft: '10px', fontSize: '11px' },
            onClick: () => onMarkSubstitute(item.id)
        }, 'Add Substitute'),

        item.sub_bom && item.sub_bom.length > 0 &&
            item.sub_bom.map((child, i) => wp.element.createElement(BOMNode, { key: i, item: child, index: i, depth: depth + 1, onRemove, onMarkSubstitute, onUpdate, onReorder }))
    );
};

const BOMBuilder = ({ productId }) => {
    const [bom, setBom] = useState({ bom: [], total_cost: 0, version: 1 });
    const [materials, setMaterials] = useState([]);
    const [equipment, setEquipment] = useState([]);
    const [newVersion, setNewVersion] = useState(false);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        Promise.all([
            wp.apiFetch({ path: `/mep/v1/bom/${productId}` }),
            wp.apiFetch({ path: '/mep/v1/materials' }),
            wp.apiFetch({ path: '/mep/v1/equipment' })
        ]).then(([bomData, matData, eqData]) => {
            setBom(bomData);
            setMaterials(matData);
            setEquipment(eqData);
            setLoading(false);
        }).catch((err) => {
            setError('Failed to load BOM or Material data. Please check your permissions.');
            setLoading(false);
            console.error(err);
        });
    }, [productId]);

    const onDragStart = (e, item, type) => {
        e.dataTransfer.setData('mepItem', JSON.stringify({ ...item, mepType: type }));
    };

    const onDragOver = (e) => {
        e.preventDefault();
    };

    const onDrop = (e) => {
        const data = JSON.parse(e.dataTransfer.getData('mepItem'));
        if (data.mepType === 'material') {
            addMaterial(data);
        } else if (data.mepType === 'operation') {
            addOperation(data);
        }
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

    const addOperation = (eq) => {
        const newOp = {
            id: eq.id,
            name: `Op: ${eq.name}`,
            type: 'operation',
            qty: 30, // Default 30 mins
            scrap: 0
        };
        setBom(prev => ({ ...prev, bom: [...prev.bom, newOp] }));
    };

    const updateComponent = (id, updates) => {
        setBom(prev => ({
            ...prev,
            bom: prev.bom.map(item => item.id === id ? { ...item, ...updates } : item)
        }));
    };

    const reorderComponents = (fromIndex, toIndex) => {
        const newBom = [...bom.bom];
        const [movedItem] = newBom.splice(fromIndex, 1);
        newBom.splice(toIndex, 0, movedItem);
        setBom({ ...bom, bom: newBom });
    };

    const removeComponent = (id) => {
        setBom(prev => ({ ...prev, bom: prev.bom.filter(item => item.id !== id) }));
    };

    const markSubstitute = (id) => {
        const subId = prompt("Enter Material ID for Substitute:");
        if (subId) {
            setBom(prev => ({
                ...prev,
                bom: prev.bom.map(item => item.id === id ? { ...item, substitute_id: subId, substitute_name: `Alt Material #${subId}` } : item)
            }));
        }
    };

    const saveBom = () => {
        wp.apiFetch({
            path: `/mep/v1/bom/${productId}`,
            method: 'POST',
            data: {
                components: bom.bom,
                create_new_version: newVersion
            }
        }).then(() => {
            alert('BOM Saved Successfully!');
            setNewVersion(false);
            wp.apiFetch({ path: `/mep/v1/bom/${productId}` }).then(setBom);
        });
    };

    if (loading) return wp.element.createElement('p', null, 'Loading Interactive BOM Builder...');
    if (error) return wp.element.createElement('div', { className: 'notice notice-error' }, wp.element.createElement('p', null, error));

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    return wp.element.createElement('div', { className: 'mep-bom-editor-layout', style: { display: 'flex', gap: '20px' } },
        // Left Sidebar: Material & Operation Library
        wp.element.createElement('div', {
            className: 'mep-material-library',
            title: helpMode ? 'Library: Drag materials or operations into the BOM canvas to build your product structure.' : '',
            style: { width: '250px', border: '1px solid #ccc', padding: '10px' }
        },
            wp.element.createElement('h3', null, 'Materials'),
            materials.map(mat => wp.element.createElement('div', {
                key: mat.id,
                className: 'mep-library-item',
                draggable: true,
                onDragStart: (e) => onDragStart(e, mat, 'material'),
                style: { padding: '8px', border: '1px solid #eee', marginBottom: '5px', cursor: 'grab', background: '#fff' }
            }, mat.name)),

            wp.element.createElement('h3', { style: { marginTop: '20px' } }, 'Work Centers (Operations)'),
            equipment.map(eq => wp.element.createElement('div', {
                key: eq.id,
                className: 'mep-library-item',
                draggable: true,
                onDragStart: (e) => onDragStart(e, eq, 'operation'),
                style: { padding: '8px', border: '1px solid #e0f0ff', marginBottom: '5px', cursor: 'grab', background: '#fff' }
            }, eq.name))
        ),
        // Central Canvas
        wp.element.createElement('div', { className: 'mep-bom-main', style: { flex: 1 } },
            wp.element.createElement('header', { className: 'mep-bom-header', style: { marginBottom: '20px' } },
                wp.element.createElement('h2', null, `Visual BOM Editor: Product #${productId}`),
                wp.element.createElement('div', {
                    className: 'mep-cost-roll-up',
                    title: helpMode ? 'Cost Roll-up: This value is calculated in real-time by aggregating the costs of all materials and labor operations in the tree below.' : ''
                },
                    wp.element.createElement('strong', null, 'Estimated Roll-up Cost: '),
                    wp.element.createElement('span', { className: 'price', style: { color: '#2271b1', fontSize: '1.2em' } }, `$${bom.total_cost}`)
                )
            ),
            wp.element.createElement('div', {
                className: 'mep-bom-canvas',
                title: helpMode ? 'Canvas: Drop materials and operations here. Click on Qty/Time or Scrap/Loss to edit them. Drag nodes to reorder the assembly sequence.' : '',
                onDragOver: onDragOver,
                onDrop: onDrop,
                style: { minHeight: '300px', border: '2px dashed #ccc', padding: '20px', background: '#fff' }
            },
                bom.bom.length > 0 ?
                    bom.bom.map((item, index) => wp.element.createElement(BOMNode, { key: index, item: item, index: index, depth: 0, onRemove: removeComponent, onMarkSubstitute: markSubstitute, onUpdate: updateComponent, onReorder: reorderComponents })) :
                    wp.element.createElement('p', { className: 'empty-msg' }, 'Drag materials or operations here to start building...')
            ),
            wp.element.createElement('footer', { className: 'mep-bom-actions', style: { marginTop: '20px', display: 'flex', alignItems: 'center', gap: '20px' } },
                wp.element.createElement('button', { className: 'button button-primary', onClick: saveBom }, 'Save BOM Structure'),
                wp.element.createElement('label', null,
                    wp.element.createElement('input', {
                        type: 'checkbox',
                        checked: newVersion,
                        onChange: (e) => setNewVersion(e.target.checked)
                    }),
                    ' Save as New Version'
                ),
                wp.element.createElement('span', { style: { color: '#666' } }, `Current Version: ${bom.version || 1}`)
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
