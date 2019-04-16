<?php

    /**
     * For full documentation, please visit: http://docs.reduxframework.com/
     * For a more extensive sample-config file, you may look at:
     * https://github.com/reduxframework/redux-framework/blob/master/sample/sample-config.php
     */

    if ( ! class_exists( 'Redux' ) ) {
        return;
    }

    // This is your option name where all the Redux data is stored.
    $opt_name = "woocommerce_pdf_invoices_options";

    /**
     * ---> SET ARGUMENTS
     * All the possible arguments for Redux.
     * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
     * */

    $theme = wp_get_theme(); // For use with some settings. Not necessary.

    $args = array(
        'opt_name' => 'woocommerce_pdf_invoices_options',
        'use_cdn' => TRUE,
        'dev_mode' => FALSE,
        'display_name' => 'WooCommerce PDF Invoices',
        'display_version' => '1.0.7',
        'page_title' => 'WooCommerce PDF Invoices',
        'update_notice' => TRUE,
        'intro_text' => '',
        'footer_text' => '&copy; '.date('Y').' weLaunch',
        'admin_bar' => TRUE,
        'menu_type' => 'submenu',
        'menu_title' => 'PDF Invoices',
        'allow_sub_menu' => TRUE,
        'page_parent' => 'woocommerce',
        'page_parent_post_type' => 'your_post_type',
        'customizer' => FALSE,
        'default_mark' => '*',
        'hints' => array(
            'icon_position' => 'right',
            'icon_color' => 'lightgray',
            'icon_size' => 'normal',
            'tip_style' => array(
                'color' => 'light',
            ),
            'tip_position' => array(
                'my' => 'top left',
                'at' => 'bottom right',
            ),
            'tip_effect' => array(
                'show' => array(
                    'duration' => '500',
                    'event' => 'mouseover',
                ),
                'hide' => array(
                    'duration' => '500',
                    'event' => 'mouseleave unfocus',
                ),
            ),
        ),
        'output' => TRUE,
        'output_tag' => TRUE,
        'settings_api' => TRUE,
        'cdn_check_time' => '1440',
        'compiler' => TRUE,
        'page_permissions' => 'manage_options',
        'save_defaults' => TRUE,
        'show_import_export' => TRUE,
        'database' => 'options',
        'transient_time' => '3600',
        'network_sites' => TRUE,
    );

    Redux::setArgs( $opt_name, $args );

    /*
     * ---> END ARGUMENTS
     */

    /*
     * ---> START HELP TABS
     */

    $tabs = array(
        array(
            'id'      => 'help-tab',
            'title'   => __( 'Information', 'woocommerce-pdf-invoices' ),
            'content' => __( '<p>Need support? Please use the comment function on codecanyon.</p>', 'woocommerce-pdf-invoices' )
        ),
    );
    Redux::setHelpTab( $opt_name, $tabs );

    // Set the help sidebar
    // $content = __( '<p>This is the sidebar content, HTML is allowed.</p>', 'woocommerce-pdf-invoices' );
    // Redux::setHelpSidebar( $opt_name, $content );


    /*
     * <--- END HELP TABS
     */


    /*
     *
     * ---> START SECTIONS
     *
     */
    global $woocommerce;
    $mailer = $woocommerce->mailer();
    $wc_emails = $mailer->get_emails();

    $non_order_emails = array(
        'customer_note',
        'customer_reset_password',
        'customer_new_account'
    );

    $emails = array();
    foreach ($wc_emails as $class => $email) {
        if ( !in_array( $email->id, $non_order_emails ) ) {
            switch ($email->id) {
                case 'new_order':
                    $emails[$email->id] = sprintf('%s (%s)', $email->title, __( 'Admin email', 'woocommerce-pdf-invoices-packing-slips' ) );
                    break;
                case 'customer_invoice':
                    $emails[$email->id] = sprintf('%s (%s)', $email->title, __( 'Manual email', 'woocommerce-pdf-invoices-packing-slips' ) );
                    break;
                default:
                    $emails[$email->id] = $email->title;
                    break;
            }
        }
    }

    Redux::setSection( $opt_name, array(
        'title'  => __( 'PDF Invoices', 'woocommerce-pdf-invoices' ),
        'id'     => 'general',
        'desc'   => __( 'Need support? Please use the comment function on codecanyon.', 'woocommerce-pdf-invoices' ),
        'icon'   => 'el el-home',
    ) );

    Redux::setSection( $opt_name, array(
        'title'      => __( 'General', 'woocommerce-pdf-invoices' ),
        // 'desc'       => __( '', 'woocommerce-pdf-invoices' ),
        'id'         => 'general-settings',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'enable',
                'type'     => 'checkbox',
                'title'    => __( 'Enable', 'woocommerce-pdf-invoices' ),
                'subtitle' => __( 'Enable PDF Invoices to use the options below', 'woocommerce-pdf-invoices' ),
                'default' => 1,
            ),
            array(
                'id'       => 'generalAutomatic',
                'type'     => 'checkbox',
                'title'    => __( 'Create Invoices Automatically', 'woocommerce-pdf-invoices' ),
                'subtitle' => __( 'PDF invoices will be created for each order automatically.', 'woocommerce-pdf-invoices' ),
                'default' => 1,
            ),
            array(
                'id'       => 'generalAttachToMail',
                'type'     => 'checkbox',
                'title'    => __( 'Attach Invoice to Email', 'woocommerce-pdf-invoices' ),
                'subtitle' => __( 'Attach invoices automatically to orders.', 'woocommerce-pdf-invoices' ),
                'default' => 1,
            ),
            array(
                'id'     =>'generalAttachToMailStatus',
                'type'  => 'select',
                'title' => __('Attach to Email order Statuses', 'woocommerce-pdf-invoices'), 
                'multi' => true,
                'options' => $emails,
                // 'options'  => array(
                //     'new_order' => __( 'New order', 'woocommerce-pdf-invoices' ),
                //     'customer_on_hold_order'    => __( 'Order on-hold', 'woocommerce-pdf-invoices' ),
                //     'customer_processing_order' => __( 'Processing order', 'woocommerce-pdf-invoices' ),
                //     'customer_completed_order'  => __( 'Completed order', 'woocommerce-pdf-invoices' ),
                //     'customer_invoice'          => __( 'Customer invoice', 'woocommerce-pdf-invoices' ),
                // ),
                'default' => array(
                    'new_order',
                    'customer_invoice',
                    'customer_processing_order',
                    'customer_completed_order',
                    'customer_refunded_order',
                ),
                'required' => array('generalAttachToMail','equals','1'),
            ),
            array(
                'id'       => 'generalShowInvoice',
                'type'     => 'checkbox',
                'title'    => __( 'Show Invoice to Customers', 'woocommerce-pdf-invoices' ),
                'subtitle' => __( 'Show Invoice in Thank you and Order detail pages.', 'woocommerce-pdf-invoices' ),
                'default' => 1,
            ),
            array(
                'id'       => 'generalShowTaxRate',
                'type'     => 'checkbox',
                'title'    => __( 'Show Tax Rates', 'woocommerce-pdf-invoices' ),
                'subtitle' => __( 'Instead of showing the tax amount also show the tax rate.', 'woocommerce-pdf-invoices' ),
                'default' => 1,
            ),
            array(
                'id'       => 'generalRenderInvoice',
                'type'     => 'checkbox',
                'title'    => __( 'Render Invoice instead of Download', 'woocommerce-pdf-invoices' ),
                'subtitle' => __( 'Instead of downloading the invoice it will show the PDF directly in browser.', 'woocommerce-pdf-invoices' ),
                'default' => 0,
            ),
        )
    ) );

    Redux::setSection( $opt_name, array(
        'title'      => __( 'Layout', 'woocommerce-pdf-invoices' ),
        // 'desc'       => __( '', 'woocommerce-pdf-invoices' ),
        'id'         => 'layout',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'             => 'layoutPadding',
                'type'           => 'spacing',
                // 'output'         => array('.site-header'),
                'mode'           => 'padding',
                'units'          => array('px'),
                'units_extended' => 'false',
                'title'          => __('Padding', 'woocommerce-pdf-catalog'),
                'default'            => array(
                    'padding-top'     => '50px', 
                    'padding-right'   => '60px', 
                    'padding-bottom'  => '10px', 
                    'padding-left'    => '60px',
                    'units'          => 'px', 
                ),
            ),
            array(
                'id'     =>'layoutTextColor',
                'type'  => 'color',
                'title' => __('Text Color', 'woocommerce-pdf-invoices'), 
                'validate' => 'color',
                'default'   => '#333333',
            ),
            array(
                'id'     =>'layoutFontFamily',
                'type'  => 'select',
                'title' => __('Default Font', 'woocommerce-pdf-invoices'), 
                'options'  => array(
                    'dejavusans' => __('Sans', 'woocommerce-pdf-invoices' ),
                    'dejavuserif' => __('Serif', 'woocommerce-pdf-invoices' ),
                    'dejavusansmono' => __('Mono', 'woocommerce-pdf-invoices' ),
                    'droidsans' => __('Droid Sans', 'woocommerce-pdf-invoices'),
                    'droidserif' => __('Droid Serif', 'woocommerce-pdf-invoices'),
                    'lato' => __('Lato', 'woocommerce-pdf-invoices'),
                    'lora' => __('Lora', 'woocommerce-pdf-invoices'),
                    'merriweather' => __('Merriweather', 'woocommerce-pdf-invoices'),
                    'montserrat' => __('Montserrat', 'woocommerce-pdf-invoices'),
                    'opensans' => __('Open sans', 'woocommerce-pdf-invoices'),
                    'opensanscondensed' => __('Open Sans Condensed', 'woocommerce-pdf-invoices'),
                    'oswald' => __('Oswald', 'woocommerce-pdf-invoices'),
                    'ptsans' => __('PT Sans', 'woocommerce-pdf-invoices'),
                    'sourcesanspro' => __('Source Sans Pro', 'woocommerce-pdf-invoices'),
                    'slabo' => __('Slabo', 'woocommerce-pdf-invoices'),
                    'raleway' => __('Raleway', 'woocommerce-pdf-invoices'),
                ),
                'default'   => 'dejavusans',
            ),
            array(
                'id'     =>'layoutFontSize',
                'type'     => 'spinner', 
                'title'    => __('Default font size', 'woocommerce-pdf-invoices'),
                'default'  => '9',
                'min'      => '1',
                'step'     => '1',
                'max'      => '40',
            ),
            array(
                'id'     =>'layoutFontLineHeight',
                'type'     => 'spinner', 
                'title'    => __('Default line height', 'woocommerce-pdf-invoices'),
                'default'  => '12',
                'min'      => '1',
                'step'     => '1',
                'max'      => '40',
            ),
        )
    ) );

    Redux::setSection( $opt_name, array(
        'title'      => __( 'Header', 'woocommerce-pdf-invoices' ),
        // 'desc'       => __( '', 'woocommerce-pdf-invoices' ),
        'id'         => 'header',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'enableHeader',
                'type'     => 'checkbox',
                'title'    => __( 'Enable', 'woocommerce-pdf-invoices' ),
                'subtitle' => __( 'Enable header', 'woocommerce-pdf-invoices' ),
                'default' => '1',
            ),

            array(
                'id'     =>'headerBackgroundColor',
                'type' => 'color',
                'title' => __('Header background color', 'woocommerce-pdf-invoices'), 
                'validate' => 'color',
                'default' => '#333333',
                'required' => array('enableHeader','equals','1'),
            ),
            array(
                'id'     =>'headerTextColor',
                'type'  => 'color',
                'title' => __('Header text color', 'woocommerce-pdf-invoices'), 
                'validate' => 'color',
                'default' => '#FFFFFF',
                'required' => array('enableHeader','equals','1'),
            ),
            array(
                'id'     =>'headerFontSize',
                'type'     => 'spinner', 
                'title'    => __('Header font size', 'woocommerce-pdf-invoices'),
                'default'  => '8',
                'min'      => '1',
                'step'     => '1',
                'max'      => '40',
            ),
            array(
                'id'     =>'headerLayout',
                'type'  => 'select',
                'title' => __('Header Layout', 'woocommerce-pdf-invoices'), 
                'required' => array('enableHeader','equals','1'),
                'options'  => array(
                    'oneCol' => __('1/1', 'woocommerce-pdf-invoices' ),
                    'twoCols' => __('1/2 + 1/2', 'woocommerce-pdf-invoices' ),
                    'threeCols' => __('1/3 + 1/3 + 1/3', 'woocommerce-pdf-invoices' ),
                ),
                'default' => 'twoCols',
            ),
            array(
                'id'     =>'headerMargin',
                'type'     => 'spinner', 
                'title'    => __('Header Margin', 'woocommerce-pdf-invoices'),
                'default'  => '10',
                'min'      => '1',
                'step'     => '1',
                'max'      => '200',
                'required' => array('enableHeader','equals','1'),
            ),
            array(
                'id'     =>'headerHeight',
                'type'     => 'spinner', 
                'title'    => __('Header Height', 'woocommerce-pdf-invoices'),
                'default'  => '30',
                'min'      => '1',
                'step'     => '1',
                'max'      => '200',
                'required' => array('enableHeader','equals','1'),
            ),
            array(
                'id'     =>'headerVAlign',
                'type'  => 'select',
                'title' => __('Vertical Align', 'woocommerce-pdf-invoices'), 
                'required' => array('enableHeader','equals','1'),
                'options'  => array(
                    'top' => __('Top', 'woocommerce-pdf-invoices' ),
                    'middle' => __('Middle', 'woocommerce-pdf-invoices' ),
                    'bottom' => __('Bottom', 'woocommerce-pdf-invoices' ),
                ),
                'default' => 'middle',
            ),
            array(
                'id'     =>'headerTopLeft',
                'type'  => 'select',
                'title' => __('Top Left Header', 'woocommerce-pdf-invoices'), 
                'required' => array('enableHeader','equals','1'),
                'options'  => array(
                    'none' => __('None', 'woocommerce-pdf-invoices' ),
                    'bloginfo' => __('Blog information', 'woocommerce-pdf-invoices' ),
                    'text' => __('Custom text', 'woocommerce-pdf-invoices' ),
                    'pagenumber' => __('Pagenumber', 'woocommerce-pdf-invoices' ),
                    'image' => __('Image', 'woocommerce-pdf-invoices' ),
                    'exportinfo' => __('Export Information', 'woocommerce-pdf-invoices' ),
                    'qr' => __('QR-Code', 'woocommerce-pdf-invoices' ),
                ),
                'default' => 'bloginfo',
            ),
            array(
                'id'     =>'headerTopLeftText',
                'type'  => 'editor',
                'title' => __('Top Left Header Text', 'woocommerce-pdf-invoices'), 
                'required' => array('headerTopLeft','equals','text'),
                'args'   => array(
                    'teeny'            => false,
                )
            ),
            array(
                'id'     =>'headerTopLeftImage',
                'type' => 'media',
                'url'      => true,
                'title' => __('Top Left Header Image', 'woocommerce-pdf-invoices'), 
                'required' => array('headerTopLeft','equals','image'),
                'args'   => array(
                    'teeny'            => false,
                )
            ),
            array(
                'id'     =>'headerTopMiddle',
                'type'  => 'select',
                'title' => __('Top Middle Header', 'woocommerce-pdf-invoices'), 
                'required' => array('headerLayout','equals','threeCols'),
                'options'  => array(
                    'none' => __('None', 'woocommerce-pdf-invoices' ),
                    'bloginfo' => __('Blog information', 'woocommerce-pdf-invoices' ),
                    'text' => __('Custom text', 'woocommerce-pdf-invoices' ),
                    'pagenumber' => __('Pagenumber', 'woocommerce-pdf-invoices' ),
                    'image' => __('Image', 'woocommerce-pdf-invoices' ),
                    'exportinfo' => __('Export Information', 'woocommerce-pdf-invoices' ),
                    'qr' => __('QR-Code', 'woocommerce-pdf-invoices' ),
                ),
            ),
            array(
                'id'     =>'headerTopMiddleText',
                'type'  => 'editor',
                'title' => __('Top Middle Header Text', 'woocommerce-pdf-invoices'), 
                'required' => array('headerTopMiddle','equals','text'),
                'args'   => array(
                    'teeny'            => false,
                )
            ),
            array(
                'id'     =>'headerTopMiddleImage',
                'type' => 'media',
                'url'      => true,
                'title' => __('Top Middle Header Image', 'woocommerce-pdf-invoices'), 
                'required' => array('headerTopMiddle','equals','image'),
                'args'   => array(
                    'teeny'            => false,
                )
            ),
            array(
                'id'     =>'headerTopRight',
                'type'  => 'select',
                'title' => __('Top Right Header', 'woocommerce-pdf-invoices'), 
                'required' => array('headerLayout','equals',array('threeCols','twoCols')),
                'options'  => array(
                    'none' => __('None', 'woocommerce-pdf-invoices' ),
                    'bloginfo' => __('Blog information', 'woocommerce-pdf-invoices' ),
                    'text' => __('Custom text', 'woocommerce-pdf-invoices' ),
                    'pagenumber' => __('Pagenumber', 'woocommerce-pdf-invoices' ),
                    'image' => __('Image', 'woocommerce-pdf-invoices' ),
                    'exportinfo' => __('Export Information', 'woocommerce-pdf-invoices' ),
                    'qr' => __('QR-Code', 'woocommerce-pdf-invoices' ),
                ),
                'default' => 'pagenumber',
            ),
            array(
                'id'     =>'headerTopRightText',
                'type'  => 'editor',
                'title' => __('Top Right Header Text', 'woocommerce-pdf-invoices'), 
                'required' => array('headerTopRight','equals','text'),
                'args'   => array(
                    'teeny'            => false,
                )
            ),
            array(
                'id'     =>'headerTopRightImage',
                'type' => 'media',
                'url'      => true,
                'title' => __('Top Right Header Image', 'woocommerce-pdf-invoices'), 
                'required' => array('headerTopRight','equals','image'),
                'args'   => array(
                    'teeny'            => false,
                )
            ),
        )
    ) );

    Redux::setSection( $opt_name, array(
        'title'      => __( 'Address', 'woocommerce-pdf-invoices' ),
        // 'desc'       => __( '', 'woocommerce-pdf-invoices' ),
        'id'         => 'address',
        'subsection' => true,
        'fields'     => array(
            // array(
            //     'id'       => 'addressLayout',
            //     'type'     => 'image_select',
            //     'title'    => __( 'Select Layout', 'woocommerce-pdf-invoices' ),
            //     'options'  => array(
            //         '1'      => array('img'   => plugin_dir_url( __FILE__ ) . 'img/1.png'),
            //         '2'      => array('img'   => plugin_dir_url( __FILE__ ). 'img/2.png'),
            //         '3'      => array('img'   => plugin_dir_url( __FILE__ ). 'img/3.png'),
            //     ),
            //     'default' => '1'
            // ),
            array(
                'id'     =>'addressTextLeft',
                'type'  => 'editor',
                'title' => __('Address Text Left', 'woocommerce-pdf-invoices'),
                'args'   => array(
                    'teeny'            => false,
                ),
                'default' => '
                <span style="font-size: 9px;">WeLaunch - In den Sandbergen - 49808 Lingen (Ems)</span><br>
                <br>
                {{billing_company}}<br>
                {{billing_first_name}} {{billing_last_name}}<br>
                {{billing_address_1}} {{billing_address_2}}<br>
                {{billing_postcode}} {{billing_city}}<br>
                {{billing_state}} {{billing_country}}'
            ),
            array(
                'id'     =>'addressTextRight',
                'type'  => 'editor',
                'title' => __('Address Text Right', 'woocommerce-pdf-invoices'),
                'args'   => array(
                    'teeny'            => false,
                ),
                'default' => 
                    'Invoice No. {{id}}<br>
                    Invoice Date {{order_created}}<br>
                    <br>
                    Your Customer No. {{customer_id}}'
            ),
        )
    ) );
    Redux::setSection( $opt_name, array(
        'title'      => __( 'Content', 'woocommerce-pdf-invoices' ),
        // 'desc'       => __( '', 'woocommerce-pdf-invoices' ),
        'id'         => 'content',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'     =>'contentTextIntro',
                'type'  => 'editor',
                'title' => __('Content Intro Text', 'woocommerce-pdf-invoices'),
                'args'   => array(
                    'teeny'            => false,
                ),
                'default' => 
                    '<h4>Invoice No {{id}}</h4>
                    Dear {{billing_first_name}} {{billing_last_name}},<br>
                    <br>
                    thank you very much for your order and the trust you have placed in!<br>
                    I hereby invoice you for the following:'
            ),
            array(
                'id'       => 'contentItemsShowPos',
                'type'     => 'checkbox',
                'title'    => __( 'Show Position Field', 'woocommerce-packing-slips' ),
                'default' => 1,
            ),
            array(
                'id'       => 'contentItemsShowProduct',
                'type'     => 'checkbox',
                'title'    => __( 'Show Product Field', 'woocommerce-packing-slips' ),
                'default' => 1,
            ),
            array(
                'id'       => 'contentItemsShowSKU',
                'type'     => 'checkbox',
                'title'    => __( 'Show SKU Field', 'woocommerce-packing-slips' ),
                'default' => 1,
            ),
            array(
                'id'       => 'contentItemsShowWeight',
                'type'     => 'checkbox',
                'title'    => __( 'Show Weight Field', 'woocommerce-packing-slips' ),
                'default' => 1,
            ),
            array(
                'id'       => 'contentItemsShowQty',
                'type'     => 'checkbox',
                'title'    => __( 'Show Quantity Field', 'woocommerce-packing-slips' ),
                'default' => 1,
            ),
            array(
                'id'       => 'contentItemsShowPrice',
                'type'     => 'checkbox',
                'title'    => __( 'Show Price Field', 'woocommerce-packing-slips' ),
                'default' => 1,
            ),
            array(
                'id'     =>'contentItemsEvenBackgroundColor',
                'type' => 'color',
                'url'      => true,
                'title' => __('Even Items Background Color', 'woocommerce-pdf-invoices'), 
                'validate' => 'color',
                'default' => '#FFFFFF',
            ),
            array(
                'id'     =>'contentItemsEvenTextColor',
                'type' => 'color',
                'url'      => true,
                'title' => __('Even Items Text Color', 'woocommerce-pdf-invoices'), 
                'validate' => 'color',
                'default' => '#333333',
            ),
            array(
                'id'     =>'contentItemsOddBackgroundColor',
                'type' => 'color',
                'url'      => true,
                'title' => __('Odd Items Background Color', 'woocommerce-pdf-invoices'), 
                'validate' => 'color',
                'default' => '#ebebeb',
            ),
            array(
                'id'     =>'contentItemsOddTextColor',
                'type' => 'color',
                'url'      => true,
                'title' => __('Odd Items Text Color', 'woocommerce-pdf-invoices'), 
                'validate' => 'color',
                'default' => '#333333',
            ),
            array(
                'id'     =>'contentTextOutro',
                'type'  => 'editor',
                'title' => __('Content Outro Text', 'woocommerce-pdf-invoices'),
                'args'   => array(
                    'teeny'            => false,
                ),
                'default' => 
                    'Please transfer the invoice amount with invoice number to the account stated below.<br>
                    The invoice amount is due immediately.<br>
                    <br>
                    Payment Method: {{payment_method_title}}<br>
                    Shipping Method: {{shipping_method_title}}<br>
                    Your Note: {{customer_note}}<br>
                    <br>
                    Yours sincerely<br>
                    WeLaunch'
            ),
        )
    ) );

    Redux::setSection( $opt_name, array(
        'title'      => __( 'Footer', 'woocommerce-pdf-invoices' ),
        // 'desc'       => __( '', 'woocommerce-pdf-invoices' ),
        'id'         => 'footer',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'enableFooter',
                'type'     => 'checkbox',
                'title'    => __( 'Enable', 'woocommerce-pdf-invoices' ),
                'subtitle' => __( 'Enable footer', 'woocommerce-pdf-invoices' ),
                'default' => '1',
            ),
            array(
                'id'     =>'footerBackgroundColor',
                'type' => 'color',
                'url'      => true,
                'title' => __('Footer background color', 'woocommerce-pdf-invoices'), 
                'validate' => 'color',
                'default' => '#F7F7F7',
                'required' => array('enableFooter','equals','1'),
            ),
            array(
                'id'     =>'footerTextColor',
                'type'  => 'color',
                'url'      => true,
                'title' => __('Footer text color', 'woocommerce-pdf-invoices'), 
                'validate' => 'color',
                'default' => '#333333',
                'required' => array('enableFooter','equals','1'),
            ),
            array(
                'id'     =>'footerFontSize',
                'type'     => 'spinner', 
                'title'    => __('Footer font size', 'woocommerce-pdf-invoices'),
                'default'  => '8',
                'min'      => '1',
                'step'     => '1',
                'max'      => '40',
            ),
            array(
                'id'     =>'footerLayout',
                'type'  => 'select',
                'title' => __('Footer Layout', 'woocommerce-pdf-invoices'), 
                'required' => array('enableFooter','equals','1'),
                'options'  => array(
                    'oneCol' => __('1/1', 'woocommerce-pdf-invoices' ),
                    'twoCols' => __('1/2 + 1/2', 'woocommerce-pdf-invoices' ),
                    'threeCols' => __('1/3 + 1/3 + 1/3', 'woocommerce-pdf-invoices' ),
                    'fourCols' => __('1/4 + 1/4 + 1/4 + 1/4', 'woocommerce-pdf-invoices' ),
                ),
                'default' => 'fourCols',
                'required' => array('enableFooter','equals','1'),
            ),
            array(
                'id'     =>'footerMargin',
                'type'     => 'spinner', 
                'title'    => __('Footer Margin', 'woocommerce-pdf-invoices'),
                'default'  => '10',
                'min'      => '1',
                'step'     => '1',
                'max'      => '200',
                'required' => array('enableFooter','equals','1'),
            ),
            array(
                'id'     =>'footerHeight',
                'type'     => 'spinner', 
                'title'    => __('Footer Height', 'woocommerce-pdf-invoices'),
                'default'  => '30',
                'min'      => '1',
                'step'     => '1',
                'max'      => '200',
                'required' => array('enableFooter','equals','1'),
            ),
            array(
                'id'     =>'footerVAlign',
                'type'  => 'select',
                'title' => __('Vertical Align', 'woocommerce-pdf-invoices'), 
                'required' => array('enableFooter','equals','1'),
                'options'  => array(
                    'top' => __('Top', 'woocommerce-pdf-invoices' ),
                    'middle' => __('Middle', 'woocommerce-pdf-invoices' ),
                    'bottom' => __('Bottom', 'woocommerce-pdf-invoices' ),
                ),
                'default' => 'top',
            ),
            array(
                'id'     =>'footerTopLeft',
                'type'  => 'select',
                'title' => __('Top Left Footer', 'woocommerce-pdf-invoices'), 
                'required' => array('enableFooter','equals','1'),
                'options'  => array(
                    'none' => __('None', 'woocommerce-pdf-invoices' ),
                    'bloginfo' => __('Blog information', 'woocommerce-pdf-invoices' ),
                    'text' => __('Custom text', 'woocommerce-pdf-invoices' ),
                    'pagenumber' => __('Pagenumber', 'woocommerce-pdf-invoices' ),
                    'image' => __('Image', 'woocommerce-pdf-invoices' ),
                    'exportinfo' => __('Export Information', 'woocommerce-pdf-invoices' ),
                    'qr' => __('QR-Code', 'woocommerce-pdf-invoices' ),
                ),
                'default' => 'text',
            ),
            array(
                'id'     =>'footerTopLeftText',
                'type'  => 'editor',
                'title' => __('Top Left Footer Text', 'woocommerce-pdf-invoices'), 
                'required' => array('footerTopLeft','equals','text'),
                'args'   => array(
                    'teeny'            => false,
                ),
                'default' => 
                    'Company<br>
                    Address 123<br>
                    1234 City<br>
                    Country'
            ),
            array(
                'id'     =>'footerTopLeftImage',
                'type' => 'media',
                'url'      => true,
                'title' => __('Top Left Footer Image', 'woocommerce-pdf-invoices'), 
                'required' => array('footerTopLeft','equals','image'),
                'args'   => array(
                    'teeny'            => false,
                )
            ),
            array(
                'id'     =>'footerTopMiddleLeft',
                'type'  => 'select',
                'title' => __('Top Middle Left Footer', 'woocommerce-pdf-invoices'), 
                'required' => array('footerLayout','equals', array('fourCols')),
                'options'  => array(
                    'none' => __('None', 'woocommerce-pdf-invoices' ),
                    'bloginfo' => __('Blog information', 'woocommerce-pdf-invoices' ),
                    'text' => __('Custom text', 'woocommerce-pdf-invoices' ),
                    'pagenumber' => __('Pagenumber', 'woocommerce-pdf-invoices' ),
                    'image' => __('Image', 'woocommerce-pdf-invoices' ),
                    'exportinfo' => __('Export Information', 'woocommerce-pdf-invoices' ),
                    'qr' => __('QR-Code', 'woocommerce-pdf-invoices' ),
                ),
                'default' => 'text',
            ),
            array(
                'id'     =>'footerTopMiddleLeftText',
                'type'  => 'editor',
                'title' => __('Top Middle Left Footer Text', 'woocommerce-pdf-invoices'), 
                'required' => array('footerTopMiddleLeft','equals','text'),
                'args'   => array(
                    'teeny'            => false,
                ),
                'default' => 
                    'Tel.: 0160 123 1534<br>
                    E-Mail: info@yourdomain.com<br>
                    Web: https://yourdomain.com'
            ),
            array(
                'id'     =>'footerTopMiddleLeftImage',
                'type' => 'media',
                'url'      => true,
                'title' => __('Top Middle Left Footer Image', 'woocommerce-pdf-invoices'), 
                'required' => array('footerTopMiddleLeft','equals','image'),
                'args'   => array(
                    'teeny'            => false,
                )
            ),
            array(
                'id'     =>'footerTopMiddleRight',
                'type'  => 'select',
                'title' => __('Top Middle Right Footer', 'woocommerce-pdf-invoices'), 
                'required' => array('footerLayout','equals', array('fourCols','threeCols')),
                'options'  => array(
                    'none' => __('None', 'woocommerce-pdf-invoices' ),
                    'bloginfo' => __('Blog information', 'woocommerce-pdf-invoices' ),
                    'text' => __('Custom text', 'woocommerce-pdf-invoices' ),
                    'pagenumber' => __('Pagenumber', 'woocommerce-pdf-invoices' ),
                    'image' => __('Image', 'woocommerce-pdf-invoices' ),
                    'exportinfo' => __('Export Information', 'woocommerce-pdf-invoices' ),
                    'qr' => __('QR-Code', 'woocommerce-pdf-invoices' ),
                ),
                'default' => 'text',
            ),
            array(
                'id'     =>'footerTopMiddleRightText',
                'type'  => 'editor',
                'title' => __('Top Middle Right Footer Text', 'woocommerce-pdf-invoices'), 
                'required' => array('footerTopMiddleRight','equals','text'),
                'args'   => array(
                    'teeny'            => false,
                ),
                'default' => 
                    'VAT-ID: 123 435 456<br>
                    Managing Director: Your Name'
            ),
            array(
                'id'     =>'footerTopMiddleRightImage',
                'type' => 'media',
                'url'      => true,
                'title' => __('Top Middle Right Footer Image', 'woocommerce-pdf-invoices'), 
                'required' => array('footerTopMiddleRight','equals','image'),
                'args'   => array(
                    'teeny'            => false,
                )
            ),
            array(
                'id'     =>'footerTopRight',
                'type'  => 'select',
                'title' => __('Top Right Footer', 'woocommerce-pdf-invoices'), 
                'required' => array('footerLayout','equals', array('fourCols','threeCols','twoCols')),
                'options'  => array(
                    'none' => __('None', 'woocommerce-pdf-invoices' ),
                    'bloginfo' => __('Blog information', 'woocommerce-pdf-invoices' ),
                    'text' => __('Custom text', 'woocommerce-pdf-invoices' ),
                    'pagenumber' => __('Pagenumber', 'woocommerce-pdf-invoices' ),
                    'image' => __('Image', 'woocommerce-pdf-invoices' ),
                    'exportinfo' => __('Export Information', 'woocommerce-pdf-invoices' ),
                    'qr' => __('QR-Code', 'woocommerce-pdf-invoices' ),
                ),
                'default' => 'text',
            ),
            array(
                'id'     =>'footerTopRightText',
                'type'  => 'editor',
                'title' => __('Top Right Footer Text', 'woocommerce-pdf-invoices'), 
                'required' => array('footerTopRight','equals','text'),
                'args'   => array(
                    'teeny'            => false,
                ),
                'default' => 
                    'Bank: Deutsche Bank<br>
                    IBAN: DE 123345 3 456<br>
                    BIC: GEN0123'
            ),
            array(
                'id'     =>'footerTopRightImage',
                'type' => 'media',
                'url'      => true,
                'title' => __('Top Right Footer Image', 'woocommerce-pdf-invoices'), 
                'required' => array('footerTopRight','equals','image'),
                'args'   => array(
                    'teeny'            => false,
                )
            ),
        )
    ) );

    Redux::setSection( $opt_name, array(
        'title'      => __( 'Advanced settings', 'woocommerce-pdf-invoices' ),
        'desc'       => __( 'Custom stylesheet / javascript.', 'woocommerce-pdf-invoices' ),
        'id'         => 'advanced',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'debugMode',
                'type'     => 'checkbox',
                'title'    => __( 'Enable Debug Mode', 'woocommerce-pdf-invoices' ),
                'subtitle' => __( 'This stops creating the PDF and shows the plain HTML.', 'woocommerce-pdf-invoices' ),
                'default'   => 0,
            ),
            array(
                'id'       => 'debugMPDF',
                'type'     => 'checkbox',
                'title'    => __( 'Enable MPDF Debug Mode', 'woocommerce-pdf-invoices' ),
                'subtitle' => __( 'Show image , font or other errors in the PDF Rendering engine.', 'woocommerce-pdf-invoices' ),
                'default'   => 0,
            ),
            array(
                'id'       => 'customCSS',
                'type'     => 'ace_editor',
                'mode'     => 'css',
                'title'    => __( 'Custom CSS', 'woocommerce-pdf-invoices' ),
                'subtitle' => __( 'Add some stylesheet if you want.', 'woocommerce-pdf-invoices' ),
            ),
        )
    ));


    Redux::setSection( $opt_name, array(
        'title'  => __( 'Preview', 'woocommerce-pdf-invoices' ),
        'id'     => 'preview',
        'desc'   => __( 'Need support? Please use the comment function on codecanyon.', 'woocommerce-pdf-invoices' ),
        'icon'   => 'el el-eye-open',
    ) );

    /*
     * <--- END SECTIONS
     */
