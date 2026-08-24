How to Hide Console Commands
============================

By default, all console commands are listed when executing the console application
script without arguments or when using the ``list`` command.

However, sometimes commands are not intended to be run by end-users; for
example, commands for the legacy parts of the application, commands exclusively
run through scheduled tasks, etc.

In those cases, you can define the command as **hidden** by setting to ``true``
the ``hidden`` property of the ``AsCommand`` attribute::

    // src/Command/LegacyCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Attribute\AsCommand;

    #[AsCommand(name: 'app:legacy', hidden: true)]
    class LegacyCommand
    {
        // ...
    }

You can also define a command as hidden using the pipe (``|``) syntax of
:ref:`command aliases <command-aliases>`. To do this, use the command name as one
of the aliases and leave the main command name (the part before the ``|``) empty::

    // src/Command/LegacyCommand.php
    namespace App\Command;

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: '|app:legacy')]
    class LegacyCommand extends Command
    {
        // ...
    }

.. note::

    Hidden commands are still available using the JSON or XML descriptor.
