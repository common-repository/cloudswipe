<?php

class CS_ProductMetaBox {
  
  public static function add() {
    
  	// Metabox location settings
  	$post_types = apply_filters('cs_product_meta_box_post_types', array('post') ); // only show on product custom post type
  	$context = apply_filters('cs_product_meta_box_context', 'side');
  	$priority = apply_filters('cs_product_meta_box_priority', 'high');

    foreach($post_types as $post_type) {
      add_meta_box(  
        'cs_product_meta_box',                 // id  
        'CloudSwipe Products',                 // title  
        array('CS_ProductMetaBox', 'draw'),    // callback  
        $post_type,
        $context,
        $priority
      );
    }
  }
  
  public static function draw($post) {
    $cs = new CS_Library();
    
    try {
      $product_data = $cs->get_products();
    }
    catch(CS_Exception_API $e) {
      $product_data = CS_Common::unavailable_product_data();
    }
    
    $products = array();
    foreach($product_data as $p) {
      $product = new CS_Product();
      $product->id = $p['id'];
      $product->name = $p['name'];
      $products[] = $product;
    }
    
    $view = CS_PATH . 'views/product_meta_box.phtml';
    $cs_product_id = get_post_meta($post->ID, 'cs_product_id', true);
    $data = array('post_id' => $post->ID, 'cs_product_id' => $cs_product_id, 'products' => $products);
    echo CS_View::get($view, $data);
  }
  
  public function save($post_id) {
    // Do not save during autosaves
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not saving because I think it is doing an autosave");
      return;
    }
        
    // Only save when the post type is cloudswipe_product
    if(isset($_POST['post_type'])) {
      if('cloudswipe_product' == $_POST['post_type']) {
        if(!current_user_can('edit_page', $post_id)) {
          CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] The current user may not perform this action");
          return;
        }
      }
      else {
        CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not saving because not a cloudswipe custom post type: " . $_POST['post_type']);
      }
      // Do not save unless nonce can be verified
      if(!isset($_POST['cs_nonce']) || !wp_verify_nonce($_POST['cs_nonce'], 'cs_save_product_id')) {
        CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not saving cloudswipe product id due to nonce failure");
        return;
      }
      
      // Everything looks good, so update the post meta
      $meta_key = 'cs_product_id';
      $meta_value = CS_Common::scrub('cs_product_id', $_POST);
      update_post_meta($post_id, $meta_key, $meta_value);
    }
    
  }
  
}
