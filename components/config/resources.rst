.. index::
   single: Config; Loading resources

Loading resources
=================

Locating resources
------------------

Loading the configuration normally starts with a search for resources – in
most cases: files. This can be done with the :class:`Symfony\\Component\\Config\\FileLocator`::

    use Symfony\Component\Config\FileLocator;

    $configDirectories = array(__DIR__.'/app/config');

    $locator = new FileLocator($configDirectories);
    $yamlUserFiles = $locator->locate('users.yml', null, false);

The locator receives a collection of locations where it should look for files.
The first argument of ``locate()`` is the name of the file to look for. The
second argument may be the current path and when supplied, the locator will
look in this directory first. The third argument indicates whether or not the
locator should return the first file it has found, or an array containing
all matches.

Resource loaders
----------------

For each type of resource (Yaml, XML, annotation, etc.) a loader must be defined.
Each loader should implement :class:`Symfony\\Component\\Config\\Loader\\LoaderInterface`
or extend the abstract :class:`Symfony\\Component\\Config\\Loader\\FileLoader`
class, which allows for recursively importing other resources::

    use Symfony\Component\Config\Loader\FileLoader;
    use Symfony\Component\Yaml\Yaml;

    class YamlUserLoader extends FileLoader
    {
        public function load($resource, $type = null)
        {
            $configValues = Yaml::parse($resource);

            // ... handle the config values

            // maybe import some other resource:

            // $this->import('extra_users.yml');
        }

        public function supports($resource, $type = null)
        {
            return is_string($resource) && 'yml' === pathinfo(
                $resource,
                PATHINFO_EXTENSION
            );
        }
    }

Finding the right loader
------------------------

The :class:`Symfony\\Component\\Config\\Loader\\LoaderResolver` receives as
its first constructor argument a collection of loaders. When a resource (for
instance an XML file) should be loaded, it loops through this collection
of loaders and returns the loader which supports this particular resource type.

The :class:`Symfony\\Component\\Config\\Loader\\DelegatingLoader` makes use
of the :class:`Symfony\\Component\\Config\\Loader\\LoaderResolver`. When
it is asked to load a resource, it delegates this question to the
:class:`Symfony\\Component\\Config\\Loader\\LoaderResolver`. In case the resolver
has found a suitable loader, this loader will be asked to load the resource::

    use Symfony\Component\Config\Loader\LoaderResolver;
    use Symfony\Component\Config\Loader\DelegatingLoader;

    $loaderResolver = new LoaderResolver(array(new YamlUserLoader($locator)));
    $delegatingLoader = new DelegatingLoader($loaderResolver);

    $delegatingLoader->load(__DIR__.'/users.yml');
    /*
    The YamlUserLoader will be used to load this resource,
    since it supports files with a "yml" extension
    */
