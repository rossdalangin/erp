<?php
/**
 * MEP Customizer Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Customizer {

	public static function init() {
		add_action( 'customize_register', array( __CLASS__, 'register' ) );
	}

	public static function register( $wp_customize ) {
		// ERP Pro Section
		$wp_customize->add_section( 'mep_branding', array(
			'title'    => __( 'Manufacturing ERP Pro', 'manufacturing-erp-pro' ),
			'priority' => 30,
		) );

		// Factory Logo
		$wp_customize->add_setting( 'mep_factory_logo' );
		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'mep_factory_logo', array(
			'label'    => __( 'Factory / Company Logo', 'manufacturing-erp-pro' ),
			'section'  => 'mep_branding',
			'mime_type' => 'image',
		) ) );

		// Legal Name
		$wp_customize->add_setting( 'mep_company_legal_name', array(
			'default'   => 'LeatherCraft Manufacturing Co.',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( 'mep_company_legal_name', array(
			'label'   => __( 'Company Legal Name', 'manufacturing-erp-pro' ),
			'section' => 'mep_branding',
			'type'    => 'text',
		) );

		// Accent Color
		$wp_customize->add_setting( 'mep_accent_color', array(
			'default'   => '#2271b1',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'mep_accent_color', array(
			'label'   => __( 'Primary ERP Accent Color', 'manufacturing-erp-pro' ),
			'section' => 'mep_branding',
		) ) );

		// Footer Note for Prints
		$wp_customize->add_setting( 'mep_print_footer_note', array(
			'default'   => 'Manufacturing ERP Pro - Enterprise Production Control System',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( 'mep_print_footer_note', array(
			'label'   => __( 'Print Document Footer', 'manufacturing-erp-pro' ),
			'section' => 'mep_branding',
			'type'    => 'textarea',
		) );
	}
}
