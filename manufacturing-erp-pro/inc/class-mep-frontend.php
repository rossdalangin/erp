<?php
/**
 * MEP Frontend Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Frontend {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_shortcode( 'mep_shop_floor', array( $this, 'render_shop_floor' ) );
		add_shortcode( 'mep_customer_portal', array( $this, 'render_customer_portal' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_filter( 'template_include', array( $this, 'load_erp_templates' ) );
	}

	/**
	 * Render the Shop Floor Portal (Kanban + Warehouse).
	 */
	public function render_shop_floor() {
		if ( ! current_user_can( 'read' ) ) {
			return '<p>' . __( 'Please log in to access the Shop Floor Portal.', 'manufacturing-erp-pro' ) . '</p>';
		}

		ob_start();
		?>
		<div class="mep-frontend-portal">
			<div class="mep-portal-header">
				<h2><?php _e( 'Shop Floor Command Center', 'manufacturing-erp-pro' ); ?></h2>
				<div class="mep-portal-tabs">
					<button class="mep-tab-link active" onclick="mepOpenTab(event, 'mep-kanban-tab')"><?php _e( 'Production Board', 'manufacturing-erp-pro' ); ?></button>
					<button class="mep-tab-link" onclick="mepOpenTab(event, 'mep-warehouse-tab')"><?php _e( 'Inventory & Bins', 'manufacturing-erp-pro' ); ?></button>
				</div>
			</div>

			<div id="mep-kanban-tab" class="mep-tab-content active">
				<div id="mep-kanban-root"></div>
			</div>
			<div id="mep-warehouse-tab" class="mep-tab-content">
				<div id="mep-warehouse-root"></div>
			</div>
		</div>
		<script>
			function mepOpenTab(evt, tabName) {
				var i, tabcontent, tablinks;
				tabcontent = document.getElementsByClassName("mep-tab-content");
				for (i = 0; i < tabcontent.length; i++) {
					tabcontent[i].style.display = "none";
				}
				tablinks = document.getElementsByClassName("mep-tab-link");
				for (i = 0; i < tablinks.length; i++) {
					tablinks[i].className = tablinks[i].className.replace(" active", "");
				}
				document.getElementById(tabName).style.display = "block";
				evt.currentTarget.className += " active";
			}
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render the B2B Customer Portal.
	 */
	public function render_customer_portal() {
		if ( ! current_user_can( 'read' ) ) {
			return '<p>' . __( 'Please log in to access your Customer Portal.', 'manufacturing-erp-pro' ) . '</p>';
		}

		ob_start();
		?>
		<div class="mep-frontend-portal">
			<h2><?php _e( 'B2B Customer Portal', 'manufacturing-erp-pro' ); ?></h2>
			<div id="mep-customer-portal-root"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Enqueue scripts and styles for the frontend.
	 */
	public function enqueue_frontend_assets() {
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) || ( ! has_shortcode( $post->post_content, 'mep_shop_floor' ) && ! has_shortcode( $post->post_content, 'mep_customer_portal' ) ) ) {
			return;
		}

		wp_enqueue_style( 'mep-frontend-style', MEP_PLUGIN_URL . 'assets/css/mep-admin.css', array(), MEP_VERSION );

		// Enqueue the same React apps used in Admin, they are built to work with roots
		wp_enqueue_script( 'mep-kanban-board', MEP_PLUGIN_URL . 'assets/js/kanban-board.js', array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ), MEP_VERSION, true );
		wp_enqueue_script( 'mep-warehouse-layout', MEP_PLUGIN_URL . 'assets/js/warehouse-layout.js', array( 'wp-element', 'wp-api-fetch' ), MEP_VERSION, true );

		// For customer portal, we might need a specific JS if it gets complex, but for now we reuse/expand
		wp_enqueue_script( 'mep-pegging-view', MEP_PLUGIN_URL . 'assets/js/pegging-view.js', array( 'wp-element', 'wp-api-fetch' ), MEP_VERSION, true );

		// Inject helpMode and other settings
		wp_localize_script( 'mep-kanban-board', 'mepSettings', array(
			'helpMode' => get_option( 'mep_help_mode', 'off' )
		) );
	}

	/**
	 * Load ERP templates for CPTs.
	 */
	public function load_erp_templates( $template ) {
		if ( is_singular( array( 'mep_product', 'mep_material', 'mep_work_order', 'mep_supplier' ) ) ) {
			$post_type = get_post_type();
			$file = MEP_PLUGIN_DIR . 'templates/single-' . $post_type . '.php';
			if ( file_exists( $file ) ) {
				return $file;
			}
		}
		return $template;
	}

	/**
	 * Auto-create portal pages on activation.
	 */
	public static function create_portal_pages() {
		$pages = array(
			'shop-floor' => array(
				'title'   => 'ERP Shop Floor',
				'content' => '[mep_shop_floor]',
			),
			'customer-portal' => array(
				'title'   => 'ERP Customer Portal',
				'content' => '[mep_customer_portal]',
			),
		);

		foreach ( $pages as $slug => $page ) {
			$exists = get_page_by_path( $slug );
			if ( ! $exists ) {
				wp_insert_post( array(
					'post_type'    => 'page',
					'post_title'   => $page['title'],
					'post_content' => $page['content'],
					'post_status'  => 'publish',
					'post_name'    => $slug,
				) );
			}
		}
	}
}
