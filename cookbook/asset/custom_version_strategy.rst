.. index::
   single: Asset; Custom Version Strategy

How to Use a Custom Version Strategy for Assets
===============================================

.. versionadded:: 2.7
    The Asset component was introduced in Symfony 2.7.

Asset versioning is a technique that improves the performance of web
applications by adding a version identifier to the URL of your static assets
(CSS, JavaScript, images, etc.) When the content of the asset changes, the
identifier changes and the browser is forced to download it again instead of
using the cached version.

Symfony supports the basic asset versioning thanks to the
:ref:`version <reference-framework-assets-version>` and
:ref:`version_format <reference-framework-assets-version-format>` configuration
options. If your application requires a more advanced versioning, you can create
your own version strategy.

Default Package
---------------

The default package is used when you do not specify a package name in the
:ref:`asset <reference-twig-function-asset>` Twig function. In order to
override the version strategy used by the default package, it is necessary
to add a compiler pass.

This example shows how to integrate with `gulp-buster`_.

.. note::

    busters.json as referenced below is the output from gulp-buster which
    maps each asset file to its hash. A small snippet of the file's format
    (JSON object):

    .. code-block:: json

        {
            "js/script.js": "f9c7afd05729f10f55b689f36bb20172",
            "css/style.css": "91cd067f79a5839536b46c494c4272d8"
        }

Create Compiler Pass
~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    // src/AppBundle/DependencyInjection/Compiler/OverrideAssetsDefaultPackagePass.php
    namespace AppBundle\DependencyInjection\Compiler;

    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    class OverrideAssetsDefaultPackagePass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
            $definition = $container->getDefinition('assets._default_package');
            $definition->replaceArgument(1, new Reference('app.assets.buster_version_strategy'));
        }
    }

The code above fetches the service definition of the default package, and replaces
its second argument (the version strategy).

Register Compiler Pass
~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    // src/AppBundle/AppBundle.php
    namespace AppBundle;

    use AppBundle\DependencyInjection\Compiler\OverrideAssetsDefaultPackagePass;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class AppBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
        {
            parent::build($container);

            // only register in prod environment
            if ('prod' === $container->getParameter('kernel.environment')) {
                $container->addCompilerPass(new OverrideAssetsDefaultPackagePass());
            }
        }
    }

See :doc:`/cookbook/service_container/compiler_passes` for more information
on how to use compiler passes.

Register Services
~~~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.assets.buster_version_strategy:
                class: AppBundle\Asset\VersionStrategy\BusterVersionStrategy
                arguments:
                    - "%kernel.root_dir%/../busters.json"
                    - "%%s?version=%%s"
                public: false

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd"
        >
            <services>
                <service id="app.assets.buster_version_strategy" class="AppBundle\Asset\VersionStrategy\BusterVersionStrategy" public="false">
                    <argument>%kernel.root_dir%/../busters.json</argument>
                    <argument>%%s?version=%%s</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition(
            'AppBundle\Asset\VersionStrategy\BusterVersionStrategy',
            array(
                '%kernel.root_dir%/../busters.json',
                '%%s?version=%%s',
            )
        );
        $definition->setPublic(false);

        $container->setDefinition('app.assets.buster_version_strategy', $definition);

Implement VersionStrategyInterface
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    // src/AppBundle/Asset/VersionStrategy/BusterVersionStrategy.php
    namespace AppBundle\Asset\VersionStrategy;

    use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

    class BusterVersionStrategy implements VersionStrategyInterface
    {
        /**
         * @var string
         */
        private $manifestPath;

        /**
         * @var string
         */
        private $format;

        /**
         * @var string[]
         */
        private $hashes;

        /**
         * @param string      $manifestPath
         * @param string|null $format
         */
        public function __construct($manifestPath, $format = null)
        {
            $this->manifestPath = $manifestPath;
            $this->format = $format ?: '%s?%s';
        }

        public function getVersion($path)
        {
            if (!is_array($this->hashes)) {
                $this->hashes = $this->loadManifest();
            }

            return isset($this->hashes[$path]) ? $this->hashes[$path] : '';
        }

        public function applyVersion($path)
        {
            $version = $this->getVersion($path);

            if ('' === $version) {
                return $path;
            }

            $versionized = sprintf($this->format, ltrim($path, '/'), $version);

            if ($path && '/' === $path[0]) {
                return '/'.$versionized;
            }

            return $versionized;
        }

        private function loadManifest(array $options)
        {
            $hashes = json_decode(file_get_contents($this->manifestPath), true);

            return $hashes;
        }
    }

.. _`gulp-buster`: https://www.npmjs.com/package/gulp-buster
