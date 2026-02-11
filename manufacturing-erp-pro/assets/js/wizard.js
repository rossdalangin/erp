/**
 * MEP Setup Wizard - Multi-step Onboarding
 */

const { useState } = wp.element;

const Wizard = () => {
    const [step, setStep] = useState(1);
    const [data, setData] = useState({ uom: 'metric', valuation: 'fifo', warehouse: 'Main Factory' });

    const nextStep = () => setStep(step + 1);
    const prevStep = () => setStep(step - 1);

    const runSeeder = () => {
        // Trigger seeder via existing utility logic (simulated via API or direct form submission if needed)
        alert('Seeding LeatherCraft Co. sample data...');
        window.location.href = 'admin.php?page=mep-utilities';
    };

    return wp.element.createElement('div', {
        className: 'mep-wizard-card',
        style: { background: '#fff', padding: '40px', maxWidth: '600px', margin: '40px auto', border: '1px solid #ccc', borderRadius: '8px' }
    },
        wp.element.createElement('div', { className: 'mep-wizard-progress', style: { marginBottom: '30px', color: '#999' } },
            `Step ${step} of 3`
        ),

        step === 1 && wp.element.createElement('div', null,
            wp.element.createElement('h2', null, 'Welcome to Manufacturing ERP Pro'),
            wp.element.createElement('p', null, 'Select your default Unit of Measure system:'),
            wp.element.createElement('select', {
                value: data.uom,
                onChange: (e) => setData({ ...data, uom: e.target.value }),
                style: { width: '100%', padding: '10px', marginBottom: '20px' }
            },
                wp.element.createElement('option', { value: 'metric' }, 'Metric (kg, m, cm)'),
                wp.element.createElement('option', { value: 'imperial' }, 'Imperial (lb, ft, in)')
            ),
            wp.element.createElement('p', null, 'Default Inventory Valuation:'),
            wp.element.createElement('select', {
                value: data.valuation,
                onChange: (e) => setData({ ...data, valuation: e.target.value }),
                style: { width: '100%', padding: '10px', marginBottom: '20px' }
            },
                wp.element.createElement('option', { value: 'fifo' }, 'FIFO (First-In-First-Out)'),
                wp.element.createElement('option', { value: 'lifo' }, 'LIFO (Last-In-First-Out)')
            ),
            wp.element.createElement('button', { className: 'button button-primary', onClick: nextStep }, 'Next: Locations →')
        ),

        step === 2 && wp.element.createElement('div', null,
            wp.element.createElement('h2', null, 'Warehouse Setup'),
            wp.element.createElement('p', null, 'Give your first warehouse a name:'),
            wp.element.createElement('input', {
                type: 'text',
                value: data.warehouse,
                onChange: (e) => setData({ ...data, warehouse: e.target.value }),
                style: { width: '100%', padding: '10px', marginBottom: '20px' }
            }),
            wp.element.createElement('div', { style: { display: 'flex', gap: '10px' } },
                wp.element.createElement('button', { className: 'button', onClick: prevStep }, '← Back'),
                wp.element.createElement('button', { className: 'button button-primary', onClick: nextStep }, 'Next: Finalize →')
            )
        ),

        step === 3 && wp.element.createElement('div', null,
            wp.element.createElement('h2', null, 'You’re Ready to Manufacture!'),
            wp.element.createElement('p', null, 'Would you like to start with sample data for "LeatherCraft Co." to explore the features?'),
            wp.element.createElement('div', { style: { display: 'flex', gap: '10px', marginTop: '20px' } },
                wp.element.createElement('button', { className: 'button button-primary', onClick: runSeeder }, 'Yes, Seed Sample Data'),
                wp.element.createElement('button', { className: 'button', onClick: () => window.location.href = 'admin.php?page=mep-dashboard' }, 'No, Start Fresh')
            )
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('mep-wizard-root');
    if (container) {
        wp.element.render(wp.element.createElement(Wizard, null), container);
    }
});
