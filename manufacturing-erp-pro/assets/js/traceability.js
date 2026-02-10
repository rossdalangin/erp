/**
 * MEP Lot Traceability - Genealogy Visualization
 */

const { useState } = wp.element;

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

    return wp.element.createElement('div', { className: 'mep-traceability-view' },
        wp.element.createElement('div', { className: 'mep-trace-search', style: { marginBottom: '30px', display: 'flex', gap: '10px' } },
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

        trace && wp.element.createElement('div', { className: 'mep-trace-results' },
            wp.element.createElement('h3', null, `Genealogy for Lot: ${trace.lot}`),
            wp.element.createElement('div', { className: 'mep-trace-graph', style: { border: '1px solid #ccc', padding: '20px', background: '#fff' } },
                wp.element.createElement('h4', null, 'Transaction History'),
                wp.element.createElement('table', { className: 'wp-list-table widefat fixed striped' },
                    wp.element.createElement('thead', null,
                        wp.element.createElement('tr', null,
                            wp.element.createElement('th', null, 'Date'),
                            wp.element.createElement('th', null, 'Type'),
                            wp.element.createElement('th', null, 'Qty'),
                            wp.element.createElement('th', null, 'Warehouse')
                        )
                    ),
                    wp.element.createElement('tbody', null,
                        trace.history.length > 0 ?
                            trace.history.map((h, i) => wp.element.createElement('tr', { key: i },
                                wp.element.createElement('td', null, h.created_at),
                                wp.element.createElement('td', null, h.transaction_type),
                                wp.element.createElement('td', null, h.quantity),
                                wp.element.createElement('td', null, h.warehouse_id)
                            )) :
                            wp.element.createElement('tr', null, wp.element.createElement('td', { colSpan: 4 }, 'No history found.'))
                    )
                )
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
