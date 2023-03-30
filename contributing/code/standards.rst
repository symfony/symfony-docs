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
        public const SOME_CONST = 42;

        /**
         * @var string
         */
        private $fooBar;
        private $qux;

        /**
         * @param $dummy some argument description
         */
        public function __construct(string $dummy, Qux $qux)
        {
            $this->fooBar = $this->transformText($dummy);
            $this->qux = $qux;
        }

        /**
         * @deprecated
         */
        public function someDeprecatedMethod(): string
        {
            trigger_deprecation('symfony/package-name', '5.1', 'The %s() method is deprecated, use Acme\Baz::someMethod() instead.', __METHOD__);

            return Baz::someMethod();
        }

        /**
         * Transforms the input given as the first argument.
         *
         * @param $options an options collection to be used within the transformation
         *
         * @throws \RuntimeException when an invalid option is provided
         */
        private function transformText(bool|string $dummy, array $options = []): ?string
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

            $mergedOptions = array_merge($defaultOptions, $options);

            if (true === $dummy) {
                return 'something';
            }

            if (\is_string($dummy)) {
                if ('values' === $mergedOptions['some_default']) {
                    return substr($dummy, 0, 5);
                }

                return ucwords($dummy);
            }

            return null;
        }

        /**
         * Performs some basic operations for a given value.
         */
        private function performOperations(mixed $value = null, bool $theSwitch = false)
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

* Do not add the ``void`` return type to methods in tests;

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
  matter how many arguments there are. The only exception are constructor methods
  using `constructor property promotion`_, where each parameter must be on a new
  line with `trailing comma`_;

* Use parentheses when instantiating classes regardless of the number of
  arguments the constructor has;

* Exception and error message strings must be concatenated using :phpfunction:`sprintf`;

* Exception and error messages must not contain backticks,
  even when referring to a technical element (such as a method or variable name).
  Double quotes must be used at all time:

  .. code-block:: diff

    - Expected `foo` option to be one of ...
    + Expected "foo" option to be one of ...

* Exception and error messages must start with a capital letter and finish with a dot ``.``;

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

* Use SCREAMING_SNAKE_CASE for constants (e.g. ``InputArgument::IS_ARRAY``);

* Use `UpperCamelCase`_ for enumeration cases (e.g. ``InputArgumentMode::IsArray``);

* Use namespaces for all PHP classes, interfaces, traits and enums and
  `UpperCamelCase`_ for their names (e.g. ``ConsoleLogger``);

* Prefix all abstract classes with ``Abstract`` except PHPUnit ``*TestCase``.
  Please note some early Symfony classes do not follow this convention and
  have not been renamed for backward compatibility reasons. However, all new
  abstract classes must follow this naming convention;

* Suffix interfaces with ``Interface``;

* Suffix traits with ``Trait``;

* Don't use a dedicated suffix for classes or enumerations (e.g. like ``Class``
  or ``Enum``), except for the cases listed below.

* Suffix exceptions with ``Exception``;

* Prefix PHP attributes with ``As`` where applicable (e.g. ``#[AsCommand]``
  instead of ``#[Command]``, but ``#[When]`` is kept as-is);

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

* Add PHPDoc blocks for classes, methods, and functions only when they add
  relevant information that does not duplicate the name, native type
  declaration or context (e.g. ``instanceof`` checks);

* Only use annotations and types defined in `the PHPDoc reference`_. In
  order to improve types for static analysis, the following annotations are
  also allowed:

  * `Generics`_, with the exception of ``@template-covariant``.
  * `Conditional return types`_ using the vendor-prefixed ``@psalm-return``;
  * `Class constants`_;
  * `Callable types`_;

* Group annotations together so that annotations of the same type immediately
  follow each other, and annotations of a different type are separated by a
  single blank line;

* Omit the ``@return`` annotation if the method does not return anything;

* Don't use one-line PHPDoc blocks on classes, methods and functions, even
  when they contain just one annotation (e.g. don't put ``/** {@inheritdoc} */``
  in a single line);

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
.. _`constructor property promotion`: https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion
.. _`trailing comma`: https://wiki.php.net/rfc/trailing_comma_in_parameter_list
.. _`the PHPDoc reference`: https://docs.phpdoc.org/3.0/guide/references/phpdoc/index.html
.. _`Conditional return types`: https://psalm.dev/docs/annotating_code/type_syntax/conditional_types/
.. _`Class constants`: https://psalm.dev/docs/annotating_code/type_syntax/value_types/#regular-class-constants
.. _`Callable types`: https://psalm.dev/docs/annotating_code/type_syntax/callable_types/
.. _`Generics`: https://psalm.dev/docs/annotating_code/templated_annotations/
