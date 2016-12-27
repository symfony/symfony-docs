.. index::
    single: Console; Single command application

Building a single Command Application
=====================================

When building a command line tool, you may not need to provide several commands.
In such case, having to pass the command name each time is tedious. Fortunately,
it is possible to remove this need by declaring a single command application::

  #!/usr/bin/env php
  <?php
  require __DIR__.'/vendor/autoload.php';

  use Symfony\Component\Console\Application;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Output\OutputInterface;

  (new Application('echo', '1.0.0'))
    ->register('echo')
        ->addArgument('foo', InputArgument::OPTIONAL, 'The directory')
        ->addOption('bar', null, InputOption::VALUE_REQUIRED)
        ->setCode(function(InputInterface $input, OutputInterface $output) {
            // output arguments and options
        })
    ->getApplication()
    ->setDefaultCommand('echo', true) // Single command application
    ->run();

The method :method:`Symfony\\Component\\Console\\Application::setDefaultCommand`
accepts a boolean as second parameter. If true, the command ``echo`` will then
always be used, without having to pass its name.

Of course, you can still register a command as usual::

  #!/usr/bin/env php
  <?php
  require __DIR__.'/vendor/autoload.php';

  use Symfony\Component\Console\Application;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Output\OutputInterface;

  use Acme\Command\DefaultCommand;

  $application = new Application('echo', '1.0.0');
  $command = new DefaultCommand();

  $application->add($command);

  $application->setDefaultCommand($command->getName(), true);
  $application->run();

If you need to create a kernel-aware application, you can use
``Symfony\Bundle\FrameworkBundle\Console\Application`` instead of ``Symfony\Component\Console\Application``

  #!/usr/bin/env php
  <?php
  require __DIR__.'/vendor/autoload.php';

  use Symfony\Bundle\FrameworkBundle\Console\Application;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Output\OutputInterface;

  use Acme\Command\DefaultCommand;

  $kernel = new AppKernel('dev', true);
  $application = new Application($kernel);
  $command = new DefaultCommand();

  $application->add($command);

  $application->setDefaultCommand($command->getName(), true);
  $application->run();

.. note::
  An instance of ``Symfony\Bundle\FrameworkBundle\Console\Application`` will automatically
  load all default commands. If you want to change this behaviour, you can pass ``false``
  as the second argument when creating the object:

    ``$application = new Application($kernel, false);``
