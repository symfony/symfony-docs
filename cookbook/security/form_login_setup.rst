How to Build a Traditional Login Form
=====================================

.. tip::

    If you need a login form and are storing users in some sort of a database,
    then you should consider using `FOSUserBundle`_, which helps you build
    your ``User`` object and gives you many routes and controllers for common
    tasks like login, registration and forgot password.

In this entry, you'll build a traditional login form. Of course, when the
user logs in, you can load your users from anywhere - like the database.
See :ref:`security-user-providers` for details.

First, enable form login under your firewall:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    anonymous: ~
                    form_login:
                        login_path: /login
                        check_path: /login_check

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall name="main">
                    <anonymous />
                    <form-login login-path="/login" check-path="/login_check" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array(
                    'anonymous'  => null,
                    'form_login' => array(
                        'login_path' => '/login',
                        'check_path' => '/login_check',
                    ),
                ),
            ),
        ));

.. tip::

    The ``login_path`` and ``check_path`` can also be route names (but cannot
    have mandatory wildcards - e.g. ``/login/{foo}`` where ``foo`` has no
    default value).

Now, when the security system initiates the authentication process, it will
redirect the user to the login form ``/login``. Implementing this login form
visually is your job. First, create a new ``SecurityController`` inside a
bundle::

    // src/AppBundle/Controller/SecurityController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class SecurityController extends Controller
    {
    }

Next, create two routes: one for each of the paths you configured earlier
under your ``form_login`` configuration (``/login`` and ``/login_check``):

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/SecurityController.php

        // ...
        use Symfony\Component\HttpFoundation\Request;
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

        class SecurityController extends Controller
        {
            /**
             * @Route("/login", name="login_route")
             */
            public function loginAction(Request $request)
            {
            }

            /**
             * @Route("/login_check", name="login_check")
             */
            public function loginCheckAction()
            {
                // this controller will not be executed,
                // as the route is handled by the Security system
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        login_route:
            path:     /login
            defaults: { _controller: AppBundle:Security:login }

        login_check:
            path: /login_check
            # no controller is bound to this route
            # as it's handled by the Security system

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="login_route" path="/login">
                <default key="_controller">AppBundle:Security:login</default>
            </route>

            <route id="login_check" path="/login_check" />
            <!-- no controller is bound to this route
                 as it's handled by the Security system -->
        </routes>

    ..  code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('login_route', new Route('/login', array(
            '_controller' => 'AppBundle:Security:login',
        )));

        $collection->add('login_check', new Route('/login_check'));
        // no controller is bound to this route
        // as it's handled by the Security system

        return $collection;

Great! Next, add the logic to ``loginAction`` that will display the login
form::

    // src/AppBundle/Controller/SecurityController.php

    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );
    }

Don't let this controller confuse you. As you'll see in a moment, when the
user submits the form, the security system automatically handles the form
submission for you. If the user had submitted an invalid username or password,
this controller reads the form submission error from the security system so
that it can be displayed back to the user.

In other words, your job is to *display* the login form and any login errors
that may have occurred, but the security system itself takes care of checking
the submitted username and password and authenticating the user.

Finally, create the template:

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/security/login.html.twig #}
        {# ... you will probably extends your base template, like base.html.twig #}

        {% if error %}
            <div>{{ error.messageKey|trans(error.messageData, 'security') }}</div>
        {% endif %}

        <form action="{{ path('login_check') }}" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="_username" value="{{ last_username }}" />

            <label for="password">Password:</label>
            <input type="password" id="password" name="_password" />

            {#
                If you want to control the URL the user
                is redirected to on success (more details below)
                <input type="hidden" name="_target_path" value="/account" />
            #}

            <button type="submit">login</button>
        </form>

    .. code-block:: html+php

        <!-- src/AppBundle/Resources/views/Security/login.html.php -->
        <?php if ($error): ?>
            <div><?php echo $error->getMessage() ?></div>
        <?php endif ?>

        <!-- The path() method was introduced in Symfony 2.8. Prior to 2.8, you
             had to use generate(). -->
        <form action="<?php echo $view['router']->path('login_check') ?>" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="_username" value="<?php echo $last_username ?>" />

            <label for="password">Password:</label>
            <input type="password" id="password" name="_password" />

            <!--
                If you want to control the URL the user
                is redirected to on success (more details below)
                <input type="hidden" name="_target_path" value="/account" />
            -->

            <button type="submit">login</button>
        </form>


.. tip::

    The ``error`` variable passed into the template is an instance of
    :class:`Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException`.
    It may contain more information - or even sensitive information - about
    the authentication failure, so use it wisely!

The form can look like anything, but has a few requirements:

* The form must POST to ``/login_check``, since that's what you configured
  under the ``form_login`` key in ``security.yml``.

* The username must have the name ``_username`` and the password must have
  the name ``_password``.

.. tip::

    Actually, all of this can be configured under the ``form_login`` key. See
    :ref:`reference-security-firewall-form-login` for more details.

.. caution::

    This login form is currently not protected against CSRF attacks. Read
    :doc:`/cookbook/security/csrf_in_login_form` on how to protect your login
    form.

And that's it! When you submit the form, the security system will automatically
check the user's credentials and either authenticate the user or send the
user back to the login form where the error can be displayed.

To review the whole process:

#. The user tries to access a resource that is protected;
#. The firewall initiates the authentication process by redirecting the
   user to the login form (``/login``);
#. The ``/login`` page renders login form via the route and controller created
   in this example;
#. The user submits the login form to ``/login_check``;
#. The security system intercepts the request, checks the user's submitted
   credentials, authenticates the user if they are correct, and sends the
   user back to the login form if they are not.

Redirecting after Success
-------------------------

If the submitted credentials are correct, the user will be redirected to
the original page that was requested (e.g. ``/admin/foo``). If the user originally
went straight to the login page, they'll be redirected to the homepage. This
can all be customized, allowing you to, for example, redirect the user to
a specific URL.

For more details on this and how to customize the form login process in general,
see :doc:`/cookbook/security/form_login`.

.. _book-security-common-pitfalls:

Avoid Common Pitfalls
---------------------

When setting up your login form, watch out for a few common pitfalls.

1. Create the Correct Routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First, be sure that you've defined the ``/login`` and ``/login_check``
routes correctly and that they correspond to the ``login_path`` and
``check_path`` config values. A misconfiguration here can mean that you're
redirected to a 404 page instead of the login page, or that submitting
the login form does nothing (you just see the login form over and over
again).

2. Be Sure the Login Page Isn't Secure (Redirect Loop!)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Also, be sure that the login page is accessible by anonymous users. For example,
the following configuration - which requires the ``ROLE_ADMIN`` role for
all URLs (including the ``/login`` URL), will cause a redirect loop:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml

        # ...
        access_control:
            - { path: ^/, roles: ROLE_ADMIN }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <rule path="^/" role="ROLE_ADMIN" />
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ...
        'access_control' => array(
            array('path' => '^/', 'role' => 'ROLE_ADMIN'),
        ),

Adding an access control that matches ``/login/*`` and requires *no* authentication
fixes the problem:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml

        # ...
        access_control:
            - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/, roles: ROLE_ADMIN }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <rule path="^/login" role="IS_AUTHENTICATED_ANONYMOUSLY" />
                <rule path="^/" role="ROLE_ADMIN" />
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ...
        'access_control' => array(
            array('path' => '^/login', 'role' => 'IS_AUTHENTICATED_ANONYMOUSLY'),
            array('path' => '^/', 'role' => 'ROLE_ADMIN'),
        ),

Also, if your firewall does *not* allow for anonymous users (no ``anonymous``
key), you'll need to create a special firewall that allows anonymous users
for the login page:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml

        # ...
        firewalls:
            # order matters! This must be before the ^/ firewall
            login_firewall:
                pattern:   ^/login$
                anonymous: ~
            secured_area:
                pattern:    ^/
                form_login: ~

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="login_firewall" pattern="^/login$">
                    <anonymous />
                </firewall>

                <firewall name="secured_area" pattern="^/">
                    <form-login />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ...
        'firewalls' => array(
            'login_firewall' => array(
                'pattern'   => '^/login$',
                'anonymous' => null,
            ),
            'secured_area' => array(
                'pattern'    => '^/',
                'form_login' => null,
            ),
        ),

3. Be Sure /login_check Is Behind a Firewall
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Next, make sure that your ``check_path`` URL (e.g. ``/login_check``) is behind
the firewall you're using for your form login (in this example, the single
firewall matches *all* URLs, including ``/login_check``). If ``/login_check``
doesn't match any firewall, you'll receive a ``Unable to find the controller
for path "/login_check"`` exception.

4. Multiple Firewalls Don't Share the Same Security Context
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you're using multiple firewalls and you authenticate against one firewall,
you will *not* be authenticated against any other firewalls automatically.
Different firewalls are like different security systems. To do this you have
to explicitly specify the same :ref:`reference-security-firewall-context`
for different firewalls. But usually for most applications, having one
main firewall is enough.

5. Routing Error Pages Are not Covered by Firewalls
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As routing is done *before* security, 404 error pages are not covered by
any firewall. This means you can't check for security or even access the
user object on these pages. See :doc:`/cookbook/controller/error_pages`
for more details.

.. _`FOSUserBundle`: https://github.com/FriendsOfSymfony/FOSUserBundle
