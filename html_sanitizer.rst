HTML Sanitizer
==============

The HTML Sanitizer component aims at sanitizing/cleaning untrusted HTML
code (e.g. created by a WYSIWYG editor in the browser) into HTML that can
be trusted. It is based on the `HTML Sanitizer W3C Standard Proposal`_.

The HTML sanitizer creates a new HTML structure from scratch, taking only
the elements and attributes that are allowed by configuration. This means
that the returned HTML is very predictable (it only contains allowed
elements), but it does not work well with badly formatted input (e.g.
invalid HTML). The sanitizer is targeted for two use cases:

* Preventing security attacks based on XSS or other technologies relying on
  execution of malicious code on the visitors browsers;
* Generating HTML that always respects a certain format (only certain
  tags, attributes, hosts, etc.) to be able to consistently style the
  resulting output with CSS. This also protects your application against
  attacks related to e.g. changing the CSS of the whole page.

.. _html-sanitizer-installation:

Installation
------------

You can install the HTML Sanitizer component with:

.. code-block:: terminal

    $ composer require symfony/html-sanitizer

Basic Usage
-----------

Use the :class:`Symfony\\Component\\HtmlSanitizer\\HtmlSanitizer` class to
sanitize the HTML. In the Symfony framework, this class is available as the
``html_sanitizer`` service. This service will be :doc:`autowired </service_container/autowiring>`
automatically when type-hinting for
:class:`Symfony\\Component\\HtmlSanitizer\\HtmlSanitizerInterface`:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Controller/BlogPostController.php
        namespace App\Controller;

        // ...
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

        class BlogPostController extends AbstractController
        {
            public function createAction(HtmlSanitizerInterface $htmlSanitizer, Request $request): Response
            {
                $unsafeContents = $request->request->get('post_contents');

                $safeContents = $htmlSanitizer->sanitize($unsafeContents);
                // ... proceed using the safe HTML
            }
        }

    .. code-block:: php-standalone

        use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

        $htmlSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())->allowSafeElements()
        );

        // unsafe HTML (e.g. from a WYSIWYG editor in the browser)
        $unsafePostContents = ...;

        $safePostContents = $htmlSanitizer->sanitize($unsafePostContents);
        // ... proceed using the safe HTML

.. note::

    The default configuration of the HTML sanitizer allows all "safe"
    elements and attributes, as defined by the `W3C Standard Proposal`_. In
    practice, this means that the resulting code will not contain any
    scripts, styles or other elements that can cause the website to behave
    or look different. Later in this article, you'll learn how to
    :ref:`fully customize the HTML sanitizer <html-sanitizer-configuration>`.

Sanitizing HTML for a Specific Context
--------------------------------------

The default :method:`Symfony\\Component\\HtmlSanitizer\\HtmlSanitizer::sanitize`
method cleans the HTML code for usage in the ``<body>`` element. Using the
:method:`Symfony\\Component\\HtmlSanitizer\\HtmlSanitizer::sanitizeFor`
method, you can instruct HTML sanitizer to customize this for the
``<head>`` or a more specific HTML tag::

    // tags not allowed in <head> will be removed
    $safeInput = $htmlSanitizer->sanitizeFor('head', $userInput);

    // encodes the returned HTML using HTML entities
    $safeInput = $htmlSanitizer->sanitizeFor('title', $userInput);
    $safeInput = $htmlSanitizer->sanitizeFor('textarea', $userInput);

    // uses the <body> context, removing tags only allowed in <head>
    $safeInput = $htmlSanitizer->sanitizeFor('body', $userInput);
    $safeInput = $htmlSanitizer->sanitizeFor('section', $userInput);

Sanitizing HTML from Form Input
-------------------------------

The HTML sanitizer component directly integrates with Symfony Forms, to
sanitize the form input before it is processed by your application.

You can enable the sanitizer in ``TextType`` forms, or any form extending
this type (such as ``TextareaType``), using the ``sanitize_html`` option::

    // src/Form/BlogPostType.php
    namespace App\Form;

    // ...
    class BlogPostType extends AbstractType
    {
        // ...

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'sanitize_html' => true,
                // use the "sanitizer" option to use a custom sanitizer (see below)
                //'sanitizer' => 'app.post_sanitizer',
            ]);
        }
    }

.. _html-sanitizer-twig:

Sanitizing HTML in Twig Templates
---------------------------------

Besides sanitizing user input, you can also sanitize HTML code before
outputting it in a Twig template using the ``sanitize_html()`` filter:

.. code-block:: twig

    {{ post.body|sanitize_html }}

    {# you can also use a custom sanitizer (see below) #}
    {{ post.body|sanitize_html('app.post_sanitizer') }}

.. _html-sanitizer-configuration:

Configuration
-------------

The behavior of the HTML sanitizer can be fully customized. This allows you
to explicitly state which elements, attributes and even attribute values
are allowed.

You can do this by defining a new HTML sanitizer in the configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/html_sanitizer.yaml
        framework:
            html_sanitizer:
                sanitizers:
                    app.post_sanitizer:
                        block_elements:
                            - h1

    .. code-block:: xml

        <!-- config/packages/html_sanitizer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:html-sanitizer>
                    <framework:sanitizer name="app.post_sanitizer">
                        <framework:block-element name="h1"/>
                    </framework:sanitizer>
                </framework:html-sanitizer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->htmlSanitizer()
                ->sanitizer('app.post_sanitizer')
                    ->blockElement('h1')
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

        $postSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->blockElement('h1')
        );

This configuration defines a new ``html_sanitizer.sanitizer.app.post_sanitizer``
service. This service will be :doc:`autowired </service_container/autowiring>`
for services having an ``HtmlSanitizerInterface $appPostSanitizer`` parameter.

Allow Element Baselines
~~~~~~~~~~~~~~~~~~~~~~~

You can start the custom HTML sanitizer by using one of the two baselines:

Static elements
    All elements and attributes on the baseline allow lists from the
    `W3C Standard Proposal`_ (this does not include scripts).
Safe elements
    All elements and attributes from the "static elements" list, excluding
    elements and attributes that can also lead to CSS
    injection/click-jacking.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/html_sanitizer.yaml
        framework:
            html_sanitizer:
                sanitizers:
                    app.post_sanitizer:
                        # enable either of these
                        allow_safe_elements: true
                        allow_static_elements: true

    .. code-block:: xml

        <!-- config/packages/html_sanitizer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:html-sanitizer>
                    <!-- allow-safe-elements/allow-static-elements:
                         enable either of these -->
                    <framework:sanitizer
                        name="app.post_sanitizer"
                        allow-safe-elements="true"
                        allow-static-elements="true"
                    />
                </framework:html-sanitizer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->htmlSanitizer()
                ->sanitizer('app.post_sanitizer')
                    // enable either of these
                    ->allowSafeElements(true)
                    ->allowStaticElements(true)
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

        $postSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                // enable either of these
                ->allowSafeElements()
                ->allowStaticElements()
        );

Allow Elements
~~~~~~~~~~~~~~

This adds elements to the allow list. For each element, you can also
specify the allowed attributes on that element. If not given, all allowed
attributes from the `W3C Standard Proposal`_ are allowed.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/html_sanitizer.yaml
        framework:
            html_sanitizer:
                sanitizers:
                    app.post_sanitizer:
                        # ...
                        allow_elements:
                            # allow the <article> element and 2 attributes
                            article: ['class', 'data-attr']
                            # allow the <img> element and preserve the src attribute
                            img: 'src'
                            # allow the <h1> element with all safe attributes
                            h1: '*'

    .. code-block:: xml

        <!-- config/packages/html_sanitizer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:html-sanitizer>
                    <!-- allow-safe-elements/allow-static-elements:
                         enable either of these -->
                    <framework:sanitizer name="app.post_sanitizer">
                        <!-- allow the <article> element and 2 attributes -->
                        <framework:allow-element name="article">
                            <framework:attribute>class</framework:attribute>
                            <framework:attribute>data-attr</framework:attribute>
                        </framework:allow-element>

                        <!-- allow the <img> element and preserve the src attribute -->
                        <framework:allow-element name="img">
                            <framework:attribute>src</framework:attribute>
                        </framework:allow-element>

                        <!-- allow the <h1> element with all safe attributes -->
                        <framework:allow-element name="img">
                            <framework:attribute>*</framework:attribute>
                        </framework:allow-element>
                    </framework:sanitizer>
                </framework:html-sanitizer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->htmlSanitizer()
                ->sanitizer('app.post_sanitizer')
                    // allow the <article> element and 2 attributes
                    ->allowElement('article', ['class', 'data-attr'])

                    // allow the <img> element and preserve the src attribute
                    ->allowElement('img', 'src')

                    // allow the <h1> element with all safe attributes
                    ->allowElement('h1', '*')
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

        $postSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                // allow the <article> element and 2 attributes
                ->allowElement('article', ['class', 'data-attr'])

                // allow the <img> element and preserve the src attribute
                ->allowElement('img', 'src')

                // allow the <h1> element with all safe attributes
                ->allowElement('h1')
        );

Block and Drop Elements
~~~~~~~~~~~~~~~~~~~~~~~

You can also block (the element will be removed, but its children
will be kept) or drop (the element and its children will be removed)
elements.

This can also be used to remove elements from the allow list.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/html_sanitizer.yaml
        framework:
            html_sanitizer:
                sanitizers:
                    app.post_sanitizer:
                        # ...

                        # remove <div>, but process the children
                        block_elements: ['div']
                        # remove <figure> and its children
                        drop_elements: ['figure']

    .. code-block:: xml

        <!-- config/packages/html_sanitizer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:html-sanitizer>
                    <!-- remove <div>, but process the children -->
                    <framework:block-element>div</framework:block-element>

                    <!-- remove <figure> and its children -->
                    <framework:drop-element>figure</framework:drop-element>
                </framework:html-sanitizer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->htmlSanitizer()
                ->sanitizer('app.post_sanitizer')
                    // remove <div>, but process the children
                    ->blockElement('div')
                    // remove <figure> and its children
                    ->dropElement('figure')
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

        $postSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                // remove <div>, but process the children
                ->blockElement('div')
                // remove <figure> and its children
                ->dropElement('figure')
        );

Allow Attributes
~~~~~~~~~~~~~~~~

Using this option, you can specify which attributes will be preserved in
the returned HTML. The attribute will be allowed on the given elements, or
on all elements allowed *before this setting*.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/html_sanitizer.yaml
        framework:
            html_sanitizer:
                sanitizers:
                    app.post_sanitizer:
                        # ...
                        allow_attributes:
                            # allow "src' on <iframe> elements
                            src: ['iframe']

                            # allow "data-attr" on all elements currently allowed
                            data-attr: '*'

    .. code-block:: xml

        <!-- config/packages/html_sanitizer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:html-sanitizer>
                    <!-- allow "src' on <iframe> elements -->
                    <framework:allow-attribute name="src">
                        <framework:element>iframe</framework:element>
                    </framework:allow-attribute>

                    <!-- allow "data-attr" on all elements currently allowed -->
                    <framework:allow-attribute name="data-attr">
                        <framework:element>*</framework:element>
                    </framework:allow-attribute>
                </framework:html-sanitizer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->htmlSanitizer()
                ->sanitizer('app.post_sanitizer')
                    // allow "src' on <iframe> elements
                    ->allowAttribute('src', ['iframe'])

                    // allow "data-attr" on all elements currently allowed
                    ->allowAttribute('data-attr', '*')
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

        $postSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                // allow "src' on <iframe> elements
                ->allowAttribute('src', ['iframe'])

                // allow "data-attr" on all elements currently allowed
                ->allowAttribute('data-attr', '*')
        );

Drop Attributes
~~~~~~~~~~~~~~~

This option allows you to disallow attributes that were allowed before.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/html_sanitizer.yaml
        framework:
            html_sanitizer:
                sanitizers:
                    app.post_sanitizer:
                        # ...
                        allow_attributes:
                            # allow the "data-attr" on all safe elements...
                            data-attr: '*'

                        drop_attributes:
                            # ...except for the <section> element
                            data-attr: ['section']
                            # disallows "style' on any allowed element
                            style: '*'

    .. code-block:: xml

        <!-- config/packages/html_sanitizer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:html-sanitizer>
                    <!-- allow the "data-attr" on all safe elements... -->
                    <framework:allow-attribute name="data-attr">
                        <framework:element>*</framework:element>
                    </framework:allow-attribute>

                    <!-- ...except for the <section> element -->
                    <framework:drop-attribute name="data-attr">
                        <framework:element>section</framework:element>
                    </framework:drop-attribute>

                    <!-- disallows "style' on any allowed element -->
                    <framework:drop-attribute name="style">
                        <framework:element>*</framework:element>
                    </framework:drop-attribute>
                </framework:html-sanitizer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->htmlSanitizer()
                ->sanitizer('app.post_sanitizer')
                    // allow the "data-attr" on all safe elements...
                    ->allowAttribute('data-attr', '*')

                    // ...except for the <section> element
                    ->dropAttribute('data-attr', ['section'])

                    // disallows "style' on any allowed element
                    ->dropAttribute('style', '*')
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

        $postSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                // allow the "data-attr" on all safe elements...
                ->allowAttribute('data-attr', '*')

                // ...except for the <section> element
                ->dropAttribute('data-attr', ['section'])

                // disallows "style' on any allowed element
                ->dropAttribute('style', '*')
        );

Force Attribute Values
~~~~~~~~~~~~~~~~~~~~~~

Using this option, you can force an attribute with a given value on an
element. For instance, use the follow config to always set ``rel="noopener noreferrer"`` on each ``<a>``
element (even if the original one didn't contain a ``rel`` attribute):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/html_sanitizer.yaml
        framework:
            html_sanitizer:
                sanitizers:
                    app.post_sanitizer:
                        # ...
                        force_attributes:
                            a:
                                rel: noopener noreferrer

    .. code-block:: xml

        <!-- config/packages/html_sanitizer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:html-sanitizer>
                    <framework:force-attribute name="a">
                        <framework:attribute name="rel">noopener noreferrer</framework:attribute>
                    </framework:force-attribute>
                </framework:html-sanitizer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->htmlSanitizer()
                ->sanitizer('app.post_sanitizer')
                    ->forceAttribute('a', ['rel' => 'noopener noreferrer'])
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

        $postSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
        );

.. _html-sanitizer-link-url:

Force/Allow Link URLs
~~~~~~~~~~~~~~~~~~~~~

Besides allowing/blocking elements and attributes, you can also control the
URLs of ``<a>`` elements:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/html_sanitizer.yaml
        framework:
            html_sanitizer:
                sanitizers:
                    app.post_sanitizer:
                        # ...

                        # if `true`, all URLs using the `http://` scheme will be converted to
                        # use the `https://` scheme instead. `http` still needs to be allowed
                        # in `allowed_link_schemes`
                        force_https_urls: true

                        # specifies the allowed URL schemes. If the URL has a different scheme, the
                        # attribute will be dropped
                        allowed_link_schemes: ['http', 'https', 'mailto']

                        # specifies the allowed hosts, the attribute will be dropped if the
                        # URL contains a different host. Subdomains are allowed: e.g. the following
                        # config would also allow 'www.symfony.com', 'live.symfony.com', etc.
                        allowed_link_hosts: ['symfony.com']

                        # whether to allow relative links (i.e. URLs without scheme and host)
                        allow_relative_links: true

    .. code-block:: xml

        <!-- config/packages/html_sanitizer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- force-https-urls: if `true`, all URLs using the `http://` scheme will be
                                       converted to use the `https://` scheme instead.
                                       `http` still needs to be allowed in `allowed-link-scheme` -->
                <!-- allow-relative-links: whether to allow relative links (i.e. URLs without
                                           scheme and host) -->
                <framework:html-sanitizer
                    force-https-urls="true"
                    allow-relative-links="true"
                >
                    <!-- specifies the allowed URL schemes. If the URL has a different scheme,
                         the attribute will be dropped -->
                    <allowed-link-scheme>http</allowed-link-scheme>
                    <allowed-link-scheme>https</allowed-link-scheme>
                    <allowed-link-scheme>mailto</allowed-link-scheme>

                    <!-- specifies the allowed hosts, the attribute will be dropped if the
                         URL contains a different host. Subdomains are allowed: e.g. the following
                         config would also allow 'www.symfony.com', 'live.symfony.com', etc. -->
                    <allowed-link-host>symfony.com</allowed-link-host>
                </framework:html-sanitizer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->htmlSanitizer()
                ->sanitizer('app.post_sanitizer')
                    // if `true`, all URLs using the `http://` scheme will be converted to
                    // use the `https://` scheme instead. `http` still needs to be
                    // allowed in `allowedLinkSchemes`
                    ->forceHttpsUrls(true)

                    // specifies the allowed URL schemes. If the URL has a different scheme, the
                    // attribute will be dropped
                    ->allowedLinkSchemes(['http', 'https', 'mailto'])

                    // specifies the allowed hosts, the attribute will be dropped if the
                    // URL contains a different host. Subdomains are allowed: e.g. the following
                    // config would also allow 'www.symfony.com', 'live.symfony.com', etc.
                    ->allowedLinkHosts(['symfony.com'])

                    // whether to allow relative links (i.e. URLs without scheme and host)
                    ->allowRelativeLinks(true)
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

        $postSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                // if `true`, all URLs using the `http://` scheme will be converted to
                // use the `https://` scheme instead. `http` still needs to be
                // allowed in `allowedLinkSchemes`
                ->forceHttpsUrls()

                // specifies the allowed URL schemes. If the URL has a different scheme, the
                // attribute will be dropped
                ->allowedLinkSchemes(['http', 'https', 'mailto'])

                // specifies the allowed hosts, the attribute will be dropped if the
                // URL contains a different host which is not a subdomain of the allowed host
                ->allowedLinkHosts(['symfony.com']) // Also allows any subdomain (i.e. www.symfony.com)

                // whether to allow relative links (i.e. URLs without scheme and host)
                ->allowRelativeLinks()
        );

Force/Allow Media URLs
~~~~~~~~~~~~~~~~~~~~~~

Like :ref:`link URLs <html-sanitizer-link-url>`, you can also control the
URLs of other media in the HTML. The following attributes are checked by
the HTML sanitizer: ``src``, ``href``, ``lowsrc``, ``background`` and ``ping``.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/html_sanitizer.yaml
        framework:
            html_sanitizer:
                sanitizers:
                    app.post_sanitizer:
                        # ...

                        # if `true`, all URLs using the `http://` scheme will be converted to
                        # use the `https://` scheme instead. `http` still needs to be allowed
                        # in `allowed_media_schemes`
                        force_https_urls: true

                        # specifies the allowed URL schemes. If the URL has a different scheme, the
                        # attribute will be dropped
                        allowed_media_schemes: ['http', 'https', 'mailto']

                        # specifies the allowed hosts, the attribute will be dropped if the URL
                        # contains a different host which is not a subdomain of the allowed host
                        allowed_media_hosts: ['symfony.com'] # Also allows any subdomain (i.e. www.symfony.com)

                        # whether to allow relative URLs (i.e. URLs without scheme and host)
                        allow_relative_medias: true

    .. code-block:: xml

        <!-- config/packages/html_sanitizer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- force-https-urls: if `true`, all URLs using the `http://` scheme will be
                                       converted to use the `https://` scheme instead. `http`
                                       still needs to be allowed in `allowed-media-scheme` -->
                <!-- allow-relative-medias: whether to allow relative URLs (i.e. URLs without
                                           scheme and host) -->
                <framework:html-sanitizer
                    force-https-urls="true"
                    allow-relative-medias="true"
                >
                    <!-- specifies the allowed URL schemes. If the URL has a different scheme,
                         the attribute will be dropped -->
                    <allowed-media-scheme>http</allowed-media-scheme>
                    <allowed-media-scheme>https</allowed-media-scheme>
                    <allowed-media-scheme>mailto</allowed-media-scheme>

                    <!-- specifies the allowed hosts, the attribute will be dropped if the URL
                         contains a different host which is not a subdomain of the allowed host.
                         Also allows any subdomain (i.e. www.symfony.com) -->
                    <allowed-media-host>symfony.com</allowed-media-host>
                </framework:html-sanitizer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->htmlSanitizer()
                ->sanitizer('app.post_sanitizer')
                    // if `true`, all URLs using the `http://` scheme will be converted to
                    // use the `https://` scheme instead. `http` still needs to be
                    // allowed in `allowedMediaSchemes`
                    ->forceHttpsUrls(true)

                    // specifies the allowed URL schemes. If the URL has a different scheme, the
                    // attribute will be dropped
                    ->allowedMediaSchemes(['http', 'https', 'mailto'])

                    // specifies the allowed hosts, the attribute will be dropped if the URL
                    // contains a different host which is not a subdomain of the allowed host
                    ->allowedMediaHosts(['symfony.com']) // Also allows any subdomain (i.e. www.symfony.com)

                    // whether to allow relative URLs (i.e. URLs without scheme and host)
                    ->allowRelativeMedias(true)
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

        $postSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                // if `true`, all URLs using the `http://` scheme will be converted to
                // use the `https://` scheme instead. `http` still needs to be
                // allowed in `allowedMediaSchemes`
                ->forceHttpsUrls()

                // specifies the allowed URL schemes. If the URL has a different scheme, the
                // attribute will be dropped
                ->allowedMediaSchemes(['http', 'https', 'mailto'])

                // specifies the allowed hosts, the attribute will be dropped if the URL
                // contains a different host which is not a subdomain of the allowed host
                ->allowedMediaHosts(['symfony.com']) // Also allows any subdomain (i.e. www.symfony.com)

                // whether to allow relative URLs (i.e. URLs without scheme and host)
                ->allowRelativeMedias()
        );

Max Input Length
~~~~~~~~~~~~~~~~

In order to prevent `DoS attacks`_, by default the HTML sanitizer limits the
input length to ``20000`` characters (as measured by ``strlen($input)``). All
the contents exceeding that length will be truncated. Use this option to
increase or decrease this limit:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/html_sanitizer.yaml
        framework:
            html_sanitizer:
                sanitizers:
                    app.post_sanitizer:
                        # ...

                        # inputs longer (in characters) than this value will be truncated
                        max_input_length: 30000 # default: 20000

    .. code-block:: xml

        <!-- config/packages/html_sanitizer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:html-sanitizer>
                    <framework:sanitizer name="app.post_sanitizer">
                        <!-- inputs longer (in characters) than this value will be truncated (default: 20000) -->
                        <framework:max-input-length>20000</framework:max-input-length>
                    </framework:sanitizer>
                </framework:html-sanitizer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->htmlSanitizer()
                ->sanitizer('app.post_sanitizer')
                    // inputs longer (in characters) than this value will be truncated (default: 20000)
                    ->withMaxInputLength(20000)
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

        $postSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                // inputs longer (in characters) than this value will be truncated (default: 20000)
                ->withMaxInputLength(20000)
        );

It is possible to disable this length limit by setting the max input length to
``-1``. Beware that it may expose your application to `DoS attacks`_.

Custom Attribute Sanitizers
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Controlling the link and media URLs is done by the
:class:`Symfony\\Component\\HtmlSanitizer\\Visitor\\AttributeSanitizer\\UrlAttributeSanitizer`.
You can also implement your own attribute sanitizer, to control the value
of other attributes in the HTML. Create a class implementing
:class:`Symfony\\Component\\HtmlSanitizer\\Visitor\\AttributeSanitizer\\AttributeSanitizerInterface`
and register it as a service. After this, use ``with_attribute_sanitizers``
to enable it for an HTML sanitizer:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/html_sanitizer.yaml
        framework:
            html_sanitizer:
                sanitizers:
                    app.post_sanitizer:
                        # ...
                        with_attribute_sanitizers:
                            - App\Sanitizer\CustomAttributeSanitizer

                        # you can also disable previously enabled custom attribute sanitizers
                        #without_attribute_sanitizers:
                        #    - App\Sanitizer\CustomAttributeSanitizer

    .. code-block:: xml

        <!-- config/packages/html_sanitizer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:html-sanitizer>
                    <with-attribute-sanitizer>App\Sanitizer\CustomAttributeSanitizer</with-attribute-sanitizer>

                    <!-- you can also disable previously enabled attribute sanitizers -->
                    <without-attribute-sanitizer>Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\UrlAttributeSanitizer</without-attribute-sanitizer>
                </framework:html-sanitizer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use App\Sanitizer\CustomAttributeSanitizer;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->htmlSanitizer()
                ->sanitizer('app.post_sanitizer')
                    ->withAttributeSanitizer(CustomAttributeSanitizer::class)

                    // you can also disable previously enabled attribute sanitizers
                    //->withoutAttributeSanitizer(CustomAttributeSanitizer::class)
            ;
        };

    .. code-block:: php-standalone

        use App\Sanitizer\CustomAttributeSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
        use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

        $customAttributeSanitizer = new CustomAttributeSanitizer();
        $postSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->withAttributeSanitizer($customAttributeSanitizer)

                // you can also disable previously enabled attribute sanitizers
                //->withoutAttributeSanitizer($customAttributeSanitizer)
        );

.. _`HTML Sanitizer W3C Standard Proposal`: https://wicg.github.io/sanitizer-api/
.. _`W3C Standard Proposal`: https://wicg.github.io/sanitizer-api/
.. _`DoS attacks`: https://en.wikipedia.org/wiki/Denial-of-service_attack
