.. index::
   single: VarDumper
   single: Components; VarDumper

The VarDumper Component
=======================

    The VarDumper component provides mechanisms for walking through any
    arbitrary PHP variable. Built on top, it provides a better ``dump()``
    function that you can use instead of :phpfunction:`var_dump`.

.. versionadded:: 2.6
    The VarDumper component was introduced in Symfony 2.6.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/var-dumper`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/VarDumper).

The dump() function
-------------------

The VarDumper component creates a global ``dump()`` function that is
configured out of the box: HTML or CLI output is automatically selected based
on the current PHP SAPI.

The advantages of this function are:

    - per object and resource types specialized view to e.g. filter out
      Doctrine internals while dumping a single proxy entity, or get more
      insight on opened files with :phpfunction:`stream_get_meta_data()`.
    - configurable output formats: HTML or colored command line output.
    - ability to dump internal references, either soft ones (objects or
      resources) or hard ones (``=&`` on arrays or objects properties).
      Repeated occurrences of the same object/array/resource won't appear
      again and again anymore. Moreover, you'll be able to inspect the
      reference structure of your data.
    - ability to operate in the context of an output buffering handler.

``dump()`` is just a thin wrapper for :method:`VarDumper::dump()
<Symfony\\Component\\VarDumper\\VarDumper::dump>` so can you also use it directly.
You can change the behavior of this function by calling
:method:`VarDumper::setHandler($callable) <Symfony\\Component\\VarDumper\\VarDumper::setHandler>`:
calls to ``dump()`` will then be forwarded to ``$callable``, given as first argument.

Advanced Usage
--------------

Cloners
~~~~~~~

A cloner is used to create an intermediate representation of any PHP variable.
Its output is a :class:`Data <Symfony\\Component\\VarDumper\\Cloner\\Data>`
object that wraps this representation. A cloner also applies limits when
creating the representation, so that the corresponding Data object could
represent only a subset of the cloned variable.

You can create a Data object this way::

    $cloner = new PhpCloner();
    $data = $cloner->cloneVar($myVar);

Before cloning, you can configure the limits with::

    $cloner->setMaxItems($number);
    $cloner->setMaxString($number);

They will be applied when calling ``->cloneVar()`` afterwards.

Casters
~~~~~~~

Objects and resources nested in a PHP variable are casted to arrays in the
intermediate Data representation. You can tweak the array representation for
each object/resource by hooking a Caster into this process. The component
already includes many casters for base PHP classes and other common classes.

If you want to build your own Caster, you can register one before cloning
a PHP variable. Casters are registered using either a Cloner's constructor
or its ``addCasters()`` method::

    $myCasters = array(...);
    $cloner = new PhpCloner($myCasters);

    // or

    $cloner->addCasters($myCasters);

The provided ``$myCasters`` argument is an array that maps a class,
an interface or a resource type to a callable::

    $myCasters = array(
        'FooClass' => $myFooClassCallableCaster,
        ':bar resource' => $myBarResourceCallableCaster,
    );

As you can notice, resource types are prefixed by a ``:`` to prevent
colliding with a class name.

Because an object has one main class and potentially many parent classes
or interfaces, many casters can be applied to one object. In this case,
casters are called one after the other, starting from casters bound to the
interfaces, the parents classes and then the main class. Several casters
can also be registered for the same resource type/class/interface.
They are called in registration order.

Casters are responsible for returning the properties of the object orresource
being cloned in an array. They are callables that accept four arguments:

    - the object or resource being casted,
    - an array modelled for objects after PHP's native ``(array)`` cast operator,
    - a :class:`Stub <Sumfony\\Component\\VarDumper\\Cloner\\Stub>` object
      representing the main properties of the object (class, type, etc.),
    - true/false when the caster is called nested is a structure or not.

Here is a simple caster not doing anything::

    function myCaster($object, $array, $stub, $isNested)
    {
        // ... populate/alter $array to your needs

        return $array;
    }

For objects, the ``$array`` parameter comes pre-populated with PHP's native
``(array)`` casting operator or with the return value of ``$object->__debugInfo()``
if the magic method exists. Then, the return value of one Caster is given
as argument to the next Caster in the chain.

When casting with the ``(array)`` operator, PHP prefixes protected properties
with a ``\0*\0`` and private ones with the class owning the property:
e.g. ``\0Foobar\0`` prefixes all private properties of objects of type Foobar.
Casters follow this convention and add two more prefixes: ``\0~\0`` is used
for virtual properties and ``\0+\0`` for dynamic ones (runtime added
properties not in the class declaration).

.. note::

    Although you can, it is best advised not to alter the state of an object
    while casting it in a Caster.

Dumpers
~~~~~~~

.. _Packagist: https://packagist.org/packages/symfony/var-dumper
