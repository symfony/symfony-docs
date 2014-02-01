.. index::
   single: Security; Securing any service
   single: Security; Securing any method

How to secure any Service or Method in your Application
=======================================================

In the security chapter, you can see how to :ref:`secure a controller <book-security-securing-controller>`
by requesting the ``security.context`` service from the Service Container
and checking the current user's role::

    // ...
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    public function helloAction($name)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        // ...
    }

You can also secure *any* service in a similar way by injecting the ``security.context``
service into it. For a general introduction to injecting dependencies into
services see the :doc:`/book/service_container` chapter of the book. For
example, suppose you have a ``NewsletterManager`` class that sends out emails
and you want to restrict its use to only users who have some ``ROLE_NEWSLETTER_ADMIN``
role. Before you add security, the class looks something like this:

.. code-block:: php

    // src/Acme/HelloBundle/Newsletter/NewsletterManager.php
    namespace Acme\HelloBundle\Newsletter;

    class NewsletterManager
    {

        public function sendNewsletter()
        {
            // ... where you actually do the work
        }

        // ...
    }

Your goal is to check the user's role when the ``sendNewsletter()`` method is
called. The first step towards this is to inject the ``security.context``
service into the object. Since it won't make sense *not* to perform the security
check, this is an ideal candidate for constructor injection, which guarantees
that the security context object will be available inside the ``NewsletterManager``
class::

    namespace Acme\HelloBundle\Newsletter;

    use Symfony\Component\Security\Core\SecurityContextInterface;

    class NewsletterManager
    {
        protected $securityContext;

        public function __construct(SecurityContextInterface $securityContext)
        {
            $this->securityContext = $securityContext;
        }

        // ...
    }

Then in your service configuration, you can inject the service:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        parameters:
            newsletter_manager.class: Acme\HelloBundle\Newsletter\NewsletterManager

        services:
            newsletter_manager:
                class:     "%newsletter_manager.class%"
                arguments: ["@security.context"]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <parameters>
            <parameter key="newsletter_manager.class">Acme\HelloBundle\Newsletter\NewsletterManager</parameter>
        </parameters>

        <services>
            <service id="newsletter_manager" class="%newsletter_manager.class%">
                <argument type="service" id="security.context"/>
            </service>
        </services>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setParameter('newsletter_manager.class', 'Acme\HelloBundle\Newsletter\NewsletterManager');

        $container->setDefinition('newsletter_manager', new Definition(
            '%newsletter_manager.class%',
            array(new Reference('security.context'))
        ));

The injected service can then be used to perform the security check when the
``sendNewsletter()`` method is called::

    namespace Acme\HelloBundle\Newsletter;

    use Symfony\Component\Security\Core\Exception\AccessDeniedException;
    use Symfony\Component\Security\Core\SecurityContextInterface;
    // ...

    class NewsletterManager
    {
        protected $securityContext;

        public function __construct(SecurityContextInterface $securityContext)
        {
            $this->securityContext = $securityContext;
        }

        public function sendNewsletter()
        {
            if (false === $this->securityContext->isGranted('ROLE_NEWSLETTER_ADMIN')) {
                throw new AccessDeniedException();
            }

            // ...
        }

        // ...
    }

If the current user does not have the ``ROLE_NEWSLETTER_ADMIN``, they will
be prompted to log in.

Securing Methods Using Annotations
----------------------------------

You can also secure method calls in any service with annotations by using the
optional `JMSSecurityExtraBundle`_ bundle. This bundle is not included in the
Symfony2 Standard Distribution, but you can choose to install it.

To enable the annotations functionality, :ref:`tag <book-service-container-tags>`
the service you want to secure with the ``security.secure_service`` tag
(you can also automatically enable this functionality for all services, see
the :ref:`sidebar <securing-services-annotations-sidebar>` below):

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml

        # ...
        services:
            newsletter_manager:
                # ...
                tags:
                    -  { name: security.secure_service }

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <!-- ... -->

        <services>
            <service id="newsletter_manager" class="%newsletter_manager.class%">
                <!-- ... -->
                <tag name="security.secure_service" />
            </service>
        </services>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $definition = new Definition(
            '%newsletter_manager.class%',
            array(new Reference('security.context'))
        ));
        $definition->addTag('security.secure_service');
        $container->setDefinition('newsletter_manager', $definition);

You can then achieve the same results as above using an annotation::

    namespace Acme\HelloBundle\Newsletter;

    use JMS\SecurityExtraBundle\Annotation\Secure;
    // ...

    class NewsletterManager
    {

        /**
         * @Secure(roles="ROLE_NEWSLETTER_ADMIN")
         */
        public function sendNewsletter()
        {
            // ...
        }

        // ...
    }

.. note::

    The annotations work because a proxy class is created for your class
    which performs the security checks. This means that, whilst you can use
    annotations on public and protected methods, you cannot use them with
    private methods or methods marked final.

The JMSSecurityExtraBundle also allows you to secure the parameters and return
values of methods. For more information, see the `JMSSecurityExtraBundle`_
documentation.

.. _securing-services-annotations-sidebar:

.. sidebar:: Activating the Annotations Functionality for all Services

    When securing the method of a service (as shown above), you can either
    tag each service individually, or activate the functionality for *all*
    services at once. To do so, set the ``secure_all_services`` configuration
    option to true:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            jms_security_extra:
                # ...
                secure_all_services: true

        .. code-block:: xml

            <!-- app/config/config.xml -->
            <?xml version="1.0" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:jms-security-extra="http://example.org/schema/dic/jms_security_extra"
                xsi:schemaLocation="http://www.example.com/symfony/schema/ http://www.example.com/symfony/schema/hello-1.0.xsd">

                <!-- ... -->
                <jms-security-extra:config secure-controllers="true" secure-all-services="true" />

            </srv:container>

        .. code-block:: php

            // app/config/config.php
            $container->loadFromExtension('jms_security_extra', array(
                // ...
                'secure_all_services' => true,
            ));

    The disadvantage of this method is that, if activated, the initial page
    load may be very slow depending on how many services you have defined.

.. _`JMSSecurityExtraBundle`: https://github.com/schmittjoh/JMSSecurityExtraBundle
