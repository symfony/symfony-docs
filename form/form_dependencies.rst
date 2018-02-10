How to Access Services or Config from Inside a Form
===================================================

Sometimes, you may need to access a :doc:`service </service_container>` or other
configuration from inside of your form class. To do this, you have 2 options:

1) Pass Options to your Form
----------------------------

The simplest way to pass services or configuration to your form is via form *options*.
Suppose you need to access the ``doctrine.orm.entity_manager`` service so that you
can make a query. First, allow (in fact, require) a new ``entity_manager`` option
to be passed to your form::

    // src/AppBundle/Form/TaskType.php
    // ...

    class TaskType extends AbstractType
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...

            $resolver->setRequired('entity_manager');
        }
    }

Now that you've done this, you *must* pass an ``entity_manager`` option when you
create your form::

    // src/AppBundle/Controller/DefaultController.php
    // ...

    public function newAction()
    {
        $task = ...;
        $form = $this->createForm(new TaskType(), $task, array(
            'entity_manager' => $this->get('doctrine.orm.entity_manager'),
        ));

        // ...
    }

Finally, the ``entity_manager`` option is accessible in the ``$options`` argument
of your ``buildForm()`` method::

    // src/AppBundle/Form/TaskType.php
    // ...

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $options['entity_manager'];
            // ...
        }

        // ...
    }

Use this method to pass *anything* to your form.

2) Define your Form as a Service
--------------------------------

Alternatively, you can define your form class as a service. This is a good idea if
you want to re-use the form in several places - registering it as a service makes
this easier.

Suppose you need to access the ``doctrine.orm.entity_manager`` service so that you
can make a query. First, add this as an argument to your form class::

    // src/AppBundle/Form/TaskType.php

    use Doctrine\ORM\EntityManager;
    // ...

    class TaskType extends AbstractType
    {
        private $em;

        public function __construct(EntityManager $em)
        {
            $this->em = $em;
        }

        // ...
    }

Next, register this as a service and tag it with ``form.type``:

.. configuration-block::

    .. code-block:: yaml

        # src/AppBundle/Resources/config/services.yml
        services:
            app.form.type.task:
                class: AppBundle\Form\TaskType
                arguments: ['@doctrine.orm.entity_manager']
                tags:
                    - { name: form.type, alias: app_task }

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.form.type.task" class="AppBundle\Form\TaskType">
                    <argument type="service" id="doctrine.orm.entity_manager"/>
                    <tag name="form.type" alias="app_task" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // src/AppBundle/Resources/config/services.php
        use AppBundle\Form\TaskType;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register('app.form.type.task', TaskType::class)
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('form.type', array(
                'alias' => 'app_task',
            ));

That's it! Use the ``alias`` key from the tag to reference your form::

    // src/AppBundle/Controller/DefaultController.php
    // ...

    public function newAction()
    {
        $task = ...;
        $form = $this->createForm('app_task', $task);

        // ...
    }

Or, use the from within another form::

    // src/AppBundle/Form/Type/ListType.php
    // ...

    class ListType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            // ...

            $builder->add('someTask', 'app_task');
        }
    }

Read :ref:`form-field-service` for more information.
