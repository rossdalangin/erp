/**
 * MEP Setup Wizard
 */

const { useState } = wp.element;
const { __ } = wp.i18n;

const SetupWizard = () => {
    const [step, setStep] = useState(1);
    const [status, setStatus] = useState('');

    const nextStep = () => setStep(step + 1);
    const prevStep = () => setStep(step - 1);

    const runSeeder = () => {
        setStatus(__('Seeding demo data...', 'manufacturing-erp-pro'));

        wp.apiFetch({
            path: '/mep/v1/seed',
            method: 'POST'
        }).then(() => {
            return wp.apiFetch({
                path: '/mep/v1/settings',
                method: 'POST',
                data: { mep_setup_complete: '1' }
            });
        }).then(() => {
            setStatus(__('Setup complete with demo data! Redirecting...', 'manufacturing-erp-pro'));
            setTimeout(() => { window.location.href = 'admin.php?page=mep-dashboard'; }, 1000);
        }).catch(err => {
            setStatus(__('Error during seeding. Please check your permissions and try again.', 'manufacturing-erp-pro'));
            console.error(err);
        });
    };

    const completeSetup = () => {
        setStatus(__('Finalizing setup...', 'manufacturing-erp-pro'));

        // Try REST API first
        wp.apiFetch({
            path: '/mep/v1/settings',
            method: 'POST',
            data: { mep_setup_complete: '1' }
        }).then(() => {
            setStep(3);
        }).catch(err => {
            console.warn('REST API failed, attempting fallback...', err);
            // If REST fails, we might be in an environment with REST disabled or misconfigured.
            // We can't easily do a form post from here without a reload, so we tell the user.
            setStatus(__('REST API Error. Please use the "Skip Setup" button on the Dashboard if this persists.', 'manufacturing-erp-pro'));
        });
    };

    return wp.element.createElement('div', { className: 'mep-wizard-container', style: { maxWidth: '800px', margin: '40px auto', background: '#fff', padding: '40px', border: '1px solid #ccd0d4', boxShadow: '0 1px 3px rgba(0,0,0,0.1)' } },
        wp.element.createElement('div', { className: 'mep-wizard-header', style: { textAlign: 'center', marginBottom: '40px' } },
            wp.element.createElement('h2', null, __('Welcome to Manufacturing ERP Pro', 'manufacturing-erp-pro')),
            wp.element.createElement('p', null, __('Let\'s get your factory up and running in minutes.', 'manufacturing-erp-pro'))
        ),

        wp.element.createElement('div', { className: 'mep-wizard-steps', style: { display: 'flex', justifyContent: 'space-between', marginBottom: '40px' } },
            [1, 2, 3].map(s => wp.element.createElement('div', {
                key: s,
                style: {
                    width: '30px',
                    height: '30px',
                    borderRadius: '50%',
                    background: step >= s ? '#2271b1' : '#eee',
                    color: step >= s ? '#fff' : '#666',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontWeight: 'bold'
                }
            }, s))
        ),

        step === 1 && wp.element.createElement('div', { className: 'mep-wizard-step' },
            wp.element.createElement('h3', null, __('Step 1: Company Profile', 'manufacturing-erp-pro')),
            wp.element.createElement('p', null, __('Enter your primary manufacturing focus.', 'manufacturing-erp-pro')),
            wp.element.createElement('select', { className: 'widefat', style: { marginBottom: '20px' } },
                wp.element.createElement('option', null, __('Discrete Manufacturing (Bags, Furniture, Electronics)', 'manufacturing-erp-pro')),
                wp.element.createElement('option', null, __('Process Manufacturing (Chemicals, Food, Liquids)', 'manufacturing-erp-pro'))
            ),
            wp.element.createElement('button', { className: 'button button-primary', onClick: nextStep }, __('Next', 'manufacturing-erp-pro'))
        ),

        step === 2 && wp.element.createElement('div', { className: 'mep-wizard-step' },
            wp.element.createElement('h3', null, __('Step 2: Experience Mode', 'manufacturing-erp-pro')),
            wp.element.createElement('p', null, __('How would you like to start?', 'manufacturing-erp-pro')),
            wp.element.createElement('div', { style: { display: 'flex', gap: '20px' } },
                wp.element.createElement('div', { style: { flex: 1, padding: '20px', border: '2px solid #eee', cursor: 'pointer' }, onClick: completeSetup },
                    wp.element.createElement('h4', null, __('Clean Slate', 'manufacturing-erp-pro')),
                    wp.element.createElement('p', null, __('I will enter my own materials and products.', 'manufacturing-erp-pro'))
                ),
                wp.element.createElement('div', { style: { flex: 1, padding: '20px', border: '2px solid #2271b1', cursor: 'pointer' }, onClick: runSeeder },
                    wp.element.createElement('h4', null, __('Demo Mode', 'manufacturing-erp-pro')),
                    wp.element.createElement('p', null, __('Load LeatherCraft Co. sample data to explore.', 'manufacturing-erp-pro'))
                )
            ),
            wp.element.createElement('p', { style: { color: '#d63638', marginTop: '10px' } }, status),
            wp.element.createElement('button', { className: 'button', onClick: prevStep, style: { marginTop: '20px' } }, __('Back', 'manufacturing-erp-pro'))
        ),

        step === 3 && wp.element.createElement('div', { className: 'mep-wizard-step' },
            wp.element.createElement('h3', null, __('Step 3: Ready to Launch', 'manufacturing-erp-pro')),
            wp.element.createElement('p', null, __('You are ready to start manufacturing. We have created your Shop Floor and Customer Portal pages automatically.', 'manufacturing-erp-pro')),
            wp.element.createElement('button', { className: 'button button-primary', onClick: () => window.location.href = 'admin.php?page=mep-dashboard' }, __('Go to Dashboard', 'manufacturing-erp-pro'))
        ),

        wp.element.createElement('div', { style: { marginTop: '40px', paddingTop: '20px', borderTop: '1px solid #eee', textAlign: 'center' } },
            wp.element.createElement('button', {
                className: 'button button-link',
                onClick: () => {
                    if (confirm(__('Skip setup and enable all features immediately?', 'manufacturing-erp-pro'))) {
                        wp.apiFetch({ path: '/mep/v1/settings', method: 'POST', data: { mep_setup_complete: '1' } })
                        .then(() => window.location.href = 'admin.php?page=mep-dashboard');
                    }
                }
            }, __('Skip Setup (Advanced Users)', 'manufacturing-erp-pro'))
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-wizard-root');
    if (container) {
        wp.element.render(wp.element.createElement(SetupWizard, null), container);
    }
});
