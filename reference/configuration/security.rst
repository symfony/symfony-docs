.. index::
   single: Security; Configuration Reference

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

            # whether or not to call eraseCredentials on the token
            erase_credentials: true

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
                memory_provider_name:
                    memory:
                        users:
                            foo: { password: foo, roles: ROLE_USER }
                            bar: { password: bar, roles: [ROLE_USER, ROLE_ADMIN] }
                entity_provider_name:
                    entity: { class: SecurityBundle:User, property: username }

            factories:
                MyFactory: %kernel.root_dir%/../src/Acme/DemoBundle/Resources/config/security_factories.xml

            firewalls:
                somename:
                    pattern: .*
                    request_matcher: some.service.id
                    access_denied_url: /foo/error403
                    access_denied_handler: some.service.id
                    entry_point: some.service.id
                    provider: some_provider_key_from_above
                    context: name
                    stateless: false
                    x509:
                        provider: some_provider_key_from_above
                    http_basic:
                        provider: some_provider_key_from_above
                    http_digest:
                        provider: some_provider_key_from_above
                    form_login:
                        check_path: /login_check
                        login_path: /login
                        use_forward: false
                        always_use_default_target_path: false
                        default_target_path: /
                        target_path_parameter: _target_path
                        use_referer: false
                        failure_path: /foo
                        failure_forward: false
                        failure_handler: some.service.id
                        success_handler: some.service.id
                        username_parameter: _username
                        password_parameter: _password
                        csrf_parameter: _csrf_token
                        intention: authenticate
                        csrf_provider: my.csrf_provider.id
                        post_only: true
                        remember_me: false
                    remember_me:
                        token_provider: name
                        key: someS3cretKey
                        name: NameOfTheCookie
                        lifetime: 3600 # in seconds
                        path: /foo
                        domain: somedomain.foo
                        secure: true
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
there are several common options for configuring the "form login" experience:

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
    intercept any requests (``POST`` requests only, be default) to this URL
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
