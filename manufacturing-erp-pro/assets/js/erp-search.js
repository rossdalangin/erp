(function() {
/**
 * MEP ERP Search - Global Command Palette
 */

const { useState, useEffect, useRef } = wp.element;
const { __ } = wp.i18n;

const ERPSearch = () => {
    const [isOpen, setIsOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [results, setResults] = useState([]);
    const inputRef = useRef(null);

    // Global shortcut Ctrl+K
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                setIsOpen(true);
            }
            if (e.key === 'Escape') {
                setIsOpen(false);
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, []);

    useEffect(() => {
        if (isOpen && inputRef.current) {
            inputRef.current.focus();
        }
    }, [isOpen]);

    useEffect(() => {
        if (query.length < 2) {
            setResults([]);
            return;
        }

        // Mock search logic - in a real app this hits a custom REST endpoint
        // For this demo, we'll suggest navigation based on keywords
        const suggestions = [
            { label: __('Go to BOM Builder', 'manufacturing-erp-pro'), url: 'admin.php?page=mep-bom-builder', type: 'nav' },
            { label: __('View Production Board', 'manufacturing-erp-pro'), url: 'admin.php?page=mep-production', type: 'nav' },
            { label: __('Check Inventory Levels', 'manufacturing-erp-pro'), url: 'admin.php?page=mep-inventory', type: 'nav' },
            { label: __('Trace a Lot #', 'manufacturing-erp-pro'), url: 'admin.php?page=mep-traceability', type: 'nav' },
            { label: __('Supplier Scorecards', 'manufacturing-erp-pro'), url: 'admin.php?page=mep-supplier-scorecard', type: 'nav' }
        ].filter(s => s.label.toLowerCase().includes(query.toLowerCase()));

        setResults(suggestions);
    }, [query]);

    const helpMode = typeof mepSettings !== 'undefined' && mepSettings.helpMode === 'on';

    if (!isOpen) return wp.element.createElement('button', {
        className: 'button',
        title: helpMode ? __('Command Palette: Use this to search across all ERP modules and records instantly.', 'manufacturing-erp-pro') : '',
        style: { position: 'fixed', bottom: '20px', right: '20px', zIndex: 9999, boxShadow: '0 2px 10px rgba(0,0,0,0.2)' },
        onClick: () => setIsOpen(true)
    }, `🔍 ${__('ERP Search', 'manufacturing-erp-pro')} (Ctrl+K)`);

    return wp.element.createElement('div', {
        className: 'mep-search-overlay',
        style: { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, background: 'rgba(0,0,0,0.8)', zIndex: 100000, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '20px' },
        onClick: () => setIsOpen(false)
    },
        wp.element.createElement('div', {
            className: 'mep-search-modal',
            style: { background: '#fff', width: '100%', maxWidth: '600px', borderRadius: '8px', padding: '20px', boxShadow: '0 10px 25px rgba(0,0,0,0.5)' },
            onClick: (e) => e.stopPropagation()
        },
            wp.element.createElement('input', {
                ref: inputRef,
                type: 'text',
                placeholder: __('Search SKU, Lot, PO or Menu...', 'manufacturing-erp-pro'),
                value: query,
                onChange: (e) => setQuery(e.target.value),
                style: { width: '100%', fontSize: '20px', padding: '10px', border: '1px solid #ddd', borderRadius: '4px', marginBottom: '20px' }
            }),
            wp.element.createElement('div', { className: 'mep-search-results' },
                results.length > 0 ?
                    results.map((r, i) => wp.element.createElement('a', {
                        key: i,
                        href: r.url,
                        style: { display: 'block', padding: '10px', borderBottom: '1px solid #eee', textDecoration: 'none', color: '#2271b1' }
                    }, r.label)) :
                    wp.element.createElement('p', { style: { color: '#999', textAlign: 'center' } }, __('Start typing to search the ERP...', 'manufacturing-erp-pro'))
            ),
            wp.element.createElement('div', { style: { marginTop: '20px', fontSize: '11px', color: '#999', textAlign: 'right' } }, __('Press ESC to close', 'manufacturing-erp-pro'))
        )
    );
};


const init = () => {
    const root = document.createElement('div');
    root.id = 'mep-erp-search-root';
    document.body.appendChild(root);
    if (wp.element.createRoot) { wp.element.createRoot(null), root).render(wp.element.createElement(ERPSearch); } else { wp.element.render(wp.element.createElement(ERPSearch, null), root); }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}

})();