.. _form-attributes:

How to Define Forms with PHP Attributes
=======================================

.. versionadded:: 8.2

    The ``#[AsFormType]`` and ``#[FormField]`` attributes were introduced in
    Symfony 8.2.

Many form types do nothing more than list the fields of a data class. For
those forms, you can skip the form type class and declare the form directly on
the class that holds the data, using the ``#[AsFormType]`` and ``#[FormField]``
attributes.

Defining the Form
-----------------

Add ``#[AsFormType]`` to the class and ``#[FormField]`` to each property that
must become a field of the form::

    // src/Dto/UserDto.php
    namespace App\Dto;

    use Symfony\Component\Form\Attribute\AsFormType;
    use Symfony\Component\Form\Attribute\FormField;
    use Symfony\Component\Form\Extension\Core\Type\EmailType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;

    #[AsFormType(options: ['label' => 'User'])]
    class UserDto
    {
        // the type is guessed, exactly as it is by FormBuilderInterface::add()
        #[FormField]
        public ?string $name = null;

        #[FormField(EmailType::class)]
        public ?string $email = null;

        #[FormField(TextareaType::class, ['label' => 'Bio'])]
        public ?string $bio = null;

        // the field is named "publicName" in the form and mapped back to
        // $internalName through the "property_path" option
        #[FormField(name: 'publicName')]
        public ?string $internalName = null;

        // properties without the #[FormField] attribute are not part of the form
        public ?\DateTimeImmutable $createdAt = null;
    }

The class name can then be used wherever a form type name is expected (see
:ref:`form-attributes-usage`).

Fields are added in the order of the properties. A property without
``#[FormField]`` is ignored by the form.

The form type is derived from the class when the service container is
compiled. Thanks to :ref:`autoconfiguration <services-autoconfigure>`, every
class carrying ``#[AsFormType]`` gets the ``form.data_class``
:ref:`resource tag <service-tags-resource-tags>`, so the class only needs to be
discovered by the service container. A class living in a directory excluded
from your service configuration is not seen, and using it as a form type fails
with an error asking you to make it discoverable.

Attribute Arguments
-------------------

``#[AsFormType]`` accepts a single argument:

``options`` (default: ``[]``)
    The default options of the form, as you would set them in the
    ``configureOptions()`` method of a form type class. The ``data_class``
    option is implied by the class itself and cannot be set here.

``#[FormField]`` accepts three arguments:

``type`` (default: ``null``)
    The fully-qualified class name of the field type. When it's ``null``, the
    type is :ref:`guessed <form-type-guessing>` from the property, as it is
    when you call ``$builder->add('name')`` without a type.

``options`` (default: ``[]``)
    The options of the field.

``name`` (default: ``null``)
    The name of the field in the form, which defaults to the property name.
    When it's set, the ``property_path`` option of the field points to the
    property, so it cannot be combined with an explicit ``property_path``
    option.

Allowed Option Values
~~~~~~~~~~~~~~~~~~~~~

Options are read when the container is compiled and stored in it. Their values
can only be scalars, ``null``, arrays, enums and references to services. A
closure or any other object is rejected at compile time. To pass a service,
for instance as a callable, use a
:class:`Symfony\\Component\\DependencyInjection\\Reference`::

    // src/Dto/PostDto.php
    namespace App\Dto;

    use App\Form\TagLabeler;
    use Symfony\Component\DependencyInjection\Reference;
    use Symfony\Component\Form\Attribute\AsFormType;
    use Symfony\Component\Form\Attribute\FormField;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

    #[AsFormType]
    class PostDto
    {
        #[FormField(ChoiceType::class, [
            'choices' => ['php', 'symfony'],
            'choice_label' => [new Reference(TagLabeler::class), 'label'],
            'multiple' => true,
        ])]
        public array $tags = [];
    }

Values are kept as they are written, so a ``%`` sign in an option (for
instance in a label) isn't mistaken for a container parameter.

Options that depend on the request or on other runtime data can't be
compiled: set them from a :ref:`type extension <form-attributes-type-extension>`
instead.

Errors Reported at Compile Time
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Because the attributes are read when the container is compiled, the following
mistakes break the compilation instead of failing on the first request that
uses the form:

* a field type that doesn't exist;
* two fields with the same name;
* the ``name`` argument combined with the ``property_path`` option;
* the ``data_class`` option set in ``#[AsFormType]``;
* an option value that can't be compiled, such as a closure or an object.

.. _form-attributes-usage:

Using the Form
--------------

In a controller, pass the class name to ``createForm()``::

    $form = $this->createForm(UserDto::class, $user);

The class can also be the type of a field in another form. In a class using the
attributes, add it as the type of a ``#[FormField]``, alone or as the entry type
of a collection::

    // src/Dto/CustomerDto.php
    namespace App\Dto;

    use Symfony\Component\Form\Attribute\AsFormType;
    use Symfony\Component\Form\Attribute\FormField;
    use Symfony\Component\Form\Extension\Core\Type\CollectionType;
    use Symfony\Component\Validator\Constraints as Assert;

    #[AsFormType]
    class CustomerDto
    {
        #[FormField]
        public ?string $name = null;

        #[FormField(AddressDto::class)]
        #[Assert\Valid]
        public ?AddressDto $address = null;

        #[FormField(CollectionType::class, [
            'entry_type' => AddressDto::class,
            'allow_add' => true,
        ])]
        public array $otherAddresses = [];
    }

In a regular form type class, use it like any other type::

    // src/Form/Type/OrderType.php
    namespace App\Form\Type;

    use App\Dto\CustomerDto;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;

    class OrderType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('reference')
                ->add('customer', CustomerDto::class)
            ;
        }
    }

The block prefix of the form is derived from the class name, ``user_dto`` for
the ``UserDto`` class, so :doc:`form themes </form/form_themes>` can target it
with blocks such as ``user_dto_widget``.

Inheritance
-----------

A data class can extend another class, and the fields of the parent class are
then part of the form. How they are added depends on whether the parent class
carries ``#[AsFormType]`` itself.

When the parent class carries ``#[AsFormType]``, it is a form type of its own.
The closest such ancestor becomes the parent type of the derived form type, so
its fields come first and its block prefix is also used when rendering the
form. The parent class can be abstract::

    // src/Dto/VehicleDto.php
    namespace App\Dto;

    use Symfony\Component\Form\Attribute\AsFormType;
    use Symfony\Component\Form\Attribute\FormField;

    #[AsFormType]
    abstract class VehicleDto
    {
        #[FormField]
        public ?string $name = null;
    }

A ``CarDto`` class extending it gets its own form, with the ``name`` field of
``VehicleDto`` followed by its own fields::

    // src/Dto/CarDto.php
    namespace App\Dto;

    use Symfony\Component\Form\Attribute\AsFormType;
    use Symfony\Component\Form\Attribute\FormField;
    use Symfony\Component\Form\Extension\Core\Type\IntegerType;

    // the form has two fields: "name", then "seats"
    #[AsFormType]
    class CarDto extends VehicleDto
    {
        #[FormField(IntegerType::class)]
        public ?int $seats = null;
    }

When the parent class doesn't carry ``#[AsFormType]``, its ``#[FormField]``
properties are read as if they were declared on the child class, including
those coming from traits and private ones (which, as in any form, need a getter
and a setter). Properties are read from the topmost
class down to the child class, and a property redeclared in a child class is
read from that child class::

    // src/Dto/PersonDto.php
    namespace App\Dto;

    use Symfony\Component\Form\Attribute\FormField;

    abstract class PersonDto
    {
        #[FormField]
        public ?string $name = null;
    }

The form derived from a ``SpeakerDto`` class extending it then includes the
``name`` field::

    // src/Dto/SpeakerDto.php
    namespace App\Dto;

    use Symfony\Component\Form\Attribute\AsFormType;
    use Symfony\Component\Form\Attribute\FormField;

    // the form has two fields: "name", then "bio"
    #[AsFormType]
    class SpeakerDto extends PersonDto
    {
        #[FormField]
        public ?string $bio = null;
    }

Validation
----------

The data class is validated like any other ``data_class`` of a form, so you can
add :doc:`validation constraints </validation>` next to the ``#[FormField]``
attributes::

    // src/Dto/AddressDto.php
    namespace App\Dto;

    use Symfony\Component\Form\Attribute\AsFormType;
    use Symfony\Component\Form\Attribute\FormField;
    use Symfony\Component\Validator\Constraints as Assert;

    #[AsFormType]
    class AddressDto
    {
        #[FormField]
        #[Assert\NotBlank]
        public ?string $street = null;

        #[FormField]
        #[Assert\NotBlank]
        public ?string $city = null;
    }

As with any embedded object, the constraints of a nested data class are only
checked when the property holding it has the
:doc:`Valid </reference/constraints/Valid>` constraint, as ``$address`` does
in the ``CustomerDto`` example above.

.. _form-attributes-type-extension:

Adding Behavior with Type Extensions
------------------------------------

The attributes are meant for forms that only describe fields. Anything
behavioral, such as :doc:`form events </form/events>`,
:doc:`data transformers </form/data_transformers>` or fields computed at
runtime, belongs in a
:doc:`form type extension </form/create_form_type_extension>` that targets the
data class. The extension is a regular service, so it keeps
full access to dependency injection::

    // src/Form/Extension/UserDtoExtension.php
    namespace App\Form\Extension;

    use App\Dto\UserDto;
    use Symfony\Component\Form\AbstractTypeExtension;
    use Symfony\Component\Form\FormBuilderInterface;

    class UserDtoExtension extends AbstractTypeExtension
    {
        public static function getExtendedTypes(): iterable
        {
            yield UserDto::class;
        }

        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder->add('agreeTerms', null, ['mapped' => false]);
        }
    }

Debugging the Forms
-------------------

The ``debug:form`` command lists the classes using ``#[AsFormType]`` in a
"Data class form types" section, and shows the options of one of them when you
pass its class name:

.. code-block:: terminal

    # lists all form types, including the data class form types
    $ php bin/console debug:form

    # shows the options of the form derived from the UserDto class
    $ php bin/console debug:form "App\Dto\UserDto"
