

(function( $, window, document, undefined ){

// Setup namespace
window.WPImageWidget = window.WPImageWidget || {};



/**
 * Open Media Library
 *
 * The event to open the thickbox with the media library.
 */
WPImageWidget.openMediaLibrary = function( event ){
	
	var $this = $( event.target );
	
	WPImageWidget.widgetInstanceId = $this.attr( 'data-widget-id' );
	WPImageWidget.widgetInstanceCurrentField = $this.attr( 'data-widget-field' );
	WPImageWidget.widgetInstanceCurrentFieldId = $this.attr( 'data-widget-field-id' );
	
	tb_show("Add an Image", event.target.href, false);
	
	event.stopPropagation();
	return false;
};


/**
 * Remove the image
 */
WPImageWidget.removeImage = function( event ){
	var $this = $(this);
	var imageField = $this.parents( '.wp-image-widget-field' ).eq(0);
	
	
	if( imageField.size() == 0 ){
		return;
	}
	
	imageField.find( 'input' ).val( '' );
	imageField.removeClass( 'wp-image-widget-image--with-image' ).addClass( 'wp-image-widget-image--without-image' );
	imageField.find('.wp-image-widget-image').empty();
	
	event.stopPropagation();
	return false;
}


// bind the links to open
$( 'a.wp-image-widget-image--open-media-library' ).live( 'click', WPImageWidget.openMediaLibrary );
$( 'a.wp-image-widget-image--remove-image' ).live( 'click', WPImageWidget.removeImage );



/**
 * Over-ride Thickbox tb_remove
 *
 * Over-ride it to clear the widgetInstanceId variable
 */
var old_tb_remove = window.tb_remove;
window.tb_remove = function(){
	
	// clear the WPImageWidget.widgetInstanceId value in a real short bit in case someone uses it.
	setTimeout( function(){
		WPImageWidget.widgetInstanceId = null;
		WPImageWidget.widgetInstanceCurrentField = null;
	}, 20 );
	
	// call the original tb_remove
	old_tb_remove();
};


/**
 * Over-ride send_to_editor
 */
var old_send_to_editor = window.send_to_editor;
window.send_to_editor = function(h) {
	if( !WPImageWidget.widgetInstanceId ){
		if( old_send_to_editor ){
			return old_send_to_editor( h );
		}
		return;
	} else {
		
		if( WPImageWidget.widgetInstanceId && WPImageWidget.widgetInstanceCurrentField &&
				WPImageWidget.returnedImages[ WPImageWidget.widgetInstanceId ] &&
				WPImageWidget.returnedImages[ WPImageWidget.widgetInstanceId ][ WPImageWidget.widgetInstanceCurrentField ]) {
			
			var img = WPImageWidget.returnedImages[ WPImageWidget.widgetInstanceId ][ WPImageWidget.widgetInstanceCurrentField ];
			var imageField = $( '#' + WPImageWidget.widgetInstanceCurrentFieldId + '--field' );
			$( 'input[name="'+WPImageWidget.widgetInstanceCurrentField+'"]' ).val( img.id );
			
			imageField.find('.wp-image-widget-image').empty().append( img.widget_thumbnail );
			imageField.addClass( 'wp-image-widget-image--with-image' ).removeClass( 'wp-image-widget-image--without-image' );
			
			// Update the change link to open up this image
			var changeLink = imageField.find( '.wp-image-widget-image--change-image-buttons a.wp-image-widget-image--open-media-library' ); 
			var changeLinkHref = changeLink.attr('href');
			changeLinkHref = changeLinkHref.replace( /imageId=\d*/, 'imageId='+img.id );
			changeLink.attr( 'href', changeLinkHref );
			
		} 
		
		// close thickbox
		tb_remove();
		
		return;
		// Below is code from the image widget
		/*// ignore content returned from media uploader and use variables passed to window instead
	
		// store attachment id in hidden field
		jQuery( '#widget-'+self.IW_instance+'-image' ).val( self.IW_img_id );
	
		// display attachment preview
		jQuery( '#display-widget-'+self.IW_instance+'-image' ).html( self.IW_html );
	
		// change width & height fields in widget to match image
		jQuery( '#widget-'+self.IW_instance+'-width' ).val(jQuery( '#display-widget-'+self.IW_instance+'-image img').attr('width'));
		jQuery( '#widget-'+self.IW_instance+'-height' ).val(jQuery( '#display-widget-'+self.IW_instance+'-image img').attr('height'));
	
		// set alignment in widget
		jQuery( '#widget-'+self.IW_instance+'-align' ).val(self.IW_align);
	
		// set title in widget
		jQuery( '#widget-'+self.IW_instance+'-title' ).val(self.IW_title);
	
		// set caption in widget
		jQuery( '#widget-'+self.IW_instance+'-description' ).val(self.IW_caption);
	
		// set alt text in widget
		jQuery( '#widget-'+self.IW_instance+'-alt' ).val(self.IW_alt);
	
		// set link in widget
		jQuery( '#widget-'+self.IW_instance+'-link' ).val(self.IW_url);
	
		// close thickbox
		tb_remove();
	
		// change button text
		jQuery('#add_image-widget-'+self.IW_instance+'-image').html(jQuery('#add_image-widget-'+self.IW_instance+'-image').html().replace(/Add Image/g, 'Change Image'));
*/	}
};




/*
function changeImgWidth(instance) {
	var width = jQuery( '#widget-'+instance+'-width' ).val();
	var height = Math.round(width / imgRatio(instance));
	changeImgSize(instance,width,height);
}

function changeImgHeight(instance) {
	var height = jQuery( '#widget-'+instance+'-height' ).val();
	var width = Math.round(height * imgRatio(instance));
	changeImgSize(instance,width,height);
}

function imgRatio(instance) {
	var width_old = jQuery( '#display-widget-'+instance+'-image img').attr('width');
	var height_old = jQuery( '#display-widget-'+instance+'-image img').attr('height');
	var ratio =  width_old / height_old;
	return ratio;
}

function changeImgSize(instance,width,height) {
	if (isNaN(width) || width < 1) {
		jQuery( '#widget-'+instance+'-width' ).val('');
		width = 'none';
	} else {
		jQuery( '#widget-'+instance+'-width' ).val(width);
		width = width + 'px';
	}
	jQuery( '#display-widget-'+instance+'-image img' ).css({
		'width':width
	});

	if (isNaN(height) || height < 1) {
		jQuery( '#widget-'+instance+'-height' ).val('');
		height = 'none';
	} else {
		jQuery( '#widget-'+instance+'-height' ).val(height);
		height = height + 'px';
	}
	jQuery( '#display-widget-'+instance+'-image img' ).css({
		'height':height
	});
}

function changeImgAlign(instance) {
	var align = jQuery( '#widget-'+instance+'-align' ).val();
	jQuery( '#display-widget-'+instance+'-image img' ).attr(
		'class', (align == 'none' ? '' : 'align'+align)
	);
}
*/

WPImageWidget.returnedImages = {};


/**
 * Save the returned media when media is being sent to the "editor"
 */
WPImageWidget.returnMedia = function( html, id, alt, caption, title, align, url, size, widget_thumbnail ){
	WPImageWidget.returnedImages[ WPImageWidget.widgetInstanceId ] = WPImageWidget.returnedImages[ WPImageWidget.widgetInstanceId ] || {};
	WPImageWidget.returnedImages[ WPImageWidget.widgetInstanceId ][ WPImageWidget.widgetInstanceCurrentField ] = {
		html: html, 
		id: id, 
		alt: alt, 
		caption: caption, 
		title: title, 
		align: align, 
		url: url, 
		size: size,
		widget_thumbnail: widget_thumbnail
	};
}




})( jQuery, window, window.document );