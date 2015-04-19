# Cliphp

> Cliphp is still under development. According to the needs of the project drastic changes might occur.
Be carefull if you are updating your code.

Cliphp stands for *Command Line Interface for PHP* and is a framework to build scripts with PHP 5.3+

Current version: **0.1-beta** ([release notes](changelog.md))

## Quick example
Create a file ```script.php``` in the ```scripts``` directory.



```php
<?php
// The class name must be CamelCase and have the same name as the file.
class Script extends Clip {
  
  // Script version. Although not mandatory it useful to keep
  // track of versions.  
  const SCRIPT_VERSION = '0.0.1';
  
  // Configuration function. Mandatory.
  // This function will be used to set up the script by defining what options are allowed, loading libraries etc.
  // The option definition can't be done outside this function.
  protected function configure() {
  	$this->setupOpt('n', ClipOption::OPTION_REQUIRED);
  }
  
  // This is the actual body of the script.
  // All the logic belonging to the script will be placed here.
  protected function execute() {
  	// Check if option was provided.
    if ($this->opt('n')->isGiven()) {
	  // Get option value.
      $name = $this->opt('n')->getValue();
	  pl('Welcome ' . $name);
    }
	else {
      pl('Example complete');
	}
  }
}

?>
```
Now run the script with:
```
$ php src/index.php
// Example complete

$ php src/index.php -n Daniel
// Welcome Daniel
```

You can find all the documentation [here](docs/Readme.md) and some [examples](src/examples/) as well.

## Contribution
You are free to contribute to the project. If you find a bug and/or have a nice idea about a feature feel free to open an issue or submit your own solution. I'll be more than happy to hear your suggestions. :)

## Testing
The framework testing is done using phpunit. To run the tests you just need to run ```phpunit``` in the root folder.

##License
Cliphp is licensed under **The MIT License (MIT)**, see the [LICENSE](LICENSE) file for more details.