/**
 * MEP Supplier Scorecard - Vendor Performance Visualization
 */

const { useState, useEffect } = wp.element;

const ScoreCard = ({ supplier }) => {
    const scoreColor = supplier.score > 80 ? '#46b450' : (supplier.score > 60 ? '#dba617' : '#d63638');

    return wp.element.createElement('div', {
        className: 'mep-supplier-card',
        style: { border: '1px solid #ccc', padding: '20px', background: '#fff', borderRadius: '8px', marginBottom: '20px' }
    },
        wp.element.createElement('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center' } },
            wp.element.createElement('h3', { style: { margin: 0 } }, supplier.name),
            wp.element.createElement('div', {
                style: { background: scoreColor, color: '#fff', padding: '5px 15px', borderRadius: '20px', fontWeight: 'bold' }
            }, `Score: ${supplier.score}/100`)
        ),
        wp.element.createElement('div', { style: { marginTop: '15px', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' } },
            wp.element.createElement('div', null,
                wp.element.createElement('strong', null, 'Quality Rate: '),
                wp.element.createElement('span', null, `${(supplier.quality_rate * 100).toFixed(1)}%`),
                wp.element.createElement('div', { style: { height: '8px', background: '#eee', marginTop: '5px', borderRadius: '4px' } },
                    wp.element.createElement('div', { style: { height: '100%', width: `${supplier.quality_rate * 100}%`, background: '#2271b1', borderRadius: '4px' } })
                )
            ),
            wp.element.createElement('div', null,
                wp.element.createElement('strong', null, 'On-Time Delivery: '),
                wp.element.createElement('span', null, `${(supplier.on_time_rate * 100).toFixed(1)}%`),
                wp.element.createElement('div', { style: { height: '8px', background: '#eee', marginTop: '5px', borderRadius: '4px' } },
                    wp.element.createElement('div', { style: { height: '100%', width: `${supplier.on_time_rate * 100}%`, background: '#673ab7', borderRadius: '4px' } })
                )
            )
        )
    );
};

const SupplierScorecard = () => {
    const [suppliers, setSuppliers] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/suppliers' })
            .then(async (data) => {
                const enriched = await Promise.all(data.map(async (s) => {
                    const scoreData = await wp.apiFetch({ path: `/mep/v1/procurement/supplier-score/${s.id}` });
                    return { ...s, ...scoreData };
                }));
                setSuppliers(enriched);
                setLoading(false);
            });
    }, []);

    if (loading) return wp.element.createElement('p', null, 'Calculating vendor performance metrics...');

    return wp.element.createElement('div', { className: 'mep-scorecard-container' },
        wp.element.createElement('h2', null, 'Supplier Performance Scorecards'),
        suppliers.length > 0 ?
            suppliers.map(s => wp.element.createElement(ScoreCard, { key: s.id, supplier: s })) :
            wp.element.createElement('p', null, 'No suppliers found.')
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-supplier-scorecard-root');
    if (container) {
        wp.element.render(wp.element.createElement(SupplierScorecard, null), container);
    }
});
