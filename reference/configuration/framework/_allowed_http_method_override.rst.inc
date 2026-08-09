.. _configuration-framework-allowed_http_method_override:

allowed_http_method_override
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``null``

This option controls which HTTP methods can be overridden via the ``_method``
request parameter or the ``X-HTTP-METHOD-OVERRIDE`` header when
:ref:`http_method_override <configuration-framework-http_method_override>` is enabled.

When set to ``null`` (the default), all HTTP methods can be overridden. When set
to an empty array (``[]``), HTTP method overriding is completely disabled. When
set to a specific list of methods, only those methods will be allowed as overrides:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            http_method_override: true
            # Only allow PUT, PATCH, and DELETE to be overridden
            allowed_http_method_override: ['PUT', 'PATCH', 'DELETE']

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config http-method-override="true">
                <framework:allowed-http-method-override>PUT</framework:allowed-http-method-override>
                <framework:allowed-http-method-override>PATCH</framework:allowed-http-method-override>
                <framework:allowed-http-method-override>DELETE</framework:allowed-http-method-override>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework
                ->httpMethodOverride(true)
                ->allowedHttpMethodOverride(['PUT', 'PATCH', 'DELETE'])
            ;
        };

This security feature is useful for hardening your application by explicitly
defining which methods can be tunneled through POST requests. For example, if
your application only needs to override POST requests to PUT and DELETE, you
can restrict the allowed methods accordingly.

You can also configure this programmatically using the
:method:`Request::setAllowedHttpMethodOverride <Symfony\\Component\\HttpFoundation\\Request::setAllowedHttpMethodOverride>`
method::

    // public/index.php

    // ...
    $kernel = new CacheKernel($kernel);

    Request::enableHttpMethodParameterOverride();
    Request::setAllowedHttpMethodOverride(['PUT', 'PATCH', 'DELETE']);
    $request = Request::createFromGlobals();
    // ...

.. versionadded:: 7.4

    The ``allowed_http_method_override`` option was introduced in Symfony 7.4.

.. deprecated:: 7.4

    Overriding methods ``GET``, ``HEAD``, ``CONNECT`` and ``TRACE`` is deprecated
    since Symfony 7.4 and will stop working in Symfony 8.0.
