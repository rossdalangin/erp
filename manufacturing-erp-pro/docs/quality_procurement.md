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
