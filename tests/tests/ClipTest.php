<?php

class ClipTest extends PHPUnit_Framework_TestCase {
  
  /**
   * @expectedException ClipException
   * @expectedExceptionMessage The script was already configured. This command is not allowed outside configure().
   */
  public function testExceptionSetupOptAfterInitialization() {
    $script = TestScript::newInstance();
    $script->setInitialized();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_NO_VALUE);
  }
  
  /**
   * @expectedException ClipException
   * @expectedExceptionMessage This option was already registered: a.
   */
  public function testExceptionSetupOptAlreadyRegistered() {
    $script = TestScript::newInstance();
    
    $opt = $script->setupOpt('a', ClipOption::OPTION_NO_VALUE);
    $opt = $script->setupOpt('a', ClipOption::OPTION_NO_VALUE);
  }
  
  public function testGetOpt() {
    $script = TestScript::newInstance();
    
    $optA = $script->setupOpt('a', ClipOption::OPTION_NO_VALUE);
    $optB = $script->setupOpt('b', ClipOption::OPTION_NO_VALUE);
    $optC = $script->setupOpt('c', ClipOption::OPTION_NO_VALUE)
      ->alias('d');
      
    $this->assertEquals(NULL, $script->opt('z'));
    $this->assertEquals($optC, $script->opt('c'));
    $this->assertEquals($optC, $script->opt('d'));
    $this->assertEquals($optA, $script->opt('a'));
  }
  
  public function testRemoveOpt() {
    $script = TestScript::newInstance();
    
    $optA = $script->setupOpt('a', ClipOption::OPTION_NO_VALUE);
    $optB = $script->setupOpt('b', ClipOption::OPTION_NO_VALUE);
    $optC = $script->setupOpt('c', ClipOption::OPTION_NO_VALUE)
      ->alias('d');
    
    $script->removeOpt('b');
    // Remove by d, search by c.
    $script->removeOpt('d');
    
    $this->assertEquals(NULL, $script->opt('b'));
    // Remove by d, search by c.
    $this->assertEquals(NULL, $script->opt('c'));
    $this->assertEquals($optA, $script->opt('a'));
  }
  
  public function testGetVersion() {
    $script = TestScript::newInstance();
    
    // The version comes from the test script.
    $this->assertEquals('0.0.1', $script->getVersion());
  }

  /**
   * @expectedException ClipException
   * @expectedExceptionMessage The script was already configured. This command is not allowed outside configure().
   */
  public function testExceptionDisableHelpAfterInitialization() {
    $script = TestScript::newInstance();
    $script->init(Clip::PHASE_CONFIGURATION);
    $script->setInitialized();
    
    $script->disableDefaultHelp();
  }
  
  /**
   * @expectedException ClipException
   * @expectedExceptionMessage The script was already configured. This command is not allowed outside configure().
   */
  public function testExceptionSetHelpAfterInitialization() {
    $script = TestScript::newInstance();
    $script->init(Clip::PHASE_CONFIGURATION);
    $script->setInitialized();
    
    $opt = $script->setHelpOption($script->setupOpt('a'));
  }
  
  public function testDefaultHelp() {
    $script = TestScript::newInstance();
    // Set the script in the state it would be in the configure phase.
    $script->init(Clip::PHASE_CONFIGURATION);
    
    // Check the defaults. Set in the init PHASE_CONFIGURATION
    $this->assertEquals(array('h', 'help'), $script->getHelpOption()->getAliases());
    
    // Disable help.
    $script->disableDefaultHelp();
    $this->assertEquals(NULL, $script->getHelpOption());
    
    // Set new help.
    $script->setHelpOption($script->setupOpt('a'));
    $this->assertEquals(array('a'), $script->getHelpOption()->getAliases());
  }
  
  /**
   * @expectedException ClipException
   * @expectedExceptionMessage PHASE_EXECUTION Help function.
   */
  public function testDefaultHelpOptionIsCalled() {
    $this->expectOutputString('Help function override.');
    
    $original_argv = $_SERVER['argv'];
    
    $script = TestScript::newInstance();
    // Set the script in the state it would be in the configure phase.
    $script->init(Clip::PHASE_CONFIGURATION);
    
    // Fake server argv for PHASE_ARG_INIT.
    $_SERVER['argv'][] = '--help';

    $script->init(Clip::PHASE_ARG_INIT);
    // Restore argv.

    $_SERVER['argv'] = $original_argv;
    $script->init(Clip::PHASE_EXECUTION);
    
  }
  
  /**
   * @expectedException ClipException
   * @expectedExceptionMessage PHASE_EXECUTION Help function.
   */
  public function testCustomHelpOptionIsCalled() {
    $this->expectOutputString('Help function override.');
    
    $original_argv = $_SERVER['argv'];
    
    $script = TestScript::newInstance();
    // Set the script in the state it would be in the configure phase.
    $script->init(Clip::PHASE_CONFIGURATION);
    
    // Set new help.
    $script->setHelpOption($script->setupOpt('a'));
    
    // Fake server argv for PHASE_ARG_INIT.
    $_SERVER['argv'][] = '-a';

    $script->init(Clip::PHASE_ARG_INIT);
    // Restore argv.

    $_SERVER['argv'] = $original_argv;
    $script->init(Clip::PHASE_EXECUTION);
    
  }
  
  /**
   * @expectedException ClipException
   * @expectedExceptionMessage The script was already configured. This command is not allowed outside configure().
   */
  public function testExceptionDisableVersionAfterInitialization() {
    $script = TestScript::newInstance();
    $script->init(Clip::PHASE_CONFIGURATION);
    $script->setInitialized();
    
    $script->disableDefaultVersion();
  }
  
  /**
   * @expectedException ClipException
   * @expectedExceptionMessage The script was already configured. This command is not allowed outside configure().
   */
  public function testExceptionSetVersionAfterInitialization() {
    $script = TestScript::newInstance();
    $script->init(Clip::PHASE_CONFIGURATION);
    $script->setInitialized();
    
    $opt = $script->setVersionOption($script->setupOpt('a'));
  }
  
  public function testDefaultVersion() {
    $script = TestScript::newInstance();
    // Set the script in the state it would be in the configure phase.
    $script->init(Clip::PHASE_CONFIGURATION);
    
    // Check the defaults. Set in the init PHASE_CONFIGURATION
    $this->assertEquals(array('v', 'version'), $script->getVersionOption()->getAliases());
    
    // Disable version.
    $script->disableDefaultVersion();
    $this->assertEquals(NULL, $script->getVersionOption());
    
    // Set new version.
    $script->setVersionOption($script->setupOpt('a'));
    $this->assertEquals(array('a'), $script->getVersionOption()->getAliases());
  }
  
  /**
   * @expectedException ClipException
   * @expectedExceptionMessage PHASE_EXECUTION Version function.
   */
  public function testDefaultVersionOptionIsCalled() {
    $this->expectOutputString('Version function override.');
    
    $original_argv = $_SERVER['argv'];
    
    $script = TestScript::newInstance();
    // Set the script in the state it would be in the configure phase.
    $script->init(Clip::PHASE_CONFIGURATION);
    
    // Fake server argv for PHASE_ARG_INIT.
    $_SERVER['argv'][] = '--version';

    $script->init(Clip::PHASE_ARG_INIT);
    // Restore argv.

    $_SERVER['argv'] = $original_argv;
    $script->init(Clip::PHASE_EXECUTION);
    
  }
  
  /**
   * @expectedException ClipException
   * @expectedExceptionMessage PHASE_EXECUTION Version function.
   */
  public function testCustomVersionOptionIsCalled() {
    $this->expectOutputString('Version function override.');
    
    $original_argv = $_SERVER['argv'];
    
    $script = TestScript::newInstance();
    // Set the script in the state it would be in the configure phase.
    $script->init(Clip::PHASE_CONFIGURATION);
    
    // Set new version.
    $script->setVersionOption($script->setupOpt('a'));
    
    // Fake server argv for PHASE_ARG_INIT.
    $_SERVER['argv'][] = '-a';

    $script->init(Clip::PHASE_ARG_INIT);
    // Restore argv.

    $_SERVER['argv'] = $original_argv;
    $script->init(Clip::PHASE_EXECUTION);
    
  }
  
  public function testInit() {
    $original_argv = $_SERVER['argv'];
    
    $script = TestScript::newInstance();
    // Set the script in the state it would be in the configure phase.
    $script->init(Clip::PHASE_CONFIGURATION);
    
    // Some configuration.
    $script->setupOpt('a', ClipOption::OPTION_NO_VALUE);
    $script->setupOpt('b', ClipOption::OPTION_REQUIRED);
    $script->setupOpt('c', ClipOption::OPTION_OPTIONAL);
    
    // Fake server argv for PHASE_ARG_INIT.
    $_SERVER['argv'][] = '-a';
    $_SERVER['argv'][] = '-c';
    $_SERVER['argv'][] = 'value for c';
    $script->init(Clip::PHASE_ARG_INIT);
    // Restore argv.
    $_SERVER['argv'] = $original_argv;
    
    $script->init(Clip::PHASE_EXECUTION);
    
    $this->assertTrue($script->opt('a')->isGiven());
    $this->assertFalse($script->opt('b')->isGiven());
    $this->assertTrue($script->opt('c')->isGiven());
    
    $this->assertEquals(NULL, $script->opt('a')->getVAlue());
    $this->assertEquals('value for c', $script->opt('c')->getVAlue());
  }
}
?>