<?php

class CS_Cart {

  protected static $_expire_days = 30;
  protected static $_cart_key = null;
  protected static $_cart_summary = null;

  protected static function _set_cookie($name, $value) {
    $cookie_name = $name;
    $expire = time()+60*60*24*self::$_expire_days;
    $https = false;
    $http_only = true;
    setcookie($cookie_name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $https, $http_only);
    if(COOKIEPATH != SITECOOKIEPATH) {
      setcookie($cookie_name, $value, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $https, $http_only);
    }
  }

  protected function match_page_request($slug) {
		global $wp;
		global $wp_query;

    $match = false;
    if(strtolower($wp->request) == strtolower($slug) ||
      (isset($wp->query_vars['page_id']) && $wp->query_vars['page_id'] == $slug)
    ) { $match = true; }
    return $match;
  }

  protected function load_summary() {
    if(!isset(self::$_cart_summary)) {
      self::$_cart_summary = self::get_summary();
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Cart summary: " . print_r(self::$_cart_summary, true));
    }
    return self::$_cart_summary;
  }

  /**
   * Drop the cs_cart_key cookie
   */
  public static function drop_cart() {
    self::_set_cookie('cs_cart_key', '');
    unset($_COOKIE['cs_cart_key']);
  }

  public static function enqueue_jquery() {
    wp_enqueue_script('jquery');
  }

  public static function enqueue_ajax_add_to_cart() {
    wp_enqueue_script('cs_add_to_cart', CS_URL . 'resources/js/add_to_cart.js');
    $ajax_url = admin_url('admin-ajax.php');
    wp_localize_script('cs_add_to_cart', 'cs_cart', array('ajax_url' => $ajax_url));
  }

  public static function enqueue_cloudswipe_styles() {
    wp_enqueue_style('cloudswipe-wp', CS_URL . 'resources/css/cloudswipe-wp.css');
  }

  /**
   * Return the cart id from self, cookie, or create a new one
   *
   * If force is false, a new cart will not be created and false will be returned
   * if a cart_key is not in the cookie
   *
   * @return mixed string or false
   */
  public static function get_cart_key($create_if_empty=true) {
    $cart_key = false;

    if(isset(self::$_cart_key)) {
      $cart_key = self::$_cart_key;
    }
    elseif(isset($_COOKIE['cs_cart_key'])) {
      $cart_key = $_COOKIE['cs_cart_key'];
    }

    if($cart_key == false && $create_if_empty !== false) {
      $cart_key = self::create_cart();
    }

    return $cart_key;
  }

  /**
   * Return stdClass summary of cart state
   *
   * If the cart is empty, the subtotal and item count will both be null.
   *
   * $summary->subtotal = '$125.00';
   * $summary->item_count = 3;
   *
   * @return stdClass
   */
  public static function get_summary() {
    $summary = new stdClass();
    $summary->subtotal = null;
    $summary->item_count = null;
    $summary->api_ok = true;
    if($cart_key = self::get_cart_key(false)) {
      $lib = new CS_Library();
      try {
        $summary = $lib->cart_summary($cart_key);
        $summary->api_ok = true;
      }
      catch(CS_Exception_API $e) {
        $summary->api_ok = false;
        CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to retrieve cart from CloudSwipe due to API failure.");
      }
    }
    return $summary;
  }

  public static function create_cart() {
    $cart_key = false;
    $lib = new CS_Library();
    try {
      $slurp_url = self::get_page_slurp_url();
      $cart_key = $lib->create_cart($slurp_url);
      self::_set_cookie('cs_cart_key', $cart_key);
      self::$_cart_key = $cart_key;
    }
    catch(CS_Exception_API $e) {
      CS_FlashData::set('api_error', 'Unable to add item to cart');
    }
    CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Creating cart key and setting cookie: $cart_key");
    return $cart_key;
  }

  public static function view_cart_url($force_create_cart=false) {
    $url = false;
    $cart_key = self::get_cart_key($force_create_cart); // Do not create a cart if the id is not available in the cookie or it is forced
    if($cart_key) {
      $public_key = get_site_option('cs_public_key');
      $lib = new CS_Library();
      $url = $lib->view_cart_url($public_key, $cart_key);
    }
    return $url;
  }

  public static function checkout_url($force_create_cart=false) {
    $url = false;
    $cart_key = self::get_cart_key($force_create_cart); // Do not create a cart if the id is not available in the cookie or it is forced
    if($cart_key) {
      $public_key = get_site_option('cs_public_key');
      $lib = new CS_Library();
      $url = $lib->checkout_url($public_key, $cart_key);
    }
    return $url;
  }

  public static function get_redirect_url() {
    $redirect_type = get_site_option('cs_redirect_type');
    if($redirect_type == 'view_cart') {
      $url = self::view_cart_url();

    }
    elseif($redirect_type == 'checkout') {
      $url = self::checkout_url();
    }
    else {
      // Stay on same page
      $url = $_SERVER['REQUEST_URI'];
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Using the request uri as the redirect url: $url");
    }
    // CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] According to the CloudSwipe settings, the redirect url is: $url :: redirect type: $redirect_type");
    return $url;
  }

  public static function get_page_slurp_url() {
    $url = get_site_url() . '?page_id=page-slurp-template';
    return $url;
  }

  public static function add_to_cart($post_data) {
    $public_key = get_site_option('cs_public_key');
    if(strlen($public_key) < 5) {
      throw new CS_Exception_API_InvalidPublicKey('Invalid public key');
    }

    $cart_key = self::get_cart_key();
    $lib = new CS_Library();
    $response = $lib->add_to_cart($public_key, $cart_key, $post_data);
    return $response;
  }

  public static function ajax_add_to_cart() {
    $response = self::add_to_cart($_POST);
    CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Ajax add to cart response: " . print_r($response, true));
    $response_code = $response['response']['code'];
    if($response_code == '500') {
      header('HTTP/1.1 500: SERVER ERROR', true, 500);
    }
    elseif($response_code != '201') {
      header('HTTP/1.1 422: UNPROCESSABLE ENTITY', true, 422);
      echo $response['body'];
    }
    else {
      $product_info = json_decode($response['body'], true);
      $product_name = $product_info['product_name'];
      $message = $product_name . ' added to cart';
      $view_cart = '<a href="' . self::view_cart_url() . '" class="btn btn-small pull-right ajax_view_cart_button">View Cart <i class="icon-arrow-right" /></a>';
      echo $message . $view_cart;
      do_action('cs_after_ajax_add_to_cart');
    }
    die();
  }

  public static function redirect_cart_links() {
    if(self::match_page_request('view_cart')) {
      $link = self::view_cart_url(true);
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Redirecting to $link");
      wp_redirect($link);
      exit();
    }
    elseif(self::match_page_request('checkout')) {
      $link = self::checkout_url(true);
      wp_redirect($link);
      exit();
    }
  }

  public static function item_count() {
    self::load_summary();
    return self::$_cart_summary->item_count > 0 ? self::$_cart_summary->item_count : 0;
  }

  public static function subtotal() {
    self::load_summary();
    return self::$_cart_summary->subtotal;
  }
}
