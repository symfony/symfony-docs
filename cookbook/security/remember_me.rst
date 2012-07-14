.. index::
   single: Security; "Remember me"

How to add "Remember Me" Login Functionality
============================================

Once a user is authenticated, their credentials are typically stored in the
session. This means that when the session ends they will be logged out and
have to provide their login details again next time they wish to access the
application. You can allow users to choose to stay logged in for longer than
the session lasts using a cookie with the ``remember_me`` firewall option.
The firewall needs to have a secret key configured, which is used to encrypt
the cookie's content. It also has several options with default values which
are shown here:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        firewalls:
            main:
                remember_me:
                    key:      "%secret%"
                    lifetime: 3600
                    path:     /
                    domain:   ~ # Defaults to the current domain from $_SERVER

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <remember-me
                    key      = "%secret%"
                    lifetime = "3600"
                    path     = "/"
                    domain   = "" <!-- Defaults to the current domain from $_SERVER -->
                />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array('remember_me' => array(
                    'key'      => '%secret%',
                    'lifetime' => 3600,
                    'path'     => '/',
                    'domain'   => '', // Defaults to the current domain from $_SERVER
                )),
            ),
        ));

It's a good idea to provide the user with the option to use or not use the
remember me functionality, as it will not always be appropriate. The usual
way of doing this is to add a checkbox to the login form. By giving the checkbox
the name ``_remember_me``, the cookie will automatically be set when the checkbox
is checked and the user successfully logs in. So, your specific login form
might ultimately look like this:

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

        <!-- src/Acme/SecurityBundle/Resources/views/Security/login.html.php -->
        <?php if ($error): ?>
            <div><?php echo $error->getMessage() ?></div>
        <?php endif; ?>

        <form action="<?php echo $view['router']->generate('login_check') ?>" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username"
                   name="_username" value="<?php echo $last_username ?>" />

            <label for="password">Password:</label>
            <input type="password" id="password" name="_password" />

            <input type="checkbox" id="remember_me" name="_remember_me" checked />
            <label for="remember_me">Keep me logged in</label>

            <input type="submit" name="login" />
        </form>

The user will then automatically be logged in on subsequent visits while
the cookie remains valid.

Forcing the User to Re-authenticate before accessing certain Resources
----------------------------------------------------------------------

When the user returns to your site, he/she is authenticated automatically based
on the information stored in the remember me cookie. This allows the user
to access protected resources as if the user had actually authenticated upon
visiting the site.

In some cases, however, you may want to force the user to actually re-authenticate
before accessing certain resources. For example, you might allow "remember me"
users to see basic account information, but then require them to actually
re-authenticate before modifying that information.

The security component provides an easy way to do this. In addition to roles
explicitly assigned to them, users are automatically given one of the following
roles depending on how they are authenticated:

* ``IS_AUTHENTICATED_ANONYMOUSLY`` - automatically assigned to a user who is
  in a firewall protected part of the site but who has not actually logged in.
  This is only possible if anonymous access has been allowed.

* ``IS_AUTHENTICATED_REMEMBERED`` - automatically assigned to a user who
  was authenticated via a remember me cookie.

* ``IS_AUTHENTICATED_FULLY`` - automatically assigned to a user that has
  provided their login details during the current session.

You can use these to control access beyond the explicitly assigned roles.

.. note::

    If you have the ``IS_AUTHENTICATED_REMEMBERED`` role, then you also
    have the ``IS_AUTHENTICATED_ANONYMOUSLY`` role. If you have the ``IS_AUTHENTICATED_FULLY``
    role, then you also have the other two roles. In other words, these roles
    represent three levels of increasing "strength" of authentication.

You can use these additional roles for finer grained control over access to
parts of a site. For example, you may want your user to be able to view their
account at ``/account`` when authenticated by cookie but to have to provide
their login details to be able to edit the account details. You can do this
by securing specific controller actions using these roles. The edit action
in the controller could be secured using the service context.

In the following example, the action is only allowed if the user has the
``IS_AUTHENTICATED_FULLY`` role.

.. code-block:: php

    // ...
    use Symfony\Component\Security\Core\Exception\AccessDeniedException

    public function editAction()
    {
        if (false === $this->get('security.context')->isGranted(
            'IS_AUTHENTICATED_FULLY'
           )) {
            throw new AccessDeniedException();
        }

        // ...
    }

You can also choose to install and use the optional JMSSecurityExtraBundle_,
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

.. tip::

    If you also had an access control in your security configuration that
    required the user to have a ``ROLE_USER`` role in order to access any
    of the account area, then you'd have the following situation:

    * If a non-authenticated (or anonymously authenticated user) tries to
      access the account area, the user will be asked to authenticate.

    * Once the user has entered his username and password, assuming the
      user receives the ``ROLE_USER`` role per your configuration, the user
      will have the ``IS_AUTHENTICATED_FULLY`` role and be able to access
      any page in the account section, including the ``editAction`` controller.

    * If the user's session ends, when the user returns to the site, he will
      be able to access every account page - except for the edit page - without
      being forced to re-authenticate. However, when he tries to access the
      ``editAction`` controller, he will be forced to re-authenticate, since
      he is not, yet, fully authenticated.

For more information on securing services or methods in this way,
see :doc:`/cookbook/security/securing_services`.

.. _JMSSecurityExtraBundle: https://github.com/schmittjoh/JMSSecurityExtraBundle
