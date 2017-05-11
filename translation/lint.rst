.. index::
    single: Translation; Lint
    single: Translation; Translation File Errors

How to Find Errors in Translation Files
=======================================

Symfony processes all the application translation files as part of the process
that compiles the application code before executing it. If there's an error in
any translation file, you'll see an error message explaining the problem.

If you prefer, you can also validate the contents of any YAML translation file
using the ``lint:yaml`` command:

.. code-block:: terminal

    # lint a single file
    $ ./bin/console lint:yaml app/Resources/translations/messages.en.yml

    # lint a whole directory
    $ ./bin/console lint:yaml app/Resources/translations

    # lint a specific bundle
    $ ./bin/console lint:yaml @AppBundle

The linter results can be exported to JSON using the ``--format`` option:

.. code-block:: terminal

    # lint a single file
    $ ./bin/console lint:yaml app/Resources/translations --format=json
