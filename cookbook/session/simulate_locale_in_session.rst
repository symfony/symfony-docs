.. index::
    single: Sessions, saving locale

Simulate old Behaviour of Saving the Locale
===========================================

Prior to Symfony 2.1, the locale was stored in a session called ``_locale``.
Since 2.1, it is stored in the Request. You'll learn how to simulate the old
way in this article.

Creating LocaleListener
-----------------------

To simulate that the locale is stored in a session, you need to create and
register a new listener. The listener will look like the following, assuming
that the parameter which handels the locale value in the request is called
``_locale``::

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

            if ($locale = $request->attributes->get('_locale')) {
                $request->getSession()->set('_locale', $locale);
            } else {
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
