.. index::
   single: Templating; Global variables

How to Inject Variables Automatically into all Templates
========================================================

Twig allows you to automatically inject one or more variables into all templates.
These global variables are defined in the ``twig.globals`` option inside the
main Twig configuration file:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...
            globals:
                ga_tracking: 'UA-xxxxx-x'

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <!-- ... -->
                <twig:global key="ga_tracking">UA-xxxxx-x</twig:global>
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig) {
            // ...

            $twig->global('ga_tracking')->value('UA-xxxxx-x');
        };

Now, the variable ``ga_tracking`` is available in all Twig templates, so you
can use it without having to pass it explicitly from the controller or service
that renders the template:

.. code-block:: html+twig

    <p>The Google tracking code is: {{ ga_tracking }}</p>

Referencing Services
--------------------

In addition to static values, Twig global variables can also reference services
from the :doc:`service container </service_container>`. The main drawback is
that these services are not loaded lazily. In other words, as soon as Twig is
loaded, your service is instantiated, even if you never use that global variable.

To define a service as a global Twig variable, prefix the service ID string with
the ``@`` character, which is the usual syntax to
:ref:`refer to services in container parameters <service-container-parameters>`:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...
            globals:
                # the value is the service's id
                uuid: '@App\Generator\UuidGenerator'

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <!-- ... -->
                <twig:global key="uuid" id="App\Generator\UuidGenerator" type="service"/>
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;
        use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

        return static function (TwigConfig $twig) {
            // ...

            $twig->global('uuid')->value(service('App\Generator\UuidGenerator'));
        };

Now you can use the ``uuid`` variable in any Twig template to access to the
``UuidGenerator`` service:

.. code-block:: twig

    UUID: {{ uuid.generate }}
