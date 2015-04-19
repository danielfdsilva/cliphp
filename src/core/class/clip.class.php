<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

/**
 * Cliphp Base Class
 * 
 * Main class for the Cliphp application.
 * Every script must extend this class in order to work.
 * Contains methods to setup the input arguments, set the command used to
 * show help and version and other utilities.
 * 
 * @package     Cliphp
 * @subpackage  Core 
 * @author      Daniel da Silva
 * 
 * @abstract
 */
abstract class Clip {

  /**
   * Initialization phases.
   * Bypass phase check and run everything.
   */
  const PHASE_ALL = 'all';

  /**
   * Initialization phases.
   * The default options are setup and the configure() function is called.
   */
  const PHASE_CONFIGURATION = 'configuration';

  /**
   * Initialization phases.
   * The arguments sent to the script are parsed, the clipOption's value
   * is set and validated. The script is marked as initialized.
   */
  const PHASE_ARG_INIT = 'arg_init';

  /**
   * Initialization phases.
   * It is checked if any of the default options was provided and if so,
   * the correspondent method is called.
   * The execute() function is called.
   */
  const PHASE_EXECUTION = 'execution';
  
  /**
   * @var Object Clip
   * Class instance.
   */
  protected static $INSTANCE = NULL;
  
  /**
   * @var boolean
   * Stores whether the initialization was run or not.
   * After the script was initialized some action are
   * no longer allowed.
   */
  private $initialized = FALSE;
  
  /**
   * @var array
   * Array of ClipOption objects.
   * Stores all the options for the current script.
   */
  private $options = array();
  
  /**
   * @var array
   * Input arguments parsed and cleaned.
   * This array's keys are the options and the value is an array
   * with all the arguments.
   * 
   * Example:
   *  -c arg1 arg2
   * 
   * [c] => array(
   *    [0] => arg1
   *    [1] => arg2 
   * )
   */
  private $args = array();
  
  /**
   * @var boolean
   * Whether or not to use the default help option.
   * If this is not disable the help command will be -h or --help.
   * Disabling this allows to set another command.
   */
  private $defaultHelp = TRUE;
  
  /**
   * @var Object ClipOption
   * Stores the help command.
   */
  private $helpClipOption = NULL;
  
  /**
   * @var Object ClipOption
   * Stores the default help command.
   * Can't be changed.
   */
  private $defaultHelpClipOption = NULL;
  
  /**
   * @var boolean
   * Whether or not to use the default version option.
   * If this is not disable the version command will be -v or --version.
   * Disabling this allows to set another command.
   */
  private $defaultVersion = TRUE;
  
  /**
   * @var Object ClipOption
   * Stores the version command.
   */
  private $versionClipOption = NULL;
  
  /**
   * @var Object ClipOption
   * Stores the default version command.
   * Can't be changed.
   */
  private $defaultVersionClipOption = NULL;
  
  /**
   * @var Objects Loader
   * Loader object.
   */
  private $loader = NULL;
  
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
   * Used to get context.
   * 
   * @return Clip object
   */
  public static function &getInstance() {
    if (self::$INSTANCE === NULL) {
      self::$INSTANCE = new static();
    }
    
    return self::$INSTANCE;
  }
  
  /**
   * Adds a new option to the stack.
   * 
   * @param $option
   *   The option.
   * 
   * @param $type
   *   The type of option to add. (OPTION_NO_VALUE | OPTION_OPTIONAL | OPTION_REQUIRED)
   * 
   * @throw Exception
   *   When the option is already registered.
   * 
   * @return ClipOption
   *   Return the created option to allow chaining.
   */
  public function setupOpt($option, $type = ClipOption::OPTION_NO_VALUE) {
    markAsConfigurationFunction();
    
    // Check if option already exists.
    if ($this->opt($option)) {
	   throw new ClipException("This option was already registered: $option.");
    }
    
    // Register new option.
    $opt = new ClipOption($option, $type);
    $this->options[] = $opt;
    // Return ClipOption object to allow chaining.
    return $opt;
  }
  
  /**
   * Searches the given option in the option stack.
   * 
   * @param $option
   *   The option to search.
   * 
   * @return ClipOption
   *   If the command is not found returns NULL. 
   */
  public function opt($option) {
    foreach ($this->options as $ClipOption) {
      if ($ClipOption->aliasExist($option)) {
        return $ClipOption;
      }
    }
    return NULL;
  }
  
  /**
   * Searches the given option in the option stack and removes it.
   * 
   * @param $option
   *   The option to search.
   * 
   */
  public function removeOpt($option) {
    foreach ($this->options as $index => $ClipOption) {
      if ($ClipOption->aliasExist($option)) {
        unset($ClipOption);
        unset($this->options[$index]);
      }
    }
  }
  
  /**
   * Disables the default help.
   * The default help command won't be created meaning that
   * to use the system help function a new help command must be set with
   * $this->setHelpOption()
   * 
   * Example:
   * $this->disableDefaultHelp()->setHelpOption(
   *   $this->setupOpt('hl')->describe("My help text.")
   * );
   * 
   * @return Clip
   *   Return the clip instance to allow chaining.
   */
  public function disableDefaultHelp() {
    markAsConfigurationFunction();
    
    if (!$this->defaultHelp) {
      // Default help was already disabled.
      return $this;
    }
    
    $this->defaultHelp = FALSE;
    $help_alias = $this->defaultHelpClipOption->getAliases();
    $this->removeOpt($help_alias[0]);
    //$this->defaultHelpClipOption = NULL;
    return $this;
  }
  
  /**
   * Sets a help command.
   * To use this the default help must be disabled.
   * $this->disableDefaultHelp()
   * 
   * Example:
   * $this->disableDefaultHelp()->setHelpOption(
   *   $this->setupOpt('hl')->describe("My help text.")
   * );
   * 
   * @return Clip
   *   Return the clip instance to allow chaining.
   */
  public function setHelpOption($ClipOption) {
    markAsConfigurationFunction();
    
    $this->disableDefaultHelp();
    $this->helpClipOption = $ClipOption;
    return $this;
  }
  
  /**
   * Returns the Help option in use.
   * 
   * @return ClipOption.
   */
  public function getHelpOption() {
    return $this->defaultHelp ? $this->defaultHelpClipOption : $this->helpClipOption;
  }
  
  /**
   * Disables the default version.
   * The default version command won't be created meaning that
   * to use the system version function a new version command must be set with
   * $this->setVersionOption()
   * 
   * Example:
   * $this->disableDefaultVersion()->setVersionOption(
   *   $this->setupOpt('ver')->describe("My own version.")
   * );
   * 
   * @return Clip
   *   Return the clip instance to allow chaining.
   */
  public function disableDefaultVersion() {
    markAsConfigurationFunction();
    
    if (!$this->defaultVersion) {
      // Default help was already disabled.
      return $this;
    }
    
    $this->defaultVersion = FALSE;
    $version_alias = $this->defaultVersionClipOption->getAliases();
    $this->removeOpt($version_alias[0]);
    //$this->defaultVersionClipOption = NULL;
    return $this;
  }
  
  /**
   * Sets a version command.
   * To use this the default version must be disabled.
   * $this->disableDefaultVersion()
   * 
   * Example:
   * $this->disableDefaultVersion()->setVersionOption(
   *   $this->setupOpt('ver')->describe("My own version.")
   * );
   * 
   * @return Clip
   *   Return the clip instance to allow chaining.
   */
  public function setVersionOption($ClipOption) {
    markAsConfigurationFunction();
    
    $this->disableDefaultVersion();
    $this->versionClipOption = $ClipOption;
    return $this;
  }
  
  /**
   * Returns the Version option in use.
   * 
   * @return ClipOption.
   */
  public function getVersionOption() {
    return $this->defaultVersion ? $this->defaultVersionClipOption : $this->versionClipOption;
  }
  
  /**
   * Returns loader to allow the user of loader methods.
   */
  public function load() {
    // Only setup loader if is going to be needed.
    if ($this->loader == NULL) {
      // Setup Loader.
      $this->loader =& ClipLoader::getInstance();
    }
    
    return $this->loader;
  }

  /**
   * Initializes the script.
   * Parses the input arguments and sets up needed vars.
   * 
   * When is finished calls the execute() function.
   *
   * @param string $phase
   *   The phase param allows to restrict what's initialized.
   *   The initialization goes through the following phases:
   *   - configuration
   *     The default options are setup and the configure() function is called.
   *   - arg_init
   *     The arguments sent to the script are parsed, the clipOption's value
   *     is set and validated. The script is marked as initialized.
   *   - execution
   *     It is checked if any of the default options was provided and if so,
   *     the correspondent method is called.
   *     The execute() function is called.
   */
  public function init($phase = Clip::PHASE_ALL) {
    if ($phase == Clip::PHASE_CONFIGURATION || $phase == Clip::PHASE_ALL) {
      // Sets up the default help command.
      $this->defaultHelpClipOption = $this->setupOpt('h')->alias('help')->describe("Shows the help text.");
      
      // Sets up the default version command.
      $this->defaultVersionClipOption = $this->setupOpt('v')->alias('version')->describe("Shows the script version.");
      
      // Runs the configure created by the user.
      $this->configure();
    }
    
    if ($phase == Clip::PHASE_ARG_INIT || $phase == Clip::PHASE_ALL) {
      $this->args = ArgumentParser::parse($_SERVER['argv']);
      // Check if used options were declared.
      foreach ($this->args as $key => $value) {
        $ClipOption = $this->opt($key);
        if (!$ClipOption) {
          // Kill.
          throw new ClipUseException("Invalid option: $key");
        }
        
        $ClipOption->setUsedAlias($key);
        
        if ($ClipOption->isRequired() && empty($value)) {
          // Kill.
          throw new ClipUseException("Option requires an argument: $key");
        }
        
        $ClipOption->setValue($value);
      }
      
      // Set the script as initialized.
      $this->setInitialized();
    }

    if ($phase == Clip::PHASE_EXECUTION || $phase == Clip::PHASE_ALL) {
      // Default options to check before running the user's code.
      // Help function.
      if (($this->defaultHelp && $this->defaultHelpClipOption->isGiven()) || ($this->helpClipOption != NULL && $this->helpClipOption->isGiven())) {
        $this->help();
        // Finish.
        finish('PHASE_EXECUTION Help function.');
      }
      
      // Version function.
      if (($this->defaultVersion && $this->defaultVersionClipOption->isGiven()) || ($this->versionClipOption != NULL && $this->versionClipOption->isGiven())) {
        $this->version();
        // Finish.
        finish('PHASE_EXECUTION Version function.');
      }
      //End default options.

      // Ready. Execute code.
  	  $this->execute();
    }
  }

  /**
   * Returns whether the script was initialized or not.
   * 
   * @return boolean.
   */
  public function isInitialized() {
    return $this->initialized;
  }

  /**
   * Sets the script as initialized.
   * 
   * @return self.
   */
  public function setInitialized() {
    $this->initialized = TRUE;
    return $this;
  }
  
  /**
   * Returns the script version
   * 
   * @return String 
   *   Script version or NULL if not defined.
   */
  public function getVersion() {
    $c = get_class($this);
    return defined($c . "::SCRIPT_VERSION") ? $c::SCRIPT_VERSION : NULL;
  }
  
  /**
   * Returns the class name of the script in use.
   * 
   * @return String 
   *   Script name
   */
  public function getName() {
    return get_class($this);
  }

  /**
   * Configure function.
   * To be implemented in the script.
   * Used to setup options and other settings.
   */
  protected abstract function configure();
  
  /**
   * Function called after initialization.
   * This represents the body of the script.
   */
  protected abstract function execute();
  
  /**
   * System help.
   * Prints the options description in a readable way.
   * 
   * After the help is printed the script exits.
   */
  public function help() {
    $space = 8;
    // Compute the longest option (counting all aliases).
    // Needed to show proper tabulation.
    $longest = 0;
    foreach ($this->options as $ClipOption) {
      $len = strlen($ClipOption->getHelp('option'));
      if ($len > $longest) {
        $longest = $len;
      }
    }
    
    pl("(args...) -> Required args");
    pl("[args...] -> Optional args");
    pl("Options available:");
    // Print commands.
    foreach ($this->options as $ClipOption) {
      $len = strlen($ClipOption->getHelp('option'));
      // Needed space between option and description.
      $needed_space = $longest - $len + $space;
      pl( '  ' . $ClipOption->getHelp('option') . str_repeat(' ', $needed_space) . $ClipOption->getHelp() );
    }
  }
  
  /**
   * Returns the help ClipOption, either the default one or the user's custom.
   */
  public function getHelpClipOption() {
    return $this->defaultHelp ? $this->defaultHelpClipOption : $this->helpClipOption;
  }
  
  /**
   * System version.
   * Prints the script version
   * 
   * After the version is printed the script exits.
   */
  public function version() {
    $version = $this->getVersion();
    if ($version) {
      pl(get_class($this) . ' ' . $version);
    }
    else {
      pl('Cliphp ' . CLIPHP_VERSION);
    }
  }

}

/**
 * Cliphp Exception Class
 * 
 * Main exception class for the Cliphp application.
 * 
 * @package     Cliphp
 * @subpackage  Core 
 * @author      Daniel da Silva
 * 
 */
class ClipException extends Exception { }

/**
 * ClipUseException
 * 
 * This class is used to handle errors and present them to the user.
 * The errors thrown by this class are always captured and the message
 * is shown to the user.
 * For example when a value is missing for an option which value is required.
 * 
 * @package     Cliphp
 * @subpackage  Core 
 * @author      Daniel da Silva
 * 
 */
class ClipUseException extends ClipException { }
