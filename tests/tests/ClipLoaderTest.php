<?php

class ClipLoaderTest extends PHPUnit_Framework_TestCase {
  
  /**
   * @expectedException ClipException
   * @expectedExceptionMessage The script was already configured. This command is not allowed outside configure().
   */
  public function testExceptionLoadAfterInitialization() {
    $script = TestScript::newInstance();
    $script->setInitialized();
    
    $loader = $script->load();
  }
  
  public function testLoaderType() {
    $script = TestScript::newInstance();
    // Set the script in the state it would be in the configure phase.
    $script->init(Clip::PHASE_CONFIGURATION);
    
    $loader = $script->load();
    
    $this->assertInstanceOf('ClipLoader', $loader);
  }
  
  /**
   * @expectedException ClipLoaderException
   * @expectedExceptionMessage Library not found: non_existent.
   */
  public function testLoadNonExistentLibrary() {
    $script = TestScript::newInstance();
    // Set the script in the state it would be in the configure phase.
    $script->init(Clip::PHASE_CONFIGURATION);
    
    $script->load()->library('non_existent');
  }
  
  public function testLoadLibrary() {
    $script = TestScript::newInstance();
    // Set the script in the state it would be in the configure phase.
    $script->init(Clip::PHASE_CONFIGURATION);
    
    $this->assertTrue($script->load()->library('testLib'));
    
    // Load again the same.
    // If already loaded will return FALSE and fails silently.
    $this->assertFalse($script->load()->library('testLib'));

    // Check that the library was actually loaded.
    $this->assertInstanceOf('TestLib', $script->testLib);
    $this->assertTrue($script->testLib->getTrue());
  }
}
?>