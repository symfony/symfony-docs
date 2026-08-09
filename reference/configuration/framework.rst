Framework Configuration Reference (FrameworkBundle)
===================================================

The FrameworkBundle defines the main framework configuration, from sessions and
translations to forms, validation, routing and more. All these options are
configured under the ``framework`` key in your application configuration.

.. code-block:: terminal

    # displays the default config values defined by Symfony
    $ php bin/console config:dump-reference framework

    # displays the actual config values used by your application
    $ php bin/console debug:config framework

.. note::

    When using XML, you must use the ``http://symfony.com/schema/dic/symfony``
    namespace and the related XSD schema is available at:
    ``https://symfony.com/schema/dic/symfony/symfony-1.0.xsd``

.. include:: /reference/configuration/framework/_annotations.rst.inc

.. include:: /reference/configuration/framework/_assets.rst.inc

.. include:: /reference/configuration/framework/_asset_mapper.rst.inc

.. include:: /reference/configuration/framework/_cache.rst.inc

.. include:: /reference/configuration/framework/_csrf_protection.rst.inc

.. include:: /reference/configuration/framework/_default_locale.rst.inc

.. include:: /reference/configuration/framework/_enabled_locales.rst.inc

.. include:: /reference/configuration/framework/_set_content_language_from_locale.rst.inc

.. include:: /reference/configuration/framework/_set_locale_from_accept_language.rst.inc

.. include:: /reference/configuration/framework/_disallow_search_engine_index.rst.inc

.. include:: /reference/configuration/framework/_error_controller.rst.inc

.. include:: /reference/configuration/framework/_esi.rst.inc

.. include:: /reference/configuration/framework/_exceptions.rst.inc

.. include:: /reference/configuration/framework/_form.rst.inc

.. include:: /reference/configuration/framework/_fragments.rst.inc

.. include:: /reference/configuration/framework/_handle_all_throwables.rst.inc

.. include:: /reference/configuration/framework/_html_sanitizer.rst.inc

.. include:: /reference/configuration/framework/_http_cache.rst.inc

.. include:: /reference/configuration/framework/_http_client.rst.inc

.. include:: /reference/configuration/framework/_http_method_override.rst.inc

.. include:: /reference/configuration/framework/_allowed_http_method_override.rst.inc

.. include:: /reference/configuration/framework/_ide.rst.inc

.. include:: /reference/configuration/framework/_json_streamer.rst.inc

.. include:: /reference/configuration/framework/_lock.rst.inc

.. include:: /reference/configuration/framework/_mailer.rst.inc

.. include:: /reference/configuration/framework/_messenger.rst.inc

.. include:: /reference/configuration/framework/_php_errors.rst.inc

.. include:: /reference/configuration/framework/_profiler.rst.inc

.. include:: /reference/configuration/framework/_property_access.rst.inc

.. include:: /reference/configuration/framework/_property_info.rst.inc

.. include:: /reference/configuration/framework/_rate_limiter.rst.inc

.. include:: /reference/configuration/framework/_remote_event.rst.inc

.. include:: /reference/configuration/framework/_request.rst.inc

.. include:: /reference/configuration/framework/_router.rst.inc

.. include:: /reference/configuration/framework/_secret.rst.inc

.. include:: /reference/configuration/framework/_secrets.rst.inc

.. include:: /reference/configuration/framework/_semaphore.rst.inc

.. include:: /reference/configuration/framework/_scheduler.rst.inc

.. include:: /reference/configuration/framework/_serializer.rst.inc

.. include:: /reference/configuration/framework/_session.rst.inc

.. include:: /reference/configuration/framework/_ssi.rst.inc

.. include:: /reference/configuration/framework/_test.rst.inc

.. include:: /reference/configuration/framework/_translator.rst.inc

.. include:: /reference/configuration/framework/_trust_x_sendfile_type_header.rst.inc

.. include:: /reference/configuration/framework/_trusted_headers.rst.inc

.. include:: /reference/configuration/framework/_trusted_hosts.rst.inc

.. include:: /reference/configuration/framework/_trusted_proxies.rst.inc

.. include:: /reference/configuration/framework/_type_info.rst.inc

.. include:: /reference/configuration/framework/_uid.rst.inc

.. include:: /reference/configuration/framework/_validation.rst.inc

.. include:: /reference/configuration/framework/_notifier.rst.inc

.. include:: /reference/configuration/framework/_web_link.rst.inc

.. include:: /reference/configuration/framework/_webhook.rst.inc

.. include:: /reference/configuration/framework/_workflows.rst.inc
