Security
========

Authentication and Firewalls (i.e. Getting the User's Credentials)
------------------------------------------------------------------

You can configure Symfony to authenticate your users using any method you
want and to load user information from any source. This is a complex topic, but
the :doc:`Security guide </security>` has a lot of information about this.

Regardless of your needs, authentication is configured in ``security.yaml``,
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

    Use the ``bcrypt`` encoder for hashing your users' passwords.

If your users have a password, then we recommend hashing it using the ``bcrypt``
encoder, instead of the traditional SHA-512 hashing encoder. The main advantages
of ``bcrypt`` are the inclusion of a *salt* value to protect against rainbow
table attacks, and its adaptive nature, which allows to make it slower to
remain resistant to brute-force search attacks.

With this in mind, here is the authentication setup from our application,
which uses a login form to load users from the database:

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        encoders:
            App\Entity\User: bcrypt

        providers:
            database_users:
                entity: { class: App\Entity\User, property: username }

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
configuration in :doc:`security.yaml </reference/configuration/security>`, the
:ref:`@Security annotation <best-practices-security-annotation>` and using
:ref:`isGranted <best-practices-directly-isGranted>` on the ``security.authorization_checker``
service directly.

.. best-practice::

    * For protecting broad URL patterns, use ``access_control``;
    * Whenever possible, use the ``@Security`` annotation;
    * Check security directly on the ``security.authorization_checker`` service
      whenever you have a more complex situation.

There are also different ways to centralize your authorization logic, like
with a custom security voter:

.. best-practice::

    Define a custom security voter to implement fine-grained restrictions.

.. _best-practices-security-annotation:

The @Security Annotation
------------------------

For controlling access on a controller-by-controller basis, use the ``@Security``
annotation whenever possible. It's easy to read and is placed consistently
above each action.

In our application, you need the ``ROLE_ADMIN`` in order to create a new post.
Using ``@Security``, this looks like:

.. code-block:: php

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
    use Symfony\Component\Routing\Annotation\Route;
    // ...

    /**
     * Displays a form to create a new Post entity.
     *
     * @Route("/new", name="admin_post_new")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function new()
    {
        // ...
    }

Using Expressions for Complex Security Restrictions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your security logic is a little bit more complex, you can use an :doc:`expression </components/expression_language>`
inside ``@Security``. In the following example, a user can only access the
controller if their email matches the value returned by the ``getAuthorEmail()``
method on the ``Post`` object:

.. code-block:: php

    use App\Entity\Post;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
    use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route("/{id}/edit", name="admin_post_edit")
     * @Security("user.getEmail() == post.getAuthorEmail()")
     */
    public function edit(Post $post)
    {
        // ...
    }

Notice that this requires the use of the `ParamConverter`_, which automatically
queries for the ``Post`` object and puts it on the ``$post`` argument. This
is what makes it possible to use the ``post`` variable in the expression.

This has one major drawback: an expression in an annotation cannot easily
be reused in other parts of the application. Imagine that you want to add
a link in a template that will only be seen by authors. Right now you'll
need to repeat the expression code using Twig syntax:

.. code-block:: html+jinja

    {% if app.user and app.user.email == post.authorEmail %}
        <a href=""> ... </a>
    {% endif %}

The easiest solution - if your logic is simple enough - is to add a new method
to the ``Post`` entity that checks if a given user is its author:

.. code-block:: php

    // src/Entity/Post.php
    // ...

    class Post
    {
        // ...

        /**
         * Is the given User the author of this Post?
         *
         * @return bool
         */
        public function isAuthor(User $user = null)
        {
            return $user && $user->getEmail() == $this->getAuthorEmail();
        }
    }

Now you can reuse this method both in the template and in the security expression:

.. code-block:: php

    use App\Entity\Post;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
    use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route("/{id}/edit", name="admin_post_edit")
     * @Security("post.isAuthor(user)")
     */
    public function edit(Post $post)
    {
        // ...
    }

.. code-block:: html+jinja

    {% if post.isAuthor(app.user) %}
        <a href=""> ... </a>
    {% endif %}

.. _best-practices-directly-isGranted:
.. _checking-permissions-without-security:
.. _manually-checking-permissions:

Checking Permissions without @Security
--------------------------------------

The above example with ``@Security`` only works because we're using the
:ref:`ParamConverter <best-practices-paramconverter>`, which gives the expression
access to the ``post`` variable. If you don't use this, or have some other
more advanced use-case, you can always do the same security check in PHP:

.. code-block:: php

    /**
     * @Route("/{id}/edit", name="admin_post_edit")
     */
    public function edit($id)
    {
        $post = $this->getDoctrine()
            ->getRepository(Post::class)
            ->find($id);

        if (!$post) {
            throw $this->createNotFoundException();
        }

        if (!$post->isAuthor($this->getUser())) {
            $this->denyAccessUnlessGranted('edit', $post);
        }
        // equivalent code without using the "denyAccessUnlessGranted()" shortcut:
        //
        // use Symfony\Component\Security\Core\Exception\AccessDeniedException;
        // ...
        //
        // if (!$this->get('security.authorization_checker')->isGranted('edit', $post)) {
        //    throw $this->createAccessDeniedException();
        // }

        // ...
    }

Security Voters
---------------

If your security logic is complex and can't be centralized into a method like
``isAuthor()``, you should leverage custom voters. These are much easier than
:doc:`ACLs </security/acl>` and will give you the flexibility you need in almost
all cases.

First, create a voter class. The following example shows a voter that implements
the same ``getAuthorEmail()`` logic you used above:

.. code-block:: php

    namespace App\Security;

    use App\Entity\Post;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\Voter;
    use Symfony\Component\Security\Core\User\UserInterface;

    class PostVoter extends Voter
    {
        const CREATE = 'create';
        const EDIT   = 'edit';

        private $decisionManager;

        public function __construct(AccessDecisionManagerInterface $decisionManager)
        {
            $this->decisionManager = $decisionManager;
        }

        protected function supports($attribute, $subject)
        {
            if (!in_array($attribute, [self::CREATE, self::EDIT])) {
                return false;
            }

            if (!$subject instanceof Post) {
                return false;
            }

            return true;
        }

        protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
        {
            $user = $token->getUser();
            /** @var Post */
            $post = $subject; // $subject must be a Post instance, thanks to the supports method

            if (!$user instanceof UserInterface) {
                return false;
            }

            switch ($attribute) {
                // if the user is an admin, allow them to create new posts
                case self::CREATE:
                    if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
                        return true;
                    }

                    break;

                // if the user is the author of the post, allow them to edit the posts
                case self::EDIT:
                    if ($user->getEmail() === $post->getAuthorEmail()) {
                        return true;
                    }

                    break;
            }

            return false;
        }
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
your application will :ref:`autoconfigure <services-autoconfigure>` your security
voter and inject an ``AccessDecisionManagerInterface`` instance into it thanks to
:doc:`autowiring </service_container/autowiring>`.

Now, you can use the voter with the ``@Security`` annotation:

.. code-block:: php

    /**
     * @Route("/{id}/edit", name="admin_post_edit")
     * @Security("is_granted('edit', post)")
     */
    public function edit(Post $post)
    {
        // ...
    }

You can also use this directly with the ``security.authorization_checker`` service or
via the even easier shortcut in a controller:

.. code-block:: php

    /**
     * @Route("/{id}/edit", name="admin_post_edit")
     */
    public function edit($id)
    {
        $post = ...; // query for the post

        $this->denyAccessUnlessGranted('edit', $post);

        // or without the shortcut:
        //
        // use Symfony\Component\Security\Core\Exception\AccessDeniedException;
        // ...
        //
        // if (!$this->get('security.authorization_checker')->isGranted('edit', $post)) {
        //    throw $this->createAccessDeniedException();
        // }
    }

Learn More
----------

The `FOSUserBundle`_, developed by the Symfony community, adds support for a
database-backed user system in Symfony. It also handles common tasks like
user registration and forgotten password functionality.

Enable the :doc:`Remember Me feature </security/remember_me>` to
allow your users to stay logged in for a long period of time.

When providing customer support, sometimes it's necessary to access the application
as some *other* user so that you can reproduce the problem. Symfony provides
the ability to :doc:`impersonate users </security/impersonating_user>`.

If your company uses a user login method not supported by Symfony, you can
develop :doc:`your own user provider </security/custom_provider>` and
:doc:`your own authentication provider </security/custom_authentication_provider>`.

.. _`ParamConverter`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
.. _`@Security annotation`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/security.html
.. _`FOSUserBundle`: https://github.com/FriendsOfSymfony/FOSUserBundle
