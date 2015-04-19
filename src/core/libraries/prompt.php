<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

/**
 * Prompt library
 * 
 * Prompts the user for a value and evaluates it.
 * 
 * @package     Cliphp
 * @subpackage  Core/Libraries
 * @author      Daniel da Silva
 * 
 */
class Prompt extends ClipLibrary {
  /**
   * Asks the user for confirmation.
   * You can use one of the default options or provide our own.
   * The user will be asked for a value until a valid one is provided.
   * 
   * Default: 
   *  yn, Yn, yesno, YesNo
   * 
   * Custom option:
   *  custom options can be added with an array.
   *  The value for true must be the first option. Ex:
   *  array('Ok', 'Not Ok')
   * 
   * @param mixed $options
   * 
   * @param $question
   *   The question to ask the user. Defaults to "Are you sure?"
   * 
   * @return boolean
   */
  public function confirm($options, $question = "Are you sure?"){
    // The FALSE value must be on the first index (0)
    // When converting indexes to booleans it will map correctly.
    $defaults = array(
      'yn' => array('n', 'y'),
      'Yn' => array('n', 'Y'),
      'yesno' => array('no', 'yes'),
      'YesNo' => array('No', 'Yes')
    );
    $bool_map = array();
    
    if (is_array($options)) {
      if (count($options) != 2 || !isset($options[0]) || !isset($options[1])) {
        throw new ClipLibraryException("Invalid custom confirm options provided for confirm prompt.");
      }
      $bool_map = array($options[1], $options[0]);
    }
    elseif (array_key_exists($options, $defaults)) {
      $bool_map = $defaults[$options];
    }
    else {
      throw new ClipLibraryException("Invalid options provided for confirm prompt.");
    }
    
    do {
      $result = static::readValue($question . sprintf(" (%s/%s): ", $bool_map[1], $bool_map[0]));
    } while (!in_array($result, $bool_map, TRUE));
    
    // Convert the key to boolean.
    return (bool) array_search($result, $bool_map);
  }
  
  /**
   * Presents the user several options to choose from.
   * Options must be defined as an array. Can be associative, numeric or mixed.
   * If the user chooses an invalid option he/she will be asked again.
   * 
   * Example:
   *  array('zero', 'one', 'two', 't' => 'tree', 't2' => 'another tree')
   * 
   * Result:
   *  [0] => zero
   *  [1] => one
   *  [2] => two
   *  [t] => tree
   *  [t2] => another tree
   * 
   * @param array $options
   * 
   * @return mixed
   *  The key of the option the user chose.
   */
  public function option($options) {
    if (!is_array($options)) {
      throw new ClipLibraryException("Invalid options.");
    }
    
    foreach ($options as $key => $value) {
      pl(sprintf('[%s] => %s', $key, $value));
    }
    
    pl();
    
    while (TRUE) {
      $result = static::readValue();
      if (!array_key_exists($result, $options)) {
        pl('Invalid option.');
      }
      else {
        break;
      }
    }
    
    return $result;
  }
  
  /**
   * Prompts the user for text input.
   * By default empty values are allowed.
   * 
   * @param boolean $NULL
   *  Whether or not to allow empty texts.
   * 
   * @return string
   *  User entered text
   */
  public function text($NULL = TRUE) {
    do {
      $result = static::readValue();
      if ($NULL) break;
    } while(trim($result) == '');

    return $result;
  }
  
  /**
   * Expects a number to be in a range or interval.
   * 
   * Example [3,10].
   * By default only integers are allowed. If you want to allow float
   * values, set the range to a float number:
   * Example [3,10.0]. (Setting only one number to float will suffice)
   * More about ranges: www.mathsisfun.com/sets/intervals.html
   * 
   * @param string $range
   * 
   * @return mixed 
   *  User entered value.
   */
  public function range($range){
    if (preg_match("/^(\\[|\\()(-?[0-9]+(\.?[0-9]+)?),(-?[0-9]+(\.?[0-9]+)?)(\\)|\\])$/", $range, $matches)) {
      // Matches
      // [0] => [-10.0,3)   -> Range
      // [1] => [           -> Lower bound inclusion/exclusion
      // [2] => -10.0       -> Lower bound value
      // [3] => .0          -> Lower bound decimal value
      // [4] => 3           -> Upper bound value
      // [5] =>             -> Upper bound decimal value
      // [6] => )           -> Upper bound inclusion/exclusion
      
      if ($matches[2] >= $matches[4]) {
        throw new ClipLibraryException("Range Error: Lower bound must be lower than upper bound.");
      }
      
      $range_parts = $matches;
      
      while (TRUE) {
        
        $result = static::readNumericValue();
        if ($result === FALSE) { continue; }
        
        list(, $lbound_in_ex, $lbound, , $ubound, , $ubound_in_ex) = $range_parts;
        // Convert upper and lower bound to number by adding 0.
        $lbound +=0;
        $ubound +=0;
        
        // If the value has to be integer and it is float fail.
        // The type is determined by the bounds.
        if (is_int($lbound) && is_int($ubound) && is_float($result)) {
          pl('Value not in range.');
          continue;
        }
        
        // Include lower bound.
        if ($lbound_in_ex == "[" && $result < $lbound) {
          pl('Value not in range.');
          continue;
        }
        
        // Exclude lower bound.
        if ($lbound_in_ex == "(" && $result <= $lbound) {
          pl('Value not in range.');
          continue;
        }
        
        // Include upper bound.
        if ($ubound_in_ex == "]" && $result > $ubound) {
          pl('Value not in range.');
          continue;
        }
        
        // Exclude upper bound.
        if ($ubound_in_ex == ")" && $result >= $ubound) {
          pl('Value not in range.');
          continue;
        }
        
        return $result;
      }
    }
    else {
      throw new ClipLibraryException("Range Error: Malformed range.");
    }
  }
  
  /**
   * Prompts a value that has to be greater than the specified value.
   * 
   * @param int $value
   * 
   * @return number
   */
  public function gt($value) {
    while (TRUE) {
      $result = static::readNumericValue();
      if ($result === FALSE) { continue; }
      
      if (is_int($value) && is_float($result)) { pl('Invalid value.'); continue; }
      
      if ($result <= $value) { pl('Invalid value.'); continue; }
      break;
    }
    return $result;
  }
  
  /**
   * Prompts a value that has to be greater than or equal to
   * the specified value.
   * 
   * @param int $value
   * 
   * @return number
   */
  public function gte($value) {
    while (TRUE) {
      $result = static::readNumericValue();
      if ($result === FALSE) { continue; }
      
      if (is_int($value) && is_float($result)) { pl('Invalid value.'); continue; }
      
      if ($result < $value) { pl('Invalid value.'); continue; }
      break;
    }
    return $result;
  }
  
  /**
   * Prompts a value that has to be less than the specified value.
   * 
   * @param int $value
   * 
   * @return number
   */
  public function lt($value) {
    while (TRUE) {
      $result = static::readNumericValue();
      if ($result === FALSE) { continue; }
      
      if (is_int($value) && is_float($result)) { pl('Invalid value.'); continue; }
            
      if ($result >= $value) { pl('Invalid value.'); continue; }
      break;
    }
    return $result;
  }
  
  /**
   * Prompts a value that has to be less than or equal to
   * the specified value.
   * 
   * @param int $value
   * 
   * @return number
   */
  public function lte($value) {
    while (TRUE) {
      $result = static::readNumericValue();
      if ($result === FALSE) { continue; }
      
      if (is_int($value) && is_float($result)) { pl('Invalid value.'); continue; }
      
      if ($result > $value) { pl('Invalid value.'); continue; }
      break;
    }
    return $result;
  }
  
  /**
   * Prompts a number. Can be integer of float.
   * 
   * @return number
   */
  public function number() {
    while (TRUE) {
      $result = static::readNumericValue();
      if ($result === FALSE) { continue; }
	  
      break;
    }
    return $result;
  }
  
  /**
   * Prompts a float
   * 
   * @return number
   */
  public function float() {
    while (TRUE) {
      $result = static::readNumericValue();
      if ($result === FALSE) { continue; }
      
      if (!is_float($result)) { pl('Invalid value.'); continue; }
      break;
    }
    return $result;
  }
  
  /**
   * Prompts a integer.
   * 
   * @return number
   */
  public function int() {
    while (TRUE) {
      $result = static::readNumericValue();
      if ($result === FALSE) { continue; }
      
      if (!is_numeric($result) || is_float($result)) { pl('Invalid value.'); continue; }
      break;
    }
    return $result;
  }
  
  /**
   * Reads a numeric value.
   * If the value is not numeric an error will be printed and
   * FALSE will be returned.
   * 
   * @return mixed
   *   Read number or FALSE.
   */
  static function readNumericValue($prompt = '> ') {
    $result = static::readValue($prompt);
  	if (is_numeric($result)) {
  	  return $result + 0;
  	}
  	else {
  	  pl('Invalid value.');
  	  return FALSE;
  	}
  }
  
  /**
   * Reads a value from command line.
   * OS agnostic.
   * 
   * @return mixed
   *   Read value.
   */
  static function readValue($prompt = '> ') {
  	if (is_windows()) {
      p($prompt);
      $result = fgets(STDIN);
    } else {
      $result = readline($prompt);
    }
  	return trim($result);
  }
}
?>