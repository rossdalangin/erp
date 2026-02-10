<?php
/**
 * MEP Printable Genealogy Report Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lot Genealogy Report - Manufacturing ERP Pro</title>
    <style>
        body { font-family: sans-serif; padding: 40px; color: #333; }
        .header { border-bottom: 2px solid #2271b1; margin-bottom: 30px; }
        h1 { color: #2271b1; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #f6f7f7; }
        .footer { margin-top: 50px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Lot Genealogy Report</h1>
        <p><strong>System:</strong> Manufacturing ERP Pro</p>
        <p><strong>Generated on:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>

    <h2>Lot Details: <?php echo esc_html( $data['lot'] ); ?></h2>

    <h3>Transaction History</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Action</th>
                <th>Quantity</th>
                <th>Warehouse ID</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $data['history'] ) ) : ?>
                <?php foreach ( $data['history'] as $h ) : ?>
                    <tr>
                        <td><?php echo esc_html( $h->created_at ); ?></td>
                        <td><?php echo esc_html( $h->transaction_type ); ?></td>
                        <td><?php echo esc_html( $h->quantity ); ?></td>
                        <td><?php echo esc_html( $h->warehouse_id ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4">No history found for this lot.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Confidential Manufacturing Data - Generated via ERP Pro for WordPress
    </div>

    <script>
        // Auto-print if requested
        if (window.location.search.includes('print=1')) {
            window.print();
        }
    </script>
</body>
</html>
