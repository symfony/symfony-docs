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

- :doc:`Install it via Composer </components/using_components>` (``symfony/var-dumper`` on `Packagist`_);
- Use the official Git repository (https://github.com/symfony/var-dumper).

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

``dump()`` is just a thin wrapper for
:method:`VarDumper::dump() <Symfony\\Component\\VarDumper\\VarDumper::dump>`
so can you also use it directly.
You can change the behavior of this function by calling
:method:`VarDumper::setHandler($callable) <Symfony\\Component\\VarDumper\\VarDumper::setHandler>`:
calls to ``dump()`` will then be forwarded to ``$callable``, given as first argument.

.. _Packagist: https://packagist.org/packages/symfony/var-dumper
