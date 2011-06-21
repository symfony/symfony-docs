How to add "Remember Me" Login Functionality
============================================

Once a user is authenticated their credentials are typically stored in the
session. This means that when the session ends they will be logged out and
have to provide their login details again next time they wish to access the 
application. You can allow users to choose to stay logged in for longer than 
the session lasts using a cookie with the ``remember_me`` firewall option. 
The firewall needs to have a secret key configured which is used to encrypt 
the cookie content. It also has several options with default values which 
are shown here:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        firewalls:
            main:
                remember_me:
                    key:      aSecretKey
                    lifetime: 3600
                    path:     /
                    domain:   #The current domain from $_SERVER

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <remember-me
                    key="aSecretKey"
                    lifetime="3600"
                    path="/"
                    domain="" <!-- The current domain from $_SERVER --.                    
                />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array('remember_me' => array(
                    'key'                     => '/login_check',
                    'lifetime'                => 3600,
                    'path'                    => '/',
                    'domain'                  => '',//The current domain from $_SERVER
                )),
            ),
        ));

Its is best to provide users with the option of whether to stay logged in 
this way as it will not always be appropriate. The usual way of doing this
is to add a checkbox to the login form. By giving the checkbox the name 
``_remember_me``, the cookie will automatically  be set when the checkbox is 
checked and the user successfully logs in. 

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

            <input type="checkbox" id="remember_me" name="_remember_me" checked />
            <label for="remember_me">Keep me logged in</label>

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

            <input type="checkbox" id="remember_me" name="_remember_me" checked />
            <label for="remember_me">Keep me logged in</label>

            <input type="submit" name="login" />
        </form>

The user will then automatically be logged in on subsequent visits in whilst
the cookie is valid.

You may not want to provide a user authenticated in this way full access, for 
example, you may want them to be able to view pages but not make changes to 
them. The security component provides an easy way to do this. As well as roles 
explicitly assigned to them, users are assigned one of the following roles 
depending on how they are logged in, ``IS_AUTHENTICATED_ANONYMOUSLY``, 
``IS_AUTHENTICATED_FULLY`` or ``IS_AUTHENTICATED_REMEMBERED``. You can use 
these to control access beyond the explicitly assigned roles. 

``IS_AUTHENTICATED_ANONYMOUSLY`` is automatically assigned to a user who is 
in a firewall protected part of the site but who has not actually logged in. 
This is only possible if anonymous access has been allowed. If a user has 
provided their login details in the current session then they will be assigned
the ``IS_AUTHENTICATED_FULLY`` role. If they have authenticated via a 
remember me cookie then they will have the ``IS_AUTHENTICATED_REMEMBERED`` 
role. If a user was authenticated by a remember me cookie and then provides
their login details they will have both the ``IS_AUTHENTICATED_REMEMBERED``
and the `IS_AUTHENTICATED_FULLY`` roles.

You can use these additional roles for finer grained control over access to 
parts of a site. For example, you may want you user to be able to view their 
account at ``/account`` when authenticated by cookie but to have to provide 
their login details to be able to edit the account details. One way to achieve 
this would be to allow access to ``/account`` but not to ``/account/edit`` using
the ``access_control`` rules in the config:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        access_decision_manager:
            # Strategy can be: affirmative, unanimous or consensus
            strategy: unanimous
    
        access_control:
            - { path: ^/account/edit, roles: [IS_AUTHENTICATED_FULLY, ROLE_USER] }
            - { path: ^/account, roles: ROLE_USER }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <access-decision-manager
                strategy="unanimous"
            />

            <access-control>
                <rule path="^/account/edit" roles="IS_AUTHENTICATED_FULLY, ROLE_USER" />
                <rule path="^/account" roles="ROLE_USER" />
            </access-control>

        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
             'access_decision_manager' => array(
                'strategy' => 'unanimous',
            ),            
            'access_control' => array(
                array('path' => '^/account/edit', 'roles' => 'IS_AUTHENTICATED_FULLY, ROLE_USER'),
                array('path' => '^/account', 'roles' => 'ROLE_USER'),
            ),
        ));

.. note::

    For the user to need to have both the ``ROLE_USER`` and 
    ``IS_AUTHENTICATED_FULLY`` to gain access rather than just one we have had 
    to change the decision strategy to ``unanimous``. If it is set to 
    ``affirmative`` they would only need one of these roles and would be 
    allowed access if they were only authenticated using the cookie.

Alternatively you can secure specific controller actions using these roles. 
The edit action in the controller could be secured using the service context. 
Here access to the action is only allowed if the user has the 
``IS_AUTHENTICATED_FULLY`` role.

.. code-block:: php

    use Symfony\Component\Security\Core\Exception\AccessDeniedException
    // ...

    public function editAction()
    {
        if (false === $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        // ...
    }

You can also choose to install and use the optional ``SecurityExtraBundle``,
which can secure your controller using annotations:

.. code-block:: php

    use JMS\SecurityExtraBundle\Annotation\Secure;

    /**
     * @Secure(roles="IS_AUTHENTICATED_FULLY")
     */
    public function editAction($name)
    {
        // ...
    }

You can also secure any service or method in this way, 
see :doc:`/cookbook/security/securing_services`.