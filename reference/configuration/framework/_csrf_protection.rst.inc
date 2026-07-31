.. _reference-framework-csrf-protection:

csrf_protection
~~~~~~~~~~~~~~~

.. seealso::

    For more information about CSRF protection, see :doc:`/security/csrf`.

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

This option can be used to disable CSRF protection on *all* Symfony features,
but you can also disable CSRF protection only on forms. See :ref:`form.csrf_protection <reference-framework-form-csrf-protection>`.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            csrf_protection: true

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">
            <framework:config>
                <framework:csrf-protection enabled="true"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;
        return static function (FrameworkConfig $framework): void {
            $framework->csrfProtection()
                ->enabled(true)
            ;
        };

If you're using forms, but want to avoid starting your session (e.g. using
forms in an API-only website), ``csrf_protection`` will need to be set to
``false``.

stateless_token_ids
...................

**type**: ``array`` **default**: ``[]``

The list of CSRF token ids that will use :ref:`stateless CSRF protection <csrf-stateless-tokens>`.

.. versionadded:: 7.2

    The ``stateless_token_ids`` option was introduced in Symfony 7.2.

check_header
............

**type**: ``integer`` or ``bool`` **default**: ``false``

Whether to check the CSRF token in an HTTP header in addition to the cookie when
using :ref:`stateless CSRF protection <csrf-stateless-tokens>`. You can also set
this to ``2`` (the value of the ``CHECK_ONLY_HEADER`` constant on the
:class:`Symfony\\Component\\Security\\Csrf\\SameOriginCsrfTokenManager` class)
to check only the header and ignore the cookie.

.. versionadded:: 7.2

    The ``check_header`` option was introduced in Symfony 7.2.

cookie_name
...........

**type**: ``string`` **default**: ``csrf-token``

The name of the cookie (and HTTP header) to use for the double-submit when using
:ref:`stateless CSRF protection <csrf-stateless-tokens>`.

.. versionadded:: 7.2

    The ``cookie_name`` option was introduced in Symfony 7.2.
