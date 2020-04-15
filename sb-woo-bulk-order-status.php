<?php

  /*
   * Plugin Name: Silverback Woo Multi Order Status
   * Plugin URI: https://silverbackdev.co.za
   * Description: Queries and returns bulk order statuses using supplied comma separated order numbers.
   * Author: Werner C. Bessinger
   * Version: 1.0.0
   * Author URI: https://silverbackdev.co.za
   */
       
  /* PREVENT DIRECT ACCESS */
  if (!defined('ABSPATH')):
      exit;
  endif;
      
  // define plugin path constant
  define('SMOS_PATH', plugin_dir_path(__FILE__));
  define('SMOS_URL', plugin_dir_url(__FILE__));
  
  require SMOS_PATH.'classes/SMOS_Order_List.php';