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

Create a class that extends ``\Twig_Extension`` and fill in the logic::

    // src/Twig/AppExtension.php
    namespace App\Twig;

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

Next, register your class as a service and tag it with ``twig.extension``. If you're
using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
you're done! Symfony will automatically know about your new service and add the tag.

You can now start using your filter in any Twig template.

.. _`Twig extensions documentation`: http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
.. _`global variables`: http://twig.sensiolabs.org/doc/advanced.html#id1
.. _`functions`: http://twig.sensiolabs.org/doc/advanced.html#id2
.. _`Twig Extensions`: https://twig.sensiolabs.org/doc/2.x/advanced.html#creating-an-extension
