.. index::
   single: Twig extensions

How to Write a custom Twig Extension
====================================

The main motivation for writing an extension is to move often used code
into a reusable class like adding support for internationalization.
An extension can define tags, filters, tests, operators, global variables,
functions, and node visitors.

Creating an extension also makes for a better separation of code that is
executed at compilation time and code needed at runtime. As such, it makes
your code faster.

.. tip::

    Before writing your own extensions, have a look at the
    `Twig official extension repository`_.

Create the Extension Class
--------------------------

.. note::

    This cookbook describes how to write a custom Twig extension as of
    Twig 1.12. If you are using an older version, please read
    `Twig extensions documentation legacy`_.

To get your custom functionality you must first create a Twig Extension class.
As an example you'll create a price filter to format a given number into price::

    // src/AppBundle/Twig/AppExtension.php
    namespace AppBundle\Twig;

    class AppExtension extends \Twig_Extension
    {
        public function getFilters()
        {
            return array(
                new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
            );
        }

        public function priceFilter($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
        {
            $price = number_format($number, $decimals, $decPoint, $thousandsSep);
            $price = '$'.$price;

            return $price;
        }

        public function getName()
        {
            return 'app_extension';
        }
    }

.. tip::

    Along with custom filters, you can also add custom `functions` and register
    `global variables`.

Register an Extension as a Service
----------------------------------

Now you must let the Service Container know about your newly created Twig Extension:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.twig_extension:
                class: AppBundle\Twig\AppExtension
                public: false
                tags:
                    - { name: twig.extension }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <services>
            <service id="app.twig_extension"
                class="AppBundle\Twig\AppExtension"
                public="false">
                <tag name="twig.extension" />
            </service>
        </services>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $container
            ->register('app.twig_extension', '\AppBundle\Twig\AppExtension')
            ->setPublic(false)
            ->addTag('twig.extension');

Using the custom Extension
--------------------------

Using your newly created Twig Extension is no different than any other:

.. code-block:: twig

    {# outputs $5,500.00 #}
    {{ '5500'|price }}

Passing other arguments to your filter:

.. code-block:: twig

    {# outputs $5500,2516 #}
    {{ '5500.25155'|price(4, ',', '') }}

Learning further
----------------

For a more in-depth look into Twig Extensions, please take a look at the
`Twig extensions documentation`_.

.. _`Twig official extension repository`: https://github.com/twigphp/Twig-extensions
.. _`Twig extensions documentation`: http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
.. _`global variables`: http://twig.sensiolabs.org/doc/advanced.html#id1
.. _`functions`: http://twig.sensiolabs.org/doc/advanced.html#id2
.. _`Twig extensions documentation legacy`: http://twig.sensiolabs.org/doc/advanced_legacy.html#creating-an-extension
