.. index::
    single: Class Loader; DebugClassLoader
    
Debugging a Class Loader
========================

When a class isn't found by the registered autoloaders you can use the
:class:`Symfony\\Component\\ClassLoader\\DebugClassLoader`. All autoloaders
which implement a ``findFile()`` method are replaced with a ``DebugClassLoader``
wrapper. It throws an exception if a file is found but does not declare the
class.

Using the ``DebugClassLoader`` is as easy as calling its static :method:`DebugClassLoader::enable`
method::

    use Symfony\Component\ClassLoader\DebugClassLoader;
    
    DebugClassLoader::enable();
