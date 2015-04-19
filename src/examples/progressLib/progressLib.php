<?php
/**
 * The ProgressLib script is an example of how to use the Progress Library.
 * 
 * @package     Cliphp
 * @subpackage  Examples 
 * @author      Daniel da Silva
 */
class ProgressLib extends Clip {

  // Script version. Although not mandatory it useful to keep
  // track of versions.  
  const SCRIPT_VERSION = '1.0.0';

  // Configuration function. Mandatory.
  // This function will be used to set up the script by defining what options are allowed, loading libraries etc.
  // The option definition can't be done outside this function.
  protected function configure() {
    $this->load()->library('progress');
    
    $this->setupOpt('n', ClipOption::OPTION_REQUIRED)
      ->describe('Total value for the progress bar. Default to 100.')
      ->expect(ClipOption::EXPECT_GT, array('#value' => 0))
      ->defaultValue(100);
      
    $this->setupOpt('t', ClipOption::OPTION_REQUIRED)
      ->describe('Amout of time each item should take to process in milliseconds. Default to 10.')
      ->expect(ClipOption::EXPECT_GT, array('#value' => 0))
      ->defaultValue(10);
      
    $this->setupOpt('r')
      ->describe('Randomize the time each item takes. Uses a value from 0 to -t');
  }

  // This is the actual body of the script.
  // All the logic belonging to the script will be placed here.
  protected function execute() {
    // Set as class properties to use them in the loop function();
    $this->total = $this->opt('n')->getValue();
    $this->time = $this->opt('t')->getValue();
    $this->rand = $this->opt('r')->isGiven();
    
    pl('The program is loading. Please hold tight.');
    usleep(500000);
    // Set up the progress defining a type and a total value.
    $this->progress->type(Progress::PERCENT)->total($this->total);
    $this->loop();
    
    pl();
    pl('The bar will start filling up in a second.');
    sleep(1);
    
    // Reset the progress.
    $this->progress->reset();
    // Re-configure.
    $this->progress->type(Progress::BAR)->total($this->total);
    $this->loop();
    
    pl();
    pl('Done with a simple bar. Let\'s see another one. This one works great with -r -t 100');
    sleep(1);
    
    // Reset the progress.
    $this->progress->reset();
    // Re-configure.
    $this->progress->type(Progress::BAR_RATE_SECONDS)->total($this->total);
    $this->loop();

    pl();
    pl('Now a bar with items per second. This one also works great with -r -t 100');
    sleep(1);
    
    // Reset the progress.
    $this->progress->reset();
    // Re-configure.
    $this->progress->type(Progress::BAR_RATE_ITEMS)->total($this->total);
    $this->loop();
  }

  /**
   * Helper function to do the progress update loop.
   */
  protected function loop() {
    for ($i = 1; $i <= $this->total; $i++) {
      // Update and sleep for specified amount of time.
      $this->progress->update($i);
      // To randomize or not to randomize.
      $t = $this->rand ? rand(1, $this->time) : $this->time;
      usleep($t * 1000);
    }
  }
}

?>