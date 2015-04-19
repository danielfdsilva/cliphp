<?php

require APP_DIR . '/core/libraries/prompt.php';

/**
 * Extend the Prompt library with a fake readValue methods to be able to
 * test input.
 * 
 * It is possible to simulate the values entered by the user using:
 * PromptTesting::$read_value_return_val = array('value')
 * 
 * The prompt library will keep on asking values until a valid one is found.
 * 
 * Adds a newInstance method that will return a new instance of the library
 * allowing to use one time methods like type().
 */
class PromptTesting extends Prompt {
  // These need to be static since the readValue method is a static one.
  static $read_value_return_val = array();
  static $read_value_iteration = 0;

  function onLoad() {
    parent::onLoad();
    static::$read_value_iteration = 0;
    static::$read_value_return_val = array();
  }

  /**
  * Mock readValue.
  */
  static function readValue($prompt = '> ') {
    $val = static::$read_value_return_val[static::$read_value_iteration];
    static::$read_value_iteration++;
    print($prompt);
    // Simulate value introduction.
    print($val . "\n");
    return $val;
  }

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
 * Prompt test class.
 */
class PromptLibraryTest extends PHPUnit_Framework_TestCase {

  /**
   * @expectedException ClipLibraryException
   * @expectedExceptionMessage Invalid options provided for confirm prompt.
   */
  public function testExceptionConfirmInvalidDefaultOptions() {
    $prompt = PromptTesting::newInstance();
    $prompt->confirm('invalid');
  }
  
  /**
   * @expectedException ClipLibraryException
   * @expectedExceptionMessage Invalid custom confirm options provided for confirm prompt.
   */
  public function testExceptionConfirmInvalidCustomOptions() {
    $prompt = PromptTesting::newInstance();
    $prompt->confirm(array('only one'));
  }
  
  /**
   * @expectedException ClipLibraryException
   * @expectedExceptionMessage Invalid custom confirm options provided for confirm prompt.
   */
  public function testExceptionConfirmInvalidCustomOptions2() {
    $prompt = PromptTesting::newInstance();
    $prompt->confirm(array('one', 'two', 'three'));
  }
  
  public function testConfirm() {
    // Values to mock user input
    $input_values = array('no', 'ok', '0', 0, 1, 'y');
    $expected_print = "Are you sure? (y/n): no\n";
    $expected_print .= "Are you sure? (y/n): ok\n";
    $expected_print .= "Are you sure? (y/n): 0\n";
    $expected_print .= "Are you sure? (y/n): 0\n";
    $expected_print .= "Are you sure? (y/n): 1\n";
    $expected_print .= "Are you sure? (y/n): y\n";

    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    // The only valid answer will be the 'y'.
    $this->assertTrue($prompt->confirm('yn'));
  }
  
  public function testConfirmCaseSensitive() {
    // Values to mock user input
    $input_values = array('y', 'y', 'y', 'n');
    $expected_print = "Are you sure? (Y/n): y\n";
    $expected_print .= "Are you sure? (Y/n): y\n";
    $expected_print .= "Are you sure? (Y/n): y\n";
    $expected_print .= "Are you sure? (Y/n): n\n";

    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    // The only valid answer will be the 'n'.
    $this->assertFalse($prompt->confirm('Yn'));
  }
  
  public function testConfirmCustomQuestion() {
    // Values to mock user input
    $input_values = array('no', 'no', 'y');
    $expected_print = "Custom question? (y/n): no\n";
    $expected_print .= "Custom question? (y/n): no\n";
    $expected_print .= "Custom question? (y/n): y\n";

    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    // The only valid answer will be the 'y'.
    $this->assertTrue($prompt->confirm('yn', 'Custom question?'));
  }
  
  /**
   * @expectedException ClipLibraryException
   * @expectedExceptionMessage Invalid options.
   */
  public function testExceptionOption() {
    $prompt = PromptTesting::newInstance();
    $prompt->option('invalid');
  }
  
  public function testOption() {
    $options = array('zero', 'one', 'two', 't' => 'tree', 't2' => 'another tree');
    
    $expected_print = "[0] => zero\n";
    $expected_print .= "[1] => one\n";
    $expected_print .= "[2] => two\n";
    $expected_print .= "[t] => tree\n";
    $expected_print .= "[t2] => another tree\n";
    $expected_print .= "\n";
    $expected_print .= "> y\n";
    $expected_print .= "Invalid option.\n";
    $expected_print .= "> 3\n";
    $expected_print .= "Invalid option.\n";
    $expected_print .= "> false\n";
    $expected_print .= "Invalid option.\n";
    $expected_print .= "> T\n";
    $expected_print .= "Invalid option.\n";
    $expected_print .= "> t\n";
    
    $input_values = array('y', '3', 'false', 'T', 't');
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    // The only valid answer will be the 't'.
    $this->assertEquals('t', $prompt->option($options));
  }

  public function testTextWithNull() {
    
    $expected_print = "> \n";
    
    // Since we're accepting nulls, hello and world are never printed
    // because the first option is accepted
    $input_values = array('', 'hello', 'world');
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals('', $prompt->text());
  }
  
  public function testTextNoNull() {
    
    $expected_print = "> \n";
    $expected_print .= ">     \n";
    $expected_print .= "> hello\n";
    
    $input_values = array('', '    ', 'hello');
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals('hello', $prompt->text(FALSE));
  }
  
  /**
   * @expectedException ClipLibraryException
   * @expectedExceptionMessage Range Error: Malformed range.
   */
  public function testExceptionRangeMalformed() {
    $prompt = PromptTesting::newInstance();
    $prompt->range('invalid');
  }
  
  /**
   * @expectedException ClipLibraryException
   * @expectedExceptionMessage Range Error: Lower bound must be lower than upper bound.
   */
  public function testExceptionRangeLowerHigher() {
    $prompt = PromptTesting::newInstance();
    $prompt->range('[10,1]');
  }
  
  public function testRange() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 1\n";
    $expected_print .= "Value not in range.\n";
    $expected_print .= "> 11\n";
    $expected_print .= "Value not in range.\n";
    $expected_print .= "> 1.1\n";
    $expected_print .= "Value not in range.\n";
    $expected_print .= "> 10\n";
    
    $input_values = array('hello', 1, 11, 1.1, 10);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(10, $prompt->range('(1,10]'));
  }
  
  public function testRangeFloat() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> -1\n";
    $expected_print .= "Value not in range.\n";
    $expected_print .= "> 10\n";
    $expected_print .= "Value not in range.\n";
    $expected_print .= "> 11\n";
    $expected_print .= "Value not in range.\n";
    $expected_print .= "> 1.1\n";
    
    $input_values = array('hello', -1, 10, 11, 1.1);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(1.1, $prompt->range('[1,10.0)'));
  }
  
  public function testGt() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> -1\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10.5\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 55\n";
    
    $input_values = array('hello', -1, 10, 10.5, 55);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(55, $prompt->gt(10));
  }
  
  public function testGtFloat() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> -1\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10.5\n";
    
    $input_values = array('hello', -1, 10, 10.5);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(10.5, $prompt->gt(10.0));
  }
  
  public function testGte() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> -1\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10.5\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10\n";
    
    $input_values = array('hello', -1, 10.5, 10);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(10, $prompt->gte(10));
  }
  
  public function testGteFloat() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> -1\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10\n";
    
    $input_values = array('hello', -1, 10);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(10, $prompt->gte(10.0));
  }
  
  public function testLt() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 5.5\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> -10\n";
    
    $input_values = array('hello', 10, 5.5, -10);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(-10, $prompt->lt(10));
  }
  
  public function testLtFloat() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 5.5\n";
    
    $input_values = array('hello', 10, 5.5);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(5.5, $prompt->lt(10.0));
  }
  
  public function testLte() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 5.5\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 11\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10\n";
    
    $input_values = array('hello', 5.5, 11, 10);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(10, $prompt->lte(10));
  }
  
  public function testLteFloat() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 11\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 11.5\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10\n";
    
    $input_values = array('hello', 11, 11.5, 10);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(10, $prompt->lte(10.0));
  }
  
  public function testNumber() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> FALSE\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> TRUE\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> false\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> true\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10.23\n";
    
    $input_values = array('hello', 'FALSE', 'TRUE', 'false', 'true', 10.23);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(10.23, $prompt->number());
  }
  
  public function testFloat() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> FALSE\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> TRUE\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> false\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> true\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 3.1415\n";
    
    $input_values = array('hello', 'FALSE', 'TRUE', 'false', 'true', 10, 3.1415);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(3.1415, $prompt->float());
  }
  
  public function testInt() {
    $expected_print = "> hello\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> FALSE\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> TRUE\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> false\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> true\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> \n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= ">     \n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 3.1415\n";
    $expected_print .= "Invalid value.\n";
    $expected_print .= "> 10\n";
    
    $input_values = array('hello', 'FALSE', 'TRUE', 'false', 'true', '', '    ', 3.1415, 10);
    $prompt = PromptTesting::newInstance();
    PromptTesting::$read_value_return_val = $input_values;
    
    // For every invalid answer the system will ask again.
    $this->expectOutputString($expected_print);
    
    $this->assertEquals(10, $prompt->int());
  }
}

?>