Security
========

Authentication and Firewalls (i.e. Getting the User's Credentials)
------------------------------------------------------------------

You can configure Symfony to authenticate your users using any method you
want and to load user information from any source. This is a complex topic,
but the :doc:`Security Cookbook Section </cookbook/security/index>` has a
lot of information about this.

Regardless of your needs, authentication is configured in ``security.yml``,
primarily under the ``firewalls`` key.

.. best-practice::

    Unless you have two legitimately different authentication systems and
    users (e.g. form login for the main site and a token system for your
    API only), we recommend having only *one* firewall entry with the ``anonymous``
    key enabled.

Most applications only have one authentication system and one set of users.
For this reason, you only need *one* firewall entry. There are exceptions
of course, especially if you have separated web and API sections on your
site. But the point is to keep things simple.

Additionally, you should use the ``anonymous`` key under your firewall. If
you need to require users to be logged in for different sections of your
site (or maybe nearly *all* sections), use the ``access_control`` area.

.. best-practice::

    Use the ``bcrypt`` encoder for encoding your users' passwords.

If your users have a password, then we recommend encoding it using the ``bcrypt``
encoder, instead of the traditional SHA-512 hashing encoder. The main advantages
of ``bcrypt`` are the inclusion of a *salt* value to protect against rainbow
table attacks, and its adaptive nature, which allows to make it slower to
remain resistant to brute-force search attacks.

With this in mind, here is the authentication setup from our application,
which uses a login form to load users from the database:

.. code-block:: yaml

    # app/config/security.yml
    security:
        encoders:
            AppBundle\Entity\User: bcrypt

        providers:
            database_users:
                entity: { class: AppBundle:User, property: username }

        firewalls:
            secured_area:
                pattern: ^/
                anonymous: true
                form_login:
                    check_path: login
                    login_path: login

                logout:
                    path: security_logout
                    target: homepage

    # ... access_control exists, but is not shown here

.. tip::

    The source code for our project contains comments that explain each part.

Authorization (i.e. Denying Access)
-----------------------------------

Symfony gives you several ways to enforce authorization, including the ``access_control``
configuration in :doc:`security.yml </reference/configuration/security>` and
using :ref:`isGranted <best-practices-directly-isGranted>` on the ``security.context``
service directly.

.. best-practice::

    * For protecting broad URL patterns, use ``access_control``;
    * Check security directly on the ``security.context`` service whenever
      you have a more complex situation.

There are also different ways to centralize your authorization logic, like
with a custom security voter or with ACL.

.. best-practice::

    * For fine-grained restrictions, define a custom security voter;
    * For restricting access to *any* object by *any* user via an admin
      interface, use the Symfony ACL.

.. _best-practices-directly-isGranted:
.. _checking-permissions-without-security:

Manually Checking Permissions
-----------------------------

If you cannot control the access based on URL patterns, you can always do
the security checks in PHP:

.. code-block:: php

    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    // ...

    /**
     * @Route("/{id}/edit", name="admin_post_edit")
     */
    public function editAction($id)
    {
        $post = $this->getDoctrine()->getRepository('AppBundle:Post')
            ->find($id);

        if (!$post) {
            throw $this->createNotFoundException();
        }

        if (!$post->isAuthor($this->getUser())) {
            throw new AccessDeniedException();
        }

        // ...
    }

Security Voters
---------------

If your security logic is complex and can't be centralized into a method
like ``isAuthor()``, you should leverage custom voters. These are an order
of magnitude easier than :doc:`ACLs </cookbook/security/acl>` and will give
you the flexibility you need in almost all cases.

First, create a voter class. The following example shows a voter that implements
the same ``getAuthorEmail`` logic you used above:

.. code-block:: php

    namespace AppBundle\Security;

    use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
    use Symfony\Component\Security\Core\User\UserInterface;

    // AbstractVoter class requires Symfony 2.6 or higher version
    class PostVoter extends AbstractVoter
    {
        const CREATE = 'create';
        const EDIT   = 'edit';

        protected function getSupportedAttributes()
        {
            return array(self::CREATE, self::EDIT);
        }

        protected function getSupportedClasses()
        {
            return array('AppBundle\Entity\Post');
        }

        protected function isGranted($attribute, $post, $user = null)
        {
            if (!$user instanceof UserInterface) {
                return false;
            }

            if ($attribute === self::CREATE && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                return true;
            }

            if ($attribute === self::EDIT && $user->getEmail() === $post->getAuthorEmail()) {
                return true;
            }

            return false;
        }
    }

To enable the security voter in the application, define a new service:

.. code-block:: yaml

    # app/config/services.yml
    services:
        # ...
        post_voter:
            class:      AppBundle\Security\PostVoter
            public:     false
            tags:
               - { name: security.voter }

Now, you can use the voter with the ``security.context`` service:

.. code-block:: php

    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    // ...

    /**
     * @Route("/{id}/edit", name="admin_post_edit")
     */
    public function editAction($id)
    {
        $post = // query for the post ...

        if (!$this->get('security.context')->isGranted('edit', $post)) {
            throw new AccessDeniedException();
        }
    }

Learn More
----------

The `FOSUserBundle`_, developed by the Symfony community, adds support for a
database-backed user system in Symfony. It also handles common tasks like
user registration and forgotten password functionality.

Enable the :doc:`Remember Me feature </cookbook/security/remember_me>` to
allow your users to stay logged in for a long period of time.

When providing customer support, sometimes it's necessary to access the application
as some *other* user so that you can reproduce the problem. Symfony provides
the ability to :doc:`impersonate users </cookbook/security/impersonating_user>`.

If your company uses a user login method not supported by Symfony, you can
develop :doc:`your own user provider </cookbook/security/custom_provider>` and
:doc:`your own authentication provider </cookbook/security/custom_authentication_provider>`.

.. _`FOSUserBundle`: https://github.com/FriendsOfSymfony/FOSUserBundle
