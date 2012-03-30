.. index::
   single: Twig extensions
   
How to write a custom Twig extension
============================================

The main motivation for writing an extension is to move often used code into a reusable class like adding support for internationalization. 
An extension can define tags, filters, tests, operators, global variables, functions, and node visitors.

Creating an extension also makes for a better separation of code that is executed at compilation time and code needed at runtime. As such, it makes your code faster.

.. tip::

    Before writing your own extensions, have a look at the `Twig official extension repository`_.
    
Create the extension class
--------------------------    

To get your custom functionality you must first create a Twig Extension class. 
As an example we will create a price filter to format a given number into price.

   .. code-block:: php

        <?php
        
        // src/Acme/DemoBundle/Twig/AcmeExtension.php

        namespace Acme\DemoBundle\Twig;

        use Twig_Extension;
        use Twig_Filter_Method;
        use Twig_Function_Method;

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
     
Register extension as a service
-------------------------------

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
                
                
Using the custom extension
---------------------------

Using your newly created Twig Extension is no different than any other:

.. code-block:: html+jinja

    {# outputs $5,500.00 #}
    {{ '5500' | price }}
     
.. _`Twig official extension repository`: http://github.com/fabpot/Twig-extensions