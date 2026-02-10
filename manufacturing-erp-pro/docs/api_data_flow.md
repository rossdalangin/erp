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
