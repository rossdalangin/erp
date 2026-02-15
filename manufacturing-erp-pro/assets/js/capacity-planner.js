(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const LoadingUI = ({ message = __('Loading...', 'manufacturing-erp-pro') }) => (
    wp.element.createElement('div', { className: 'mep-loading-container' },
        wp.element.createElement('div', { className: 'mep-spinner' }),
        wp.element.createElement('p', null, message)
    )
);

const CapacityPlanner = () => {
    const [equipment, setEquipment] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/equipment/detailed' })
            .then(res => { setEquipment(res || []); setLoading(false); })
            .catch(() => setLoading(false));
    }, []);

    if (loading) return wp.element.createElement(LoadingUI, { message: __('Loading Capacity...', 'manufacturing-erp-pro') });

    return wp.element.createElement('div', { className: 'mep-module-container' },
        wp.element.createElement('h2', { className: 'mep-card-title' }, __('Production Capacity Planning', 'manufacturing-erp-pro')),
        wp.element.createElement('div', { className: 'mep-kpi-grid' },
            (equipment || []).map(item => wp.element.createElement('div', { key: item.id, className: 'mep-card' },
                wp.element.createElement('h3', { className: 'mep-card-title' }, item.name),
                wp.element.createElement('div', { className: 'mep-resource-item' },
                    wp.element.createElement('div', { className: 'mep-resource-header' },
                        wp.element.createElement('span', null, __('Available Time', 'manufacturing-erp-pro')),
                        wp.element.createElement('span', null, `${item.capacity} mins`)
                    ),
                    wp.element.createElement('div', { className: 'mep-progress-bg' },
                        wp.element.createElement('div', { className: 'mep-progress-fill', style: { width: '100%' } })
                    )
                ),
                wp.element.createElement('div', { style: { display: 'flex', gap: '5px', marginTop: '15px' } },
                    ['M', 'T', 'W', 'T', 'F'].map(day => wp.element.createElement('div', { key: day, style: { flex: 1, height: '30px', background: '#f1f5f9', border: '1px solid #e2e8f0', textAlign: 'center', fontSize: '11px', lineHeight: '30px', borderRadius: '4px' } }, day))
                )
            ))
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-capacity-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(container).render(wp.element.createElement(CapacityPlanner, null)); }
        else { wp.element.render(wp.element.createElement(CapacityPlanner, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();