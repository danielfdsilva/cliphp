<?php

// Store the working directory.
define('APP_DIR', dirname(__FILE__));

if (!defined('ENVIRONMENT')) {
  define('ENVIRONMENT', 'development');
}

// Load bootstrap and here we go...
require 'core/bootstrap.php';
