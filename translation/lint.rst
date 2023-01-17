.. index::
    single: Translation; Lint
    single: Translation; Translation File Errors

How to Find Errors in Translation Files
=======================================

Symfony processes all the application translation files as part of the process
that compiles the application code before executing it. If there's an error in
any translation file, you will see an error message explaining the problem.

If you prefer, you can also validate the contents of any YAML and XLIFF
translation file using the ``lint:yaml`` and ``lint:xliff`` commands:

.. code-block:: terminal

    # lint a single file
    $ php bin/console lint:yaml translations/messages.en.yaml
    $ php bin/console lint:xliff translations/messages.en.xlf

    # lint a whole directory
    $ php bin/console lint:yaml translations
    $ php bin/console lint:xliff translations

    # lint multiple files or directories
    $ php bin/console lint:yaml translations path/to/trans
    $ php bin/console lint:xliff translations/messages.en.xlf translations/messages.es.xlf

The linter results can be exported to JSON using the ``--format`` option:

.. code-block:: terminal

    $ php bin/console lint:yaml translations/ --format=json
    $ php bin/console lint:xliff translations/ --format=json

When running these linters inside `GitHub Actions`_, the output is automatically
adapted to the format required by GitHub, but you can force that format too:

.. code-block:: terminal

    $ php bin/console lint:yaml translations/ --format=github
    $ php bin/console lint:xliff translations/ --format=github

.. versionadded:: 5.3

    The ``github`` output format was introduced in Symfony 5.3 for ``lint:yaml``
    and in Symfony 5.4 for ``lint:xliff``.

.. tip::

    The Yaml component provides a stand-alone ``yaml-lint`` binary allowing
    you to lint YAML files without having to create a console application:

    .. code-block:: terminal

        $ php vendor/bin/yaml-lint translations/

    .. versionadded:: 5.1

        The ``yaml-lint`` binary was introduced in Symfony 5.1.

.. _`GitHub Actions`: https://docs.github.com/en/free-pro-team@latest/actions
