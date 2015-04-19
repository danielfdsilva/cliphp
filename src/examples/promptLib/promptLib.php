<?php
/**
 * The promptLib script is an example of how to use the Prompt Library.
 * 
 * @package     Cliphp
 * @subpackage  Examples 
 * @author      Daniel da Silva
 */
class PromptLib extends Clip {

  // Script version. Although not mandatory it useful to keep
  // track of versions.  
  const SCRIPT_VERSION = '1.0.0';

  // Configuration function. Mandatory.
  // This function will be used to set up the script by defining what options are allowed, loading libraries etc.
  // The option definition can't be done outside this function.
  protected function configure() {
    $this->load()->library('prompt');
  }

  // This is the actual body of the script.
  // All the logic belonging to the script will be placed here.
  protected function execute() {
    pl('Hi there. What\'s your name?');
    $name = $this->prompt->text();
    
    pl('Hello ' . $name);
    pl();
    
    pl('Tell me one thing. Which one of these fruits do you prefer?');
    $options = array(
      '1' => 'Apple',
      '2' => 'Pear',
      '3' => 'Banana',
      '4' => 'Figs',
      '0' => 'None'
    );
    $val = $this->prompt->option($options);
    if ($val == 0) {
      pl('Really!? No fruits at all? That\'s sad...');
      return;
    }
    
    if ($val == 4) {
      pl('Cool. I also like Figs! :)');
      pl();
    }
    
    pl('I won\'t bother you with more questions.');
    pl();
    if ($this->prompt->confirm('yn', 'Do you want to start over?')) {
      pl();
      pl();
      $this->execute();
    }
  }
}

?>