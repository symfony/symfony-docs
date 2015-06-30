.. index::
    single: Console; Commands as Services

How to Define Commands as Services
==================================

By default, Symfony will take a look in the ``Command`` directory of each
bundle and automatically register your commands. If a command extends the
:class:`Symfony\\Bundle\\FrameworkBundle\\Command\\ContainerAwareCommand`,
Symfony will even inject the container.
While making life easier, this has some limitations:

* Your command must live in the ``Command`` directory;
* There's no way to conditionally register your service based on the environment
  or availability of some dependencies;
* You can't access the container in the ``configure()`` method (because
  ``setContainer`` hasn't been called yet);
* You can't use the same class to create many commands (i.e. each with
  different configuration).

To solve these problems, you can register your command as a service and tag it
with ``console.command``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            app.command.my_command:
                class: AppBundle\Command\MyCommand
                tags:
                    -  { name: console.command }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
            http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.command.my_command"
                    class="AppBundle\Command\MyCommand">
                    <tag name="console.command" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container
            ->register(
                'app.command.my_command',
                'AppBundle\Command\MyCommand'
            )
            ->addTag('console.command')
        ;

Using Dependencies and Parameters to Set Default Values for Options
-------------------------------------------------------------------

Imagine you want to provide a default value for the ``name`` option. You could
pass one of the following as the 5th argument of ``addOption()``:

* a hardcoded string;
* a container parameter (e.g. something from ``parameters.yml``);
* a value computed by a service (e.g. a repository).

By extending ``ContainerAwareCommand``, only the first is possible, because you
can't access the container inside the ``configure()`` method. Instead, inject
any parameter or service you need into the constructor. For example, suppose you
store the default value in some ``%command.default_name%`` parameter::

    // src/AppBundle/Command/GreetCommand.php
    namespace AppBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

    class GreetCommand extends Command
    {
        protected $defaultName;

        public function __construct($defaultName)
        {
            $this->defaultName = $defaultName;
            
            parent::__construct();
        }

        protected function configure()
        {
            // try to avoid work here (e.g. database query)
            // this method is *always* called - see warning below
            $defaultName = $this->defaultName;

            $this
                ->setName('demo:greet')
                ->setDescription('Greet someone')
                ->addOption(
                    'name',
                    '-n',
                    InputOption::VALUE_REQUIRED,
                    'Who do you want to greet?',
                    $defaultName
                )
            ;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $name = $input->getOption('name');

            $output->writeln($name);
        }
    }

Now, just update the arguments of your service configuration like normal to
inject the ``command.default_name`` parameter:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        parameters:
            command.default_name: Javier

        services:
            app.command.my_command:
                class: AppBundle\Command\MyCommand
                arguments: ["%command.default_name%"]
                tags:
                    -  { name: console.command }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
            http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="command.default_name">Javier</parameter>
            </parameters>

            <services>
                <service id="app.command.my_command"
                    class="AppBundle\Command\MyCommand">
                    <argument>%command.default_name%</argument>
                    <tag name="console.command" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->setParameter('command.default_name', 'Javier');

        $container
            ->register(
                'app.command.my_command',
                'AppBundle\Command\MyCommand',
            )
            ->setArguments(array('%command.default_name%'))
            ->addTag('console.command')
        ;

Great, you now have a dynamic default value!

.. caution::

    Be careful not to actually do any work in ``configure`` (e.g. make database
    queries), as your code will be run, even if you're using the console to
    execute a different command.
