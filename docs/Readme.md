# Cliphp
Cliphp stands for *Command Line Interface for PHP* and is meant to be used as a framework to build scripts. It's very fast to setup and pack for easy distribution.

## Table of Contents
- <a href="#getting-started">Getting started</a>
- <a href="#options">Options</a>
	- <a href="#executing-a-script-with-options">Executing a script with options</a>
	- <a href="#grouping-options">Grouping options</a>
	- <a href="#orphan-parameters">Orphan parameters</a>
	- <a href="#alias">Alias</a>
	- <a href="#description">Description</a>
	- <a href="#default-value">Default value</a>
	- <a href="#expect">Expect</a>
		- <a href="#number">Number</a>
		- <a href="#integer">Integer</a>
		- <a href="#float">Float</a>
		- <a href="#discrete">Discrete</a>
		- <a href="#greater-than">Greater than</a>
		- <a href="#greater-than-or-equals-to">Greater than or equals to</a>
		- <a href="#less-than">Less than</a>
		- <a href="#less-than-or-equals-to">Less than or equals to</a>
		- <a href="#in-range">In range</a>
		- <a href="#custom">Custom</a>
- <a href="#default-options">Default options</a>
	- <a href="#replace-default-options">Replace default options</a>
	- <a href="#disable-default-options">Disable default options</a>
- <a href="#configuration">Configuration</a>
- <a href="#packaging-distribution">Packaging & Distribution</a>
- <a href="#libraries">Libraries</a>
	- <a href="#using-libraries">Using libraries</a>
		- <a href="#prompt">Prompt</a>
		- <a href="#progress">Progress</a>
	- <a href="#creating-libraries-advanced">Creating libraries (Advanced)</a>
- <a href="#examples">Examples</a>

## Getting started

Create a file ```script.php``` in the ```scripts``` directory. The file name can only have alphanumeric characters and underscores and must match the class name except that it must start with a lowercase character.


The script must extend the ```Clip``` class:

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
  protected function configure() { }
  
  // This is the actual body of the script.
  // All the logic belonging to the script will be placed here.
  protected function execute() {
    pl('Getting started complete');
  }
}

?>
```
Now run the script with:
```
$ php src/index.php
```

>**Suggestion:** To keep your scripts and code organized you can create a sub folder and put your script along with any needed files inside it. Just name it the same as your script. So for a script called ```clone.php``` the folder structure would be ```scripts/clone/clone.php```  

>**Info:** In case you need to include additional files to use in your script, you are free to do so. Be sure to use ```APP_DIR``` since it will give you the proper path to the ```src/``` folder. For a file ```myFunc.php``` in the root of ```src/``` the include would be ```include APP_DIR . '/myFunc.php';``` Keep in mind that ```APP_DIR``` doesn't have a trailing slash.

-----

## Options
A script argument, or option (as they're known in Cliphp) allows you to interact with the script. They are setup during the configuration phase (inside ```configure()```)

Options can be long, like ```version```, or short, like ```v```. To setup an option you have to specify its type and depending on whether the value is mandatory or not, can assume 3 different types:
- Options without value or flags (```ClipOption::OPTION_NO_VALUE```)
- Options that require a value. This doesn't mean that the option is required, but if it's used, a value is mandatory (```ClipOption::OPTION_REQUIRED```)
- Options with an optional value meaning that they can be used without providing a value. A default value can be provided for these cases. (```ClipOption::OPTION_OPTIONAL```)

```php
$opt = $this->setupOpt('r', ClipOption::OPTION_OPTIONAL);
```

If you need to modify an already existing option you can get a reference to it by using:
```php
$opt = $this->opt('r');
```
> Note: After setting up an option, its type can't be changed.

#### Executing a script with options

Although long options and short options are set up exactly the same way, when executing the script they are used in different ways:
```
$ php scr/index.php -v
```
```
$ php scr/index.php --version
```

#### Grouping options
When using short options it is possible to group them all together, but there are some caveats:
```
$ php scr/index.php -abc
```
is the same as
```
$ php scr/index.php -a -b -c
```
However if you're passing some value to a group it will only be set for the last option which might lead to some undesirable behavior. Therefore grouping options is advised only when using options with no value (or flags).
```
$ php scr/index.php -abc 3
```
is the same as
```
$ php scr/index.php -a -b -c 3
```

#### Orphan parameters
All the parameters passed to a script must be in the form of an option. Therefore the following is not allowed:
```
$ php scr/index.php value -a
```
In this case ```value``` will be flagged as an orphan argument and a warning is shown.

### Alias
Aliases can be setup allowing multiple options to have the same behavior:
```php
$opt = $this->setupOpt('r', ClipOption::OPTION_OPTIONAL)
  ->alias('repeat');
```

### Description
The description is useful to keep track of what each option does and it will show up when the help is printed (more on that later):
```php
$opt = $this->setupOpt('r', ClipOption::OPTION_OPTIONAL)
  ->alias('repeat')
  ->describe('Word to repeat');
```

### Default value
All the options, except ```OPTION_NO_VALUE``` can have a default value. 
```php
$opt = $this->setupOpt('r', ClipOption::OPTION_OPTIONAL)
  ->alias('repeat')
  ->describe('Word to repeat')
  ->defaultValue('Hello');
```

### Expect
Expectations allow you to validate the option´s value. There are several expectiations that can be used:

#### Number
Expects the option's value to be a number.
```php
$opt->expect(ClipOption::EXPECT_NUMBER);
```

#### Integer
Expects the option's value to be an integer.
```php
$opt->expect(ClipOption::EXPECT_INT);
```

#### Float
Expects the option's value to be a float.
```php
$opt->expect(ClipOption::EXPECT_FLOAT);
```

#### Discrete
Expects the value to be one of the specified in the options array.
```php
$opt->expect(ClipOption::EXPECT_DISCRETE, array('#options' => array('apple', 'banana', 'kiwi')));
```

#### Greater than
Expects the numeric value to be greater than the provided one. If the value is provided as an integer, only integers will be allowed.  
For example: to allow float values greater than 10 you'd use:
```php
$opt->expect(ClipOption::EXPECT_GT, array('#value' => 10.0));
```

#### Greater than or equals to
The same rules as Greater than apply but you'd use:
```php
$opt->expect(ClipOption::EXPECT_GTE, array('#value' => 10.0));
```

#### Less than
The same rules as Greater than apply but you'd use:
```php
$opt->expect(ClipOption::EXPECT_LT, array('#value' => 10.0));
```

#### Less than or equals to
The same rules as Greater than apply but you'd use:
```php
$opt->expect(ClipOption::EXPECT_LTE, array('#value' => 10.0));
```

#### In range
Expects the number to be within the given interval.  
Example: The interval of all the numbers between 1 and 6, with 6 included would be defined as ```(1,6]```. Parenthesis exclude numbers whereas square brackets include them.  
A much nicer explanation about intervals can be found at [mathsisfun](http://www.mathsisfun.com/sets/intervals.html).  
If you want to allow float numbers in the interval you just need to set one as float:
```php
$opt->expect(ClipOption::EXPECT_IN_RANGE, array('#range' => '(1,10.0]'));
```

#### Custom
It is also possible to specify custom validation for the option's value. This function is specified as the third parameter for ```expect()``` and must return either ```TRUE``` or ```FALSE```.  The option's values will be passed to the function one by one and if one fails the execution will be stopped.
```php
$opt->expect(ClipOption::EXPECT_CUSTOM, array(), function($value) { return $value % 2 == 0 ? TRUE : FALSE; });
```
With custom validation it is possible to get all the values simultaneously.  
Important: When using ```#multi``` the system doesn't print any error message, that's up to you.
```php
$opt->expect(ClipOption::EXPECT_CUSTOM, array('#multi' => TRUE), function($values) {
  if (count($values) == 3) {
    return TRUE;
  }
  else {
    pl('You must specify exactly 3 values.');
    return FALSE;
  }
```

-----

## Default Options
Cliphp comes with two pre set options:  

```-v``` or ```--version```
Shows the version of the script. This version is set with the constant ```SCRIPT_VERSION```. If this constant is not set the cliphp version is shown instead.
By default the version option executes a function which can be overridden by your script. Then it's up to you to show whatever version information you want.
```php
/**
 * Override default version function.
 */
public function version() {
  pl('Version IV');
}
```

```-h``` or ```--help```
Shows information about the options defined in the script. This information comes from the ```describe()``` method of an option. By default has the following format:
```
(args...) -> Required args
[args...] -> Optional args
Options available:
  -h,--help                    Shows the help text.
  -v,--version,                Shows the script version.
  -c (args...)                 Character to repeat.
  -n,--number (args...)        Number of times to repeat.
```
Like the ```version``` option the output can be changed by overriding the ```help()``` function.
```php
/**
 * Override default help function.
 */
public function help() {
  // Don't do this :)
  pl('No help for you.');
}
```

These options behave like all the other and can be customized in the same way. For example:
```php
// Add new alias and a different help text to the v option.
$this->opt('v')
->describe('See the version of the script.')
->alias('ver');
```

### Replace default options
If the default option used to show the help is not of your liking you can easily replace it with another:
```php
// Create option.
$new_help = $this->setupOpt('myhelp', ClipOption::OPTION_NO_VALUE)
  ->describe('Show help');

// Set the option. This will still trigger the help() function when used.
$this->setHelpOption($new_help);

// Version option.
// The same can be done for the version option, just need to use a different method:
$this->setVersionOption($new_version);
```

### Disable default options
The default options can also be disabled completely:
```php
// Disable default help option.
$this->disableDefaultHelp();

// Disable default version option.
$this->disableDefaultVersion();

```

-----

## Configuration
**@config.php**
```php
// This options controls the default file name.
// By default the program will search for a file called script.php inside the scripts directory.
$config['default_script_name'] = 'script';

// By default cliphp only allows one script file but it is possible to have multiple.
// In that case, the first parameter passed to the program when executing must be 
// the script name. If the specified script is not found the default is loaded.
$config['multi_script'] = FALSE;
```

```
$ php src/index.php scriptName [options]
```

## Packaging & Distribution
Although you can distribute your script as is, it is much better to compile it into a nice phar archive. It will be executed in the exact same way but the users will only need to worry about one file.

By default phar creation is disabled by php. You need to edit the ```php.ini``` file and set:
```
phar.readonly = 0
```

Once this is done just run ```builder.php``` and specify the name you want for your archive.
```
php builder.php myScript
```
You script will be compiled and placed in the ```dist``` directory.

-----

## Libraries
Libraries provide additional functionalities for your scripts. Right now Cliphp ships with two built in libraries:
- Prompt
- Progress

### Using libraries
Libraries must be loaded in the configure function with the load method.
```php
protected function configure() {
  $this->load()->library('library_name');
  
  // Once the library is loaded it is available as a class property
  // with the same name as the one used to load.
  $this->library_name->hello();
}
```

#### Prompt
The prompt library allows the user to interact with the script during runtime and provide values.

**Confirm**
Asks the user for confirmation with a sentence like:
```
Are you sure? (y/n)
```
In this case the user would have to enter either ```y``` or ```n```. Any other option would not be accepted and the same question would be presented again.  
You can change the available options using one of the defaults:
- ```yn``` = ```(y/n)```
- ```Yn``` = ```(Y/n)```
- ```yesno``` = ```(yes/no)```
- ```YesNo``` = ```(Yes/No)```

```php
$val = $this->prompt->confirm('yesno');
```

or you can provide your own options using an array where the first parameter is the value for No and the second the value for Yes. (The values will be switched when presenting the options):
```php
$val = $this->prompt->confirm(array('nay', 'yay'));
```
```
Are you sure? (yay/nay)
```
If the default question does not suit your needs you can always pass a new one with the sencond parametet:
```php
$val = $this->prompt->confirm('yn', 'Do you want to quit?');
```
```
Do you want to quit? (y/n)
```

**Text**
Prompts the user for a text. By default no null values are allowed. To allow them just call the method with ```TRUE```
```php
$val = $this->prompt->text();
```

**Options**
Provides the user with a series of options from where he can pick one. If the user selects an invalid option an error message will be printed and he will be prompted again.
The options are configured with a key value array:
```php
$options = array(
  'A' => 'Apple',
  'P' => 'Pear',
  'B' => 'Banana'
);
$val = $this->prompt->option(options);
```
```
[A] => Apple
[P] => Pear
[B] => Banana
```

**Range**
Prompts the user for a number in a given interval. Works in the same fashion as the "expect in range".
Example: The interval of all the numbers between 1 and 6, with 6 included would be defined as ```(1,6]```. Parenthesis exclude numbers whereas square brackets include them.  
A much nicer explanation about intervals can be found at [mathsisfun](http://www.mathsisfun.com/sets/intervals.html).  
If you want to allow float numbers in the interval you just need to set one as float:
```php
$val = $this->prompt->range('(1,6.0]');
```
>**Note:** No message is presented to the user indicating what the interval is. That task will fall upon you.

**Number**
Prompts the user for a number.
```php
$val = $this->prompt->number();
```
**Float**
Prompts the user for a floating number.
```php
$val = $this->prompt->float();
```
**Integer**
Prompts the user for an integer number.
```php
$val = $this->prompt->int();
```
**Lt, Lte, Gt, Gte**
Less than, Less than or equals to, Greater than, Greater than or equals to. Yup, you guessed what it does.
```php
$val = $this->prompt->gt(10);
$val2 = $this->prompt->gte(10);
$val3 = $this->prompt->lt(10);
$val4 = $this->prompt->lte(10);
```

#### Progress
The progress library allows you to show a progress bar to the user.
After loading the library you only need to specify the type and the total value:
```php
$this->progress->type(Progress::PERCENT)->total(1000);
```	
Then use the update method to set the current value:
```php
$this->progress->update(500);
```	
This will result in a progress bar filled to 50%:
```
[======================>                       ] 50 %
```

> **Warning:** To ensure that the progress bar works correctly you should not print anything while there's a progress bar being shown.

There are 4 types of progress available:
- ``` Progress:BAR ``` - Presents a progress bar to the user:
```
[======================>                       ] 50 %
```
- ``` Progress:BAR_RATE_ITEMS ``` - Presents a progress bar to the user showing how many items are processed per second:
```
[======================>                       ] 50 %   10 items per second
```
- ``` Progress:BAR_RATE_SECONDS ``` - Presents a progress bar to the user showing how seconds each item takes to be processed:
```
[======================>                       ] 50 %   0.5 seconds per item
```
- ``` Progress:PERCENT ``` - Presents the progress in percentage format:
```
50 %
```

> **Warning:** Printing information to the console has a huge impact on performance. The library takes this into account and is optimized to only print information when there's a visual change. However the types ```Progress::BAR_RATE_SECONDS``` and ```Progress::BAR_RATE_ITEMS``` change quite often because of the rates so use them with caution.

Only a progress bar can be used at a time. If you need to use it again just reset it and start over:
```php
$this->progress->reset();
$this->progress->type(Progress::PERCENT)->total(1500);
```

**Other methods**
- ```getTotalTime()```
**Return** *float*
Returns the total amount of time, in milliseconds, the progress took to complete. If the progress is not finished yet, will return the elapsed time.

- ```getItemsPerSecond()```
**Return** *int*
Returns the amount of items processed per second.

- ```getSecondsPerItem()```
**Return** *float*
Returns the amount of seconds needed to process one item.

- ```getNumPrints()```
**Return** *int*
Returns the number of times the information was updated on the console.


### Creating libraries (Advanced)
Creating a library is quite easy. In the ```libraries``` folder create a new folder and a file with the name of your library. The name, like the scripts must start with a lowercase letter.
```
src/
|
|-- libraries/
  |
  |-- myLibrary/
    |
	|-- myLibrary.php
	|-- (...)
```

Your library class name must be the same as the file but start with uppercase ans extend ```ClipLibrary```

```php
<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

/**
 * MyLibrary library
 * 
 * Does all sort of stuff.
 * 
 * @package     Cliphp
 * @subpackage  Libraries
 * @version     1.0.0
 * @author      John Doe
 * 
 */
class MyLibrary extends ClipLibrary {
  
  /**
   * Prints a message.
   */
  public function sayHello() {
    pl('Hello from MyLibrary');
  }
}
```
Once is created you can load and access it like any other library.
```php
// Only the class name starts with uppercase. Every other reference
// will always be lowercase.
$this->load()->library('myLibrary');

// Access method.
$this->myLibrary->sayHello();
```
>**Important** When a library is loaded an instance of the library is created and that instance is used across the script. Libraries use a single instance pattern and it's not possible, nor should you allow, to create new instances.

If you need to configure the library once it loads do not use the ```__construct()``` method. Simply override ```onLoad()``` and add your configuration.
```php
  protected function onLoad() {
    pl('MyLibrary was loaded. Now we can begin!');	
  }
```
You can also extend existing libraries to add your own methods.  
As an example let's extend the ```Prompt``` library:
```php
<?php defined('APP_DIR') OR exit("No direct script access allowed\n");

// If you're extending a class, you need to make sure it is loaded.
// getContext() returns an instance of your script and all methods
// will be available.
getContext()->load()->library('prompt');

/**
 * BetterPrompt library
 * 
 * Extends the Prompt library adding some useful methods. 
 * 
 * @package     Cliphp
 * @subpackage  Libraries
 * @version     1.0.0
 * @author      John Doe
 * 
 */
class BetterPrompt extends Prompt {

  /**
   * Prompts the user for his/her name and says Hello.
   */
  public function sayHello() {
    pl("What's your name?");
	// Use text() method from Prompt.
    $name = $this->text();
    pl("Hello $name");
  }

}
```

## Examples
You can find examples for everything in this guide in ```src/examples```
