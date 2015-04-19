<?php
/**
 * @file builder.php
 * Compiler for Cliphp.
 * 
 * @version 1.0.0
 * @author Daniel da Silva
 */

$pharName = (isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'cliphp') . '.phar';
$srcRoot = "src";
$buildRoot = "build";
  
$phar = new Phar("$buildRoot/$pharName", FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, $pharName);
//$phar = $phar->convertToExecutable(Phar::ZIP);

// Include everything in the source directory.
$phar->buildFromDirectory($srcRoot);


// Set index file manually to set APP_PATH.
$phar["index.php"] = <<<EOF
<?php
define('APP_DIR', 'phar://{$pharName}');
define('ENVIRONMENT', 'production');
require 'core/bootstrap.php';
?>
EOF;
$phar->setStub($phar->createDefaultStub("index.php"));

?>