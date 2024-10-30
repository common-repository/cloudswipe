<?php
/*
Plugin Name: CloudSwipe
Plugin URI: http://cloudswipe.com
Description: Securely Hosted Ecommerce For WordPress
Version: 1.1.1
Author: Reality66
Author URI: http://www.reality66.com

-------------------------------------------------------------------------
CloudSwipe Ecommerce Toolkit
Copyright 2012  Reality66

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if(!defined('CS_PATH')) {
  $plugin_file = __FILE__;
  if(isset($plugin)) { $plugin_file = $plugin; }
  elseif (isset($mu_plugin)) { $plugin_file = $mu_plugin; }
  elseif (isset($network_plugin)) { $plugin_file = $network_plugin; }

  define('CS_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($plugin_file)) . '/');
  define('CS_URL',  WP_PLUGIN_URL . '/' . basename(dirname($plugin_file)) . '/');
}

if(!class_exists('CS_Loader')) {
  require 'autoloader.php';

  define('CS_VERSION_NUMBER', '1.1.1');

  // IS_ADMIN is true when the dashboard or the administration panels are displayed
  if(!defined('IS_ADMIN')) {
    define("IS_ADMIN",  is_admin());
  }

  if(!defined("CS_CURRENT_PAGE")) {
    define("CS_CURRENT_PAGE", basename($_SERVER['PHP_SELF']));
  }

  $cloudswipe = new CS_CloudSwipe();
}
