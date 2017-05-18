.. index::
   single: Security; Customizing form login

How to Customize your Form Login
================================

Using a :doc:`form login </security/form_login_setup>` for authentication
is a common, and flexible, method for handling authentication in Symfony.
Pretty much every aspect of the form login can be customized. The full, default
configuration is shown in the next section.

Form Login Configuration Reference
----------------------------------

To see the full form login configuration reference, see
:doc:`/reference/configuration/security`. Some of the more interesting options
are explained below.

Redirecting after Success
-------------------------

You can change where the login form redirects after a successful login using
the various config options. By default the form will redirect to the URL the
user requested (i.e. the URL which triggered the login form being shown).
For example, if the user requested ``http://www.example.com/admin/post/18/edit``,
then after they successfully log in, they will eventually be sent back to
``http://www.example.com/admin/post/18/edit``.
This is done by storing the requested URL in the session.
If no URL is present in the session (perhaps the user went
directly to the login page), then the user is redirected to the default page,
which is  ``/`` (i.e. the homepage) by default. You can change this behavior
in several ways.

Changing the default Page
~~~~~~~~~~~~~~~~~~~~~~~~~

First, the default page can be set (i.e. the page the user is redirected to
if no previous page was stored in the session). To set it to the
``default_security_target`` route use the following config:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    form_login:
                        # ...
                        default_target_path: default_security_target

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

                <firewall name="main">
                    <form-login default-target-path="default_security_target" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'main' => array(
                    // ...

                    'form_login' => array(
                        // ...
                        'default_target_path' => 'default_security_target',
                    ),
                ),
            ),
        ));

Now, when no URL is set in the session, users will be sent to the
``default_security_target`` route.

Always Redirect to the default Page
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can make it so that users are always redirected to the default page regardless
of what URL they had requested previously by setting the
``always_use_default_target_path`` option to true:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    form_login:
                        # ...
                        always_use_default_target_path: true

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

                <firewall name="main">
                    <!-- ... -->
                    <form-login always-use-default-target-path="true" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
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

Using the Referring URL
~~~~~~~~~~~~~~~~~~~~~~~

In case no previous URL was stored in the session, you may wish to try using
the ``HTTP_REFERER`` instead, as this will often be the same. You can do
this by setting ``use_referer`` to true (it defaults to false):

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    form_login:
                        # ...
                        use_referer: true

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

                <firewall name="main">
                    <!-- ... -->
                    <form-login use-referer="true" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
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

Redirecting on Login Failure
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

After a failed login (e.g. an invalid username or password was submitted), the
user is redirected back to the login form itself. Use the ``failure_path``
option to define the route or URL the user is redirected to:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    form_login:
                        # ...
                        failure_path: login_failure

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

                <firewall name="main">
                    <!-- ... -->
                    <form-login failure-path="login_failure" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'main' => array(
                    // ...
                    'form_login' => array(
                        // ...
                        'failure_path' => 'login_failure',
                    ),
                ),
            ),
        ));

Control the Redirect URL from inside the Form
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also override where the user is redirected to via the form itself by
including a hidden field with the name ``_target_path`` for successful logins
and ``_failure_path`` for login errors:

.. configuration-block::

    .. code-block:: html+twig

        {# src/AppBundle/Resources/views/Security/login.html.twig #}
        {% if error %}
            <div>{{ error.message }}</div>
        {% endif %}

        <form action="{{ path('login') }}" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="_username" value="{{ last_username }}" />

            <label for="password">Password:</label>
            <input type="password" id="password" name="_password" />

            <input type="hidden" name="_target_path" value="account" />
            <input type="hidden" name="_failure_path" value="login" />

            <input type="submit" name="login" />
        </form>

    .. code-block:: html+php

        <!-- src/AppBundle/Resources/views/Security/login.html.php -->
        <?php if ($error): ?>
            <div><?php echo $error->getMessage() ?></div>
        <?php endif ?>

        <!-- The path() method was introduced in Symfony 2.8. Prior to 2.8, you
             had to use generate(). -->
        <form action="<?php echo $view['router']->path('login') ?>" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="_username" value="<?php echo $last_username ?>" />

            <label for="password">Password:</label>
            <input type="password" id="password" name="_password" />

            <input type="hidden" name="_target_path" value="account" />
            <input type="hidden" name="_failure_path" value="login" />

            <input type="submit" name="login" />
        </form>

Now, the user will be redirected to the value of the hidden form field. The
value attribute can be a relative path, absolute URL, or a route name. 
The name of the hidden fields in the login form is also configurable using the
``target_path_parameter`` and ``failure_path_parameter`` options of the firewall.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    form_login:
                        target_path_parameter: login_success
                        failure_path_parameter: login_fail

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

                <firewall name="main">
                    <!-- ... -->
                    <form-login target-path-parameter="login_success" />
                    <form-login failure-path-parameter="login_fail" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'main' => array(
                    // ...
                    'form_login' => array(
                        'target_path_parameter' => 'login_success',
                        'failure_path_parameter' => 'login_fail',
                    ),
                ),
            ),
        ));
