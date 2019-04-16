<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://woocommerce-pdf-invoices.db-dzine.de
 * @since      1.0.0
 *
 * @package    WooCommerce_PDF_Invoices
 * @subpackage WooCommerce_PDF_Invoices/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WooCommerce_PDF_Invoices
 * @subpackage WooCommerce_PDF_Invoices/admin
 * @author     Daniel Barenkamp <contact@db-dzine.de>
 */
class WooCommerce_PDF_Invoices_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) 
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function load_redux()
	{
        if(!is_admin() || !current_user_can('administrator')){
            return false;
        }

	    // Load the theme/plugin options
	    if ( file_exists( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/options-init.php' ) ) {
	        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/options-init.php';
	    }
	}

    /**
     * Init
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function init()
    {
        global $woocommerce_pdf_invoices_options;

        if(!is_admin() || !current_user_can('administrator')){
            $woocommerce_pdf_invoices_options = get_option('woocommerce_pdf_invoices_options');
        }

        $this->upload_dir = $this->get_uploads_dir( 'pdf-invoices' );
        $this->options = $woocommerce_pdf_invoices_options;
    }

   /**
     * Enqueue Admin Styles
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name.'-admin', plugin_dir_url(__FILE__).'css/woocommerce-pdf-invoices-admin.css', array(), $this->version, 'all');
    }

    /**
     * Enqueue Admin Scripts
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name.'-admin', plugin_dir_url(__FILE__).'js/woocommerce-pdf-invoices-admin.js', array('jquery'), $this->version, true);
    }

	public function add_custom_order_status_actions_button( $actions, $order ) {

		$order_id = $order->get_id();

		$invoice = $this->upload_dir . '/' . $order_id . '.pdf';
		$invoice_exists = file_exists($invoice);
		if(!$invoice_exists) {
			return $actions;
		}

		$query_params = array( 
			'download_invoice_pdf' => 'true', 
			'_wpnonce' => wp_create_nonce( 'download_pdf_invoice' ),
			'order' => $order_id,
		);

        // Set the action button
        $actions['invoice'] = array(
            'url'       => add_query_arg( $query_params ),
            'name'      => __( 'Download Invoice', 'woocommerce-pdf-invoices' ),
            'action'    => "download-invoice",
        );

	    return $actions;
	}

	protected function get_uploads_dir( $subdir = '' ) {
		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'];
		if ( '' != $subdir ) {
			$upload_dir = $upload_dir . '/' . $subdir;
		}
		return $upload_dir;
	}

    public function add_pdf_invoice_meta_box()
    {
		$screen   = 'shop_order';
		$context  = 'side';
		$priority = 'high';
		add_meta_box(
			'wc_invoice_pdfs_upload_metabox',
			__( 'PDF Invoice', 'woocommerce-pdf-invoices' ),
			array( $this, 'create_pdf_invoice_meta_box' ),
			$screen,
			$context,
			$priority
		);
    }

	/**
	 * create_pdf_invoice_meta_box.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function create_pdf_invoice_meta_box() 
	{
		$html = '';
		$order_id = get_the_ID();

		$invoice = $this->upload_dir . '/' . $order_id . '.pdf';
		$invoice_exists = file_exists($invoice);

		if($invoice_exists) {

			$query_params = array( 
				'download_invoice_pdf' => 'true', 
				'_wpnonce' => wp_create_nonce( 'download_pdf_invoice' ),
				'order' => $order_id,
			);
			$html .= '<a target="_blank" href="' . add_query_arg( $query_params ) . '">' . __('Download Invoice (PDF)', 'woocommerce-pdf-invoices') . '</a>';
			$html .= '<hr><button type="submit" class="button button-primary" name="create_pdf_invoice" value="' . $order_id . '">' . __('Update Invoice', 'woocommerce-pdf-invoices') . '</button>';

		} else {
			$html .= '<p><em>' . __( 'No files uploaded.', 'woocommerce-pdf-invoices' ) . '</em></p>';
		
			$html .= '<hr><button type="submit" class="button button-primary" name="create_pdf_invoice" value="' . $order_id . '">' . __('Create Invoice', 'woocommerce-pdf-invoices') . '</button>';
		}

		echo $html;
	}

	public function add_preview_frame()
	{
		$shop_order_ids = get_posts(array(
		    'fields'          => 'ids',
		    'posts_per_page'  => 20,
		    'post_type' => 'shop_order',
		    'post_status' => 'any'
		));
		?>
		<div id="pdf-invoices-preview-frame-container" class="pdf-invoices-preview-frame-container">
			<div class="pdf-invoices-preview-frame-header">
				<label for="order_id"><?php _e('Select Order ID', 'woocommerce-pdf-invoices') ?></label>
				<select name="order_id" id="pdf-invoices-preview-order-id">
					<?php foreach ($shop_order_ids as $key => $shop_order_id) {
						if($key == 0) {
							echo '<option value="' . $shop_order_id . '" selected>' . $shop_order_id . '</option>';
							continue;
						}
						echo '<option value="' . $shop_order_id . '">' . $shop_order_id . '</option>';
					} ?>
				</select>
			</div>
			<div id="pdf-invoices-preview-spinner" class="pdf-invoices-preview-spinner">
				<i class="el el-refresh el-spin"></i>
			</div>
			<iframe id="pdf-invoices-preview-frame" src="" width="100%" height="100%" class="pdf-invoices-preview-frame">

			</iframe>
		</div>
		<div id="pdf-invoices-preview-frame-overlay" class="pdf-invoices-preview-frame-overlay"></div>
		<?php
	}

   	public function add_bulk_action_download_invoice($bulk_actions)
	{
        $bulk_actions['download_invoice'] = __( 'Download Invoice', 'woocommerce-pdf-invoices');
        return $bulk_actions;
    }

    public function handle_bulk_action_download_invoice($redirect_to, $action, $order_ids)
    {
        $checkAction = strpos($action, 'assign_printer_');
        
        if ( $action !== 'download_invoice') {
            return $redirect_to;
        }
		
		$files = array();
		foreach ($order_ids as $order_id) {
			$invoice = $this->upload_dir . '/' . $order_id . '.pdf';
			$invoice_exists = file_exists($invoice);

			if($invoice_exists) {
				$files[] = $invoice;
			}
		}

		if(empty($files)) {
			wp_die(__('No Invoice PDFs found', 'woocommerce-pdf-invoices'));
		}

		$zipname = 'invoices-' . time() . '.zip';
		$zip = new ZipArchive;
		$zip->open($zipname, ZipArchive::CREATE);
		foreach ($files as $file) {
			$zip->addFile($file,basename($file));
		}
		$zip->close();

		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename='.$zipname);
		header('Content-Length: ' . filesize($zipname));
		readfile($zipname);

        exit();
    }
}
