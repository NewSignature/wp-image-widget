
jQuery(function(){


/**
 * This opens the current image that is selected. 
 * 
 */
var id = location.search;
var re = /^(.*&)?imageId=(\d+)/;
var m = re.exec(id);

if( m ){
  var imgId = m[2];
  
  setTimeout( function(){
    jQuery( '#media-item-'+imgId ).find( 'a.describe-toggle-on' ).click();
  }, 10 );
}



});