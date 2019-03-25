.. index::
    single: Sessions, saving locale

Making the Locale "Sticky" during a User's Session
==================================================

Symfony stores the locale setting in the Request, which means that this setting
is not automatically saved ("sticky") across requests. But, you *can* store the locale
in the session, so that it's used on subsequent requests.

.. _creating-a-LocaleSubscriber:

Creating a LocaleSubscriber
---------------------------

Create a :ref:`new event subscriber <events-subscriber>`. Typically, ``_locale``
is used as a routing parameter to signify the locale, though you can determine the
correct locale however you want::

    // src/EventSubscriber/LocaleSubscriber.php
    namespace App\EventSubscriber;

    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    use Symfony\Component\HttpKernel\KernelEvents;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class LocaleSubscriber implements EventSubscriberInterface
    {
        private $defaultLocale;

        public function __construct($defaultLocale = 'en')
        {
            $this->defaultLocale = $defaultLocale;
        }

        public function onKernelRequest(GetResponseEvent $event)
        {
            $request = $event->getRequest();
            if (!$request->hasPreviousSession()) {
                return;
            }

            // try to see if the locale has been set as a _locale routing parameter
            if ($locale = $request->attributes->get('_locale')) {
                $request->getSession()->set('_locale', $locale);
            } else {
                // if no explicit locale has been set on this request, use one from the session
                $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
            }
        }

        public static function getSubscribedEvents()
        {
            return [
                // must be registered before (i.e. with a higher priority than) the default Locale listener
                KernelEvents::REQUEST => [['onKernelRequest', 20]],
            ];
        }
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
you're done! Symfony will automatically know about the event subscriber and call
the ``onKernelRequest`` method on each request.

To see it working, either set the ``_locale`` key on the session manually (e.g.
via some "Change Locale" route & controller), or create a route with a the :ref:`_locale default <translation-locale-url>`.

.. sidebar:: Explicitly Configure the Subscriber

    You can also explicitly configure it, in order to pass in the :ref:`default_locale <config-framework-default_locale>`:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            services:
                # ...

                App\EventSubscriber\LocaleSubscriber:
                    arguments: ['%kernel.default_locale%']
                    # uncomment the next line if you are not using autoconfigure
                    # tags: [kernel.event_subscriber]

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <service id="App\EventSubscriber\LocaleSubscriber">
                        <argument>%kernel.default_locale%</argument>

                        <!-- uncomment the next line if you are not using autoconfigure -->
                        <!-- <tag name="kernel.event_subscriber"/> -->
                    </service>
                </services>
            </container>

        .. code-block:: php

            // config/services.php
            use App\EventSubscriber\LocaleSubscriber;

            $container->register(LocaleSubscriber::class)
                ->addArgument('%kernel.default_locale%')
                // uncomment the next line if you are not using autoconfigure
                // ->addTag('kernel.event_subscriber');

That's it! Now celebrate by changing the user's locale and seeing that it's
sticky throughout the request.

Remember, to get the user's locale, always use the :method:`Request::getLocale <Symfony\\Component\\HttpFoundation\\Request::getLocale>`
method::

    // from a controller...
    use Symfony\Component\HttpFoundation\Request;

    public function index(Request $request)
    {
        $locale = $request->getLocale();
    }

Setting the Locale Based on the User's Preferences
--------------------------------------------------

You might want to improve this technique even further and define the locale based on
the user entity of the logged in user. However, since the ``LocaleSubscriber`` is called
before the ``FirewallListener``, which is responsible for handling authentication and
setting the user token on the ``TokenStorage``, you have no access to the user
which is logged in.

Suppose you have a ``locale`` property on your ``User`` entity and
want to use this as the locale for the given user. To accomplish this,
you can hook into the login process and update the user's session with this
locale value before they are redirected to their first page.

To do this, you need an event subscriber on the ``security.interactive_login``
event::

    // src/EventSubscriber/UserLocaleSubscriber.php
    namespace App\EventSubscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpFoundation\Session\SessionInterface;
    use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
    use Symfony\Component\Security\Http\SecurityEvents;

    /**
     * Stores the locale of the user in the session after the
     * login. This can be used by the LocaleSubscriber afterwards.
     */
    class UserLocaleSubscriber implements EventSubscriberInterface
    {
        private $session;

        public function __construct(SessionInterface $session)
        {
            $this->session = $session;
        }

        public function onInteractiveLogin(InteractiveLoginEvent $event)
        {
            $user = $event->getAuthenticationToken()->getUser();

            if (null !== $user->getLocale()) {
                $this->session->set('_locale', $user->getLocale());
            }
        }

        public static function getSubscribedEvents()
        {
            return [
                SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
            ];
        }
    }

.. caution::

    In order to update the language immediately after a user has changed
    their language preferences, you also need to update the session when you change
    the ``User`` entity.
