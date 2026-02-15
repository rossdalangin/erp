(function() {
/**
 * MEP Setup Wizard - v2.0
 */

const { useState } = wp.element;
const { __ } = wp.i18n;

const SetupWizard = () => {
    const [step, setStep] = useState(1);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [status, setStatus] = useState('');

    const nextStep = () => setStep(step + 1);
    const prevStep = () => setStep(step - 1);

    const finishSetup = (withSeeding = false) => {
        setLoading(true);
        setError(null);
        setStatus(withSeeding ? __('Planting factory seeds...', 'manufacturing-erp-pro') : __('Initializing factory...', 'manufacturing-erp-pro'));

        const tasks = [];
        if (withSeeding) {
            tasks.push(wp.apiFetch({ path: '/mep/v1/seed', method: 'POST' }));
        }

        // Finalize setting
        tasks.push(wp.apiFetch({
            path: '/mep/v1/settings',
            method: 'POST',
            data: { mep_setup_complete: '1', mep_help_mode: 'on' }
        }));

        Promise.all(tasks)
            .then(() => {
                setStatus(__('Success! Launching your ERP...', 'manufacturing-erp-pro'));
                setTimeout(() => {
                    window.location.href = 'admin.php?page=mep-dashboard';
                }, 1500);
            })
            .catch(err => {
                setLoading(false);
                setError(err.message || __('Critical error during setup. Please try "Skip Setup" if this persists.', 'manufacturing-erp-pro'));
                console.error('MEP Wizard Error:', err);
            });
    };

    return wp.element.createElement('div', {
        className: 'mep-wizard-overlay mep-admin-style mep-animate-fade-in',
        style: {
            position: 'fixed', top: 0, left: 0, width: '100%', height: '100%',
            zIndex: 99999, display: 'flex', alignItems: 'center', justifyContent: 'center',
            padding: '20px', boxSizing: 'border-box'
        }
    },
        wp.element.createElement('div', {
            className: 'mep-wizard-card',
            style: {
                background: '#fff', padding: '50px', maxWidth: '650px', width: '100%',
                borderRadius: 'var(--mep-radius-lg)', boxShadow: 'var(--mep-shadow-lg)',
                textAlign: 'center'
            }
        },
            wp.element.createElement('header', { style: { marginBottom: '40px' } },
                wp.element.createElement('div', { style: { fontSize: '48px', marginBottom: '20px' } }, '🏭'),
                wp.element.createElement('h1', { style: { margin: '0 0 10px 0', fontSize: '32px', color: 'var(--mep-primary)' } }, __('Manufacturing ERP Pro', 'manufacturing-erp-pro')),
                wp.element.createElement('p', { style: { color: 'var(--mep-text-muted)', fontSize: '18px' } }, __('Factory Setup & Onboarding', 'manufacturing-erp-pro'))
            ),

            error && wp.element.createElement('div', {
                style: { background: '#fcf2f2', border: '1px solid #d63638', padding: '15px', color: '#d63638', borderRadius: '4px', marginBottom: '20px' }
            }, error),

            status && wp.element.createElement('div', {
                style: { background: '#f0f6fb', border: '1px solid #2271b1', padding: '15px', color: '#2271b1', borderRadius: '4px', marginBottom: '20px', fontWeight: 'bold' }
            },
                wp.element.createElement('span', { className: 'spinner is-active', style: { float: 'none', margin: '0 10px 0 0' } }),
                status
            ),

            !loading && step === 1 && wp.element.createElement('div', null,
                wp.element.createElement('h2', null, __('Step 1: Your Production Model', 'manufacturing-erp-pro')),
                wp.element.createElement('p', null, __('What kind of manufacturing facility do you run?', 'manufacturing-erp-pro')),
                wp.element.createElement('div', { style: { display: 'flex', gap: '15px', marginTop: '20px' } },
                    wp.element.createElement('button', {
                        className: 'button button-large',
                        style: { flex: 1, height: '100px', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' },
                        onClick: nextStep
                    },
                        wp.element.createElement('span', { style: { fontSize: '24px', marginBottom: '5px' } }, '👜'),
                        __('Discrete (Goods)', 'manufacturing-erp-pro')
                    ),
                    wp.element.createElement('button', {
                        className: 'button button-large',
                        style: { flex: 1, height: '100px', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' },
                        onClick: nextStep
                    },
                        wp.element.createElement('span', { style: { fontSize: '24px', marginBottom: '5px' } }, '⚗️'),
                        __('Process (Liquids/Mix)', 'manufacturing-erp-pro')
                    )
                )
            ),

            !loading && step === 2 && wp.element.createElement('div', null,
                wp.element.createElement('h2', null, __('Step 2: Initialize Factory Data', 'manufacturing-erp-pro')),
                wp.element.createElement('p', null, __('Choose how you would like to start using the system.', 'manufacturing-erp-pro')),

                wp.element.createElement('div', {
                    onClick: () => finishSetup(true),
                    style: { background: '#f6f7f7', border: '2px solid #2271b1', padding: '20px', borderRadius: '6px', cursor: 'pointer', marginBottom: '15px', textAlign: 'left' }
                },
                    wp.element.createElement('h4', { style: { margin: '0 0 5px 0', color: '#2271b1' } }, '🚀 ' + __('Demo Mode (Recommended)', 'manufacturing-erp-pro')),
                    wp.element.createElement('p', { style: { margin: 0, fontSize: '13px' } }, __('Instantly load LeatherCraft Co. sample data: Materials, BOMs, Work Orders, and Warehouses.', 'manufacturing-erp-pro'))
                ),

                wp.element.createElement('div', {
                    onClick: () => finishSetup(false),
                    style: { background: '#fff', border: '1px solid #ccc', padding: '20px', borderRadius: '6px', cursor: 'pointer', textAlign: 'left' }
                },
                    wp.element.createElement('h4', { style: { margin: '0 0 5px 0' } }, '🏢 ' + __('Clean Slate', 'manufacturing-erp-pro')),
                    wp.element.createElement('p', { style: { margin: 0, fontSize: '13px' } }, __('Start with an empty system and enter your own master data manually.', 'manufacturing-erp-pro'))
                ),

                wp.element.createElement('button', { className: 'button', style: { marginTop: '20px' }, onClick: prevStep }, __('Back', 'manufacturing-erp-pro'))
            ),

            !loading && wp.element.createElement('div', { style: { marginTop: '30px', borderTop: '1px solid #eee', paddingTop: '20px' } },
                wp.element.createElement('button', {
                    className: 'button button-link',
                    style: { color: '#999' },
                    onClick: () => {
                        if (confirm(__('Skip setup and enable all ERP features immediately?', 'manufacturing-erp-pro'))) {
                            finishSetup(false);
                        }
                    }
                }, __('Skip wizard and enable features', 'manufacturing-erp-pro'))
            )
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-wizard-root');
    if (container) {
        wp.element.render(wp.element.createElement(SetupWizard, null), container);
    }
});

})();