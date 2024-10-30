<?php

class CS_ShortcodeManager {

  public function add_media_button($context) {
    $button = '<img src="/TODO/add/image/src" alt="' . __("Add CloudSwipe Product", 'cloudswipe') . '" />';
    $button = 'CS';
    $button ='<a href="#TB_inline?width=480&height=600&inlineId=cs_editor_pop_up" class="thickbox" id="cs_product_shortcodes" title="' . __("Add CloudSwipe Product", 'cloudswipe') . '">'.$button.'</a>';
    return $context . $button;
  }

  public static function add_media_button_popup() {
    $product_data = array();

    try {
      $cloudswipe = new CS_Library();
      $product_data = $cloudswipe->get_products();
    }
    catch(CS_Exception_API $e) {
      $product_data = CS_Common::unavailable_product_data();
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to retreive products for media button pop up: " . $e->get_message());
    }

    $data = array('product_data' => $product_data);
    $view = CS_View::get(CS_PATH . 'views/editor_pop_up.phtml', $data);
    echo $view;
  }

  public static function product($args, $content) {
    $form = '';

    if($error_message = CS_FlashData::get('api_error')) {
      $form .= "<p class=\"cs_error\">$error_message</p>";
    }

    $product_id = isset($args['id']) ? $args['id'] : false;
    $product_sku = isset($args['sku']) ? $args['sku'] : false;
    $display_quantity = isset($args['quantity']) ? $args['quantity'] : 'true';
    $display_price = isset($args['price']) ? $args['price'] : 'true';
    $display_mode = isset($args['display']) ? $args['display'] : null;

    if($form_with_errors = CS_FlashData::get($product_sku)) {
      $form .= $form_with_errors;
    }
    else {
      $product = new CS_Product();
      if($product_sku) {
        $product->sku = $product_sku;
      }
      elseif($product_id) {
        $product->id = $product_id;
      }
      else {
        throw new CS_Exception_Product('Unable to add product to cart without know the product sku or id');
      }

      try {
        $form .= $product->get_order_form($display_quantity, $display_price, $display_mode);
      }
      catch(CS_Exception_Product $e) {
        $form = "Product order form unavailable";
      }
    }

    return $form;
  }

  public static function product_link($args, $content) {
    $sku = isset($args['sku']) ? $args['sku'] : false;
    if($sku) {
      $quantity = isset($args['quantity']) ? (int)$args['quantity'] : 1;
      $query_string = array(
        'cs_task=add_to_cart',
        'sku=' . $args['sku'],
        'quantity=' . $quantity
      );
      if(isset($args['redirect'])) {
        $query_string['redirect'] = 'redirect=' . urlencode($args['redirect']);
      }
      $query_string = implode('&', $query_string);
      $link = get_site_url() . '?' . $query_string;
    }
    $link = "<a href='$link'>$content</a>";
    return $link;
  }

}
