.. index::
   single: Bundle; Dependencies

How to Use Bundle Dependencies to Load other Bundles
====================================================

.. versionadded:: 2.8
    Support for bundle dependencies was introduced in Symfony 2.8.

When working on your own bundle(s), you'll sometimes have the need to reuse
other bundles, either by just requiring, overriding or inheriting from them.

While Composer takes care about making sure these dependencies are loaded,
you'll also need to enable these bundles in the kernel.

If your bundle is meant to be reused, bundle dependencies will complicate
your installation documentation. This makes installation and upgrading your
bundle more tedious for your users.

You can avoid this by specifying your dependencies. This will make sure that
they are loaded in the kernel. It'll also make sure they are loaded *before*
your own bundle, to make sure you can extend them.

Additional use case for this is for distribution bundle use cases where one
bundle is in fact bundling several others.

Specifying dependencies
-----------------------

Dependencies are specified using a Fully Qualified Name for the bundle class in same
format as PHP uses for ``get_class`` and for ``class`` constant introduced in PHP 5.5.

This implies you can only specify bundles that does not take arguments in it's constructor.
This is in-line with Symfony best practice and furthermore avoids same bundle being loaded
several times.

Specifying dependencies is accomplished by implementing the
:class:`Symfony\\Component\\HttpKernel\\Bundle\\BundleDependenciesInterface`::

    // src/CacheBundle/CacheBundle.php
    namespace CacheBundle;

    use Symfony\Component\HttpKernel\Bundle\Bundle;
    use Symfony\Component\HttpKernel\Bundle\BundleDependenciesInterface;

    class CacheBundle extends Bundle implements BundleDependenciesInterface
    {
        public function getBundleDependencies($environment, $debug)
        {
            return array('FOS\HttpCacheBundle\FOSHttpCacheBundle' => self::DEP_REQUIRED);
        }
    }

.. tip::

    If your bundle requires PHP 5.5 or higher, you can also take advantage of
    the ``class`` constant::

        use FOS\HttpCacheBundle\FOSHttpCacheBundle;

        // ...
        public function getBundleDependencies($environment, $debug)
        {
            return array(FOSHttpCacheBundle::class => self::DEP_REQUIRED);
        }

.. tip::

    If your dependency is only to be loaded in ``dev`` or when debugging use the provided arguments::

        use Egulias\SecurityDebugCommandBundle\EguliasSecurityDebugCommandBundle;

        // ...
        public function getBundleDependencies($environment, $debug)
        {
            if ($environment !== 'dev')
                return array();

            return array('Egulias\SecurityDebugCommandBundle\EguliasSecurityDebugCommandBundle' => self::DEP_REQUIRED);
        }


Specifying optional dependencies
--------------------------------

Specifying a optional dependency follows the same format but with a different constant::

        // ...
        public function getBundleDependencies($environment, $debug)
        {
            return array('Oneup\FlysystemBundle\OneupFlysystemBundle' => self::DEP_OPTIONAL);
        }

 .. tip::

     Make sure to not use PHP's ``class`` constant in combination with optional dependencies as that effectively will
     make PHP error when they are missing.