.. index::
    single: Class Loader; DebugClassLoader
    single: Debug; DebugClassLoader

Debugging a Class Loader
========================

The :class:`Symfony\\Component\\Debug\\DebugClassLoader` attempts to
throw more helpful exceptions when a class isn't found by the registered
autoloaders. All autoloaders that implement a ``findFile()`` method are replaced
with a ``DebugClassLoader`` wrapper.

Using the ``DebugClassLoader`` is as easy as calling its static
:method:`Symfony\\Component\\Debug\\DebugClassLoader::enable` method::

    use Symfony\Component\Debug\DebugClassLoader;

    DebugClassLoader::enable();
