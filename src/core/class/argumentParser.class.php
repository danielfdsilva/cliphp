<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

/**
 * Cliphp Argument Parser Class
 * 
 * Static class containing method to parse and validate the input arguments.
 * 
 * 
 * @package     Cliphp
 * @subpackage  Core
 * @author      Daniel da Silva
 * 
 * @static
 */
class ArgumentParser {
  
  /**
   * Parses the input arguments and removes orphan arguments:
   * 
   * #Input:
   * Array (
   *   [0] => orphan
   *   [1] => --bounds
   *   [2] => -10
   *   [3] => 10
   *   [4] => -r
   * )
   * 
   * #Output
   * Array (
   *   ['bounds'] => array (-10, 10)
   *   ['r']
   * )
   * 
   * @param string $option
   *   The option to validate
   * 
   * @return array
   *   The parsed arguments.
   */
  public static function parse($args) {
    // First arg is the file name. Remove it.
    array_shift($args);
    // Parse input arguments manually.
    // getopt() function is not good enough.
    $input_args = array();
    $option_found = FALSE;
    foreach ($args as $arg) {
      // Actual arguments that do not start with - or -- will only be allowed 
      // as a param of an option (like -n name).
      // If the first arguments are like this, just discard them and warn the user.
      if (!$option_found && !self::isSmallOptGroup($arg) && !self::isSmallOpt($arg) && !self::isLongOpt($arg)) {
        pl("Orphan argument: $arg. Every argument must be part of an option.");
      }
      else {
        $option_found = TRUE;
        
        // Expand small options from -abc to -a -b -c
        if (self::isSmallOptGroup($arg)) {
          $arg_pieces = array_slice(str_split($arg), 1);
          
          foreach ($arg_pieces as $arg_piece) {
            $input_args[$arg_piece] = '';
          }
        }
        elseif (self::isSmallOpt($arg)) {
          $input_args[substr($arg, 1)] = '';
        }
        elseif (self::isLongOpt($arg)) {
          $input_args[substr($arg, 2)] = '';
        }
        else {
          end($input_args);
          $key = key($input_args);
          
          if (!is_array($input_args[$key])) {
            $input_args[$key] = array();
          }
          
          // If the argument is a numeric string, convert it to number
          // by adding a 0.
          if (is_numeric($arg)) {
           $arg +=0; 
          }
          
          $input_args[$key][] = $arg;
        }
      }
    }
    
    // After all this cleaning lets reset the pointer.
    reset($input_args);

    return $input_args;
  }
  
  /**
   * Checks if an option is a smallOpt like -a
   * 
   * @param string $option
   *   The option to validate
   * 
   * @return boolean
   *   Whether it validates or not.
   */
  private static function isSmallOpt($opt) {
    return (bool) preg_match("/^\-[a-zA-Z]$/", $opt);
  }
  
  /**
   * Checks if an option is a smallOptGroup like -abc
   * 
   * @param string $option
   *   The option to validate
   * 
   * @return boolean
   *   Whether it validates or not.
   */
  private static function isSmallOptGroup($opt) {
    return (bool) preg_match("/^\-[a-zA-Z]{2,}$/", $opt);
  }
  
  /**
   * Checks if an option is a longOpt like --abc
   * 
   * @param string $option
   *   The option to validate
   * 
   * @return boolean
   *   Whether it validates or not.
   */
  public static function isLongOpt($opt) {
    return (bool) preg_match("/^\-(\-[a-zA-Z][a-zA-Z0-9]+)+$/", $opt);
  }

}
