(function() {
const { useState, useEffect, useRef } = wp.element;
const { __ } = wp.i18n;

const LoadingUI = ({ message = __('Loading...', 'manufacturing-erp-pro') }) => (
    wp.element.createElement('div', { className: 'mep-loading-container' },
        wp.element.createElement('div', { className: 'mep-spinner' }),
        wp.element.createElement('p', null, message)
    )
);

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
        className: 'mep-search-trigger',
        style: {
            position: 'fixed', bottom: '30px', right: '30px', zIndex: 9999,
            background: 'var(--mep-primary)', color: '#fff', border: 'none',
            padding: '12px 20px', borderRadius: '30px', boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            cursor: 'pointer', fontWeight: '600', display: 'flex', alignItems: 'center', gap: '8px'
        },
        onClick: () => setIsOpen(true)
    }, wp.element.createElement('span', null, '🔍'), wp.element.createElement('span', null, __('Search ERP (Ctrl+K)', 'manufacturing-erp-pro')));

    return wp.element.createElement('div', {
        style: { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, background: 'rgba(15, 23, 42, 0.9)', zIndex: 100000, display: 'flex', alignItems: 'start', justifyContent: 'center', paddingTop: '100px' },
        onClick: () => setIsOpen(false)
    },
        wp.element.createElement('div', {
            style: { background: '#fff', width: '600px', borderRadius: '12px', boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1)', overflow: 'hidden' },
            onClick: (e) => e.stopPropagation()
        },
            wp.element.createElement('div', { style: { padding: '20px', borderBottom: '1px solid #e2e8f0', display: 'flex', alignItems: 'center', gap: '15px' } },
                wp.element.createElement('span', { style: { fontSize: '20px' } }, '🔍'),
                wp.element.createElement('input', {
                    ref: inputRef,
                    type: 'text',
                    placeholder: __('Search for materials, work orders, lots...', 'manufacturing-erp-pro'),
                    value: query,
                    onChange: (e) => setQuery(e.target.value),
                    style: { width: '100%', fontSize: '18px', border: 'none', outline: 'none', background: 'transparent' }
                })
            ),
            wp.element.createElement('div', { style: { padding: '40px 20px', textAlign: 'center', color: '#64748b' } },
                query ? __('No results found for ', 'manufacturing-erp-pro') + `"${query}"` : __('Type to search...', 'manufacturing-erp-pro')
            ),
            wp.element.createElement('div', { style: { padding: '10px 20px', background: '#f8fafc', borderTop: '1px solid #e2e8f0', display: 'flex', justifyContent: 'space-between', fontSize: '12px', color: '#94a3b8' } },
                wp.element.createElement('span', null, [
                    wp.element.createElement('kbd', { style: { background: '#fff', border: '1px solid #cbd5e1', padding: '2px 4px', borderRadius: '3px' } }, 'ESC'),
                    ' to close'
                ]),
                wp.element.createElement('span', null, [
                    wp.element.createElement('kbd', { style: { background: '#fff', border: '1px solid #cbd5e1', padding: '2px 4px', borderRadius: '3px' } }, 'ENTER'),
                    ' to select'
                ])
            )
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