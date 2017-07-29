How to Access Services or Config from Inside a Form
===================================================

Sometimes, you may need to access a :doc:`service </service_container>` or other
configuration from inside of your form class. To do this, you have 2 options:

1) Pass Options to your Form
----------------------------

The simplest way to pass services or configuration to your form is via form *options*.
Suppose you need to access the Doctrine entity manager so that you can make a
query. First, allow (in fact, require) a new ``entity_manager`` option to be
passed to your form::

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
    use AppBundle\Form\TaskType;

    // ...
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();

        $task = ...;
        $form = $this->createForm(TaskType::class, $task, array(
            'entity_manager' => $em,
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

Suppose you need to access the :ref:`EntityManager <doctrine-entity-manager>` object
so that you can make a query. First, add this as an argument to your form class::

    // src/AppBundle/Form/TaskType.php

    use Doctrine\ORM\EntityManagerInterface;
    // ...

    class TaskType extends AbstractType
    {
        private $em;

        public function __construct(EntityManagerInterface $em)
        {
            $this->em = $em;
        }

        // ...
    }

If you're using :ref:`autowire <services-autowire>` and
:ref:`autoconfigure <services-autoconfigure>`, then you don't need to do *anything*
else: Symfony will automatically know to pass the correct ``EntityManager`` object
to your ``__construct()`` method.

If you are **not using autowire and autoconfigure**, register your form as a service
manually and tag it with ``form.type``:

.. configuration-block::

    .. code-block:: yaml

        # src/AppBundle/Resources/config/services.yml
        services:
            AppBundle\Form\TaskType:
                arguments: ['@doctrine.orm.entity_manager']
                tags: [form.type]

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Form\TaskType">
                    <argument type="service" id="doctrine.orm.entity_manager"/>
                    <tag name="form.type" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // src/AppBundle/Resources/config/services.php
        use AppBundle\Form\TaskType;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(TaskType::class)
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('form.type')
        ;

.. versionadded:: 3.3
    Prior to Symfony 3.3, you needed to define form type services as ``public``.
    Starting from Symfony 3.3, you can also define them as ``private``.

That's it! Your controller - where you create the form - doesn't need to change
at all: Symfony is smart enough to load the ``TaskType`` from the container.

Read :ref:`form-field-service` for more information.
