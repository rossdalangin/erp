# Manufacturing ERP Pro: Comprehensive Operational Guide (v1.0.0)

Welcome to the **Manufacturing ERP Pro** master manual. This document provides a chronological, step-by-step roadmap to implementing and effectively using the ERP system in a real-world manufacturing SME.

---

## 🚀 Getting Started Checklist
Before going live, ensure you have completed these essential setup items:
- [ ] Plugin activated and Setup Wizard completed.
- [ ] Primary Currency and Weight Units set in **System Utilities**.
- [ ] At least one Warehouse and one Bin created.
- [ ] At least one Supplier registered with an accurate Lead Time.
- [ ] Your primary Materials (Raw Items) uploaded or created with "Avg Unit Cost".
- [ ] Your Finished Products created with a defined "Standard Selling Price".

---

## Phase 1: Installation & Initial Configuration

### Step 1: Plugin Activation
1. Navigate to your WordPress Dashboard > **Plugins > Add New**.
2. Upload the `manufacturing-erp-pro.zip` file.
3. Click **Activate**. You will be immediately redirected to the **Setup Wizard**.

### Step 2: The Setup Wizard
1. **Company Profile**: Select your manufacturing type (**Discrete** or **Process**).
2. **Experience Mode**: Choose between:
    - **Clean Slate**: Recommended for live production if you have your data ready.
    - **Demo Mode (LeatherCraft Co.)**: Recommended for first-time users to see how data interlinks.
3. Click **Finish**. You are now ready to begin.

---

## Phase 2: Master Data Setup (The Foundation)

Before you can produce anything, you must define *what* you use and *who* you buy it from.

### Step 3: Register Suppliers
1. Go to **ERP Pro > Suppliers**.
2. Click **Add New Supplier**.
3. Enter the Company Name, Contact Email, and **Average Lead Time**.
    - *Tip*: Lead time is critical for MRP planning. If a supplier takes 14 days to deliver leather, the system will use this to suggest when you should place your order.

### Step 4: Define Materials (Raw Items)
1. Go to **ERP Pro > Materials**.
2. Click **Add New Material**.
3. **Crucial Fields**:
    - **SKU**: A unique identifier (e.g., `MAT-LTH-TAN-01`).
    - **Avg Unit Cost**: Enter your current purchase price. This drives your **BOM Cost Roll-up**.
    - **Safety Stock**: The minimum amount you must have on hand. If stock falls below this, MRP will flag it.
    - **Preferred Supplier**: Link this material to a supplier created in Step 3.

### Step 5: Define Equipment & Resources
1. Go to **ERP Pro > Equipment**.
2. Click **Add New Equipment**.
3. Enter the **Daily Capacity (Minutes)**.
    - *Example*: A 1-shift operation (8 hours) is 480 minutes.
4. Set the **Labor Rate ($/min)**. This is used to calculate the manufacturing cost of your products.

---

## Phase 3: Engineering & Design

### Step 6: Create Products (Finished Goods)
1. Go to **ERP Pro > Products**.
2. Click **Add New Product**.
3. Enter the Product Name (e.g., "Luxury Handbag") and SKU.
4. Set the **Standard Selling Price**.

### Step 7: Build the Visual BOM (Bill of Materials)
1. In the **Products** list, click **BOM Builder** for your new product.
2. **Drag & Drop**:
    - From the left sidebar, drag **Materials** into the central canvas.
    - Adjust the **Quantity** (e.g., `1.2 m2` of leather).
    - Set a **Scrap Factor** (e.g., `0.05` for 5% waste during cutting).
3. **Nesting**: If your bag has a "Strap" that is also a manufactured product, drag the "Strap" product into the canvas to create a multi-level BOM.
4. **Cost Roll-up**: Watch the "Total Estimated Cost" at the bottom. It updates instantly.
5. Click **Save as New Version**.

### Step 8: Define the Production Route
1. Go to **ERP Pro > Routes**.
2. Link a route to your Product.
3. Add steps in sequence:
    - *Step 1*: Cutting (Work Center: Laser Cutter, Time: 15 mins).
    - *Step 2*: Stitching (Work Center: Sewing Machine, Time: 45 mins).
4. These steps define the labor cost and the schedule for your capacity planning.

---

## Phase 4: Inventory Establishment

### Step 9: Establish Warehouse Layout
1. Go to **ERP Pro > Warehouses**. Create a "Main Warehouse".
2. Go to **ERP Pro > Bins**. Create bins like "Raw Material Rack A" and "Finished Goods Zone". Link them to the warehouse.

### Step 10: Receive Initial Stock
1. Go to **ERP Pro > Receive Shipments**.
2. **Visual Receiving**: Drag a material from the "Open PO Items" list (or use the Manual Receipt button) and drop it into a specific bin.
3. The system will prompt for a **Lot Number**. Enter it to ensure traceability.
4. **Result**: Your inventory levels are now updated, and the transaction is logged in the Audit Trail.

---

## Phase 5: Planning & Procurement

### Step 11: Create a Forecast
1. Go to **ERP Pro > Forecasts**.
2. Enter your expected demand for the next month (e.g., "Winter Sale - 500 units of Handbags").

### Step 12: Run the MRP Engine
1. Go to **ERP Pro > MRP Planning**.
2. Click **Recalculate MRP**.
3. **Analyze Results**: The system will explode your 500-unit forecast into raw material requirements, compare them with current stock, and show "Purchase Suggestions".
4. **Pegging**: Click on a suggestion to see the **Pegging View**. It will show exactly which forecast is driving the need for more leather.

### Step 13: Generate Purchase Orders
1. In the MRP Planning screen, select the materials you need to buy.
2. Click **Generate Purchase Orders**.
3. The system creates draft POs in **ERP Pro > Purchase Orders**, ready to be sent to your suppliers.

---

## Phase 6: Production Execution

### Step 14: Release Work Orders
1. You can release a Work Order directly from the **BOM Builder** (One-Click Release) or via **ERP Pro > Work Orders > Add New**.
2. Specify the **Target Quantity** and **Due Date**.
3. The order now appears in the "Released" state.

### Step 15: The Shop Floor Kanban Board
1. Open the **Production Board** (via Admin or the `/erp-shop-floor/` frontend page).
2. **Start Production**: Drag the Work Order card from "Backlog" to **"In Progress"**.
    - Assign an **Operator** and a **Machine**.
3. **Finish Production**: Drag the card to **"Completed"**.
    - **Log Actuals**: The system will ask for **Actual Scrap** and **Actual Labor Time**.
    - **Auto-Inventory**: The raw materials are automatically deducted (backflushed) from inventory, and the finished products are added to stock.
    - **Auto-QC**: A PENDING Quality Check is automatically generated for the new batch.

---

## Phase 7: Quality & Traceability

### Step 16: Perform Quality Inspections
1. Go to **ERP Pro > Quality Dashboard**.
2. Find the PENDING check for your recent batch.
3. Mark it as **PASS** or **FAIL**.
4. **Handling Failures**: If it fails, the system creates an **NCR (Non-Conformance Report)**.
5. **CAPA**: If the defect is serious, click **Promote to CAPA** on the NCR page to define a long-term corrective action plan.

### Step 17: Lot Traceability (The Recall Test)
1. If a customer reports a defect in "Batch #101", go to **ERP Pro > Traceability**.
2. Enter the Batch/Lot ID.
3. The system displays a **Visual Genealogy Graph**.
    - You can see exactly which supplier lot of leather was used.
    - You can see which operator performed the stitching.
    - You can see which other Work Orders used the same material lot (to find other potentially defective products).

---

## Phase 8: Monitoring & Optimization

### Step 18: Executive Dashboards
1. Monitor the **Main Dashboard** for high-level KPIs:
    - **Inventory Value**: Total capital tied up in stock.
    - **OTD (On-Time Delivery)**: Percentage of orders completed by their due date.
    - **Scrap Rate**: Are you wasting too much material?
2. Check the **Capacity Planner** to ensure no machines are over-scheduled.

### Step 19: System Maintenance
1. **Audit Logs**: Periodically review **System Utilities > Audit Logs** to track all user actions.
2. **Backups**: Use the **Export ERP Backup** tool before performing any major data changes.
3. **Soft Reset**: Use this to clear your transaction history at the end of a fiscal year while keeping your Material/Product master data.

---

## 🛠️ Troubleshooting Section

### Common Issues & Solutions

| Issue | Potential Cause | Solution |
|---|---|---|
| **BOM Costs are $0.00** | Raw materials have no "Avg Unit Cost" set. | Go to **Materials**, edit the item, and ensure the cost field is populated. |
| **MRP Suggesting Nothing** | Forecasts are in 'Draft' or Safety Stock is not set. | Ensure Forecasts are 'Published'. Set Safety Stock on critical materials. |
| **Cannot Drag Kanban Cards** | User lacks `mep_manage_production` capability. | Assign the correct Role (Administrator or Production Manager) to the user. |
| **Inventory Not Backflushing** | Work Order was closed without logging actuals. | Ensure "Complete Work Order" pop-up is filled out correctly on the Kanban board. |
| **Receipt Workspace is Empty** | No open Purchase Orders (POs) exist for that supplier. | Generate or manually create a PO first, then set it to 'Sent' status. |
| **"Restricted Access" Error** | Nonce timeout or session expiry. | Refresh the page. Ensure the user is logged into the WordPress admin. |

### System Health Indicators
If the "System Health" on the main dashboard is Red:
1. Check **System Utilities > Audit Logs** for recent database resets.
2. Verify that the `wp_mep_*` tables exist in your database via **System Utilities > Recreate Tables**.
3. Ensure no other plugins are conflicting with the REST API endpoints.

---

**Congratulations!** You have successfully implemented a complete manufacturing lifecycle in Manufacturing ERP Pro.
