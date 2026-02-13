/**
 * MEP Lot Traceability - Graphical Genealogy Visualization
 */

const { useState } = wp.element;

const TraceNode = ({ label, type, date, qty, onDragStart, onDragEnd }) => {
    return wp.element.createElement('div', {
        className: 'mep-trace-node',
        draggable: true,
        onDragStart: onDragStart,
        onDragEnd: onDragEnd,
        style: { border: '1px solid #ccc', padding: '15px', borderRadius: '8px', background: '#fff', marginBottom: '0', boxShadow: '0 2px 4px rgba(0,0,0,0.05)', position: 'relative', zIndex: 2, minWidth: '200px', cursor: 'grab' }
    },
        wp.element.createElement('div', { style: { fontWeight: 'bold', color: '#2271b1', marginBottom: '5px' } }, label),
        wp.element.createElement('div', { style: { fontSize: '11px', color: '#666' } }, `${type}`),
        wp.element.createElement('div', { style: { fontSize: '10px', color: '#999', marginTop: '3px' } }, `${date} | Qty: ${qty}`)
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
            title: helpMode ? 'Lot Search: Enter a lot number to see its entire production history. Example: Enter "LOT-COW-001" to trace raw cowhide.' : '',
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
            title: helpMode ? 'Trace Results: Shows upstream and downstream movements of this lot. The graph displays receipt, transfer, and consumption nodes.' : ''
        },
            wp.element.createElement('h3', null, `Genealogy Graph for Lot: ${trace.lot}`),
            wp.element.createElement('div', {
                className: 'mep-trace-graph-layout',
                onDragOver: (e) => e.preventDefault(),
                style: { padding: '40px', background: '#f0f0f1', borderRadius: '8px', overflowX: 'auto', minHeight: '400px' }
            },
                trace.history.length > 0 ?
                    wp.element.createElement('div', { style: { display: 'flex', flexDirection: 'column', alignItems: 'center' } },
                        trace.history.map((h, i) => wp.element.createElement(wp.element.Fragment, { key: i },
                            wp.element.createElement(TraceNode, {
                                label: h.transaction_type,
                                type: `Material #${h.material_id}`,
                                date: h.created_at,
                                qty: h.quantity,
                                onDragStart: (e) => {
                                    e.target.style.opacity = '0.5';
                                    e.dataTransfer.setData('text/plain', i);
                                },
                                onDragEnd: (e) => {
                                    e.target.style.opacity = '1';
                                }
                            }),
                            i < trace.history.length - 1 && wp.element.createElement('div', {
                                className: 'mep-trace-connector',
                                style: { height: '30px', width: '2px', background: '#2271b1', position: 'relative' }
                            },
                                wp.element.createElement('div', {
                                    style: { position: 'absolute', bottom: '-5px', left: '-4px', borderTop: '6px solid #2271b1', borderLeft: '5px solid transparent', borderRight: '5px solid transparent' }
                                })
                            )
                        ))
                    ) :
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
