How to customize your Form Login
================================

Using a :ref:`form login<book-security-form-login>` for authentication is
a common, and flexible, method for handling authentication in Symfony2. Pretty
much every aspect of the form login can be customized. The full, default
configuration is shown in the next section.

Form Login Configuration Reference
----------------------------------

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    form_login:
                        # the user is redirected here when he/she needs to login
                        login_path:                     /login

                        # if true, forward the user to the login form instead of redirecting
                        use_forward:                    false

                        # submit the login form here
                        check_path:                     /login_check

                        # by default, the login form *must* be a POST, not a GET
                        post_only:                      true

                        # login success redirecting options (read further below)
                        always_use_default_target_path: false
                        default_target_path:            /
                        target_path_parameter:          _target_path
                        use_referer:                    false

                        # login failure redirecting options (read further below)
                        failure_path:                   null
                        failure_forward:                false

                        # field names for the username and password fields
                        username_parameter:             _username
                        password_parameter:             _password

                        # csrf token options
                        csrf_parameter:                 _csrf_token
                        intention:                      authenticate

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <form-login
                    check_path="/login_check"
                    login_path="/login"
                    use_forward="false"
                    always_use_default_target_path="false"
                    default_target_path="/"
                    target_path_parameter="_target_path"
                    use_referer="false"
                    failure_path="null"
                    failure_forward="false"
                    username_parameter="_username"
                    password_parameter="_password"
                    csrf_parameter="_csrf_token"
                    intention="authenticate"
                    post_only="true"
                />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array('form_login' => array(
                    'check_path'                     => '/login_check',
                    'login_path'                     => '/login',
                    'user_forward'                   => false,
                    'always_use_default_target_path' => false,
                    'default_target_path'            => '/',
                    'target_path_parameter'          => _target_path,
                    'use_referer'                    => false,
                    'failure_path'                   => null,
                    'failure_forward'                => false,
                    'username_parameter'             => '_username',
                    'password_parameter'             => '_password',
                    'csrf_parameter'                 => '_csrf_token',
                    'intention'                      => 'authenticate',
                    'post_only'                      => true,
                )),
            ),
        ));

Redirecting after Success
-------------------------

You can change where the login form redirects after a successful login using
the various config options. By default the form will redirect to the URL the
user requested (i.e. the URL which triggered the login form being shown).
For example, if the user requested ``http://www.example.com/admin/post/18/edit``
then after he/she will eventually be sent back to ``http://www.example.com/admin/post/18/edit`` 
after successfully logging in. This is done by storing the requested URL
in the session. If no URL is present in the session (perhaps the user went
directly to the login page), then the user is redirected to the default page,
which is  ``/`` (i.e. the homepage) by default. You can change this behavior
in several ways.

Changing the Default Page
~~~~~~~~~~~~~~~~~~~~~~~~~

First, the default page can be set (i.e. the page the user is redirected to
if no previous page was stored in the session). To set it to ``/admin`` use
the following config:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    form_login:
                        # ...
                        default_target_path: /admin

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <form-login
                    default_target_path="/admin"                    
                />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array('form_login' => array(
                    // ...
                    'default_target_path' => '/admin',
                )),
            ),
        ));

Now, when no URL is set in the session users will be sent to ``/admin``.

Always Redirect to the Default Page
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can make it so that users are always redirected to the default page regardless
of what URL they had requested previously by setting the 
``always_use_default_target_path`` option to true:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    form_login:
                        # ...
                        always_use_default_target_path: true
                        
    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <form-login
                    always_use_default_target_path="true"
                />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array('form_login' => array(
                    // ...
                    'always_use_default_target_path' => true,
                )),
            ),
        ));

Using the Referring URL
~~~~~~~~~~~~~~~~~~~~~~~

In case no previous URL was stored in the session, you may wish to try using
the ``HTTP_REFERER`` instead, as this will often be the same. You can  do
this by setting ``use_referer`` to true (it defaults to false): 

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    form_login:
                        # ...
                        use_referer:        true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <form-login
                    use_referer="true"
                />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array('form_login' => array(
                    // ...
                    'use_referer' => true,
                )),
            ),
        ));

Control the Redirect URL from inside the Form
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also override where the user is redirected to via the form itself by 
including a hidden field with the name ``_target_path``. For example, to
redirect to the URL defined by some ``acount`` route, use the following:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/SecurityBundle/Resources/views/Security/login.html.twig #}
        {% if error %}
            <div>{{ error.message }}</div>
        {% endif %}

        <form action="{{ path('login_check') }}" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="_username" value="{{ last_username }}" />

            <label for="password">Password:</label>
            <input type="password" id="password" name="_password" />

            <input type="hidden" name="_target_path" value="account" />

            <input type="submit" name="login" />
        </form>

    .. code-block:: html+php

        <?php // src/Acme/SecurityBundle/Resources/views/Security/login.html.php ?>
        <?php if ($error): ?>
            <div><?php echo $error->getMessage() ?></div>
        <?php endif; ?>

        <form action="<?php echo $view['router']->generate('login_check') ?>" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="_username" value="<?php echo $last_username ?>" />

            <label for="password">Password:</label>
            <input type="password" id="password" name="_password" />

            <input type="hidden" name="_target_path" value="account" />
            
            <input type="submit" name="login" />
        </form>

Now, the user will be redirected to the value of the hidden form field. The
value attribute can be a relative path, absolute URL, or a route name. You 
can even change the name of the hidden form field by changing the ``target_path_parameter`` 
option to another value.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    form_login:
                        target_path_parameter: redirect_url

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <form-login
                    target_path_parameter="redirect_url"
                />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array('form_login' => array(
                    'target_path_parameter' => redirect_url,
                )),
            ),
        ));

Redirecting on Login Failure
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to redirect the user after a successful login, you can also set
the URL that the user should be redirected to after a failed login (e.g. an
invalid username or password was submitted). By default, the user is redirected
back to the login form itself. You can set this to a different URL with the
following config:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    form_login:
                        # ...
                        failure_path: /login_failure
                        
    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <form-login
                    failure_path="login_failure"
                />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array('form_login' => array(
                    // ...
                    'failure_path' => login_failure,
                )),
            ),
        ));
