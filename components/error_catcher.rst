.. index::
   single: Error
   single: Exception
   single: Components; ErrorCatcher

The ErrorCatcher Component
==========================

    The ErrorCatcher component converts PHP errors and exceptions into other
    formats such as JSON and HTML and renders them.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/error-catcher

.. include:: /components/require_autoload.rst.inc

Usage
-----

The ErrorCatcher component provides several handlers and renderers to convert
PHP errors and exceptions into other formats easier to debug when working with
HTTP applications.

.. TODO: how are these handlers enabled in the app? (Previously: Debug::enable())

Handling PHP Errors and Exceptions
----------------------------------

Enabling the Error Handler
~~~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\ErrorCatcher\\ErrorHandler` class catches PHP
errors and converts them to exceptions (of class :phpclass:`ErrorException` or
:class:`Symfony\\Component\\ErrorCatcher\\Exception\\FatalErrorException` for
PHP fatal errors)::

    use Symfony\Component\ErrorCatcher\ErrorHandler;

    ErrorHandler::register();

This error handler is enabled by default in the production environment when the
application uses the FrameworkBundle because it generates better error logs.

Enabling the Exception Handler
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\ErrorCatcher\\ExceptionHandler` class catches
uncaught PHP exceptions and converts them to a nice PHP response. It is useful
in :ref:`debug mode <debug-mode>` to replace the default PHP/XDebug output with
something prettier and more useful::

    use Symfony\Component\ErrorCatcher\ExceptionHandler;

    ExceptionHandler::register();

.. note::

    If the :doc:`HttpFoundation component </components/http_foundation>` is
    available, the handler uses a Symfony Response object; if not, it falls
    back to a regular PHP response.

Rendering PHP Errors and Exceptions
-----------------------------------

Another feature provided by this component are the "error renderers", which
converts PHP errors and exceptions into other formats such as JSON and HTML::

    use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRenderer;
    use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
    use Symfony\Component\ErrorHandler\ErrorRenderer\JsonErrorRenderer;

    $renderers = [
        new HtmlErrorRenderer(),
        new JsonErrorRenderer(),
        // ...
    ];
    $errorRenderer = new ErrorRenderer($renderers);

    /** @var Symfony\Component\ErrorHandler\Exception\FlattenException */
    $exception = ...;
    /** @var Symfony\Component\HttpFoundation\Request */
    $request = ...;

    return new Response(
        $errorRenderer->render($exception, $request->getRequestFormat()),
        $exception->getStatusCode(),
        $exception->getHeaders()
    );

Built-in Error Renderers
~~~~~~~~~~~~~~~~~~~~~~~~

This component provides error renderers for the most common needs:

  * :class:`Symfony\\Component\\ErrorHandler\\ErrorRenderer\\HtmlErrorRenderer`
    renders errors in HTML format;
  * :class:`Symfony\\Component\\ErrorHandler\\ErrorRenderer\\JsonErrorRenderer`
    renders errors in JsonErrorRenderer format and it's compliant with the
    `RFC 7807`_ standard;
  * :class:`Symfony\\Component\\ErrorHandler\\ErrorRenderer\\XmlErrorRenderer`
    renders errors in XML and Atom formats. It's compliant with the `RFC 7807`_
    standard;
  * :class:`Symfony\\Component\\ErrorHandler\\ErrorRenderer\\TxtErrorRenderer`
    renders errors in regular text format.

Adding a Custom Error Renderer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Error renderers are PHP classes that implement the
:class:`Symfony\\Component\\ErrorHandler\\ErrorRenderer\\ErrorRendererInterface`.
For example, if you need to render errors in `JSON-LD format`_, create this
class anywhere in your project::

    namespace App\ErrorHandler\ErrorRenderer;

    use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
    use Symfony\Component\ErrorHandler\Exception\FlattenException;

    class JsonLdErrorRenderer implements ErrorRendererInterface
    {
        private $debug;

        public static function getFormat(): string
        {
            return 'jsonld';
        }

        public function __construct(bool $debug = true)
        {
            $this->debug = $debug;
        }

        public function render(FlattenException $exception): string
        {
            $content = [
                '@id' => 'https://example.com',
                '@type' => 'error',
                '@context' => [
                    'title' => $exception->getTitle(),
                    'code' => $exception->getStatusCode(),
                    'message' => $exception->getMessage(),
                ],
            ];

            if ($this->debug) {
                $content['@context']['exceptions'] = $exception->toArray();
            }

            return (string) json_encode($content);
        }
    }

.. tip::

    If the ``getformat()`` method of your error renderer matches one of formats
    supported by the built-in renderers, the built-in renderer is replaced by
    your custom renderer.

To enable the new error renderer in the application,
:ref:`register it as a service <service-container-creating-service>` and
:doc:`tag it </service_container/tags>` with the ``error_handler.renderer``
tag.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\ErrorHandler\ErrorRenderer\JsonLdErrorRenderer:
                arguments: ['%kernel.debug%']
                tags: ['error_handler.renderer']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\ErrorHandler\ErrorRenderer\JsonLdErrorRenderer">
                    <argument>true</argument>
                    <tag name="error_handler.renderer"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\ErrorHandler\ErrorRenderer\JsonLdErrorRenderer;

        $container->register(JsonLdErrorRenderer::class)
            ->setArguments([true]);
            ->addTag('error_handler.renderer');

.. _`RFC 7807`: https://tools.ietf.org/html/rfc7807
.. _`JSON-LD format`: https://en.wikipedia.org/wiki/JSON-LD
