Creating Private Console Commands
=================================

Console commands are public by default, meaning that they are listed alongside
other commands when executing the console application script without arguments
or when using the ``list`` command.

However, sometimes commands are not intended to be executed by end-users; for
example, commands for the legacy parts of the application, commands exclusively
executed through scheduled tasks, etc.

In those cases, you can define the command as **private** setting the
``setPublic()`` method to ``false`` in the command configuration::

    // src/AppBundle/Command/FooCommand.php
    namespace AppBundle\Command;

    use Symfony\Component\Console\Command\Command;

    class FooCommand extends Command
    {
        protected function configure()
        {
            $this
                ->setName('app:foo')
                // ...
                ->setPublic(false)
            ;
        }
    }

Private commands behave the same as public commands and they can be executed as
before, but they are no longer displayed in command listings, so end-users are
not aware of their existence.
