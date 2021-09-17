.. index::
   single: VarDumper
   single: Components; VarDumper

Advanced Usage of the VarDumper Component
=========================================

The ``dump()`` function is just a thin wrapper and a more convenient way to call
:method:`VarDumper::dump() <Symfony\\Component\\VarDumper\\VarDumper::dump>`.
You can change the behavior of this function by calling
:method:`VarDumper::setHandler($callable) <Symfony\\Component\\VarDumper\\VarDumper::setHandler>`.
Calls to ``dump()`` will then be forwarded to ``$callable``.

By adding a handler, you can customize the `Cloners`_, `Dumpers`_ and `Casters`_
as explained below. A simple implementation of a handler function might look
like this::

    use Symfony\Component\VarDumper\Cloner\VarCloner;
    use Symfony\Component\VarDumper\Dumper\CliDumper;
    use Symfony\Component\VarDumper\Dumper\HtmlDumper;
    use Symfony\Component\VarDumper\VarDumper;

    VarDumper::setHandler(function ($var) {
        $cloner = new VarCloner();
        $dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();

        $dumper->dump($cloner->cloneVar($var));
    });

Cloners
-------

A cloner is used to create an intermediate representation of any PHP variable.
Its output is a :class:`Symfony\\Component\\VarDumper\\Cloner\\Data`
object that wraps this representation.

You can create a ``Data`` object this way::

    use Symfony\Component\VarDumper\Cloner\VarCloner;

    $cloner = new VarCloner();
    $data = $cloner->cloneVar($myVar);
    // this is commonly then passed to the dumper
    // see the example at the top of this page
    // $dumper->dump($data);

Whatever the cloned data structure, resulting ``Data`` objects are always
serializable.

A cloner applies limits when creating the representation, so that one
can represent only a subset of the cloned variable.
Before calling :method:`Symfony\\Component\\VarDumper\\Cloner\\VarCloner::cloneVar`,
you can configure these limits:

:method:`Symfony\\Component\\VarDumper\\Cloner\\VarCloner::setMaxItems`
    Configures the maximum number of items that will be cloned
    *past the minimum nesting depth*. Items are counted using a breadth-first
    algorithm so that lower level items have higher priority than deeply nested
    items. Specifying ``-1`` removes the limit.

:method:`Symfony\\Component\\VarDumper\\Cloner\\VarCloner::setMinDepth`
    Configures the minimum tree depth where we are guaranteed to clone
    all the items. After this depth is reached, only ``setMaxItems``
    items will be cloned. The default value is ``1``, which is consistent
    with older Symfony versions.

:method:`Symfony\\Component\\VarDumper\\Cloner\\VarCloner::setMaxString`
    Configures the maximum number of characters that will be cloned before
    cutting overlong strings.  Specifying ``-1`` removes the limit.

Before dumping it, you can further limit the resulting
:class:`Symfony\\Component\\VarDumper\\Cloner\\Data` object using the following methods:

:method:`Symfony\\Component\\VarDumper\\Cloner\\Data::withMaxDepth`
    Limits dumps in the depth dimension.

:method:`Symfony\\Component\\VarDumper\\Cloner\\Data::withMaxItemsPerDepth`
    Limits the number of items per depth level.

:method:`Symfony\\Component\\VarDumper\\Cloner\\Data::withRefHandles`
    Removes internal objects' handles for sparser output (useful for tests).

:method:`Symfony\\Component\\VarDumper\\Cloner\\Data::seek`
    Selects only sub-parts of already cloned arrays, objects or resources.

Unlike the previous limits on cloners that remove data on purpose, these can
be changed back and forth before dumping since they do not affect the
intermediate representation internally.

.. note::

    When no limit is applied, a :class:`Symfony\\Component\\VarDumper\\Cloner\\Data`
    object is as accurate as the native :phpfunction:`serialize` function,
    and thus could be used for purposes beyond debugging.

Dumpers
-------

A dumper is responsible for outputting a string representation of a PHP variable,
using a :class:`Symfony\\Component\\VarDumper\\Cloner\\Data` object as input.
The destination and the formatting of this output vary with dumpers.

This component comes with an :class:`Symfony\\Component\\VarDumper\\Dumper\\HtmlDumper`
for HTML output and a :class:`Symfony\\Component\\VarDumper\\Dumper\\CliDumper`
for optionally colored command line output.

For example, if you want to dump some ``$variable``, do::

    use Symfony\Component\VarDumper\Cloner\VarCloner;
    use Symfony\Component\VarDumper\Dumper\CliDumper;

    $cloner = new VarCloner();
    $dumper = new CliDumper();

    $dumper->dump($cloner->cloneVar($variable));

By using the first argument of the constructor, you can select the output
stream where the dump will be written. By default, the ``CliDumper`` writes
on ``php://stdout`` and the ``HtmlDumper`` on ``php://output``. But any PHP
stream (resource or URL) is acceptable.

Instead of a stream destination, you can also pass it a ``callable`` that
will be called repeatedly for each line generated by a dumper. This
callable can be configured using the first argument of a dumper's constructor,
but also using the
:method:`Symfony\\Component\\VarDumper\\Dumper\\AbstractDumper::setOutput`
method or the second argument of the
:method:`Symfony\\Component\\VarDumper\\Dumper\\AbstractDumper::dump` method.

For example, to get a dump as a string in a variable, you can do::

    use Symfony\Component\VarDumper\Cloner\VarCloner;
    use Symfony\Component\VarDumper\Dumper\CliDumper;

    $cloner = new VarCloner();
    $dumper = new CliDumper();
    $output = '';

    $dumper->dump(
        $cloner->cloneVar($variable),
        function ($line, $depth) use (&$output) {
            // A negative depth means "end of dump"
            if ($depth >= 0) {
                // Adds a two spaces indentation to the line
                $output .= str_repeat('  ', $depth).$line."\n";
            }
        }
    );

    // $output is now populated with the dump representation of $variable

Another option for doing the same could be::

    use Symfony\Component\VarDumper\Cloner\VarCloner;
    use Symfony\Component\VarDumper\Dumper\CliDumper;

    $cloner = new VarCloner();
    $dumper = new CliDumper();
    $output = fopen('php://memory', 'r+b');

    $dumper->dump($cloner->cloneVar($variable), $output);
    $output = stream_get_contents($output, -1, 0);

    // $output is now populated with the dump representation of $variable

.. tip::

    You can pass ``true`` to the second argument of the
    :method:`Symfony\\Component\\VarDumper\\Dumper\\AbstractDumper::dump`
    method to make it return the dump as a string::

        $output = $dumper->dump($cloner->cloneVar($variable), true);

Dumpers implement the :class:`Symfony\\Component\\VarDumper\\Dumper\\DataDumperInterface`
interface that specifies the
:method:`dump(Data $data) <Symfony\\Component\\VarDumper\\Dumper\\DataDumperInterface::dump>`
method. They also typically implement the
:class:`Symfony\\Component\\VarDumper\\Cloner\\DumperInterface` that frees
them from re-implementing the logic required to walk through a
:class:`Symfony\\Component\\VarDumper\\Cloner\\Data` object's internal structure.

The :class:`Symfony\\Component\\VarDumper\\Dumper\\HtmlDumper` uses a dark
theme by default. Use the :method:`Symfony\\Component\\VarDumper\\Dumper\\HtmlDumper::setTheme`
method to use a light theme::

    // ...
    $htmlDumper->setTheme('light');

The :class:`Symfony\\Component\\VarDumper\\Dumper\\HtmlDumper` limits string
length and nesting depth of the output to make it more readable. These options
can be overridden by the third optional parameter of the
:method:`dump(Data $data) <Symfony\\Component\\VarDumper\\Dumper\\DataDumperInterface::dump>`
method::

    use Symfony\Component\VarDumper\Dumper\HtmlDumper;

    $output = fopen('php://memory', 'r+b');

    $dumper = new HtmlDumper();
    $dumper->dump($var, $output, [
        // 1 and 160 are the default values for these options
        'maxDepth' => 1,
        'maxStringLength' => 160,
    ]);

The output format of a dumper can be fine tuned by the two flags
``DUMP_STRING_LENGTH`` and ``DUMP_LIGHT_ARRAY`` which are passed as a bitmap
in the third constructor argument. They can also be set via environment
variables when using
:method:`assertDumpEquals($dump, $data, $filter, $message) <Symfony\\Component\\VarDumper\\Test\\VarDumperTestTrait::assertDumpEquals>`
during unit testing.

The ``$filter`` argument of ``assertDumpEquals()`` can be used to pass a
bit field of ``Caster::EXCLUDE_*`` constants and influences the expected
output produced by the different casters.

If ``DUMP_STRING_LENGTH`` is set, then the length of a string is displayed
next to its content::

    use Symfony\Component\VarDumper\Cloner\VarCloner;
    use Symfony\Component\VarDumper\Dumper\AbstractDumper;
    use Symfony\Component\VarDumper\Dumper\CliDumper;

    $varCloner = new VarCloner();
    $var = ['test'];

    $dumper = new CliDumper();
    echo $dumper->dump($varCloner->cloneVar($var), true);

    // array:1 [
    //   0 => "test"
    // ]

    $dumper = new CliDumper(null, null, AbstractDumper::DUMP_STRING_LENGTH);
    echo $dumper->dump($varCloner->cloneVar($var), true);

    // (added string length before the string)
    // array:1 [
    //   0 => (4) "test"
    // ]

If ``DUMP_LIGHT_ARRAY`` is set, then arrays are dumped in a shortened format
similar to PHP's short array notation::

    use Symfony\Component\VarDumper\Cloner\VarCloner;
    use Symfony\Component\VarDumper\Dumper\AbstractDumper;
    use Symfony\Component\VarDumper\Dumper\CliDumper;

    $varCloner = new VarCloner();
    $var = ['test'];

    $dumper = new CliDumper();
    echo $dumper->dump($varCloner->cloneVar($var), true);

    // array:1 [
    //   0 => "test"
    // ]

    $dumper = new CliDumper(null, null, AbstractDumper::DUMP_LIGHT_ARRAY);
    echo $dumper->dump($varCloner->cloneVar($var), true);

    // (no more array:1 prefix)
    // [
    //   0 => "test"
    // ]

If you would like to use both options, then you can combine them by
using the logical OR operator ``|``::

    use Symfony\Component\VarDumper\Cloner\VarCloner;
    use Symfony\Component\VarDumper\Dumper\AbstractDumper;
    use Symfony\Component\VarDumper\Dumper\CliDumper;

    $varCloner = new VarCloner();
    $var = ['test'];

    $dumper = new CliDumper(null, null, AbstractDumper::DUMP_STRING_LENGTH | AbstractDumper::DUMP_LIGHT_ARRAY);
    echo $dumper->dump($varCloner->cloneVar($var), true);

    // [
    //   0 => (4) "test"
    // ]

Casters
-------

Objects and resources nested in a PHP variable are "cast" to arrays in the
intermediate :class:`Symfony\\Component\\VarDumper\\Cloner\\Data`
representation. You can customize the array representation for each object/resource
by hooking a Caster into this process. The component already includes many
casters for base PHP classes and other common classes.

If you want to build your own Caster, you can register one before cloning
a PHP variable. Casters are registered using either a Cloner's constructor
or its ``addCasters()`` method::

    use Symfony\Component\VarDumper\Cloner\VarCloner;

    $myCasters = [...];
    $cloner = new VarCloner($myCasters);

    // or

    $cloner->addCasters($myCasters);

The provided ``$myCasters`` argument is an array that maps a class,
an interface or a resource type to a callable::

    $myCasters = [
        'FooClass' => $myFooClassCallableCaster,
        ':bar resource' => $myBarResourceCallableCaster,
    ];

As you can notice, resource types are prefixed by a ``:`` to prevent
colliding with a class name.

Because an object has one main class and potentially many parent classes
or interfaces, many casters can be applied to one object. In this case,
casters are called one after the other, starting from casters bound to the
interfaces, the parents classes and then the main class. Several casters
can also be registered for the same resource type/class/interface.
They are called in registration order.

Casters are responsible for returning the properties of the object or resource
being cloned in an array. They are callables that accept five arguments:

* the object or resource being casted;
* an array modeled for objects after PHP's native ``(array)`` cast operator;
* a :class:`Symfony\\Component\\VarDumper\\Cloner\\Stub` object
  representing the main properties of the object (class, type, etc.);
* true/false when the caster is called nested in a structure or not;
* A bit field of :class:`Symfony\\Component\\VarDumper\\Caster\\Caster` ``::EXCLUDE_*``
  constants.

Here is a simple caster not doing anything::

    use Symfony\Component\VarDumper\Cloner\Stub;

    function myCaster($object, $array, Stub $stub, $isNested, $filter)
    {
        // ... populate/alter $array to your needs

        return $array;
    }

For objects, the ``$array`` parameter comes pre-populated using PHP's native
``(array)`` casting operator or with the return value of ``$object->__debugInfo()``
if the magic method exists. Then, the return value of one Caster is given
as the array argument to the next Caster in the chain.

When casting with the ``(array)`` operator, PHP prefixes protected properties
with a ``\0*\0`` and private ones with the class owning the property. For example,
``\0Foobar\0`` will be the prefix for all private properties of objects of
type Foobar. Casters follow this convention and add two more prefixes: ``\0~\0``
is used for virtual properties and ``\0+\0`` for dynamic ones (runtime added
properties not in the class declaration).

.. note::

    Although you can, it is advised to not alter the state of an object
    while casting it in a Caster.

.. tip::

    Before writing your own casters, you should check the existing ones.

Adding Semantics with Metadata
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Since casters are hooked on specific classes or interfaces, they know about the
objects they manipulate. By altering the ``$stub`` object (the third argument of
any caster), one can transfer this knowledge to the resulting ``Data`` object,
thus to dumpers. To help you do this (see the source code for how it works),
the component comes with a set of wrappers for common additional semantics. You
can use:

* :class:`Symfony\\Component\\VarDumper\\Caster\\ConstStub` to wrap a value that is
  best represented by a PHP constant;
* :class:`Symfony\\Component\\VarDumper\\Caster\\ClassStub` to wrap a PHP identifier
  (*i.e.* a class name, a method name, an interface, *etc.*);
* :class:`Symfony\\Component\\VarDumper\\Caster\\CutStub` to replace big noisy
  objects/strings/*etc.* by ellipses;
* :class:`Symfony\\Component\\VarDumper\\Caster\\CutArrayStub` to keep only some
  useful keys of an array;
* :class:`Symfony\\Component\\VarDumper\\Caster\\ImgStub` to wrap an image;
* :class:`Symfony\\Component\\VarDumper\\Caster\\EnumStub` to wrap a set of virtual
  values (*i.e.* values that do not exist as properties in the original PHP data
  structure, but are worth listing alongside with real ones);
* :class:`Symfony\\Component\\VarDumper\\Caster\\LinkStub` to wrap strings that can
  be turned into links by dumpers;
* :class:`Symfony\\Component\\VarDumper\\Caster\\TraceStub` and their
* :class:`Symfony\\Component\\VarDumper\\Caster\\FrameStub` and
* :class:`Symfony\\Component\\VarDumper\\Caster\\ArgsStub` relatives to wrap PHP
  traces (used by :class:`Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster`).

For example, if you know that your ``Product`` objects have a ``brochure`` property
that holds a file name or a URL, you can wrap them in a ``LinkStub`` to tell
``HtmlDumper`` to make them clickable::

    use Symfony\Component\VarDumper\Caster\LinkStub;
    use Symfony\Component\VarDumper\Cloner\Stub;

    function ProductCaster(Product $object, $array, Stub $stub, $isNested, $filter = 0)
    {
        $array['brochure'] = new LinkStub($array['brochure']);

        return $array;
    }
