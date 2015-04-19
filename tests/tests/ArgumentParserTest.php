<?php

class ArgumentParserTest extends PHPUnit_Framework_TestCase {
  
  public function testParser() {
      $input_args = array(
        'filename',
        'orphan element',
        '-a',
          'value',
          '-10',
          '-z1',
        '-b',
        '--help',
          '----weirdval',
          'another',
          '-def-g',
        '--with-dash'
      );
      
      $expected = array(
        'a' => array('value', -10, '-z1'),
        'b' => '',
        'help' => array('----weirdval', 'another', '-def-g'),
        'with-dash' => ''
      );

      $this->expectOutputString("Orphan argument: orphan element. Every argument must be part of an option.\n");
      $this->assertEquals($expected, ArgumentParser::parse($input_args));
  }
    
  public function testLongOptions() {
    $this->assertTrue(ArgumentParser::isLongOpt('--ab'));
    $this->assertTrue(ArgumentParser::isLongOpt('--abc'));
    $this->assertTrue(ArgumentParser::isLongOpt('--no-cont-rib'));
    $this->assertTrue(ArgumentParser::isLongOpt('--no-contrib'));
    $this->assertTrue(ArgumentParser::isLongOpt('--a1'));
    
    $this->assertFalse(ArgumentParser::isLongOpt('--99-9999'));
    $this->assertFalse(ArgumentParser::isLongOpt('--ab-a'));
    $this->assertFalse(ArgumentParser::isLongOpt('--ab-'));
    $this->assertFalse(ArgumentParser::isLongOpt('--ab--'));
    $this->assertFalse(ArgumentParser::isLongOpt('---no-contrib'));
    $this->assertFalse(ArgumentParser::isLongOpt('---no--contrib'));
    $this->assertFalse(ArgumentParser::isLongOpt('--n-o-co-o'));
    $this->assertFalse(ArgumentParser::isLongOpt('--no-contrib-'));
  }
}
?>