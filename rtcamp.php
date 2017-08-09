<?php

/*
Plugin Name: rtCamp Contributors Plugin
Plugin URI:  https://www.techrrival.com
Description: Contributors Plugin For rtCamp
Version:     1.0
Author:      Mehul Boricha
Author URI:  https://www.mehulboricha.com
License:     GPLv2
*/

//Prevent Direct Access to File (Only WordPress Can Access)
if (!defined('ABSPATH')) {
	die('No script kiddies please!');
}

//Include Admin Panel File
require_once (plugin_dir_path(__FILE__) . 'admin/admin.php');

//Include Frontend View File
require_once (plugin_dir_path(__FILE__) . 'view/view.php');