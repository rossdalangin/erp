# Reporting, Onboarding & Governance

## 1. Reporting & Dashboards

### Executive Dashboard
- **Production KPI Tiles**: OEE (Overall Equipment Effectiveness), Total Output, Scrap Rate.
- **Inventory Health**: Stock Value, Turn Rate, % of Items with Safety Stock violations.
- **Financials**: Cost Variance (Standard vs. Actual), PO Spend by Category.

### Operational Reports
- **WIP Report**: Current Work Orders on the floor and their status.
- **Material Shortage Report**: Generated from MRP, highlighting critical items.
- **Lot Genealogy PDF**: One-click export for compliance audits.

## 2. Onboarding & Demo Mode

### First-Run Wizard
1. **Business Type Selection**: Discrete vs. Light Process.
2. **UOM Configuration**: Define base units (e.g., metric vs imperial).
3. **Location Setup**: Create first Warehouse and Bins.
4. **Data Seed Option**: "Start with Sample Data (LeatherCraft Co.)".

### Demo Mode
- **Visual Badge**: A "Demo Mode Active" badge displayed in the Admin Bar.
- **Restricted Actions**: Prevention of certain system-wide settings changes while in Demo mode.
- **Cleanup**: Button to "Wipe Demo Data" while keeping custom configuration.

## 3. Security & Governance

### Role-Based Access Control (RBAC)
Custom roles defined within the plugin:
- **ERP Administrator**: Full access.
- **Production Manager**: Manage BOMs, Work Orders, and Routes.
- **Warehouse Clerk**: Bin transfers, Receipts, and Issuance.
- **Quality Inspector**: View QC tasks and record results.
- **Procurement Officer**: Manage Suppliers and POs.

### Permission Matrix
- Granular control over "View", "Create", "Edit", "Approve", and "Delete" for each CPT and Custom Table.

### Transaction Locking & Integrity
- **Concurrency Control**: Version numbers on Work Orders to prevent two managers from updating the same record simultaneously.
- **Period Closing**: Ability to "Close" a month, preventing any inventory transactions in the past.

### Audit Logs & Traceability
- Every entry in the `wp_mep_audit_logs` table includes the User ID, Timestamp, IP Address, and a "Before/After" snapshot of the modified data.
