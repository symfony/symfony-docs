How to customize your Form Login
================================

Form Login Configuration Reference
----------------------------------

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    form_login:
                        check_path:                     /login_check
                        login_path:                     /login
                        failure_path:                   null
                        always_use_default_target_path: false
                        default_target_path:            /
                        target_path_parameter:          _target_path
                        use_referer:                    false

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <form-login
                    check_path="/login_check"
                    login_path="/login"
                    failure_path="null"
                    always_use_default_target_path="false"
                    default_target_path="/"
                    target_path_parameter="_target_path"
                    use_referer="false"
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
                    'failure_path'                   => null,
                    'always_use_default_target_path' => false,
                    'default_target_path'            => '/',
                    'target_path_parameter'          => _target_path,
                    'use_referer'                    => false,
                )),
            ),
        ));


You can change where the login form redirects to using the various config 
options. By default the form will redirect to the url the user requested 
which triggered the login form being shown. For example if they requested 
``http://www.example.com/admin/post/18/edit`` then after being redirected 
to the login form they will be sent back to 
``http://www.example.com/admin/post/18/edit`` if they login successfully. 
This is done by storing the requested URL in the session, if no URL is present
 in the session, then the user is redirected to the default page, which is 
``/`` by default. You can change this behaviour in several ways.

The first is that the default page can be set, to set it to ``/admin`` 
use the following config:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    form_login:
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
                    'default_target_path' => '/admin',
                )),
            ),
        ));


Now when no URL is set in the session users will be sent to ``/admin``.

You can set it so that users are always redirected to the default page 
regardless of what URL they had requested with the 
``always_use_default_target_path`` option:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    form_login:
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
                    'always_use_default_target_path' => true,
                )),
            ),
        ));


If in the case that there is not a target URL in the session you may wish to
try using the HTTP_REFERER instead as this will often be the same. You can 
do this by setting use_referer to true (it defaults to false): 

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    form_login:
                        use_referer:                    true

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
                    'use_referer' => true,
                )),
            ),
        ));


You can also override where the user is redirected to from the form itself by 
including a hidden field with the name _target_path:

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

            <input type="hidden" name="_target_path" value="/account" />

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

            <input type="hidden" name="_target_path" value="/account" />
            
            <input type="submit" name="login" />
        </form>


The user will then be redirected to the value of the hidden form field. You can
change the name of the hidden form field with the following config:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    form_login:
                        target_path_parameter: _a_different_name

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <form-login
                    target_path_parameter="_a_different_name"
                />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array('form_login' => array(
                    'target_path_parameter' => _a_different_name,
                )),
            ),
        ));


As well as the URL the user is redirected to on success you can set the URL 
they are redirected to on failure. This is by default back to the login
form itself but you can set this to a different URL with the following config:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    form_login:
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
                    'failure_path' => login_failure,
                )),
            ),
        ));

