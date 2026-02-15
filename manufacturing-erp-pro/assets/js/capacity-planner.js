(function() {
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;

const CapacityPlanner = () => {
    const [equipment, setEquipment] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        wp.apiFetch({ path: '/mep/v1/equipment/detailed' })
            .then(res => { setEquipment(res || []); setLoading(false); })
            .catch(() => setLoading(false));
    }, []);

    if (loading) return wp.element.createElement('p', null, __('Loading Capacity...', 'manufacturing-erp-pro'));

    return wp.element.createElement('div', { className: 'mep-capacity-planner', style: { background: '#fff', padding: '20px', border: '1px solid #ccc' } },
        (equipment || []).map(item => wp.element.createElement('div', { key: item.id, style: { borderBottom: '1px solid #eee', padding: '15px 0' } },
            wp.element.createElement('h2', null, item.name),
            wp.element.createElement('p', null, `${__('Daily Capacity:', 'manufacturing-erp-pro')} ${item.capacity} mins`),
            wp.element.createElement('div', { style: { display: 'flex', gap: '5px' } },
                ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].map(day => wp.element.createElement('div', { key: day, style: { flex: 1, height: '40px', background: '#eee', textAlign: 'center', lineHeight: '40px' } }, day))
            )
        ))
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
