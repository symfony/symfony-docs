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
    $ ./bin/console lint:yaml translations/messages.en.yaml
    $ ./bin/console lint:xliff translations/messages.en.xlf

    # lint a whole directory
    $ ./bin/console lint:yaml translations
    $ ./bin/console lint:xliff translations
    
    # lint multiple files or directories
    $ ./bin/console lint:yaml translations path/to/trans
    $ ./bin/console lint:xliff translations/messages.en.xlf translations/messages.es.xlf

.. versionadded:: 4.2
    The feature to lint multiple files and directories was introduced in Symfony 4.2.

The linter results can be exported to JSON using the ``--format`` option:

.. code-block:: terminal

    $ ./bin/console lint:yaml translations/ --format=json
    $ ./bin/console lint:xliff translations/ --format=json
