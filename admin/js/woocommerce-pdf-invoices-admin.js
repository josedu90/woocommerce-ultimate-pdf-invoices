(function( $ ) {
	'use strict';

	var openPreviewBtn = $('.woocommerce_page_woocommerce_pdf_invoices_options_options #9_section_group_li_a');
	var previewFrameContainer = $('#pdf-invoices-preview-frame-container');
	var previewFrame = $('#pdf-invoices-preview-frame');
	var previewFrameSpinner = $('#pdf-invoices-preview-spinner');
	var previewOrderID= $('#pdf-invoices-preview-order-id');
	var overlay = $('.pdf-invoices-preview-frame-overlay');
	var url = window.location.href.split('?')[0];

	previewFrame.load(function(){
        $(this).show();
        previewFrameSpinner.hide();
        previewFrame.show();
    });

	openPreviewBtn.on('click', function(e) {
		e.preventDefault();

		var order_id = $(previewOrderID).val();

		overlay.fadeIn();
		previewFrameContainer.fadeIn();
		previewFrameSpinner.show();

		previewFrame.attr("src", url + '?create_pdf_invoice=' + order_id);

	});

	previewOrderID.on('change', function(e) {

		var order_id = $(this).val();

		previewFrame.hide();
		previewFrameSpinner.show();
		previewFrame.attr("src", url + '?create_pdf_invoice=' + order_id);
	})

	overlay.on('click', function(e) {
		e.preventDefault();
		previewFrameContainer.fadeOut();
		overlay.fadeOut();
	});


})( jQuery );