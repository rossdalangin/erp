# UX & UI Design Specifications

## 1. Visual BOM & Routing Builder (Drag-and-Drop)

The BOM Builder is a React-based interactive canvas that allows engineers to design product structures and production steps visually.

### Components:
- **Left Sidebar (Library)**: Contains draggable Raw Materials, Sub-assemblies, and Operations (e.g., Cutting, Stitching).
- **Central Canvas (Tree/Flow View)**:
  - **Tree Node**: Represents a material or sub-assembly.
  - **Connection Lines**: Show parent-child relationships.
  - **Operation Blocks**: Integrated into the tree to show where materials are consumed.
- **Right Sidebar (Properties)**: Context-aware panel to edit quantities, scrap rates, and specific operation details (machine time, labor time).

### Key Interactions:
- **Drag Materials into Tree**: Dropping a material onto an assembly node adds it to the BOM.
- **Nested Assemblies**: Expand/collapse sub-assemblies (e.g., "Strap Assembly" inside "Leather Bag").
- **Drag Reorder Operations**: Change the sequence of routing steps by dragging blocks up or down.
- **Real-time Cost Roll-up**: As components are added or quantities changed, the "Total Estimated Cost" updates instantly in the header.
- **Substitute Management**: Drag an alternative material onto an existing BOM line to create a "Substitute Group".

## 2. Visual Warehouse & Inventory Management

### Visual Warehouse Layout:
- **Grid View**: A 2D bird's-eye view of the warehouse floor.
- **Bin Status**: Bins are color-coded based on occupancy (Empty: Green, Partial: Yellow, Full: Red).
- **Drag-and-Drop Bin Transfers**: Move stock between bins by dragging a "Stock Card" from one bin to another. This triggers a modal to specify quantity and lot.

### Inventory Command Center:
- **Reservation View**: Visual indicators showing stock that is "On Hand" vs "Reserved" for specific Work Orders.
- **Aging Heatmap**: Highlights older stock (FIFO enforcement) to ensure material rotation.

## 3. Production Planning Board (Kanban)

A Trello-style board for managing the lifecycle of Work Orders.

### Kanban Columns:
1. **Backlog**: New Work Orders awaiting material release.
2. **Ready**: Materials are reserved/staged; ready to start.
3. **In Progress**: Active production.
4. **QC / Inspection**: Finished goods undergoing quality checks.
5. **Completed**: Finished and moved to stock.

### Drag-and-Drop Actions:
- **Prioritization**: Drag cards up/down within a column to change production priority.
- **Status Change**: Drag a Work Order from "Ready" to "In Progress" to trigger material issuance and start time tracking.
- **Operator Assignment**: Drag "User Avatars" onto a Work Order card to assign labor resources.

## 4. Mobile & Tablet Optimization
- **Responsive Tables**: Horizontal scrolling with "sticky" first columns (SKU/ID).
- **Scan-to-Action**: Floating barcode icon that opens the camera to scan Lot numbers or Bin IDs.
- **Large Touch Targets**: Button sizes and spacing optimized for use on the production floor with gloves.
