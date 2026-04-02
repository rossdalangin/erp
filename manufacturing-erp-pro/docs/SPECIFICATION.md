# Manufacturing ERP Pro - COMPLETE SYSTEM SPECIFICATION (v1.7.0)

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
- **WordPress Native (CPTs)**: Master data (Products, Materials, Suppliers) for easy management.
- **Custom DB Tables**: High-volume transactional data (Stock moves, Production logs) optimized for performance.
- **REST API-First**: Modern decoupled design for React-based visual builders.

---

# 2. Custom Post Types (CPTs) & Schemas

| CPT Slug | Name | Operational Workflow | Meta Fields | Status Lifecycle |
|---|---|---|---|---|
| `mep_material` | Materials | **Stock Tracking** | `sku`, `uom`, `cost_avg`, `safety_stock`, `lead_time`, `supplier_id` | Active, Archived |
| `mep_product` | Products | **Product Catalog** | `sku`, `category`, `weight`, `price`, `pack_size` | Draft, Active, EOL |
| `mep_bom` | BOM | **Product Recipe** | `components` (JSON), `version` | Draft, Active, Superseded |
| `mep_work_order` | Work Orders | **Production Instruction** | `qty`, `due_date`, `route_id`, `batch_code` | Draft, Released, In Progress, Completed |
| `mep_batch` | Batches | **Lot Management** | `mfg_date`, `expiry_date`, `batch_size`, `parent_wo` | Scheduled, Running, Finished |
| `mep_warehouse` | Warehouses | **Multi-Site Control** | `location_code`, `capacity` | Active, Inactive |
| `mep_bin` | Bins | **Micro-Location** | `parent_wh`, `capacity` | Active, Full, Inactive |
| `mep_supplier` | Suppliers | **Vendor CRM** | `contact_name`, `email`, `phone`, `lead_time_avg` | Active, Probation, Blacklisted |
| `mep_po` | Purchase Orders| **Buying Control** | `supplier_id`, `expected_date`, `total_amount` | Draft, Sent, Received, Closed |
| `mep_qc_check` | Quality Checks| **Inspection Gateway**| `status`, `lot_number`, `qc_defects` | Pending, Pass, Fail |
| `mep_ncr` | NCR | **Problem Solving** | `defect_type`, `resolution`, `capa_plan` | Open, Investigating, Closed, CAPA Pending |
| `mep_equipment` | Equipment | **Asset Tracking** | `daily_capacity`, `labor_rate`, `maintenance_logs` | Available, Maintenance, Down |
| `mep_route` | Routes | **Factory Map** | `steps` (JSON) | Active, Inactive |
| `mep_forecast` | Forecasts | **Future Planning** | `product_id`, `forecast_qty`, `customer_id` | Draft, Approved |
| `mep_customer` | Customers | **B2B Management** | `contact_name`, `email`, `credit_limit` | Active, Inactive |

---

# 3. Custom Database Tables

### `wp_mep_inventory_transactions`
Tracks every movement of material.
- `id`: BIGINT (PK)
- `material_id`: BIGINT
- `warehouse_id`: BIGINT
- `bin_id`: BIGINT
- `quantity`: DECIMAL
- `transaction_type`: VARCHAR (RECEIVE, ISSUE, TRANSFER, ADJUST)
- `reference_id`: BIGINT
- `lot_number`: VARCHAR
- `batch_id`: BIGINT (Optional link to Production Batch)

### `wp_mep_production_logs`
Tracks actual floor performance and cost variance.
- `id`: BIGINT
- `work_order_id`: BIGINT
- `output_qty`: DECIMAL
- `scrap_qty`: DECIMAL
- `labor_mins`: DECIMAL

### `wp_mep_stock_reservations`
Handles allocation of material for released orders.
- `id`: BIGINT
- `material_id`: BIGINT
- `work_order_id`: BIGINT
- `quantity`: DECIMAL
- `status`: VARCHAR (ACTIVE, RELEASED)

### `wp_mep_audit_logs`
- `id`: BIGINT
- `user_id`: BIGINT
- `object_type`: VARCHAR
- `object_id`: BIGINT
- `action`: VARCHAR
- `old_value`: LONGTEXT
- `new_value`: LONGTEXT

---

# 4. UX & UI Design Specifications

## 1. Visual BOM Builder (Drag-and-Drop)
React-based interactive canvas for product engineering.
- **Operations Support**: Drag "Work Centers" into the BOM to define routing and labor costs.
- **Inline Editing**: Click quantities or scrap factors to edit values directly on the canvas.
- **Version Control**: "Save as New Version" functionality to track recipe iterations.

## 2. Visual Warehouse Layout
- **Occupancy Heatmapping**: Bins change color (Yellow/Red) as they approach capacity.
- **Drag-and-Drop Transfers**: Move stock cards between bins to trigger transactions.

## 3. Production Planning Board (Kanban)
- **Kanban Flow**: Move Work Orders from Backlog -> In Progress -> Completed.
- **Completion Workflow**: Captures actual scrap and labor time for variance analysis.

---

# 5. MRP & Production Engine Specifications

## 1. MRP (Material Requirements Planning) Engine
**Netting Logic**: `Net Needed = Gross Demand - (On Hand - Reserved)`.
- **Recursive BOM Explosion**: Traverses multiple levels (e.g., Handbag -> Strap -> Buckle) to aggregate material needs at all tiers.
- **Time-Phased Planning**: Considers `Lead Time` and `Safety Stock` to calculate "Order By" dates.
- **Demand Pegging**: A JSON-structured tree that links every material requirement back to its parent Work Order or Forecast ID.

---

# 6. Quality, Traceability & Procurement

## 1. Traceability & Lot Genealogy
**Interactive Node Graph**: A visualization mapping the journey of a specific Lot.
- **Nodes**: Materials, Work Orders, Operators, Equipment, and QC Checks.
- **Data Structure**: Hierarchical object containing `lot_id`, `parents` (upstream), and `children` (downstream batches).

## 2. Procurement & Supplier Management
- **Supplier Scoring**: Automated tracking of Quality Rate (Pass/Fail) and OTD (On-Time Delivery).
- **Scorecards**: Visual performance analysis dashboard for vendors.

---

# 7. Reporting & Analytics

## 1. Executive Dashboard
- **KPI Tiles**: Output, Scrap Rate, Inventory Value, At-Risk Materials.
- **Inventory Aging**: Bar chart showing stock by age buckets (0-30, 31-60, etc.).
- **Cost Variance**: Comparison of BOM estimates vs. actual floor performance.

---

# 8. Admin Tools & Safeguards

- **Multi-Step Reset**: Hard wipe requires typed phrase confirmation: `RESET PRODUCTION ENVIRONMENT`.
- **Setup Wizard**: 3-step onboarding for UOM, initial warehouse, and sample data injection.
- **Demo Mode**: Persistent badge indicates when "LeatherCraft Co." sample data is active.

---

# 9. API & Data Flow

## 1. REST API Endpoints
All endpoints prefixed with `/wp-json/mep/v1`.

| Endpoint | Method | Description |
|---|---|---|
| `/materials` | GET | List all raw materials with stock levels. |
| `/work-orders` | GET/POST | Manage production work orders. |
| `/work-orders/{id}/print`| GET | Returns HTML printable shop traveler. |
| `/bom/{id}` | GET/POST | Get/Save BOM structure (supports versioning). |
| `/mrp/run` | POST | Trigger MRP explosion. |
| `/mrp/pegging` | GET | Fetch hierarchical demand chain. |
| `/inventory/transfer` | POST | Atomic bin-to-bin stock movement. |
| `/procurement/supplier-score/{id}` | GET | Returns Quality/OTD performance metrics. |
| `/reports/kpis` | GET | Dashboard data (Output, Aging, Variance). |
| `/qc/trace/{lot}` | GET | Fetch lot genealogy graph data. |
| `/reports/quality` | GET | QC Pass Rates, Defect Pareto, Active NCRs, CAPAs. |
| `/equipment/detailed`| GET | Detailed capacity and maintenance logs. |
| `/settings` | POST | Update ERP settings (e.g., setup_complete, help_mode). |

## 2. Data Flow: Production Loop
`[Forecast] -> [MRP Engine] -> [Purchase/Work Orders] -> [Inventory Issuance] -> [Shop Floor Production] -> [QC Check] -> [Finished Stock]`
