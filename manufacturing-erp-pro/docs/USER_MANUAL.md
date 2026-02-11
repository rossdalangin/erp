# Manufacturing ERP Pro: The Complete Documentation & Marketing Manual

Welcome to the future of SME manufacturing. This manual serves as both your operational guide and your business launchpad for **Manufacturing ERP Pro**.

---

## Part 1: Product Description & Benefits
*Why Manufacturing ERP Pro is the #1 Choice for Scaling SMEs*

### Beyond Features: The Business Value
Manufacturing ERP Pro isn't just a database; it's a **Growth Engine**.

1. **Stop the "Stock-Out" Profit Leak**: Most SMEs lose 15-20% of their potential revenue due to missing raw materials. Our real-time inventory and safety stock alerts ensure your factory never stops.
2. **Recover Lost Time with Visual Planning**: Ditch the spreadsheets. Our Drag-and-Drop BOM and Kanban boards reduce planning time by 70%, allowing your managers to focus on quality, not data entry.
3. **Data-Driven Costing**: Stop guessing your margins. With recursive cost roll-ups, you know the *exact* cost of every handbag down to the last centimeter of thread.
4. **World-Class Traceability**: Protect your brand reputation. In the event of a defect, our Lot Genealogy trace allows you to identify affected products in seconds, not days.
5. **WordPress-Native Simplicity**: No expensive proprietary servers. If you can run a WordPress site, you can run an Enterprise-grade factory.

---

## Part 2: Module-by-Module User Instructions

### 1. Inventory & Warehouse Management
- **Setup**: Go to `ERP Pro > Materials` to define your items and their storage rules.
- **Visual Grid**: Access `ERP Pro > Visual Warehouse` to see a 2D bird's-eye view of your factory floor and bin occupancy.
- **Stock Movement**: Transfers are handled visually—simply drag stock cards from one bin to another.
- **Exporting**: Use the "Export Inventory CSV" button in the Visual Warehouse view to generate real-time stock reports for accounting.

### 2. The Visual BOM Builder
- **Building a Product**: Navigate to `ERP Pro > BOM Builder`.
- **Interactive Canvas**: Drag materials and **Operations** (Work Centers) from the left library into the assembly tree.
- **Operations**: Adding an operation allows you to factor in labor time and machine costs into the product's total cost.
- **Sub-Assemblies**: Nest BOMs seamlessly. (e.g., "Strap Assembly" inside "Leather Bag").
- **Costing**: Watch the "Roll-up Cost" update live as you adjust quantities, times, or scrap factors.
- **Versioning**: Use the "Save as New Version" checkbox to archive the current structure and start a new iteration (e.g., v1, v2).
- **Reordering**: Drag existing BOM nodes (Materials or Operations) to change their sequence. This updates the production flow logic for shop floor travelers.

### 3. Production Planning (Kanban)
- **The Board**: Go to `ERP Pro > Production Board`.
- **Status Flow**: Drag-and-drop Work Order cards between columns.
- **Operator Assignment**: When moving an order to "In Progress", the system prompts for an Operator ID to assign responsibility.
- **Completion & Yield**: When moving to "Completed", the system prompts for actual scrap and labor time. This data is used for **Cost Variance Analysis**.
- **Printing**: You can generate a professional shop-floor traveler by visiting the Work Order's "Print" view via the API or Admin link.

### 4. MRP (Demand Planning)
- **Forecasting**: Enter sales targets in `ERP Pro > Forecasts`.
- **MRP Planning**: Use `ERP Pro > MRP Planning` to see a prioritized list of material requirements.
- **Asynchronous Recalculation**: Heavy demand explosions run in the background. Click "Recalculate MRP Results" to trigger a fresh analysis without freezing your browser.
- **Interactive POs**: Drag material suggestions into the "Basket" and click "Generate Purchase Orders" to automate procurement.
- **Pegging**: Use the `Pegging View` to trace *exactly* which customer forecast or order triggered a specific material requirement.
- **Stock Reservations**: The MRP engine accounts for stock already reserved for active Work Orders, ensuring procurement is accurate.

### 5. Equipment & Capacity Planning
- **Machine Catalog**: Manage your factory assets in `Equipment`. Set the "Daily Capacity" in minutes for each machine.
- **Maintenance**: Log repairs and services directly under each machine to track uptime and maintenance costs.
- **Capacity Planner**: Access the dedicated `Capacity Planner` view to see detailed machine schedules for the current week and historical maintenance records.
- **Load Monitoring**: The Executive Dashboard displays a real-time "Resource Capacity" chart.
- **Bottleneck Alerts**: Machines exceeding 90% load are highlighted in Red, allowing you to reassign work orders before delays occur.

### 6. Master Data Importer
- **Transitioning from Excel**: Access `ERP Pro > System Utilities`.
- **Bulk Import**: Paste your material data in CSV format (Name, SKU, UOM, Cost) to populate your catalog in seconds.

### 7. Valuation Settings
- **Methodology**: In `System Utilities`, you can toggle between **FIFO** (First-In-First-Out) and **LIFO** (Last-In-First-Out) valuation.
- **Financial Impact**: The Executive Dashboard KPI for "Inventory Valuation" automatically updates based on your chosen method and historical transaction costs.

### 7. Roles & Security
- **Access Control**: We provide 4 specialized roles (Admin, Production, Warehouse, Quality). See `GOVERNANCE.md` for the full permission matrix.
- **Audit Logs**: Every transaction is recorded. View them in `System Utilities` or the DB for full accountability.

### 8. B2B & Customers
- **Customer CRM**: Manage your B2B relationships in `ERP Pro > Customers`.
- **Demand Link**: Link forecasts to specific customers to improve pegging accuracy and demand planning.

---

## Part 3: Marketing & Sales Strategy

### 1. Marketing Strategy: The "Efficiency First" Approach
- **Target Audience**: SME Manufacturers (10-100 employees) in Discrete industries (Fashion, Furniture, Electronics).
- **Core Message**: "Enterprise Power, WordPress Simplicity."
- **Channel Strategy**:
  - **Inbound**: Content marketing focused on "Manufacturing Efficiency" and "Cost Control."
  - **Outbound**: LinkedIn targeting of Operations Managers and Factory Owners.
  - **Partnerships**: WordPress Agency partners who serve B2B manufacturing clients.

### 2. Pricing Tiers (SME Optimized)
- **Starter**: $99/mo (Up to 2 Warehouses, 500 SKUs).
- **Professional**: $249/mo (Unlimited Warehouses, MRP Engine, Quality Module).
- **Enterprise**: $499/mo (Priority Support, Custom API Integrations, Multi-site management).

### 3. Sales Strategy & Outreach Scripts
- **The "Audit" Hook**: Offer a free "Inventory Health Check" where you show them how much money they are losing in unoptimized stock.
- **Demo-Led Close**: Always lead with the Visual BOM Builder. It's the "Aha!" moment where they realize how easy factory management can be.

#### Outreach Script: LinkedIn Cold DM
"Hi [Name], noticed you're scaling [Company Name]'s production. We just launched a tool that helps leather/apparel SMEs cut material waste by 15% using visual BOMs in WordPress. Would you be open to a 10-min 'Inventory Health Check' next week? Best, [Your Name]"

#### Outreach Script: Email to Factory Owner
"Subject: Is spreadsheet chaos costing [Company Name] 20% margin?
Hi [Name], Most manufacturers we talk to are flying blind on material costs. Manufacturing ERP Pro digitalizes your floor in days, not months. Our MRP engine ensures you never over-order raw materials again. Are you free for a quick demo of our visual planning board? Cheers, [Your Name]"

---

## Part 4: Sales Assets & Scripts

### 1. Sales Letter: The End of Spreadsheet Chaos
**Headline: Stop Flying Blind. Digitalize Your Factory Floor with the World's First WordPress-Native Manufacturing ERP.**

Dear Factory Owner,

You’re scaling. Orders are coming in. But your margins are shrinking. Why?

Because you’re still using spreadsheets to manage your Bill of Materials. Because your warehouse team is "guessing" stock levels. Because you only find out a batch is defective *after* it ships.

**Manufacturing ERP Pro** changes everything. We’ve taken the power of enterprise systems like Protean and simplified them for the scaling SME.

- **Visual BOM Builder**: Drag, drop, and cost your products in real-time.
- **MRP Engine**: Let the "Brain" handle your procurement.
- **Lot Traceability**: Protect your brand with surgical precision.

Don't let spreadsheet chaos kill your growth.

[Click Here to Start Your Free Trial]

### 2. Video Sales Letter (VSL) Script
**Hook**: "Is your factory floor running on spreadsheets and guesswork? You're losing 20% of your margins every single day."
**Problem**: Explain the chaos of missing materials and inaccurate costing.
**Solution**: Introduce Manufacturing ERP Pro. Show the Visual BOM and Kanban boards.
**Social Proof**: "The LeatherCraft Co. saved $12k in their first month by optimizing leather yields."
**CTA**: "Click below to start your 14-day free trial and digitalize your factory today."

### 3. "How It Works" Explainer Script
- **0:00-0:30**: Introduction to the Modular Architecture.
- **0:30-1:30**: Explaining the BOM Explosion and MRP Logic.
- **1:30-2:30**: Demonstrating the Inventory Transaction & Audit Log system.
- **2:30-3:00**: Summary of Security and Data Integrity features.

---

## Part 5: Launch Campaign & Content

### 1. 30-Day Social Media Calendar & "Content that Converts"
- **Week 1**: Problem Awareness (The hidden costs of manufacturing chaos).
- **Week 2**: Feature Spotlights (Visual BOM, MRP Engine).
- **Week 3**: Case Studies & Social Proof.
- **Week 4**: Urgency & Launch Offers.

#### LinkedIn Post Templates (High Conversion)

1. **The "Efficiency Gap" Post**
   "Your factory floor is leaking profit. 💸 Most SMEs lose 15% of their margins simply because they don't have real-time visibility into their material costs. Spreadsheets worked when you had 5 SKUs. They're killing you now that you have 50. It’s time to digitalize. [Link to Demo]"

2. **The "BOM Stress" Post**
   "Building a multi-level BOM shouldn't feel like doing advanced calculus. 🧮 Our new Visual BOM Builder for WordPress makes product costing as easy as drag-and-drop. See your cost roll-ups in real-time and stop guessing your margins. #Manufacturing #ERP"

3. **The "Traceability" Post**
   "What happens if a customer reports a defect today? 📉 Can you trace that specific component back to the batch and supplier in under 60 seconds? If not, your brand is at risk. World-class traceability is no longer just for the 'big guys.' Meet Manufacturing ERP Pro."

4. **The "MRP Brain" Post**
   "Stop over-ordering raw materials. 🛑 Your cash flow is tied up in excess inventory because your planning is reactive, not proactive. Our MRP engine 'explodes' your demand and gives you a surgical shopping list. Buy what you need, when you need it."

5. **The "WordPress Advantage" Post**
   "Why spend $50k on a legacy ERP that requires a dedicated IT team? 🖥️ Manufacturing ERP Pro gives you enterprise power with the simplicity of WordPress. Scale your factory on the platform you already know and love."

#### Twitter Thread Templates (Educational)

1. **Thread: The 5 Spreadsheet Traps for Manufacturers**
   - 1/5: Why Excel is the #1 enemy of your scaling factory.
   - 2/5: Version control chaos: Who has the 'real' BOM?
   - 3/5: The hidden cost of manual data entry errors.
   - 4/5: Why spreadsheets can't handle real-time inventory netting.
   - 5/5: The solution: A dedicated MRP engine in WordPress. [Link]

2. **Thread: How to Calculate Your 'Real' Product Cost**
   - 1/4: Most SMEs only account for raw material costs. They forget 'The Hidden Three.'
   - 2/4: 1. Yield Loss & Scrap: That 5% waste adds up.
   - 3/4: 2. Routing Labor: Every minute at the stitching station has a price.
   - 4/4: 3. Recursive Roll-ups: Costing sub-assemblies accurately. ERP Pro does this for you automatically.

3. **Thread: The 60-Second Recall Challenge**
   - 1/3: In a crisis, speed is everything.
   - 2/3: Lot Genealogy isn't just paperwork; it's insurance for your brand.
   - 3/3: How we visualised the journey from Raw Material -> Finished Good in ERP Pro. [Screenshot/Link]

4. **Thread: Inventory vs. Cash Flow**
   - 1/5: Inventory is just 'frozen cash' on your warehouse shelves. ❄️
   - 2/5: How Safety Stock alerts prevent the 'Panic Buy.'
   - 3/5: Why FIFO matters for material aging (especially in leather/textiles).
   - 4/5: Moving from reactive to proactive procurement.
   - 5/5: Ready to unfreeze your cash? Let's talk MRP.

5. **Thread: The SME Scaling Blueprint**
   - 1/4: You've hit your first $1M in revenue. What's next?
   - 2/4: You can't scale manual processes. You need a 'System of Record.'
   - 3/4: Why modular ERPs are better for growth than all-in-one monoliths.
   - 4/4: Start with Inventory, grow into MRP. [Link to ERP Pro Tiers]

### 2. 7-Day Promotional Email Series
- **Day 1**: The Spreadsheet Trap (Why Excel is killing your growth).
- **Day 2**: Meet the Brain (How the MRP Engine plans your success).
- **Day 3**: Visual Factory Floor (The power of Kanban).
- **Day 4**: Traceability & Trust (Protecting your brand).
- **Day 5**: The ROI of ERP (Hard numbers on savings).
- **Day 6**: FAQ & Objection Handling.
- **Day 7**: Last Call (Launch discount expires).

### 9. Quality & Rework
- **QC Gateway**: Record inspection results for batches or individual items.
- **Quality Dashboard**: View real-time analytics on pass rates, active Non-Conformance Reports (NCRs), and defect distribution (Pareto analysis).
- **Rework Trigger**: If a QC check fails, the system can automatically generate a "REWORK" order to correct the defect.
- **Traceability**: Enter a Lot/Batch number in the `Traceability` module to see a visual graph of its genealogy, including all upstream movements.

### 10. Vendor Performance
- **Scorecards**: Visit `ERP Pro > Supplier Scorecard` to see a visual analysis of Quality and On-Time Delivery performance per vendor.
- **Visual Receiving**: Go to `ERP Pro > Receive Shipments` to visually drag items from an open Purchase Order into a Warehouse Bin, completing the goods receipt transaction.

### 11. Accountability & Forensics
- **Audit Logs**: Access the full history of system actions via `ERP Pro > System Utilities`. Track who changed what and when.
- **Backups**: Before performing a system reset, use the "Export ERP Database" button to download a diagnostic backup of all ERP-specific transaction tables.

---

## Part 6: Troubleshooting & FAQ

### FAQ

**Q: Can I use this for process manufacturing (liquids/chemicals)?**
A: Manufacturing ERP Pro is optimized for discrete manufacturing but supports "Light Process" via flexible UOMs (Liters, KG) and recursive BOMs for recipes.

**Q: Does it integrate with WooCommerce?**
A: Currently, demand is managed via the `Forecasts` CPT. A WooCommerce integration module is on the roadmap for Q4.

**Q: Is my data safe if I reset the plugin?**
A: The "Soft Reset" keeps your Master Data (Products, Materials) and only wipes transactions. The "Hard Reset" wipes everything. Always perform a backup before any reset.

### Troubleshooting

**Problem: The Visual BOM Builder is not loading.**
- Ensure you have the `wp-element` and `wp-api-fetch` scripts enabled (standard in modern WP).
- Check for JavaScript errors in the console (F12).

**Problem: MRP suggestions are showing 0 requirements.**
- Verify that your `Forecasts` are published.
- Ensure the Finished Product has an "Active" BOM assigned to it.
- Check that your current inventory is actually lower than the demand + safety stock.

**Problem: "Invalid confirmation phrase" when resetting.**
- The phrase is case-sensitive and must be exactly: `RESET PRODUCTION ENVIRONMENT`.
