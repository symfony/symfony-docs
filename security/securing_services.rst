.. index::
   single: Security; Securing any service
   single: Security; Securing any method

How to Secure any Service or Method in your Application
=======================================================

In the security article, you can see how to
:ref:`secure a controller <security-securing-controller>` by requesting
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
services see the :doc:`/service_container` article. For example, suppose you
have a ``NewsletterManager`` class that sends out emails and you want to
restrict its use to only users who have some ``ROLE_NEWSLETTER_ADMIN`` role.
Before you add security, the class looks something like this::

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
service into the object::

    // src/AppBundle/Newsletter/NewsletterManager.php

    // ...
    use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    class NewsletterManager
    {
        protected $authorizationChecker;

        public function __construct(AuthorizationCheckerInterface $authorizationChecker)
        {
            $this->authorizationChecker = $authorizationChecker;
        }

        public function sendNewsletter()
        {
            if (!$this->authorizationChecker->isGranted('ROLE_NEWSLETTER_ADMIN')) {
                throw new AccessDeniedException();
            }

            // ...
        }

        // ...
    }

If you're using the :ref:`default services.yml configuration <service-container-services-load-example>`,
Symfony will automatically pass the ``security.authorization_checker`` to your service
thanks to autowiring and the ``AuthorizationCheckerInterface`` type-hint.

If the current user does not have the ``ROLE_NEWSLETTER_ADMIN``, they will
be prompted to log in.

Securing Methods Using Annotations
----------------------------------

You can also secure method calls in any service with annotations by using the
optional `JMSSecurityExtraBundle`_ bundle. This bundle is not included in the
Symfony Standard Distribution, but you can choose to install it.

See the `JMSSecurityExtraBundle Documentation`_ for more details.

.. _`JMSSecurityExtraBundle`: https://github.com/schmittjoh/JMSSecurityExtraBundle
.. _`JMSSecurityExtraBundle Documentation`: http://jmsyst.com/bundles/JMSSecurityExtraBundle
