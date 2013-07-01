.. index::
    single: Sessions, saving locale

Making the Locale "Sticky" during a User's Session
==================================================

Prior to Symfony 2.1, the locale was stored in a session called ``_locale``.
Since 2.1, it is stored in the Request, which means that it's not "sticky"
during a user's request. In this article, you'll learn how to make the locale
of a user "sticky" so that once it's set, that same locale will be used for
every subsequent request.

Creating LocaleListener
-----------------------

To simulate that the locale is stored in a session, you need to create and
register a :doc:`new event listener</cookbook/service_container/event_listener>`.
The listener will look something like this. Typically, ``_locale`` is used
as a routing parameter to signify the locale, though it doesn't really matter
how you determine the desired locale from the request::

    // src/Acme/LocaleBundle/EventListener/LocaleListener.php
    namespace Acme\LocaleBundle\EventListener;

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
            acme_locale.locale_listener:
                class: Acme\LocaleBundle\EventListener\LocaleListener
                arguments: ["%kernel.default_locale%"]
                tags:
                    - { name: kernel.event_subscriber }

    .. code-block:: xml

        <service id="acme_locale.locale_listener"
            class="Acme\LocaleBundle\EventListener\LocaleListener">
            <argument>%kernel.default_locale%</argument>

            <tag name="kernel.event_subscriber" />
        </service>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $container
            ->setDefinition('acme_locale.locale_listener', new Definition(
                'Acme\LocaleBundle\EventListener\LocaleListener',
                array('%kernel.default_locale%')
            ))
            ->addTag('kernel.event_subscriber')
        ;

That's it! Now celebrate by changing the user's locale and seeing that it's
sticky throughout the request. Remember, to get the user's locale, always
use the :method:`Request::getLocale<Symfony\\Component\\HttpFoundation\\Request::getLocale>`
method::

    // from a controller...
    $locale = $this->getRequest()->getLocale();
