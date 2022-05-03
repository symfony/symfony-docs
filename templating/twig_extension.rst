.. index::
   single: Twig extensions

How to Write a custom Twig Extension
====================================

`Twig Extensions`_ allow the creation of custom functions, filters, and more to use
in your Twig templates. Before writing your own Twig extension, check if
the filter/function that you need is already implemented in:

* The `default Twig filters and functions`_;
* The :doc:`Twig filters and functions added by Symfony </reference/twig_reference>`;
* The `official Twig extensions`_ related to strings, HTML, Markdown, internationalization, etc.

Create the Extension Class
--------------------------

Suppose you want to create a new filter called ``price`` that formats a number
as currency:

.. code-block:: twig

    {{ product.price|price }}

    {# pass in the 3 optional arguments #}
    {{ product.price|price(2, ',', '.') }}

Create a class that extends ``AbstractExtension`` and fill in the logic::

    // src/Twig/AppExtension.php
    namespace App\Twig;

    use Twig\Extension\AbstractExtension;
    use Twig\TwigFilter;

    class AppExtension extends AbstractExtension
    {
        public function getFilters()
        {
            return [
                new TwigFilter('price', [$this, 'formatPrice']),
            ];
        }

        public function formatPrice(float $number, int $decimals = 0, string $decPoint = '.', string $thousandsSep = ','): string
        {
            $price = number_format($number, $decimals, $decPoint, $thousandsSep);
            $price = '$'.$price;

            return $price;
        }
    }

If you want to create a function instead of a filter, define the
``getFunctions()`` method::

    // src/Twig/AppExtension.php
    namespace App\Twig;

    use Twig\Extension\AbstractExtension;
    use Twig\TwigFunction;

    class AppExtension extends AbstractExtension
    {
        public function getFunctions()
        {
            return [
                new TwigFunction('area', [$this, 'calculateArea']),
            ];
        }

        public function calculateArea(int $width, int $length): int
        {
            return $width * $length;
        }
    }

.. tip::

    Along with custom filters and functions, you can also register
    `global variables`_.

Register an Extension as a Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Next, register your class as a service and tag it with ``twig.extension``. If you're
using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
you're done! Symfony will automatically know about your new service and add the tag.

You can now start using your filter in any Twig template. Optionally, execute
this command to confirm that your new filter was successfully registered:

.. code-block:: terminal

    # display all information about Twig
    $ php bin/console debug:twig

    # display only the information about a specific filter
    $ php bin/console debug:twig --filter=price

.. _lazy-loaded-twig-extensions:

Creating Lazy-Loaded Twig Extensions
------------------------------------

.. versionadded:: 1.35

    Support for lazy-loaded extensions was introduced in Twig 1.35.0 and 2.4.4.

Including the code of the custom filters/functions in the Twig extension class
is the simplest way to create extensions. However, Twig must initialize all
extensions before rendering any template, even if the template doesn't use an
extension.

If extensions don't define dependencies (i.e. if you don't inject services in
them) performance is not affected. However, if extensions define lots of complex
dependencies (e.g. those making database connections), the performance loss can
be significant.

That's why Twig allows decoupling the extension definition from its
implementation. Following the same example as before, the first change would be
to remove the ``formatPrice()`` method from the extension and update the PHP
callable defined in ``getFilters()``::

    // src/Twig/AppExtension.php
    namespace App\Twig;

    use App\Twig\AppRuntime;
    use Twig\Extension\AbstractExtension;
    use Twig\TwigFilter;

    class AppExtension extends AbstractExtension
    {
        public function getFilters()
        {
            return [
                // the logic of this filter is now implemented in a different class
                new TwigFilter('price', [AppRuntime::class, 'formatPrice']),
            ];
        }
    }

Then, create the new ``AppRuntime`` class (it's not required but these classes
are suffixed with ``Runtime`` by convention) and include the logic of the
previous ``formatPrice()`` method::

    // src/Twig/AppRuntime.php
    namespace App\Twig;

    use Twig\Extension\RuntimeExtensionInterface;

    class AppRuntime implements RuntimeExtensionInterface
    {
        public function __construct()
        {
            // this simple example doesn't define any dependency, but in your own
            // extensions, you'll need to inject services using this constructor
        }

        public function formatPrice(float $number, int $decimals = 0, string $decPoint = '.', string $thousandsSep = ','): string
        {
            $price = number_format($number, $decimals, $decPoint, $thousandsSep);
            $price = '$'.$price;

            return $price;
        }
    }

If you're using the default ``services.yaml`` configuration, this will already
work! Otherwise, :ref:`create a service <service-container-creating-service>`
for this class and :doc:`tag your service </service_container/tags>` with ``twig.runtime``.

.. _`Twig Extensions`: https://twig.symfony.com/doc/3.x/advanced.html#creating-an-extension
.. _`default Twig filters and functions`: https://twig.symfony.com/doc/3.x/#reference
.. _`official Twig extensions`: https://github.com/twigphp?q=extra
.. _`global variables`: https://twig.symfony.com/doc/3.x/advanced.html#id1
