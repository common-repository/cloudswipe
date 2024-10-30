<?php
class CS_CloudSwipe {

  public function __construct() {
    IS_ADMIN ? $this->init_admin() : $this->init_public();

    // Handle tasks passed via query strings and post backs
    add_action('init', array('CS_TaskDispatcher', 'dispatch'));

    // Register sidebar widgets
    add_action('widgets_init', create_function('', 'return register_widget("CS_CartWidget");'));

    // Enqueue jQuery
    add_action('wp_enqueue_scripts', array('CS_Cart', 'enqueue_jquery'));

    // Add actions for ajax add to cart
    if(get_site_option('cs_redirect_type') == 'stay_ajax') {
      add_action('wp_enqueue_scripts', array('CS_Cart', 'enqueue_ajax_add_to_cart'));
      add_action('wp_ajax_cs_ajax_add_to_cart', array('CS_Cart', 'ajax_add_to_cart'));
      add_action('wp_ajax_nopriv_cs_ajax_add_to_cart', array('CS_Cart', 'ajax_add_to_cart'));
    }
  }

  public function init_public() {
    // Check for page slurp
    add_action('init', array('CS_PageSlurp', 'check'));
    add_action('template_redirect', array('CS_Cart', 'redirect_cart_links'));
    // add_action('template_redirect', array('CS_PageSlurp', 'debug'));
    add_shortcode('cs_product', array('CS_ShortcodeManager', 'product'));
    add_shortcode('cs_product_link', array('CS_ShortcodeManager', 'product_link'));

    // Enqueue cloudswipe styles
    add_action('wp_enqueue_scripts', array('CS_Cart', 'enqueue_cloudswipe_styles'));
  }

  public function init_admin() {
    add_action('add_meta_boxes', array('CS_ProductMetaBox', 'add'));
    add_action('save_post', array('CS_ProductMetaBox', 'save'));
    add_action('admin_menu', array($this, 'attach_settings_page'));
    add_action('admin_notices', array($this, 'show_cloudswipe_account_notice'));

    // Add media button for cloudswipe shortcodes
    if(in_array(CS_CURRENT_PAGE, array('post.php', 'page.php', 'page-new.php', 'post-new.php'))) {
      add_action('media_buttons_context', array('CS_ShortcodeManager', 'add_media_button'));
      add_action('admin_footer',  array('CS_ShortcodeManager', 'add_media_button_popup'));
    }

    add_filter('plugin_action_links', array($this, 'add_settings_link'), 10, 2);
  }

  public function attach_settings_page() {
    $settings_page = new CS_SettingsPage();
    add_submenu_page('options-general.php',
      __('CloudSwipe', 'cloudswipe'),
      __('CloudSwipe', 'cloudswipe'),
      'administrator',
      'cloudswipe_admin',
      array($settings_page, 'render')
    );
  }

  public function show_cloudswipe_account_notice() {
    if(!(get_site_option('cs_public_key') && get_site_option('cs_secret_key'))) {
      echo '<div class="updated"><p>Please <a href="http://cloudswipe.com/pricing" target="_blank">create a CloudSwipe account</a> then enter your <a href="admin.php?page=cloudswipe_admin">CloudSwipe keys</a>.</p></div>';
    }
  }

  public function add_settings_link($links, $file) {
    $pattern = DIRECTORY_SEPARATOR . 'cloudswipe.php';
    if(strpos($file, $pattern) > 0) {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] YES! Adding the link");
      $settings_link = '<a href="admin.php?page=cloudswipe_admin">' . __('Settings', 'cloudswipe') . '</a>';
      array_unshift($links, $settings_link);
    }
    return $links;
  }

}
