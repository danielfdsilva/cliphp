<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

/**
 * Cliphp ClipOption Class
 * 
 * This class handles setup, validation and aliasing of the 
 * input commands.
 * 
 * @package     Cliphp
 * @subpackage  Core
 * @author      Daniel da Silva
 * 
 */
class ClipOption {
  
  // Type of options.
  const OPTION_NO_VALUE = 0;
  const OPTION_REQUIRED = 1;
  const OPTION_OPTIONAL = 2;
  
  // Type of expectations.
  const EXPECT_CUSTOM = 'custom';
  const EXPECT_INT = 'int';
  const EXPECT_FLOAT = 'float';
  const EXPECT_NUMBER = 'number';
  const EXPECT_IN_RANGE = 'in_range';
  const EXPECT_DISCRETE = 'discrete';
  const EXPECT_GT = 'gt';
  const EXPECT_GTE = 'gte';
  const EXPECT_LT = 'lt';
  const EXPECT_LTE = 'lte';
  
  /**
   * @var int
   * Type of option
   */
  private $type = NULL;
  
  /**
   * @var mixed
   * Value of the option.
   * When set the option's value will always be an array, even if
   * only has one value.
   */
  private $value = NULL;
  
  /**
   * @var array
   * Aliases through which this option can be accessed
   */
  private $aliases = array();
  
  /**
   * @var boolean
   * Flag to store whether or not the option was used
   * when calling the script.
   */
  private $set = FALSE;
  
  /**
   * @var string
   * Option description.
   */
  private $description = '';
  
  /**
   * @var string
   * Stores the alias used to call this option.
   */
  private $called_as = NULL;
  
  /**
   * @var string
   * Expectation.
   */
  private $expectation = NULL;
  
  /**
   * @var function
   * Function to validate the parameters.
   */
  private $validate_function = NULL;
  
  /**
   * @var array
   * Options sent through the expect function.
   */
  private $validate_options = array();
  
  /**
   * Constructor function
   * @param String $option
   * @param int $type
   */
  function __construct($option, $type) {
    if (!self::isValidOption($option)) {
      throw new ClipOptionException("Invalid option: $option. Only alphanumeric characters.");
    }
    
    $this->aliases[] = $option;
    $this->type = $type;
  }
  
  /**
   * Adds an alias to this option.
   * @param String $option
   * 
   * @throw Exception
   *   When the alias already exists.
   * 
   * @return ClipOption
   *   Returns the object to allow chaining.
   */
  public function alias($option) {
    markAsConfigurationFunction();
    
    if (!self::isValidOption($option)) {
      throw new ClipOptionException("Invalid alias: $option. Only alphanumeric characters.");
    }
    
    if ($this->aliasExist($option)) {
      throw new ClipOptionException("This alias already exists: $option.");
    }
    
    $this->aliases[] = $option;
    return $this;
  }
  
  /**
   * Sets the method description.
   * Used for help text.
   * @param String $description
   *   The method description
   * 
   * @return ClipOption
   *   Returns the object to allow chaining.
   */
  public function describe($description) {
    markAsConfigurationFunction();
    
    $this->description = $description;
    return $this;
  }
  
  /**
   * Sets a default value for the parameter.
   * @param array|String $default
   * 
   * @throw Exception
   *   When the option type is not optional.
   *   It only makes sense to add a default value
   *   for an option whose argument is optional.
   * 
   * @return ClipOption
   *   Returns the object to allow chaining.
   */
  public function defaultValue($default) {
    markAsConfigurationFunction();
    
    if ($this->isFlag()) {
      throw new ClipOptionException("There's no point setting a default value for a option of type OPTION_NO_VALUE.");
    }
    
    $default = arrayfy($default);
    $this->value = $default;
    
    return $this;
  }
  
  /**
   * Allows the setting of an expectation for the parameter value.
   * 
   * @param string $expectation
   *   Use class constants:
   *   ClipOption::EXPECT_CUSTOM
   *     Allows setting a custom function to validate the arguments.
   *     To set the function use the $custom_validate;
   *     The arguments will be sent to the function one at a time.
   *
   *     There will be cases when you have more than one argument
   *     for your option. For example:
   *     --bounds 10 20
   *     In this case, instead of validating one argument at a time
   *     you may want to check them against each other.
   *     Setting "#multi" => TRUE in the options array will tell
   *     the system to send all the values to your function in array format.
   *     You will be responsible to print the error messages.
   * 
   *     In either case the function must always return a boolean value. 
   *     
   *   ClipOption::EXPECT_INT
   *     Expects an int value.
   * 
   *   ClipOption::EXPECT_FLOAT
   *     Expects a float value.
   * 
   *   ClipOption::EXPECT_NUMBER
   *     Expects a number.
   * 
   *   ClipOption::EXPECT_DISCRETE
   *     Expects the value to be one of the specified in the options array:
   *     "#options" => array('monday', 'sunday')
   * 
   *   ClipOption::EXPECT_IN_RANGE
   *     Expects a number to be in a range or interval.
   *     Example [3,10].
   *     By default only integers are allowed. If you want to allow float
   *     values, set the range to a float number:
   *     Example [3,10.0]. (Setting only one number to float will suffice)
   *     More about ranges: www.mathsisfun.com/sets/intervals.html
   * 
   *   ClipOption::EXPECT_GT
   *     Expect a number greater than the one specified in the options array:
   *     "#value" => 10
   *     By default only integers are allowed. If you want to allow float
   *     values, set the #value to a float number:
   *     "#value" => 10.0
   * 
   *   ClipOption::EXPECT_GTE
   *     Expect a number greater than or equal to the one specified
   *     in the options array:
   *     "#value" => 10
   *     By default only integers are allowed. If you want to allow float
   *     values, set the #value to a float number:
   *     "#value" => 10.0
   * 
   *   ClipOption::EXPECT_LT
   *     Expect a number less than the one specified in the options array:
   *     "#value" => 10
   *     By default only integers are allowed. If you want to allow float
   *     values, set the #value to a float number:
   *     "#value" => 10.0
   * 
   *   ClipOption::EXPECT_LTE
   *     Expect a number less than or equal to the one specified
   *     in the options array:
   *     "#value" => 10
   *     By default only integers are allowed. If you want to allow float
   *     values, set the #value to a float number:
   *     "#value" => 10.0
   * 
   * 
   * @param array $options
   *  The options used will depend on the $expectation param.
   * 
   * @param function $custom_validate
   *   Function to use for the ClipOption::EXPECT_CUSTOM.
   * 
   * @throws Exception
   *   When parameters are not correct.
   * 
   * @return ClipOption
   *   Returns the object to allow chaining.
   */
  public function expect($expectation, $options = array(), $custom_validate = NULL) {
    markAsConfigurationFunction();
    $this->validate_options = $options;
    $this->expectation = $expectation;

    switch ($this->expectation) {
      case ClipOption::EXPECT_CUSTOM:
        
        if (!is_callable($custom_validate)) {
          throw new ClipOptionExpectException("Invalid custom validate function.");
        }
        $this->validate_function = $custom_validate;
        
      break;
       
      case ClipOption::EXPECT_INT:
        $this->validate_function = "is_int";
      break;
       
      case ClipOption::EXPECT_FLOAT:
        $this->validate_function = "is_float";
      break;
       
      case ClipOption::EXPECT_NUMBER:
        $this->validate_function = "is_numeric";
      break;
       
      case ClipOption::EXPECT_DISCRETE:
        // options array:
        // "#options" => array('morning', 'afternoon', 'evening')
        if (isset($this->validate_options['#options']) && is_array($this->validate_options['#options'])) {
          $this->validate_function = function ($value) {
            return in_array($value, $this->validate_options['#options']);
          };
        }
        else {
          throw new ClipOptionExpectException("Discrete Error: Invalid discrete options.");
        }
      break;
       
      case ClipOption::EXPECT_IN_RANGE:
        // options array:
        // "#range" => [2,10[
        if (isset($this->validate_options['#range'])) {
          if (preg_match("/^(\\[|\\()(-?[0-9]+(\.?[0-9]+)?),(-?[0-9]+(\.?[0-9]+)?)(\\)|\\])$/", $this->validate_options['#range'], $matches)) {
            // Matches
            // [0] => [-10.0,3)   -> Range
            // [1] => [           -> Lower bound inclusion/exclusion
            // [2] => -10.0       -> Lower bound value
            // [3] => .0          -> Lower bound decimal value
            // [4] => 3           -> Upper bound value
            // [5] =>             -> Upper bound decimal value
            // [6] => )           -> Upper bound inclusion/exclusion
            
            if ($matches[2] >= $matches[4]) {
              throw new ClipOptionExpectException("Range Error: Lower bound must be lower than upper bound.");
            }
            // Store range parts.
            $this->validate_options['range_parts'] = $matches;
            
            
            // Build validate function.
            $this->validate_function = function ($value) {
              
              // The value has to be numeric.
              if (!is_numeric($value)) {
                return FALSE;
              }
              
              list(, $lbound_in_ex, $lbound, , $ubound, , $ubound_in_ex) = $this->validate_options['range_parts'];
              // Convert upper and lower bound to number by adding 0.
              $lbound +=0;
              $ubound +=0;
              
              // If the value has to be integer and it is float fail.
              // The type is determined by the bounds.
              if (is_int($lbound) && is_int($ubound) && is_float($value)) {
                return FALSE;
              }
              
              // Include lower bound.
              if ($lbound_in_ex == "[" && $value < $lbound) {
                return FALSE;
              }
              
              // Exclude lower bound.
              if ($lbound_in_ex == "(" && $value <= $lbound) {
                return FALSE;
              }
              
              // Include upper bound.
              if ($ubound_in_ex == "]" && $value > $ubound) {
                return FALSE;
              }
              
              // Exclude upper bound.
              if ($ubound_in_ex == ")" && $value >= $ubound) {
                return FALSE;
              }
              
              return TRUE;
            };
            
          }
          else {
            throw new ClipOptionExpectException("Range Error: Malformed range.");
          }
        }
        else {
          throw new ClipOptionExpectException("Range Error: Range missing from option.");
        }
      break;
       
      case ClipOption::EXPECT_GT:
        // options array:
        // "#value" => 12
        if (isset($this->validate_options['#value']) && is_numeric($this->validate_options['#value'])) {
          
          $this->validate_function = function ($value) {
            // Has to be numeric.
            if (!is_numeric($value)) { return FALSE; }
            // If the option is an integer let's force an integer value.
            // If the user wants to allow floats needs to
            // use something like 10.0
            if (is_int($this->validate_options['#value']) && is_float($value)) { return FALSE; }
            
            if ($value <= $this->validate_options['#value']) { return FALSE; }
            return TRUE;
          };
          
        }
        else {
          throw new ClipOptionExpectException("GT Error: Invalid value.");
        }
      break;
       
      case ClipOption::EXPECT_GTE:
        // options array:
        // "#value" => 12
        if (isset($this->validate_options['#value']) && is_numeric($this->validate_options['#value'])) {
          
          $this->validate_function = function ($value) {
            // Has to be numeric.
            if (!is_numeric($value)) { return FALSE; }
            // If the option is an integer let's force an integer value.
            // If the user wants to allow floats needs to
            // use something like 10.0
            if (is_int($this->validate_options['#value']) && is_float($value)) { return FALSE; }
            
            if ($value < $this->validate_options['#value']) { return FALSE; }
            return TRUE;
          };
          
        }
        else {
          throw new ClipOptionExpectException("GTE Error: Invalid value.");
        }
      break;
       
      case ClipOption::EXPECT_LT:
        // options array:
        // "#value" => 12
        if (isset($this->validate_options['#value']) && is_numeric($this->validate_options['#value'])) {
          
          $this->validate_function = function ($value) {
            // Has to be numeric.
            if (!is_numeric($value)) { return FALSE; }
            // If the option is an integer let's force an integer value.
            // If the user wants to allow floats needs to
            // use something like 10.0
            if (is_int($this->validate_options['#value']) && is_float($value)) { return FALSE; }
            
            if ($value >= $this->validate_options['#value']) { return FALSE; }
            return TRUE;
          };
          
        }
        else {
          throw new ClipOptionExpectException("LT Error: Invalid value.");
        }
      break;
       
      case ClipOption::EXPECT_LTE:
        // options array:
        // "#value" => 12
        if (isset($this->validate_options['#value']) && is_numeric($this->validate_options['#value'])) {
          
          $this->validate_function = function ($value) {
            // Has to be numeric.
            if (!is_numeric($value)) { return FALSE; }
            // If the option is an integer let's force an integer value.
            // If the user wants to allow floats needs to
            // use something like 10.0
            if (is_int($this->validate_options['#value']) && is_float($value)) { return FALSE; }
            
            if ($value > $this->validate_options['#value']) { return FALSE; }
            return TRUE;
          };
          
        }
        else {
          throw new ClipOptionExpectException("LTE Error: Invalid value.");
        }
      break;
     
      default:
        throw new ClipOptionExpectException("Invalid expectation.");
      break;
    }
    
    return $this;
  }
  
  /**
   * Checks if the given option is valid.
   * Valid options are made only from alphanumeric characters
   * and dashes, in case of long options.
   * 
   * @param String $opt
   *
   * @return Boolean
   */
  public static function isValidOption($opt) {
    if (strlen($opt) == 1) {
      return (bool) preg_match('/^[a-zA-Z0-9]$/', $opt);
    }
    else {
      return (bool) preg_match('/^[a-zA-Z0-9]{2,}((\-[a-zA-Z0-9]{2,})?)+$/', $opt);
    }
  }
  
  /**
   * Checks if the given alias exists.
   * 
   * @param string $option
   *   Alias to check.
   */
  public function aliasExist($option) {
    return in_array($option, $this->aliases);
  }
  
  /**
   * Sets the value of this option.
   * It will only be set if the option's type is not "no value"
   * 
   * @param array $val
   */
  public function setValue($val) {
    $val = arrayfy($val);
    
    $this->set = TRUE;
    // If this option has no value, do not set it.
    if ($this->type != ClipOption::OPTION_NO_VALUE && !empty($val)) {
      
      // Do we need to validate the value?
      if ($this->validate_function != NULL) {
        $func = $this->validate_function;
        
        if (isset($this->validate_options['#multi']) && $this->validate_options['#multi']) {
          if (!$func($val)) {
            $err_msg = 'Custom validation function failed.';
            $help = getContext()->getHelpClipOption();
            if ($help != NULL) {
              $err_msg .= sprintf("\nUse %s to see help.", $help->getHelp('option'));
            }
            // Kill.
            throw new ClipUseException($err_msg);
          }
        }
        else {
          // Pass each value to the validation function.
          foreach ($val as $index => $value) {
            if (!$func($value)) {
              $err_msg = sprintf("Invalid value. Option: %s Value: %s.", $this->called_as, $value);
              $help = getContext()->getHelpClipOption();
              if ($help != NULL) {
                $err_msg .= sprintf("\nUse %s to see help.", $help->getHelp('option'));
              }
              // Kill.
              throw new ClipUseException($err_msg);
            }
          }
        }
      }
      
      $this->value = $val;
    }
    
    return $this;
  }
  
  /**
   * Used to set alias used to call this option.
   * Only meant to be used by the system.
   * 
   * @param $alias
   *   Used alias
   */
  public function setUsedAlias($alias) {
    $this->called_as = $alias;
    return $this;
  }
  
  /**
   * Returns a value at a given index.
   * By default returns the first value.
   * 
   * To get all values use getMultiValue() instead.
   * 
   * @param int $index
   *    The value's index.
   * 
   * @return mixed
   *    Required value. If the value is not present NULL will be returned
   */
  public function getValue($index = 0) {
    if (isset($this->value[$index])) {
      return $this->value[$index];
    }
    return NULL;
  }
  
  /**
   * Returns all values for this option.
   * To get only a specific value use getValue() instead.
   * 
   * @return array
   */
  public function getMultiValue() {
    return $this->value;
  }
  
  /**
   * Returns all the aliases for this option.
   * 
   * @return array
   */
  public function getAliases() {
    return $this->aliases;
  }
  
  /**
   * Returns the help for this option.
   * By default returns the description.
   * 
   * Setting the $for param to "option" will return
   * a string with all the aliases comma separated.
   * 
   * @param string $for
   *    What to get back. (description | option);
   * 
   * @return string
   */
  public function getHelp($for = 'description') {
    switch ($for) {
      case 'option':
        
        // Prepare options for printing.
        // Add - to small options
        // Add -- to long options
        // Arg arg information if needed.
        // Using an array is easier to add commas later.
        $options = array();
        foreach ($this->aliases as $alias) {
          $options[] = strlen($alias) == 1 ? '-' . $alias : '--' . $alias;
        }
        
        // Flatten the array.
        $options = implode(',', $options);
        
        if ($this->isRequired()) {
          $options .= ' (args...)';
        }
        elseif ($this->isOptional()) {
          $options .= ' [args...]';
        }
        return $options;
        break;
      
      default:
        return $this->description;
        break;
    }
  }
  
  /**
   * Returns whether or not this option was specified
   * when the script was called.
   * 
   * @return boolean.
   */
  public function isGiven() {
    return $this->set;
  }
  
  /**
   * Returns whether or not this option was not specified
   * when the script was called.
   * Opposite of isGiven()
   *
   * @return boolean.
   */
  public function notGiven() {
    return !$this->isGiven();
  }

  /**
   * Returns whether or not this option's value is required.
   * 
   * @return boolean.
   */
  public function isRequired() {
    return $this->type === ClipOption::OPTION_REQUIRED;
  }
  
  /**
   * Returns whether or not this option's value is optional.
   * 
   * @return boolean.
   */
  public function isOptional() {
    return $this->type === ClipOption::OPTION_OPTIONAL;
  }
  
  /**
   * Returns whether or not this option is of no value type, a.k.a flag.
   * 
   * @return boolean.
   */
  public function isFlag() {
    return $this->type === ClipOption::OPTION_NO_VALUE;
  }
}


/**
 * ClipOptionException
 * 
 * Exception for ClipOptions.
 * 
 * @package     Cliphp
 * @subpackage  Core 
 * @author      Daniel da Silva
 * 
 */
class ClipOptionException extends ClipException { }

/**
 * ClipOptionExpectException
 * 
 * Exception for ClipOption's expectations.
 * 
 * @package     Cliphp
 * @subpackage  Core 
 * @author      Daniel da Silva
 * 
 */
class ClipOptionExpectException extends ClipOptionException { }
