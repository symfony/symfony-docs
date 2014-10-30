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
* Use the official Git repository (https://github.com/symfony/var-dumper).

The dump() Function
-------------------

The VarDumper component creates a global ``dump()`` function that you can
use instead of e.g. :phpfunction:`var_dump`. By using it, you'll gain:

* Per object and resource types specialized view to e.g. filter out
  Doctrine internals while dumping a single proxy entity, or get more
  insight on opened files with :phpfunction:`stream_get_meta_data()`;
* Configurable output formats: HTML or colored command line output;
* Ability to dump internal references, either soft ones (objects or
  resources) or hard ones (``=&`` on arrays or objects properties).
  Repeated occurrences of the same object/array/resource won't appear
  again and again anymore. Moreover, you'll be able to inspect the
  reference structure of your data;
* Ability to operate in the context of an output buffering handler.

``dump()`` is just a thin wrapper and more convenient way to call
:method:`VarDumper::dump() <Symfony\\Component\\VarDumper\\VarDumper::dump>`.
You can change the behavior of this function by calling
:method:`VarDumper::setHandler($callable) <Symfony\\Component\\VarDumper\\VarDumper::setHandler>`:
calls to ``dump()`` will then be forwarded to ``$callable``.

Output Format and Destination
-----------------------------

If you read the `advanced documentation <advanced>`, you'll learn how to
change the format or redirect the output to wherever you want.

By default, these are selected based on your current PHP SAPI:

* On the command line (CLI SAPI), the output is written on ``STDERR``. This
  can be surprising to some because this bypasses PHP's output buffering
  mechanism. On the other hand, it give the possibility to easily split
  dumps from regular output by using pipe redirection;
* On other SAPIs, dumps are written as HTML on the regular output.

DebugBundle and Twig Integration
--------------------------------

The ``DebugBundle`` allows greater integration of the component into the
Symfony full stack framework. It is enabled by default in the dev
environement of the standard edition since version 2.6.

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

By default for nested variables, dumps are limited to a subset of their
original value. You can configure the limits in terms of:

* maximum number of items to dump,
* maximum string length before truncation.

.. configuration-block::

    .. code-block:: yaml

        debug:
           max_items: 250
           max_string_length: -1

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/debug"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/debug http://symfony.com/schema/dic/debug/debug-1.0.xsd">

            <config max-items="250" max-string-length="-1" />
        </container>

Reading a Dump
--------------

For simple variables, reading the output should be straightforward::

    dump(array(true, 1.1, "string"));

.. _Packagist: https://packagist.org/packages/symfony/var-dumper
