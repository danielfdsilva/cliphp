<?php
/**
 * The Main script provides a list of all the available example scripts.
 * 
 * @package     Cliphp
 * @subpackage  Examples 
 * @author      Daniel da Silva
 */
class Main extends Clip {

  // Script version. Although not mandatory it useful to keep
  // track of versions.  
  //const SCRIPT_VERSION = '1.0.0';

  // Configuration function. Mandatory.
  // This function will be used to set up the script by defining what options are allowed, loading libraries etc.
  // The option definition can't be done outside this function.
  protected function configure() {  }

  // This is the actual body of the script.
  // All the logic belonging to the script will be placed here.
  protected function execute() {
    pl('These are the example scripts that are available:');
    pl();
    
    pl("   - options\t\tExample of how to use options.");
    pl("   - defaultOptions\tExample of how to change default options.");
    pl("   - promptLib\t\tExample of how to use the prompt library.");
    pl("   - progressLib\tExample of how to use the progress library.");
    
    pl();
    pl('Try them and take a look at the code to see what each example does.');
    pl();
    pl('You can run them using: php src/index.php [script name]');
  }
}

?>