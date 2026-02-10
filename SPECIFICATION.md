# Manufacturing ERP Pro - Complete Specification

## Table of Contents
1. [Core System Architecture](#1-core-system-architecture)
2. [Custom Post Types (CPTs) & Schemas](#2-custom-post-types-cpts--schemas)
3. [Custom Database Tables](#3-custom-database-tables)
4. [UX & UI Design Specifications](#ux--ui-design-specifications)
5. [MRP & Production Engine Specifications](#mrp--production-engine-specifications)
6. [Quality, Traceability & Procurement](#quality-traceability--procurement)
7. [Reporting, Onboarding & Governance](#reporting-onboarding--governance)
8. [Admin Tools, Safeguards & Sample Data](#admin-tools-safeguards--sample-data)
9. [API, Data Flow & Sample JSON](#api-data-flow--sample-json)

---

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

---

# UX & UI Design Specifications

## 1. Visual BOM & Routing Builder (Drag-and-Drop)

The BOM Builder is a React-based interactive canvas that allows engineers to design product structures and production steps visually.

### Components:
- **Left Sidebar (Library)**: Contains draggable Raw Materials, Sub-assemblies, and Operations (e.g., Cutting, Stitching).
- **Central Canvas (Tree/Flow View)**:
  - **Tree Node**: Represents a material or sub-assembly.
  - **Connection Lines**: Show parent-child relationships.
  - **Operation Blocks**: Integrated into the tree to show where materials are consumed.
- **Right Sidebar (Properties)**: Context-aware panel to edit quantities, scrap rates, and specific operation details (machine time, labor time).

### Key Interactions:
- **Drag Materials into Tree**: Dropping a material onto an assembly node adds it to the BOM.
- **Nested Assemblies**: Expand/collapse sub-assemblies (e.g., "Strap Assembly" inside "Leather Bag").
- **Drag Reorder Operations**: Change the sequence of routing steps by dragging blocks up or down.
- **Real-time Cost Roll-up**: As components are added or quantities changed, the "Total Estimated Cost" updates instantly in the header.
- **Substitute Management**: Drag an alternative material onto an existing BOM line to create a "Substitute Group".

## 2. Visual Warehouse & Inventory Management

### Visual Warehouse Layout:
- **Grid View**: A 2D bird's-eye view of the warehouse floor.
- **Bin Status**: Bins are color-coded based on occupancy (Empty: Green, Partial: Yellow, Full: Red).
- **Drag-and-Drop Bin Transfers**: Move stock between bins by dragging a "Stock Card" from one bin to another. This triggers a modal to specify quantity and lot.

### Inventory Command Center:
- **Reservation View**: Visual indicators showing stock that is "On Hand" vs "Reserved" for specific Work Orders.
- **Aging Heatmap**: Highlights older stock (FIFO enforcement) to ensure material rotation.

## 3. Production Planning Board (Kanban)

A Trello-style board for managing the lifecycle of Work Orders.

### Kanban Columns:
1. **Backlog**: New Work Orders awaiting material release.
2. **Ready**: Materials are reserved/staged; ready to start.
3. **In Progress**: Active production.
4. **QC / Inspection**: Finished goods undergoing quality checks.
5. **Completed**: Finished and moved to stock.

### Drag-and-Drop Actions:
- **Prioritization**: Drag cards up/down within a column to change production priority.
- **Status Change**: Drag a Work Order from "Ready" to "In Progress" to trigger material issuance and start time tracking.
- **Operator Assignment**: Drag "User Avatars" onto a Work Order card to assign labor resources.

## 4. Mobile & Tablet Optimization
- **Responsive Tables**: Horizontal scrolling with "sticky" first columns (SKU/ID).
- **Scan-to-Action**: Floating barcode icon that opens the camera to scan Lot numbers or Bin IDs.
- **Large Touch Targets**: Button sizes and spacing optimized for use on the production floor with gloves.

---

# MRP & Production Engine Specifications

## 1. MRP (Material Requirements Planning) Engine

The MRP engine is the "brain" of the ERP, responsible for calculating what to buy and what to make based on demand (Forecasts + Orders) and current supply (Inventory + Open POs + Open Work Orders).

### MRP Explosion Logic:
1. **Demand Aggregation**: Collect all active Forecasts (`mep_forecast`) and Sales Orders (if integrated).
2. **Gross Requirements**: Break down the demand for finished goods into their constituent parts using the active BOM (`mep_bom`).
3. **Netting**:
   - `Net Requirements = Gross Requirements - (Inventory On Hand + Scheduled Receipts - Reserved Stock)`.
4. **Lot Sizing**: Apply safety stock rules and minimum order quantities (MOQ) from Material/Product meta.
5. **Time Phasing**: Calculate the "Start Date" for Production/Procurement by back-scheduling from the "Need Date" using Lead Times.

### MRP Output:
- **Planned Purchase Orders**: Suggested POs for raw materials.
- **Planned Work Orders**: Suggested production runs for sub-assemblies and finished goods.

## 2. Demand Planning & Pegging

### Forecast CPT
- Supports Weekly or Monthly granularity.
- Can be "Exploded" to see the underlying material demand before committing to actual orders.

### Pegging View
A visual trace (demand → BOM → materials) that shows *why* a material is being requested.
- **User Action**: Click on a "Suggested PO Line" to see which Work Order or Forecast entry generated that demand.
- **Visual**: A tree-like structure showing the link from the Raw Material up to the final Finished Good.

## 3. Capacity Planning (RCCP)

Rough-Cut Capacity Planning ensures that the production schedule is realistic given machine and labor availability.

- **Load vs. Capacity Chart**: A bar chart for each Work Center (Equipment Group) showing available hours vs. scheduled hours.
- **Overload Alerts**: Visual warning (Red highlighting) on the Planning Board if an operation is scheduled on a machine that is already at 100% capacity.

## 4. Performance: Background Workers

MRP and Costing are computationally expensive. These processes are executed asynchronously.

### Action Scheduler Integration:
- **`mep_run_mrp`**: A background task triggered manually or on a nightly schedule.
- **Progress Tracking**: A progress bar in the Admin UI that polls a custom DB table (`wp_mep_background_tasks`) to show the status of the current MRP run.
- **Scalability**: Large BOMs are exploded in chunks to avoid PHP timeouts and memory exhaustion.

## 5. Cost Roll-up Logic
- **Real-time Costing**: As material costs change (from PO receipts), the "Standard Cost" of finished goods is updated via a background cost-roll-up task.
- **Yield/Scrap Factor**: Costs are adjusted by the `yield_loss` percentage defined in the BOM operations.

---

# Quality, Traceability & Procurement

## 1. Traceability & Lot Genealogy

Ensuring complete visibility from raw material receipt to finished product delivery.

### Lot & Batch Genealogy Graph
- **Visualization**: A node-based graph (similar to the BOM builder but historical) showing the journey of a specific Lot.
- **Upstream Trace**: Start from a Finished Good Lot and see every Raw Material Lot used in its production.
- **Downstream Trace**: Start from a Raw Material Lot and see every Finished Good it was consumed in (Critical for Recalls).
- **Interactions**: Click on any node to view the associated QC records, Material movements, and Operator logs.

### Quality Control (QC)
- **QC Checkpoints**: Definable steps in a Production Route or PO Receipt that require manual or automated inspection.
- **Defect Categories**: Standardized list of defects (e.g., "Scratched Leather", "Broken Stitching") for consistent reporting.
- **CAPA (Corrective and Preventive Action)**: Workflow triggered by a "Fail" QC status.
  1. **NCR Creation**: Auto-generate a Non-Conformance Report.
  2. **Quarantine**: Material is moved to a virtual "Quarantine Bin" and blocked from use.
  3. **Disposition**: Manager decides to "Rework", "Scrap", or "Use As-Is".

## 2. Procurement & Supplier Management

### Supplier Performance Scorecard
- **KPIs**:
  - **Quality Rate**: % of batches passing QC on first receipt.
  - **On-Time Delivery (OTD)**: % of POs delivered by the promised lead time.
  - **Price Variance**: Difference between quoted cost and actual invoice cost.
- **Supplier Status**: Automatically downgrades suppliers to "Probation" if scores fall below a threshold.

### Purchase Order (PO) Lifecycle
1. **Suggested (MRP)**: Drafted by the MRP engine.
2. **Pending Approval**: Review by Procurement Manager.
3. **Sent**: Transmitted to Supplier (via PDF or REST API integration).
4. **Partial Receipt**: Highlighting remaining quantities.
5. **Closed**: Fully received and invoiced.

### Auto-PO Generation
- **Logic**: Based on "Safety Stock" and "Reorder Points".
- **Bundling**: The system attempts to bundle materials from the same supplier into a single PO to save on shipping costs and meet MOQs.
- **Drag-and-Drop PO Builder**: Users can drag "Material Shortage Cards" from the MRP suggestion dashboard directly into a new PO.

---

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

---

# Admin Tools, Safeguards & Sample Data

## 1. System Settings & Safeguards

The "System Utilities" module is restricted to Super Admins and contains high-risk operations.

### Database Reset Utility
- **Hard Reset**: Deletes all plugin-related CPTs, Meta, and Custom Tables. Resets the plugin to a "Just Installed" state.
- **Soft Reset**: Keeps master data (Materials, Products, BOMs, Suppliers, Warehouses) but wipes all transactional data (Inventory movements, Work Orders, POs, Production logs).

#### Safety Protocols:
1. **Multi-Step Confirmation**:
   - Step 1: Checkbox "I understand this action is irreversible".
   - Step 2: User must type the phrase: `RESET PRODUCTION ENVIRONMENT`.
2. **Automatic Backup**: Triggers a partial DB export of ERP tables before execution.
3. **Role Restriction**: Visible only to users with `manage_options` and specific ERP Admin capabilities.

## 2. Sample Data Seeder (LeatherCraft Manufacturing Co.)

Designed for demoing and testing, the seeder populates the ERP with a realistic scenario.

### Sample Company Profile
- **Name**: LeatherCraft Manufacturing Co.
- **Industry**: High-end leather goods (Bags & Slippers).

### Seeded Master Data:
- **Warehouses**: `Main Warehouse`, `Production Floor`.
- **Bins**: `Leather Storage (A1)`, `Cutting Area (P1)`, `Assembly Area (P2)`.
- **Suppliers**: `Leather Supplier A`, `Sole Supplier B`.
- **Materials**:
  - Cowhide leather (m2)
  - PU leather (m2)
  - Rubber soles (pair)
  - Thread (spool)
  - Zippers (ea)
- **Products**:
  - "Signature Leather Handbag" (Variants: Tan, Black, Chocolate)
  - "Classic Leather Slippers" (Variants: S, M, L)
- **BOMs & Routes**:
  - Bag BOM: Body assembly + Strap assembly + Lining.
  - Slipper BOM: Sole assembly + Upper.
  - Route: Cutting → Stitching → Assembly → QC → Packing.

### Seeded Transactional Data:
- **Inventory Levels**: Initial stock for all raw materials.
- **Work Orders**: 2 active Work Orders (1 in "Stitching", 1 in "Ready").
- **Purchase Orders**: 1 "Received" PO and 1 "Pending" PO for Leather.
- **QC Records**: Historical "Pass" and "Fail" records for the Handbag product.

### Seeder Implementation:
- **Reversibility**: All seeded records are tagged with a meta key `_mep_is_sample_data = 1`.
- **Prevention**: The seeder checks if master data already exists to prevent duplication.
- **One-Click Cleanup**: A button in Admin to "Remove All Sample Data" based on the meta tag.

---

# API, Data Flow & Sample JSON

## 1. REST API Endpoints

The plugin exposes a versioned REST API under `/wp-json/mep/v1/`.

| Endpoint | Method | Description |
|---|---|---|
| `/materials` | GET | List all materials with current inventory. |
| `/materials/{id}` | GET | Get material details & transaction history. |
| `/inventory/transfer` | POST | Execute a bin-to-bin transfer. |
| `/work-orders` | GET | List work orders (filterable by status). |
| `/work-orders/{id}/start` | POST| Trigger start of production (Material issuance). |
| `/bom/calculate-cost` | POST | Calculate roll-up cost for a given BOM structure. |
| `/mrp/run` | POST | Trigger a background MRP calculation. |
| `/reports/kpis` | GET | Fetch dashboard KPI data. |

## 2. Sample JSON Objects

### Material Object (`mep_material`)
```json
{
  "id": 1025,
  "sku": "LTH-COW-001",
  "name": "Premium Cowhide Leather",
  "uom": "m2",
  "cost_avg": 45.50,
  "inventory": {
    "on_hand": 150.00,
    "reserved": 25.0,
    "available": 125.0
  },
  "lead_time": 14,
  "safety_stock": 50.0
}
```

### BOM Object (`mep_bom`)
```json
{
  "product_id": 2040,
  "version": "1.2",
  "components": [
    {
      "material_id": 1025,
      "qty_per": 1.5,
      "uom": "m2",
      "scrap_factor": 0.05
    },
    {
      "material_id": 1088,
      "qty_per": 1,
      "uom": "ea",
      "scrap_factor": 0
    }
  ],
  "routing": [
    {"step": 1, "op": "Cutting", "work_center": "WC-01", "time": 30},
    {"step": 2, "op": "Stitching", "work_center": "WC-02", "time": 120}
  ]
}
```

## 3. Data Flow Diagrams

### Production Workflow
```text
[Forecast/Order]
      ↓
[MRP Engine] → [Purchase Suggestions] → [Purchase Order] → [Receipt/Inventory]
      ↓
[Planned Work Order]
      ↓
[Release Materials] → [Inventory Issuance]
      ↓
[Production Steps] → [Labor/Machine Logs]
      ↓
[QC Inspection] → [Pass/Fail]
      ↓
[Finished Goods Inventory]
```

### Inventory Transaction Flow
```text
[Trigger Action (PO Receipt/WO Issue/Transfer)]
      ↓
[Validate Availability & Locks]
      ↓
[Write to Custom DB: wp_mep_inventory_transactions]
      ↓
[Update Cache & Inventory Meta]
      ↓
[Write to Audit Log]
```

## 4. Admin Safeguard Flow (Hard Reset)
```text
[Admin Clicks Reset]
      ↓
[Verify Super Admin Role]
      ↓
[Display Checkbox & Phrase Input]
      ↓
[User Types "RESET PRODUCTION ENVIRONMENT"]
      ↓
[System Triggers Background DB Export]
      ↓
[System Drops Custom Tables & Deletes CPTs via SQL for Speed]
      ↓
[Clear Cache & Redirect to Wizard]
```
