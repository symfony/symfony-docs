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

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    anonymous: ~
                    form_login:
                        login_path: login
                        check_path: login

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall name="main">
                    <anonymous />
                    <form-login login-path="login" check-path="login" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array(
                    'anonymous'  => null,
                    'form_login' => array(
                        'login_path' => 'login',
                        'check_path' => 'login',
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
is your job. First, create a new ``SecurityController`` inside a bundle::

    // src/Controller/SecurityController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class SecurityController extends Controller
    {
    }

Next, configure the route that you earlier used under your ``form_login``
configuration (``login``):

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/SecurityController.php

        // ...
        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\Routing\Annotation\Route;

        class SecurityController extends Controller
        {
            /**
             * @Route("/login", name="login")
             */
            public function login(Request $request)
            {
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        login:
            path:       /login
            controller: App\Controller\SecurityController::login

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="login" path="/login">
                <default key="_controller">App\Controller\SecurityController::login</default>
            </route>
        </routes>

    ..  code-block:: php

        // config/routes.php
        use App\Controller\SecurityController;
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $routes = new RouteCollection();
        $routes->add('login', new Route('/login', array(
            '_controller' => array(SecurityController::class, 'login'),
        )));

        return $routes;

Great! Next, add the logic to ``login()`` that displays the login form::

    // src/Controller/SecurityController.php
    use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

.. note::

    If you get an error that the ``$authenticationUtils`` argument is missing,
    it's probably because the controllers of your application are not defined as
    services and tagged with the ``controller.service_arguments`` tag, as done
    in the :ref:`default services.yaml configuration <service-container-services-load-example>`.

Don't let this controller confuse you. As you'll see in a moment, when the
user submits the form, the security system automatically handles the form
submission for you. If the user submits an invalid username or password,
this controller reads the form submission error from the security system,
so that it can be displayed back to the user.

In other words, your job is to *display* the login form and any login errors
that may have occurred, but the security system itself takes care of checking
the submitted username and password and authenticating the user.

Finally, create the template:

.. configuration-block::

    .. code-block:: html+twig

        {# templates/security/login.html.twig #}
        {# ... you will probably extend your base template, like base.html.twig #}

        {% if error %}
            <div>{{ error.messageKey|trans(error.messageData, 'security') }}</div>
        {% endif %}

        <form action="{{ path('login') }}" method="post">
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

        <!-- src/Resources/views/Security/login.html.php -->
        <?php if ($error): ?>
            <div><?php echo $error->getMessage() ?></div>
        <?php endif ?>

        <form action="<?php echo $view['router']->path('login') ?>" method="post">
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

The form can look like anything, but it usually follows some conventions:

* The ``<form>`` element sends a ``POST`` request to the ``login`` route, since
  that's what you configured under the ``form_login`` key in ``security.yaml``;
* The username field has the name ``_username`` and the password field has the
  name ``_password``.

.. tip::

    Actually, all of this can be configured under the ``form_login`` key. See
    :ref:`reference-security-firewall-form-login` for more details.

.. caution::

    This login form is currently not protected against CSRF attacks. Read
    :doc:`/security/csrf` on how to protect your login form.

And that's it! When you submit the form, the security system will automatically
check the user's credentials and either authenticate the user or send the
user back to the login form where the error can be displayed.

To review the whole process:

#. The user tries to access a resource that is protected;
#. The firewall initiates the authentication process by redirecting the
   user to the login form (``/login``);
#. The ``/login`` page renders login form via the route and controller created
   in this example;
#. The user submits the login form to ``/login``;
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
see :doc:`/security/form_login`.

.. _security-common-pitfalls:

Avoid Common Pitfalls
---------------------

When setting up your login form, watch out for a few common pitfalls.

1. Create the Correct Routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First, be sure that you've defined the ``/login`` route correctly and that
it corresponds to the ``login_path`` and ``check_path`` config values.
A misconfiguration here can mean that you're redirected to a 404 page instead
of the login page, or that submitting the login form does nothing (you just see
the login form over and over again).

2. Be Sure the Login Page Isn't Secure (Redirect Loop!)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Also, be sure that the login page is accessible by anonymous users. For example,
the following configuration - which requires the ``ROLE_ADMIN`` role for
all URLs (including the ``/login`` URL), will cause a redirect loop:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml

        # ...
        access_control:
            - { path: ^/, roles: ROLE_ADMIN }

    .. code-block:: xml

        <!-- config/packages/security.xml -->
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

        // config/packages/security.php

        // ...
        'access_control' => array(
            array('path' => '^/', 'role' => 'ROLE_ADMIN'),
        ),

Adding an access control that matches ``/login/*`` and requires *no* authentication
fixes the problem:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml

        # ...
        access_control:
            - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/, roles: ROLE_ADMIN }

    .. code-block:: xml

        <!-- config/packages/security.xml -->
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

        // config/packages/security.php

        // ...
        'access_control' => array(
            array('path' => '^/login', 'role' => 'IS_AUTHENTICATED_ANONYMOUSLY'),
            array('path' => '^/', 'role' => 'ROLE_ADMIN'),
        ),

3. Be Sure check_path Is Behind a Firewall
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Next, make sure that your ``check_path`` URL (e.g. ``/login``) is behind
the firewall you're using for your form login (in this example, the single
firewall matches *all* URLs, including ``/login``). If ``/login``
doesn't match any firewall, you'll receive a ``Unable to find the controller
for path "/login"`` exception.

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
user object on these pages. See :doc:`/controller/error_pages`
for more details.

.. _`FOSUserBundle`: https://github.com/FriendsOfSymfony/FOSUserBundle
