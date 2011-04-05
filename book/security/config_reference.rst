.. index::
   single: Security; Configuration Reference

Configuration Reference
=======================

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            access_denied_url: /foo/error403
            
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

            always_authenticate_before_granting: false

            # strategy can be: none, migrate, invalidate
            session_fixation_strategy: migrate

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
                MyFactory: %kernel.root_dir%/../src/Acme/DemoBundle/Resources/config/security_factories.xml

            firewalls:
                somename:
                    pattern: .*
                    request_matcher: some.service.id
                    access_denied_url: /foo/error403
                    access_denied_handler: some.service.id
                    entry_point: some.service.id
                    provider: name
                    context: name
                    x509:
                        provider: name
                    http_basic:
                        provider: name
                    http_digest:
                        provider: name
                    form_login:
                        check_path: /login_check
                        login_path: /login
                        use_forward: true
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
                        csrf_page_id: form_login
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

