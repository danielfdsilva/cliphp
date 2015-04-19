<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

class TestScript extends Clip {
  
  /**
   * Script version.
   */
  const SCRIPT_VERSION = '0.0.1';
  
  /*
   * Function to reset the instance. Allows to clear the script and
   * instantiate a new one. This is needed for the tests to work.
   */
  public static function newInstance() {
    self::$INSTANCE = null;
    return self::getInstance();
  }
  
  protected function configure() { }
  
  protected function execute() { }
  
  public function help() {
    p('Help function override.');
  }
  
  public function version() {
    p('Version function override.');
  }
}

?>