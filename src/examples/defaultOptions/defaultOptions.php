<?php
/**
 * The DefaultOptions script is an example of how to override the
 * default options set by cliphp
 * 
 * @package     Cliphp
 * @subpackage  Examples 
 * @author      Daniel da Silva
 */
class DefaultOptions extends Clip {

  // Script version. Although not mandatory it useful to keep
  // track of versions.  
  const SCRIPT_VERSION = '1.9.4';

  // Configuration function. Mandatory.
  // This function will be used to set up the script by defining what options are allowed, loading libraries etc.
  // The option definition can't be done outside this function.
  protected function configure() {
    // Disable default version option.
    $this->disableDefaultVersion();
    
    // New and improved help version.
    $help = $this->setupOpt('sos')
      ->describe('Provides help to a poor lost soul.');
    $this->setHelpOption($help);
    
    // Setup the old help command just in case the user uses it.
    // If he does show a message.
    $this->setupOpt('h')->alias('help');
  }

  // This is the actual body of the script.
  // All the logic belonging to the script will be placed here.
  protected function execute() {
    // User used -h. Tell him what the correct help is.
    if ($this->opt('h')->isGiven()) {
      pl('This is not the help you\'re looking for.');
      pl('Try with --sos');
    }
    else {
      pl('This script doesn\'t have an option to show a version.');
      pl('But here it is written in Roman Numerals');
      pl();
      pl('Version: I.IX.IV');
    }
  }
  
  // Override help to show a different message.
  public function help() {
    pl('This script does not provide any options.');
    pl('Just call it with no arguments.');
  }
}

?>