.. index::
    single: Translation; Lint
    single: Translation; Translation File Errors

How to Find Errors in Translation Files
=======================================

Symfony processes all the application translation files as part of the process
that compiles the application code before executing it. If there's an error in
any translation file, you'll see an error message explaining the problem.

If you prefer, you can also validate the contents of any YAML and XLIFF
translation file using the ``lint:yaml`` and ``lint:xliff`` commands:

.. code-block:: terminal

    # lint a single file
    $ ./bin/console lint:yaml app/Resources/translations/messages.en.yml
    $ ./bin/console lint:xliff app/Resources/translations/messages.en.xlf

    # lint a whole directory
    $ ./bin/console lint:yaml app/Resources/translations
    $ ./bin/console lint:xliff app/Resources/translations

    # lint a specific bundle
    $ ./bin/console lint:yaml @AppBundle
    $ ./bin/console lint:xliff @AppBundle

.. versionadded:: 3.3
    The ``lint:xliff`` command was introduced in Symfony 3.3.

The linter results can be exported to JSON using the ``--format`` option:

.. code-block:: terminal

    # lint a single file
    $ ./bin/console lint:yaml app/Resources/translations --format=json
    $ ./bin/console lint:xliff app/Resources/translations --format=json
