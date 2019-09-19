.. index::
   single: Error
   single: Exception
   single: Components; ErrorRenderer

The ErrorRenderer Component
===========================

    The ErrorRenderer component converts PHP errors and exceptions into other
    formats such as JSON and HTML and renders them.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/error-renderer

.. include:: /components/require_autoload.rst.inc

Usage
-----

The ErrorRenderer component provides several renderers to convert PHP errors and
exceptions into other formats such as JSON and HTML easier to debug when working
with HTTP applications::

    use Symfony\Component\ErrorRenderer\ErrorRenderer;
    use Symfony\Component\ErrorRenderer\ErrorRenderer\HtmlErrorRenderer;
    use Symfony\Component\ErrorRenderer\ErrorRenderer\JsonErrorRenderer;
    use Symfony\Component\ErrorRenderer\Exception\FlattenException;
    use Symfony\Component\HttpFoundation\Response;

    $renderers = [
        new HtmlErrorRenderer(),
        new JsonErrorRenderer(),
        // ...
    ];
    $errorRenderer = new ErrorRenderer($renderers);

    try {
        // ...
    } catch (\Throwable $e) {
        $e = FlattenException::createFromThrowable($e);

        return new Response($errorRenderer->render($e, 'json'), 500, ['Content-Type' => 'application/json']);
    }

Built-in Error Renderers
------------------------

This component provides error renderers for the most common needs:

  * :class:`Symfony\\Component\\ErrorRenderer\\ErrorRenderer\\HtmlErrorRenderer`
    renders errors in HTML format;
  * :class:`Symfony\\Component\\ErrorRenderer\\ErrorRenderer\\JsonErrorRenderer`
    renders errors in JSON format and it's compliant with the `RFC 7807`_ standard;
  * :class:`Symfony\\Component\\ErrorRenderer\\ErrorRenderer\\XmlErrorRenderer`
    renders errors in XML and Atom formats. It's compliant with the `RFC 7807`_
    standard;
  * :class:`Symfony\\Component\\ErrorRenderer\\ErrorRenderer\\TxtErrorRenderer`
    renders errors in plain text format.

Adding a Custom Error Renderer
------------------------------

Error renderers are PHP classes that implement the
:class:`Symfony\\Component\\ErrorRenderer\\ErrorRenderer\\ErrorRendererInterface`.
For example, if you need to render errors in `JSON-LD format`_, create this
class anywhere in your project::

    namespace App\ErrorRenderer;

    use Symfony\Component\ErrorRenderer\ErrorRenderer\ErrorRendererInterface;
    use Symfony\Component\ErrorRenderer\Exception\FlattenException;

    class JsonLdErrorRenderer implements ErrorRendererInterface
    {
        private $debug;

        public function __construct(bool $debug = true)
        {
            $this->debug = $debug;
        }

        public static function getFormat(): string
        {
            return 'jsonld';
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

    If the ``getFormat()`` method of your error renderer matches one of formats
    supported by the built-in renderers, the built-in renderer is replaced by
    your custom renderer.

To enable the new error renderer in the application,
:ref:`register it as a service <service-container-creating-service>` and
:doc:`tag it </service_container/tags>` with the ``error_renderer.renderer``
tag.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\ErrorRenderer\JsonLdErrorRenderer:
                arguments: ['%kernel.debug%']
                tags: ['error_renderer.renderer']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\ErrorRenderer\JsonLdErrorRenderer">
                    <argument>%kernel.debug%</argument>
                    <tag name="error_renderer.renderer"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\ErrorRenderer\JsonLdErrorRenderer;

        $container->register(JsonLdErrorRenderer::class)
            ->setArguments([$container->getParameter('kernel.debug')]);
            ->addTag('error_renderer.renderer');

.. _`RFC 7807`: https://tools.ietf.org/html/rfc7807
.. _`JSON-LD format`: https://en.wikipedia.org/wiki/JSON-LD
