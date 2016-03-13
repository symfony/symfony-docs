.. index::
   single: Security; Securing any service
   single: Security; Securing any method

How to Secure any Service or Method in your Application
=======================================================

In the security chapter, you can see how to
:ref:`secure a controller <book-security-securing-controller>` by requesting
the ``security.authorization_checker`` service from the Service Container and
checking the current user's role::

    // ...
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    public function helloAction($name)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // ...
    }

You can also secure *any* service by injecting the ``security.authorization_checker``
service into it. For a general introduction to injecting dependencies into
services see the :doc:`/book/service_container` chapter of the book. For
example, suppose you have a ``NewsletterManager`` class that sends out emails
and you want to restrict its use to only users who have some
``ROLE_NEWSLETTER_ADMIN`` role. Before you add security, the class looks
something like this::

    // src/AppBundle/Newsletter/NewsletterManager.php
    namespace AppBundle\Newsletter;

    class NewsletterManager
    {
        public function sendNewsletter()
        {
            // ... where you actually do the work
        }

        // ...
    }

Your goal is to check the user's role when the ``sendNewsletter()`` method is
called. The first step towards this is to inject the ``security.authorization_checker``
service into the object. Since it won't make sense *not* to perform the security
check, this is an ideal candidate for constructor injection, which guarantees
that the authorization checker object will be available inside the ``NewsletterManager``
class::

    // src/AppBundle/Newsletter/NewsletterManager.php

    // ...
    use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

    class NewsletterManager
    {
        protected $authorizationChecker;

        public function __construct(AuthorizationCheckerInterface $authorizationChecker)
        {
            $this->authorizationChecker = $authorizationChecker;
        }

        // ...
    }

Then in your service configuration, you can inject the service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            newsletter_manager:
                class:     AppBundle\Newsletter\NewsletterManager
                arguments: ['@security.authorization_checker']

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="newsletter_manager" class="AppBundle\Newsletter\NewsletterManager">
                    <argument type="service" id="security.authorization_checker"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('newsletter_manager', new Definition(
            'AppBundle\Newsletter\NewsletterManager',
            array(new Reference('security.authorization_checker'))
        ));

The injected service can then be used to perform the security check when the
``sendNewsletter()`` method is called::

    namespace AppBundle\Newsletter;

    use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;
    // ...

    class NewsletterManager
    {
        protected $authorizationChecker;

        public function __construct(AuthorizationCheckerInterface $authorizationChecker)
        {
            $this->authorizationChecker = $authorizationChecker;
        }

        public function sendNewsletter()
        {
            if (false === $this->authorizationChecker->isGranted('ROLE_NEWSLETTER_ADMIN')) {
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
Symfony Standard Distribution, but you can choose to install it.

To enable the annotations functionality, :ref:`tag <book-service-container-tags>`
the service you want to secure with the ``security.secure_service`` tag
(you can also automatically enable this functionality for all services, see
the :ref:`sidebar <securing-services-annotations-sidebar>` below):

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            newsletter_manager:
                class: AppBundle\Newsletter\NewsletterManager
                tags:
                    -  { name: security.secure_service }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="newsletter_manager" class="AppBundle\Newsletter\NewsletterManager">
                    <tag name="security.secure_service" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $definition = new Definition(
            'AppBundle\Newsletter\NewsletterManager',
            // ...
        ));
        $definition->addTag('security.secure_service');
        $container->setDefinition('newsletter_manager', $definition);

You can then achieve the same results as above using an annotation::

    namespace AppBundle\Newsletter;

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
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd">

                <!-- ... -->
                <jms-security-extra:config secure-all-services="true" />
            </container>

        .. code-block:: php

            // app/config/config.php
            $container->loadFromExtension('jms_security_extra', array(
                // ...
                'secure_all_services' => true,
            ));

    The disadvantage of this method is that, if activated, the initial page
    load may be very slow depending on how many services you have defined.

.. _`JMSSecurityExtraBundle`: https://github.com/schmittjoh/JMSSecurityExtraBundle
