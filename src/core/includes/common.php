<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

/**
 * Exception handler function.
 */
function exception_error_handler($errno, $errstr, $errfile, $errline) {
  throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}

/**
 * Loads the default script as specified in the config file.
 */
function load_default_script() {
  global $config;
  if (!load_script($config['default_script_name'])) {
    print("Unable to load main script file. Expected file: {$config['default_script_name']}.php\n");
    exit ;
  }
}

/**
 * Loads a script with a given name.
 * 
 * @param $name
 *   Script to load.
 */
function load_script($name) {
  $search_paths = array();
  // During development include the examples folder in the search.
  if (ENVIRONMENT == 'development') {
    $search_paths[] = APP_DIR . "/examples/{$name}.php";
    $search_paths[] = APP_DIR . "/examples/{$name}/{$name}.php";
  }

  // User script in the root.
  $search_paths[] = APP_DIR . "/scripts/{$name}.php";
  // User script inside folder.
  $search_paths[] = APP_DIR . "/scripts/{$name}/{$name}.php";

  foreach ($search_paths as $path) {
    if (file_exists($path)) {
      require $path;
      return TRUE;
    }
  }

  return FALSE;
}

/**
 * Returns the context in which the script should run.
 * Alias of Clip::getInstance()
 *
 * @return Clip Object
 */
function &getContext() {
  return Clip::getInstance();
}

/**
 * Putting it simply, marks a function as a configuration function,
 * meaning that can only be called in the configure() function.
 *
 * Throws an exception when the function is called after the script was initialized.
 *
 * @throws Exception
 */
function markAsConfigurationFunction() {
  if (getContext()->isInitialized()) {
    throw new ClipException("The script was already configured. This command is not allowed outside configure().");
  }
}

/**
 * Print text.
 *
 * @param String $txt
 */
function p($txt) {
  print $txt;
}

/**
 * Print text in a line.
 *
 * @param String $txt
 */
function pl($txt = "") {
  p($txt . "\n");
}

/**
 * Determines if a command exists on the current environment.
 *
 * @param string $command
 *   The command to check.
 * @return bool True
 *   If the command has been found ; otherwise, FALSE.
 */
function command_exists($command) {
  $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';

  $process = proc_open("$whereIsCommand $command", array(
    0 => array("pipe", "r"), //STDIN
    1 => array("pipe", "w"), //STDOUT
    2 => array("pipe", "w"), //STDERR
  ), $pipes);
  
  if ($process !== FALSE) {
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    return $stdout != '';
  }

  return FALSE;
}

/**
 * Finishes the script execution.
 * When in test environment, throws exception.
 * Any other case, kills the script.
 * 
 * @param String $msg
 *   Message to use for the exception.
 *   Only used in test environment. Default to Script finished execution.
 * 
 * @throws ClipException
 *   When in test environment.
 */
function finish($msg = 'Script finished execution.') {
  if (ENVIRONMENT == 'test') {
    throw new ClipException($msg);
  }
  else {
    exit();
  }
}

/**
 * Converts the value to array if it's not.
 * @param mixed $val
 * 
 * @return array
 */
function arrayfy($val) {
  if (empty($val)) {
    return array();
  }
  elseif (!is_array($val)) {
    $val = array($val);
  }
  
  return $val;
}

/**
 * Check whether the system running the script is windows.
 * 
 * @return boolean.
 */
function is_windows() {
  return PHP_OS == 'WINNT';
}

/**
 * Parses the output of exec mode and returns the values.
 * Output:
 * 
 * [0] =>
 * [1] => Status for device CON:
 * [2] => ----------------------
 * [3] =>     Lines:          25
 * [4] =>     Columns:        80
 * [5] =>     Keyboard rate:  31
 * [6] =>     Keyboard delay: 1
 * [7] =>     Code page:      850
 * [8] =>
 * 
 * When using mode cols,rows it changes the size.
 * 
 * Warning: On windows, the values for columns and lines refer to the
 * buffer size. They have no relation with the window size. Decreasing
 * the windows size will make a scrollbar appear.
 * 
 * @param string $what
 *   What to return. 
 */
function parse_exec_mode($what = NULL) {
  $parsed = array();
  exec('mode', $return);
  
  preg_match('/^\s+Lines:\s+([0-9]+)$/', $return[3], $lines);
  $parsed['lines'] = $lines[1];
  preg_match('/^\s+Columns:\s+([0-9]+)$/', $return[4], $cols);
  $parsed['cols'] = $cols[1];
  
  return isset($parsed[$what]) ? $parsed[$what] : $parsed;
}

/**
 * System function.
 * Returns the cli columns.
 * 
 * Warning: On windows, the values for columns and lines refer to the
 * buffer size. They have no relation with the window size. Decreasing
 * the windows size will make a scrollbar appear.
 * 
 * @return int cols
 */
function get_cli_cols() {
  if (command_exists('tput')) {
    return exec('tput cols');
  }
  else if (is_windows() && command_exists('mode')){
    return parse_exec_mode('cols');
  }
  else {
    return FALSE;
  }
}

/**
 * System function.
 * Returns the cli rows.
 * 
 * Warning: On windows, the values for columns and lines refer to the
 * buffer size. They have no relation with the window size. Decreasing
 * the windows size will make a scrollbar appear.
 * 
 * @return int rows
 */
function get_cli_rows() {
  if (command_exists('tput')) {
    return exec('tput lines');
  }
  else if (is_windows() && command_exists('mode')){
    return parse_exec_mode('lines');
  }
  else {
    return FALSE;
  }
}