Authentication
==============

Authentication in Symfony2 is managed by the Firewall system. It is made of
listeners that enforce security and redirect the user if his credentials are
not available, not sufficient, or just wrong.

.. note::

    The Firewall is implemented via a ``core.security`` event, notified just
    after the ``core.request`` one. All features described in this document
    are implemented as listeners to this event.

The Firewall Map
----------------

The Firewall can be configured to secure your application as a whole, or to
use different authentication strategies for different parts of the application.

Typically, a website can open the public part to all, secure the backend via a
form based authentication, and secure the public API/Web Service via an HTTP
basic authentication:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                backend:
                    pattern:    /admin/.*
                    form-login: true
                    logout:     true
                api:
                    pattern:    /api/.*
                    http_basic: true
                    stateless:  true
                public:
                    pattern:    /.*
                    security:   false

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall pattern="/admin/.*">
                <form-login />
                <logout />
            </firewall>
            <firewall pattern="/api/.*" stateless="true">
                <http-basic />
            </firewall>
            <firewall pattern="/.*" security="false" />
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'backend' => array('pattern' => '/admin/.*', 'http_basic' => true, 'logout' => true),
                'api'     => array('pattern' => '/api/.*', 'http_basic' => true, 'stateless' => true),
                'public'  => array('pattern' => '/.*', 'security' => false),
            ),
        ));

Each firewall configuration is activated when the incoming request matches the
regular expression defined by the ``pattern`` setting. This pattern must match
the request path info (``preg_match('#^'.PATTERN_VALUE.'$#',
$request->getPathInfo())``.)

.. tip::

    The definition order of firewall configurations is significant as Symfony2
    will use the first configuration for which the pattern matches the request
    (so you need to define more specific configurations first).

Authentication Mechanisms
-------------------------

Out of the box, Symfony2 supports the following authentication mechanisms:

* HTTP Basic;
* HTTP Digest;
* Form based authentication;
* X.509 certificates;
* Anonymous authentication.

Each authentication mechanism consists of two classes that makes it work: a
listener and an entry point. The *listener* tries to authenticate incoming
requests. When the user is not authenticated or when the listener detects
wrong credentials, the *entry point* creates a response to send feedback to
the user and to provide a way for him to enter his credentials.

You can configure a firewall to use more than one authentication mechanisms:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                backend:
                    pattern:    /admin/.*
                    x509:       true
                    http_basic: true
                    form_login: true
                    logout:     true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall pattern="/admin/.*">
                <x509 />
                <http-basic />
                <form-login />
                <logout />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'backend' => array(
                    'pattern'    => '/admin/.*',
                    'x509'       => true,
                    'http_basic' => true,
                    'form_login' => true,
                    'logout'     => true,
                ),
            ),
        ));

A user accessing a resource under ``/admin/`` will be able to provide a valid
X.509 certificate, an Authorization HTTP header, or use a form to login.

.. note::

    When the user is not authenticated and if there is more than one
    authentication mechanisms, Symfony2 automatically defines a default entry
    point (in the example above, the login form; but if the user send an
    Authorization HTTP header with wrong credentials, Symfony2 will use the
    HTTP basic entry point.)

HTTP Basic
~~~~~~~~~~

Configuring HTTP basic authentication is as simple as it can get:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_basic: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <http-basic />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('http_basic' => true),
            ),
        ));

HTTP Digest
~~~~~~~~~~~

Configuring HTTP digest authentication is as simple as it can get:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_digest: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <http-digest />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('http_digest' => true),
            ),
        ));

.. caution::

    To use HTTP Digest, you must store the user passwords in clear.

Form based authentication
~~~~~~~~~~~~~~~~~~~~~~~~~

Form based authentication is the most used authentication mechanism on the Web
nowadays:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    form_login: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <form-login />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('form_login' => true),
            ),
        ));

When the user is not authenticated, he is redirected to the ``login_path`` URL
(``/login`` by default).

This listener relies on a form to interact with the user. It handles the form
submission automatically but not its display; so you must implement that part
yourself::

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\Security\SecurityContext;

    class SecurityController extends Controller
    {
        public function loginAction()
        {
            // get the error if any (works with forward and redirect -- see below)
            if ($this['request']->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
                $error = $this['request']->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
            } else {
                $error = $this['request']->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
            }

            return $this->render('SecurityBundle:Security:login.php', array(
                // last username entered by the user
                'last_username' => $this['request']->getSession()->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
            ));
        }
    }

And the corresponding template:

.. configuration-block::

    .. code-block:: html+php

        <?php if ($error): ?>
            <div><?php echo $error ?></div>
        <?php endif; ?>

        <form action="<?php echo $view['router']->generate('_security_check') ?>" method="post">
            <label for="username">Username:</label>

            <input type="text" id="username" name="_username" value="<?php echo $last_username ?>" />
            <label for="password">Password:</label>
            <input type="password" id="password" name="_password" />

            <input type="submit" name="login" />
        </form>

    .. code-block:: jinja

        {% if error %}
            <div>{{ error }}</div>
        {% endif %}

        <form action="{% route "_security_check" %}" method="post">
            <label for="username">Username:</label>

            <input type="text" id="username" name="_username" value="{{ last_username }}" />
            <label for="password">Password:</label>
            <input type="password" id="password" name="_password" />

            <input type="submit" name="login" />
        </form>

The template must have a ``_username`` and ``_password`` fields, and the form
submission URL must be the value of the ``check_path`` setting
(``/login_check`` by default).

Finally, add routes for the ``/login`` (``login_path`` value) and
``/_login_check`` (``login_check`` value) URLs:

.. code-block:: xml

    <route id="_security_login" pattern="/login">
        <default key="_controller">SecurityBundle:Security:login</default>
    </route>

    <route id="_security_check" pattern="/_login_check" />

After an authentication failure, the user is redirected to the login page. You
can use forward instead by setting the ``failure_forward`` to ``true``. You
can also redirect or forward to another page if you set the ``failure_path``
setting.

After a successful authentication, the user is redirected based on the
following algorithm:

* if ``always_use_default_target_path`` is ``true`` (``false`` is the
  default), redirect to the ``default_target_path`` (``/`` by default);

* if the request contains a parameter named ``_target_path`` (configurable via
  ``target_path_parameter``), redirect the user to this parameter value;

* if there is a target URL stored in the session (which is done automatically
  when a user is redirected to the login page), redirect the user to that URL;

* if ``use_referer`` is set to ``true`` (``false`` is the default), redirect
  the use to the Referrer URL;

* Redirect the user to the ``default_target_path`` URL (``/`` by default).

.. note::

    All URLs must be path info values or absolute URLs.

The default values for all settings are the most sensible ones, but here is a
configuration example that shows how to override them all:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
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
        $container->loadFromExtension('security', 'config', array(
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

X.509 Certificates
~~~~~~~~~~~~~~~~~~

X.509 certificates are a great way to authenticate users if you know them all:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    x509: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <x509 />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('x509' => true),
            ),
        ));

As Symfony2 does not validate the certificate itself, and because obviously it
cannot enforce the password, you must first configure your web server
correctly before enabling this authenticating mechanism. Here is a simple but
working configuration for Apache:

.. code-block:: xml

    <VirtualHost *:443>
        ServerName intranet.example.com:443

        DocumentRoot "/some/path"
        DirectoryIndex index.php
        <Directory "/some/path">
            Allow from all
            Order allow,deny
            SSLOptions +StdEnvVars
        </Directory>

        SSLEngine on
        SSLCertificateFile "/path/to/server.crt"
        SSLCertificateKeyFile "/path/to/server.key"
        SSLCertificateChainFile "/path/to/ca.crt"
        SSLCACertificateFile "/path/to/ca.crt"
        SSLVerifyClient require
        SSLVerifyDepth 1
    </VirtualHost>

By default, the username is the email declared in the certificate (the value
of the ``SSL_CLIENT_S_DN_Email`` environment variable.)

.. tip::

    Certificate authentication only works when the user access the application
    via HTTPS.

Anonymous Users
~~~~~~~~~~~~~~~

When you disable security, no user is attached to the request anymore. If you
still want one, you can activate anonymous users. An anonymous user is not
authenticated and "real" authentication occurs whenever the user wants to
access a resource restricted by an access control rule:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    anonymous: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <anonymous />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('anonymous' => true),
            ),
        ));

You can check if a user is fully-authenticated with the ``isAuthenticated()``
of the security context:

    $container->get('security.context')->isAuthenticated();

.. tip::

    All anonymous users automatically have the 'IS_AUTHENTICATED_ANONYMOUSLY'
    role.

Stateless Authentication
------------------------

By default, Symfony2 relies on a cookie (the Session) to persist the security
context of the user. But if you use certificates or HTTP authentication for
instance, persistence is not needed as credentials are available for each
request. In that case, and if you don't need to store anything else between
requests, you can activate the stateless authentication (which means that no
cookie will be ever created by Symfony2):

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_basic: true
                    stateless:  true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall stateless="true">
                <http-basic />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main' => array('http_basic' => true, 'stateless' => true),
            ),
        ));

.. note::

    If you use a form login, Symfony2 will create a cookie even if you set
    ``stateless`` to ``true``.

Impersonating a User
--------------------

Sometimes, it's useful to be able to switch from one user to another without
having to logout and login again (for instance when you are debugging or try
to understand a bug a user see and you cannot reproduce.) This can be easily
done by activating the ``switch-user`` listener:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_basic:  true
                    switch_user: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <http-basic />
                <switch-user />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main'=> array('http_basic' => true, 'switch_user' => true),
            ),
        ));

To switch to another user, just add a query string with the ``_switch_user``
parameter and the username as the value to the current URL:

    http://example.com/somewhere?_switch_user=thomas

To switch back to the original user, use the special ``_exit`` username:

    http://example.com/somewhere?_switch_user=_exit

Of course, this feature needs to be made available to a small group of users.
By default, access is restricted to users having the 'ROLE_ALLOWED_TO_SWITCH'
role. Change the default role with the ``role`` setting and for extra
security, also change the parameter name via the ``parameter`` setting:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_basic:  true
                    switch_user: { role: ROLE_ADMIN, parameter: _want_to_be_this_user }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <http-basic />
                <switch-user role="ROLE_ADMIN" parameter="_want_to_be_this_user" />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main'=> array(
                    'http_basic'  => true,
                    'switch_user' => array('role' => 'ROLE_ADMIN', 'parameter' => '_want_to_be_this_user'),
                ),
            ),
        ));

Logout Users
------------

If you want to provide a way for your users to logout, activate the logout
listener:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_basic: true
                    logout:     true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <http-basic />
                <logout />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main'=> array('http_basic' => true, 'logout' => true),
            ),
        ));

By default, users are logged out when they access ``/logout`` path and they
are redirected to ``/``. This can be easily changed via the ``path`` and
``target`` settings:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            firewalls:
                main:
                    http_basic: true
                    logout:     { path: /signout, target: /signin }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <firewall>
                <http-basic />
                <logout path="/signout" target="/signin" />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'firewalls' => array(
                'main'=> array(
                    'http_basic' => true,
                    'logout' => array('path' => '/signout', 'target' => '/signin')),
            ),
        ));

Authentication and User Providers
---------------------------------

By default, a firewall uses the first declared user provider for
authentication. But if you want to use different user providers for different
parts of your website, you can explicitly change the user provider for a
firewall, or just for an authentication mechanism:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security.config:
            providers:
                default:
                    password_encoder: sha1
                    entity: { class: SecurityBundle:User, property: username }
                certificate:
                    users:
                        fabien@example.com: { roles: ROLE_USER }

            firewalls:
                backend:
                    pattern:    /admin/.*
                    x509:       { provider: certificate }
                    form-login: { provider: default }
                    logout:     true
                api:
                    provider:   default
                    pattern:    /api/.*
                    http_basic: true
                    stateless:  true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <provider name="default">
                <password-encoder>sha1</password-encoder>
                <entity class="SecurityBundle:User" property="username" />
            </provider>

            <provider name="certificate">
                <user name="fabien@example.com" roles="ROLE_USER" />
            </provider>

            <firewall pattern="/admin/.*">
                <x509 provider="certificate" />
                <form-login provider="default" />
                <logout />
            </firewall>
            <firewall pattern="/api/.*" stateless="true" provider="default">
                <http-basic />
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'config', array(
            'providers' => array(
                'default' => array(
                    'password_encoder' => 'sha1',
                    'entity' => array('class' => 'SecurityBundle:User', 'property' => 'username'),
                ),
                'certificate' => array('users' => array(
                    'fabien@example.com' => array('roles' => 'ROLE_USER'),
                ),
            ),

            'firewalls' => array(
                'backend' => array(
                    'pattern' => '/admin/.*',
                    'x509' => array('provider' => 'certificate'),
                    'form-login' => array(provider' => 'default')
                    'logout' => true,
                ),
                'api' => array(
                    'provider' => 'default',
                    'pattern' => '/api/.*',
                    'http_basic' => true,
                    'stateless' => true,
                ),
            ),
        ));

In the above example, ``/admin/.*`` URLs accepts users from the
``certificate`` user provider when using X.509 authenticating, and the
``default`` provider when the user signs in with a form. The ``/api/.*`` URLs
uses the ``default`` provider for all authentication mechanisms.

.. note::

    The listeners do not use the user providers directly, but authenticating
    providers. They do the actual authentication, like checking the password,
    and they can use a user provider for that (this is not the case for the
    anonymous authentication provider for instance).
