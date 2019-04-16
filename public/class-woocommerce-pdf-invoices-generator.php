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
class WooCommerce_PDF_Invoices_Generator extends WooCommerce_PDF_Invoices {

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
	public function __construct( $plugin_name, $version ) 
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->data = new stdClass;
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
    }

	public function setup_data($order_id)
	{
    	global $post, $woocommerce, $wpdb;

    	$this->woocommerce_version = $woocommerce->version;

    	// default Variables
		$this->data->blog_name = get_bloginfo('name');
		$this->data->blog_description  = get_bloginfo('description');

		$this->data->order_id = $order_id;

		// Order Data
		$order = wc_get_order($order_id);
		if(!$order) {
			wp_die( __('No Woo Order Found', 'woocommerce-pdf-invoices') );
		}
		$this->data->order = $order;

		$order_data = $order->get_data();
		unset($order_data['meta_data']);
		unset($order_data['data_store']);
		unset($order_data['default_data']);
		unset($order_data['line_items']);
		unset($order_data['tax_lines']);

		if(!empty($order_data['billing'])) {
			foreach ($order_data['billing'] as $key => $value) {
				$order_data['billing_' . $key] = $value;
			}
			unset($order_data['billing']);
		}

		if(!empty($order_data['shipping'])) {
			foreach ($order_data['shipping'] as $key => $value) {
				$order_data['shipping_' . $key] = $value;
			}
			unset($order_data['shipping']);
		}

		$order_data['order_created'] = $order_data['date_created']->date_i18n(get_option('date_format'));
		$order_data['order_modified'] = $order_data['date_modified']->date_i18n(get_option('date_format'));
		unset($order_data['date_created']);
		unset($order_data['date_modified']);

		// Prices
		$order_data['subtotal'] = $order_data['total'] - $order_data['total_tax'];

		// Shipping
		$order_data['shipping_method_title'] = $order->get_shipping_method();

		// Order Meta Data
		$order_meta_data = get_post_meta( $order_id, '', true);
		if(!empty($order_meta_data)) {
			$tmp = array();
			foreach ($order_meta_data as $key => $value) {
				if(is_array($value) && !empty($value)) {
					$tmp[$key] = $value[0];
				} else {
					$tmp[$key] = $value;
				}
			}
			$order_meta_data = $tmp;
		}

		$order_meta_data = apply_filters('woocommerce_pdf_invoices_order_meta_data', $order_meta_data);
		$order_data = array_merge($order_data, $order_meta_data);

		// Customer Meta Data
		if(isset($order_data['customer_id']) && !empty($order_data['customer_id'])) {
			$customer_meta_data = get_user_meta( $order_data['customer_id'], '', true);
			if(!empty($customer_meta_data)) {
				$tmp = array();
				foreach ($customer_meta_data as $key => $value) {
					if(is_array($value) && !empty($value)) {
						$tmp[$key] = $value[0];
					} else {
						$tmp[$key] = $value;
					}
				}
				$customer_meta_data = $tmp;
			}
			$customer_meta_data = apply_filters('woocommerce_pdf_invoices_customer_meta_data', $customer_meta_data);
			$order_data = array_merge($order_data, $customer_meta_data);
		}

		unset($order_data['shipping_lines']);
		unset($order_data['fee_lines']);
		unset($order_data['tax_lines']);

		$this->data->order_data = apply_filters('woocommerce_pdf_invoices_order_data', $order_data);
		$this->data->items = apply_filters('woocommerce_pdf_invoices_order_items', $order->get_items());

		return TRUE;
	}

    public function create_pdf($upload_dir, $output = false, $credit_note = false)
    {
    	if(!class_exists('\Mpdf\Mpdf')) return FALSE;

    	require_once(plugin_dir_path( dirname( __FILE__ ) ) . 'fonts/customFonts.php');

    	$headerMargin = $this->get_option('headerMargin');
    	$footerMargin = $this->get_option('footerMargin');

		$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
		$fontData = $defaultFontConfig['fontdata'];

    	try {
			$mpdfConfig = array(
				'mode' => 'utf-8', 
				'format' => 'A4',    // format - A4, for example, default ''
				'default_font_size' => 0,     // font size - default 0
				'default_font' => '',    // default font family
				'margin_left' => 0,    	// 15 margin_left
				'margin_right' => 0,    	// 15 margin right
				'margin_top' => $headerMargin,     // 16 margin top
				'margin_bottom' => $footerMargin,    	// margin bottom
				'margin_header' => 0,     // 9 margin header
				'margin_footer' => 0,     // 9 margin footer
				'orientation' => 'P',  	// L - landscape, P - portrait
				'tempDir' => dirname( __FILE__ ) . '/../cache/',
				'fontDir' => array(
					plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/mpdf/mpdf/ttfonts/',
					plugin_dir_path( dirname( __FILE__ ) ) . 'fonts/',
				),
			    'fontdata' => array_merge($fontData, $customFonts),
			);
			$mpdf = new \Mpdf\Mpdf($mpdfConfig);	

			if($this->get_option('debugMPDF')) {
				$mpdf->debug = true;
				$mpdf->debugfonts = true;
				$mpdf->showImageErrors = true;
			}

			$css = $this->build_CSS();

			if($this->get_option('enableHeader')) {
				$header =  apply_filters('woocommerce_pdf_invoices_header', $this->get_header());
				$mpdf->SetHTMLHeader($header);
			}

			$html = '<div class="frame">';

				$html .= apply_filters('woocommerce_pdf_invoices_address', $this->get_address());
				$html .= apply_filters('woocommerce_pdf_invoices_content', $this->get_content());

			$html .= '</div>';

			if($this->get_option('enableFooter')) {
				$footer =  apply_filters('woocommerce_pdf_invoices_footer', $this->get_footer());
				$mpdf->SetHTMLFooter($footer);
			}

			$html = $this->replace_vars($html);

			if($credit_note) {
				$html = str_replace( __('Invoice', 'woocommerce-pdf-invoices'), __('Credit Note', 'woocommerce-pdf-invoices'), $html);
			}

			$filename = $this->escape_filename($this->data->order_data['id']);

			if($this->get_option('debugMode')) {
				echo $header;
				echo $css.$html;
				echo $footer;
				die();
			}
			$mpdf->useAdobeCJK = true;
			$mpdf->autoScriptToLang = true;
			$mpdf->autoLangToFont = true;
			$mpdf->WriteHTML($css . $html);

			if($output) {
				$mpdf->Output($upload_dir . '/' . $filename . '.pdf', 'I');
				die();
			}
			
			if(file_exists($upload_dir . '/' . $filename . '.pdf')){
				rename($upload_dir . '/' . $filename . '.pdf', $upload_dir . '/' . $filename . '_old_' . uniqid() . '.pdf');
			}

			$mpdf->Output($upload_dir . '/' . $filename . '.pdf', 'F');
			return true;
    	} catch (Exception $e) {
    		echo $e->getMessage();
    		return false;
    	}

		exit;
    }

    public function build_CSS()
    {
    	$layoutPadding = $this->get_option('layoutPadding');
    	
    	// Font
    	$layoutFontFamily = $this->get_option('layoutFontFamily') ? $this->get_option('layoutFontFamily') : 'dejavusans';
    	$layoutTextColor = $this->get_option('layoutTextColor');

    	$layoutFontSize = $this->get_option('layoutFontSize') ? $this->get_option('layoutFontSize') : '11';
    	$layoutFontSize = intval($layoutFontSize);

    	$layoutFontLineHeight =  $this->get_option('layoutFontLineHeight') ? $this->get_option('layoutFontLineHeight') : $layoutFontSize + 6; 
    	$layoutFontLineHeight = intval($layoutFontLineHeight);

		$css = '
		<head>
			<style media="all">';


		$css .= '
			body, table { 
				color: ' . $layoutTextColor . ';
				font-family: ' . $layoutFontFamily . ', sans-serif;
				font-size: ' . $layoutFontSize . 'pt;
				line-height: ' . $layoutFontLineHeight . 'pt;
	 		}

			table {
				width: 100%;
				text-align: left;
				border-spacing: 0;
			}

			table th, table td {
				padding: 4px 5px;
				text-align: left;
			}

	 		.header, .footer {
				padding-top: 10px;
				padding-bottom: 10px;
				padding-right: ' . $layoutPadding['padding-right'] . '; 
				padding-left: ' . $layoutPadding['padding-left'] . '; 
	 		}

	 		h1 {
				font-size: 20pt;
				line-height: 26pt;
	 		}

	 		h2 {
				font-size: 18pt;
				line-height: 24pt;
	 		}

	 		h3 {
				font-size: 16pt;
				line-height: 22pt;
	 		}

	 		h4 {
				font-size: 14pt;
				line-height: 20pt;
	 		}

	 		h5 {
				font-size: 12pt;
				line-height: 18pt;
	 		}

	 		.col {
				float: left;
	 		}
			
			.col-4 {
				width: 44%;
			}
	
	 		.col-8 {
	 			width: 66%;
	 		}

	 		.row {
	 			clear: both;
	 			float: none;
	 		}

	 		.frame {
				padding-top: ' . $layoutPadding['padding-top'] . '; 
				padding-right: ' . $layoutPadding['padding-right'] . '; 
				padding-bottom: ' . $layoutPadding['padding-bottom'] . '; 
				padding-left: ' . $layoutPadding['padding-left'] . '; 
 			}';

	 	// Header
	 	$headerBackgroundColor = $this->get_option('headerBackgroundColor');
		$headerTextColor = $this->get_option('headerTextColor');
		$headerFontSize = intval($this->get_option('headerFontSize'));

		$css .= '
			.header {
				color: ' . $headerTextColor . ';
				background-color: ' . $headerBackgroundColor . ';
				font-size: ' . $headerFontSize . 'pt;
			}';

    	// Items
    	$contentItemsEvenBackgroundColor = $this->get_option('contentItemsEvenBackgroundColor');
		$contentItemsEvenTextColor = $this->get_option('contentItemsEvenTextColor');

    	$contentItemsOddBackgroundColor = $this->get_option('contentItemsOddBackgroundColor');
		$contentItemsOddTextColor = $this->get_option('contentItemsOddTextColor');

		$css .= '
			table.content-items {
				margin-top: 20px;
				margin-bottom: 20px;
			}

			table.content-items tr.even { 
				background-color: ' . $contentItemsEvenBackgroundColor . ';
				color: ' . $contentItemsEvenTextColor . ';
	 		}
	 		table.content-items tr.odd { 
				background-color: ' . $contentItemsOddBackgroundColor . ';
				color: ' . $contentItemsOddTextColor . ';
	 		}
	 		table.content-items tfoot tr.black-border td {
				border-top: 1px solid #cecece;
 			}
 			table.content-items .content-item-total {
 				text-align: right;
			}

			table.content-items .discount {
				color: #F44336;
			}';


	 	// Foooter
	 	$footerBackgroundColor = $this->get_option('footerBackgroundColor');
		$footerTextColor = $this->get_option('footerTextColor');
		$footerFontSize = intval($this->get_option('footerFontSize'));

		$css .= '
			.footer {
				color: ' . $footerTextColor . ';
				background-color: ' . $footerBackgroundColor . ';
				font-size: ' . $footerFontSize . 'pt;
			}';

		$customCSS = $this->get_option('customCSS');
		if(!empty($customCSS))
		{
			$css .= $customCSS;
		}

		$css .= '
			</style>

		</head>';

		return $css;
    }

    public function get_address()
    {
    	
    	$addressTextLeft = apply_filters('woocommerce_pdf_invoices_address_left', $this->get_option('addressTextLeft'));
    	$addressTextRight = apply_filters('woocommerce_pdf_invoices_address_right', $this->get_option('addressTextRight'));

		$address = '
		<div id="address-container" class="row">
			<div id="address-left" class="col col-8">' . wpautop( $addressTextLeft ) . '</div>
			<div id="address-right" class="col col-10">' . wpautop( $addressTextRight ) . '</div>
		</div>';

		return $address;
    }

    public function get_content()
    {
    	$contentTextIntro = apply_filters('woocommerce_pdf_invoices_content_intro', $this->get_option('contentTextIntro'));
    	$contentTextOutro = apply_filters('woocommerce_pdf_invoices_content_outro', $this->get_option('contentTextOutro'));

    	$content = '<div id="content-container" class="row">';

	    	if(!empty($contentTextIntro)) {
				$content .= '
				<div class="content-text-intro">
					' . wpautop( $contentTextIntro ) . '
				</div>';
			}

			if(!empty($this->data->items)) {
				$content .= '
				<div class="content-items-container">
					' . $this->get_items_table($this->data->items) . '
				</div>';
			}

			if(!empty($contentTextOutro)) {
				$content .= '
				<div class="content-text-outro">
					' . wpautop( $contentTextOutro ) . '
				</div>';
			}

		$content .= '</div>';

		return $content;
    }

    public function get_items_table($items) 
    {
    	$html = "";
    	if(empty($items)) {
    		return $html;
    	}

    	$showData = array(
			'showPos' => $this->get_option('contentItemsShowPos'),
			'showProduct' => $this->get_option('contentItemsShowProduct'),
			'showSKU' => $this->get_option('contentItemsShowSKU'),
			'showWeight' => $this->get_option('contentItemsShowWeight'),
			'showQty' => $this->get_option('contentItemsShowQty'),
			'showPrice' => $this->get_option('contentItemsShowPrice'),
    	);
    	// var_dump($showData);
    	// echo count(array_filter($showData));
    	// die('');

    	$html .= 
    	'<table class="content-items">
	    	<thead>
				<tr class="odd">
					' . ($showData['showPos'] ? '<th width="10%">' . __('Pos.', 'woocommerce-pdf-invoices') . '</th>' : '') . '
					' . ($showData['showProduct'] ? '<th width="30%">' . __('Product', 'woocommerce-pdf-invoices') . '</th>' : '') . '
					' . ($showData['showSKU'] ? '<th width="20%">' . __('SKU', 'woocommerce-pdf-invoices') . '</th>' : '') . '
					' . ($showData['showWeight'] ? '<th width="10%">' . __('Weight', 'woocommerce-pdf-invoices') . '</th>' : '') . '
					' . ($showData['showQty'] ? '<th width="10%">' . __('Qty', 'woocommerce-pdf-invoices') . '</th>' : '') . '
					' . ($showData['showPrice'] ? '<th width="10%">' . __('Price', 'woocommerce-pdf-invoices') . '</th>' : '') . '
					<th width="20%" class="content-item-total">' . __('Total', 'woocommerce-pdf-invoices') . '</th>
				</tr>
	    	</thead>
	    	<tbody>';

			do_action( 'woocommerce_order_details_before_order_table_items', $this->data->order );

			$tax_display = get_option( 'woocommerce_tax_display_cart' );

	    	$i = 1;
	    	foreach ($items as $item) {
	    		
	    		$item_id			= $item->get_id();
	    		$item_data 			= $item->get_data();
	    		$product 			= $item->get_product();
				$is_visible        	= $product && $product->is_visible();
				$product_permalink 	= apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $this->data->order );

				if ( 'excl' === $tax_display ) {
					$subtotal = $this->data->order->get_line_subtotal( $item );
				} else {
					$subtotal = $this->data->order->get_line_subtotal( $item, true );
				}

	    		$tr_class = ($i % 2 == 0) ? 'odd' : 'even';

	    		$html .= 
	    			'<tr class="' . $tr_class . '">';

	    				if($showData['showPos']) {
	    					$html .= '<td>' . $i . '</td>';
	    				}

						if($showData['showProduct']) {
	    					$html .=  '<td>' . 
	    						apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible );

								do_action( 'woocommerce_order_item_meta_start', $item->get_id(), $item, $this->data->order, false );

								$html .= wc_display_item_meta( $item, array('echo' => false) );

								do_action( 'woocommerce_order_item_meta_end', $item->get_id(), $item, $this->data->order, false );

							$html .= '</td>';
	    				}

						if($showData['showSKU']) {
	    					$html .= '<td>' . ($product->get_sku() ? $product->get_sku() : __('N/A', 'woocommerce-packing-slips')) . '</td>';
	    				}

						if($showData['showWeight']) {
	    					$html .= '<td>' . ($product->get_weight() ? $product->get_weight() : __('N/A', 'woocommerce-packing-slips')) . '</td>';
	    				}

						if($showData['showQty']) {
	    					$html .= '<td>' . apply_filters( 
									'woocommerce_order_item_quantity_html', 
									$item->get_quantity(), 
									$item ) .
								'</td>';
	    				}

	    				if($showData['showPrice']) {
	    					$html .= '<td>' . wc_price( $subtotal / $item->get_quantity()) . '</td>';
    					}

						$html .=
						'<td class="content-item-total">' . $this->data->order->get_formatted_line_subtotal( $item ) .'</td>
	    			</tr>';
				$i++;
	    	}

	    	do_action( 'woocommerce_order_details_after_order_table_items', $this->data->order );

    	$html .= 
    		'</tbody>
    		<tfoot>';

				foreach ( $this->data->order->get_order_item_totals() as $key => $total ) {

					$tr_class = ($i % 2 == 0) ? 'odd' : 'even';

					$html .= '
					<tr class="' . $tr_class . '">
						<td></td>
						<td colspan="' . (count(array_filter($showData)) - 1) . '">' . $total['label'] . '</td>
						<td class="content-item-total">' . $total['value'] . '</td>
					</tr>';
					$i++;
				}
    	$html .= '
    		</tfoot>
    	</table>';
    	
    	return $html;
    }

	public function get_header()
    {
    	$headerLayout = $this->get_option('headerLayout');
    	$this->get_option('headerHeight') ? $headerHeight = $this->get_option('headerHeight') : $headerHeight = 'auto';
		$headerVAlign = $this->get_option('headerVAlign');

    	$topLeft = $this->get_option('headerTopLeft');
    	$topMiddle = $this->get_option('headerTopMiddle');
    	$topRight = $this->get_option('headerTopRight');

    	$header = "";

    	if($headerLayout == "oneCol")
    	{
			$header .= '
			<table class="header">
				<tr>
					<td height="' . $headerHeight . '" valign="' . $headerVAlign . '" width="100%" style="text-align: center;">' . $this->get_header_footer_type($topLeft, 'headerTopLeft') . '</td>
				</tr>
			</table>';
    	} elseif($headerLayout == "threeCols") {
			$header .= '
			<table class="header">
				<tr>
					<td height="' . $headerHeight . '" valign="' . $headerVAlign . '" width="33%" style="text-align: left;">' . $this->get_header_footer_type($topLeft, 'headerTopLeft') . '</td>
					<td height="' . $headerHeight . '" valign="' . $headerVAlign . '" width="33%" style="text-align: center;">' . $this->get_header_footer_type($topMiddle, 'headerTopMiddle') . '</td>
					<td height="' . $headerHeight . '" valign="' . $headerVAlign . '" width="33%" style="text-align: right;">' . $this->get_header_footer_type($topRight, 'headerTopRight') . '</td>
				</tr>
			</table>';
		} else {
			$header .= '
			<table class="header">
				<tr>
					<td height="' . $headerHeight . '" valign="' . $headerVAlign . '" width="50%" style="text-align: left;">' . $this->get_header_footer_type($topLeft, 'headerTopLeft') . '</td>
					<td height="' . $headerHeight . '" valign="' . $headerVAlign . '" width="50%" style="text-align: right;">' . $this->get_header_footer_type($topRight, 'headerTopRight') . '</td>
				</tr>
			</table>';
		}

		return $header;
    }

    public function get_footer()
    {
    	$footerLayout = $this->get_option('footerLayout');
    	$this->get_option('footerHeight') ? $footerHeight = $this->get_option('footerHeight') : $footerHeight = 'auto';
		$footerVAlign = $this->get_option('footerVAlign');

    	$topLeft = $this->get_option('footerTopLeft');
    	$topRight = $this->get_option('footerTopRight');
    	$topMiddleLeft = $this->get_option('footerTopMiddleLeft');
    	$topMiddleRight = $this->get_option('footerTopMiddleRight');
    	
    	$footer = "";

    	if($footerLayout == "oneCol")
    	{
			$footer .= '
			<table class="footer">
				<tr>
					<td height="' . $footerHeight . '" valign="' . $footerVAlign . '" width="100%" style="text-align: center;">' . $this->get_header_footer_type($topLeft, 'footerTopLeft') . '</td>
				</tr>
			</table>';
    	} elseif($footerLayout == "threeCols") {
			$footer .= '
			<table class="footer">
				<tr>
					<td height="' . $footerHeight . '" valign="' . $footerVAlign . '" width="33%">' . $this->get_header_footer_type($topLeft, 'footerTopLeft') . '</td>
					<td height="' . $footerHeight . '" valign="' . $footerVAlign . '" width="33%">'. $this->get_header_footer_type($topMiddleRight, 'footerTopMiddleRight') . '</td>
					<td height="' . $footerHeight . '" valign="' . $footerVAlign . '" width="33%">' . $this->get_header_footer_type($topRight, 'footerTopRight') . '</td>
				</tr>
			</table>';
		}  elseif($footerLayout == "fourCols") {
			$footer .= '
			<table class="footer">
				<tr>
					<td height="' . $footerHeight . '" valign="' . $footerVAlign . '" width="25%" style="text-align: left;">' . $this->get_header_footer_type($topLeft, 'footerTopLeft') . '</td>
					<td height="' . $footerHeight . '" valign="' . $footerVAlign . '" width="25%" style="text-align: left;">' . $this->get_header_footer_type($topMiddleLeft, 'footerTopMiddleLeft') . '</td>
					<td height="' . $footerHeight . '" valign="' . $footerVAlign . '" width="25%" style="text-align: left;">'. $this->get_header_footer_type($topMiddleRight, 'footerTopMiddleRight') . '</td>
					<td height="' . $footerHeight . '" valign="' . $footerVAlign . '" width="25%" style="text-align: left;">' . $this->get_header_footer_type($topRight, 'footerTopRight') . '</td>
				</tr>
			</table>';
		}else {
			$footer .= '
			<table class="footer">
				<tr>
					<td height="' . $footerHeight . '" valign="' . $footerVAlign . '" width="50%" style="text-align: left;">' . $this->get_header_footer_type($topLeft, 'footerTopLeft') . '</td>
					<td height="' . $footerHeight . '" valign="' . $footerVAlign . '" width="50%" style="text-align: left;">' . $this->get_header_footer_type($topRight, 'footerTopRight') . '</td>
				</tr>
			</table>';
		}

		return $footer;
    }

    private function get_header_footer_type($type, $position)
    {
    	switch ($type) {
    		case 'text':
    			return wpautop( do_shortcode( $this->get_option($position.'Text') ) );
    			break;
    		case 'bloginfo':
    			return $this->data->blog_name.'<br/>'.$this->data->blog_description;
    			break;
    		case 'pagenumber':
				return __( 'Page:', 'woocommerce-pdf-invoices').' {PAGENO}';
    		case 'productinfo':
    			return $this->data->title.'<br/>'.get_permalink();
    			break;
			case 'categories':
				return wc_get_product_category_list($this->data->ID);
			case 'categorydescription':
				$terms = get_the_terms( $this->data->ID, 'product_cat' );
				$txt = "";
				if(!empty($terms)) {
					foreach ($terms as $term) {
						if(isset($term->description) && !empty($term->description)) {
							$txt = $term->description;
							break;
						}
					}
				}
				return $txt;
    		case 'image':
    			$image = $this->get_option($position.'Image');
    			$imageSrc = $image['url'];
    			$imageHTML = '<img src="' . $image['url'] . '">';
    			return $imageHTML;
    			break;
    		case 'exportinfo':
    			return date('d.m.y');
    			break;
			case 'qr':
				return '<barcode code="' . get_permalink($this->data->ID) . '" type="QR" class="barcode" size="0.8" error="M" />';
				break;
    		default:
    			return '';
    			break;
    	}
    }

    private function escape_filename($file)
    {
		// everything to lower and no spaces begin or end
		$file = strtolower(trim($file));

		// adding - for spaces and union characters
		$find = array(' ', '&', '\r\n', '\n', '+',',');
		$file = str_replace ($find, '-', $file);

		//delete and replace rest of special chars
		$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
		$repl = array('', '-', '');
		$file = preg_replace ($find, $repl, $file);

		return $file;
    }

	public function replace_vars($string)
	{
		if (preg_match_all("/{{(.*?)}}/", $string, $m)) {
			foreach ($m[1] as $i => $var) {

				$string = str_replace($m[0][$i], $this->data->order_data[$var], $string);
			}
	    }

		return $string;
	}
}