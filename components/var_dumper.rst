.. index::
   single: VarDumper
   single: Components; VarDumper

The VarDumper Component
=======================

    The VarDumper component provides mechanisms for extracting the state out of
    any PHP variables. Built on top, it provides a better ``dump()`` function
    that you can use instead of :phpfunction:`var_dump`.

Installation
------------

.. code-block:: terminal

    $ composer require --dev symfony/var-dumper

Alternatively, you can clone the `<https://github.com/symfony/var-dumper>`_ repository.

.. include:: /components/require_autoload.rst.inc

.. note::

    If using it inside a Symfony application, make sure that the DebugBundle has
    been installed (or run ``composer require symfony/debug-bundle`` to install it).

.. _components-var-dumper-dump:

The dump() Function
-------------------

The VarDumper component creates a global ``dump()`` function that you can
use instead of e.g. :phpfunction:`var_dump`. By using it, you'll gain:

* Per object and resource types specialized view to e.g. filter out
  Doctrine internals while dumping a single proxy entity, or get more
  insight on opened files with :phpfunction:`stream_get_meta_data`;
* Configurable output formats: HTML or colored command line output;
* Ability to dump internal references, either soft ones (objects or
  resources) or hard ones (``=&`` on arrays or objects properties).
  Repeated occurrences of the same object/array/resource won't appear
  again and again anymore. Moreover, you'll be able to inspect the
  reference structure of your data;
* Ability to operate in the context of an output buffering handler.

For example::

    require __DIR__.'/vendor/autoload.php';

    // create a variable, which could be anything!
    $someVar = ...;

    dump($someVar);

    // dump() returns the passed value, so you can dump an object and keep using it
    dump($someObject)->someMethod();

By default, the output format and destination are selected based on your
current PHP SAPI:

* On the command line (CLI SAPI), the output is written on ``STDOUT``. This
  can be surprising to some because this bypasses PHP's output buffering
  mechanism;
* On other SAPIs, dumps are written as HTML in the regular output.

.. tip::

    You can also select the output format explicitly defining the
    ``VAR_DUMPER_FORMAT`` environment variable and setting its value to either
    ``html`` or ``cli``.

.. note::

    If you want to catch the dump output as a string, please read the
    :doc:`advanced documentation </components/var_dumper/advanced>` which contains examples of it.
    You'll also learn how to change the format or redirect the output to
    wherever you want.

.. tip::

    In order to have the ``dump()`` function always available when running
    any PHP code, you can install it globally on your computer:

    #. Run ``composer global require symfony/var-dumper``;
    #. Add ``auto_prepend_file = ${HOME}/.composer/vendor/autoload.php``
       to your ``php.ini`` file;
    #. From time to time, run ``composer global update symfony/var-dumper``
       to have the latest bug fixes.

.. tip::

    The VarDumper component also provides a ``dd()`` ("dump and die") helper
    function. This function dumps the variables using ``dump()`` and
    immediately ends the execution of the script (using :phpfunction:`exit`).

.. _var-dumper-dump-server:

The Dump Server
---------------

The ``dump()`` function outputs its contents in the same browser window or
console terminal as your own application. Sometimes mixing the real output
with the debug output can be confusing. That's why this component provides a
server to collect all the dumped data.

Start the server with the ``server:dump`` command and whenever you call to
``dump()``, the dumped data won't be displayed in the output but sent to that
server, which outputs it to its own console or to an HTML file:

.. code-block:: terminal

    # displays the dumped data in the console:
    $ php bin/console server:dump
      [OK] Server listening on tcp://0.0.0.0:9912

    # stores the dumped data in a file using the HTML format:
    $ php bin/console server:dump --format=html > dump.html

Inside a Symfony application, the output of the dump server is configured with
the :ref:`dump_destination option <configuration-debug-dump_destination>` of the
``debug`` package:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/debug.yaml
        debug:
           dump_destination: "tcp://%env(VAR_DUMPER_SERVER)%"

    .. code-block:: xml

        <!-- config/packages/debug.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/debug"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:debug="http://symfony.com/schema/dic/debug"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/debug https://symfony.com/schema/dic/debug/debug-1.0.xsd">

            <debug:config dump-destination="tcp://%env(VAR_DUMPER_SERVER)%"/>
        </container>

    .. code-block:: php

        // config/packages/debug.php
        $container->loadFromExtension('debug', [
           'dump_destination' => 'tcp://%env(VAR_DUMPER_SERVER)%',
        ]);

Outside a Symfony application, use the :class:`Symfony\\Component\\VarDumper\\Dumper\\ServerDumper` class::

    require __DIR__.'/vendor/autoload.php';

    use Symfony\Component\VarDumper\VarDumper;
    use Symfony\Component\VarDumper\Cloner\VarCloner;
    use Symfony\Component\VarDumper\Dumper\CliDumper;
    use Symfony\Component\VarDumper\Dumper\ContextProvider\CliContextProvider;
    use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
    use Symfony\Component\VarDumper\Dumper\HtmlDumper;
    use Symfony\Component\VarDumper\Dumper\ServerDumper;

    $cloner = new VarCloner();
    $fallbackDumper = \in_array(\PHP_SAPI, ['cli', 'phpdbg']) ? new CliDumper() : new HtmlDumper();
    $dumper = new ServerDumper('tcp://127.0.0.1:9912', $fallbackDumper, [
        'cli' => new CliContextProvider(),
        'source' => new SourceContextProvider(),
    ]);

    VarDumper::setHandler(function ($var) use ($cloner, $dumper) {
        $dumper->dump($cloner->cloneVar($var));
    });

.. note::

    The second argument of :class:`Symfony\\Component\\VarDumper\\Dumper\\ServerDumper`
    is a :class:`Symfony\\Component\\VarDumper\\Dumper\\DataDumperInterface` instance
    used as a fallback when the server is unreachable. The third argument are the
    context providers, which allow to gather some info about the context in which the
    data was dumped. The built-in context providers are: ``cli``, ``request`` and ``source``.

Then you can use the following command to start a server out-of-the-box:

.. code-block:: terminal

     $ ./vendor/bin/var-dump-server
       [OK] Server listening on tcp://127.0.0.1:9912

DebugBundle and Twig Integration
--------------------------------

The DebugBundle allows greater integration of this component into Symfony
applications.

Since generating (even debug) output in the controller or in the model
of your application may just break it by e.g. sending HTTP headers or
corrupting your view, the bundle configures the ``dump()`` function so that
variables are dumped in the web debug toolbar.

But if the toolbar cannot be displayed because you e.g. called
``die()``/``exit()``/``dd()`` or a fatal error occurred, then dumps are written
on the regular output.

In a Twig template, two constructs are available for dumping a variable.
Choosing between both is mostly a matter of personal taste, still:

* ``{% dump foo.bar %}`` is the way to go when the original template output
  shall not be modified: variables are not dumped inline, but in the web
  debug toolbar;
* on the contrary, ``{{ dump(foo.bar) }}`` dumps inline and thus may or not
  be suited to your use case (e.g. you shouldn't use it in an HTML
  attribute or a ``<script>`` tag).

This behavior can be changed by configuring the ``debug.dump_destination``
option. Read more about this and other options in
:doc:`the DebugBundle configuration reference </reference/configuration/debug>`.

.. tip::

    If the dumped contents are complex, consider using the local search box to
    look for specific variables or values. First, click anywhere on the dumped
    contents and then press ``Ctrl. + F`` or ``Cmd. + F`` to make the local
    search box appear. All the common shortcuts to navigate the search results
    are supported (``Ctrl. + G`` or ``Cmd. + G``, ``F3``, etc.) When
    finished, press ``Esc.`` to hide the box again.

Using the VarDumper Component in your PHPUnit Test Suite
--------------------------------------------------------

The VarDumper component provides
:class:`a trait <Symfony\\Component\\VarDumper\\Test\\VarDumperTestTrait>`
that can help writing some of your tests for PHPUnit.

This will provide you with two new assertions:

:method:`Symfony\\Component\\VarDumper\\Test\\VarDumperTestTrait::assertDumpEquals`
    verifies that the dump of the variable given as the second argument matches
    the expected dump provided as the first argument.

:method:`Symfony\\Component\\VarDumper\\Test\\VarDumperTestTrait::assertDumpMatchesFormat`
    is like the previous method but accepts placeholders in the expected dump,
    based on the ``assertStringMatchesFormat()`` method provided by PHPUnit.

Example::

    use PHPUnit\Framework\TestCase;

    class ExampleTest extends TestCase
    {
        use \Symfony\Component\VarDumper\Test\VarDumperTestTrait;

        public function testWithDumpEquals()
        {
            $testedVar = [123, 'foo'];

            $expectedDump = <<<EOTXT
    array:2 [
      0 => 123
      1 => "foo"
    ]
    EOTXT;

            // if the first argument is a string, it must be the whole expected dump
            $this->assertDumpEquals($expectedDump, $testedVar);

            // if the first argument is not a string, assertDumpEquals() dumps it
            // and compares it with the dump of the second argument
            $this->assertDumpEquals($testedVar, $testedVar);
        }
    }

Dump Examples and Output
------------------------

For simple variables, reading the output should be straightforward.
Here are some examples showing first a variable defined in PHP,
then its dump representation::

    $var = [
        'a simple string' => "in an array of 5 elements",
        'a float' => 1.0,
        'an integer' => 1,
        'a boolean' => true,
        'an empty array' => [],
    ];
    dump($var);

.. image:: /_images/components/var_dumper/01-simple.png

.. note::

    The gray arrow is a toggle button for hiding/showing children of
    nested structures.

.. code-block:: php

    $var = "This is a multi-line string.\n";
    $var .= "Hovering a string shows its length.\n";
    $var .= "The length of UTF-8 strings is counted in terms of UTF-8 characters.\n";
    $var .= "Non-UTF-8 strings length are counted in octet size.\n";
    $var .= "Because of this `\xE9` octet (\\xE9),\n";
    $var .= "this string is not UTF-8 valid, thus the `b` prefix.\n";
    dump($var);

.. image:: /_images/components/var_dumper/02-multi-line-str.png

.. code-block:: php

    class PropertyExample
    {
        public $publicProperty = 'The `+` prefix denotes public properties,';
        protected $protectedProperty = '`#` protected ones and `-` private ones.';
        private $privateProperty = 'Hovering a property shows a reminder.';
    }

    $var = new PropertyExample();
    dump($var);

.. image:: /_images/components/var_dumper/03-object.png

.. note::

    `#14` is the internal object handle. It allows comparing two
    consecutive dumps of the same object.

.. code-block:: php

    class DynamicPropertyExample
    {
        public $declaredProperty = 'This property is declared in the class definition';
    }

    $var = new DynamicPropertyExample();
    $var->undeclaredProperty = 'Runtime added dynamic properties have `"` around their name.';
    dump($var);

.. image:: /_images/components/var_dumper/04-dynamic-property.png

.. code-block:: php

    class ReferenceExample
    {
        public $info = "Circular and sibling references are displayed as `#number`.\nHovering them highlights all instances in the same dump.\n";
    }
    $var = new ReferenceExample();
    $var->aCircularReference = $var;
    dump($var);

.. image:: /_images/components/var_dumper/05-soft-ref.png

.. code-block:: php

    $var = new \ErrorException(
        "For some objects, properties have special values\n"
        ."that are best represented as constants, like\n"
        ."`severity` below. Hovering displays the value (`2`).\n",
        0,
        E_WARNING
    );
    dump($var);

.. image:: /_images/components/var_dumper/06-constants.png

.. code-block:: php

    $var = [];
    $var[0] = 1;
    $var[1] =& $var[0];
    $var[1] += 1;
    $var[2] = ["Hard references (circular or sibling)"];
    $var[3] =& $var[2];
    $var[3][] = "are dumped using `&number` prefixes.";
    dump($var);

.. image:: /_images/components/var_dumper/07-hard-ref.png

.. code-block:: php

    $var = new \ArrayObject();
    $var[] = "Some resources and special objects like the current";
    $var[] = "one are sometimes best represented using virtual";
    $var[] = "properties that describe their internal state.";
    dump($var);

.. image:: /_images/components/var_dumper/08-virtual-property.png

.. code-block:: php

    $var = new AcmeController(
        "When a dump goes over its maximum items limit,\n"
        ."or when some special objects are encountered,\n"
        ."children can be replaced by an ellipsis and\n"
        ."optionally followed by a number that says how\n"
        ."many have been removed; `9` in this case.\n"
    );
    dump($var);

.. image:: /_images/components/var_dumper/09-cut.png

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    var_dumper/*

.. _Packagist: https://packagist.org/packages/symfony/var-dumper
