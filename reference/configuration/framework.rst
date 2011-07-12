.. index::
   single: Configuration Reference; Framework

FrameworkBundle Configuration ("framework")
===========================================

This reference document is a work in progress. It should be accurate, but
all options are not yet fully covered.

The ``FrameworkBundle`` contains most of the "base" framework functionality
and can be configured under the ``framework`` key in your application configuration.
This includes settings related to sessions, translation, forms, validation,
routing and more.

Configuration
-------------

* `charset`_
* `secret`_
* `exception_controller`_
* `ide`_
* `test`_
* `form`_
    * :ref:`enabled<config-framework-form-enabled>`
* `csrf_protection`_
    * :ref:`enabled<config-framework-csrf-enabled>`
    * `field_name`

charset
.......

**type**: ``string`` **default**: ``UTF-8``

The character set that's used throughout the framework. It becomes the service
container parameter named ``kernel.charset``.

secret
......

**type**: ``string`` **required**

This is a string that should be unique to your application. In practice,
it's used for generating the CSRF tokens, but it could be used in any other
context where having a unique string is useful. It becomes the service container
parameter named ``kernel.secret``.

exception_controller
....................

**type**: ``string`` **default**: ``Symfony\\Bundle\\FrameworkBundle\\Controller\\ExceptionController::showAction``

This is the controller that is activated after an exception is thrown anywhere
in your application. The default controller
(:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\ExceptionController`)
is what's responsible for rendering specific templates under different error
conditions (see :doc:`/cookbook/controller/error_pages`). Modifying this
option is advanced. If you need to customize an error page you should use
the previous link. If you need to perform some behavior on an exception,
you should add a listener to the ``kernel.exception`` event (see :ref:`dic-tags-kernel-event-listener`).

ide
...

**type**: ``string`` **default**: ``null``

If you're using an IDE like TextMate or Mac Vim, then Symfony can turn all
of the file paths in an exception message into a link, which will open that
file in your IDE.

Currently, the following options are supported:

* ``textmate``
* ``macvim``

test
....

**type**: ``Boolean``

If this configuration parameter is present, then the services related to
testing your application are loaded. This setting should be present in your
``test`` environment (usually via ``app/config/config_test.yml``). For more
information, see :doc:`/book/testing`.

form
....

csrf_protection
...............


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



