.. index::
   single: Twig extensions

How to Write a custom Twig Extension
====================================

If you need to create custom Twig functions, filters, tests or more, you'll need
to create a Twig extension. You can read more about `Twig Extensions`_ in the Twig
documentation.

.. tip::

    Before writing your own Twig extension, check if the filter/function that
    you need is already implemented in the :doc:`Symfony Twig extensions </reference/twig_reference>`.
    Check also the `official Twig extensions`_, which can be installed in your
    application as follows:

    .. code-block:: terminal

        $ composer require twig/extensions

Create the Extension Class
--------------------------

Suppose you want to create a new filter called ``price`` that formats a number into
money:

.. code-block:: twig

    {{ product.price|price }}

    {# pass in the 3 optional arguments #}
    {{ product.price|price(2, ',', '.') }}

Create a class that extends the ``AbstractExtension`` class defined by Twig and
fill in the logic::

    // src/Twig/AppExtension.php
    namespace App\Twig;

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

.. tip::

    Along with custom filters, you can also add custom `functions`_ and register
    `global variables`_.

Register an Extension as a Service
----------------------------------

Next, register your class as a service and tag it with ``twig.extension``. If you're
using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
you're done! Symfony will automatically know about your new service and add the tag.

You can now start using your filter in any Twig template.

.. _`official Twig extensions`: https://github.com/twigphp/Twig-extensions
.. _`Twig extensions documentation`: http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
.. _`global variables`: http://twig.sensiolabs.org/doc/advanced.html#id1
.. _`functions`: http://twig.sensiolabs.org/doc/advanced.html#id2
.. _`Twig Extensions`: https://twig.sensiolabs.org/doc/2.x/advanced.html#creating-an-extension
