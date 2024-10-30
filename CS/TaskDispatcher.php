<?php

class CS_TaskDispatcher {

  /**
   * Keys: Query strting or hidden form field C66_task values
   * Values: Function names to handle the specified task
   */
  private static $_tasks = array(
    'admin_save_settings' => 'admin_save_settings',
    'add_to_cart' => 'add_to_cart'
  );

  /**
   * Make sure the task is a valid task name then call it
   */
  public static function dispatch() {
    $ajax_call = false;
    $url = $_SERVER['REQUEST_URI'];
    if(strpos($url, 'admin-ajax.php') > 0) {
      $ajax_call = true;
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Doing AJAX :: Not dispatching any tasks");
    }
    else {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not doing AJAX :: Preparing to process task from $url");
    }

    if(!$ajax_call && isset($_REQUEST['cs_task'])) {
      $task = $_REQUEST['cs_task'];
      if(in_array($task, array_keys(self::$_tasks))) {
        $dispatch = self::$_tasks[$task];
        CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Dispatching task: $task :: $dispatch");
        self::$dispatch();
      }
    }
  }

  public static function admin_save_settings() {
    $settings_page = new CS_SettingsPage();
    $settings_page->save_settings();
  }

  public static function add_to_cart() {
    $post_data = false;

    CS_Cart::get_cart_key(); // Create cart if one does not already exist.
    $redirect_url = CS_Cart::get_redirect_url();
    CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Default redirect url is now set to: $redirect_url");

    // Run hook before the product is added to the cart
    do_action('cs_before_add_to_cart');

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
      $post_data = $_POST;
    }
    else {
      if(isset($_GET['sku'])) {
        $product_id = $_GET['sku'];
        $quantity = 1;
        if(isset($_GET['quantity'])) {
          $quantity = (int)$_GET['quantity'];
        }

        if(isset($_GET['redirect'])) {
          CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Redirect is set in $_GET: " . $_GET['redirect']);
          $redirect = strtolower($_GET['redirect']);
          if($redirect == 'checkout') {
            $redirect_url = CS_Cart::checkout_url();
            CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Set redirect url to the checkout url: $redirect_url");
          }
          elseif($redirect == 'cart') {
            $redirect_url = CS_Cart::view_cart_url();
            CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Set redirect url to the view cart url: $redirect_url");
          }
          else {
            $redirect_url = $redirect;
            CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Set redirect url to a custom url: $redirect_url");
          }
        }
        $post_data = array(
          'product_id' => $product_id,
          'quantity' => $quantity
        );
      }      
    }

    CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Calling add to cart with this data: " . print_r($post_data, true));
    $response = CS_Cart::add_to_cart($post_data);
    CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Add to cart response: " . print_r($response, true));
    $response_code = $response['response']['code'];
    if($response_code == '201') {
      do_action('cs_after_add_to_cart');
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] After adding to cart - about to redirect: $redirect_url");
      wp_redirect($redirect_url);
      die();
    }
    else {
      $sku = $_POST['sku'];
      CS_FlashData::set($sku, $response['body']);
    }
  }

}
