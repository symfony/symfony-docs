.. index::
   single: Twig extensions

How to Write a custom Twig Extension
====================================

If you need to create custom Twig functions, filters, tests or more, you'll need
to create a Twig extension. You can read more about `Twig Extensions`_ in the Twig
documentation.

Create the Extension Class
--------------------------

Suppose you want to create a new filter called ``price`` that formats a number into
money:

.. code-block:: twig

    {{ product.price|price }}

    {# pass in the 3 optional arguments #}
    {{ product.price|price(2, ',', '.') }}

Create a class that extends ``AbstractExtension`` and fill in the logic::

    // src/AppBundle/Twig/AppExtension.php
    namespace AppBundle\Twig;

    use Twig\Extension\AbstractExtension;
    use Twig\TwigFilter;

    class AppExtension extends AbstractExtension
    {
        public function getFilters()
        {
            return array(
                new TwigFilter('price', array($this, 'priceFilter')),
            );
        }

        public function priceFilter($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
        {
            $price = number_format($number, $decimals, $decPoint, $thousandsSep);
            $price = '$'.$price;

            return $price;
        }
    }

.. note::

    Prior to Twig 1.26, your extension had to define an additional ``getName()``
    method that returned a string with the extension's internal name (e.g.
    ``app.my_extension``). When your extension needs to be compatible with Twig
    versions before 1.26, include this method which is omitted in the example
    above.

.. tip::

    Along with custom filters, you can also add custom `functions`_ and register
    `global variables`_.

Register an Extension as a Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Next, register your class as a service and tag it with ``twig.extension``. If you're
using the :ref:`default services.yml configuration <service-container-services-load-example>`,
you're done! Symfony will automatically know about your new service and add the tag.

You can now start using your filter in any Twig template.

Creating Lazy-Loaded Twig Extensions
------------------------------------

.. versionadded:: 1.26
    Support for lazy-loaded extensions was introduced in Twig 1.26.

Including the code of the custom filters/functions in the Twig extension class
is the simplest way to create extensions. However, Twig must initialize all
extensions before rendering any template, even if the template doesn't use an
extension.

If extensions don't define dependencies (i.e. if you don't inject services in
them) performance is not affected. However, if extensions define lots of complex
dependencies (e.g. those making database connections), the performance loss can
be significant.

That's why Twig allows to decouple the extension definition from its
implementation. Following the same example as before, the first change would be
to remove the ``priceFilter()`` method from the extension and update the PHP
callable defined in ``getFilters()``::

    // src/AppBundle/Twig/AppExtension.php
    namespace AppBundle\Twig;

    use AppBundle\Twig\AppRuntime;
    use Twig\Extension\AbstractExtension;
    use Twig\TwigFilter;

    class AppExtension extends AbstractExtension
    {
        public function getFilters()
        {
            return array(
                // the logic of this filter is now implemented in a different class
                new TwigFilter('price', array(AppRuntime::class, 'priceFilter')),
            );
        }
    }

Then, create the new ``AppRuntime`` class (it's not required but these classes
are suffixed with ``Runtime`` by convention) and include the logic of the
previous ``priceFilter()`` method::

    // src/AppBundle/Twig/AppRuntime.php
    namespace AppBundle\Twig;

    class AppRuntime
    {
        public function __construct()
        {
            // this simple example doesn't define any dependency, but in your own
            // extensions, you'll need to inject services using this constructor
        }

        public function priceFilter($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
        {
            $price = number_format($number, $decimals, $decPoint, $thousandsSep);
            $price = '$'.$price;

            return $price;
        }
    }

Register the Lazy-Loaded Extension as a Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Finally, register your new class as a service and tag it with ``twig.runtime``
(and optionally inject any service needed by the Twig extension runtime):

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.twig_runtime:
                class: AppBundle\Twig\AppRuntime
                public: false
                tags:
                    - { name: twig.runtime }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.twig_runtime"
                    class="AppBundle\Twig\AppRuntime"
                    public="false">
                    <tag name="twig.runtime" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Twig\AppExtension;

        $container
            ->register('app.twig_runtime', AppRuntime::class)
            ->setPublic(false)
            ->addTag('twig.runtime');

.. _`Twig extensions documentation`: http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
.. _`global variables`: http://twig.sensiolabs.org/doc/advanced.html#id1
.. _`functions`: http://twig.sensiolabs.org/doc/advanced.html#id2
.. _`Twig Extensions`: https://twig.sensiolabs.org/doc/2.x/advanced.html#creating-an-extension
