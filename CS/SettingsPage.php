<?php
class CS_SettingsPage {

  protected $_errors = array();
  protected $_warnings = array();
  protected $_success = array();

  public function __construct() {
    $this->clear_messages();
  }

  /**
   * Clear the error messages, warning messages, and success messages arrays
   */
  public function clear_messages() {
    $this->_errors = array();
    $this->_warnings = array();
    $this->_success = array();
  }

  public function render() {
    $templates = CS_PageSlurp::get_page_templates();

	  // Look for selected page template
	  $selected_template = CS_PageSlurp::get_selected_page_template();

	  $data = array(
	    'templates' => $templates,
	    'selected_page_template' => $selected_template,
	    'redirect_type' => get_site_option('cs_redirect_type'),
      'logging' => get_site_option('cs_logging')
	  );

    $view = CS_View::get(CS_PATH . 'views/admin_settings_page.phtml', $data);
    echo $view;
  }

  public function save_settings() {
    $settings = CS_Common::scrub('cs_settings', $_POST);
    if(is_array($settings)) {
      foreach($settings as $key => $value) {
        $value = trim($value);
        CS_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Saving CloudSwipe settings $key => $value");
        $old_value = get_site_option($key);
        if($value != $old_value) {
          if(!update_site_option($key, $value)) {
            $this->_errors[] = __('Failed to save: ' . $key);
          }
        }
      }
    }

    if(count($this->_errors)) {
      throw new CS_Exception('Failed to save CloudSwipe settings');
    }
  }

}
