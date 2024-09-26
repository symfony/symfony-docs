Create a UX bundle
==================

.. tip::

    Before reading this, you may want to have a look at
    :doc:`Best Practices for Reusable Bundles </bundles/best_practices>`.

Here are a few tricks to make your bundle install as a UX bundle.

composer.json file
------------------

Your ``composer.json`` file must have the ``symfony-ux`` keyword:

.. code-block:: json

    {
        "keywords": ["symfony-ux"]
    }

Assets location
---------------

Your assets must be located in one of the following directories, with a
``package.json`` file so Flex can handle it during install/update:

* ``/assets`` (recommended)
* ``/Resources/assets``
* ``/src/Resources/assets``

package.json file
-----------------

Your ``package.json`` file must contain a ``symfony`` config with controllers defined,
and also add required packages to the ``peerDependencies`` and ``importmap`` (the list
of packages in ``importmap`` should be the same as the ones in ``peerDependencies``):

.. code-block:: json

    {
        "name": "@acme/feature",
        "version": "1.0.0",
        "symfony": {
            "controllers": {
                "slug": {
                    "main": "dist/controller.js",
                    "fetch": "eager",
                    "enabled": true,
                    "autoimport": {
                        "@acme/feature/dist/bootstrap4-theme.css": false,
                        "@acme/feature/dist/bootstrap5-theme.css": true
                    }
                }
            },
            "importmap": {
                "@hotwired/stimulus": "^3.0.0",
                "slugify": "^1.6.5"
            }
        },
        "peerDependencies": {
            "@hotwired/stimulus": "^3.0.0",
            "slugify": "^1.6.5"
        }
    }

In this case, the file located at ``[assets directory]/dist/controller.js`` will be exposed.

.. tip::

    You can either write raw JS in this ``dist/controller.js`` file, or you can
    e.g. write your controller with TypeScript and transpile it to JavaScript.

    Here is an example to do so:

    1. Add the following to your ``package.json`` file:

       .. code-block:: json

           {
               "scripts": {
                   "build": "babel src --extensions .ts -d dist"
               },
               "devDependencies": {
                   "@babel/cli": "^7.20.7",
                   "@babel/core": "^7.20.12",
                   "@babel/plugin-proposal-class-properties": "^7.18.6",
                   "@babel/preset-env": "^7.20.2",
                   "@babel/preset-typescript": "^7.18.6",
                   "@hotwired/stimulus": "^3.2.1",
                   "typescript": "^4.9.5"
               }
           }

    2. Add the following to your ``babel.config.js`` file (should be located next
       to your ``package.json`` file):

       .. code-block:: javascript

           module.exports = {
               presets: [
                   ['@babel/preset-env', {
                       "loose": true,
                       "modules": false
                   }],
                   ['@babel/preset-typescript', { allowDeclareFields: true }]
               ],
               assumptions: {
                   superIsCallableConstructor: false,
               },
           };

    3. Run ``npm install`` to install the new dependencies.

    4. Write your Stimulus controller with TypeScript in ``src/controller.ts``.

    5. Run ``npm run build`` to transpile your TypeScript controller into JavaScript.

To use your controller in a template (e.g. one defined in your bundle) you can use it like this:

.. code-block:: html+twig

    <div
        {{ stimulus_controller('acme/feature/slug', { modal: 'my-value' }) }}
        {#
            will render:
            data-controller="acme--feature--slug"
            data-acme--feature--slug-modal-value="my-value"
        #}
    >
        ...
    </div>

Don't forget to add ``symfony/stimulus-bundle:^2.9`` as a composer dependency to use
Twig ``stimulus_*`` functions.

.. tip::

    Controller Naming: In this example, the ``name`` of the PHP package is ``acme/feature`` and the name
    of the controller in ``package.json`` is ``slug``. So, the full controller name for Stimulus will be
    ``acme--feature--slug``, though with the ``stimulus_controller()`` function, you can use ``acme/feature/slug``.

Each controller has a number of options in ``package.json`` file:

==================  ====================================================================================================
Option              Description
==================  ====================================================================================================
enabled             Whether the controller should be enabled by default.
main                Path to the controller file.
fetch               How controller & dependencies are included when the page loads.
                    Use ``eager`` (default) to make controller & dependencies included in the JavaScript that's
                    downloaded when the page is loaded.
                    Use ``lazy`` to make controller & dependencies isolated into a separate file and only downloaded
                    asynchronously if (and when) the data-controller HTML appears on the page.
autoimport          List of files to be imported with the controller. Useful e.g. when there are several CSS styles
                    depending on the frontend framework used (like Bootstrap 4 or 5, Tailwind CSS...).
                    The value must be an object with files as keys, and a boolean as value for each file to set
                    whether the file should be imported.
==================  ====================================================================================================

Specifics for Asset Mapper
--------------------------

To make your bundle's assets work with AssetMapper, you must add the ``importmap``
config like above in your ``package.json`` file, and prepend some configuration
to the container::

    namespace Acme\FeatureBundle;

    use Symfony\Component\AssetMapper\AssetMapperInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

    class AcmeFeatureBundle extends AbstractBundle
    {
        public function prependExtension(ContainerConfigurator $configurator, ContainerBuilder $container): void
        {
            if (!$this->isAssetMapperAvailable($container)) {
                return;
            }

            $container->prependExtensionConfig('framework', [
                'asset_mapper' => [
                    'paths' => [
                        __DIR__ . '/../assets/dist' => '@acme/feature-bundle',
                    ],
                ],
            ]);
        }

        private function isAssetMapperAvailable(ContainerBuilder $container): bool
        {
            if (!interface_exists(AssetMapperInterface::class)) {
                return false;
            }

            // check that FrameworkBundle 6.3 or higher is installed
            $bundlesMetadata = $container->getParameter('kernel.bundles_metadata');
            if (!isset($bundlesMetadata['FrameworkBundle'])) {
                return false;
            }

            return is_file($bundlesMetadata['FrameworkBundle']['path'] . '/Resources/config/asset_mapper.php');
        }
    }
