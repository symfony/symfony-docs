.. index::
   single: Security; Customizing form login redirect

Using the form_login Authentication Provider
============================================

.. caution::

    To have complete control over your login form, we recommend building a
    :doc:`form login authentication with Guard </security/form_login_setup>`.

Symfony comes with a built-in ``form_login`` system that handles a login form
POST automatically. Before you start, make sure you've followed the
:doc:`Security Guide </security>` to create your User class.

form_login Setup
----------------

First, enable ``form_login`` under your firewall:

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
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall name="main">
                    <anonymous/>
                    <form-login login-path="login" check-path="login"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            'firewalls' => [
                'main' => [
                    'anonymous'  => null,
                    'form_login' => [
                        'login_path' => 'login',
                        'check_path' => 'login',
                    ],
                ],
            ],
        ]);

.. tip::

    The ``login_path`` and ``check_path`` can also be route names (but cannot
    have mandatory wildcards - e.g. ``/login/{foo}`` where ``foo`` has no
    default value).

Now, when the security system initiates the authentication process, it will
redirect the user to the login form ``/login``. Implementing this login form
is your job. First, create a new ``SecurityController``::

    // src/Controller/SecurityController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class SecurityController extends AbstractController
    {
    }

Next, configure the route that you earlier used under your ``form_login``
configuration (``login``):

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/SecurityController.php

        // ...
        use Symfony\Component\Routing\Annotation\Route;

        class SecurityController extends AbstractController
        {
            /**
             * @Route("/login", name="login", methods={"GET", "POST"})
             */
            public function login()
            {
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        login:
            path:       /login
            controller: App\Controller\SecurityController::login
            methods: GET|POST

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="login" path="/login" controller="App\Controller\SecurityController::login" methods="GET|POST"/>
        </routes>

    ..  code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\SecurityController;

        return function (RoutingConfigurator $routes) {
            $routes->add('login', '/login')
                ->controller([SecurityController::class, 'login'])
                ->methods(['GET', 'POST'])
            ;
        };

Great! Next, add the logic to ``login()`` that displays the login form::

    // src/Controller/SecurityController.php
    use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

    public function login(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
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

.. code-block:: html+twig

    {# templates/security/login.html.twig #}
    {# ... you will probably extend your base template, like base.html.twig #}

    {% if error %}
        <div>{{ error.messageKey|trans(error.messageData, 'security') }}</div>
    {% endif %}

    <form action="{{ path('login') }}" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="_username" value="{{ last_username }}"/>

        <label for="password">Password:</label>
        <input type="password" id="password" name="_password"/>

        {#
            If you want to control the URL the user
            is redirected to on success (more details below)
            <input type="hidden" name="_target_path" value="/account"/>
        #}

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
    :ref:`form_login-csrf` on how to protect your login form.

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

.. _form_login-csrf:

CSRF Protection in Login Forms
------------------------------

`Login CSRF attacks`_ can be prevented using the same technique of adding hidden
CSRF tokens into the login forms. The Security component already provides CSRF
protection, but you need to configure some options before using it.

First, configure the CSRF token provider used by the form login in your security
configuration. You can set this to use the default provider available in the
security component:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                secured_area:
                    # ...
                    form_login:
                        # ...
                        csrf_token_generator: security.csrf.token_manager

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="secured_area">
                    <!-- ... -->
                    <form-login csrf-token-generator="security.csrf.token_manager"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            // ...

            'firewalls' => [
                'secured_area' => [
                    // ...
                    'form_login' => [
                        // ...
                        'csrf_token_generator' => 'security.csrf.token_manager',
                    ],
                ],
            ],
        ]);

.. _csrf-login-template:

Then, use the ``csrf_token()`` function in the Twig template to generate a CSRF
token and store it as a hidden field of the form. By default, the HTML field
must be called ``_csrf_token`` and the string used to generate the value must
be ``authenticate``:

.. code-block:: html+twig

    {# templates/security/login.html.twig #}

    {# ... #}
    <form action="{{ path('login') }}" method="post">
        {# ... the login fields #}

        <input type="hidden" name="_csrf_token"
            value="{{ csrf_token('authenticate') }}"
        >

        <button type="submit">login</button>
    </form>

After this, you have protected your login form against CSRF attacks.

.. tip::

    You can change the name of the field by setting ``csrf_parameter`` and change
    the token ID by setting  ``csrf_token_id`` in your configuration:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/security.yaml
            security:
                # ...

                firewalls:
                    secured_area:
                        # ...
                        form_login:
                            # ...
                            csrf_parameter: _csrf_security_token
                            csrf_token_id: a_private_string

        .. code-block:: xml

            <!-- config/packages/security.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <srv:container xmlns="http://symfony.com/schema/dic/security"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:srv="http://symfony.com/schema/dic/services"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <config>
                    <!-- ... -->

                    <firewall name="secured_area">
                        <!-- ... -->
                        <form-login csrf-parameter="_csrf_security_token"
                            csrf-token-id="a_private_string"
                        />
                    </firewall>
                </config>
            </srv:container>

        .. code-block:: php

            // config/packages/security.php
            $container->loadFromExtension('security', [
                // ...

                'firewalls' => [
                    'secured_area' => [
                        // ...
                        'form_login' => [
                            // ...
                            'csrf_parameter' => '_csrf_security_token',
                            'csrf_token_id'  => 'a_private_string',
                        ],
                    ],
                ],
            ]);

Redirecting after Success
-------------------------

By default, the form will redirect to the URL the user requested (i.e. the URL
which triggered the login form being shown). For example, if the user requested
``http://www.example.com/admin/post/18/edit``, then after they have successfully
logged in, they will be sent back to ``http://www.example.com/admin/post/18/edit``.

This is done by storing the requested URL in the session. If no URL is present
in the session (perhaps the user went directly to the login page), then the user
is redirected to ``/`` (i.e. the homepage). You can change this behavior in
several ways.

Changing the default Page
~~~~~~~~~~~~~~~~~~~~~~~~~

Define the ``default_target_path`` option to change the page where the user
is redirected to if no previous page was stored in the session. The value can be
a relative/absolute URL or a Symfony route name:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    form_login:
                        # ...
                        default_target_path: after_login_route_name

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <form-login default-target-path="after_login_route_name"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            // ...

            'firewalls' => [
                'main' => [
                    // ...

                    'form_login' => [
                        // ...
                        'default_target_path' => 'after_login_route_name',
                    ],
                ],
            ],
        ]);

Always Redirect to the default Page
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Define the ``always_use_default_target_path`` boolean option to ignore the
previously requested URL and always redirect to the default page:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    form_login:
                        # ...
                        always_use_default_target_path: true

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <form-login always-use-default-target-path="true"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            // ...

            'firewalls' => [
                'main' => [
                    // ...

                    'form_login' => [
                        // ...
                        'always_use_default_target_path' => true,
                    ],
                ],
            ],
        ]);

.. _control-the-redirect-url-from-inside-the-form:

Control the Redirect Using Request Parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The URL to redirect after the login can be defined using the ``_target_path``
parameter of GET and POST requests. Its value must be a relative or absolute
URL, not a Symfony route name.

Defining the redirect URL via GET using a query string parameter:

.. code-block:: text

    http://example.com/some/path?_target_path=/dashboard

Defining the redirect URL via POST using a hidden form field:

.. code-block:: html+twig

    {# templates/security/login.html.twig #}
    <form action="{{ path('login') }}" method="post">
        {# ... #}

        <input type="hidden" name="_target_path" value="{{ path('account') }}"/>
        <input type="submit" name="login"/>
    </form>

Using the Referring URL
~~~~~~~~~~~~~~~~~~~~~~~

In case no previous URL was stored in the session and no ``_target_path``
parameter is included in the request, you may use the value of the
``HTTP_REFERER`` header instead, as this will often be the same. Define the
``use_referer`` boolean option to enable this behavior:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    form_login:
                        # ...
                        use_referer: true

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <form-login use-referer="true"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            // ...

            'firewalls' => [
                'main' => [
                    // ...
                    'form_login' => [
                        // ...
                        'use_referer' => true,
                    ],
                ],
            ],
        ]);

.. note::

    The referrer URL is only used when it is different from the URL generated by
    the ``login_path`` route to avoid a redirection loop.

.. _redirecting-on-login-failure:

Redirecting after Failure
-------------------------

After a failed login (e.g. an invalid username or password was submitted), the
user is redirected back to the login form itself. Use the ``failure_path``
option to define a new target via a relative/absolute URL or a Symfony route name:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    form_login:
                        # ...
                        failure_path: login_failure_route_name

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <form-login failure-path="login_failure_route_name"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            // ...

            'firewalls' => [
                'main' => [
                    // ...
                    'form_login' => [
                        // ...
                        'failure_path' => 'login_failure_route_name',
                    ],
                ],
            ],
        ]);

This option can also be set via the ``_failure_path`` request parameter:

.. code-block:: text

    http://example.com/some/path?_failure_path=/forgot-password

.. code-block:: html+twig

    {# templates/security/login.html.twig #}
    <form action="{{ path('login') }}" method="post">
        {# ... #}

        <input type="hidden" name="_failure_path" value="{{ path('forgot_password') }}"/>
        <input type="submit" name="login"/>
    </form>

Customizing the Target and Failure Request Parameters
-----------------------------------------------------

The name of the request attributes used to define the success and failure login
redirects can be customized using the  ``target_path_parameter`` and
``failure_path_parameter`` options of the firewall that defines the login form.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    form_login:
                        target_path_parameter: go_to
                        failure_path_parameter: back_to

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <form-login target-path-parameter="go_to"/>
                    <form-login failure-path-parameter="back_to"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            // ...

            'firewalls' => [
                'main' => [
                    // ...
                    'form_login' => [
                        'target_path_parameter' => 'go_to',
                        'failure_path_parameter' => 'back_to',
                    ],
                ],
            ],
        ]);

Using the above configuration, the query string parameters and hidden form fields
are now fully customized:

.. code-block:: text

    http://example.com/some/path?go_to=/dashboard&back_to=/forgot-password

.. code-block:: html+twig

    {# templates/security/login.html.twig #}
    <form action="{{ path('login') }}" method="post">
        {# ... #}

        <input type="hidden" name="go_to" value="{{ path('dashboard') }}"/>
        <input type="hidden" name="back_to" value="{{ path('forgot_password') }}"/>
        <input type="submit" name="login"/>
    </form>

.. _`Login CSRF attacks`: https://en.wikipedia.org/wiki/Cross-site_request_forgery#Forging_login_requests
