/**
 * MEP Lot Traceability - Graphical Genealogy Visualization
 */

const { useState } = wp.element;

const TraceNode = ({ label, type, date, qty }) => {
    return wp.element.createElement('div', {
        className: 'mep-trace-node',
        style: { border: '1px solid #ccc', padding: '15px', borderRadius: '8px', background: '#fff', marginBottom: '10px', boxShadow: '0 2px 4px rgba(0,0,0,0.05)' }
    },
        wp.element.createElement('div', { style: { fontWeight: 'bold', color: '#2271b1' } }, label),
        wp.element.createElement('div', { style: { fontSize: '11px', color: '#666' } }, `${type} | ${date} | Qty: ${qty}`)
    );
};

const Traceability = () => {
    const [lot, setLot] = useState('');
    const [trace, setTrace] = useState(null);
    const [loading, setLoading] = useState(false);

    const performTrace = () => {
        if (!lot) return;
        setLoading(true);
        wp.apiFetch({ path: `/mep/v1/qc/trace/${lot}` })
            .then(data => {
                setTrace(data);
                setLoading(false);
            })
            .catch(err => {
                console.error(err);
                setLoading(false);
            });
    };

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    return wp.element.createElement('div', { className: 'mep-traceability-view' },
        wp.element.createElement('div', {
            className: 'mep-trace-search',
            title: helpMode ? 'Lot Search: Enter a lot number to see its entire production history.' : '',
            style: { marginBottom: '30px', display: 'flex', gap: '10px' }
        },
            wp.element.createElement('input', {
                type: 'text',
                placeholder: 'Enter Lot Number...',
                value: lot,
                onChange: (e) => setLot(e.target.value),
                style: { flex: 1 }
            }),
            wp.element.createElement('button', { className: 'button button-primary', onClick: performTrace }, 'Trace Lot')
        ),

        loading && wp.element.createElement('p', null, 'Tracing lot genealogy...'),

        trace && wp.element.createElement('div', {
            className: 'mep-trace-results',
            title: helpMode ? 'Trace Results: Shows upstream and downstream movements of this lot.' : ''
        },
            wp.element.createElement('h3', null, `Genealogy Graph for Lot: ${trace.lot}`),
            wp.element.createElement('div', { className: 'mep-trace-graph-layout', style: { padding: '20px', background: '#f6f7f7', borderRadius: '8px' } },
                trace.history.length > 0 ?
                    trace.history.map((h, i) => wp.element.createElement('div', { key: i, style: { display: 'flex', flexDirection: 'column', alignItems: 'center' } },
                        wp.element.createElement(TraceNode, {
                            label: h.transaction_type,
                            type: `Material #${h.material_id}`,
                            date: h.created_at,
                            qty: h.quantity
                        }),
                        i < trace.history.length - 1 && wp.element.createElement('div', { style: { height: '20px', borderLeft: '2px dashed #ccc', marginBottom: '10px' } })
                    )) :
                    wp.element.createElement('p', null, 'No history found.')
            ),
            wp.element.createElement('div', { style: { marginTop: '20px' } },
                wp.element.createElement('button', {
                    className: 'button button-secondary',
                    onClick: () => {
                        const reportUrl = wpApiSettings.root + `mep/v1/qc/trace/${trace.lot}/report?_wpnonce=` + wpApiSettings.nonce + '&print=1';
                        window.open(reportUrl, '_blank');
                    }
                }, 'Generate Trace Report (PDF)')
            )
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-traceability-root');
    if (container) {
        wp.element.render(wp.element.createElement(Traceability, null), container);
    }
});
