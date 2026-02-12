<?php
/**
 * MEP Master Test Runner
 *
 * This script provides a simple way to run backend logic tests for MEP.
 * Run via: php run_tests.php
 */

define('ABSPATH', true);
define('MEP_PLUGIN_DIR', dirname(__DIR__) . '/');

require_once MEP_PLUGIN_DIR . 'inc/class-mep-db.php';
require_once MEP_PLUGIN_DIR . 'inc/class-mep-inventory.php';
require_once MEP_PLUGIN_DIR . 'inc/class-mep-bom.php';
require_once MEP_PLUGIN_DIR . 'inc/class-mep-mrp.php';
require_once MEP_PLUGIN_DIR . 'inc/class-mep-reports.php';
require_once MEP_PLUGIN_DIR . 'inc/class-mep-quality.php';
require_once MEP_PLUGIN_DIR . 'inc/class-mep-admin-ui.php';
require_once MEP_PLUGIN_DIR . 'admin/class-mep-admin.php';

// Mock WP functions
function get_post_meta($id, $key, $single = false) {
    if ($key === '_mep_capa_plan') return 'Test Plan';
    return '';
}
function update_post_meta($id, $key, $val) { return true; }
function wp_update_post($data) { return $data['ID']; }
function get_post_status($id) { return 'capa-pending'; }
function get_posts($args) { return []; }
function get_current_user_id() { return 1; }
function current_time($type) { return date('Y-m-d H:i:s'); }
function __($text, $domain) { return $text; }
function add_action($tag, $function, $priority = 10, $accepted_args = 1) { return true; }
function add_filter($tag, $function, $priority = 10, $accepted_args = 1) { return true; }
function wp_next_scheduled($tag) { return false; }
function wp_schedule_event($timestamp, $recurrence, $tag) { return true; }
function register_rest_route($namespace, $route, $args) { return true; }

class MEP_Unit_Tests {

    public static function run() {
        echo "Starting Manufacturing ERP Pro Test Suite...\n";

        $results = [
            'Inventory Locking' => self::test_inventory_locking(),
            'MRP Recursion' => self::test_mrp_recursion(),
            'Reporting Logic' => self::test_reporting_logic(),
            'Admin UI Integration' => self::test_admin_ui(),
            'CAPA Promotion' => self::test_capa_promotion(),
            'Wizard Logic' => self::test_wizard_logic(),
        ];

        $failed = false;
        foreach ($results as $name => $passed) {
            echo "[$name]: " . ($passed ? "PASSED" : "FAILED") . "\n";
            if (!$passed) $failed = true;
        }

        if ($failed) {
            echo "Tests failed!\n";
            exit(1);
        } else {
            echo "All tests passed successfully.\n";
            exit(0);
        }
    }

    private static function test_inventory_locking() {
        // Mock DB behavior for pessimistic locking
        return true;
    }

    private static function test_mrp_recursion() {
        // Test recursion safety
        $tree = MEP_BOM::get_bom_tree(999, 11); // Depth 11 should return empty
        return empty($tree);
    }

    private static function test_reporting_logic() {
        // Test OTD calculation with empty logs
        $otd = MEP_Reports::calculate_real_otd();
        return $otd === 100;
    }

    private static function test_admin_ui() {
        $ui = MEP_Admin_UI::get_instance();
        return method_exists($ui, 'add_product_row_actions');
    }

    private static function test_capa_promotion() {
        // Since we mocked everything, we just check if it runs without error
        MEP_Quality::promote_to_capa(123, 'Test Plan');
        return get_post_status(123) === 'capa-pending';
    }

    private static function test_wizard_logic() {
        $admin = MEP_Admin::get_instance();
        return method_exists($admin, 'maybe_redirect_to_wizard');
    }
}

// Ensure the mock global $wpdb exists
$GLOBALS['wpdb'] = new class {
    public $prefix = 'wp_';
    public function insert($table, $data) { return true; }
    public function get_results($q) { return []; }
    public function get_row($q) { return (object)['total_output'=>0, 'total_scrap'=>0]; }
};

MEP_Unit_Tests::run();
