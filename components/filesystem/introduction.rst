.. index::
    single: Filesystem
    single: Components; Filesystem

The Filesystem Component
========================

    The Filesystem component provides an easy API to
    interact with the underlying filesystem.
    
Using an abstraction layer above the filesystem eases the development,
especially if different filesystems are used for development/production. 
It also makes it easier to test code that interacts with the filesystem as 
the component can be easily mocked.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Filesystem);
* :doc:`Install it via Composer</components/using_components>` (``symfony/filesystem`` on `Packagist`_).

Using the filesystem
--------------------

To use the component, you need to create a `Filesystem` object::

    use Symfony\Component\Filesystem\Filesystem;

    $filesystem = new Filesystem();
    
The class provides different methods for both normal filesystem interaction
like copying a file and some addition methods like checking if a path is an 
absolute path::

    $filesystem->copy('source.txt', 'dest.txt');
    
    $filesystem->isAbsolutePath('/var/www');
    
Recursive operations
~~~~~~~~~~~~~~~~~~~~

The Filesystem Component provides different methods that are working
recursive, making it more easy to use than the build-in functions of PHP::

    $filesystem->mkdir('/var/www/symfony/default'); // This works even if /var/www/symfony does not exist
    
Making use of Traversable
~~~~~~~~~~~~~~~~~~~~~~~~~

Many methods are implemented in a way that let you provide both a string 
representing a file or folder, an array or a `\Traversable` object::

    $filesystem->exists('/var/www/symfony/default');
    
    $someFiles = array('file1.txt', 'file2.txt', 'dir/subdir');
    $filesystem->chmod($someFiles, 0777);
    
    $traversable = new \ArrayObject($someFiles);
    $filesystem->chown($traversable, 'root');

.. _Packagist: https://packagist.org/packages/symfony/filesystem