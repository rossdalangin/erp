# Standard Operating Procedures (SOP) by User Role

This document defines the daily, weekly, and monthly responsibilities and system interactions for each user level in Manufacturing ERP Pro.

---

## 1. ERP Administrator (Role: `mep_administrator`)
**Objective**: Maintain system integrity, global settings, and audit governance.

### Daily Tasks:
1. **System Health Check**: Visit the **ERP Dashboard** to monitor global KPIs and ensure 'System Health' indicators are green.
2. **Audit Review**: Go to **System Utilities > Audit Logs**. Review recent destructive actions (deletions or resets).
3. **Backup Management**: Perform a **Diagnostic Export** before any major plugin updates or data imports.

### Weekly Tasks:
1. **User Provisioning**: Ensure new staff are assigned the correct ERP role (Clerk, Manager, or Inspector).
2. **Settings Tuning**: Review **Global Settings** for Currency and Standard Labor Rates to ensure cost roll-ups remain accurate.
3. **Audit Log Cleanup**: Review and archive (if needed) logs older than 3 months.

### Monthly Tasks:
1. **Financial Alignment**: Compare **Inventory Value** from the Executive Dashboard with your accounting software totals.
2. **System Optimization**: Use the **Recreate DB Tables** utility if performance is lagging (after taking a backup).

---

## 2. Production Manager (Role: `mep_production_manager`)
**Objective**: Bridge the gap between engineering, planning, and the shop floor.

### Daily Tasks:
1. **Production Scheduling**: Review the **Production Board (Kanban)**. Ensure 'Backlog' orders are moving to 'In Progress'.
2. **MRP Run**: Run the **MRP Planning Engine** every morning to identify material shortages.
3. **Capacity Planning**: Check the **Resource Capacity** view. If a machine (e.g., Laser Cutter) is at >90% load, reassign work orders or schedule overtime.

### Weekly Tasks:
1. **Lead Time Review**: Check **Supplier Performance** metrics. Update material "Lead Time" if a vendor is consistently late.
2. **Scrap Analysis**: Review the **Scrap Rate KPI**. Identify the top 3 materials contributing to waste.

### Project-Based Tasks:
1. **New Product Engineering**: Use the **Visual BOM Builder** to define materials and operations for new SKUs.
2. **Costing**: Verify 'Estimated Roll-up Cost' against target margins.

---

## 3. Warehouse Clerk (Role: `mep_warehouse_clerk`)
**Objective**: Ensure physical inventory matches the digital twin.

### Daily Tasks:
1. **Goods Receipt**: Monitor 'Incoming Shipments'. Use the **Receipt Workspace** to drag received items into their correct Bins.
2. **Lot Logging**: Always enter the **Supplier Lot Number** during receipt to maintain traceability.
3. **Inventory Transfers**: Use the **Visual Warehouse** to move stock from 'Bulk Storage' to the 'Production Floor' bins as needed by operators.

### Weekly Tasks:
1. **Cycle Counting**: Export the **Inventory CSV**. Perform a physical count of 'At Risk' materials (those below safety stock).
2. **Reorder Triggers**: Drag low-stock items into the **Reorder Basket** in the Visual Warehouse view.

### Monthly Tasks:
1. **Warehouse Optimization**: Review the **Bin Heatmap**. Reorganize bins that are consistently at 100% capacity to larger locations.

---

## 4. Quality Inspector (Role: `mep_quality_inspector`)
**Objective**: Protect the brand by enforcing inspection standards.

### Daily Tasks:
1. **Pending Inspections**: Review the **Compliance Dashboard**. Select all 'PENDING' checks created by completed Work Orders.
2. **Result Logging**: Record Pass/Fail status. For Failures, provide detailed 'Defect Nature' notes.
3. **NCR Management**: Review newly created **Non-Conformance Reports**.

### Weekly Tasks:
1. **Defect Pareto Analysis**: Identify the most common cause of failure (e.g., "Loose Threads") and brief the Production Manager.
2. **NCR Closure**: Follow up on "Investigating" NCRs to ensure they are either closed or promoted to CAPA.

### Monthly Tasks:
1. **CAPA Effectiveness Review**: Review all CAPAs closed in the last 30 days. Verify that the preventive actions have successfully reduced the failure rate for those SKUs.
2. **Lot Genealogy Drills**: Perform one "Mock Recall" using the **Traceability** tool to ensure the team can trace a lot from material receipt to finished shipment within 1 hour.
