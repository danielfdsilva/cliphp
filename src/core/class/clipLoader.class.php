<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

/**
 * Cliphp Loader Class
 * 
 * Class to load Clip libraries.
 *   Libraries can be found inside:
 *     core/libraries/
 *     core/libraries/[lib_name]/
 *     libraries/
 *     libraries/[lib_name]/
 * 
 * @package     Cliphp
 * @subpackage  Core
 * @author      Daniel da Silva
 * 
 */
class ClipLoader {
  
  /**
   * @var Object Loader
   * Class instance.
   */
  private static $INSTANCE = NULL;
  
  /**
   * @var object Clip
   *   App context.
   *   Instance of Clip.
   */
  private $context = NULL;
  
  /**
   * Protected constructor to prevent creating a new instance of the
   * *Singleton* via the new operator from outside of this class.
   */
  private function __construct() { }

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
    
    if (self::$INSTANCE === NULL) {
      self::$INSTANCE = new static();
      self::$INSTANCE->context =& getContext();
    }
    
    return self::$INSTANCE;
  }
  
  /**
   * Loads library.
   * The loader will give priority to user libraries, meaning that users
   * can override system libraries by creating one with the same name.
   * 
   * If the library was already loaded, the loader will quietly return FALSE.
   * 
   * @param String $name
   *   The library to load.
   * 
   * @return boolean
   *   True if the library was loaded, FALSE otherwise.
   * 
   * @throw Exception
   *   If the library doesn't exist.
   */
  public function library($name) {
    markAsConfigurationFunction();
    
    // Check if the library was already loaded.
    if (isset($this->context->{$name})) {
      // Library already loaded.
      // Ignoring.
      return FALSE;
    }
    
    // Check the user libraries.
    // User libraries override system ones.
    
    // User library inside folder.
    if (file_exists(APP_DIR . "/libraries/{$name}/{$name}.php")) {
      require APP_DIR . "/libraries/{$name}/{$name}.php";
    }
    // User library in root folder.
    elseif (file_exists(APP_DIR . "/libraries/{$name}.php")){
      require APP_DIR . "/libraries/{$name}.php";
    }
    // Core library inside folder.
    elseif (file_exists(APP_DIR . "/core/libraries/{$name}/{$name}.php")){
      require APP_DIR . "/core/libraries/{$name}/{$name}.php";
    }
    // Core library in root folder.
    elseif (file_exists(APP_DIR . "/core/libraries/{$name}.php")){
      require APP_DIR . "/core/libraries/{$name}.php";
    }
    else {
      // Library was not found.
      throw new ClipLoaderException("Library not found: $name.");
    }
    
    // The class name starts with uppercase
    // while every file starts with lowercase.
    $class_name = ucfirst($name);
    $this->context->{$name} = $class_name::getInstance();
    return TRUE;
  }

}

/**
 * ClipLoaderException
 * 
 * Exception for ClipLoader.
 * 
 * @package     Cliphp
 * @subpackage  Core 
 * @author      Daniel da Silva
 * 
 */
class ClipLoaderException extends ClipException { }
