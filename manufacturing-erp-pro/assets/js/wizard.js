(function() {
const { useState } = wp.element;
const { __ } = wp.i18n;

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
        style: { position: 'fixed', top: 0, left: 0, width: '100%', height: '100%', background: '#f0f0f1', zIndex: 99999, display: 'flex', alignItems: 'center', justifyContent: 'center' }
    },
        wp.element.createElement('div', {
            className: 'mep-wizard-card',
            style: { background: '#fff', padding: '40px', maxWidth: '500px', width: '100%', borderRadius: '8px', boxShadow: '0 10px 25px rgba(0,0,0,0.1)', textAlign: 'center' }
        },
            wp.element.createElement('h1', null, __('Manufacturing ERP Pro Setup', 'manufacturing-erp-pro')),
            status && wp.element.createElement('p', { style: { color: '#2271b1', fontWeight: 'bold' } }, status),

            !loading && step === 1 && wp.element.createElement('div', null,
                wp.element.createElement('h2', null, __('Step 1: Production Model', 'manufacturing-erp-pro')),
                wp.element.createElement('button', { className: 'button button-large', style: { marginRight: '10px' }, onClick: () => setStep(2) }, __('Discrete Goods', 'manufacturing-erp-pro')),
                wp.element.createElement('button', { className: 'button button-large', onClick: () => setStep(2) }, __('Process Mixing', 'manufacturing-erp-pro'))
            ),

            !loading && step === 2 && wp.element.createElement('div', null,
                wp.element.createElement('h2', null, __('Step 2: Initialize Data', 'manufacturing-erp-pro')),
                wp.element.createElement('button', { className: 'button button-primary button-large', style: { display: 'block', width: '100%', marginBottom: '10px' }, onClick: () => finishSetup(true) }, __('🚀 Demo Mode (LeatherCraft Co.)', 'manufacturing-erp-pro')),
                wp.element.createElement('button', { className: 'button button-large', style: { display: 'block', width: '100%' }, onClick: () => finishSetup(false) }, __('🏢 Clean Slate', 'manufacturing-erp-pro'))
            ),

            !loading && wp.element.createElement('button', { className: 'button button-link', style: { marginTop: '20px' }, onClick: () => finishSetup(false) }, __('Skip Wizard', 'manufacturing-erp-pro'))
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
