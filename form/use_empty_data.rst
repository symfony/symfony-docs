How to Configure empty Data for a Form Class
============================================

The ``empty_data`` option allows you to specify an empty data set for your
form class. This empty data set would be used if you submit your form, but
haven't called ``setData()`` on your form or passed in data when you created
your form. For example, in a controller::

    public function index(): Response
    {
        $blog = ...;

        // $blog is passed in as the data, so the empty_data
        // option is not needed
        $form = $this->createForm(BlogType::class, $blog);

        // no data is passed in, so empty_data is
        // used to get the "starting data"
        $form = $this->createForm(BlogType::class);
    }

By default, ``empty_data`` is set to ``null``. Or, if you have specified
a ``data_class`` option for your form class, it will default to a new instance
of that class. That instance will be created by calling the constructor
with no arguments, unless the constructor has required arguments or promoted
readonly properties (see :ref:`forms-empty-data-constructor`).

If you want to override this default behavior, there are two ways to do this:

* `Option 1: Instantiate a new Class`_
* `Option 2: Provide a Closure`_

If you didn't set the ``data_class`` option, you can pass the initial data as a
string or pass an array of strings (where the key matches the field name) when
the form type is compound.

Option 1: Instantiate a new Class
---------------------------------

One reason you might use this option is if you want to use a constructor
that takes arguments. Remember, the default ``data_class`` option calls
that constructor with no arguments::

    // src/Form/Type/BlogType.php
    namespace App\Form\Type;

    // ...
    use App\Entity\Blog;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class BlogType extends AbstractType
    {
        public function __construct(
            private object $someDependency,
        ) {
        }
        // ...

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'empty_data' => new Blog($this->someDependency),
            ]);
        }
    }

You can instantiate your class however you want. In this example, you pass
some dependency into the ``BlogType`` then use that to instantiate the ``Blog`` class.
The point is, you can set ``empty_data`` to the exact "new" object that you want to use.

.. tip::

    In order to pass arguments to the ``BlogType`` constructor, you'll need to
    :ref:`register the form as a service <service-container-creating-service>`
    and :doc:`tag it </service_container/tags>` with ``form.type``.
    If you're using the
    :ref:`default services.yaml configuration <service-container-services-load-example>`,
    this is already done for you.

.. _forms-empty-data-closure:

Option 2: Provide a Closure
---------------------------

Using a closure is the preferred method, since it will only create the object
if it is needed.

The closure must accept a ``FormInterface`` instance as the first argument::

    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    // ...

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'empty_data' => function (FormInterface $form): Blog {
                return new Blog($form->get('title')->getData());
            },
        ]);
    }

.. _forms-empty-data-constructor:

Using the Constructor of the Data Class
---------------------------------------

.. versionadded:: 8.2

    The mapping of the fields to the constructor of the data class was
    introduced in Symfony 8.2.

Immutable classes that use constructor property promotion with ``readonly``
properties require their data in the constructor and can't be modified once
created. When the constructor of the ``data_class`` is public and has required
arguments or promoted readonly properties, you don't need an ``empty_data``
option or a ``setter`` for each field: Symfony passes the data of the form
fields to the constructor, matching each argument with the field whose property
path is the name of that argument (e.g. ``email``, but not ``address.street``)::

    // src/Model/SignUp.php
    namespace App\Model;

    final readonly class SignUp
    {
        public function __construct(
            public string $email,
            public \DateTimeImmutable $birthDate,
            public bool $newsletter = false,
        ) {
        }
    }

    // src/Form/Type/SignUpType.php
    namespace App\Form\Type;

    use App\Model\SignUp;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\DateType;
    use Symfony\Component\Form\Extension\Core\Type\EmailType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class SignUpType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('email', EmailType::class)
                ->add('birthDate', DateType::class, ['widget' => 'single_text'])
            ;
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults(['data_class' => SignUp::class]);
        }
    }

This works for any required constructor argument, promoted or not. The
arguments that have no matching field get their default value. Symfony throws a
``LogicException`` when a required argument has no matching field. In that
case, add such a field or define the ``empty_data`` option. Disabled and
unsubmitted fields provide their initial data to the constructor. Symfony
doesn't map the fields to variadic arguments, nor the fields that define the
``setter`` option or whose ``mapped`` option is ``false``.

When the user changes a field mapped to a promoted readonly property, Symfony
creates the object again with the new value and reads the other arguments from
the previous instance. As a result, ``$form->getData()`` returns a new object
and the object passed to the form is left untouched. Symfony does this only
when the constructor promotes all the properties of the class (including the
ones declared in its parent classes), so that no state is lost. Otherwise, the
change can't be written to the readonly property and the form fails. The fields
mapped to promoted properties that aren't readonly give their value to the
constructor and are then written like any other property.

If a field is empty, a non-nullable argument gets its default value. When it
has none, the form reports an error on that field: with the Validator
component, the constraints of the property it is mapped to (e.g. ``NotBlank``
or ``NotNull``) are validated against the data of the field. When they pass,
the ``invalid_message`` of the field is used.
