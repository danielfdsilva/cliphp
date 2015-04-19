<?php
/**
 * The Options script is an example of how to setup and use the
 * cliphp's options.
 * 
 * @package     Cliphp
 * @subpackage  Examples 
 * @author      Daniel da Silva
 */
class Options extends Clip {

  // Script version. Although not mandatory it useful to keep
  // track of versions.  
  const SCRIPT_VERSION = '1.0.0';

  // Configuration function. Mandatory.
  // This function will be used to set up the script by defining what options are allowed, loading libraries etc.
  // The option definition can't be done outside this function.
  protected function configure() {
    $this->setupOpt('n', ClipOption::OPTION_REQUIRED)
     ->alias('name')
     ->describe('The name to greet.');
   
   $this->setupOpt('r', ClipOption::OPTION_OPTIONAL)
     ->describe('The amout of times to repeat. Min: 2, Max 10')
     ->expect(ClipOption::EXPECT_IN_RANGE, array('#range' => '[2,10]'))
     ->defaultValue(10);
  }

  // This is the actual body of the script.
  // All the logic belonging to the script will be placed here.
  protected function execute() {
    // If the user provided an amount of times to repeat
    // use it, otherwise just repeat once.
    if ($this->opt('r')->isGiven()) {
      $repeat = $this->opt('r')->getValue();
    }
    else {
      $repeat = 1;
    }
    
    // If the user provides a name, greet him
    // otherwise say "Hello, I'm Cliphp"
    if ($this->opt('n')->isGiven()) {
      $message = "Hello " . $this->opt('n')->getValue();
    }
    else {
      $message = "Hello, I'm Cliphp";
    }
    
    // Repeat specidied amount of times.
    for ($i = 0; $i < $repeat; $i++) {
      pl($message);
    }
  }
}

?>