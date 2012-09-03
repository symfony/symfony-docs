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
            access_denied_url:    ~ # Example: /foo/error403

            # strategy can be: none, migrate, invalidate
            session_fixation_strategy:  migrate
            hide_user_not_found:  true
            always_authenticate_before_granting:  false
            erase_credentials:    true
            access_decision_manager:
                strategy:             affirmative
                allow_if_all_abstain:  false
                allow_if_equal_granted_denied:  true
            acl:

                # any name configured in doctrine.dbal section
                connection:           ~
                cache:
                    id:                   ~
                    prefix:               sf2_acl_
                provider:             ~
                tables:
                    class:                acl_classes
                    entry:                acl_entries
                    object_identity:      acl_object_identities
                    object_identity_ancestors:  acl_object_identity_ancestors
                    security_identity:    acl_security_identities
                voter:
                    allow_if_object_identity_unavailable:  true

            encoders:
                # Examples:
                Acme\DemoBundle\Entity\User1: sha512
                Acme\DemoBundle\Entity\User2:
                    algorithm:           sha512
                    encode_as_base64:    true
                    iterations:          5000

                # Example options/values for what a custom encoder might look like
                Acme\Your\Class\Name:
                    algorithm:            ~
                    ignore_case:          false
                    encode_as_base64:     true
                    iterations:           5000
                    id:                   ~

            providers:            # Required
                # Examples:
                memory:
                    name:                memory
                    users:
                        foo:
                            password:            foo
                            roles:               ROLE_USER
                        bar:
                            password:            bar
                            roles:               [ROLE_USER, ROLE_ADMIN]
                entity:
                    entity:
                        class:               SecurityBundle:User
                        property:            username

                # Example custom provider
                some_custom_provider:
                    id:                   ~
                    chain:
                        providers:            []

            firewalls:            # Required
                # Examples:
                somename:
                    pattern: .*
                    request_matcher: some.service.id
                    access_denied_url: /foo/error403
                    access_denied_handler: some.service.id
                    entry_point: some.service.id
                    provider: some_key_from_above
                    context: name
                    stateless: false
                    x509:
                        provider: some_key_from_above
                    http_basic:
                        provider: some_key_from_above
                    http_digest:
                        provider: some_key_from_above
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

                # Default values and options for any firewall
                some_firewall_listener:
                    pattern:              ~
                    security:             true
                    request_matcher:      ~
                    access_denied_url:    ~
                    access_denied_handler:  ~
                    entry_point:          ~
                    provider:             ~
                    stateless:            false
                    context:              ~
                    logout:
                        csrf_parameter:       _csrf_token
                        csrf_provider:        ~
                        intention:            logout
                        path:                 /logout
                        target:               /
                        success_handler:      ~
                        invalidate_session:   true
                        delete_cookies:

                            # Prototype
                            name:
                                path:                 ~
                                domain:               ~
                        handlers:             []
                    anonymous:
                        key:                  4f954a0667e01
                    switch_user:
                        provider:             ~
                        parameter:            _switch_user
                        role:                 ROLE_ALLOWED_TO_SWITCH

            access_control:
                requires_channel:     ~

                # use the urldecoded format
                path:                 ~ # Example: ^/path to resource/
                host:                 ~
                ip:                   ~
                methods:              []
                roles:                []
            role_hierarchy:
                ROLE_ADMIN:      [ROLE_ORGANIZER, ROLE_USER]
                ROLE_SUPERADMIN: [ROLE_ADMIN]

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
