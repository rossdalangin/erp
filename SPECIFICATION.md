# Manufacturing ERP Pro - COMPLETE SYSTEM SPECIFICATION

## Table of Contents
1. [Core System Architecture](#1-core-system-architecture)
2. [Custom Post Types (CPTs) & Schemas](#2-custom-post-types-cpts--schemas)
3. [Custom Database Tables](#3-custom-database-tables)
4. [UX & UI Design Specifications](#4-ux--ui-design-specifications)
5. [MRP & Production Engine Specifications](#5-mrp--production-engine-specifications)
6. [Quality, Traceability & Procurement](#6-quality-traceability--procurement)
7. [Reporting, Onboarding & Governance](#7-reporting-onboarding--governance)
8. [Admin Tools, Safeguards & Sample Data](#8-admin-tools-safeguards--sample-data)
9. [API, Data Flow & Sample JSON](#9-api-data-flow--sample-json)

---

# 1. Core System Architecture

## Plugin Name: Manufacturing ERP Pro

### Modular Architecture
**Business Value**: Scalability without complexity. Only use the modules you need today, and expand as your factory grows.
- **Inventory Module**: Ensures real-time visibility. Eliminates "missing stock" surprises.
- **Production Module**: Digitalizes your factory floor. Know exactly what's being cut and stitched.
- **MRP Module**: Material Requirements Planning. Tells you exactly what to buy and when.
- **Procurement Module**: Automates vendor relationships. Turns material shortages into POs.
- **Quality Module**: Brand protection. Ensures only high-quality goods reach customers.
- **Reporting Module**: Command center. Data-driven decision making.

### Data Handling Strategy
- **WordPress Native (CPTs)**: Master data for easy management.
- **Custom DB Tables**: High-volume transactional data optimized for performance.
- **REST API-First**: Modern decoupled design for React-based visual builders.

---

# 2. Custom Post Types (CPTs) & Schemas

| CPT Slug | Name | Operational Workflow | Meta Fields | Status Lifecycle |
|---|---|---|---|---|
| `mep_material` | Materials | **Stock Tracking** | `sku`, `uom`, `cost_avg`, `lead_time`, `safety_stock` | Active, Archived |
| `mep_product` | Products | **Product Catalog** | `sku`, `category`, `weight` | Draft, Active, EOL |
| `mep_bom` | BOM | **Product Recipe** | `components` (JSON), `version` | Draft, Active, Superseded |
| `mep_work_order` | Work Orders | **Production Instruction** | `qty`, `start_date`, `assigned_to` | Draft, Released, In Progress, Completed |
| `mep_batch` | Batches | **Lot Management** | `batch_code`, `expiry_date` | Scheduled, Running, Finished |
| `mep_warehouse` | Warehouses | **Multi-Site Control** | `location_code`, `capacity` | Active, Inactive |
| `mep_bin` | Bins | **Micro-Location** | `bin_type`, `dimensions` | Active, Full, Inactive |
| `mep_supplier` | Suppliers | **Vendor CRM** | `contact`, `terms`, `lead_time_avg` | Active, Probation, Blacklisted |
| `mep_po` | Purchase Orders| **Buying Control** | `items`, `total_cost`, `expected_date` | Draft, Sent, Received, Closed |
| `mep_qc_check` | Quality Checks| **Inspection Gateway**| `results`, `inspector_id`, `object_id` | Pending, Pass, Fail |
| `mep_ncr` | NCR | **Problem Solving** | `defect_type`, `resolution` | Open, Investigating, Closed |
| `mep_equipment` | Equipment | **Asset Tracking** | `serial_num`, `maintenance_log` | Available, Maintenance, Down |
| `mep_route` | Routes | **Factory Map** | `steps` (JSON), `estimated_total_time` | Active, Inactive |
| `mep_forecast` | Forecasts | **Future Planning** | `period`, `forecast_qty` | Draft, Approved |

---

# 3. Custom Database Tables

### `wp_mep_inventory_transactions`
Tracks every movement of material.
- `id`: BIGINT (PK)
- `material_id`: BIGINT (FK to mep_material)
- `warehouse_id`: BIGINT (FK to mep_warehouse)
- `bin_id`: BIGINT (FK to mep_bin)
- `quantity`: DECIMAL(18,4)
- `transaction_type`: VARCHAR(50) (RECEIVE, ISSUE, TRANSFER, ADJUST)
- `reference_id`: BIGINT (PO or Work Order)
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

---

# 4. UX & UI Design Specifications

## 1. Visual BOM & Routing Builder (Drag-and-Drop)
The BOM Builder is a React-based interactive canvas.
- **Interactions**: Drag materials from sidebar into trees. Expanded/collapsed sub-assemblies.
- **Features**: Real-time cost roll-up, version control, and yield loss factor per step.

## 2. Visual Warehouse & Inventory Management
- **Grid Layout**: 2D view of factory floor. Color-coded bin occupancy.
- **Drag-and-Drop Transfers**: Move stock cards between bins visually.

## 3. Production Planning Board (Kanban)
- **Columns**: Backlog, Ready, In Progress, QC, Completed.
- **Logic**: Drag to advance status, assign materials/machines, and track labor time.

---

# 5. MRP & Production Engine Specifications

## 1. MRP (Material Requirements Planning) Engine
**Simplified Logic for SMEs**: Explodes BOMs based on forecasts and nets requirements against current inventory.
- **Explosion Logic**: Recursive traversal of multi-level BOMs.
- **Netting**: `Net = Gross - (On Hand + Scheduled - Reserved)`.

---

# 6. Quality, Traceability & Procurement

## 1. Traceability & Lot Genealogy
Node-based historical graph showing the journey of a specific Lot from Raw Material to Finished Good.

## 2. Procurement & Supplier Management
- **Supplier Scoring**: Automated tracking of Quality Rate, OTD (On-Time Delivery), and Price Variance.
- **Auto-PO**: Generates POs from MRP suggestions with one-click approval.

---

# 7. Reporting, Onboarding & Governance

## 1. Executive Dashboard
Real-time tiles for Production Output, Scrap Rate, Inventory Value, and OEE.

## 2. Onboarding & Demo Mode
Multi-step Setup Wizard for UOM, Warehouse setup, and Sample Data ("LeatherCraft Co.") injection.

## 3. Security & Governance
Role-Based Access Control (RBAC) with dedicated roles: Production Manager, Warehouse Clerk, Quality Inspector.

---

# 8. Admin Tools, Safeguards & Sample Data

## 1. System Settings & Safeguards
- **Hard Reset**: Full wipe with typed phrase confirmation: `RESET PRODUCTION ENVIRONMENT`.
- **Soft Reset**: Wipes transactions but keeps master data.

## 2. Sample Data Seeder (LeatherCraft Manufacturing Co.)
Comprehensive environment with multi-level BOMs, historical transactions, and active Work Orders.

---

# 9. API, Data Flow & Sample JSON

## 1. REST API Endpoints
- `/materials` (GET)
- `/work-orders` (GET/POST)
- `/bom/{id}` (GET/POST)
- `/mrp/run` (POST)
- `/reports/kpis` (GET)

## 2. Data Flow: Production Workflow
`[Forecast] -> [MRP Engine] -> [Purchase/Work Orders] -> [Inventory Issuance] -> [Production] -> [QC] -> [Stock]`
