.. index::
   single: Security; "Remember me"

How to Add "Remember Me" Login Functionality
============================================

Once a user is authenticated, their credentials are typically stored in the
session. This means that when the session ends they will be logged out and
have to provide their login details again next time they wish to access the
application. You can allow users to choose to stay logged in for longer than
the session lasts using a cookie with the ``remember_me`` firewall option:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    remember_me:
                        secret:   '%secret%'
                        lifetime: 604800 # 1 week in seconds
                        path:     /
                        # by default, the feature is enabled by checking a
                        # checkbox in the login form (see below), uncomment the
                        # following line to always enable it.
                        #always_remember_me: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="utf-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->

                    <!-- 604800 is 1 week in seconds -->
                    <remember-me
                        secret="%secret%"
                        lifetime="604800"
                        path="/" />
                    <!-- by default, the feature is enabled by checking a checkbox
                         in the login form (see below), add always-remember-me="true"
                         to always enable it. -->
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
                    'remember_me' => array(
                        'secret'   => '%secret%',
                        'lifetime' => 604800, // 1 week in seconds
                        'path'     => '/',
                        // by default, the feature is enabled by checking a
                        // checkbox in the login form (see below), uncomment
                        // the following line to always enable it.
                        //'always_remember_me' => true,
                    ),
                ),
            ),
        ));

The ``remember_me`` firewall defines the following configuration options:

``secret`` (**required**)
    .. versionadded:: 2.8
        The ``secret`` option was introduced in Symfony 2.8. Prior to 2.8, it
        was named ``key``.

    The value used to encrypt the cookie's content. It's common to use the
    ``secret`` value defined in the ``app/config/parameters.yml`` file.

``name`` (default value: ``REMEMBERME``)
    The name of the cookie used to keep the user logged in. If you enable the
    ``remember_me`` feature in several firewalls of the same application, make sure
    to choose a different name for the cookie of each firewall. Otherwise, you'll
    face lots of security related problems.

``lifetime`` (default value: ``31536000``)
    The number of seconds during which the user will remain logged in. By default
    users are logged in for one year.

``path`` (default value: ``/``)
    The path where the cookie associated with this feature is used. By default
    the cookie will be applied to the entire website but you can restrict to a
    specific section (e.g. ``/forum``, ``/admin``).

``domain`` (default value: ``null``)
    The domain where the cookie associated with this feature is used. By default
    cookies use the current domain obtained from ``$_SERVER``.

``secure`` (default value: ``false``)
    If ``true``, the cookie associated with this feature is sent to the user
    through an HTTPS secure connection.

``httponly`` (default value: ``true``)
    If ``true``, the cookie associated with this feature is accessible only
    through the HTTP protocol. This means that the cookie won't be accessible
    by scripting languages, such as JavaScript.

``remember_me_parameter`` (default value: ``_remember_me``)
    The name of the form field checked to decide if the "Remember Me" feature
    should be enabled or not. Keep reading this article to know how to enable
    this feature conditionally.

``always_remember_me`` (default value: ``false``)
    If ``true``, the value of the ``remember_me_parameter`` is ignored and the
    "Remember Me" feature is always enabled, regardless of the desire of the
    end user.

``token_provider`` (default value: ``null``)
    Defines the service id of a token provider to use. By default, tokens are
    stored in a cookie. For example, you might want to store the token in a
    database, to not have a (hashed) version of the password in a cookie. The
    DoctrineBridge comes with a
    ``Symfony\Bridge\Doctrine\Security\RememberMe\DoctrineTokenProvider`` that
    you can use.

Forcing the User to Opt-Out of the Remember Me Feature
------------------------------------------------------

It's a good idea to provide the user with the option to use or not use the
remember me functionality, as it will not always be appropriate. The usual
way of doing this is to add a checkbox to the login form. By giving the checkbox
the name ``_remember_me`` (or the name you configured using ``remember_me_parameter``),
the cookie will automatically be set when the checkbox is checked and the user
successfully logs in. So, your specific login form might ultimately look like
this:

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/security/login.html.twig #}
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

        <!-- app/Resources/views/security/login.html.php -->
        <?php if ($error): ?>
            <div><?php echo $error->getMessage() ?></div>
        <?php endif ?>

        <!-- The path() method was introduced in Symfony 2.8. Prior to 2.8, you
             had to use generate(). -->
        <form action="<?php echo $view['router']->path('login_check') ?>" method="post">
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

Forcing the User to Re-Authenticate before Accessing certain Resources
----------------------------------------------------------------------

When the user returns to your site, they are authenticated automatically based
on the information stored in the remember me cookie. This allows the user
to access protected resources as if the user had actually authenticated upon
visiting the site.

In some cases, however, you may want to force the user to actually re-authenticate
before accessing certain resources. For example, you might allow "remember me"
users to see basic account information, but then require them to actually
re-authenticate before modifying that information.

The Security component provides an easy way to do this. In addition to roles
explicitly assigned to them, users are automatically given one of the following
roles depending on how they are authenticated:

``IS_AUTHENTICATED_ANONYMOUSLY``
    Automatically assigned to a user who is in a firewall protected part of the
    site but who has not actually logged in. This is only possible if anonymous
    access has been allowed.

``IS_AUTHENTICATED_REMEMBERED``
    Automatically assigned to a user who was authenticated via a remember me
    cookie.

``IS_AUTHENTICATED_FULLY``
    Automatically assigned to a user that has provided their login details
    during the current session.

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

    // ...
    public function editAction()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // ...
    }

If your application is based on the Symfony Standard Edition, you can also secure
your controller using annotations:

.. code-block:: php

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

    /**
     * @Security("has_role('IS_AUTHENTICATED_FULLY')")
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

    * Once the user has entered their username and password, assuming the
      user receives the ``ROLE_USER`` role per your configuration, the user
      will have the ``IS_AUTHENTICATED_FULLY`` role and be able to access
      any page in the account section, including the ``editAction`` controller.

    * If the user's session ends, when the user returns to the site, they will
      be able to access every account page - except for the edit page - without
      being forced to re-authenticate. However, when they try to access the
      ``editAction`` controller, they will be forced to re-authenticate, since
      they are not, yet, fully authenticated.

For more information on securing services or methods in this way,
see :doc:`/cookbook/security/securing_services`.
