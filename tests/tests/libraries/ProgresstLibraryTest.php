<?php

require APP_DIR . '/core/libraries/progress.php';

/**
 * Extend the Progress library.
 * 
 * Adds a newInstance method that will return a new instance of the library
 * allowing to use one time methods like type().
 * 
 */
class ProgressTesting extends Progress {
  /*
   * Function to reset the instance. Allows to clear the library and
   * instantiate a new one. This is needed for the tests to work.
   */
  public static function newInstance() {
    self::$INSTANCES[get_called_class()] = NULL;
    return self::getInstance();
  }
}

/**
 * Progress test class.
 */
class ProgressLibraryTest extends PHPUnit_Framework_TestCase {
  
  public static function setUpBeforeClass() {
    // Since the libraries can only be instantiated during the configure phase
    // we initialise the test script.
    TestScript::newInstance();
  }
  
  /**
   * @expectedException ClipLibraryException
   * @expectedExceptionMessage Invalid Progress type.
   */
  function testExceptionInvalidType() {
    $progress = ProgressTesting::newInstance();
    $progress->type('invalid');
  }
  
  /**
   * @expectedException ClipLibraryException
   * @expectedExceptionMessage Progress total value was not set.
   */
  function testExceptionMissingTotal() {
    $progress = ProgressTesting::newInstance();
    $progress->update(10);
  }
  
  /**
   * @expectedException ClipLibraryException
   * @expectedExceptionMessage Progress total can't be lower than 1.
   */
  function testExceptionMissingType() {
    $progress = ProgressTesting::newInstance();
    $progress->total(0);
  }
  
  /**
   * @expectedException ClipLibraryException
   * @expectedExceptionMessage After the progress is updated, the settings can't be changed.
   */
  function testExceptionSetTypeLocked() {
    $progress = ProgressTesting::newInstance();
    $progress->total(10);
    // Prevent the output from showing in the console.
    ob_start();
    $progress->update(10);
    ob_end_clean();
    
    $progress->type(Progress::BAR);
  }
  
  /**
   * @expectedException ClipLibraryException
   * @expectedExceptionMessage After the progress is updated, the settings can't be changed.
   */
  function testExceptionSetTotalLocked() {
    $progress = ProgressTesting::newInstance();
    $progress->total(10);
    // Prevent the output from showing in the console.
    ob_start();
    $progress->update(10);
    ob_end_clean();
    
    $progress->total(100);
  }
  
  function testPrintCaching() {
    $progress = ProgressTesting::newInstance();
    $progress->type(Progress::PERCENT);
    $progress->total(100);
    
    // Prevent the output from showing in the console.
    // The percent type of progress has a precision of 2 decimals.
    // That means that the maximum number of different values possible
    // will be 10000.
    // So, on values lower then 10000 every iteration is printed.
    // On values from 10000 to 20001, 10000 iterations are printed since
    // the first value to be printed is 0.01 %.
    // From 20002 onwards the first value to be printed becomes 0 % thus the
    // 10001 prints.
    ob_start();
    for ($i=1; $i <= 100; $i++) { 
      $progress->update($i);
    }
    ob_end_clean();
    $this->assertEquals(100, $progress->getNumPrints());
    
    // 9000
    $progress->reset();
    $progress->total(9000);
    ob_start();
    for ($i=1; $i <= 9000; $i++) { 
      $progress->update($i);
    }
    ob_end_clean();
    $this->assertEquals(9000, $progress->getNumPrints());
    
    // 10000
    $progress->reset();
    $progress->total(10000);
    ob_start();
    for ($i=1; $i <= 10000; $i++) { 
      $progress->update($i);
    }
    ob_end_clean();
    $this->assertEquals(10000, $progress->getNumPrints());
    
    // 20001
    $progress->reset();
    $progress->total(20001);
    ob_start();
    for ($i=1; $i <= 20001; $i++) { 
      $progress->update($i);
    }
    ob_end_clean();
    $this->assertEquals(10001, $progress->getNumPrints());
    
    // If the second parameter for update is TRUE every iteration will
    // always be printed.
    // 12345
    $progress->reset();
    $progress->total(12345);
    ob_start();
    for ($i=1; $i <= 12345; $i++) { 
      $progress->update($i, TRUE);
    }
    ob_end_clean();
    $this->assertEquals(12345, $progress->getNumPrints());
  }
  
  function testPrintingAgainstMaster() {
    $progress = ProgressTesting::newInstance();
    $progress->type(Progress::PERCENT);
    $progress->total(10000);
    
    ob_start();
    for ($i=1; $i <= 10000; $i++) { 
      $progress->update($i);
    }
    $prints = ob_get_contents();
    ob_end_clean();
    $master = file_get_contents('tests/resources/libraries/prompt/progress_PERCENT_print_master_10000.txt');
    $this->assertTrue($prints === $master, 'Failed asserting progress PERCENT.');
   
    $progress->reset();
    $progress->total(10000);
    $progress->type(Progress::BAR);
    ob_start();
    for ($i=1; $i <= 10000; $i++) { 
      $progress->update($i);
    }
    $prints = ob_get_contents();
    ob_end_clean();
    $master = file_get_contents('tests/resources/libraries/prompt/progress_BAR_print_master_10000.txt');
    $this->assertTrue($prints === $master, 'Failed asserting progress BAR.');
  }
}

?>