.. index::
    single: ClassLoader; DebugClassLoader
    
Debugging a Class Loader
========================

The :class:`Symfony\\Component\\ClassLoader\\DebugClassLoader` attempts to
throw more helpful exceptions when a class isn't found by the registered
autoloaders. All autoloaders that implement a ``findFile()`` method are replaced
with a ``DebugClassLoader`` wrapper.

Using the ``DebugClassLoader`` is as easy as calling its static
:method:`Symfony\\Component\\ClassLoader\\DebugClassLoader::enable` method::

    use Symfony\Component\ClassLoader\DebugClassLoader;
    
    DebugClassLoader::enable();
