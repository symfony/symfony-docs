.. index::
    single: Sessions, saving locale

Making the Locale "Sticky" during a User's Session
==================================================

Prior to Symfony 2.1, the locale was stored in a session attribute called ``_locale``.
Since 2.1, it is stored in the Request, which means that it's not "sticky"
during a user's request. In this article, you'll learn how to make the locale
of a user "sticky" so that once it's set, that same locale will be used for
every subsequent request.

Creating a LocaleListener
-------------------------

To simulate that the locale is stored in a session, you need to create and
register a :doc:`new event listener </cookbook/event_dispatcher/event_listener>`.
The listener will look something like this. Typically, ``_locale`` is used
as a routing parameter to signify the locale, though it doesn't really matter
how you determine the desired locale from the request::

    // src/AppBundle/EventListener/LocaleListener.php
    namespace AppBundle\EventListener;

    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    use Symfony\Component\HttpKernel\KernelEvents;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class LocaleListener implements EventSubscriberInterface
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
            return array(
                // must be registered before the default Locale listener
                KernelEvents::REQUEST => array(array('onKernelRequest', 17)),
            );
        }
    }

Then register the listener:

.. configuration-block::

    .. code-block:: yaml

        services:
            app.locale_listener:
                class: AppBundle\EventListener\LocaleListener
                arguments: ['%kernel.default_locale%']
                tags:
                    - { name: kernel.event_subscriber }

    .. code-block:: xml

        <service id="app.locale_listener"
            class="AppBundle\EventListener\LocaleListener">
            <argument>%kernel.default_locale%</argument>

            <tag name="kernel.event_subscriber" />
        </service>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $container
            ->setDefinition('app.locale_listener', new Definition(
                'AppBundle\EventListener\LocaleListener',
                array('%kernel.default_locale%')
            ))
            ->addTag('kernel.event_subscriber')
        ;

That's it! Now celebrate by changing the user's locale and seeing that it's
sticky throughout the request. Remember, to get the user's locale, always
use the :method:`Request::getLocale <Symfony\\Component\\HttpFoundation\\Request::getLocale>`
method::

    // from a controller...
    use Symfony\Component\HttpFoundation\Request;

    public function indexAction(Request $request)
    {
        $locale = $request->getLocale();
    }

Setting the Locale Based on the User's Preferences
--------------------------------------------------

You might want to improve this technique even further and define the locale based on
the user entity of the logged in user. However, since the ``LocaleListener`` is called
before the ``FirewallListener``, which is responsible for handling authentication and
setting the user token on the ``TokenStorage``, you have no access to the user
which is logged in.

Suppose you have defined a ``locale`` property on your ``User`` entity and
you want to use this as the locale for the given user. To accomplish this,
you can hook into the login process and update the user's session with this
locale value before they are redirected to their first page.

To do this, you need an event listener for the ``security.interactive_login``
event:

.. code-block:: php

    // src/AppBundle/EventListener/UserLocaleListener.php
    namespace AppBundle\EventListener;

    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

    /**
     * Stores the locale of the user in the session after the
     * login. This can be used by the LocaleListener afterwards.
     */
    class UserLocaleListener
    {
        /**
         * @var Session
         */
        private $session;

        public function __construct(Session $session)
        {
            $this->session = $session;
        }

        /**
         * @param InteractiveLoginEvent $event
         */
        public function onInteractiveLogin(InteractiveLoginEvent $event)
        {
            $user = $event->getAuthenticationToken()->getUser();

            if (null !== $user->getLocale()) {
                $this->session->set('_locale', $user->getLocale());
            }
        }
    }

Then register the listener:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.user_locale_listener:
                class: AppBundle\EventListener\UserLocaleListener
                arguments: ['@session']
                tags:
                    - { name: kernel.event_listener, event: security.interactive_login, method: onInteractiveLogin }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.user_locale_listener"
                    class="AppBundle\EventListener\UserLocaleListener">

                    <argument type="service" id="session"/>

                    <tag name="kernel.event_listener"
                        event="security.interactive_login"
                        method="onInteractiveLogin" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register('app.user_locale_listener', 'AppBundle\EventListener\UserLocaleListener')
            ->addArgument('session')
            ->addTag(
                'kernel.event_listener',
                array('event' => 'security.interactive_login', 'method' => 'onInteractiveLogin'
            );

.. caution::

    In order to update the language immediately after a user has changed
    their language preferences, you need to update the session after an update
    to the ``User`` entity.
