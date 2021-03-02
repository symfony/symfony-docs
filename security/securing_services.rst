.. index::
   single: Security; Securing any service
   single: Security; Securing any method

How to Secure any Service or Method in your Application
=======================================================

In the security article, you learned how to
:ref:`secure a controller <security-securing-controller>` via a shortcut method.

But, you can check access *anywhere* in your code by injecting the ``Security``
service. For example, suppose you have a ``SalesReportManager`` service and you
want to include extra details only for users that have a ``ROLE_SALES_ADMIN`` role:

.. code-block:: diff

      // src/Newsletter/NewsletterManager.php

      // ...
      use Symfony\Component\Security\Core\Exception\AccessDeniedException;
    + use Symfony\Component\Security\Core\Security;

      class SalesReportManager
      {
    +     private $security;

    +     public function __construct(Security $security)
    +     {
    +         $this->security = $security;
    +     }

          public function sendNewsletter()
          {
              $salesData = [];

    +         if ($this->security->isGranted('ROLE_SALES_ADMIN')) {
    +             $salesData['top_secret_numbers'] = rand();
    +         }

              // ...
          }

          // ...
      }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
Symfony will automatically pass the ``security.helper`` to your service
thanks to autowiring and the ``Security`` type-hint.

You can also use a lower-level
:class:`Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationCheckerInterface`
service. It does the same thing as ``Security``, but allows you to type-hint a
more-specific interface.
