# 1. Core System Architecture

## Plugin Name: Manufacturing ERP Pro

### Modular Architecture
The plugin is designed with a decoupled, modular architecture to ensure scalability and ease of maintenance.
- **Inventory Module**: Manages raw materials, finished goods, warehouses, and bins.
- **Production Module**: Manages BOMs, Work Orders, Batches, and Routing.
- **MRP Module**: Material Requirements Planning engine for demand and supply balancing.
- **Procurement Module**: Supplier management and Purchase Orders.
- **Quality Module**: QC checks and Non-Conformance Reports (NCR).
- **Reporting Module**: Dashboards and KPI tracking.
- **Settings & Utilities**: System configuration, Audit Logs, and Data Seeder.

### Data Handling Strategy
- **WordPress Native (CPTs)**: Master data (Products, Materials, Suppliers) are stored as Custom Post Types for easy management and compatibility with the WP ecosystem.
- **Custom DB Tables**: High-volume transactional data (Inventory movements, Production logs, Audit trails) are stored in custom tables to optimize performance and ensure data integrity.
- **REST API-First**: All backend logic is exposed via a robust REST API, enabling the development of decoupled React/Vue based front-end components and mobile applications.

### Performance & Scalability
- **Background Workers**: Heavy computations like MRP explosion and Cost Roll-ups are offloaded to background workers using Action Scheduler or custom WP-Cron handlers.
- **Transaction Locking**: Implementation of optimistic/pessimistic locking for inventory transactions to prevent race conditions.
- **Audit Logs**: Every change to transactional data is logged in a dedicated audit table for compliance and traceability.

# 2. Custom Post Types (CPTs) & Schemas

| CPT Slug | Name | Description | Parent/Child | Status Lifecycle |
|---|---|---|---|---|
| `mep_material` | Materials | Raw materials & components | N/A | Active, Archived |
| `mep_product` | Products | Finished goods (SKUs) | N/A | Draft, Active, EOL |
| `mep_bom` | BOM | Bill of Materials | `mep_product` | Draft, Active, Superseded |
| `mep_work_order` | Work Orders | Production instructions | N/A | Draft, Released, In Progress, Completed, Cancelled |
| `mep_batch` | Batches | Production batches | `mep_work_order` | Scheduled, Running, Finished |
| `mep_warehouse` | Warehouses | Storage locations | N/A | Active, Inactive |
| `mep_bin` | Bins | Specific locations within WH | `mep_warehouse` | Active, Full, Inactive |
| `mep_supplier` | Suppliers | Material vendors | N/A | Active, Probation, Blacklisted |
| `mep_po` | Purchase Orders| Procurement documents | N/A | Draft, Sent, Received, Closed |
| `mep_qc_check` | Quality Checks| Quality inspection records | N/A | Pending, Pass, Fail |
| `mep_ncr` | NCR | Non-Conformance Reports | `mep_qc_check`| Open, Investigating, Closed |
| `mep_equipment` | Equipment | Machines & Tools | N/A | Available, Maintenance, Down |
| `mep_route` | Routes | Production steps | `mep_product` | Active, Inactive |
| `mep_forecast` | Forecasts | Demand planning | N/A | Draft, Approved |

### Detailed CPT Meta Fields (Example: `mep_material`)
- `_mep_sku`: Unique SKU string.
- `_mep_uom`: Unit of Measure (ea, kg, m, etc).
- `_mep_cost_avg`: Weighted average cost.
- `_mep_lead_time`: Lead time in days.
- `_mep_safety_stock`: Minimum stock level.

# 3. Custom Database Tables

### `wp_mep_inventory_transactions`
Tracks every movement of material.
- `id`: BIGINT (Primary Key)
- `material_id`: BIGINT (FK to mep_material)
- `warehouse_id`: BIGINT (FK to mep_warehouse)
- `bin_id`: BIGINT (FK to mep_bin)
- `quantity`: DECIMAL(18,4)
- `transaction_type`: VARCHAR(50) (e.g., 'RECEIVE', 'ISSUE', 'TRANSFER', 'ADJUST')
- `reference_id`: BIGINT (e.g., PO ID or Work Order ID)
- `lot_number`: VARCHAR(100)
- `created_at`: DATETIME

### `wp_mep_audit_logs`
- `id`: BIGINT
- `user_id`: BIGINT
- `object_type`: VARCHAR(50)
- `object_id`: BIGINT
- `action`: VARCHAR(50)
- `old_value`: LONGTEXT
- `new_value`: LONGTEXT
- `created_at`: DATETIME

### `wp_mep_production_logs`
Tracks labor and machine time per operation.
- `id`: BIGINT
- `work_order_id`: BIGINT
- `operation_id`: BIGINT
- `resource_id`: BIGINT (Equipment or User)
- `start_time`: DATETIME
- `end_time`: DATETIME
- `output_qty`: DECIMAL(18,4)
- `scrap_qty`: DECIMAL(18,4)
