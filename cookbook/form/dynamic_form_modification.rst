.. index::
   single: Form; Events

How to Dynamically Modify Forms Using Form Events
=================================================

Often times, a form can't be created statically. In this entry, you'll learn
how to customize your form based on three common use-cases:

1) :ref:`cookbook-form-events-underlying-data`

Example: you have a "Product" form and need to modify/add/remove a field
based on the data on the underlying Product being edited.

2) :ref:`cookbook-form-events-user-data`

Example: you create a "Friend Message" form and need to build a drop-down
that contains only users that are friends with the *current* authenticated
user.

3) :ref:`cookbook-form-events-submitted-data`

Example: on a registration form, you have a "country" field and a "state"
field which should populate dynamically based on the value in the "country"
field.

.. _cookbook-form-events-underlying-data:

Customizing your Form based on the underlying Data
--------------------------------------------------

Before jumping right into dynamic form generation, let's have a quick review
of what a bare form class looks like::

    // src/Acme/DemoBundle/Form/Type/ProductType.php
    namespace Acme\DemoBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('name');
            $builder->add('price');
        }

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'Acme\DemoBundle\Entity\Product'
            ));
        }

        public function getName()
        {
            return 'product';
        }
    }

.. note::

    If this particular section of code isn't already familiar to you, you
    probably need to take a step back and first review the :doc:`Forms chapter </book/forms>`
    before proceeding.

Assume for a moment that this form utilizes an imaginary "Product" class
that has only two properties ("name" and "price"). The form generated from
this class will look the exact same regardless if a new Product is being created
or if an existing product is being edited (e.g. a product fetched from the database).

Suppose now, that you don't want the user to be able to change the ``name`` value
once the object has been created. To do this, you can rely on Symfony's
:doc:`Event Dispatcher </components/event_dispatcher/introduction>`
system to analyze the data on the object and modify the form based on the
Product object's data. In this entry, you'll learn how to add this level of
flexibility to your forms.

.. _`cookbook-forms-event-subscriber`:

Adding An Event Subscriber To A Form Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

So, instead of directly adding that "name" widget via your ProductType form
class, let's delegate the responsibility of creating that particular field
to an Event Subscriber::

    // src/Acme/DemoBundle/Form/Type/ProductType.php
    namespace Acme\DemoBundle\Form\Type;

    // ...
    use Acme\DemoBundle\Form\EventListener\AddNameFieldSubscriber;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('price');

            $builder->addEventSubscriber(new AddNameFieldSubscriber());
        }

        // ...
    }

.. _`cookbook-forms-inside-subscriber-class`:

Inside the Event Subscriber Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The goal is to create a "name" field *only* if the underlying Product object
is new (e.g. hasn't been persisted to the database). Based on that, the subscriber
might look like the following:

.. versionadded:: 2.2
    The ability to pass a string into :method:`FormInterface::add<Symfony\\Component\\Form\\FormInterface::add>`
    was added in Symfony 2.2.

.. code-block:: php

    // src/Acme/DemoBundle/Form/EventListener/AddNameFieldSubscriber.php
    namespace Acme\DemoBundle\Form\EventListener;

    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class AddNameFieldSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            // Tells the dispatcher that you want to listen on the form.pre_set_data
            // event and that the preSetData method should be called.
            return array(FormEvents::PRE_SET_DATA => 'preSetData');
        }

        public function preSetData(FormEvent $event)
        {
            $data = $event->getData();
            $form = $event->getForm();

            // check if the product object is "new"
            // If you didn't pass any data to the form, the data is "null".
            // This should be considered a new "Product"
            if (!$data || !$data->getId()) {
                $form->add('name', 'text');
            }
        }
    }

.. tip::

    The ``FormEvents::PRE_SET_DATA`` line actually resolves to the string
    ``form.pre_set_data``. :class:`Symfony\\Component\\Form\\FormEvents` serves
    an organizational purpose. It is a centralized location in which you can
    find all of the various form events available.

.. note::

    You can view the full list of form events via the :class:`Symfony\\Component\\Form\\FormEvents`
    class.

.. _cookbook-form-events-user-data:

How to Dynamically Generate Forms based on user Data
----------------------------------------------------

Sometimes you want a form to be generated dynamically based not only on data
from the form but also on something else - like some data from the current user.
Suppose you have a social website where a user can only message people who
are his friends on the website. In this case, a "choice list" of whom to message
should only contain users that are the current user's friends.

Creating the Form Type
~~~~~~~~~~~~~~~~~~~~~~

Using an event listener, your form might look like this::

    // src/Acme/DemoBundle/Form/Type/FriendMessageFormType.php
    namespace Acme\DemoBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Security\Core\SecurityContext;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class FriendMessageFormType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('subject', 'text')
                ->add('body', 'textarea')
            ;
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
                // ... add a choice list of friends of the current application user
            });
        }

        public function getName()
        {
            return 'acme_friend_message';
        }

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
        }
    }

The problem is now to get the current user and create a choice field that
contains only this user's friends.

Luckily it is pretty easy to inject a service inside of the form. This can be
done in the constructor::

    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

.. note::

    You might wonder, now that you have access to the User (through the security
    context), why not just use it directly in ``buildForm`` and omit the
    event listener? This is because doing so in the ``buildForm`` method
    would result in the whole form type being modified and not just this
    one form instance. This may not usually be a problem, but technically
    a single form type could be used on a single request to create many forms
    or fields.

Customizing the Form Type
~~~~~~~~~~~~~~~~~~~~~~~~~

Now that you have all the basics in place you an take advantage of the ``securityContext``
and fill in the listener logic::

    // src/Acme/DemoBundle/FormType/FriendMessageFormType.php

    use Symfony\Component\Security\Core\SecurityContext;
    use Doctrine\ORM\EntityRepository;
    // ...

    class FriendMessageFormType extends AbstractType
    {
        private $securityContext;

        public function __construct(SecurityContext $securityContext)
        {
            $this->securityContext = $securityContext;
        }

        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('subject', 'text')
                ->add('body', 'textarea')
            ;

            // grab the user, do a quick sanity check that one exists
            $user = $this->securityContext->getToken()->getUser();
            if (!$user) {
                throw new \LogicException(
                    'The FriendMessageFormType cannot be used without an authenticated user!'
                );
            }

            $factory = $builder->getFormFactory();

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function(FormEvent $event) use($user, $factory){
                    $form = $event->getForm();

                    $formOptions = array(
                        'class' => 'Acme\DemoBundle\Entity\User',
                        'multiple' => false,
                        'expanded' => false,
                        'property' => 'fullName',
                        'query_builder' => function(EntityRepository $er) use ($user) {
                            // build a custom query, or call a method on your repository (even better!)
                        },
                    );

                    // create the field, this is similar the $builder->add()
                    // field name, field type, data, options
                    $form->add($factory->createNamed('friend', 'entity', null, $formOptions));
                }
            );
        }

        // ...
    }

Using the Form
~~~~~~~~~~~~~~

Our form is now ready to use and there are two possible ways to use it inside
of a controller:

a) create it manually and remember to pass the security context to it;

or

b) define it as a service.

a) Creating the Form manually
.............................

This is very simple, and is probably the better approach unless you're using
your new form type in many places or embedding it into other forms::

    class FriendMessageController extends Controller
    {
        public function newAction(Request $request)
        {
            $securityContext = $this->container->get('security.context');
            $form = $this->createForm(
                new FriendMessageFormType($securityContext)
            );

            // ...
        }
    }

b) Defining the Form as a Service
.................................

To define your form as a service, just create a normal service and then tag
it with :ref:`dic-tags-form-type`.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            acme.form.friend_message:
                class: Acme\DemoBundle\Form\Type\FriendMessageFormType
                arguments: [@security.context]
                tags:
                    -
                        name: form.type
                        alias: acme_friend_message

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <services>
            <service id="acme.form.friend_message" class="Acme\DemoBundle\Form\Type\FriendMessageFormType">
                <argument type="service" id="security.context" />
                <tag name="form.type" alias="acme_friend_message" />
            </service>
        </services>

    .. code-block:: php

        // app/config/config.php
        $definition = new Definition('Acme\DemoBundle\Form\Type\FriendMessageFormType');
        $definition->addTag('form.type', array('alias' => 'acme_friend_message'));
        $container->setDefinition(
            'acme.form.friend_message',
            $definition,
            array('security.context')
        );

If you wish to create it from within a controller or any other service that has
access to the form factory, you then use::

    class FriendMessageController extends Controller
    {
        public function newAction(Request $request)
        {
            $form = $this->createForm('acme_friend_message');

            // ...
        }
    }

You can also easily embed the form type into another form::

    // inside some other "form type" class
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('message', 'acme_friend_message');
    }

.. _cookbook-form-events-submitted-data:

Dynamic generation for submitted Forms
--------------------------------------

Another case that can appear is that you want to customize the form specific to
the data that was submitted by the user. For example, imagine you have a registration
form for sports gatherings. Some events will allow you to specify your preferred
position on the field. This would be a ``choice`` field for example. However the
possible choices will depend on each sport. Football will have attack, defense,
goalkeeper etc... Baseball will have a pitcher but will not have goalkeeper. You
will need the correct options to be set in order for validation to pass.

The meetup is passed as an entity hidden field to the form. So we can access each
sport like this::

    // src/Acme/DemoBundle/Form/Type/SportMeetupType.php
    class SportMeetupType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('number_of_people', 'text')
                ->add('discount_coupon', 'text')
            ;
            $factory = $builder->getFormFactory();

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function(FormEvent $event) use($user, $factory){
                    $form = $event->getForm();

                    // this would be your entity, i.e. SportMeetup
                    $data = $event->getData();

                    $positions = $data->getSport()->getAvailablePositions();

                    // ... proceed with customizing the form based on available positions
                }
            );
        }
    }

When you're building this form to display to the user for the first time,
then this example works perfectly.

However, things get more difficult when you handle the form submission. This
is be cause the ``PRE_SET_DATA`` event tells us the data that you're starting
with (e.g. an empty ``SportMeetup`` object), *not* the submitted data.

On a form, we can usually listen to the following events:

* ``PRE_SET_DATA``
* ``POST_SET_DATA``
* ``PRE_SUBMIT``
* ``SUBMIT``
* ``POST_SUBMIT``

.. versionadded:: 2.3
    The events ``PRE_SUBMIT``, ``SUBMIT`` and ``POST_SUBMIT`` were added in
    Symfony 2.3. Before, they were named ``PRE_BIND``, ``BIND`` and ``POST_BIND``.

When listening to ``SUBMIT`` and ``POST_SUBMIT``, it's already "too late" to make
changes to the form. Fortunately, ``PRE_SUBMIT`` is perfect for this. There
is, however, a big difference in what ``$event->getData()`` returns for each
of these events. Specifically, in ``PRE_SUBMIT``, ``$event->getData()`` returns
the raw data submitted by the user.

This can be used to get the ``SportMeetup`` id and retrieve it from the database,
given you have a reference to the object manager (if using doctrine). In
the end, you have an event subscriber that listens to two different events,
requires some external services and customizes the form. In such a situation,
it's probably better to define this as a service rather than using an anonymous
function as the event listener callback.

The subscriber would now look like::

    // src/Acme/DemoBundle/Form/EventListener/RegistrationSportListener.php
    namespace Acme\DemoBundle\Form\EventListener;

    use Symfony\Component\Form\FormFactoryInterface;
    use Doctrine\ORM\EntityManager;
    use Symfony\Component\Form\FormEvent;

    class RegistrationSportListener implements EventSubscriberInterface
    {
        /**
         * @var FormFactoryInterface
         */
        private $factory;

        /**
         * @var EntityManager
         */
        private $em;

        /**
         * @param factory FormFactoryInterface
         */
        public function __construct(FormFactoryInterface $factory, EntityManager $em)
        {
            $this->factory = $factory;
            $this->em = $em;
        }

        public static function getSubscribedEvents()
        {
            return array(
                FormEvents::PRE_SUBMIT => 'preSubmit',
                FormEvents::PRE_SET_DATA => 'preSetData',
            );
        }

        /**
         * @param event FormEvent
         */
        public function preSetData(FormEvent $event)
        {
            $meetup = $event->getData()->getMeetup();

            // Before SUBMITing the form, the "meetup" will be null
            if (null === $meetup) {
                return;
            }

            $form = $event->getForm();
            $positions = $meetup->getSport()->getPositions();

            $this->customizeForm($form, $positions);
        }

        public function preSubmit(FormEvent $event)
        {
            $data = $event->getData();
            $id = $data['event'];
            $meetup = $this->em
                ->getRepository('AcmeDemoBundle:SportMeetup')
                ->find($id);

            if ($meetup === null) {
                $msg = 'The event %s could not be found for you registration';
                throw new \Exception(sprintf($msg, $id));
            }
            $form = $event->getForm();
            $positions = $meetup->getSport()->getPositions();

            $this->customizeForm($form, $positions);
        }

        protected function customizeForm($form, $positions)
        {
            // ... customize the form according to the positions
        }
    }

You can see that you need to listen on these two events and have different callbacks
only because in two different scenarios, the data that you can use is given in a
different format. Other than that, this class always performs exactly the same
things on a given form.

Now that you have that setup, register your form and the listener as services:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        acme.form.sport_meetup:
            class: Acme\SportBundle\Form\Type\SportMeetupType
            arguments: [@acme.form.meetup_registration_listener]
            tags:
                - { name: form.type, alias: acme_meetup_registration }
        acme.form.meetup_registration_listener
            class: Acme\SportBundle\Form\EventListener\RegistrationSportListener
            arguments: [@form.factory, @doctrine.orm.entity_manager]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <services>
            <service id="acme.form.sport_meetup" class="Acme\SportBundle\FormType\SportMeetupType">
                <argument type="service" id="acme.form.meetup_registration_listener" />
                <tag name="form.type" alias="acme_meetup_registration" />
            </service>
            <service id="acme.form.meetup_registration_listener" class="Acme\SportBundle\Form\EventListener\RegistrationSportListener">
                <argument type="service" id="form.factory" />
                <argument type="service" id="doctrine.orm.entity_manager" />
            </service>
        </services>

    .. code-block:: php

        // app/config/config.php
        $definition = new Definition('Acme\SportBundle\Form\Type\SportMeetupType');
        $definition->addTag('form.type', array('alias' => 'acme_meetup_registration'));
        $container->setDefinition(
            'acme.form.meetup_registration_listener',
            $definition,
            array('security.context')
        );
        $definition = new Definition('Acme\SportBundle\Form\EventListener\RegistrationSportListener');
        $container->setDefinition(
            'acme.form.meetup_registration_listener',
            $definition,
            array('form.factory', 'doctrine.orm.entity_manager')
        );

In this setup, the ``RegistrationSportListener`` will be a constructor argument
to ``SportMeetupType``. You can then register it as an event subscriber on
your form::

    private $registrationSportListener;

    public function __construct(RegistrationSportListener $registrationSportListener)
    {
        $this->registrationSportListener = $registrationSportListener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...
        $builder->addEventSubscriber($this->registrationSportListener);
    }

And this should tie everything together. You can now retrieve your form from the
controller, display it to a user, and validate it with the right choice options
set for every possible kind of sport that our users are registering for.

One piece that may still be missing is the client-side updating of your form
after the sport is selected. This should be handled by making an AJAX call
back to your application. In that controller, you can submit your form, but
instead of processing it, simply use the submitted form to render the updated
fields. The response from the AJAX call can then be used to update the view.
