jQuery(document).ready(function($) {
  var data = { 
    action: 'render_cloudswipe_cart_widget'
  };
  $.post(cs_widget.ajax_url, data, function(response) {
    $('#cs_cart_widget').html(response);
  });
});
