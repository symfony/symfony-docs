Coding Standards
================

When contributing code to Symfony2, you must follow its coding standards. To
make a long story short, here is the golden rule: **Imitate the existing
Symfony2 code**. Most open-source Bundles and libraries used by Symfony2 also
follow the same guidelines, and you should too.

Remember that the main advantage of standards is that every piece of code
looks and feels familiar, it's not about this or that being more readable.

Since a picture - or some code - is worth a thousand words, here's a short
example containing most features described below:

.. code-block:: php

    <?php

    /*
     * This file is part of the Symfony package.
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */

    namespace Acme;

    class Foo
    {
        const SOME_CONST = 42;

        private $foo;

        /**
         * @param string $dummy Some argument description
         */
        public function __construct($dummy)
        {
            $this->foo = $this->transform($dummy);
        }

        /**
         * @param string $dummy Some argument description
         * @return string|null Transformed input
         */
        private function transform($dummy)
        {
            if (true === $dummy) {
                return;
            } elseif ('string' === $dummy) {
                $dummy = substr($dummy, 0, 5);
            }

            return $dummy;
        }
    }

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

* Separate the conditional statements (`if`, `else`, ...) and the opening
  brace with a single space and no blank line;

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

* Don't forget to look at the more verbose :doc:`conventions` document for
  more subjective naming considerations.

Documentation
-------------

* Add PHPDoc blocks for all classes, methods, and functions;

* The `@package` and `@subpackage` annotations are not used.

License
-------

* Symfony is released under the MIT license, and the license block has to be
  present at the top of every PHP file, before the namespace.
