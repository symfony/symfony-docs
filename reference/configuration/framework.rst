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

ide
...

**type**: ``string`` **default**: ``null``

If you're using an IDE like TextMate or Mac Vim, then Symfony can turn all
of the file paths in an exception message into a link, which will open that
file in your IDE.

If you use TextMate or Mac Vim, you can simply use one of the following built-in
values:

* ``textmate``
* ``macvim``

You can also specify a custom file link string. If you do this, all percentage
signs (``%``) must be doubled to escape that character. For example, the
full TextMate string would look like this:

.. code-block:: yaml

    framework:
        ide:  "txmt://open?url=file://%%f&line=%%l"

Of course, since every developer uses a different IDE, it's better to set
this on a system level. This can be done by setting the ``xdebug.file_link_format``
PHP.ini value to the file link string. If this configuration value is set, then
the ``ide`` option does not need to be specified.

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



