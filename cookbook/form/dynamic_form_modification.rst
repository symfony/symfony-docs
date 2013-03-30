.. index::
   single: Form; Events

How to Dynamically Modify Forms Using Form Events
===================================================

Before jumping right into dynamic form generation, let's have a quick review
of what a bare form class looks like::

    // src/Acme/DemoBundle/Form/Type/ProductType.php
    namespace Acme\DemoBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('name');
            $builder->add('price');
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

Let's assume for a moment that this form utilizes an imaginary "Product" class
that has only two relevant properties ("name" and "price"). The form generated
from this class will look the exact same regardless if a new Product is being created
or if an existing product is being edited (e.g. a product fetched from the database).

Suppose now, that you don't want the user to be able to change the ``name`` value
once the object has been created. To do this, you can rely on Symfony's
:doc:`Event Dispatcher </components/event_dispatcher/introduction>`
system to analyze the data on the object and modify the form based on the
Product object's data. In this entry, you'll learn how to add this level of
flexibility to your forms.

.. _`cookbook-forms-event-subscriber`:

Adding An Event Subscriber To A Form Class
------------------------------------------

So, instead of directly adding that "name" widget via your ProductType form
class, let's delegate the responsibility of creating that particular field
to an Event Subscriber::

    // src/Acme/DemoBundle/Form/Type/ProductType.php
    namespace Acme\DemoBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Acme\DemoBundle\Form\EventListener\AddNameFieldSubscriber;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $subscriber = new AddNameFieldSubscriber($builder->getFormFactory());
            $builder->addEventSubscriber($subscriber);
            $builder->add('price');
        }

        public function getName()
        {
            return 'product';
        }
    }

The event subscriber is passed the FormFactory object in its constructor so
that your new subscriber is capable of creating the form widget once it is
notified of the dispatched event during form creation.

.. _`cookbook-forms-inside-subscriber-class`:

Inside the Event Subscriber Class
---------------------------------

The goal is to create a "name" field *only* if the underlying Product object
is new (e.g. hasn't been persisted to the database). Based on that, the subscriber
might look like the following::

    // src/Acme/DemoBundle/Form/EventListener/AddNameFieldSubscriber.php
    namespace Acme\DemoBundle\Form\EventListener;

    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\Form\FormFactoryInterface;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class AddNameFieldSubscriber implements EventSubscriberInterface
    {
        private $factory;

        public function __construct(FormFactoryInterface $factory)
        {
            $this->factory = $factory;
        }

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

            // During form creation setData() is called with null as an argument
            // by the FormBuilder constructor. You're only concerned with when
            // setData is called with an actual Entity object in it (whether new
            // or fetched with Doctrine). This if statement lets you skip right
            // over the null condition.
            if (null === $data) {
                return;
            }

            // check if the product object is "new"
            if (!$data->getId()) {
                $form->add($this->factory->createNamed('name', 'text'));
            }
        }
    }

.. caution::

    It is easy to misunderstand the purpose of the ``if (null === $data)`` segment
    of this event subscriber. To fully understand its role, you might consider
    also taking a look at the `Form class`_ and paying special attention to
    where setData() is called at the end of the constructor, as well as the
    setData() method itself.

The ``FormEvents::PRE_SET_DATA`` line actually resolves to the string ``form.pre_set_data``.
The `FormEvents class`_ serves an organizational purpose. It is a centralized location
in which you can find all of the various form events available.

While this example could have used the ``form.post_set_data``
event just as effectively, by using ``form.pre_set_data`` you guarantee that
the data being retrieved from the ``Event`` object has in no way been modified
by any other subscribers or listeners because ``form.pre_set_data`` is the
first form event dispatched.

.. note::

    You may view the full list of form events via the `FormEvents class`_,
    found in the form bundle.

How to Dynamically Generate Forms based on user data
====================================================

Sometimes you want a form to be generated dynamically based not only on data
from this form (see :doc:`Dynamic form generation</cookbook/dynamic_form_generation>`)
but also on something else. For example depending on the user currently using
the application. If you have a social website where a user can only message
people who are his friends on the website, then the current user doesn't need to
be included as a field of your form, but a "choice list" of whom to message
should only contain users that are the current user's friends.

Creating the form type
----------------------

Using an event listener, our form could be built like this::

    // src/Acme/DemoBundle/FormType/FriendMessageFormType.php
    namespace Acme\DemoBundle\FormType;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Security\Core\SecurityContext;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;
    use Acme\DemoBundle\FormSubscriber\UserListener;

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

The problem is now to get the current application user and create a choice field
that would contain only this user's friends.

Luckily it is pretty easy to inject a service inside of the form. This can be
done in the constructor.

.. code-block:: php

    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

.. note::

    You might wonder, now that we have access to the User (through) the security
    context, why don't we just use that inside of the buildForm function and
    still use a listener?
    This is because doing so in the buildForm method would result in the whole
    form type being modified and not only one form instance.

Customizing the form type
-------------------------

Now that we have all the basics in place, we can put everything in place and add
our listener::

    // src/Acme/DemoBundle/FormType/FriendMessageFormType.php
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
            $user = $this->securityContext->getToken()->getUser();
            $factory = $builder->getFormFactory();

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function(FormEvent $event) use($user, $factory){
                    $form = $event->getForm();
                    $userId = $user->getId();

                    $formOptions = array(
                        'class' => 'Acme\DemoBundle\Document\User',
                        'multiple' => false,
                        'expanded' => false,
                        'property' => 'fullName',
                        'query_builder' => function(DocumentRepository $dr) use ($userId) {
                            return $dr->createQueryBuilder()->field('friends.$id')->equals(new \MongoId($userId));
                        },
                    );

                    $form->add($factory->createNamed('friend', 'document', null, $formOptions));
                }
            );
        }

        public function getName()
        {
            return 'acme_friend_message';
        }

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
        }
    }

Using the form
--------------

Our form is now ready to use. We have two possible ways to use it inside of a
controller. Either by creating it everytime and remembering to pass the security
context, or by defining it as a service. This is the option we will show here.

To define your form as a service, you simply add the configuration to your
configuration.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        acme.form.friend_message:
            class: Acme\DemoBundle\FormType\FriendMessageType
            arguments: [@security.context]
            tags:
                - { name: form.type, alias: acme_friend_message}

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <services>
            <service id="acme.form.friend_message" class="Acme\DemoBundle\FormType\FriendMessageType">
                <argument type="service" id="security.context" />
                <tag name="form.type" alias="acme_friend_message" />
            </service>
        </services>

    .. code-block:: php

        // app/config/config.php
        $definition = new Definition('Acme\DemoBundle\FormType\FriendMessageType');
        $definition->addTag('form.type', array('alias' => 'acme_friend_message'));
        $container->setDefinition(
            'acme.form.friend_message',
            $definition,
            array('security.context')
        );

By adding the form as a service, we make sure that this form can now be used
simply from anywhere. If you need to add it to another form, you will just need
to use::

    $builder->add('message', 'acme_friend_message');

If you wish to create it from within a controller or any other service that has
access to the form factory, you then use::

    // src/AcmeDemoBundle/Controller/FriendMessageController.php
    public function friendMessageAction()
    {
        $form = $this->get('form.factory')->create('acme_friend_message');
        $form = $form->createView();

        return compact('form');
    }

Dynamic generation for submitted forms
======================================

An other case that can appear is that you want to customize the form specific to
the data that was submitted by the user. If we take as an example a registration
form for sports gatherings. Some events will allow you to specify your preferred
position on the field. This would be a choice field for example. However the
possible choices will depend on each sport. Football will have attack, defense,
goalkeeper etc... Baseball will have a pitcher but will not have goalkeeper. We
will need the correct options to be set in order for validation to pass.

The meetup is passed as an entity hidden field to the form. So we can access each
sport like this::

    // src/Acme/DemoBundle/FormType/SportMeetupType.php
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
                    $event->getData()->getSport()->getAvailablePositions();

                    // ... proceed with customizing the form based on available positions
                }
            );
        }
    }


While generating this kind of form to display it to the user for the first time,
we can just as previously, use a simple listener and all goes fine.

When considering form submission, things are usually a bit different because
subscribing to PRE_SET_DATA will only return us an empty ``SportMeetup`` object.
That object will then be populated with the data sent by the user when there is a
call to ``$form->bind($request)``.

On a form, we can usually listen to the following events::

 * ``PRE_SET_DATA``
 * ``POST_SET_DATA``
 * ``PRE_BIND``
 * ``BIND``
 * ``POST_BIND``

When listening to bind and post-bind, it's already "too late" to make changes to
the form. But pre-bind is fine. There is however a big difference in what
``$event->getData()`` will return for each of these events as pre-bind will return
an array instead of an object. This is the raw data submitted by the user.

This can be used to get the SportMeetup's id and retrieve it from the database,
given we have a reference to our object manager (if using doctrine). So we have
an event subscriber that listens to two different events, requires some
external services and customizes our form. In such a situation, it seems cleaner
to define this as a service rather than use closure like in the previous example.

Our subscriber would now look like::

    class RegistrationSportListener implements EventSubscriberInterface
    {
        /**
         * @var FormFactoryInterface
         */
        private $factory;

        /**
         * @var DocumentManager
         */
        private $om;

        /**
         * @param factory FormFactoryInterface
         */
        public function __construct(FormFactoryInterface $factory, ObjectManager $om)
        {
            $this->factory = $factory;
            $this->om = $om;
        }

        public static function getSubscribedEvents()
        {
            return [
                FormEvents::PRE_BIND => 'preBind',
                FormEvents::PRE_SET_DATA => 'preSetData',
            ];
        }

        /**
         * @param event DataEvent
         */
        public function preSetData(DataEvent $event)
        {
            $meetup = $event->getData()->getMeetup();

            // Before binding the form, the "meetup" will be null
            if (null === $meetup) {
                return;
            }

            $form = $event->getForm();
            $positions = $meetup->getSport()->getPostions();

            $this->customizeForm($form, $positions);
        }

        public function preBind(DataEvent $event)
        {
            $data = $event->getData();
            $id = $data['event'];
            $meetup = $this->om
                        ->getRepository('Acme\SportBundle\Document\Event')
                        ->find($id);
            if($meetup === null){
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

We can see that we need to listen on these two events and have different callbacks
only because in two different scenarios, the data that we can use is given in a
different format. Other than that, this class always performs exactly the same
things on a given form.

Now that we have this set up, we need to create our services:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        acme.form.sport_meetup:
            class: Acme\SportBundle\FormType\RegistrationType
            arguments: [@acme.form.meetup_registration_listener]
            tags:
                - { name: form.type, alias: acme_meetup_registration }
        acme.form.meetup_registration_listener
            class: Acme\SportBundle\Form\RegistrationSportListener
            arguments: [@form.factory, @doctrine]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <services>
            <service id="acme.form.sport_meetup" class="Acme\SportBundle\FormType\RegistrationType">
                <argument type="service" id="acme.form.meetup_registration_listener" />
                <tag name="form.type" alias="acme_meetup_registration" />
            </service>
            <service id="acme.form.meetup_registration_listener" class="Acme\SportBundle\Form\RegistrationSportListener">
                <argument type="service" id="form.factory" />
                <argument type="service" id="doctrine" />
            </service>
        </services>

    .. code-block:: php

        // app/config/config.php
        $definition = new Definition('Acme\SportBundle\FormType\RegistrationType');
        $definition->addTag('form.type', array('alias' => 'acme_meetup_registration'));
        $container->setDefinition(
            'acme.form.meetup_registration_listener',
            $definition,
            array('security.context')
        );
        $definition = new Definition('Acme\SportBundle\Form\RegistrationSportListener');
        $container->setDefinition(
            'acme.form.meetup_registration_listener',
            $definition,
            array('form.factory', 'doctrine')
        );

And this should tie everything together. We can now retrieve our form from the
controller, display it to a user, and validate it with the right choice options
set for every possible kind of sport that our users are registering for.

.. _`DataEvent`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Event/DataEvent.php
.. _`FormEvents class`: https://github.com/symfony/Form/blob/master/FormEvents.php
.. _`Form class`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Form.php
