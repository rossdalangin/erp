# Manufacturing ERP Pro: The Complete Operational Manual (v1.7.0)

Welcome to **Manufacturing ERP Pro**. This manual is designed to help you transform your manufacturing facility into a data-driven, highly efficient operation.

---

## 1. Installation & Initial Setup

### System Requirements
- **WordPress:** 5.8 or higher.
- **PHP:** 7.4 or higher (8.1 recommended for performance).
- **Database:** MySQL 5.7+ or MariaDB 10.3+.
- **Recommended Plugin:** A database optimization plugin (to handle high-frequency transaction tables).

### Installation Steps
1. **Upload:** Download the `manufacturing-erp-pro.zip` and upload it via the WordPress Admin (`Plugins > Add New > Upload Plugin`).
2. **Activate:** Once uploaded, click "Activate."
3. **Setup Wizard:** Upon activation, you will be redirected to the **Setup Wizard**.
    - **Step 1:** Select your default Unit of Measure (Metric or Imperial).
    - **Step 2:** Define your primary warehouse name (e.g., "Main Plant").
    - **Step 3:** (Optional but Recommended) Click "Seed Sample Data" to load the **LeatherCraft Co.** demo. This provides a complete working environment for testing.

---

## 2. Core Operational Modules

### 2.0 Frontend Portals & Shop Floor UI

Manufacturing ERP Pro provides dedicated frontend pages for shop floor operators and B2B customers, allowing them to interact with the system without needing access to the WordPress dashboard.

#### 1. Shop Floor Command Center
Accessible by default at `/erp-shop-floor/`.
- **Production Board**: A touch-optimized Kanban board for moving Work Orders.
- **Inventory & Bins**: A visual interface for warehouse staff to perform bin transfers via tablets.
- **Security**: Access is restricted to logged-in users with appropriate ERP roles.

#### 2. B2B Customer Portal
Accessible by default at `/erp-customer-portal/`.
- Customers can view their demand forecasts, historical order status, and track shipments.
- This portal can be embedded on any WordPress page using the `[mep_customer_portal]` shortcode.


### 2.1 Inventory & Warehouse Management
Manage your raw materials and finished goods with surgical precision.

- **Creating Materials:** Go to `ERP Pro > Materials`. Define your SKU, Unit of Measure, and **Safety Stock**.
- **Visual Warehouse:** Navigate to `ERP Pro > Visual Warehouse`.
    - **Heatmap:** Bins will turn Yellow (>70% capacity) or Red (>90% capacity) based on your settings.
    - **Transfers:** To move stock, drag a material card from its source bin and drop it into the destination bin.
- **Lot Tracking:** Every "Receive" transaction generates a unique Lot ID, enabling full downstream traceability.

### 2.2 The Visual BOM Builder
Build multi-level assemblies using our proprietary drag-and-drop canvas.

1. **Select Product:** Choose a finished product from the `Products` list.
2. **Library:** Drag Materials or **Operations** (Work Centers) from the left sidebar into the canvas.
3. **Recursive Assemblies:** To nest a sub-assembly (e.g., a "Strap Assembly" inside a "Bag"), drag the sub-assembly product node into the main tree.
4. **Real-Time Costing:** Watch the "Estimated Roll-up Cost" update live as you change quantities or scrap percentages.
5. **Versioning:** Click "Save as New Version" to archive the previous BOM and activate the new one.

### 2.3 Production & Kanban Planning
Manage your shop floor travelers visually.

- **Backlog:** Newly created Work Orders appear here.
- **In-Progress:** When a worker starts an order, drag it to this column. You will be prompted to assign an **Operator ID**.
- **Completion:** Drag to "Completed" to capture:
    - **Actual Scrap:** Used for yield variance reporting.
    - **Actual Labor:** Used for labor cost variance analysis.

### 2.4 MRP (Material Requirements Planning)
The "Brain" of your factory.

1. **Demand Source:** Enter your targets in `ERP Pro > Forecasts`.
2. **Run MRP:** Click "Recalculate MRP Results." This runs in the background to prevent server timeouts.
3. **Pegging:** Use the `Pegging View` to see *why* the system is asking you to buy 500 meters of leather (traced back to a specific customer forecast).
4. **Procurement:** Select items from the suggestions and click "Generate Purchase Orders" to automatically create POs for your suppliers.

---

## 3. Quality & Traceability

### 3.1 Quality Dashboard
Monitor your factory's health in real-time.
- **QC Pass Rate:** See the percentage of inspections that passed vs. failed.
- **Defect Pareto:** Identify the most common production issues (e.g., "Loose Stitching").
- **Active NCRs:** Monitor Non-Conformance Reports currently being investigated.
- **CAPA Workflow:** Promote critical NCRs to the **CAPA (Corrective and Preventive Action)** stage. This adds a dedicated tracking layer for long-term resolution and preventive measures.

### 3.2 Lot Genealogy Trace
In the event of a customer complaint:
1. Enter the Batch/Lot ID in the `Traceability` module.
2. View the **Genealogy Graph** showing every upstream movement (which supplier provided the leather, which operator cut the pattern).
3. Export the report as a PDF for compliance audits.

---

## 4. Advanced Capacity Planning

Avoid bottlenecks before they happen.
- **Machine Schedule:** View the `Capacity Planner` to see the load vs. capacity for every Work Center.
- **Maintenance Logs:** Track the health of your equipment. Log every service and repair to understand the "Total Cost of Ownership" for your machines.

---

## 5. Security & Governance
This is an ERP-grade system, not a toy.

- **Roles:** Assign users to `ERP Administrator`, `Production Manager`, `Warehouse Clerk`, or `Quality Inspector`.
- **Audit Logs:** Every status change, inventory move, and BOM update is logged. Access these in `System Utilities > Audit Logs`.
- **Reset Safely:** To wipe transactional data while keeping your products, use the "Soft Reset." A "Hard Reset" requires the confirmation phrase: `RESET PRODUCTION ENVIRONMENT`.
- **Help Mode:** Click the "Interactive Help Mode" button on the Dashboard. When enabled, the system displays information icons (ℹ️) and contextual tooltips across the BOM Builder, Kanban, and Warehouse views to guide new users.
- **ERP Search (Command Palette):** Press `Ctrl+K` at any time to open the global search. Quickly jump between modules, search for SKUs, or trace Lots without multiple clicks.

---

## 6. Pro UX Workflows

### 6.1 Engineering to Production (The One-Click Release)
Inside the **Visual BOM Builder**, after you have finalized your product structure and cost roll-up, you don't need to navigate away. Use the **🚀 Release Work Order** button in the footer to immediately launch a production job.

### 6.2 Visual Replenishment
In the **Visual Warehouse** view, you can drag any material from a bin into the **🛒 Reorder Basket** sidebar. Once you have flagged all needed items, one click will take you to the MRP Planning page to finalize the procurement.

### 6.3 Automated QC Loop
When you drag a Work Order to "Completed" on the Production Board, the system automatically:
1. Generates a Lot ID.
2. Creates a PENDING Quality Check.
3. Provides a direct link to the QC record for immediate inspection.

---

## 7. Quick Start Examples

### Scenario A: Producing a New Leather Bag
1.  **Define Materials:** Go to `ERP Pro > Materials`. Add "Tan Cowhide" and "Heavy Duty Zip".
2.  **Define Product:** Go to `ERP Pro > Products`. Add "Classic Tan Handbag".
3.  **Build BOM:** Click "Visual BOM Builder" on the handbag row. Drag the Zip and Cowhide into the tree. Set Cowhide quantity to `1.2` and Zip to `1`.
4.  **Receive Stock:** Go to `ERP Pro > Receive Shipments`. Drag leather from a draft PO into "Main Warehouse".
5.  **Release Production:** Go to `ERP Pro > Work Orders`. Create a new order for 10 units.
6.  **Execute:** Go to `ERP Pro > Production Board`. Drag the order to "In Progress".

### Scenario B: Handling a Quality Failure
1.  **Inspect:** On the Shop Floor Portal, mark a Work Order as "Completed".
2.  **Log QC:** The system prompts for inspection. Select "FAIL" and enter "Scratch on leather".
3.  **Investigate:** Go to `ERP Pro > Quality Dashboard`. Click on the new NCR.
4.  **Action Plan:** If it's a systemic issue, click "Promote to CAPA" and enter a preventive plan (e.g., "Add protective sheets between stacked bags").

---

## 8. Troubleshooting FAQ

**Q: Why is my BOM cost roll-up incorrect?**
A: Ensure every material in the BOM has an "Avg Cost" defined in its master record.

**Q: How do I handle rework?**
A: When a QC check fails, select "Trigger Rework." The system will automatically create a linked Work Order with the "REWORK" prefix.

**Q: Can I export my inventory to my accounting software?**
A: Yes. Use the "Export Inventory CSV" button in the `Visual Warehouse` or `System Utilities` pages.
