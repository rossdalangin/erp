<?php
/**
 * MEP Printable Work Order Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Work Order #<?php echo esc_html( $data['id'] ); ?> - Manufacturing ERP Pro</title>
    <style>
        body { font-family: sans-serif; padding: 30px; color: #222; line-height: 1.4; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #2271b1; padding-bottom: 15px; margin-bottom: 25px; }
        h1 { margin: 0; color: #2271b1; }
        .badge { background: #eee; padding: 5px 10px; border-radius: 4px; font-size: 0.8em; text-transform: uppercase; }
        .section { margin-bottom: 30px; }
        h2 { border-bottom: 1px solid #ccc; padding-bottom: 5px; font-size: 1.2em; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f9f9f9; font-size: 0.9em; }
        .wo-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .notes-area { height: 100px; border: 1px dashed #ccc; margin-top: 10px; }
        .footer { margin-top: 50px; font-size: 11px; color: #777; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>WORK ORDER</h1>
            <p><strong>#<?php echo esc_html( $data['id'] ); ?></strong></p>
        </div>
        <div style="text-align: right;">
            <span class="badge"><?php echo esc_html( $data['status'] ); ?></span>
            <p>Date: <?php echo date('Y-m-d'); ?></p>
        </div>
    </div>

    <div class="section wo-meta">
        <div>
            <h2>Product Details</h2>
            <p><strong>Name:</strong> <?php echo esc_html( $data['product_name'] ); ?></p>
            <p><strong>SKU:</strong> <?php echo esc_html( $data['product_sku'] ); ?></p>
            <p><strong>Quantity to Produce:</strong> <?php echo esc_html( $data['qty'] ); ?></p>
        </div>
        <div>
            <h2>Assignment</h2>
            <p><strong>Assignee:</strong> <?php echo esc_html( $data['assignee'] ); ?></p>
            <p><strong>Start Date:</strong> <?php echo esc_html( $data['start_date'] ); ?></p>
            <p><strong>Batch:</strong> <?php echo esc_html( $data['batch_code'] ); ?></p>
        </div>
    </div>

    <div class="section">
        <h2>Bill of Materials (Components)</h2>
        <table>
            <thead>
                <tr>
                    <th>Component Name</th>
                    <th>Type</th>
                    <th>Required Qty</th>
                    <th>UOM</th>
                    <th>Picked [ ]</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $data['bom'] as $item ) : ?>
                    <tr>
                        <td><?php echo esc_html( $item['name'] ); ?></td>
                        <td><?php echo esc_html( $item['type'] ); ?></td>
                        <td><?php echo esc_html( $item['qty'] * $data['qty'] ); ?></td>
                        <td><?php echo esc_html( $item['uom'] ?? '' ); ?></td>
                        <td style="width: 60px;"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Production Routing (Steps)</h2>
        <table>
            <thead>
                <tr>
                    <th>Step #</th>
                    <th>Operation / Work Center</th>
                    <th>Est. Time (Mins)</th>
                    <th>Instructions</th>
                    <th>Completed By</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $data['route'] ) ) : ?>
                    <?php foreach ( $data['route'] as $i => $step ) : ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo esc_html( $step['work_center_name'] ); ?></td>
                            <td><?php echo esc_html( $step['time'] ); ?></td>
                            <td><?php echo esc_html( $step['desc'] ); ?></td>
                            <td style="width: 120px;"></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="5">No routing steps defined for this product.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Shop Floor Notes</h2>
        <div class="notes-area"></div>
    </div>

    <div class="footer">
        Manufacturing ERP Pro - Enterprise Production Control System
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2271b1; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Print This Order</button>
    </div>
</body>
</html>
