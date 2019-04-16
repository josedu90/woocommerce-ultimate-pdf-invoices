<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://woocommerce-pdf-invoices.db-dzine.de
 * @since      1.0.0
 *
 * @package    WooCommerce_PDF_Invoices
 * @subpackage WooCommerce_PDF_Invoices/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WooCommerce_PDF_Invoices
 * @subpackage WooCommerce_PDF_Invoices/public
 * @author     Daniel Barenkamp <contact@db-dzine.de>
 */
class WooCommerce_PDF_Invoices_Public extends WooCommerce_PDF_Invoices {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $version;

	/**
	 * options of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $options
	 */
	protected $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $generator) 
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->generator = $generator;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() 
	{

		global $woocommerce_pdf_invoices_options;

		$this->options = $woocommerce_pdf_invoices_options;

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-pdf-invoices-public.css', array(), $this->version, 'all' );
		
	}
	
	/**
	 * Inits the print products
	 *
	 * @since    1.0.0
	 */
    public function init()
    {

		global $woocommerce_pdf_invoices_options;

		$this->options = $woocommerce_pdf_invoices_options;

		if (!$this->get_option('enable')) {
			return false;
		}

		$this->upload_dir = $this->get_uploads_dir( 'pdf-invoices' );
		if ( ! file_exists( $this->upload_dir ) ) {
			mkdir( $this->upload_dir, 0755, true );
		}

		$user_id = get_current_user_id();
		if(!$user_id) {
			return false;
		}

		if(isset($_POST['create_pdf_invoice']) && !empty($_POST['create_pdf_invoice'])) {
			$order = wc_get_order(intval($_POST['create_pdf_invoice']));
			$customer_id = $order->get_customer_id();

			if(!$this->is_user_role('administrator', $user_id) && !$this->is_user_role('shop_manager', $user_id) && ($customer_id !== $user_id) ) {
				return false;
			}

			$this->generator->setup_data($_POST['create_pdf_invoice']);
			$this->generator->create_pdf($this->upload_dir);
		}

		if(isset($_GET['create_pdf_invoice']) && !empty($_GET['create_pdf_invoice'])) {

			$order = wc_get_order(intval($_GET['create_pdf_invoice']));
			$customer_id = $order->get_customer_id();

			if(!$this->is_user_role('administrator', $user_id) && !$this->is_user_role('shop_manager', $user_id) && ($customer_id !== $user_id) ) {
				return false;
			}

			$this->generator->setup_data($_GET['create_pdf_invoice']);
			$this->generator->create_pdf($this->upload_dir, true);
		}
    }

	/**
	 * add_files_to_email_attachments.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	public function add_files_to_email_attachments( $attachments, $status, $order ) {

		if(!$this->get_option('generalAttachToMail')) {
			return false;
		}

		if ( ! $order instanceof WC_Order ) {
			return $attachments;
		}

		$allowed_statuses = $this->get_option('generalAttachToMailStatus');
		if(!$allowed_statuses) {
			$allowed_statuses = array( 
				'new_order', 
				'customer_invoice', 
				'customer_processing_order', 
				'customer_completed_order', 
				'customer_refunded_order'
			);
		}

		if($status == "customer_refunded_order") {
			$this->generator->setup_data($order->get_id());
			if(!$this->generator->create_pdf($this->upload_dir, false, true)) {
				return false;
			}
		}

		if( isset( $status ) && in_array ( $status, $allowed_statuses ) ) {
			$order_id = $order->get_id();
			$invoice = $this->upload_dir . '/' . $order_id . '.pdf';
			$invoice_exists = file_exists($invoice);
			if(!$invoice_exists) {
				return $attachments;
			}
			$attachments[] = $invoice;
		}
		return $attachments;
	}

	/**
	 * add_files_to_order.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function create_pdf_invoice_automatically( $order_id, $posted ) 
	{
		if(!$this->get_option('generalAutomatic')) {
			return false;
		}

		if ( ! file_exists( $this->upload_dir ) ) {
			mkdir( $this->upload_dir, 0755, true );
		}

		$this->generator->setup_data($order_id);
		if(!$this->generator->create_pdf($this->upload_dir)) {
			return false;
		}

		return true;
	}

	public function customer_download_pdf()
	{
		if ( isset( $_GET['download_invoice_pdf'] ) && isset( $_GET['_wpnonce'] ) && ( false !== wp_verify_nonce( $_GET['_wpnonce'], 'download_pdf_invoice' ) ) ) {

			$order_id = isset( $_GET['order'] ) ? $_GET['order'] : '';
			if(empty($order_id)) {
				return false;
			}

			$user_id = get_current_user_id();
			if(!$user_id) {
				return false;
			}

			$order = wc_get_order($order_id);
			$customer_id = $order->get_customer_id();
			
			if(!$this->is_user_role('administrator', $user_id) && !$this->is_user_role('shop_manager', $user_id) && ($customer_id !== $user_id) ) {
				return false;
			}

			$invoice = $this->upload_dir . '/' . $order_id . '.pdf';
			$invoice_exists = file_exists($invoice);
			if(!$invoice_exists) {
				return false;
			}
			
			$disposition = 'attachment';
			if($this->get_option('generalRenderInvoice')) {
				$disposition = 'inline';
			}
			// 
			header( "Expires: 0" );
			header('Content-Type: application/pdf');
			header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
			header( "Cache-Control: private", false );
			header( 'Content-disposition: ' . $disposition . '; filename=' . $order_id . '.pdf' );
			header( "Content-Transfer-Encoding: binary" );
			header( "Content-Length: ". filesize( $invoice ) );
			readfile( $invoice );
			exit();
		}
	}

	/**
	 * add_files_upload_form_to_thankyou_and_myaccount_page.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function add_files_upload_form_to_thankyou_and_myaccount_page( $order_id ) 
	{
		if(!$this->get_option('generalShowInvoice')) {
			return false;
		}

		$invoice_exists = file_exists($this->upload_dir . '/' . $order_id . '.pdf');
		if(!$invoice_exists) {
			return false;
		}

		$html = '<h2>' . __('Invoice (PDF)', 'woocommerce-pdf-invoices') . '</h2>';
		$query_params = array( 
			'download_invoice_pdf' => 'true', 
			'_wpnonce' => wp_create_nonce( 'download_pdf_invoice' ),
			'order' => $order_id,
		);
		$html .= '<a target="_blank" href="' . add_query_arg( $query_params ) . '">' . __('Download Invoice (PDF)', 'woocommerce-pdf-invoices') . '</a>';

		echo $html;
	}

	public function show_tax_rates($tax_totals, $order)
	{
		if(!$this->get_option('generalShowTaxRate')) {
			return $tax_totals;
		}
		
		foreach ($tax_totals as $key => &$value) {
			$rate = WC_TAX::get_rate_percent( $value->rate_id);
			$value->label .= ' (' . $rate . ')';
		}
		return $tax_totals;
	}

	protected function get_uploads_dir( $subdir = '' ) 
	{
		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'];
		if ( '' != $subdir ) {
			$upload_dir = $upload_dir . '/' . $subdir;
		}
		return $upload_dir;
	}

	protected function is_user_role( $user_role, $user_id = 0 ) 
	{
		$the_user = ( 0 == $user_id ) ? wp_get_current_user() : get_user_by( 'id', $user_id );
		if ( ! isset( $the_user->roles ) || empty( $the_user->roles ) ) {
			$the_user->roles = array( 'guest' );
		}
		return ( isset( $the_user->roles ) && is_array( $the_user->roles ) && in_array( $user_role, $the_user->roles ) );
	}
}