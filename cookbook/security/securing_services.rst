How to secure any Service or Method in your Application
=======================================================

In the :doc:`/book/security` book pages you can see how to secure 
``ContainerAware`` controllers by requesting the ``security.context`` service 
from the Service Container and checking the current user's role:

.. code-block:: php

    use Symfony\Component\Security\Core\Exception\AccessDeniedException
    // ...

    public function helloAction($name)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        // ...
    }

You can also secure any service in a similar way by injecting the 
``security.context`` service into it. For a general introduction to injecting 
dependencies into services see the :doc:`/book/service_container` chapter of 
the book. For example, you have a ``NewsletterManager`` class for sending out 
newsletters and want to restrict its use to only users with the 
``ROLE_NEWSLETTER_ADMIN`` role. The class currently looks like this:

.. code-block:: php

    namespace Acme\HelloBundle\Newsletter;

    class NewsletterManager
    {

        public function sendNewsletter()
        {
            //--
        }

        // ...
    }

You want to check the user's role when the ``sendNewsletter()`` method is 
called. The first step towards this is to inject the ``security.context`` 
service into the object. As we require the service it is an ideal candidate 
for constructor injection as this makes it a required dependency:

.. code-block:: php

    namespace Acme\HelloBundle\Newsletter;

    class NewsletterManager
    {
        protected $securityContext;

        public function __construct($securityContext)
        {
            $this->securityContext = $securityContext;
        }

        // ...
    }

Then in your config you can inject the service:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        parameters:
            # ...
            newsletter_manager.class: Acme\HelloBundle\Newsletter\NewsletterManager

        services:
            my_mailer:
                # ...
            newsletter_manager:
                class:     %newsletter_manager.class%
                arguments: [@security.context]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">Acme\HelloBundle\Newsletter\NewsletterManager</parameter>
        </parameters>

        <services>
            <service id="my_mailer" ... >
              <!-- ... -->
            </service>
            <service id="newsletter_manager" class="%newsletter_manager.class%">
                <argument type="service" id="security.context"/>
            </service>
        </services>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('newsletter_manager.class', 'Acme\HelloBundle\Newsletter\NewsletterManager');

        $container->setDefinition('newsletter_manager', new Definition(
            '%newsletter_manager.class%',
            array(new Reference('security.context'))
        ));

The injected service can then be used to perform the security check when the
``sendNewsletter()`` method is called:

.. code-block:: php::

    namespace Acme\HelloBundle\Newsletter;

    use Symfony\Component\Security\Core\Exception\AccessDeniedException
    use Symfony\Component\Security\Core\SecurityContext;
    // ...

    class NewsletterManager
    {
        protected $securityContext;

        public function __construct(SecurityContext $securityContext)
        {
            $this->securityContext = $securityContext;
        }

        public function sendNewsletter()
        {
            if (false === $this->securityContext->isGranted('ROLE_NEWSLETTER_ADMIN')) {
                throw new AccessDeniedException();
            }
            
            //--
        }

        // ...
    }

If the current user does not have the ``ROLE_NEWSLETTER_ADMIN`` then they 
will be prompted to log in.

Securing Methods Using Annotations
~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also secure method calls in any service using annotations using the 
``SecurityExtraBundle`` optional bundle. This is included in the standard
Symfony2 distribution. The default configuration for the 
``SecurityExtraBundle`` only secures Controllers and not all services:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        jms_security_extra:
            secure_controllers:  true
            secure_all_services: false
    
    .. code-block:: xml

        <!-- app/config/config.xml -->
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <jms_security_extra secure_controllers="true"secure_all_services="true" />
                
        </srv:container>    

    .. code-block:: php
    
        // app/config/config.php
        $container->loadFromExtension('jms_security_extra', array(            
             'secure_controllers'  => true,
             'secure_all_services' => false,
        ));

To use annotations to secure other services you can set ``secure_all_services``
to true. Alternatively you can specify individual services to secure by tagging
them with ``security.secure_service``:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        parameters:
            # ...
            newsletter_manager.class: Acme\HelloBundle\Newsletter\NewsletterManager

        services:
            my_mailer:
                # ...
            newsletter_manager:
                class:     %newsletter_manager.class%
                tags:
                    -  { name: security.secure_service }

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">Acme\HelloBundle\Newsletter\NewsletterManager</parameter>
        </parameters>

        <services>
            <service id="my_mailer" ... >
              <!-- ... -->
            </service>
            <service id="newsletter_manager" class="%newsletter_manager.class%">
                <tag name="security.secure_service" />
            </service>
        </services>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('newsletter_manager.class', 'Acme\HelloBundle\Newsletter\NewsletterManager');

        $definition = new Definition('%newsletter_manager.class%');
        $definition->addTag('security.secure_service');
        $container->setDefinition('newsletter_manager', $definition);        

You can then achieve the same results as above using an annotation:

.. code-block:: php::

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
            //--
        }

        // ...
    }

.. note::

    The annotations work because a proxy class is created for your class
    which performs the security checks. This means that, whilst you can use 
    annotations on public and protected methods, you cannot use them with
    private methods or methods marked final.

The ``SecurityExtraBundle`` also allows you to secure the parameters and return
values of methods. For more information, see the `SecurityExtraBundle`_ 
documentation.

.. _`SecurityExtraBundle`: https://github.com/schmittjoh/SecurityExtraBundle