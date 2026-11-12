Creating a New Mailer, Notifier or Translation Bridge
=====================================================

Creating a new bridge for the Mailer, Notifier or Translation components
requires changes in several places of the Symfony code, the Symfony Flex recipes
and the Symfony documentation. Use the following checklist to make sure you
don't forget any of them.

Update the Code
---------------

* Add the new bridge to the ``UnsupportedSchemeException`` class of the
  component and to its ``UnsupportedSchemeExceptionTest`` test class;
* For Mailer and Notifier bridges, add the new transport factory to the
  ``FACTORY_CLASSES`` constant of the component's ``Transport`` class;
* Register the new factory service in the related FrameworkBundle config file
  (``mailer_transports.php``, ``notifier_transports.php`` or
  ``translation_providers.php``) and add its class to the ``$classToServices``
  list of the related method in
  :class:`Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\FrameworkExtension`;
* Add the ``#[\SensitiveParameter]`` attribute to the arguments that contain
  sensitive information (API keys, passwords, tokens, etc.);
* Use the current year in the copyright notice of the ``LICENSE`` file.

Update the Recipes
------------------

Add a Symfony Flex recipe for the new bridge to the `symfony/recipes`_
repository, so the needed environment variables (e.g. the DSN) are added to the
``.env`` file of the application when installing the bridge.

Update the Documentation
------------------------

Each bridge is documented in its own ``README.md`` file, so explain there how to
configure and use it. However, you must also update the following parts of the
component documentation.

For a Mailer bridge:

* Add the new bridge to the :ref:`list of third-party transports <mailer_3rd_party_transport>`;
* Add its available DSN formats to the list of DSN formats of that same section;
* If the new bridge supports tags and metadata, add it to the list of
  transports that support them in the :doc:`Mailer </mailer>` docs;
* If the new bridge supports webhooks, add it to the list of providers in the
  :doc:`Webhook </webhook>` docs.

For a Notifier bridge:

* Add the new bridge to the list of the related :doc:`Notifier </notifier>`
  channel (SMS, chat, push, etc.);
* If the new bridge supports webhooks, add it to the list of providers in the
  :doc:`Webhook </webhook>` docs.

For a Translation bridge:

* Add the new bridge to the list of :ref:`translation providers <translation-providers>`;
* Add its available DSN formats to the list of DSN formats of that same section.

.. _`symfony/recipes`: https://github.com/symfony/recipes
