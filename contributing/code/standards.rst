Coding Standards
================

Symfony code is contributed by thousands of developers around the world. To make
every piece of code look and feel familiar, Symfony defines some coding standards
that all contributions must follow.

These Symfony coding standards are based on the `PSR-1`_, `PSR-2`_, `PSR-4`_
and `PSR-12`_ standards, so you may already know most of them.

Making your Code Follow the Coding Standards
--------------------------------------------

Instead of reviewing your code manually, Symfony makes it simple to ensure that
your contributed code matches the expected code syntax. First, install the
`PHP CS Fixer tool`_ and then, run this command to fix any problem:

.. code-block:: terminal

    $ cd your-project/
    $ php php-cs-fixer.phar fix -v

If you forget to run this command and make a pull request with any syntax issue,
our automated tools will warn you about that and will provide the solution.

Symfony Coding Standards in Detail
----------------------------------

If you want to learn about the Symfony coding standards in detail, here's a
short example containing most features described below::

    /*
     * This file is part of the Symfony package.
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */

    namespace Acme;

    use Other\Qux;

    /**
     * Coding standards demonstration.
     */
    class FooBar
    {
        const SOME_CONST = 42;

        /**
         * @var string
         */
        private $fooBar;

        private $qux;

        /**
         * @param string $dummy Some argument description
         */
        public function __construct($dummy, Qux $qux)
        {
            $this->fooBar = $this->transformText($dummy);
            $this->qux = $qux;
        }

        /**
         * @return string
         *
         * @deprecated
         */
        public function someDeprecatedMethod()
        {
            trigger_deprecation('symfony/package-name', '5.1', 'The %s() method is deprecated, use Acme\Baz::someMethod() instead.', __METHOD__);

            return Baz::someMethod();
        }

        /**
         * Transforms the input given as first argument.
         *
         * @param bool|string $dummy   Some argument description
         * @param array       $options An options collection to be used within the transformation
         *
         * @return string|null The transformed input
         *
         * @throws \RuntimeException When an invalid option is provided
         */
        private function transformText($dummy, array $options = [])
        {
            $defaultOptions = [
                'some_default' => 'values',
                'another_default' => 'more values',
            ];

            foreach ($options as $name => $value) {
                if (!array_key_exists($name, $defaultOptions)) {
                    throw new \RuntimeException(sprintf('Unrecognized option "%s"', $name));
                }
            }

            $mergedOptions = array_merge(
                $defaultOptions,
                $options
            );

            if (true === $dummy) {
                return 'something';
            }

            if (is_string($dummy)) {
                if ('values' === $mergedOptions['some_default']) {
                    return substr($dummy, 0, 5);
                }

                return ucwords($dummy);
            }

            return null;
        }

        /**
         * Performs some basic operations for a given value.
         *
         * @param mixed $value     Some value to operate against
         * @param bool  $theSwitch Some switch to control the method's flow
         */
        private function performOperations($value = null, $theSwitch = false)
        {
            if (!$theSwitch) {
                return;
            }

            $this->qux->doFoo($value);
            $this->qux->doBar($value);
        }
    }

Structure
~~~~~~~~~

* Add a single space after each comma delimiter;

* Add a single space around binary operators (``==``, ``&&``, ...), with
  the exception of the concatenation (``.``) operator;

* Place unary operators (``!``, ``--``, ...) adjacent to the affected variable;

* Always use `identical comparison`_ unless you need type juggling;

* Use `Yoda conditions`_ when checking a variable against an expression to avoid
  an accidental assignment inside the condition statement (this applies to ``==``,
  ``!=``, ``===``, and ``!==``);

* Add a comma after each array item in a multi-line array, even after the
  last one;

* Add a blank line before ``return`` statements, unless the return is alone
  inside a statement-group (like an ``if`` statement);

* Use ``return null;`` when a function explicitly returns ``null`` values and
  use ``return;`` when the function returns ``void`` values;

* Use braces to indicate control structure body regardless of the number of
  statements it contains;

* Define one class per file - this does not apply to private helper classes
  that are not intended to be instantiated from the outside and thus are not
  concerned by the `PSR-0`_ and `PSR-4`_ autoload standards;

* Declare the class inheritance and all the implemented interfaces on the same
  line as the class name;

* Declare class properties before methods;

* Declare public methods first, then protected ones and finally private ones.
  The exceptions to this rule are the class constructor and the ``setUp()`` and
  ``tearDown()`` methods of PHPUnit tests, which must always be the first methods
  to increase readability;

* Declare all the arguments on the same line as the method/function name, no
  matter how many arguments there are;

* Use parentheses when instantiating classes regardless of the number of
  arguments the constructor has;

* Exception and error message strings must be concatenated using :phpfunction:`sprintf`;

* Do not use ``else``, ``elseif``, ``break`` after ``if`` and ``case`` conditions
  which return or throw something;

* Do not use spaces around ``[`` offset accessor and before ``]`` offset accessor;

* Add a ``use`` statement for every class that is not part of the global namespace;

* When PHPDoc tags like ``@param`` or ``@return`` include ``null`` and other
  types, always place ``null`` at the end of the list of types.

Naming Conventions
~~~~~~~~~~~~~~~~~~

* Use `camelCase`_ for PHP variables, function and method names, arguments
  (e.g. ``$acceptableContentTypes``, ``hasSession()``);

* Use `snake_case`_ for configuration parameters and Twig template variables
  (e.g. ``framework.csrf_protection``, ``http_status_code``);

* Use namespaces for all PHP classes and `UpperCamelCase`_ for their names (e.g.
  ``ConsoleLogger``);

* Prefix all abstract classes with ``Abstract`` except PHPUnit ``*TestCase``.
  Please note some early Symfony classes do not follow this convention and
  have not been renamed for backward compatibility reasons. However, all new
  abstract classes must follow this naming convention;

* Suffix interfaces with ``Interface``;

* Suffix traits with ``Trait``;

* Suffix exceptions with ``Exception``;

* Use UpperCamelCase for naming PHP files (e.g. ``EnvVarProcessor.php``) and
  snake case for naming Twig templates and web assets (``section_layout.html.twig``,
  ``index.scss``);

* For type-hinting in PHPDocs and casting, use ``bool`` (instead of ``boolean``
  or ``Boolean``), ``int`` (instead of ``integer``), ``float`` (instead of
  ``double`` or ``real``);

* Don't forget to look at the more verbose :doc:`conventions` document for
  more subjective naming considerations.

.. _service-naming-conventions:

Service Naming Conventions
~~~~~~~~~~~~~~~~~~~~~~~~~~

* A service name must be the same as the fully qualified class name (FQCN) of
  its class (e.g. ``App\EventSubscriber\UserSubscriber``);

* If there are multiple services for the same class, use the FQCN for the main
  service and use lowercase and underscored names for the rest of services.
  Optionally divide them in groups separated with dots (e.g.
  ``something.service_name``, ``fos_user.something.service_name``);

* Use lowercase letters for parameter names (except when referring
  to environment variables with the ``%env(VARIABLE_NAME)%`` syntax);

* Add class aliases for public services (e.g. alias ``Symfony\Component\Something\ClassName``
  to ``something.service_name``).

Documentation
~~~~~~~~~~~~~

* Add PHPDoc blocks for all classes, methods, and functions (though you may
  be asked to remove PHPDoc that do not add value);

* Group annotations together so that annotations of the same type immediately
  follow each other, and annotations of a different type are separated by a
  single blank line;

* Omit the ``@return`` tag if the method does not return anything;

* The ``@package`` and ``@subpackage`` annotations are not used;

* Don't inline PHPDoc blocks, even when they contain just one tag (e.g. don't
  put ``/** {@inheritdoc} */`` in a single line);

* When adding a new class or when making significant changes to an existing class,
  an ``@author`` tag with personal contact information may be added, or expanded.
  Please note it is possible to have the personal contact information updated or
  removed per request to the :doc:`core team </contributing/code/core_team>`.

License
~~~~~~~

* Symfony is released under the MIT license, and the license block has to be
  present at the top of every PHP file, before the namespace.

.. _`PHP CS Fixer tool`: https://cs.symfony.com/
.. _`PSR-0`: https://www.php-fig.org/psr/psr-0/
.. _`PSR-1`: https://www.php-fig.org/psr/psr-1/
.. _`PSR-2`: https://www.php-fig.org/psr/psr-2/
.. _`PSR-4`: https://www.php-fig.org/psr/psr-4/
.. _`PSR-12`: https://www.php-fig.org/psr/psr-12/
.. _`identical comparison`: https://www.php.net/manual/en/language.operators.comparison.php
.. _`Yoda conditions`: https://en.wikipedia.org/wiki/Yoda_conditions
.. _`camelCase`: https://en.wikipedia.org/wiki/Camel_case
.. _`UpperCamelCase`: https://en.wikipedia.org/wiki/Camel_case
.. _`snake_case`: https://en.wikipedia.org/wiki/Snake_case
