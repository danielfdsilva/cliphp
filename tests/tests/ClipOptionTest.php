<?php

class ClipOptionTest extends PHPUnit_Framework_TestCase {

    public function testIsValidOption() {
      $this->assertTrue(ClipOption::isValidOption('a'));
      $this->assertTrue(ClipOption::isValidOption('9'));
      
      $this->assertTrue(ClipOption::isValidOption('ab'));
      $this->assertTrue(ClipOption::isValidOption('abc'));
      $this->assertTrue(ClipOption::isValidOption('99-9999'));
      $this->assertTrue(ClipOption::isValidOption('no-cont-rib'));
      $this->assertTrue(ClipOption::isValidOption('no-contrib'));
      
      $this->assertFalse(ClipOption::isValidOption('ab-a'));
      $this->assertFalse(ClipOption::isValidOption('ab-'));
      $this->assertFalse(ClipOption::isValidOption('ab--'));
      $this->assertFalse(ClipOption::isValidOption('no--contrib'));
      $this->assertFalse(ClipOption::isValidOption('n-o-co-o'));
      $this->assertFalse(ClipOption::isValidOption('no-contrib-'));
      
      $this->assertFalse(ClipOption::isValidOption('-'));
      $this->assertFalse(ClipOption::isValidOption('ab_cde'));
      $this->assertFalse(ClipOption::isValidOption('efe/ee'));
    }
    
    public function testClipOptionTypes() {
      $script = TestScript::newInstance();
      $opt1 = $script->setupOpt('a', ClipOption::OPTION_OPTIONAL);
      $this->assertTrue($opt1->isOptional());
      $this->assertFalse($opt1->isFlag());
      $this->assertFalse($opt1->isRequired());
      
      $opt2 = $script->setupOpt('b', ClipOption::OPTION_NO_VALUE);
      $this->assertTrue($opt2->isFlag());
      $this->assertFalse($opt2->isRequired());
      $this->assertFalse($opt2->isOptional());
      
      $opt3 = $script->setupOpt('c', ClipOption::OPTION_REQUIRED);
      $this->assertTrue($opt3->isRequired());
      $this->assertFalse($opt3->isFlag());
      $this->assertFalse($opt3->isOptional());
    }
    
    /**
     * @expectedException ClipOptionException
     * @expectedExceptionMessage Invalid option: inv alid. Only alphanumeric characters.
     */
    public function testClipOptionExceptionWrongValue() {
      $script = TestScript::newInstance();
      $script->setupOpt('inv alid', ClipOption::OPTION_OPTIONAL);
    }
    
    /**
     * @expectedException ClipOptionException
     * @expectedExceptionMessage Invalid alias: inv alid. Only alphanumeric characters.
     */
    public function testClipOptionExceptionAddInvalidAlias() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_OPTIONAL)
        ->alias('inv alid');
    }
    
    /**
     * @expectedException ClipOptionException
     * @expectedExceptionMessage This alias already exists: a.
     */
    public function testClipOptionExceptionAddExistingAlias() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_OPTIONAL)
        ->alias('a');
    }
    
    public function testClipOptionAddAlias() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_OPTIONAL)
        ->alias('b')
        ->alias('c')
        ->alias('def');
        
      $this->assertEquals(array('a', 'b', 'c', 'def'), $opt->getAliases());
    }
    
    /**
     * @expectedException ClipOptionException
     * @expectedExceptionMessage There's no point setting a default value for a option of type OPTION_NO_VALUE.
     */
    public function testClipOptionExceptionAddDefaultValueFlagOption() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_NO_VALUE)
        ->defaultValue('hello');
    }
    
    public function testClipOptionAddDefaultValueOptionalOption() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_OPTIONAL)
        ->defaultValue('hello');
        
      $this->assertEquals('hello', $opt->getValue());
    }
    
    public function testClipOptionAddDefaultValueRequiredOption() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
        ->defaultValue('hello');
        
      $this->assertEquals('hello', $opt->getValue());
    }
    
    /**
     * @expectedException ClipOptionExpectException
     * @expectedExceptionMessage Invalid expectation.
     */
    public function testClipOptionExceptionInvalidExpect() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
        ->expect('invalid');
    }
    
    /**
     * @expectedException ClipOptionExpectException
     * @expectedExceptionMessage Discrete Error: Invalid discrete options.
     */
    public function testClipOptionExceptionExpectDiscrete() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
        ->expect(ClipOption::EXPECT_DISCRETE);
    }
    
    /**
     * @expectedException ClipOptionExpectException
     * @expectedExceptionMessage GT Error: Invalid value.
     */
    public function testClipOptionExceptionExpectGt() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
        ->expect(ClipOption::EXPECT_GT, 'non_numeric');
    }
    
    /**
     * @expectedException ClipOptionExpectException
     * @expectedExceptionMessage GTE Error: Invalid value.
     */
    public function testClipOptionExceptionExpectGte() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
        ->expect(ClipOption::EXPECT_GTE, 'non_numeric');
    }
    
    /**
     * @expectedException ClipOptionExpectException
     * @expectedExceptionMessage LT Error: Invalid value.
     */
    public function testClipOptionExceptionExpectLt() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
        ->expect(ClipOption::EXPECT_LT, 'non_numeric');
    }
    
    /**
     * @expectedException ClipOptionExpectException
     * @expectedExceptionMessage LTE Error: Invalid value.
     */
    public function testClipOptionExceptionExpectLte() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
        ->expect(ClipOption::EXPECT_LTE, 'non_numeric');
    }
    
    /**
     * @expectedException ClipOptionExpectException
     * @expectedExceptionMessage Invalid custom validate function.
     */
    public function testClipOptionExceptionExpectCustom() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
        ->expect(ClipOption::EXPECT_CUSTOM, $custom_validate = 'non_existent_function');
    }
    
    /**
     * @expectedException ClipOptionExpectException
     * @expectedExceptionMessage Range Error: Range missing from option.
     */
    public function testClipOptionExceptionExpectRangeMissing() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
        ->expect(ClipOption::EXPECT_IN_RANGE);
    }
    
    /**
     * @expectedException ClipOptionExpectException
     * @expectedExceptionMessage Range Error: Malformed range.
     */
    public function testClipOptionExceptionExpectRangeMalformed() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
        ->expect(ClipOption::EXPECT_IN_RANGE, array('#range' => '23'));
    }
    
    /**
     * @expectedException ClipOptionExpectException
     * @expectedExceptionMessage Range Error: Lower bound must be lower than upper bound.
     */
    public function testClipOptionExceptionExpectRangeLowerHigher() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_REQUIRED)
        ->expect(ClipOption::EXPECT_IN_RANGE, array('#range' => '[10,5]'));
    }
    
    public function testClipOptionSetValue() {
      $script = TestScript::newInstance();
      
      $opt = $script->setupOpt('a', ClipOption::OPTION_NO_VALUE)
        ->setValue('hello');
      // setValue on OPTION_NO_VALUE has no effect.
      $this->assertEquals(null, $opt->getValue());
      
      $opt2 = $script->setupOpt('b', ClipOption::OPTION_OPTIONAL)
        ->setValue('hello');
        
      $this->assertEquals('hello', $opt2->getValue());
      
      $opt3 = $script->setupOpt('c', ClipOption::OPTION_REQUIRED)
        ->setValue('hello');
        
      $this->assertEquals('hello', $opt3->getValue());
    }
}
?>