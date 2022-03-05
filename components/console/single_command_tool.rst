.. index::
    single: Console; Single command application

Building a single Command Application
=====================================

When building a command line tool, you may not need to provide several commands.
In such a case, having to pass the command name each time is tedious. Fortunately,
it is possible to remove this need by declaring a single command application::

    #!/usr/bin/env php
    <?php
    require __DIR__.'/vendor/autoload.php';

    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\SingleCommandApplication;

    (new SingleCommandApplication())
        ->setName('My Super Command') // Optional
        ->setVersion('1.0.0') // Optional
        ->addArgument('foo', InputArgument::OPTIONAL, 'The directory')
        ->addOption('bar', null, InputOption::VALUE_REQUIRED)
        ->setCode(function (InputInterface $input, OutputInterface $output) {
            // output arguments and options
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

    $application->add($command);

    $application->setDefaultCommand($command->getName(), true);
    $application->run();

The :method:`Symfony\\Component\\Console\\Application::setDefaultCommand` method
accepts a boolean as second parameter. If true, the command ``echo`` will then
always be used, without having to pass its name.
