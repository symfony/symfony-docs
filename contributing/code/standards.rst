Coding Standards
================

When contributing code to Symfony2, you must follow its coding standards. To
make a long story short, here is the golden rule: **Imitate the existing
Symfony2 code**. Most open-source Bundles and libraries used by Symfony2 also
follow the same guidelines, and you should too.

Remember that the main advantage of standards is that every piece of code
looks and feels familiar, it's not about this or that being more readable.

Symfony follows the standards defined in the `PSR-0`_, `PSR-1`_ and `PSR-2`_
documents.

Since a picture - or some code - is worth a thousand words, here's a short
example containing most features described below:

.. code-block:: html+php

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

    /**
     * Coding standards demonstration.
     */
    class FooBar
    {
        const SOME_CONST = 42;

        private $fooBar;

        /**
         * @param string $dummy Some argument description
         */
        public function __construct($dummy)
        {
            $this->fooBar = $this->transformText($dummy);
        }

        /**
         * @param string $dummy Some argument description
         * @param array  $options
         *
         * @return string|null Transformed input
         */
        private function transformText($dummy, array $options = array())
        {
            $mergedOptions = array_merge(
                $options,
                array(
                    'some_default' => 'values',
                    'another_default' => 'more values',
                )
            );

            if (true === $dummy) {
                return;
            }
            if ('string' === $dummy) {
                if ('values' === $mergedOptions['some_default']) {
                    $dummy = substr($dummy, 0, 5);
                } else {
                    $dummy = ucwords($dummy);
                }
            } else {
                throw new \RuntimeException(sprintf('Unrecognized dummy option "%s"', $dummy));
            }

            return $dummy;
        }
    }

Structure
---------

* Add a single space after each comma delimiter;

* Add a single space around operators (``==``, ``&&``, ...);

* Add a comma after each array item in a multi-line array, even after the
  last one;

* Add a blank line before ``return`` statements, unless the return is alone
  inside a statement-group (like an ``if`` statement);

* Use braces to indicate control structure body regardless of the number of
  statements it contains;

* Define one class per file - this does not apply to private helper classes
  that are not intended to be instantiated from the outside and thus are not
  concerned by the `PSR-0`_ standard;

* Declare class properties before methods;

* Declare public methods first, then protected ones and finally private ones;

* Use parentheses when instantiating classes regardless of the number of
  arguments the constructor has;

* Exception message strings should be concatenated using :phpfunction:`sprintf`.

Naming Conventions
------------------

* Use camelCase, not underscores, for variable, function and method
  names, arguments;

* Use underscores for option names and parameter names;

* Use namespaces for all classes;

* Prefix abstract classes with ``Abstract``. Please note some early Symfony2 classes
  do not follow this convention and have not been renamed for backward compatibility
  reasons. However all new abstract classes must follow this naming convention;

* Suffix interfaces with ``Interface``;

* Suffix traits with ``Trait``;

* Suffix exceptions with ``Exception``;

* Use alphanumeric characters and underscores for file names;

* Don't forget to look at the more verbose :doc:`conventions` document for
  more subjective naming considerations.

Documentation
-------------

* Add PHPDoc blocks for all classes, methods, and functions;

* Omit the ``@return`` tag if the method does not return anything;

* The ``@package`` and ``@subpackage`` annotations are not used.

License
-------

* Symfony is released under the MIT license, and the license block has to be
  present at the top of every PHP file, before the namespace.

.. _`PSR-0`: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
.. _`PSR-1`: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
.. _`PSR-2`: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
