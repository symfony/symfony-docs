.. index::
   single: Request; Add a request format and mime type

How to register a new Request Format and Mime Type
==================================================

Every ``Request`` has a "format" (e.g. ``html``, ``json``), which is used
to determine what type of content to return in the ``Response``. In fact,
the request format, accessible via
:method:`Symfony\\Component\\HttpFoundation\\Request::getRequestFormat`,
is used to set the MIME type of the ``Content-Type`` header on the ``Response``
object. Internally, Symfony contains a map of the most common formats (e.g.
``html``, ``json``) and their associated MIME types (e.g. ``text/html``,
``application/json``). Of course, additional format-MIME type entries can
easily be added. This document will show how you can add the ``jsonp`` format
and corresponding MIME type.

.. versionadded:: 2.5
    The possibility to configure request formats was introduced in Symfony 2.5.

Configure your New Format
-------------------------

The FrameworkBundle registers a subscriber that will add formats to incoming requests.

All you have to do is to configure the ``jsonp`` format:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            request:
                formats:
                    jsonp: 'application/javascript'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >
            <framework:config>
                <framework:request>
                    <framework:format name="jsonp">
                        <framework:mime-type>application/javascript</framework:mime-type>
                    </framework:format>
                </framework:request>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'request' => array(
                'formats' => array(
                    'jsonp' => 'application/javascript',
                ),
            ),
        ));

.. tip::

    You can also associate multiple mime types to a format, but please note that
    the preferred one must be the first as it will be used as the content type:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            framework:
                request:
                    formats:
                        csv: ['text/csv', 'text/plain']

        .. code-block:: xml

            <!-- app/config/config.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>

            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    http://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
            >
                <framework:config>
                    <framework:request>
                        <framework:format name="csv">
                            <framework:mime-type>text/csv</framework:mime-type>
                            <framework:mime-type>text/plain</framework:mime-type>
                        </framework:format>
                    </framework:request>
                </framework:config>
            </container>

        .. code-block:: php

            // app/config/config.php
            $container->loadFromExtension('framework', array(
                'request' => array(
                    'formats' => array(
                        'jsonp' => array(
                            'text/csv',
                            'text/plain',
                        ),
                    ),
                ),
            ));
