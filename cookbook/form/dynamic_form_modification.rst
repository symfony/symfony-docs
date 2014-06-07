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

If you wish to learn more about the basics behind form events, you can
take a look at the :doc:`Form Events </components/form/form_events>`
documentation.

.. _cookbook-form-events-underlying-data:

Customizing your Form based on the underlying Data
--------------------------------------------------

Before jumping right into dynamic form generation, hold on and recall what
a bare form class looks like::

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
:doc:`EventDispatcher </components/event_dispatcher/introduction>`
system to analyze the data on the object and modify the form based on the
Product object's data. In this entry, you'll learn how to add this level of
flexibility to your forms.

.. _`cookbook-forms-event-listener`:

Adding an Event Listener to a Form Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

So, instead of directly adding that ``name`` widget, the responsibility of
creating that particular field is delegated to an event listener::

    // src/Acme/DemoBundle/Form/Type/ProductType.php
    namespace Acme\DemoBundle\Form\Type;

    // ...
    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('price');

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                // ... adding the name field if needed
            });
        }

        // ...
    }


The goal is to create a ``name`` field *only* if the underlying ``Product``
object is new (e.g. hasn't been persisted to the database). Based on that,
the event listener might look like the following::

    // ...
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $product = $event->getData();
            $form = $event->getForm();

            // check if the Product object is "new"
            // If no data is passed to the form, the data is "null".
            // This should be considered a new "Product"
            if (!$product || null === $product->getId()) {
                $form->add('name', 'text');
            }
        });
    }

.. note::

    The ``FormEvents::PRE_SET_DATA`` line actually resolves to the string
    ``form.pre_set_data``. :class:`Symfony\\Component\\Form\\FormEvents`
    serves an organizational purpose. It is a centralized location in which
    you can find all of the various form events available. You can view the
    full list of form events via the
    :class:`Symfony\\Component\\Form\\FormEvents` class.

.. _`cookbook-forms-event-subscriber`:

Adding an Event Subscriber to a Form Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For better reusability or if there is some heavy logic in your event listener,
you can also move the logic for creating the ``name`` field to an
:ref:`event subscriber <event_dispatcher-using-event-subscribers>`::

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

Now the logic for creating the ``name`` field resides in it own subscriber
class::

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
            $product = $event->getData();
            $form = $event->getForm();

            if (!$product || null === $product->getId()) {
                $form->add('name', 'text');
            }
        }
    }


.. _cookbook-form-events-user-data:

How to Dynamically Generate Forms based on user Data
----------------------------------------------------

Sometimes you want a form to be generated dynamically based not only on data
from the form but also on something else - like some data from the current user.
Suppose you have a social website where a user can only message people marked
as friends on the website. In this case, a "choice list" of whom to message
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
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
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

Now that you have all the basics in place you can take advantage of the ``SecurityContext``
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

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($user) {
                    $form = $event->getForm();

                    $formOptions = array(
                        'class' => 'Acme\DemoBundle\Entity\User',
                        'property' => 'fullName',
                        'query_builder' => function (EntityRepository $er) use ($user) {
                            // build a custom query
                            // return $er->createQueryBuilder('u')->addOrderBy('fullName', 'DESC');

                            // or call a method on your repository that returns the query builder
                            // the $er is an instance of your UserRepository
                            // return $er->createOrderByFullNameQueryBuilder();
                        },
                    );

                    // create the field, this is similar the $builder->add()
                    // field name, field type, data, options
                    $form->add('friend', 'entity', $formOptions);
                }
            );
        }

        // ...
    }

.. note::

    The ``multiple`` and ``expanded`` form options will default to false
    because the type of the friend field is ``entity``.

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
                arguments: ["@security.context"]
                tags:
                    - { name: form.type, alias: acme_friend_message }

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

    use Symfony\Component\DependencyInjection\ContainerAware;

    class FriendMessageController extends ContainerAware
    {
        public function newAction(Request $request)
        {
            $form = $this->get('form.factory')->create('acme_friend_message');

            // ...
        }
    }

If you extend the ``Symfony\Bundle\FrameworkBundle\Controller\Controller`` class, you can simply call::

    $form = $this->createForm('acme_friend_message');

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
goalkeeper etc... Baseball will have a pitcher but will not have a goalkeeper. You
will need the correct options in order for validation to pass.

The meetup is passed as an entity field to the form. So we can access each
sport like this::

    // src/Acme/DemoBundle/Form/Type/SportMeetupType.php
    namespace Acme\DemoBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;
    // ...

    class SportMeetupType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('sport', 'entity', array(
                    'class'       => 'AcmeDemoBundle:Sport',
                    'empty_value' => '',
                ))
            ;

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $form = $event->getForm();

                    // this would be your entity, i.e. SportMeetup
                    $data = $event->getData();

                    $sport = $data->getSport();
                    $positions = null === $sport ? array() : $sport->getAvailablePositions();

                    $form->add('position', 'entity', array(
                        'class'       => 'AcmeDemoBundle:Position',
                        'empty_value' => '',
                        'choices'     => $positions,
                    ));
                }
            );
        }

        // ...
    }

When you're building this form to display to the user for the first time,
then this example works perfectly.

However, things get more difficult when you handle the form submission. This
is because the ``PRE_SET_DATA`` event tells us the data that you're starting
with (e.g. an empty ``SportMeetup`` object), *not* the submitted data.

On a form, we can usually listen to the following events:

* ``PRE_SET_DATA``
* ``POST_SET_DATA``
* ``PRE_SUBMIT``
* ``SUBMIT``
* ``POST_SUBMIT``

.. versionadded:: 2.3
    The events ``PRE_SUBMIT``, ``SUBMIT`` and ``POST_SUBMIT`` were introduced
    in Symfony 2.3. Before, they were named ``PRE_BIND``, ``BIND`` and ``POST_BIND``.

The key is to add a ``POST_SUBMIT`` listener to the field that your new field
depends on. If you add a ``POST_SUBMIT`` listener to a form child (e.g. ``sport``),
and add new children to the parent form, the Form component will detect the
new field automatically and map it to the submitted client data.

The type would now look like::

    // src/Acme/DemoBundle/Form/Type/SportMeetupType.php
    namespace Acme\DemoBundle\Form\Type;

    // ...
    use Symfony\Component\Form\FormInterface;
    use Acme\DemoBundle\Entity\Sport;

    class SportMeetupType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('sport', 'entity', array(
                    'class'       => 'AcmeDemoBundle:Sport',
                    'empty_value' => '',
                ));
            ;

            $formModifier = function (FormInterface $form, Sport $sport = null) {
                $positions = null === $sport ? array() : $sport->getAvailablePositions();

                $form->add('position', 'entity', array(
                    'class'       => 'AcmeDemoBundle:Position',
                    'empty_value' => '',
                    'choices'     => $positions,
                ));
            };

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($formModifier) {
                    // this would be your entity, i.e. SportMeetup
                    $data = $event->getData();

                    $formModifier($event->getForm(), $data->getSport());
                }
            );

            $builder->get('sport')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifier) {
                    // It's important here to fetch $event->getForm()->getData(), as
                    // $event->getData() will get you the client data (that is, the ID)
                    $sport = $event->getForm()->getData();

                    // since we've added the listener to the child, we'll have to pass on
                    // the parent to the callback functions!
                    $formModifier($event->getForm()->getParent(), $sport);
                }
            );
        }

        // ...
    }

You can see that you need to listen on these two events and have different
callbacks only because in two different scenarios, the data that you can use is
available in different events. Other than that, the listeners always perform
exactly the same things on a given form.

One piece that is still missing is the client-side updating of your form after
the sport is selected. This should be handled by making an AJAX call back to
your application. Assume that you have a sport meetup creation controller::

    // src/Acme/DemoBundle/Controller/MeetupController.php
    namespace Acme\DemoBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    use Acme\DemoBundle\Entity\SportMeetup;
    use Acme\DemoBundle\Form\Type\SportMeetupType;
    // ...

    class MeetupController extends Controller
    {
        public function createAction(Request $request)
        {
            $meetup = new SportMeetup();
            $form = $this->createForm(new SportMeetupType(), $meetup);
            $form->handleRequest($request);
            if ($form->isValid()) {
                // ... save the meetup, redirect etc.
            }

            return $this->render(
                'AcmeDemoBundle:Meetup:create.html.twig',
                array('form' => $form->createView())
            );
        }

        // ...
    }

The associated template uses some JavaScript to update the ``position`` form
field according to the current selection in the ``sport`` field:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/DemoBundle/Resources/views/Meetup/create.html.twig #}
        {{ form_start(form) }}
            {{ form_row(form.sport) }}    {# <select id="meetup_sport" ... #}
            {{ form_row(form.position) }} {# <select id="meetup_position" ... #}
            {# ... #}
        {{ form_end(form) }}

        <script>
        var $sport = $('#meetup_sport');
        // When sport gets selected ...
        $sport.change(function() {
          // ... retrieve the corresponding form.
          var $form = $(this).closest('form');
          // Simulate form data, but only include the selected sport value.
          var data = {};
          data[$sport.attr('name')] = $sport.val();
          // Submit data via AJAX to the form's action path.
          $.ajax({
            url : $form.attr('action'),
            type: $form.attr('method'),
            data : data,
            success: function(html) {
              // Replace current position field ...
              $('#meetup_position').replaceWith(
                // ... with the returned one from the AJAX response.
                $(html).find('#meetup_position')
              );
              // Position field now displays the appropriate positions.
            }
          });
        });
        </script>

    .. code-block:: html+php

        <!-- src/Acme/DemoBundle/Resources/views/Meetup/create.html.php -->
        <?php echo $view['form']->start($form) ?>
            <?php echo $view['form']->row($form['sport']) ?>    <!-- <select id="meetup_sport" ... -->
            <?php echo $view['form']->row($form['position']) ?> <!-- <select id="meetup_position" ... -->
            <!-- ... -->
        <?php echo $view['form']->end($form) ?>

        <script>
        var $sport = $('#meetup_sport');
        // When sport gets selected ...
        $sport.change(function() {
          // ... retrieve the corresponding form.
          var $form = $(this).closest('form');
          // Simulate form data, but only include the selected sport value.
          var data = {};
          data[$sport.attr('name')] = $sport.val();
          // Submit data via AJAX to the form's action path.
          $.ajax({
            url : $form.attr('action'),
            type: $form.attr('method'),
            data : data,
            success: function(html) {
              // Replace current position field ...
              $('#meetup_position').replaceWith(
                // ... with the returned one from the AJAX response.
                $(html).find('#meetup_position')
              );
              // Position field now displays the appropriate positions.
            }
          });
        });
        </script>

The major benefit of submitting the whole form to just extract the updated
``position`` field is that no additional server-side code is needed; all the
code from above to generate the submitted form can be reused.

.. _cookbook-dynamic-form-modification-suppressing-form-validation:

Suppressing Form Validation
---------------------------

To suppress form validation you can use the ``POST_SUBMIT`` event and prevent
the :class:`Symfony\\Component\\Form\\Extension\\Validator\\EventListener\\ValidationListener`
from being called.

The reason for needing to do this is that even if you set ``group_validation``
to ``false`` there  are still some integrity checks executed. For example
an uploaded file will still be checked to see if it is too large and the form
will still check to see if non-existing fields were submitted. To disable
all of this, use a listener::

    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvents;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function ($event) {
            $event->stopPropagation();
        }, 900); // Always set a higher priority than ValidationListener

        // ...
    }

.. caution::

    By doing this, you may accidentally disable something more than just form
    validation, since the ``POST_SUBMIT`` event may have other listeners.
