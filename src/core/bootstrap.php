<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

define('CLIPHP_VERSION', '0.1-beta');

require_once(APP_DIR . '/core/includes/common.php');

// Prevent timeouts.
set_time_limit(0);

// Tell php to rise exceptions instead of errors.
set_error_handler('exception_error_handler');

// Load main classes.
$core_classes_path = APP_DIR . '/core/class/';

// Main class. All scripts inherit from this. 
require $core_classes_path . 'clip.class.php';
// ClipOption.
require $core_classes_path . 'clipOption.class.php';
// Argument parser.
require $core_classes_path . 'argumentParser.class.php';
// Loader.
require $core_classes_path . 'clipLoader.class.php';
// Base library class.
require $core_classes_path . 'clipLibrary.class.php';

// Load default configuration.
require APP_DIR . '/core/config/config.default.php';

// Try to load the user config file.
// If it doesn't exist pretend nothing happened.
try {
  include(APP_DIR . '/config.php');
}
catch (Exception $e) {
  // There's no user config file. Don't do anything.
}
// Set config as global.
$GLOBALS['config'] = $config;



// When testing the script should not be initialized.
// The script used for testing should not be in the same directory
// as normal scripts therefore each test should load, configure and
// initialize the script as needed for the test.
if (ENVIRONMENT != 'test') {
  // Script to use.
  $script_in_use = $config['default_script_name'];
  
  // Load the user script as defined in the config file.
  // The user has the option to use multiple script. In that case the first
  // argument is the script's name. If not present the default will be loaded.
  if ($config['multi_script'] && isset($_SERVER['argv'][1]) && preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_]+$/', $_SERVER['argv'][1])) {
    $input_script = $_SERVER['argv'][1];

    // Load script.
    if (load_script($input_script)) {
      $script_in_use = $input_script;
      // Remove selected script so it's not flagged as orphan argument.
      unset($_SERVER['argv'][1]);
    }
    else {
      pl("ERROR. Unable to find script: $input_script");
      pl("Loading default script: {$config['default_script_name']}");
      load_default_script();
    }

  }
  else {
    load_default_script();
  }

  // Create script handler.
  $script_in_use = ucfirst($script_in_use);
  $script = $script_in_use::getInstance();
  
  try {
    // Initialize script.
    $script->init();
  }
  // Only catch ClipUseException.
  catch(ClipUseException $e) {
    pl($e->getMessage());
  }
}