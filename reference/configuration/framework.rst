.. index::
   single: Configuration Reference; Framework

FrameworkBundle Configuration
=============================

The ``FrameworkBundle`` contains most of the "base" framework functionality
and can be configured under the ``framework`` key in your application configuration.
This includes settings related to sessions, translation, forms, validation,
routing and more.

Full Default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        framework:

            # general configuration
            charset:              ~
            secret:               ~ # Required
            exception_controller:  Symfony\Bundle\FrameworkBundle\Controller\ExceptionController::showAction
            ide:                  ~
            test:                 ~

            # form configuration
            form:
                enabled:              true
            csrf_protection:
                enabled:              true
                field_name:           _token

            # esi configuration
            esi:
                enabled:              true

            # profiler configuration
            profiler:
                only_exceptions:      false
                only_master_requests:  false
                dsn:                  sqlite:%kernel.cache_dir%/profiler.db
                username:
                password:
                lifetime:             86400
                matcher:
                    ip:                   ~
                    path:                 ~
                    service:              ~

            # router configuration
            router:
                resource:             ~ # Required
                type:                 ~
                http_port:            80
                https_port:           443

            # session configuration
            session:
                auto_start:           ~
                default_locale:       en
                storage_id:           session.storage.native
                name:                 ~
                lifetime:             ~
                path:                 ~
                domain:               ~
                secure:               ~
                httponly:             ~

            # templating configuration
            templating:
                assets_version:       ~
                assets_version_format:  ~
                assets_base_urls:
                    http:                 []
                    ssl:                  []
                cache:                ~
                engines:              # Required
                form:
                    resources:        [FrameworkBundle:Form]

                    # Example:
                    - twig
                loaders:              []
                packages:

                    # Prototype
                    name:
                        version:              ~
                        version_format:       ~
                        base_urls:
                            http:                 []
                            ssl:                  []

            # translator configuration
            translator:
                enabled:              true
                fallback:             en

            # validation configuration
            validation:
                enabled:              true
                cache:                ~
                enable_annotations:   false

            # annotation configuration
            annotations:
                cache:                file
                file_cache_dir:       %kernel.cache_dir%/annotations
                debug:                true

General Configuration
---------------------

* ``charset`` (type: string)

* ``secret`` (type: string, *required*)

* ``exception_controller`` (type: string)

* ``ide`` (type: string)

* ``test`` (type: Boolean)
