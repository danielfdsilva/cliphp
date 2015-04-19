<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

/**
 * Cliphp Library Base Class
 * 
 * Base class to use when creating libraries for Cliphp.
 * 
 * @package     Cliphp
 * @subpackage  Core
 * @author      Daniel da Silva
 * 
 * @abstract
 */
abstract class ClipLibrary {
  
  /**
   * Instances of ClipLibrary.
   */
  protected static $INSTANCES = array();

  /**
   * Protected constructor to prevent creating a new instance of the
   * *Singleton* via the new operator from outside of this class.
   */
  private function __construct() {
    markAsConfigurationFunction();
    $this->onLoad();
  }

  /**
   * Private clone method to prevent cloning of the instance of the
   * *Singleton* instance.
   *
   * @return void
   */
  private function __clone() { }

  /**
   * Returns the class instance.
   * 
   * @return Loader object
   */
  public static function &getInstance() {
    markAsConfigurationFunction();
    
    $calledClass = get_called_class();
    
    if (!isset(self::$INSTANCES[$calledClass])) {
      self::$INSTANCES[$calledClass] = new static();
    }
    
    return self::$INSTANCES[$calledClass];
  }

  /**
   * Allows additional configuration of the library.
   */
  protected function onLoad() { }
}

/**
 * ClipLibraryException
 * 
 * Exception for ClipLibrary.
 * 
 * @package     Cliphp
 * @subpackage  Core 
 * @author      Daniel da Silva
 * 
 */
class ClipLibraryException extends ClipException { }
