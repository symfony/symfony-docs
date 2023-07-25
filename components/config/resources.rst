Loading Resources
=================

Loaders populate the application's configuration from different sources
like YAML files. The Config component defines the interface for such
loaders. The :doc:`Dependency Injection </components/dependency_injection>`
and `Routing`_ components come with specialized loaders for different file
formats.

Locating Resources
------------------

Loading the configuration normally starts with a search for resources, mostly
files. This can be done with the :class:`Symfony\\Component\\Config\\FileLocator`::

    use Symfony\Component\Config\FileLocator;

    $configDirectories = [__DIR__.'/config'];

    $fileLocator = new FileLocator($configDirectories);
    $yamlUserFiles = $fileLocator->locate('users.yaml', null, false);

The locator receives a collection of locations where it should look for
files. The first argument of ``locate()`` is the name of the file to look
for. The second argument may be the current path and when supplied, the
locator will look in this directory first. The third argument indicates
whether or not the locator should return the first file it has found or
an array containing all matches.

Resource Loaders
----------------

For each type of resource (YAML, XML, attributes, etc.) a loader must be
defined. Each loader should implement
:class:`Symfony\\Component\\Config\\Loader\\LoaderInterface` or extend the
abstract :class:`Symfony\\Component\\Config\\Loader\\FileLoader` class,
which allows for recursively importing other resources::

    namespace Acme\Config\Loader;

    use Symfony\Component\Config\Loader\FileLoader;
    use Symfony\Component\Yaml\Yaml;

    class YamlUserLoader extends FileLoader
    {
        public function load($resource, $type = null): void
        {
            $configValues = Yaml::parse(file_get_contents($resource));

            // ... handle the config values

            // maybe import some other resource:

            // $this->import('extra_users.yaml');
        }

        public function supports($resource, $type = null): bool
        {
            return is_string($resource) && 'yaml' === pathinfo(
                $resource,
                PATHINFO_EXTENSION
            );
        }
    }

Finding the Right Loader
------------------------

The :class:`Symfony\\Component\\Config\\Loader\\LoaderResolver` receives
as its first constructor argument a collection of loaders. When a resource
(for instance an XML file) should be loaded, it loops through this collection
of loaders and returns the loader which supports this particular resource
type.

The :class:`Symfony\\Component\\Config\\Loader\\DelegatingLoader` makes
use of the :class:`Symfony\\Component\\Config\\Loader\\LoaderResolver`.
When it is asked to load a resource, it delegates this question to the
:class:`Symfony\\Component\\Config\\Loader\\LoaderResolver`. In case the
resolver has found a suitable loader, this loader will be asked to load
the resource::

    use Acme\Config\Loader\YamlUserLoader;
    use Symfony\Component\Config\Loader\DelegatingLoader;
    use Symfony\Component\Config\Loader\LoaderResolver;

    $loaderResolver = new LoaderResolver([new YamlUserLoader($fileLocator)]);
    $delegatingLoader = new DelegatingLoader($loaderResolver);

    // YamlUserLoader is used to load this resource because it supports
    // files with the '.yaml' extension
    $delegatingLoader->load(__DIR__.'/users.yaml');

.. _Routing: https://github.com/symfony/routing
