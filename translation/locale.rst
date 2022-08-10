.. index::
    single: Translation; Locale

How to Work with the User's Locale
==================================

The locale of the current user is stored in the request and is accessible
via the ``Request`` object::

    use Symfony\Component\HttpFoundation\Request;

    public function index(Request $request)
    {
        $locale = $request->getLocale();
    }

To set the user's locale, you may want to create a custom event listener so
that it's set before any other parts of the system (i.e. the translator) need
it::

        public function onKernelRequest(RequestEvent $event)
        {
            $request = $event->getRequest();

            // some logic to determine the $locale
            $request->setLocale($locale);
        }

.. note::

    The custom listener must be called **before** ``LocaleListener``, which
    initializes the locale based on the current request. To do so, set your
    listener priority to a higher value than ``LocaleListener`` priority (which
    you can obtain by running the ``debug:event kernel.request`` command).

Read :doc:`/session/locale_sticky_session` for more information on making
the user's locale "sticky" to their session.

.. note::

    Setting the locale using ``$request->setLocale()`` in the controller is
    too late to affect the translator. Either set the locale via a listener
    (like above), the URL (see next) or call ``setLocale()`` directly on the
    ``translator`` service.

See the :ref:`translation-locale-url` section below about setting the
locale via routing.

.. _translation-locale-url:

The Locale and the URL
----------------------

Since you can store the locale of the user in the session, it may be tempting
to use the same URL to display a resource in different languages based on
the user's locale. For example, ``http://www.example.com/contact`` could show
content in English for one user and French for another user. Unfortunately,
this violates a fundamental rule of the Web: that a particular URL returns
the same resource regardless of the user. To further muddy the problem, which
version of the content would be indexed by search engines?

A better policy is to include the locale in the URL using the
:ref:`special _locale parameter <routing-locale-parameter>`:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/ContactController.php
        namespace App\Controller;

        // ...
        class ContactController extends AbstractController
        {
            #[Route(
                path: '/{_locale}/contact',
                name: 'contact',
                requirements: [
                    '_locale' => 'en|fr|de',
                ],
            )]
            public function contact()
            {
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        contact:
            path:       /{_locale}/contact
            controller: App\Controller\ContactController::index
            requirements:
                _locale: en|fr|de

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" path="/{_locale}/contact">
                controller="App\Controller\ContactController::index">
                <requirement key="_locale">en|fr|de</requirement>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\ContactController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('contact', '/{_locale}/contact')
                ->controller([ContactController::class, 'index'])
                ->requirements([
                    '_locale' => 'en|fr|de',
                ])
            ;
        };

When using the special ``_locale`` parameter in a route, the matched locale
is *automatically set on the Request* and can be retrieved via the
:method:`Symfony\\Component\\HttpFoundation\\Request::getLocale` method. In
other words, if a user visits the URI ``/fr/contact``, the locale ``fr`` will
automatically be set as the locale for the current request.

You can now use the locale to create routes to other translated pages in your
application.

.. tip::

    Define the locale requirement as a :ref:`container parameter <configuration-parameters>`
    to avoid hardcoding its value in all your routes.

.. index::
    single: Translations; Fallback and default locale

.. _translation-default-locale:

Setting a Default Locale
------------------------

What if the user's locale hasn't been determined? You can guarantee that a
locale is set on each user's request by defining a ``default_locale`` for
the framework:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            default_locale: en

    .. code-block:: xml

        <!-- config/packages/translation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config default-locale="en"/>
        </container>

    .. code-block:: php

        // config/packages/translation.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->defaultLocale('en');
        };
