<?php

class CS_PageSlurp {

	public $page_slug = 'page-slurp-template';
	public $page_title = '{{cloudswipe_title}}';

	public static function check() {
	  $slurp = new CS_PageSlurp();
	}

	public function __construct() {
    // Drop the cart key cookie if the receipt page is requested
    if(isset($_REQUEST['cs_page_name']) && $_REQUEST['cs_page_name'] == 'receipt') {
      CS_Cart::drop_cart();
    }

		add_filter('the_posts',array($this,'detect_post'));
	}

	public function create_post() {
    if(isset($_REQUEST['cs_page_title'])) {
      $this->page_title = CS_Common::scrub('cs_page_title', $_REQUEST);
    }

    $posts = get_posts(array('numberposts' => 1, 'post_type'=>'page'));
    $post_id = (count($posts) && isset($posts[0]))? $posts[0]->ID : 1;

		$post = new stdClass;
		$post->post_author = 1;
		$post->post_name = $this->page_slug;
		$post->guid = get_bloginfo('wpurl') . '/' . $this->page_slug;
		$post->post_title = $this->page_title;
		$post->post_content = $this->get_content();
		$post->ID = $post_id;
		$post->post_status = 'static';
		$post->comment_status = 'closed';
		$post->ping_status = 'closed';
		$post->comment_count = 0;
		$post->post_date = current_time('mysql');
		$post->post_date_gmt = current_time('mysql', 1);
		$post->post_type = 'page-slurp';
		$post->post_parent = false;

		return $post;
	}

	public function set_page_template() {
	  $template = $this->get_selected_page_template();
    if(file_exists(TEMPLATEPATH . '/' . $template)) {
      load_template(TEMPLATEPATH . '/' . $template);
      die();
    }
	}

	public function detect_post($posts){
		global $wp;
		global $wp_query;
		/**
		 * Check if the requested page matches our target
		 */
		if (strtolower($wp->request) == strtolower($this->page_slug) || (isset($wp->query_vars['page_id']) && $wp->query_vars['page_id'] == $this->page_slug)) {
		  add_action('template_redirect', array($this, 'set_page_template'));

			$posts=NULL;
			$posts[]=$this->create_post();
			$wp_query->is_page = true;
			$wp_query->is_singular = true;
			$wp_query->is_home = false;
			$wp_query->is_archive = false;
			$wp_query->is_category = false;
			$wp_query->is_date = false;
			unset($wp_query->query["error"]);
			$wp_query->query_vars["error"]="";
			$wp_query->is_404=false;
      $wp_query->comments = array();
      $wp_query->comments_by_type = array();
      $wp_query->comment_count = '0';
      $wp_query->queried_object->comment_count = '0';
      $wp_query->queried_object->comment_status = 'closed';
		}
    else {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to detect a page slurp for unknown page_id.");
    }

	  return $posts;
	}

	/**
   * Return the page template selected for use with cloudswipe.
   *
   * If the database setting does not exist or the specified file is
   * not found, return 'page.php' if that exists.
   *
   * @return string
   */
  public static function get_selected_page_template() {
    $selected_template = get_site_option('cs_selected_page_template');
    CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Selected template from database settings: $selected_template");

    if(!locate_template(array($selected_template))) {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Could not find the selected templated in the current theme: $selected_template");
      $selected_template = '';
    }

    if(empty($selected_template)) {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] The selected template is empty - maybe it's not set or maybe a new theme is selected and the saved template is unavailable in the new theme");
      $selected_template = self::look_for_full_width_page_template();
    }

    return $selected_template;
  }

  public function get_page_templates() {
    $templates = array();
    if(function_exists('wp_get_theme')) {
      $theme = wp_get_theme();
      $templates = $theme->get_page_templates();
    }
    else {
      require_once(ABSPATH . 'wp-admin/includes/admin.php');
      if(function_exists('get_page_templates')) {
        $theme_templates = array_flip(get_page_templates());
        $templates = array_merge($templates, $theme_templates);
      }
      else {
        CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Returning default page template because get_page_templates() is not defined");
      }
    }

    $templates = array_merge(array('page.php' => 'Default Page Template'), $templates);
    return $templates;
  }

  public function look_for_full_width_page_template() {
	  $template = false;
	  $hints = array('full', 'no sidebar', 'one');

    $templates = self::get_page_templates();
	  foreach($templates as $file => $name) {
	    foreach($hints as $hint) {
	      if(stristr($file, $hint) !== false || stristr($name, $hint) !== false) {
	        if(stristr($file, 'blog') === false && stristr($name, 'blog') === false) {
	          $template = $file;
    	      break;
	        }
  	    }
	    }
	    if($template) {
	      break;
	    }
	  }

	  if(!$template) {
	    $template = 'page.php';
	  }

	  CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Using template file: " . $template);
	  return $template;
	}

  public function get_content() {
    // The keys are possible values for cs_page_name
    $content_generators = array(
      'receipt' => 'build_receipt_page'
    );

    $content = '{{cloudswipe_content}}';

    if(isset($_REQUEST['cs_page_name'])) {
      CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to get page slurp content for requested page name: " . $_REQUEST['cs_page_name']);
      $page_name = $_REQUEST['cs_page_name'];
      if(in_array($page_name, array_keys($content_generators))) {
        $function = $content_generators[$page_name];
        $content = $this->$function();
      }
    }

    return $content;
  }

  public function build_receipt_page() {
    $order_id = '';
    if(isset($_REQUEST['cs_order_id'])) {
      $order_id = $_REQUEST['cs_order_id'];
      try {
        $lib = new CS_Library();
        $secret_key = get_site_option('cs_secret_key');
        $content = $lib->get_receipt_content($secret_key, $order_id);
      }
      catch(CS_Exception_Store_ReceiptNotFound $e) {
        $content = '<p>Unable to find receipt for the given order number.</p>';
      }
    }
    else {
      $content = '<p>Unable to find receipt because the order number was not provided.</p>';
    }

    return $content;
  }

  public function debug() {
  }
}
