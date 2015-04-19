<?php defined('APP_DIR') OR exit("No direct script access allowed\n");
/**
 * This is the configuration file.
 */

/**
 * Specify the name of your script file.
 * The class should folow CamelCase and start with uppercase.
 * The file name should also follow camelCaps and start with lowercase.
 * 
 * Example:
 * myScript.php
 * class MyScript extends Clip {}
 */
$config['default_script_name'] = 'script';


// When allowing multiple scripts the first parameter becomes the
// script name.
$config['multi_script'] = FALSE;
