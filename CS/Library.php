<?php
class CS_Library {

  protected $_protocol;
  protected $_app_domain;
  protected $_api;
  protected $_secure;

  public function __construct() {
    $this->_protocol = 'https://';
    $this->_app_domain = 'cloudswipe.com';
    $this->_api = $this->_protocol . 'api.' . $this->_app_domain . '/1/';
    $this->_secure = $this->_protocol . 'secure.' . $this->_app_domain . '/';
  }

  /**
   * Return an array of arrays of product data
   *
   * @return array
   */
  public function get_products() {
    $url = $this->_api . 'products';
    $headers = array('Accept' => 'application/json');
    $response = wp_remote_get($url, $this->_basic_auth_header($headers));

    if(!$this->_response_ok($response)) {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CS_Library::get_products failed: $url :: " . print_r($response, true));
      throw new CS_Exception_API("Failed to retrieve products from CloudSwipe");
    }

    $product_data = json_decode($response['body'], true);
    return $product_data;
  }

  /**
   * Return the custom subdomain for the account of false if no subdomain is set
   * 
   * @return mixed String or False
   */
  public function get_subdomain() {
    $subdomain = false;

    $url = $this->_api . 'subdomain';
    $headers = array('Accept' => 'text/html');
    $response = wp_remote_get($url, $this->_basic_auth_header($headers));

    if($this->_response_ok($response)) {
      $subdomain = $response['body'];
    }

    return $subdomain;
  }

  /**
   * Returns the HTML markup for the add to cart form for the given product id
   *
   *  GET: /products/<id>/add_to_cart_form
   */
  public function get_order_form($product_id, $redirect_url, $display_quantity=null, $display_price=null, $display_mode=null) {

    // Prepare the query string
    $params = array(
      'redirect_url' => urlencode($redirect_url)
    );

    if(isset($display_mode)) {
      $params[] = 'display=' . $display_mode;
    }

    if(isset($display_quantity)) {
      $params[] = 'quantity=' . $display_quantity;
    }

    if(isset($display_price)) {
      $params[] = 'price=' . $display_price;
    }

    $query_string = '?' . implode('&', $params);

    // Prepare the url
    $headers = array('Accept' => 'text/html');
    $public_key = get_site_option('cs_public_key');
    $url = $this->_secure . 'stores/' . $public_key . '/products/' . $product_id . '/forms/add_to_cart' . $query_string;
    $response = wp_remote_get($url, $this->_basic_auth_header($headers));

    if(is_wp_error($response)) {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CS_Library::get_add_to_cart_form had an error: " . print_r($response, true));
      throw new CS_Exception_API("Failed to retrieve product add to cart form from CloudSwipe");
    }
    elseif($response['response']['code'] != 200) {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CS_Library::get_add_to_cart_form invalid response code: " . print_r($response, true));
      throw new CS_Exception_API("Failed to retrieve product add to cart form from CloudSwipe :: Response code error :: " . $response['response']['code']);
    }

    $form_html = $response['body'];
    return $form_html;
  }

  /**
   * Create a cart on CloudSwipe and return the cart_key
   *
   * @return string
   */
  public function create_cart($slurp_url="") {
    $url = $this->_api . 'carts';

    // Build the headers to create the cart
    $headers = array('Accept' => 'application/json');
    $args = $this->_basic_auth_header($headers);

    $data = array('ip_address' => $_SERVER['REMOTE_ADDR']);
    $data = json_encode($data);
    $args['body'] = $data;

    // Post to create cart
    CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Create cart via library call to cloudswipe: $url " . print_r($args, true));
    $response = wp_remote_post($url, $args);

    if(!$this->_response_created($response)) {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Failed to create new cart in CloudSwipe: $url :: " . print_r($response, true));
      throw new CS_Exception_API("Failed to create new cart in CloudSwipe");
    }

    $cart_data = json_decode($response['body']);
    return $cart_data->key;
  }

  /**
   * Returns summary information for the given shopping cart
   *
   * subtotal: the sum of the totals of all the items (not including shipping, taxes, or discounts)
   * item_count: the number of items in the cart
   *
   * @return stdClass object
     */
  public function cart_summary($cart_key) {
    $headers = array('Accept' => 'application/json');
    $url = $this->_api . "carts/$cart_key/summary";
    $response = wp_remote_get($url, $this->_basic_auth_header($headers));
    if(!$this->_response_ok($response)) {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Cart summary response from library: $url :: " . print_r($response, true));
      throw new CS_Exception_API("Unable to retrieve cart summary information for cart id: $cart_key");
    }
    $summary = json_decode($response['body']);
    return $summary;
  }

  /**
   * Return the URL to the view cart page on the cloud
   *
   * @return string
   */
  public function view_cart_url($public_key, $cart_key) {
    $subdomain = $this->get_subdomain();
    $url = $this->_secure . "stores/$public_key/carts/$cart_key";
    if($subdomain) {
      $url = $this->_protocol . $subdomain . '.' . $this->_app_domain . '/carts/' . $cart_key;
    }
    return $url;
  }

  /**
   * Return the URL to the checkout page on the cloud
   *
   * @return string
   */
  public function checkout_url($public_key, $cart_key) {
    $subdomain = $this->get_subdomain();
    $url = $this->_secure . "stores/$public_key/checkout/$cart_key";
    if($subdomain) {
      $url = $this->_protocol . $subdomain . '.' . $this->_app_domain . '/checkout/' . $cart_key;
    }
    return $url;
  }

  public function add_to_cart($public_key, $cart_key, $post_data) {
    $url = $this->_secure . "stores/$public_key/carts/$cart_key/items";
    $headers = $this->_basic_auth_header();
    $headers = array(
      'sslverify' => false,
      'method' => 'POST',
      'body' => $post_data,
      'headers' => $headers['headers']
    );
    $response = wp_remote_post($url, $headers);
    return $response;
  }

  public function get_receipt_content($secret_key, $order_number) {
    $url = $this->_secure . "stores/$secret_key/receipt/$order_number";
    $response = wp_remote_get($url, array('sslverify' => false));
    CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Receipt content response from:\n$url\n\n" . print_r($response, true));
    if($response['response']['code'] == '200') {
      return $response['body'];
    }
    else {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to locate a receipt with the order number: $order_number");
      throw new CS_Exception_Store_ReceiptNotFound('Unable to locate a receipt with the given order number.');
    }
  }

  /* ==========================================================================
   * Protected functions
   * ==========================================================================
   */

  protected function _basic_auth_header($extra_headers=array()) {
    $username = get_site_option('cs_secret_key');
    $password = ''; // not in use
    $headers = array(
      'sslverify' => false,
      'headers' => array(
        'Authorization' => 'Basic ' . base64_encode($username . ':' . $password)
      )
    );

    if(is_array($extra_headers)) {
      foreach($extra_headers as $key => $value) {
        $headers['headers'][$key] = $value;
      }
    }

    //CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Built headers :: " . print_r($headers, true));
    return $headers;
  }

  protected function _response_ok($response) {
    $ok = true;
    if(is_wp_error($response) || $response['response']['code'] != 200) {
      $ok = false;
    }
    return $ok;
  }

  protected function _response_created($response) {
    $ok = true;
    if(is_wp_error($response) || $response['response']['code'] != 201) {
      $ok = false;
    }
    return $ok;
  }

}

