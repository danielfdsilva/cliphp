<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

class Progress extends ClipLibrary{
  
  // Types of progress available.
  const PERCENT = 1;
  const BAR = 2;
  const BAR_RATE_ITEMS = 3;
  const BAR_RATE_SECONDS = 4;
  
  /**
   * Type of progress.
   * Default to Progress::PERCENT
   */
  private $type = Progress::PERCENT;
  
  /**
   * Total number of items.
   */
  private $total = NULL;
  
  /**
   * Number of items done
   */
  private $done = 0;
  
  /**
   * Last printed line.
   * For performance reasons the progress is only printed
   * if different than last one.
   */
  private $last_printed = NULL;
  
  /**
   * Data to be printed.
   */
  private $current_printed = NULL;
  
  /**
   * Number of times information was printed.
   */ 
  private $num_prints = 0;
  
  /**
   * Lock to prevent changing data after the progress started.
   */
  private $locked = FALSE;
  
  /**
   * Status of the progress. Finished or not.
   */
  private $finished = FALSE;
  
  /**
   * Time of the first update.
   */
  private $time_start = NULL;
  
  /**
   * Time of the last update.
   */
  private $time_end = NULL;
  
  /**
   * Dumps the status of the Progress
   * 
   * @return string
   */
  function __toString() {
    $dump = "Total time (sec):\t" . $this->getTotalTime() . "\n";
    $dump .= "Total items:\t\t" . $this->total . "\n";
    $dump .= "Rate (items/sec):\t" . $this->getItemsPerSecond() . "\n";
    $dump .= "Rate (secs/item):\t" . $this->getSecondsPerItem() . "\n";
    $dump .= "Number prints:\t\t" . $this->getNumPrints() . "\n";
    
    return $dump;
  }
  
  /**
   * Sets the type of Progress.
   * Available types:
   * Progress::BAR, Progress::PERCENT, Progress::BAR_RATE_ITEMS, Progress::BAR_RATE_SECONDS
   * 
   * @param int $type
   * 
   * @return Progress
   *   Returns object to allow chaining.
   * 
   * @throw Exception
   *   If the trying to change type after the Progress started.
   *   If trying to set an invalid Progress type.
   */
  public function type($type){
   if ($this->locked) {
     throw new ClipLibraryException("After the progress is updated, the settings can't be changed.");
   }
   
   if (!in_array($type, array(Progress::BAR, Progress::PERCENT, Progress::BAR_RATE_ITEMS, Progress::BAR_RATE_SECONDS))) {
     throw new ClipLibraryException("Invalid Progress type.");
   }
   
   $this->type =  $type;
   return $this;
  }
  
  /**
   * Sets the total for the progress.
   * 
   * @param int $total
   * 
   * @return Progress
   *   Returns object to allow chaining.
   * 
   * @throw Exception
   *   If the trying to change type after the Progress started.
   */
  public function total($total) {
    if ($this->locked) {
      throw new ClipLibraryException("After the progress is updated, the settings can't be changed.");
    }
    
    if ($total < 1) {
      throw new ClipLibraryException("Progress total can't be lower than 1.");
    }
   
    $this->total = $total;
    return $this;
  }
  
  /**
   * Returns the total amount of time the progress took to complete.
   * If the progress is not finished yet, will return the elapsed time.
   * 
   * @return float
   */
  public function getTotalTime() {
    if ($this->time_start == NULL) {
      // No update was made.
      return 0;
    }
    elseif ($this->time_end == NULL) {
      // Elapsed time.
    	return microtime(TRUE) - $this->time_start;
    }
    else {
      // Total time.
      return $this->time_end - $this->time_start;
    }
  }
  
  /**
   * Returns the amount of items processed per second.
   * 
   * @return float
   */
  public function getItemsPerSecond() {
    // x items per second.
    return $this->getTotalTime() == 0 ? $this->done : $this->done / $this->getTotalTime();
  }
  
  /**
   * Returns the amount of seconds needed to process one item.
   * 
   * @return float
   */
  public function getSecondsPerItem() {
    // x seconds per item.
    return $this->done == 0 ? 0 : $this->getTotalTime() / $this->done;
  }
  
  /**
   * Return the number of times progress was printed.
   * 
   * @return int
   */
  public function getNumPrints() {
    return $this->num_prints;
  }
  
  /**
   * Resets the progress to allow a new usage.
   * 
   * @return Progress
   *   Returns object to allow chaining.
   */
  public function reset() {
    $this->locked = FALSE;
    $this->done = 0;
    $this->total = NULL;
    $this->last_printed = NULL;
    $this->current_printed = NULL;
    $this->num_prints = 0;
    $this->finished = FALSE;
    $this->time_start = NULL;
    $this->time_end = NULL;    
    
    return $this;
  }
  
  /**
   * Computes the progress based on the type.
   * By default, and for performance reasons the progress is only printed
   * if there were any changes.
   * 
   * @param int $value
   *   The new progress value.
   * @param boolean $force_update
   *   Forces the printing of the progress even if there are no changes.
   *   Has a huge impact on performance.
   *   Default to FALSE.
   */
  public function update($value, $force_update = FALSE) {
    if ($this->total === NULL) {
      throw new ClipLibraryException("Progress total value was not set.");
    }
    
    // After the first update lock the progress.
    if (!$this->locked) {
      $this->locked = TRUE;
      $this->time_start = microtime(TRUE);
    }

    // Finished. Do nothing.
    if ($this->finished) {
      return FALSE;
    }
    
    // Total value fail safe.
    if ($value >= $this->total) {
      $this->done = $this->total;
      // Set the progress to finish but still allow one last execution.
      $this->finished = TRUE;
    }
    else {
      // Set the current value.
      $this->done = $value;
    }
    
    // Render depending on the type.
    switch ($this->type) {
      case Progress::PERCENT:
        $this->computePercent();
        break;
      case Progress::BAR:
        $this->computeBar();
        break;
      case Progress::BAR_RATE_ITEMS:
        $this->computeBarRateItems();
        break;
      case Progress::BAR_RATE_SECONDS:
        $this->computeBarRateSeconds();
        break;
    }
    
    // Print only if something changed.
    // Printing takes a huge impact on performance, so print the
    // least amount of times possible.
    if ($this->current_printed != $this->last_printed || $force_update) {
      // Only print \r if is not the first line.
      if ($this->last_printed !== NULL) {
        p("\r");
      }
      
      p($this->current_printed);
      // Store the number of prints just because.
      $this->num_prints++;
      
      // If the string to be printed is smaller then the one printed
      // before, add spaces.
      $length_diff = strlen($this->last_printed) - strlen($this->current_printed);
      if ($length_diff > 0) {
        p(str_repeat(" ", $length_diff));
      }
      
      // Store the last printed value.
      $this->last_printed = $this->current_printed;
    }
    
    // This means it's the last iteration.
    if ($this->finished) {
      // Print a new line.
      pl();
      $this->time_end = microtime(TRUE);
    }    
  }
  
  /**
   * Compose the PERCENT progress type.
   * Sets the current_printed value.
   */
  private function computePercent() {
    $this->current_printed = $this->drawPercent();
  }
  
  /**
   * Compose the BAR progress type.
   * Sets the current_printed value.
   */
  private function computeBar() {
    $this->current_printed = $this->drawBar() . " " . $this->drawPercent();
  }
  
  /**
   * Compose the BAR_RATE_ITEMS progress type.
   * Sets the current_printed value.
   */
  private function computeBarRateItems() {
    $this->current_printed = $this->drawBar() . " " . $this->drawPercent() . "    " . round($this->getItemsPerSecond()) . " items per second";
  }
  
  /**
   * Compose the BAR_RATE_SECONDS progress type.
   * Sets the current_printed value.
   */
  private function computeBarRateSeconds() {
    $this->current_printed = $this->drawBar() . " " . $this->drawPercent() . "    " . round($this->getSecondsPerItem(), 2) . " seconds per item";
  }
  
  /**
   * Calc percentage.
   * 
   * @param int $precision
   *   Rounding precision.
   * 
   * @return string
   *   Percent value: 12.34 %
   */
  private function drawPercent($precision = 2) {
    return round($this->done / $this->total * 100, 2) . ' %';
  }
  
  /**
   * Prepares a progress bar.
   * 
   * @param int $size
   *   Bar size
   * 
   * @return string
   *   Bar: [======>     ]
   */
  private function drawBar($size = 45) {
    $perc = (double)($this->done / $this->total);
    $bar = floor($perc * $size);
    
    $status_bar = "[";
    $status_bar .= str_repeat("=", $bar);
    if ($bar < $size) {
        $status_bar .= ">";
        $status_bar .= str_repeat(" ", $size - $bar);
    } else {
        $status_bar .= "=";
    }
    $status_bar .= "]";
    
    return $status_bar;
  }
}

?>