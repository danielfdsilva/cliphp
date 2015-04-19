<?php

class ClipOptionExpectValueTest extends PHPUnit_Framework_TestCase {
 
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 10.1.
   */
  public function testClipOptionExceptionSetValueExpectInt() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_INT)
      ->setUsedAlias('a')
      ->setValue(10.1);
  }
  
  public function testClipOptionSetValueExpectInt() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_INT)
      ->setUsedAlias('a')
      ->setValue(10);
    
    $this->assertEquals(10, $opt->getValue());
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 10.
   */
  public function testClipOptionExceptionSetValueExpectFloat() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_FLOAT)
      ->setUsedAlias('a')
      ->setValue(10);
  }
  
  public function testClipOptionSetValueExpectFloat() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_FLOAT)
      ->setUsedAlias('a')
      ->setValue(10.2);
    
    $this->assertEquals(10.2, $opt->getValue());
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: hello.
   */
  public function testClipOptionExceptionSetValueExpectNumber() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_NUMBER)
      ->setUsedAlias('a')
      ->setValue('hello');
  }
  
  public function testClipOptionSetValueExpectNumber() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_FLOAT)
      ->setUsedAlias('a')
      ->setValue(10.2);
    
    $this->assertEquals(10.2, $opt->getValue());
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: four.
   */
  public function testClipOptionExceptionSetValueExpectDiscrete() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_DISCRETE, array('#options' => array('one', 'two', 'three')))
      ->setUsedAlias('a')
      ->setValue('four');
  }
  
  public function testClipOptionSetValueExpectDiscrete() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_DISCRETE, array('#options' => array('one', 'two', 'three')))
      ->setUsedAlias('a')
      ->setValue('one');
    
    $this->assertEquals('one', $opt->getValue());
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: hello.
   */
  public function testClipOptionExceptionSetValueExpectGt() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_GT, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue('hello');
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 10.
   */
  public function testClipOptionExceptionSetValueExpectGt2() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_GT, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue(10);
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 100.5.
   */
  public function testClipOptionExceptionSetValueExpectGt3() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      // If the option is an integer let's force an integer value.
      // If the user wants to allow floats needs to
      // use something like 10.0
      ->expect(ClipOption::EXPECT_GT, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue(100.5);
  }
  
  public function testClipOptionSetValueExpectGt() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_GT, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue(101);
    
    $this->assertEquals(101, $opt->getValue());
  }
  
  public function testClipOptionSetValueExpectGt2() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_GT, array('#value' => 100.0))
      ->setUsedAlias('a')
      ->setValue(100.5);
    
    $this->assertEquals(100.5, $opt->getValue());
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: hello.
   */
  public function testClipOptionExceptionSetValueExpectGte() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_GTE, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue('hello');
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 10.
   */
  public function testClipOptionExceptionSetValueExpectGte2() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_GTE, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue(10);
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 100.5.
   */
  public function testClipOptionExceptionSetValueExpectGte3() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      // If the option is an integer let's force an integer value.
      // If the user wants to allow floats needs to
      // use something like 10.0
      ->expect(ClipOption::EXPECT_GTE, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue(100.5);
  }
  
  public function testClipOptionSetValueExpectGte() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_GTE, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue(100);
    
    $this->assertEquals(100, $opt->getValue());
  }
  
  public function testClipOptionSetValueExpectGte2() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_GTE, array('#value' => 100.0))
      ->setUsedAlias('a')
      ->setValue(100);
    
    $this->assertEquals(100, $opt->getValue());
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: hello.
   */
  public function testClipOptionExceptionSetValueExpectLt() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_LT, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue('hello');
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 100.
   */
  public function testClipOptionExceptionSetValueExpectLt2() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_LT, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue(100);
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 99.5.
   */
  public function testClipOptionExceptionSetValueExpectLt3() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      // If the option is an integer let's force an integer value.
      // If the user wants to allow floats needs to
      // use something like 10.0
      ->expect(ClipOption::EXPECT_LT, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue(99.5);
  }
  
  public function testClipOptionSetValueExpectLt() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_LT, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue(99);
    
    $this->assertEquals(99, $opt->getValue());
  }
  
  public function testClipOptionSetValueExpectLt2() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_LT, array('#value' => 100.0))
      ->setUsedAlias('a')
      ->setValue(99.5);
    
    $this->assertEquals(99.5, $opt->getValue());
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: hello.
   */
  public function testClipOptionExceptionSetValueExpectLte() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_LTE, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue('hello');
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 1000.
   */
  public function testClipOptionExceptionSetValueExpectLte2() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_LTE, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue(1000);
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 99.5.
   */
  public function testClipOptionExceptionSetValueExpectLte3() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      // If the option is an integer let's force an integer value.
      // If the user wants to allow floats needs to
      // use something like 10.0
      ->expect(ClipOption::EXPECT_LTE, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue(99.5);
  }
  
  public function testClipOptionSetValueExpectLte() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_LTE, array('#value' => 100))
      ->setUsedAlias('a')
      ->setValue(100);
    
    $this->assertEquals(100, $opt->getValue());
  }
  
  public function testClipOptionSetValueExpectLte2() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_LTE, array('#value' => 100.0))
      ->setUsedAlias('a')
      ->setValue(100);
    
    $this->assertEquals(100, $opt->getValue());
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: hello.
   */
  public function testClipOptionExceptionSetValueExpectCustomFunction2() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_CUSTOM, array(), function($val) { return FALSE; })
      ->setUsedAlias('a')
      ->setValue('hello');
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Custom error message.
   */
  public function testClipOptionExceptionSetValueExpectCustomFunctionMulti() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      // When using multi, all the values are sent to the function.
      // Error messages must be set by the custom function.
      ->expect(ClipOption::EXPECT_CUSTOM, array('#multi' => TRUE), function($values) {
        $this->assertEquals(array('hello', 'world'), $values);
        throw new ClipUseException('Custom error message.');
        return FALSE;
      })
      ->setUsedAlias('a')
      ->setValue(array('hello', 'world'));
  }
  
  public function testClipOptionSetValueExpectCustomFunction() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_CUSTOM, array(), function($val) { return TRUE; })
      ->setUsedAlias('a')
      ->setValue(100);
    
    $this->assertEquals(100, $opt->getValue());
  }
  
  public function testClipOptionSetValueExpectCustomFunctionMulti() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_CUSTOM, array('#multi' => TRUE), function($values) {
        $this->assertEquals(array('hello', 'world'), $values);
        return TRUE;
      })
      ->setUsedAlias('a')
      ->setValue(array('hello', 'world'));
    
    $this->assertEquals('hello', $opt->getValue());
    $this->assertEquals('world', $opt->getValue(1));
    $this->assertEquals(array('hello', 'world'), $opt->getMultiValue());
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: hello.
   * 
   * Expecting int, string given.
   */
  public function testClipOptionExceptionSetValueExpectRangeStringGiven() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_IN_RANGE, array('#range' => '[1,10]'))
      ->setUsedAlias('a')
      ->setValue('hello');
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 5.5.
   * 
   * Expecting int, float given.
   */
  public function testClipOptionExceptionSetValueExpectRangeFloatGiven() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_IN_RANGE, array('#range' => '[1,10]'))
      ->setUsedAlias('a')
      ->setValue(array(1, 3, 5.5, 8));
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 15.
   * 
   * Expecting max 10, 15 given
   */
  public function testClipOptionExceptionSetValueExpectRangeOverUBound() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_IN_RANGE, array('#range' => '[1,10]'))
      ->setUsedAlias('a')
      ->setValue(15);
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 1.
   * 
   * Lower Bound not included.
   */
  public function testClipOptionExceptionSetValueExpectRangeLBoundNotIncluded() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_IN_RANGE, array('#range' => '(1,10]'))
      ->setUsedAlias('a')
      ->setValue(1);
  }
  
  /**
   * @expectedException ClipUseException
   * @expectedExceptionMessage Invalid value. Option: a Value: 10.
   * 
   * Upper Bound not included.
   */
  public function testClipOptionExceptionSetValueExpectRangeUBoundNotIncluded() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_IN_RANGE, array('#range' => '[1,10)'))
      ->setUsedAlias('a')
      ->setValue(10);
  }
  
  public function testClipOptionSetValueExpectRange() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_IN_RANGE, array('#range' => '(1,10]'))
      ->setUsedAlias('a')
      ->setValue(array(2, 3, 8, 10));
  }
  
  public function testClipOptionSetValueExpectRangeWithFloats() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
      ->expect(ClipOption::EXPECT_IN_RANGE, array('#range' => '(1,10.0]'))
      ->setUsedAlias('a')
      ->setValue(array(2, 3.6, 8.32846, 10, pi()));
  }
}
?>