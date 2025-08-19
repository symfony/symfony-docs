Building a single Command Application
=====================================

When building a command line tool, you may not need to provide several commands.
In such a case, having to pass the command name each time is tedious. Fortunately,
it is possible to remove this need by declaring a single command application::

    #!/usr/bin/env php
    <?php // bin/file-counter.php
    require __DIR__.'/../vendor/autoload.php';

    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\SingleCommandApplication;

    (new SingleCommandApplication())
        ->setName('File Counter') // Optional
        ->setVersion('1.0.0') // Optional
        ->addArgument('dir', InputArgument::OPTIONAL, 'The directory', default: '.')
        ->addOption('all', 'a', InputOption::VALUE_NONE, 'count all files')
        ->setCode(function (InputInterface $input, OutputInterface $output): int {
            $dir = realpath($input->getArgument('dir'));
            $all = $input->getOption('all');
            $finder = (new Symfony\Component\Finder\Finder())
                ->in($dir)
                ->files()
                ->ignoreVCSIgnored(!$all)
            ;
            $count = iterator_count($finder);
            $output->writeln( "$dir has $count " .
                ($all ? "files" : "files in source control"));
            return SingleCommandApplication::SUCCESS;
        })
        ->run();

Now run it with

    php bin/file-counter.php

    php bin/file-counter.php --all

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
