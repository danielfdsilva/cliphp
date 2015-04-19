<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

/**
 * Test Library to check the clipLoader.
 * Doesn't do anything. Is meant to be used during tests.
 * 
 * @package     Cliphp
 * @subpackage  Test
 * @author      Daniel da Silva
 * 
 */
class TestLib extends ClipLibrary {
  
  /**
   * Simple test function.
   * Returns TRUE.
   * 
   * @retun Boolean
   */
  public function getTrue() {
    return TRUE;
  }
  
}