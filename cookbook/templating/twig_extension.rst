.. index::
   single: Twig extensions
   
How to write a custom Twig Extension
====================================

The main motivation for writing an extension is to move often used code
into a reusable class like adding support for internationalization. 
An extension can define tags, filters, tests, operators, global variables,
functions, and node visitors.

Creating an extension also makes for a better separation of code that is
executed at compilation time and code needed at runtime. As such, it makes
your code faster.

.. tip::

    Before writing your own extensions, have a look at the `Twig official extension repository`_.
    
Create the Extension Class
--------------------------    

To get your custom functionality you must first create a Twig Extension class. 
As an example we will create a price filter to format a given number into price::

    // src/Acme/DemoBundle/Twig/AcmeExtension.php
    namespace Acme\DemoBundle\Twig;

    use Twig_Extension;
    use Twig_Filter_Method;

    class AcmeExtension extends Twig_Extension
    {
        public function getFilters()
        {
            return array(
                'price' => new Twig_Filter_Method($this, 'priceFilter'),
            );
        }

        public function priceFilter($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
        {
            $price = number_format($number, $decimals, $decPoint, $thousandsSep);
            $price = '$' . $price;

            return $price;
        }

        public function getName()
        {
            return 'acme_extension';
        }
    }

.. tip::

    Along with custom filters, you can also add custom `functions` and register `global variables`.    
     
Register an Extension as a Service
----------------------------------

Now you must let Service Container know about your newly created Twig Extension:

.. configuration-block::

    .. code-block:: xml
        
        <!-- src/Acme/DemoBundle/Resources/config/services.xml -->
        <services>
            <service id="acme.twig.acme_extension" class="Acme\DemoBundle\Twig\AcmeExtension">
                <tag name="twig.extension" />
            </service>
        </services>

    .. code-block:: yaml
        
        # src/Acme/DemoBundle/Resources/config/services.yml
        services:
            acme.twig.acme_extension:
                class: Acme\DemoBundle\Twig\AcmeExtension
                tags:
                    - { name: twig.extension }

    .. code-block:: php

        // src/Acme/DemoBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $acmeDefinition = new Definition('\Acme\DemoBundle\Twig\AcmeExtension');
        $acmeDefinition->addTag('twig.extension');
        $container->setDefinition('acme.twig.acme_extension', $acmeDefinition);
         
.. note::

   Keep in mind that Twig Extensions are not lazily loaded. This means that 
   there's a higher chance that you'll get a **CircularReferenceException**
   or a **ScopeWideningInjectionException** if any services 
   (or your Twig Extension in this case) are dependent on the request service.
   For more information take a look at :doc:`/cookbook/service_container/scopes`.
                
Using the custom Extension
--------------------------

Using your newly created Twig Extension is no different than any other:

.. code-block:: jinja

    {# outputs $5,500.00 #}
    {{ '5500'|price }}
    
Passing other arguments to your filter:

.. code-block:: jinja
    
    {# outputs $5500,2516 #}
    {{ '5500.25155'|price(4, ',', '') }}
    
Learning further
----------------
    
For a more in-depth look into Twig Extensions, please take a look at the `Twig extensions documentation`_.
     
.. _`Twig official extension repository`: http://github.com/fabpot/Twig-extensions
.. _`Twig extensions documentation`: http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
.. _`global variables`: http://twig.sensiolabs.org/doc/advanced.html#id1
.. _`functions`: http://twig.sensiolabs.org/doc/advanced.html#id2
