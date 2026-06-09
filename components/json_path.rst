The JsonPath Component
======================

The JsonPath component lets you query and extract data from JSON structures.
It implements the `RFC 9535 – JSONPath`_ standard, allowing you to navigate
complex JSON data.

Similar to the :doc:`DomCrawler component </components/dom_crawler>`, which lets
you navigate and query HTML or XML documents with XPath, the JsonPath component
offers the same convenience for traversing and searching JSON structures through
JSONPath expressions. The component also provides an abstraction layer for data
extraction.

Installation
------------

You can install the component in your project using Composer:

.. code-block:: terminal

    $ composer require symfony/json-path

.. include:: /components/require_autoload.rst.inc

Usage
-----

To start querying a JSON document, first create a :class:`Symfony\\Component\\JsonPath\\JsonCrawler`
object from a JSON string. The following examples use this sample "bookstore"
JSON data::

    use Symfony\Component\JsonPath\JsonCrawler;

    $json = <<<'JSON'
    {
        "store": {
            "book": [
                {
                    "category": "reference",
                    "author": "Nigel Rees",
                    "title": "Sayings of the Century",
                    "price": 8.95
                },
                {
                    "category": "fiction",
                    "author": "Evelyn Waugh",
                    "title": "Sword of Honour",
                    "price": 12.99
                },
                {
                    "category": "fiction",
                    "author": "Herman Melville",
                    "title": "Moby Dick",
                    "isbn": "0-553-21311-3",
                    "price": 8.99
                },
                {
                    "category": "fiction",
                    "author": "John Ronald Reuel Tolkien",
                    "title": "The Lord of the Rings",
                    "isbn": "0-395-19395-8",
                    "price": 22.99
                }
            ],
            "bicycle": {
                "color": "red",
                "price": 399
            }
        }
    }
    JSON;

    $crawler = new JsonCrawler($json);

Once you have the crawler instance, use its :method:`Symfony\\Component\\JsonPath\\JsonCrawler::find`
method to start querying the data. This method returns an array of matching values.

Querying with Expressions
-------------------------

The primary way to query the JSON is by passing a JSONPath expression string
to the :method:`Symfony\\Component\\JsonPath\\JsonCrawler::find` method.

Accessing a Specific Property
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Use dot notation for object keys and square brackets for array indices. The root
of the document is represented by ``$``::

    // get the title of the first book in the store
    $titles = $crawler->find('$.store.book[0].title');

    // $titles is ['Sayings of the Century']

Dot notation is the default, but JSONPath provides other syntaxes for cases
where it doesn't work. Use bracket notation (``['...']``) when a key contains
spaces or special characters::

    // this is equivalent to the previous example
    $titles = $crawler->find('$["store"]["book"][0]["title"]');

    // this expression requires brackets because some keys use dots or spaces
    $titles = $crawler->find('$["store"]["book collection"][0]["title.original"]');

    // you can combine both notations
    $titles = $crawler->find('$["store"].book[0].title');

Searching with the Descendant Operator
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The descendant operator (``..``) recursively searches for a given key, allowing
you to find values without specifying the full path::

    // get all authors from anywhere in the document
    $authors = $crawler->find('$..author');

    // $authors is ['Nigel Rees', 'Evelyn Waugh', 'Herman Melville', 'John Ronald Reuel Tolkien']

Filtering Results
~~~~~~~~~~~~~~~~~

JSONPath includes a filter syntax (``?(expression)``) to select items based on
a condition. The current item within the filter is referenced by ``@``::

    // get all books with a price less than 10
    $cheapBooks = $crawler->find('$.store.book[?(@.price < 10)]');

Building Queries Programmatically
---------------------------------

For more dynamic or complex query building, use the fluent API provided
by the :class:`Symfony\\Component\\JsonPath\\JsonPath` class. This lets you
construct a query object step by step. The ``JsonPath`` object can then be passed
to the crawler's :method:`Symfony\\Component\\JsonPath\\JsonCrawler::find` method.

The main advantage of the programmatic builder is that it automatically handles
escaping of keys and values, preventing syntax errors::

    use Symfony\Component\JsonPath\JsonPath;

    $path = new JsonPath()
        ->key('store') // selects the 'store' key
        ->key('book')  // then the 'book' key
        ->index(1);    // then the second item (indexes start at 0)

    // the created $path object is equivalent to the string '$["store"]["book"][1]'
    $book = $crawler->find($path);

    // $book contains the book object for "Sword of Honour"

The :class:`Symfony\\Component\\JsonPath\\JsonPath` class provides several
methods to build your query:

* :method:`Symfony\\Component\\JsonPath\\JsonPath::key`
  Adds a key selector. The key name is properly escaped::

      // creates the path '$["key\"with\"quotes"]'
      $path = new JsonPath()->key('key"with"quotes');

* :method:`Symfony\\Component\\JsonPath\\JsonPath::deepScan`
  Adds the descendant operator ``..`` to perform a recursive search from the
  current point in the path::

      // get all prices in the store: '$["store"]..["price"]'
      $path = new JsonPath()->key('store')->deepScan()->key('price');

* :method:`Symfony\\Component\\JsonPath\\JsonPath::all`
  Adds the wildcard operator ``[*]`` to select all items in an array or object::

        // creates the path '$["store"]["book"][*]'
        $path = new JsonPath()->key('store')->key('book')->all();

* :method:`Symfony\\Component\\JsonPath\\JsonPath::index`
  Adds an array index selector. Index numbers start at ``0``.

* :method:`Symfony\\Component\\JsonPath\\JsonPath::first` /
  :method:`Symfony\\Component\\JsonPath\\JsonPath::last`
  Shortcuts for ``index(0)`` and ``index(-1)`` respectively::

      // get the last book: '$["store"]["book"][-1]'
      $path = new JsonPath()->key('store')->key('book')->last();

* :method:`Symfony\\Component\\JsonPath\\JsonPath::slice`
  Adds an array slice selector ``[start:end:step]``::

      // get books from index 1 up to (but not including) index 3
      // creates the path '$["store"]["book"][1:3]'
      $path = new JsonPath()->key('store')->key('book')->slice(1, 3);

      // get every second book from the first four books
      // creates the path '$["store"]["book"][0:4:2]'
      $path = new JsonPath()->key('store')->key('book')->slice(0, 4, 2);

* :method:`Symfony\\Component\\JsonPath\\JsonPath::filter`
  Adds a filter expression. The expression string is the part that goes inside
  the ``?()`` syntax::

      // get expensive books: '$["store"]["book"][?(@.price > 20)]'
      $path = new JsonPath()
          ->key('store')
          ->key('book')
          ->filter('@.price > 20');

Advanced Querying
-----------------

For a complete overview of advanced operators like wildcards and functions within
filters, refer to the `Querying with Expressions`_ section above. All these
features are supported and can be combined with the programmatic builder when
appropriate (e.g., inside a ``filter()`` expression).

.. _json-path-custom-functions:

Custom Functions
----------------

.. versionadded:: 8.1

    Custom function support was introduced in Symfony 8.1.

The JSONPath specification allows you to `define custom functions`_. Symfony
supports them as well, so you can register your own functions for use in filter
expressions.

First, define the function and register it as a JSONPath function:

.. configuration-block::

    .. code-block:: php-symfony

        use Symfony\Component\JsonPath\Attribute\AsJsonPathFunction;

        #[AsJsonPathFunction('upper')]
        final class UppercaseFunction
        {
            public function __invoke(mixed $value): ?string
            {
                return \is_string($value) ? strtoupper($value) : null;
            }
        }

    .. code-block:: php-standalone

        use Symfony\Component\JsonPath\JsonCrawler;

        $upper = static fn (mixed $value): ?string => \is_string($value) ? strtoupper($value) : null;

        // create a service locator with the custom function
        $functionsLocator = new class(['upper' => $upper]) implements \Psr\Container\ContainerInterface {
            public function __construct(private array $functions) {}
            public function get(string $id): mixed { return $this->functions[$id]; }
            public function has(string $id): bool { return isset($this->functions[$id]); }
        };

        $crawler = new JsonCrawler('{"items": [{"title": "hello"}, {"title": "world"}]}', $functionsLocator);

Then use it in your JsonPath expressions like any built-in function:

.. configuration-block::

    .. code-block:: php-symfony

        use Symfony\Component\JsonPath\JsonPathCrawlerInterface;

        class BookController
        {
            public function index(JsonPathCrawlerInterface $crawlerFactory): Response
            {
                $crawler = $crawlerFactory->crawl($json);

                $result = $crawler->find('$.items[?upper(@.title) == "HELLO"]');

                // ...
            }
        }

    .. code-block:: php-standalone

        $result = $crawler->find('$.items[?upper(@.title) == "HELLO"]');
        // returns [{"title": "hello"}]

        // you can also use the JsonPathCrawler factory, which creates
        // pre-configured JsonCrawlerInterface instances
        use Symfony\Component\JsonPath\JsonPathCrawler;

        $crawlerFactory = new JsonPathCrawler($functionsLocator);
        $crawler = $crawlerFactory->crawl('{"items": [{"title": "hello"}]}');

The ``#[AsJsonPathFunction]`` attribute accepts two arguments:

``name``
    The function name used in JsonPath expressions (required).

``returnType``
    The return type of the function (default: ``FunctionReturnType::Value``).
    This controls how the function can be used in filter expressions, following
    the `RFC 9535 type system`_:

    * ``FunctionReturnType::Value``: the function returns a JSON value that can
      be used in comparisons (``==``, ``<``, ``>``, etc.) but not as a standalone
      filter test;
    * ``FunctionReturnType::Logical``: the function returns a boolean result
      that can be used as a standalone filter test (e.g.
      ``$.items[?is_valid(@.field)]``) but not in comparisons;
    * ``FunctionReturnType::Nodes``: the function returns a node list that can
      be used as a filter test but not in comparisons.

For example::

    use Symfony\Component\JsonPath\Attribute\AsJsonPathFunction;
    use Symfony\Component\JsonPath\FunctionReturnType;

    #[AsJsonPathFunction('is_positive', returnType: FunctionReturnType::Logical)]
    final class IsPositiveFunction
    {
        public function __invoke(mixed $value): bool
        {
            return is_numeric($value) && $value > 0;
        }
    }

    // usage: $.items[?is_positive(@.value)]

The number of required arguments is automatically derived from the ``__invoke()``
method signature.

.. note::

    Custom functions can override built-in functions (``length``, ``count``,
    ``match``, ``search``, ``value``). When a custom function has the same name
    as a built-in one, the custom function takes precedence.

Testing with JSON Assertions
----------------------------

The component provides a set of PHPUnit assertions to make testing JSON data
more convenient. Use the :class:`Symfony\\Component\\JsonPath\\Test\\JsonPathAssertionsTrait`
in your test class::

    use PHPUnit\Framework\TestCase;
    use Symfony\Component\JsonPath\Test\JsonPathAssertionsTrait;

    class MyTest extends TestCase
    {
        use JsonPathAssertionsTrait;

        public function testSomething(): void
        {
            $json = '{"books": [{"title": "A"}, {"title": "B"}]}';

            self::assertJsonPathCount(2, '$.books[*]', $json);
        }
    }

The trait provides the following assertion methods:

* :method:`Symfony\\Component\\JsonPath\\Test\\JsonPathAssertionsTrait::assertJsonPathCount`
  Asserts that the number of elements found by the JSONPath expression matches
  an expected count::

      $json = '{"a": [1, 2, 3]}';
      self::assertJsonPathCount(3, '$.a[*]', $json);

* :method:`Symfony\\Component\\JsonPath\\Test\\JsonPathAssertionsTrait::assertJsonPathEquals`
  Asserts that the result of a JSONPath expression is equal to an expected
  value. The comparison uses ``==`` (type coercion) instead of ``===``::

      $json = '{"a": [1, 2, 3]}';

      // passes because "1" == 1
      self::assertJsonPathEquals(['1'], '$.a[0]', $json);

* :method:`Symfony\\Component\\JsonPath\\Test\\JsonPathAssertionsTrait::assertJsonPathNotEquals`
  Asserts that the result of a JSONPath expression is not equal to an expected
  value. The comparison uses ``!=`` (type coercion) instead of ``!==``::

      $json = '{"a": [1, 2, 3]}';
      self::assertJsonPathNotEquals([42], '$.a[0]', $json);

* :method:`Symfony\\Component\\JsonPath\\Test\\JsonPathAssertionsTrait::assertJsonPathSame`
  Asserts that the result of a JSONPath expression is identical (``===``) to an
  expected value. This is a strict comparison and does not perform type
  coercion::

      $json = '{"a": [1, 2, 3]}';

      // fails because "1" !== 1
      // self::assertJsonPathSame(['1'], '$.a[0]', $json);

      self::assertJsonPathSame([1], '$.a[0]', $json);

* :method:`Symfony\\Component\\JsonPath\\Test\\JsonPathAssertionsTrait::assertJsonPathNotSame`
  Asserts that the result of a JSONPath expression is not identical (``!==``) to
  an expected value::

      $json = '{"a": [1, 2, 3]}';
      self::assertJsonPathNotSame(['1'], '$.a[0]', $json);

* :method:`Symfony\\Component\\JsonPath\\Test\\JsonPathAssertionsTrait::assertJsonPathContains`
  Asserts that a given value is found within the array of results from the
  JSONPath expression::

      $json = '{"tags": ["php", "symfony", "json"]}';
      self::assertJsonPathContains('symfony', '$.tags[*]', $json);

* :method:`Symfony\\Component\\JsonPath\\Test\\JsonPathAssertionsTrait::assertJsonPathNotContains`
  Asserts that a given value is NOT found within the array of results from the
  JSONPath expression::

      $json = '{"tags": ["php", "symfony", "json"]}';
      self::assertJsonPathNotContains('java', '$.tags[*]', $json);

Error Handling
--------------

The component throws specific exceptions for invalid input or queries:

* :class:`Symfony\\Component\\JsonPath\\Exception\\InvalidArgumentException`:
  Thrown if the input to the ``JsonCrawler`` constructor is not a valid JSON string;
* :class:`Symfony\\Component\\JsonPath\\Exception\\InvalidJsonStringInputException`:
  Thrown during a ``find()`` call if the JSON string is malformed (e.g., syntax error);
* :class:`Symfony\\Component\\JsonPath\\Exception\\JsonCrawlerException`:
  Thrown for errors within the JsonPath expression itself, such as using an
  unknown function

Example of handling errors::

    use Symfony\Component\JsonPath\Exception\InvalidJsonStringInputException;
    use Symfony\Component\JsonPath\Exception\JsonCrawlerException;

    try {
        // the following line contains malformed JSON
        $crawler = new JsonCrawler('{"store": }');
        $crawler->find('$..*');
    } catch (InvalidJsonStringInputException $e) {
        // ... handle error
    }

    try {
        // the following line contains an invalid query
        $crawler->find('$.store.book[?unknown_function(@.price)]');
    } catch (JsonCrawlerException $e) {
        // ... handle error
    }

.. _`RFC 9535 – JSONPath`: https://datatracker.ietf.org/doc/html/rfc9535
.. _`define custom functions`: https://datatracker.ietf.org/doc/html/rfc9535#name-function-extensions
.. _`RFC 9535 type system`: https://datatracker.ietf.org/doc/html/rfc9535#name-type-system-for-function-ex
