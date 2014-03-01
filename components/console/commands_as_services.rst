.. index::
    single: Console; Commands as Services

How to define Commands as Services
==================================

.. versionadded:: 2.4
   Support for registering commands in the service container was added in
   version 2.4.

By default, Symfony will take a look in the ``Command`` directory of your
bundles and automatically register your commands. For the ones implementing
the ``ContainerAwareCommand`` interface, Symfony will even inject the container.

While making life easier, this default implementation has some drawbacks in some
situations:

* what if you want your command to be defined elsewhere than in the ``Command``
  folder?
* what if you want to register conditionally your command, depending on the
  current environment or on the availability of some dependencies?
* what if you need to access dependencies before the ``setContainer`` is called
  (for example in the ``configure`` method)?
* what if you want to reuse a command many times, but with different
  dependencies or parameters?

To solve those problems, you can register your command as a service by simply
defining it with the ``console.command`` tag:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            acme_hello.command.my_command:
                class: Acme\HelloBundle\Command\MyCommand
                tags:
                    -  { name: console.command }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <service id="acme_hello.command.my_command"
                class="Acme\HelloBundle\Command\MyCommand">
                <tag name="console.command" />
            </service>
        </container>

    .. code-block:: php

        // app/config/config.php

        $container
            ->register('acme_hello.command.my_command', 'Acme\HelloBundle\Command\MyCommand')
            ->addTag('console.command')
        ;

Here are some use cases.

Use dependencies and parameters in configure
--------------------------------------------

For example, imagine you want to provide a default value for the ``name``
argument. You could:

* hard code a string and pass it as the 4th argument of ``addArgument``;
* allow the user to set the default value in the configuration;
* retrieve the default value from a service (a repository for example).

With a ``ContainerAwareCommand`` you wouldn't be able to retrieve the
configuration parameter, because the ``configure`` method is called in the
command's constructor. The only solution is to inject them through its
constructor:

    <?php
    // src/Acme/DemoBundle/Command/GreetCommand.php
    namespace Acme\DemoBundle\Command;

    use Acme\DemoBundle\Entity\NameRepository;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

    class GreetCommand extends Command
    {
        protected $nameRepository;

        public function __construct(NameRepository $nameRepository)
        {
            $this->nameRepository = $nameRepository;
        }

        protected function configure()
        {
            $defaultName = $this->nameRepository->findLastOne();

            $this
                ->setName('demo:greet')
                ->setDescription('Greet someone')
                ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?', $defaultName)
            ;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $name = $input->getArgument('name');

            $output->writeln($name);
        }
    }
