# Manufacturing ERP Pro: Governance & Permission Matrix

This document outlines the Role-Based Access Control (RBAC) and security governance within Manufacturing ERP Pro.

## ERP Roles & Capabilities

The plugin registers four specialized roles upon activation. These roles are designed to match standard manufacturing organizational structures.

| Role | User Persona | Primary Capabilities |
|---|---|---|
| **ERP Administrator** | Factory Owner / IT Manager | Full system access, plugin settings, database resets, user management, and multi-warehouse setup. |
| **Production Manager** | Shop Floor Supervisor | Manage BOMs, Work Orders, Routes, and Capacity. Can release orders and track labor time. |
| **Warehouse Clerk** | Inventory Specialist | Bin transfers, material receipts, inventory movements, and stock counts. |
| **Quality Inspector** | QC Lead | Perform quality checks, generate NCRs (Non-Conformance Reports), and manage Lot Traceability. |

## Permission Matrix (By Module)

| Module | Administrator | Production Mgr | Warehouse Clerk | Quality Inspector |
|---|:---:|:---:|:---:|:---:|
| **Dashboard** | View All | View All | View Inventory | View Quality |
| **Materials** | Manage | View | Manage Stock | View |
| **BOMs** | Manage | Manage | View | View |
| **Work Orders** | Manage | Manage | View | View |
| **Inventory** | Manage | View | Manage | View |
| **Quality/QC** | Manage | View | View | Manage |
| **Procurement** | Manage | Request | View | View |
| **Equipment** | Manage | Manage | View | View |
| **System Tools** | Manage | Denied | Denied | Denied |

## Security & Integrity Measures

1. **Transaction Locking**: The system uses database-level pessimistic locking (`FOR UPDATE`) for all inventory movements to ensure data integrity during high-concurrency operations.
2. **Audit Trails**: Every administrative action and stock movement is recorded in `wp_mep_audit_logs`, capturing the User ID, Timestamp, and a JSON snapshot of the change.
3. **API Security**: All REST API endpoints are protected via `permission_callback` functions that verify the user's ERP role and nonce.
4. **Data Safeguards**: Destructive actions (like the Hard Reset) require a multi-step confirmation process including a typed safety phrase: `RESET PRODUCTION ENVIRONMENT`.
