(function() {
const { useState } = wp.element;
const { __ } = wp.i18n;

const LoadingUI = ({ message = __('Loading...', 'manufacturing-erp-pro') }) => (
    wp.element.createElement('div', { className: 'mep-loading-container' },
        wp.element.createElement('div', { className: 'mep-spinner' }),
        wp.element.createElement('p', null, message)
    )
);

const SetupWizard = () => {
    const [step, setStep] = useState(1);
    const [loading, setLoading] = useState(false);
    const [status, setStatus] = useState('');

    const finishSetup = (withSeeding = false) => {
        setLoading(true);
        setStatus(withSeeding ? __('Planting factory seeds...', 'manufacturing-erp-pro') : __('Initializing factory...', 'manufacturing-erp-pro'));

        const tasks = [];
        if (withSeeding) tasks.push(wp.apiFetch({ path: '/mep/v1/seed', method: 'POST' }));
        tasks.push(wp.apiFetch({ path: '/mep/v1/settings', method: 'POST', data: { mep_setup_complete: '1', mep_help_mode: 'on' } }));

        Promise.all(tasks).then(() => {
            setStatus(__('Success! Launching...', 'manufacturing-erp-pro'));
            setTimeout(() => { window.location.href = 'admin.php?page=mep-dashboard'; }, 1000);
        });
    };

    return wp.element.createElement('div', {
        className: 'mep-wizard-overlay',
        style: { position: 'fixed', top: 0, left: 0, width: '100%', height: '100%', background: '#f8fafc', zIndex: 99999, display: 'flex', alignItems: 'center', justifyContent: 'center' }
    },
        wp.element.createElement('div', {
            className: 'mep-card',
            style: { maxWidth: '600px', width: '90%', textAlign: 'center', padding: '60px 40px', boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)' }
        },
            wp.element.createElement('div', { style: { fontSize: '48px', marginBottom: '20px' } }, '🏭'),
            wp.element.createElement('h1', { style: { fontSize: '28px', marginBottom: '10px', color: 'var(--mep-text)' } }, __('Welcome to Manufacturing ERP Pro', 'manufacturing-erp-pro')),
            wp.element.createElement('p', { style: { color: '#64748b', marginBottom: '40px' } }, __('The intelligent backbone for your SME manufacturing business.', 'manufacturing-erp-pro')),

            status && wp.element.createElement('div', { className: 'mep-loading-container', style: { minHeight: 'auto' } },
                wp.element.createElement('div', { className: 'mep-spinner' }),
                wp.element.createElement('p', { style: { fontWeight: '600', color: 'var(--mep-primary)' } }, status)
            ),

            !loading && step === 1 && wp.element.createElement('div', null,
                wp.element.createElement('h3', { style: { marginBottom: '25px' } }, __('Select your manufacturing archetype:', 'manufacturing-erp-pro')),
                wp.element.createElement('div', { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' } },
                    wp.element.createElement('button', {
                        className: 'mep-step-link',
                        style: { cursor: 'pointer', textAlign: 'center' },
                        onClick: () => setStep(2)
                    },
                        wp.element.createElement('strong', null, __('Discrete', 'manufacturing-erp-pro')),
                        wp.element.createElement('span', { style: { fontSize: '12px' } }, __('Bags, Shoes, Electronics', 'manufacturing-erp-pro'))
                    ),
                    wp.element.createElement('button', {
                        className: 'mep-step-link',
                        style: { cursor: 'pointer', textAlign: 'center' },
                        onClick: () => setStep(2)
                    },
                        wp.element.createElement('strong', null, __('Process', 'manufacturing-erp-pro')),
                        wp.element.createElement('span', { style: { fontSize: '12px' } }, __('Chemicals, Food, Liquids', 'manufacturing-erp-pro'))
                    )
                )
            ),

            !loading && step === 2 && wp.element.createElement('div', null,
                wp.element.createElement('h3', { style: { marginBottom: '25px' } }, __('Choose your starting point:', 'manufacturing-erp-pro')),
                wp.element.createElement('button', {
                    className: 'button button-primary button-large',
                    style: { display: 'block', width: '100%', padding: '20px', marginBottom: '15px', height: 'auto', fontSize: '18px' },
                    onClick: () => finishSetup(true)
                }, __('🚀 Start with Demo Data', 'manufacturing-erp-pro')),
                wp.element.createElement('button', {
                    className: 'button button-large',
                    style: { display: 'block', width: '100%', padding: '15px', height: 'auto' },
                    onClick: () => finishSetup(false)
                }, __('🏢 Start with Clean Factory', 'manufacturing-erp-pro')),
                wp.element.createElement('p', { style: { marginTop: '20px', fontSize: '12px', color: '#94a3b8' } }, __('Demo data includes LeatherCraft Co. sample materials, BOMs, and Work Orders.', 'manufacturing-erp-pro'))
            ),

            !loading && wp.element.createElement('button', { className: 'button button-link', style: { marginTop: '30px' }, onClick: () => finishSetup(false) }, __('Skip Onboarding', 'manufacturing-erp-pro'))
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-wizard-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(container).render(wp.element.createElement(SetupWizard, null)); }
        else { wp.element.render(wp.element.createElement(SetupWizard, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();