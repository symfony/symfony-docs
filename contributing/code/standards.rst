Coding Standards
================

When contributing code to Symfony2, you must follow its coding standards. To
make a long story short, here is the golden rule: *Imitate the existing
Symfony2 code*.

Structure
---------

* Never use short tags (`<?`);

* Don't end class files with the usual `?>` closing tag;

* Indentation is done by steps of four spaces (tabs are never allowed);

* Use the linefeed character (`0x0A`) to end lines;

* Add a single space after each comma delimiter;

* Don't put spaces after an opening parenthesis and before a closing one;

* Add a single space around operators (`==`, `&&`, ...);

* Add a single space before the opening parenthesis of a control keyword
  (`if`, `else`, `for`, `while`, ...);

* Add a blank line before `return` statements;

* Don't add trailing spaces at the end of lines;

* Use braces to indicate control structure body regardless of the number of
  statements it contains;

* Put braces on their own line for classes, methods, and functions
  declaration;

* Separate the conditional statement and the opening brace with a single
  space and no blank line;

* Declare visibility explicitly for class, methods, and properties (usage of
  `var` is prohibited);

* Use lowercase PHP native typed constants: `false`, `true`, and `null`. The
  same goes for `array()`;

* Use uppercase strings for constants with words separated with underscores;

* Define one class per file;

* Declare class properties before methods;

* Declare public methods first, then protected ones and finally private ones.

Naming Conventions
------------------

* Use camelCase, not underscores, for variable, function and method
  names;

* Use underscores for option, argument, parameter names;

* Use namespaces for all classes;

* Use `Symfony` as the first namespace level;

* Suffix interfaces with `Interface`;

* Use alphanumeric characters and underscores for file names;

Documentation
-------------

* Add PHPDoc blocks for all classes, methods, and functions;

* The `@package` and `@subpackage` annotations are not used.
