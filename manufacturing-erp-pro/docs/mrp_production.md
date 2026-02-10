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
