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

    When writing your own extensions, you might want to learn
    from having a look at the `Twig Bridge`_  which contains most of
    the extensions provided by the Symfony Framework.

    We also have :doc:`a short article </reference/using_twig_extension_repository>`
    on how to use extensions from the `Twig official extension repository`_.

Create the Extension Class
--------------------------

.. note::

    This article describes how to write a custom Twig extension as of
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
        use AppBundle\Twig\AppExtension;
        use Symfony\Component\DependencyInjection\Definition;

        $container
            ->register('app.twig_extension', AppExtension::class)
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
.. _`Twig Bridge`: https://github.com/symfony/symfony/tree/master/src/Symfony/Bridge/Twig/Extension
