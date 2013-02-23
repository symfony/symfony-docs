.. index::
   single: Security; Configuration reference

Security Configuration Reference
================================

The security system is one of the most powerful parts of Symfony2, and can
largely be controlled via its configuration.

Full Default Configuration
--------------------------

The following is the full default configuration for the security system.
Each part will be explained in the next section.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            access_denied_url: /foo/error403

            always_authenticate_before_granting: false

            # strategy can be: none, migrate, invalidate
            session_fixation_strategy: migrate

            access_decision_manager:
                strategy: affirmative
                allow_if_all_abstain: false
                allow_if_equal_granted_denied: true

            acl:
                connection: default # any name configured in doctrine.dbal section
                tables:
                    class: acl_classes
                    entry: acl_entries
                    object_identity: acl_object_identities
                    object_identity_ancestors: acl_object_identity_ancestors
                    security_identity: acl_security_identities
                cache:
                    id: service_id
                    prefix: sf2_acl_
                voter:
                    allow_if_object_identity_unavailable: true

            encoders:
                somename:
                    class: Acme\DemoBundle\Entity\User
                Acme\DemoBundle\Entity\User: sha512
                Acme\DemoBundle\Entity\User: plaintext
                Acme\DemoBundle\Entity\User:
                    algorithm: sha512
                    encode_as_base64: true
                    iterations: 5000
                Acme\DemoBundle\Entity\User:
                    id: my.custom.encoder.service.id

            providers:
                memory:
                    name: memory
                    users:
                        foo: { password: foo, roles: ROLE_USER }
                        bar: { password: bar, roles: [ROLE_USER, ROLE_ADMIN] }
                entity:
                    entity: { class: SecurityBundle:User, property: username }

            factories:
                MyFactory: "%kernel.root_dir%/../src/Acme/DemoBundle/Resources/config/security_factories.xml"

            firewalls:
                somename:
                    pattern: .*
                    request_matcher: some.service.id
                    access_denied_url: /foo/error403
                    access_denied_handler: some.service.id
                    entry_point: some.service.id
                    provider: name
                    # manages where each firewall stores session information
                    # See "Firewall Context" below for more details
                    context: context_key
                    stateless: false
                    x509:
                        provider: name
                    http_basic:
                        provider: name
                    http_digest:
                        provider: name
                    form_login:
                        # submit the login form here
                        check_path: /login_check

                        # the user is redirected here when he/she needs to login
                        login_path: /login

                        # if true, forward the user to the login form instead of redirecting
                        use_forward: false

                        # login success redirecting options (read further below)
                        always_use_default_target_path: false
                        default_target_path:            /
                        target_path_parameter:          _target_path
                        use_referer:                    false

                        # login failure redirecting options (read further below)
                        failure_path:    /foo
                        failure_forward: false
                        failure_handler: some.service.id
                        success_handler: some.service.id

                        # field names for the username and password fields
                        username_parameter: _username
                        password_parameter: _password

                        # csrf token options
                        csrf_parameter: _csrf_token
                        intention:      authenticate
                        csrf_provider:  my.csrf_provider.id

                        # by default, the login form *must* be a POST, not a GET
                        post_only:      true
                        remember_me:    false
                    remember_me:
                        token_provider: name
                        key: someS3cretKey
                        name: NameOfTheCookie
                        lifetime: 3600 # in seconds
                        path: /foo
                        domain: somedomain.foo
                        secure: false
                        httponly: true
                        always_remember_me: false
                        remember_me_parameter: _remember_me
                    logout:
                        path:   /logout
                        target: /
                        invalidate_session: false
                        delete_cookies:
                            a: { path: null, domain: null }
                            b: { path: null, domain: null }
                        handlers: [some.service.id, another.service.id]
                        success_handler: some.service.id
                    anonymous: ~

            access_control:
                -
                    path: ^/foo
                    host: mydomain.foo
                    ip: 192.0.0.0/8
                    roles: [ROLE_A, ROLE_B]
                    requires_channel: https

            role_hierarchy:
                ROLE_SUPERADMIN: ROLE_ADMIN
                ROLE_SUPERADMIN: 'ROLE_ADMIN, ROLE_USER'
                ROLE_SUPERADMIN: [ROLE_ADMIN, ROLE_USER]
                anything: { id: ROLE_SUPERADMIN, value: 'ROLE_USER, ROLE_ADMIN' }
                anything: { id: ROLE_SUPERADMIN, value: [ROLE_USER, ROLE_ADMIN] }

.. _reference-security-firewall-form-login:

Form Login Configuration
------------------------

When using the ``form_login`` authentication listener beneath a firewall,
there are several common options for configuring the "form login" experience.

For even more details, see :doc:`/cookbook/security/form_login`.

The Login Form and Process
~~~~~~~~~~~~~~~~~~~~~~~~~~

*   ``login_path`` (type: ``string``, default: ``/login``)
    This is the URL that the user will be redirected to (unless ``use_forward``
    is set to ``true``) when he/she tries to access a protected resource
    but isn't fully authenticated.

    This URL **must** be accessible by a normal, un-authenticated user, else
    you may create a redirect loop. For details, see
    ":ref:`Avoid Common Pitfalls<book-security-common-pitfalls>`".

*   ``check_path`` (type: ``string``, default: ``/login_check``)
    This is the URL that your login form must submit to. The firewall will
    intercept any requests (``POST`` requests only, by default) to this URL
    and process the submitted login credentials.
    
    Be sure that this URL is covered by your main firewall (i.e. don't create
    a separate firewall just for ``check_path`` URL).

*   ``use_forward`` (type: ``Boolean``, default: ``false``)
    If you'd like the user to be forwarded to the login form instead of being
    redirected, set this option to ``true``.

*   ``username_parameter`` (type: ``string``, default: ``_username``)
    This is the field name that you should give to the username field of
    your login form. When you submit the form to ``check_path``, the security
    system will look for a POST parameter with this name.

*   ``password_parameter`` (type: ``string``, default: ``_password``)
    This is the field name that you should give to the password field of
    your login form. When you submit the form to ``check_path``, the security
    system will look for a POST parameter with this name.

*   ``post_only`` (type: ``Boolean``, default: ``true``)
    By default, you must submit your login form to the ``check_path`` URL
    as a POST request. By setting this option to ``false``, you can send a
    GET request to the ``check_path`` URL.

Redirecting after Login
~~~~~~~~~~~~~~~~~~~~~~~

* ``always_use_default_target_path`` (type: ``Boolean``, default: ``false``)
* ``default_target_path`` (type: ``string``, default: ``/``)
* ``target_path_parameter`` (type: ``string``, default: ``_target_path``)
* ``use_referer`` (type: ``Boolean``, default: ``false``)

.. _reference-security-firewall-context:

Firewall Context
----------------

Most applications will only need one :ref:`firewall<book-security-firewalls>`.
But if your application *does* use multiple firewalls, you'll notice that
if you're authenticated in one firewall, you're not automatically authenticated
in another. In other words, the systems don't share a common "context": each
firewall acts like a separate security system.

However, each firewall has an optional ``context`` key (which defaults to
the name of the firewall), which is used when storing and retrieving security
data to and from the session. If this key were set to the same value across
multiple firewalls, the "context" could actually be shared:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                somename:
                    # ...
                    context: my_context
                othername:
                    # ...
                    context: my_context

    .. code-block:: xml

       <!-- app/config/security.xml -->
       <security:config>
          <firewall name="somename" context="my_context">
            <! ... ->
          </firewall>
          <firewall name="othername" context="my_context">
            <! ... ->
          </firewall>
       </security:config>

    .. code-block:: php

       // app/config/security.php
       $container->loadFromExtension('security', array(
            'firewalls' => array(
                'somename' => array(
                    // ...
                    'context' => 'my_context'
                ),
                'othername' => array(
                    // ...
                    'context' => 'my_context'
                ),
            ),
       ));

HTTP-Digest Authentication
--------------------------

To use HTTP-Digest authentication you need to provide a realm and a key:

.. configuration-block::

   .. code-block:: yaml

      # app/config/security.yml
      security:
         firewalls:
            somename:
              http_digest:
               key: "a_random_string"
               realm: "secure-api"

   .. code-block:: xml

      <!-- app/config/security.xml -->
      <security:config>
         <firewall name="somename">
            <http-digest key="a_random_string" realm="secure-api" />
         </firewall>
      </security:config>

   .. code-block:: php

      // app/config/security.php
      $container->loadFromExtension('security', array(
           'firewalls' => array(
               'somename' => array(
                   'http_digest' => array(
                       'key'   => 'a_random_string',
                       'realm' => 'secure-api',
                   ),
               ),
           ),
      ));

