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
with no arguments or, if the constructor has required arguments or promoted
readonly properties, by passing it the data of the form fields (see
:ref:`forms-empty-data-constructor`).

If you want to override this default behavior, there are two ways to do this:

* `Option 1: Instantiate a new Class`_
* `Option 2: Provide a Closure`_

If you didn't set the ``data_class`` option, you can pass the initial data as a
string or pass an array of strings (where the key matches the field name) when
the form type is compound.

Option 1: Instantiate a new Class
---------------------------------

One reason you might use this option is if you want to use a constructor
that takes arguments that don't come from the form fields (e.g. a service)::

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

Immutable classes, such as the ones that use promoted ``readonly`` properties,
receive all their data in the constructor and can't be modified after creating
them. If the constructor of the ``data_class`` is public and has required
arguments or promoted readonly properties, you don't need to define the
``empty_data`` option or the ``setter`` option of each field. Symfony passes
the data of the form fields to the constructor, matching each argument with the
field whose property path is the name of that argument (e.g. ``email``, but not
``address.street``)::

    // src/Model/SignUp.php
    namespace App\Model;

    final readonly class SignUp
    {
        public function __construct(
            public string $email,
            public \DateTimeImmutable $birthDate,
            // the form doesn't define a 'newsletter' field, so the
            // default value of this argument is used
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

This works for any required constructor argument, promoted or not. Arguments
without a matching field get their default value. If a required argument has
no matching field, the form fails; add that field or define the ``empty_data``
option. Disabled and unsubmitted fields pass their initial data to the
constructor. Symfony doesn't map fields to variadic arguments, nor fields that
define the ``setter`` option or set the ``mapped`` option to ``false``.

When editing an existing object, if the user changes a field mapped to a
promoted readonly property, Symfony creates a new object with the new value and
reads the other arguments from the previous object. As a result,
``$form->getData()`` returns a new object and the original object doesn't
change. Symfony only does this when the constructor promotes all the properties
of the class (including the ones declared in its parent classes), so no data is
lost. Otherwise, the form fails because it can't write the new value into the
readonly property. Fields mapped to promoted properties that aren't readonly
pass their value to the constructor and are then written like any other
property.

If the submitted value of a field is empty and the argument isn't nullable,
Symfony uses the default value of the argument. If the argument has no default
value, the form adds an error to that field. When the Validator component is
installed, the constraints of the mapped property (e.g. ``NotBlank`` or
``NotNull``) are validated against the field data first. If they pass, the
``invalid_message`` of the field is displayed.
