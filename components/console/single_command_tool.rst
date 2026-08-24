Building a single Command Application
=====================================

When building a command line tool, you may not need to provide several commands.
In such a case, having to pass the command name each time is tedious. Fortunately,
it is possible to remove this need by declaring a single command application::

    #!/usr/bin/env php
    <?php
    require __DIR__.'/vendor/autoload.php';

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\Option;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\SingleCommandApplication;

    new SingleCommandApplication()
        ->setName('My Super Command') // Optional
        ->setVersion('1.0.0') // Optional
        ->setCode(function (OutputInterface $output, #[Argument] string $foo = 'The directory', #[Option] string $bar = ''): int {
            // output arguments and options

            return 0;
        })
        ->run();

You can still register a command as usual::

    #!/usr/bin/env php
    <?php
    require __DIR__.'/vendor/autoload.php';

    use Acme\Command\DefaultCommand;
    use Symfony\Component\Console\Application;

    $application = new Application('echo', '1.0.0');
    $command = new DefaultCommand();

    $application->addCommand($command);

    $application->setDefaultCommand($command->getName(), true);
    $application->run();

The :method:`Symfony\\Component\\Console\\Application::setDefaultCommand` method
accepts a boolean as second parameter. If true, the command ``echo`` will then
always be used, without having to pass its name.
