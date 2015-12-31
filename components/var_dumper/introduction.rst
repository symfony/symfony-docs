.. index::
   single: VarDumper
   single: Components; VarDumper

The VarDumper Component
=======================

    The VarDumper component provides mechanisms for walking through any
    arbitrary PHP variable. Built on top, it provides a better ``dump()``
    function that you can use instead of :phpfunction:`var_dump`.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/var-dumper`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/var-dumper).

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

By default, the output format and destination are selected based on your
current PHP SAPI:

* On the command line (CLI SAPI), the output is written on ``STDOUT``. This
  can be surprising to some because this bypasses PHP's output buffering
  mechanism;
* On other SAPIs, dumps are written as HTML in the regular output.

.. note::

    If you want to catch the dump output as a string, please read the
    `advanced documentation <advanced>`_ which contains examples of it.
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

DebugBundle and Twig Integration
--------------------------------

The DebugBundle allows greater integration of the component into the Symfony
full-stack framework. It is enabled by default in the *dev* and *test*
environment of the Symfony Standard Edition.

Since generating (even debug) output in the controller or in the model
of your application may just break it by e.g. sending HTTP headers or
corrupting your view, the bundle configures the ``dump()`` function so that
variables are dumped in the web debug toolbar.

But if the toolbar can not be displayed because you e.g. called ``die``/``exit``
or a fatal error occurred, then dumps are written on the regular output.

In a Twig template, two constructs are available for dumping a variable.
Choosing between both is mostly a matter of personal taste, still:

* ``{% dump foo.bar %}`` is the way to go when the original template output
  shall not be modified: variables are not dumped inline, but in the web
  debug toolbar;
* on the contrary, ``{{ dump(foo.bar) }}`` dumps inline and thus may or not
  be suited to your use case (e.g. you shouldn't use it in an HTML
  attribute or a ``<script>`` tag).

This behavior can be changed by configuring the ``dump.dump_destination``
option. Read more about this and other options in
:doc:`the DebugBundle configuration reference </reference/configuration/debug>`.

Using the VarDumper Component in your PHPUnit Test Suite
--------------------------------------------------------

.. versionadded:: 2.7
    The :class:`Symfony\\Component\\VarDumper\\Test\\VarDumperTestTrait` was
    introduced in Symfony 2.7.

The VarDumper component provides
:class:`a trait <Symfony\\Component\\VarDumper\\Test\\VarDumperTestTrait>`
that can help writing some of your tests for PHPUnit.

This will provide you with two new assertions:

:method:`Symfony\\Component\\VarDumper\\Test\\VarDumperTestTrait::assertDumpEquals`
    verifies that the dump of the variable given as the second argument matches
    the expected dump provided as a string in the first argument.

:method:`Symfony\\Component\\VarDumper\\Test\\VarDumperTestTrait::assertDumpMatchesFormat`
    is like the previous method but accepts placeholders in the expected dump,
    based on the ``assertStringMatchesFormat`` method provided by PHPUnit.

Example::

    class ExampleTest extends \PHPUnit_Framework_TestCase
    {
        use \Symfony\Component\VarDumper\Test\VarDumperTestTrait;

        public function testWithDumpEquals()
        {
            $testedVar = array(123, 'foo');

            $expectedDump = <<<EOTXT
    array:2 [
      0 => 123
      1 => "foo"
    ]
    EOTXT;

            $this->assertDumpEquals($expectedDump, $testedVar);
        }
    }

Dump Examples and Output
------------------------

For simple variables, reading the output should be straightforward.
Here are some examples showing first a variable defined in PHP,
then its dump representation::

    $var = array(
        'a simple string' => "in an array of 5 elements",
        'a float' => 1.0,
        'an integer' => 1,
        'a boolean' => true,
        'an empty array' => array(),
    );
    dump($var);

.. image:: /images/components/var_dumper/01-simple.png

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

.. image:: /images/components/var_dumper/02-multi-line-str.png

.. code-block:: php

    class PropertyExample
    {
        public $publicProperty = 'The `+` prefix denotes public properties,';
        protected $protectedProperty = '`#` protected ones and `-` private ones.';
        private $privateProperty = 'Hovering a property shows a reminder.';
    }

    $var = new PropertyExample();
    dump($var);

.. image:: /images/components/var_dumper/03-object.png

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

.. image:: /images/components/var_dumper/04-dynamic-property.png

.. code-block:: php

    class ReferenceExample
    {
        public $info = "Circular and sibling references are displayed as `#number`.\nHovering them highlights all instances in the same dump.\n";
    }
    $var = new ReferenceExample();
    $var->aCircularReference = $var;
    dump($var);

.. image:: /images/components/var_dumper/05-soft-ref.png

.. code-block:: php

    $var = new \ErrorException(
        "For some objects, properties have special values\n"
        ."that are best represented as constants, like\n"
        ."`severity` below. Hovering displays the value (`2`).\n",
        0,
        E_WARNING
    );
    dump($var);

.. image:: /images/components/var_dumper/06-constants.png

.. code-block:: php

    $var = array();
    $var[0] = 1;
    $var[1] =& $var[0];
    $var[1] += 1;
    $var[2] = array("Hard references (circular or sibling)");
    $var[3] =& $var[2];
    $var[3][] = "are dumped using `&number` prefixes.";
    dump($var);

.. image:: /images/components/var_dumper/07-hard-ref.png

.. code-block:: php

    $var = new \ArrayObject();
    $var[] = "Some resources and special objects like the current";
    $var[] = "one are sometimes best represented using virtual";
    $var[] = "properties that describe their internal state.";
    dump($var);

.. image:: /images/components/var_dumper/08-virtual-property.png

.. code-block:: php

    $var = new AcmeController(
        "When a dump goes over its maximum items limit,\n"
        ."or when some special objects are encountered,\n"
        ."children can be replaced by an ellipsis and\n"
        ."optionally followed by a number that says how\n"
        ."many have been removed; `9` in this case.\n"
    );
    dump($var);

.. image:: /images/components/var_dumper/09-cut.png

.. _Packagist: https://packagist.org/packages/symfony/var-dumper
