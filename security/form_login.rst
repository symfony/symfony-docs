Customizing the Form Login Authenticator Responses
==================================================

The form login authenticator creates a login form where users authenticate
using an identifier (e.g. email address or username) and a password. In
:ref:`security-form-login` the usage of this authenticator is explained.

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

Changing the Default Page
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

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                // ...
                'firewalls' => [
                    'main' => [
                        'form_login' => [
                            'default_target_path' => 'after_login_route_name',
                        ],
                    ],
                ],
            ],
        ]);

Always Redirect to the Default Page
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

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                // ...
                'firewalls' => [
                    'main' => [
                        'form_login' => [
                            'always_use_default_target_path' => true,
                        ],
                    ],
                ],
            ],
        ]);

.. _control-the-redirect-url-from-inside-the-form:

Control the Redirect Using Request Parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The URL to redirect to after the login can be dynamically defined using the ``_target_path``
parameter of the GET or POST request. Its value must be a relative or absolute
URL, not a Symfony route name.

For GET, use a query string parameter:

.. code-block:: text

    http://example.com/some/path?_target_path=/dashboard

For POST, use a hidden form field:

.. code-block:: html+twig

    {# templates/login/index.html.twig #}
    <form action="{{ path('app_login') }}" method="post">
        {# ... #}

        <input type="hidden" name="_target_path" value="{{ path('account') }}">
        <input type="submit" name="login">
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

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                // ...
                'firewalls' => [
                    'main' => [
                        'form_login' => [
                            'use_referer' => true,
                        ],
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

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                // ...
                'firewalls' => [
                    'main' => [
                        'form_login' => [
                            'failure_path' => 'login_failure_route_name',
                        ],
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

        <input type="hidden" name="_failure_path" value="{{ path('forgot_password') }}">
        <input type="submit" name="login">
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

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                // ...
                'firewalls' => [
                    'main' => [
                        'form_login' => [
                            'target_path_parameter' => 'go_to',
                            'failure_path_parameter' => 'back_to',
                        ],
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

        <input type="hidden" name="go_to" value="{{ path('dashboard') }}">
        <input type="hidden" name="back_to" value="{{ path('forgot_password') }}">
        <input type="submit" name="login">
    </form>
