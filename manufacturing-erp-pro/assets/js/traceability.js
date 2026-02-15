(function() {
const { useState } = wp.element;
const { __ } = wp.i18n;

const LoadingUI = ({ message = __('Loading...', 'manufacturing-erp-pro') }) => (
    wp.element.createElement('div', { className: 'mep-loading-container' },
        wp.element.createElement('div', { className: 'mep-spinner' }),
        wp.element.createElement('p', null, message)
    )
);

const TraceNode = ({ label, type, date, qty }) => {
    return wp.element.createElement('div', {
        className: 'mep-trace-node mep-wo-card', style: { minWidth: '300px' }
    },
        wp.element.createElement('div', { style: { fontWeight: 'bold', color: '#2271b1' } }, label),
        wp.element.createElement('div', { style: { fontSize: '11px', color: '#666' } }, type),
        wp.element.createElement('div', { style: { fontSize: '10px', color: '#999' } }, `${date} | Qty: ${qty}`)
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
            .then(data => { setTrace(data); setLoading(false); })
            .catch(() => setLoading(false));
    };

    return wp.element.createElement('div', { className: 'mep-traceability-view mep-card' },
        wp.element.createElement('div', { style: { marginBottom: '30px', display: 'flex', gap: '10px' } },
            wp.element.createElement('input', { type: 'text', placeholder: __('Lot Number...', 'manufacturing-erp-pro'), value: lot, onChange: (e) => setLot(e.target.value), style: { flex: 1 } }),
            wp.element.createElement('button', { className: 'button button-primary', onClick: performTrace }, __('Trace Lot', 'manufacturing-erp-pro'))
        ),
        trace && wp.element.createElement('div', null,
            wp.element.createElement('h3', null, `${__('Genealogy Graph:', 'manufacturing-erp-pro')} ${trace.lot}`),
            wp.element.createElement('div', { className: 'mep-trace-canvas', style: { display: 'flex', flexDirection: 'column', alignItems: 'center', padding: '40px', background: '#f8fafc', borderRadius: '8px' } },
                (trace.history || []).map((h, i) => wp.element.createElement(wp.element.Fragment, { key: i },
                    wp.element.createElement(TraceNode, { label: h.transaction_type, type: `Material #${h.material_id}`, date: h.created_at, qty: h.quantity }),
                    i < trace.history.length - 1 && wp.element.createElement('div', { style: { height: '20px', width: '2px', background: '#2271b1' } })
                ))
            )
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-traceability-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(container).render(wp.element.createElement(Traceability, null)); }
        else { wp.element.render(wp.element.createElement(Traceability, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
