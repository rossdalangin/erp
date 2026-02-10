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
**Business Value**: Scalability without complexity. Only use the modules you need today, and expand as your factory grows.
- **Inventory Module**: Ensures you never run out of leather or soles again. Eliminates "missing stock" surprises.
- **Production Module**: Digitalizes your factory floor. Know exactly what's being cut and stitched in real-time.
- **MRP Module**: The "Brain". Tells you exactly what to buy and when, preventing overstocking and cash flow ties.
- **Procurement Module**: Automates vendor relationships. Turns material shortages into Purchase Orders with one click.
- **Quality Module**: Protects your brand. Ensures only "A-Grade" bags reach your customers.
- **Reporting Module**: Your command center. Real-time KPIs (OEE, Scrap, Value) for data-driven decisions.
- **Settings & Utilities**: Enterprise-grade control with the simplicity of WordPress.

---

# 2. Custom Post Types (CPTs) & Schemas

| CPT Slug | Name | Operational Workflow |
|---|---|---|
| `mep_material` | Materials | **Stock Tracking**: Define your raw hides, threads, and buckles. Set safety stocks to trigger alerts. |
| `mep_product` | Products | **Product Catalog**: Manage your finished handbags and slippers. Track total inventory value per SKU. |
| `mep_bom` | BOM | **Product Recipe**: The multi-level blueprint. Drag materials into a tree to build your bag structure. |
| `mep_work_order` | Work Orders | **Production Instruction**: Release a WO to the floor. Tracks progress from "Ready" to "Completed". |
| `mep_batch` | Batches | **Lot Management**: Group production runs for easier quality tracing and cost analysis. |
| `mep_warehouse` | Warehouses | **Multi-Site Control**: Manage Main WH, Production Floor, or even Vendor stock. |
| `mep_bin` | Bins | **Micro-Location**: Know exactly which shelf or area a roll of leather is sitting on. |
| `mep_supplier` | Suppliers | **Vendor CRM**: Store lead times and quality scores to optimize your supply chain. |
| `mep_po` | Purchase Orders| **Buying Control**: Standardize your ordering. Track partial receipts and vendor accuracy. |
| `mep_qc_check` | Quality Checks| **Inspection Gateway**: Define pass/fail criteria at any step in production or receiving. |
| `mep_ncr` | NCR | **Problem Solving**: Document defects. Use "CAPA" to prevent leather scratches from happening again. |
| `mep_equipment` | Equipment | **Asset Tracking**: Monitor machine uptime for your cutting and stitching stations. |
| `mep_route` | Routes | **Factory Map**: Sequence of operations (Cutting -> Assembly -> QC) with estimated times. |
| `mep_forecast` | Forecasts | **Future Planning**: Input expected sales to let the MRP engine calculate your material needs. |

---

# 5. MRP & Production Engine Specifications

## 1. MRP (Material Requirements Planning) Engine
**Simplified Logic for SMEs**: The system looks at what you *plan* to sell (Forecasts) and what you *must* ship (Orders). It then "explodes" your Bag BOM to see every buckle and centimeter of leather needed. It checks your current stock, subtracts what's already reserved, and gives you a "Shopping List" (Suggested POs).

### Business Impact:
- **Reduces Waste**: Only buy what you need.
- **Improves Delivery**: Ensures materials arrive *before* the production team is ready to stitch.
- **Saves Time**: Automates hours of manual spreadsheet calculations.

---

# 6. Quality, Traceability & Procurement

## 1. Traceability & Lot Genealogy
**Why it matters**: If a customer reports a broken zipper, you can trace that specific zipper back to the supplier and the exact batch. You can also see which *other* bags used zippers from that same batch, allowing for surgical recalls instead of total shutdowns.

## 2. Procurement & Supplier Management
**Auto-PO Generation**: Stop manually typing POs. When the MRP engine sees you're low on tan leather, it suggests a PO for your preferred tan leather supplier. You just review and click "Send".
