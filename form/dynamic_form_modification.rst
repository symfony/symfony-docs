.. index::
   single: Form; Events

How to Dynamically Modify Forms Using Form Events
=================================================

Often times, a form can't be created statically. In this article, you'll learn
how to customize your form based on three common use-cases:

1) :ref:`form-events-underlying-data`

   Example: you have a "Product" form and need to modify/add/remove a field
    based on the data on the underlying Product being edited.

2) :ref:`form-events-user-data`

   Example: you create a "Friend Message" form and need to build a drop-down
   that contains only users that are friends with the *current* authenticated
   user.

3) :ref:`form-events-submitted-data`

   Example: on a registration form, you have a "country" field and a "state"
   field which should populate dynamically based on the value in the "country"
   field.

If you wish to learn more about the basics behind form events, you can
take a look at the :doc:`Form Events </form/events>` documentation.

.. _form-events-underlying-data:

Customizing your Form Based on the Underlying Data
--------------------------------------------------

Before starting with dynamic form generation, remember what
a bare form class looks like::

    // src/Form/Type/ProductType.php
    namespace App\Form\Type;

    use App\Entity\Product;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('name');
            $builder->add('price');
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'data_class' => Product::class,
            ]);
        }
    }

.. note::

    If this particular section of code isn't already familiar to you, you
    probably need to take a step back and first review the :doc:`Forms article </forms>`
    before proceeding.

Assume for a moment that this form utilizes an imaginary "Product" class
that has only two properties ("name" and "price"). The form generated from
this class will look the exact same regardless if a new Product is being created
or if an existing product is being edited (e.g. a product fetched from the database).

Suppose now, that you don't want the user to be able to change the ``name`` value
once the object has been created. To do this, you can rely on Symfony's
:doc:`EventDispatcher component </components/event_dispatcher>`
system to analyze the data on the object and modify the form based on the
Product object's data. In this article, you'll learn how to add this level of
flexibility to your forms.

Adding an Event Listener to a Form Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

So, instead of directly adding that ``name`` widget, the responsibility of
creating that particular field is delegated to an event listener::

    // src/Form/Type/ProductType.php
    namespace App\Form\Type;

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

            // checks if the Product object is "new"
            // If no data is passed to the form, the data is "null".
            // This should be considered a new "Product"
            if (!$product || null === $product->getId()) {
                $form->add('name', TextType::class);
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

Adding an Event Subscriber to a Form Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For better reusability or if there is some heavy logic in your event listener,
you can also move the logic for creating the ``name`` field to an
:ref:`event subscriber <event_dispatcher-using-event-subscribers>`::

    // src/Form/EventListener/AddNameFieldSubscriber.php
    namespace App\Form\EventListener;

    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Form\Extension\Core\Type\TextType;

    class AddNameFieldSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            // Tells the dispatcher that you want to listen on the form.pre_set_data
            // event and that the preSetData method should be called.
            return [FormEvents::PRE_SET_DATA => 'preSetData'];
        }

        public function preSetData(FormEvent $event)
        {
            $product = $event->getData();
            $form = $event->getForm();

            if (!$product || null === $product->getId()) {
                $form->add('name', TextType::class);
            }
        }
    }

Great! Now use that in your form class::

    // src/Form/Type/ProductType.php
    namespace App\Form\Type;

    // ...
    use App\Form\EventListener\AddNameFieldSubscriber;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('price');

            $builder->addEventSubscriber(new AddNameFieldSubscriber());
        }

        // ...
    }

.. _form-events-user-data:

How to dynamically Generate Forms Based on user Data
----------------------------------------------------

Sometimes you want a form to be generated dynamically based not only on data
from the form but also on something else - like some data from the current user.
Suppose you have a social website where a user can only message people marked
as friends on the website. In this case, a "choice list" of whom to message
should only contain users that are the current user's friends.

Creating the Form Type
~~~~~~~~~~~~~~~~~~~~~~

Using an event listener, your form might look like this::

    // src/Form/Type/FriendMessageFormType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;

    class FriendMessageFormType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('subject', TextType::class)
                ->add('body', TextareaType::class)
            ;
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                // ... add a choice list of friends of the current application user
            });
        }
    }

The problem is now to get the current user and create a choice field that
contains only this user's friends. This can be done injecting the ``Security``
service into the form type so you can get the current user object::

    use Symfony\Component\Security\Core\Security;
    // ...

    class FriendMessageFormType extends AbstractType
    {
        private $security;

        public function __construct(Security $security)
        {
            $this->security = $security;
        }

        // ....
    }

Customizing the Form Type
~~~~~~~~~~~~~~~~~~~~~~~~~

Now that you have all the basics in place you can use the features of the
security helper to fill in the listener logic::

    // src/Form/Type/FriendMessageFormType.php
    use App\Entity\User;
    use Doctrine\ORM\EntityRepository;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\Security\Core\Security;
    // ...

    class FriendMessageFormType extends AbstractType
    {
        private $security;

        public function __construct(Security $security)
        {
            $this->security = $security;
        }

        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('subject', TextType::class)
                ->add('body', TextareaType::class)
            ;

            // grab the user, do a quick sanity check that one exists
            $user = $this->security->getUser();
            if (!$user) {
                throw new \LogicException(
                    'The FriendMessageFormType cannot be used without an authenticated user!'
                );
            }

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
                if (null !== $event->getData()->getFriend()) {
                    // we don't need to add the friend field because
                    // the message will be addressed to a fixed friend
                    return;
                }

                $form = $event->getForm();

                $formOptions = [
                    'class' => User::class,
                    'choice_label' => 'fullName',
                    'query_builder' => function (UserRepository $userRepository) use ($user) {
                        // call a method on your repository that returns the query builder
                        // return $userRepository->createFriendsQueryBuilder($user);
                    },
                ];

                // create the field, this is similar the $builder->add()
                // field name, field type, field options
                $form->add('friend', EntityType::class, $formOptions);
            });
        }

        // ...
    }

.. note::

    You might wonder, now that you have access to the ``User`` object, why not
    just use it directly in ``buildForm()`` and omit the event listener? This is
    because doing so in the ``buildForm()`` method would result in the whole
    form type being modified and not just this one form instance. This may not
    usually be a problem, but technically a single form type could be used on a
    single request to create many forms or fields.

Using the Form
~~~~~~~~~~~~~~

If you're using :ref:`autowire <services-autowire>` and
:ref:`autoconfigure <services-autoconfigure>`, your form is ready to be used!
Otherwise, see :doc:`/form/form_dependencies` to learn how to register your form
type as a service.

In a controller, create the form like normal::

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class FriendMessageController extends AbstractController
    {
        public function new(Request $request)
        {
            $form = $this->createForm(FriendMessageFormType::class);

            // ...
        }
    }

You can also  embed the form type into another form::

    // inside some other "form type" class
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('message', FriendMessageFormType::class);
    }

.. _form-events-submitted-data:

Dynamic Generation for Submitted Forms
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

    // src/Form/Type/SportMeetupType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;
    // ...

    class SportMeetupType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('sport', EntityType::class, [
                    'class'       => 'App\Entity\Sport',
                    'placeholder' => '',
                ])
            ;

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $form = $event->getForm();

                    // this would be your entity, i.e. SportMeetup
                    $data = $event->getData();

                    $sport = $data->getSport();
                    $positions = null === $sport ? [] : $sport->getAvailablePositions();

                    $form->add('position', EntityType::class, [
                        'class' => 'App\Entity\Position',
                        'placeholder' => '',
                        'choices' => $positions,
                    ]);
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

The key is to add a ``POST_SUBMIT`` listener to the field that your new field
depends on. If you add a ``POST_SUBMIT`` listener to a form child (e.g. ``sport``),
and add new children to the parent form, the Form component will detect the
new field automatically and map it to the submitted client data.

The type would now look like::

    // src/Form/Type/SportMeetupType.php
    namespace App\Form\Type;

    use App\Entity\Sport;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;
    // ...

    class SportMeetupType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('sport', EntityType::class, [
                    'class'       => 'App\Entity\Sport',
                    'placeholder' => '',
                ]);
            ;

            $formModifier = function (FormInterface $form, Sport $sport = null) {
                $positions = null === $sport ? [] : $sport->getAvailablePositions();

                $form->add('position', EntityType::class, [
                    'class' => 'App\Entity\Position',
                    'placeholder' => '',
                    'choices' => $positions,
                ]);
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

.. tip::

    The ``FormEvents::POST_SUBMIT`` event does not allow to modify the form
    the listener is bound to, but it allows to modify its parent.

One piece that is still missing is the client-side updating of your form after
the sport is selected. This should be handled by making an AJAX call back to
your application. Assume that you have a sport meetup creation controller::

    // src/Controller/MeetupController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use App\Entity\SportMeetup;
    use App\Form\Type\SportMeetupType;
    // ...

    class MeetupController extends AbstractController
    {
        public function create(Request $request)
        {
            $meetup = new SportMeetup();
            $form = $this->createForm(SportMeetupType::class, $meetup);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // ... save the meetup, redirect etc.
            }

            return $this->render(
                'meetup/create.html.twig',
                ['form' => $form->createView()]
            );
        }

        // ...
    }

The associated template uses some JavaScript to update the ``position`` form
field according to the current selection in the ``sport`` field:

.. code-block:: html+twig

    {# templates/meetup/create.html.twig #}
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

The major benefit of submitting the whole form to just extract the updated
``position`` field is that no additional server-side code is needed; all the
code from above to generate the submitted form can be reused.
