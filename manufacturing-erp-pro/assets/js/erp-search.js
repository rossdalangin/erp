(function() {
const { useState, useEffect, useRef } = wp.element;
const { __ } = wp.i18n;

const ERPSearch = () => {
    const [isOpen, setIsOpen] = useState(false);
    const [query, setQuery] = useState('');
    const inputRef = useRef(null);

    useEffect(() => {
        const handleKeyDown = (e) => {
            if (e.ctrlKey && e.key === 'k') { e.preventDefault(); setIsOpen(true); }
            if (e.key === 'Escape') setIsOpen(false);
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, []);

    useEffect(() => { if (isOpen && inputRef.current) inputRef.current.focus(); }, [isOpen]);

    if (!isOpen) return wp.element.createElement('button', {
        className: 'button',
        style: { position: 'fixed', bottom: '20px', right: '20px', zIndex: 9999 },
        onClick: () => setIsOpen(true)
    }, `🔍 ${__('ERP Search', 'manufacturing-erp-pro')} (Ctrl+K)`);

    return wp.element.createElement('div', {
        style: { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, background: 'rgba(0,0,0,0.8)', zIndex: 100000, display: 'flex', alignItems: 'center', justifyContent: 'center' },
        onClick: () => setIsOpen(false)
    },
        wp.element.createElement('div', {
            style: { background: '#fff', width: '400px', padding: '20px', borderRadius: '8px' },
            onClick: (e) => e.stopPropagation()
        },
            wp.element.createElement('input', {
                ref: inputRef,
                type: 'text',
                placeholder: __('Search...', 'manufacturing-erp-pro'),
                value: query,
                onChange: (e) => setQuery(e.target.value),
                style: { width: '100%', fontSize: '18px', padding: '10px' }
            }),
            wp.element.createElement('p', { style: { color: '#999', marginTop: '10px', fontSize: '12px' } }, __('Press ESC to close', 'manufacturing-erp-pro'))
        )
    );
};

const init = () => {
    const root = document.createElement('div');
    root.id = 'mep-erp-search-root';
    document.body.appendChild(root);
    if (wp.element.createRoot) { wp.element.createRoot(root).render(wp.element.createElement(ERPSearch, null)); }
    else { wp.element.render(wp.element.createElement(ERPSearch, null), root); }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
