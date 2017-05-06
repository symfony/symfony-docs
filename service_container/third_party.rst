.. index::
    single: DependencyInjection; Third-Party Bundles
    single: Service Container; Third-Party Bundles

How to Work with Services Provided by Third-Party Bundles
=========================================================

Since Symfony and all third-party bundles configure and retrieve their services
via the container, you can easily access them or even use them in your own
services. To keep things simple, Symfony by default does not require that
controllers must be defined as services. Furthermore, Symfony injects the entire
service container into your controller. For example, to handle the storage of
information on a user's session, Symfony provides a ``session`` service,
which you can access inside a standard controller as follows::

    public function indexAction($bar)
    {
        $session = $this->get('session');
        $session->set('foo', $bar);

        // ...
    }

In Symfony, you'll constantly use services provided by the Symfony core or
other third-party bundles to perform tasks such as rendering templates (``templating``),
sending emails (``mailer``), or accessing information on the request through the request stack (``request_stack``).

You can take this a step further by using these services inside services that
you've created for your application. Beginning by modifying the ``NewsletterManager``
to use the real Symfony ``mailer`` service (instead of the pretend ``app.mailer``).
Also pass the templating engine service to the ``NewsletterManager``
so that it can generate the email content via a template::

    // src/AppBundle/Newsletter/NewsletterManager.php
    namespace AppBundle\Newsletter;

    use Symfony\Component\Templating\EngineInterface;

    class NewsletterManager
    {
        protected $mailer;

        protected $templating;

        public function __construct(
            \Swift_Mailer $mailer,
            EngineInterface $templating
        ) {
            $this->mailer = $mailer;
            $this->templating = $templating;
        }

        // ...
    }

Configuring the service container is easy:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.newsletter_manager:
                class:     AppBundle\Newsletter\NewsletterManager
                arguments: ['@mailer', '@templating']

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <service id="app.newsletter_manager" class="AppBundle\Newsletter\NewsletterManager">
                <argument type="service" id="mailer"/>
                <argument type="service" id="templating"/>
            </service>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Newsletter\NewsletterManager;

        $container->register('app.newsletter_manager', NewsletterManager::class)
            ->setArguments(array(
                new Reference('mailer'),
                new Reference('templating'),
            ));

The ``app.newsletter_manager`` service now has access to the core ``mailer``
and ``templating`` services. This is a common way to create services specific
to your application that leverage the power of different services within
the framework.

.. tip::

    Be sure that the ``swiftmailer`` entry appears in your application
    configuration. As was mentioned in :ref:`service-container-extension-configuration`,
    the ``swiftmailer`` key invokes the service extension from the
    SwiftmailerBundle, which registers the ``mailer`` service.
