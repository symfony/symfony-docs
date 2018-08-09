.. index::
   single: Security; Customizing form login redirect

How to Customize Redirect After Form Login
==========================================

Using a :doc:`form login </security/form_login_setup>` for authentication is a
common, and flexible, method for handling authentication in Symfony. This
article explains how to customize the URL which the user is redirected to after
a successful or failed login. Check out the full
:doc:`form login configuration reference </reference/configuration/security>` to
learn of the possible customization options.

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

.. note::

    Sometimes, redirecting to the originally requested page can cause problems,
    like if a background Ajax request "appears" to be the last visited URL,
    causing the user to be redirected there. For information on controlling this
    behavior, see :doc:`/security`.

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
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <form-login default-target-path="after_login_route_name" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'main' => array(
                    // ...

                    'form_login' => array(
                        // ...
                        'default_target_path' => 'after_login_route_name',
                    ),
                ),
            ),
        ));

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
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <form-login always-use-default-target-path="true" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'main' => array(
                    // ...

                    'form_login' => array(
                        // ...
                        'always_use_default_target_path' => true,
                    ),
                ),
            ),
        ));

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

        <input type="hidden" name="_target_path" value="{{ path('account') }}" />
        <input type="submit" name="login" />
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
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <form-login use-referer="true" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'main' => array(
                    // ...
                    'form_login' => array(
                        // ...
                        'use_referer' => true,
                    ),
                ),
            ),
        ));

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
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <form-login failure-path="login_failure_route_name" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'main' => array(
                    // ...
                    'form_login' => array(
                        // ...
                        'failure_path' => 'login_failure_route_name',
                    ),
                ),
            ),
        ));

This option can also be set via the ``_failure_path`` request parameter:

.. code-block:: text

    http://example.com/some/path?_failure_path=/forgot-password

.. code-block:: html+twig

    {# templates/security/login.html.twig #}
    <form action="{{ path('login') }}" method="post">
        {# ... #}

        <input type="hidden" name="_failure_path" value="{{ path('forgot_password') }}" />
        <input type="submit" name="login" />
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
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <form-login target-path-parameter="go_to" />
                    <form-login failure-path-parameter="back_to" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'main' => array(
                    // ...
                    'form_login' => array(
                        'target_path_parameter' => 'go_to',
                        'failure_path_parameter' => 'back_to',
                    ),
                ),
            ),
        ));

Using the above configuration, the query string parameters and hidden form fields
are now fully customized:

.. code-block:: text

    http://example.com/some/path?go_to=/dashboard&back_to=/forgot-password

.. code-block:: html+twig

    {# templates/security/login.html.twig #}
    <form action="{{ path('login') }}" method="post">
        {# ... #}

        <input type="hidden" name="go_to" value="{{ path('dashboard') }}" />
        <input type="hidden" name="back_to" value="{{ path('forgot_password') }}" />
        <input type="submit" name="login" />
    </form>

Redirecting to the Last Accessed Page with ``TargetPathTrait``
--------------------------------------------------------------

The last request URI is stored in a session variable named
``_security.<your providerKey>.target_path`` (e.g. ``_security.main.target_path``
if the name of your firewall is ``main``). Most of the times you don't have to
deal with this low level session variable. However, if you ever need to get or
remove this variable, it's better to use the
:class:`Symfony\\Component\\Security\\Http\\Util\\TargetPathTrait` utility::

    // ...
    use Symfony\Component\Security\Http\Util\TargetPathTrait;

    $targetPath = $this->getTargetPath($request->getSession(), $providerKey);

    // equivalent to:
    // $targetPath = $request->getSession()->get('_security.'.$providerKey.'.target_path');

.. versionadded:: 4.2
    The ``TargetPathHelper`` class was introduced in Symfony 4.2.

You can also use the ``TargetPathHelper`` service in the same::

    // ... for example: from inside the controller
    use Symfony\Bundle\SecurityBundle\Security\TargetPathHelper;
    // ...

    public function register(Request $request, TargetPathHelper $targetPathHelper)
    {
        // the user clicked to register: save the previous URL
        if ($request->isMethod('GET') && !$targetPathHelper->getPath()) {
            // redirect to the Referer, or the homepage if none
            $target = $request->headers->get('Referer', $this->generateUrl('homepage');
            $targetPathHelper->savePath($target);
        }

        // later, after a successful registration POST submit
        return $this->redirect($targetPathHelper->getPath());
    }
