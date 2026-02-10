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
