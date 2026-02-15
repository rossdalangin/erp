(function() {
const { useState } = wp.element;
const { __ } = wp.i18n;

const SetupWizard = () => {
    const [step, setStep] = useState(1);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [status, setStatus] = useState('');

    const finishSetup = (withSeeding = false) => {
        setLoading(true);
        setError(null);
        setStatus(withSeeding ? __('Planting factory seeds...', 'manufacturing-erp-pro') : __('Initializing factory...', 'manufacturing-erp-pro'));

        const tasks = [];
        if (withSeeding) {
            tasks.push(wp.apiFetch({ path: '/mep/v1/seed', method: 'POST' }));
        }

        tasks.push(wp.apiFetch({
            path: '/mep/v1/settings',
            method: 'POST',
            data: { mep_setup_complete: '1', mep_help_mode: 'on' }
        }));

        Promise.all(tasks)
            .then(() => {
                setStatus(__('Success! Launching ERP...', 'manufacturing-erp-pro'));
                setTimeout(() => { window.location.href = 'admin.php?page=mep-dashboard'; }, 1000);
            })
            .catch(err => {
                setLoading(false);
                setError(__('Critical error during setup.', 'manufacturing-erp-pro'));
            });
    };

    return wp.element.createElement('div', { className: 'mep-wizard-overlay mep-admin-style mep-animate-fade-in' },
        wp.element.createElement('div', {
            className: 'mep-wizard-card',
            style: { background: '#fff', padding: '50px', maxWidth: '650px', width: '100%', borderRadius: '12px', textAlign: 'center', boxShadow: '0 25px 50px -12px rgba(0,0,0,0.25)' }
        },
            wp.element.createElement('div', { style: { fontSize: '64px', marginBottom: '20px' } }, '🏗️'),
            wp.element.createElement('h1', { style: { fontSize: '32px', fontWeight: '800', color: 'var(--mep-primary)', margin: '0 0 10px 0' } }, __('Manufacturing ERP Pro', 'manufacturing-erp-pro')),
            wp.element.createElement('p', { style: { color: '#64748b', fontSize: '18px', marginBottom: '40px' } }, __('Factory Setup & Onboarding', 'manufacturing-erp-pro')),

            error && wp.element.createElement('div', { className: 'notice notice-error', style: { marginBottom: '20px' } }, wp.element.createElement('p', null, error)),
            status && wp.element.createElement('div', { style: { padding: '20px', background: '#f0f9ff', color: '#0369a1', borderRadius: '8px', fontWeight: 'bold' } }, status),

            !loading && step === 1 && wp.element.createElement('div', null,
                wp.element.createElement('h2', null, __('Step 1: Production Model', 'manufacturing-erp-pro')),
                wp.element.createElement('div', { style: { display: 'flex', gap: '20px', marginTop: '30px' } },
                    wp.element.createElement('button', { className: 'button button-large', style: { flex: 1, padding: '40px 20px' }, onClick: () => setStep(2) }, __('Discrete Goods', 'manufacturing-erp-pro')),
                    wp.element.createElement('button', { className: 'button button-large', style: { flex: 1, padding: '40px 20px' }, onClick: () => setStep(2) }, __('Process Mixing', 'manufacturing-erp-pro'))
                )
            ),

            !loading && step === 2 && wp.element.createElement('div', null,
                wp.element.createElement('h2', null, __('Step 2: Initialization', 'manufacturing-erp-pro')),
                wp.element.createElement('div', {
                    onClick: () => finishSetup(true),
                    style: { background: 'var(--mep-primary)', color: '#fff', padding: '25px', borderRadius: '12px', cursor: 'pointer', marginBottom: '15px', textAlign: 'left' }
                },
                    wp.element.createElement('h4', { style: { margin: '0 0 5px 0' } }, '🚀 ' + __('Demo Mode', 'manufacturing-erp-pro')),
                    wp.element.createElement('p', { style: { margin: 0, fontSize: '13px', opacity: 0.8 } }, __('Populate with LeatherCraft Co. sample data.', 'manufacturing-erp-pro'))
                ),
                wp.element.createElement('div', {
                    onClick: () => finishSetup(false),
                    style: { background: '#f8fafc', border: '1px solid #e2e8f0', padding: '25px', borderRadius: '12px', cursor: 'pointer', textAlign: 'left' }
                },
                    wp.element.createElement('h4', { style: { margin: '0 0 5px 0' } }, '🏢 ' + __('Empty System', 'manufacturing-erp-pro')),
                    wp.element.createElement('p', { style: { margin: 0, fontSize: '13px', color: '#64748b' } }, __('Start with a clean slate.', 'manufacturing-erp-pro'))
                )
            ),

            !loading && wp.element.createElement('button', {
                className: 'button button-link',
                style: { marginTop: '40px', color: '#94a3b8' },
                onClick: () => { if (confirm(__('Skip and enable all features?', 'manufacturing-erp-pro'))) finishSetup(false); }
            }, __('Skip Setup Wizard', 'manufacturing-erp-pro'))
        )
    );
};

const init = () => {
    const container = document.getElementById('mep-wizard-root');
    if (container) {
        if (wp.element.createRoot) { wp.element.createRoot(null), container).render(wp.element.createElement(SetupWizard); } else { wp.element.render(wp.element.createElement(SetupWizard, null), container); }
    }
};
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
})();
