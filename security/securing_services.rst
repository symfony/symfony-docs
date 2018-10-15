.. index::
   single: Security; Securing any service
   single: Security; Securing any method

How to Secure any Service or Method in your Application
=======================================================

In the security article, you can see how to
:ref:`secure a controller <security-securing-controller>` by requesting
the ``Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface``
service from the Service Container and checking the current user's role::

    // ...
    use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    public function hello(AuthorizationCheckerInterface $authChecker)
    {
        if (!$authChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        // ...
    }

You can also secure *any* service by injecting the authorization checker
service into it. For a general introduction to injecting dependencies into
services see the :doc:`/service_container` article. For example, suppose you
have a ``NewsletterManager`` class that sends out emails and you want to
restrict its use to only users who have some ``ROLE_NEWSLETTER_ADMIN`` role.
Before you add security, the class looks something like this::

    // src/Newsletter/NewsletterManager.php
    namespace App\Newsletter;

    class NewsletterManager
    {
        public function sendNewsletter()
        {
            // ... where you actually do the work
        }

        // ...
    }

Your goal is to check the user's role when the ``sendNewsletter()`` method is
called. The first step towards this is to inject the ``security.helper`` service
using the :class:`Symfony\\Component\\Security\\Core\\Security` class::

    // src/Newsletter/NewsletterManager.php

    // ...
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;
    use Symfony\Component\Security\Core\Security;

    class NewsletterManager
    {
        protected $security;

        public function __construct(Security $security)
        {
            $this->security = $security;
        }

        public function sendNewsletter()
        {
            if (!$this->security->isGranted('ROLE_NEWSLETTER_ADMIN')) {
                throw new AccessDeniedException();
            }

            // ...
        }

        // ...
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
Symfony will automatically pass the ``security.helper`` to your service
thanks to autowiring and the ``Security`` type-hint.

If the current user does not have the ``ROLE_NEWSLETTER_ADMIN``, they will
be prompted to log in.
