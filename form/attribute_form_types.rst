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

    // the argument defines the default options of the form
    #[AsFormType(['label' => 'User'])]
    class UserDto
    {
        // the field type is guessed, as when calling $builder->add('name')
        #[FormField]
        public ?string $name = null;

        #[FormField(EmailType::class)]
        public ?string $email = null;

        #[FormField(TextareaType::class, ['label' => 'Bio'])]
        public ?string $bio = null;

        // the field is named "publicName" in the form and mapped back to
        // $internalName through the "property_path" option
        #[FormField(null, [], 'publicName')]
        public ?string $internalName = null;

        // properties without the #[FormField] attribute are not part of the form
        public ?\DateTimeImmutable $createdAt = null;
    }

Fields are added in the same order as the properties. You can then use the
class name wherever a form type name is expected (see
:ref:`form-attributes-usage`).

The form type is created from the class when the service container is
compiled. Thanks to :ref:`autoconfiguration <services-autoconfigure>`, every
class with the ``#[AsFormType]`` attribute gets the ``form.data_class``
:ref:`resource tag <service-tags-resource-tags>`. This means that the class
must be in a directory loaded by your service configuration; classes in
excluded directories can't be used as form types.

Attribute Arguments
-------------------

``#[AsFormType]`` accepts a single argument:

``options`` (default: ``[]``)
    The default options of the form, as you would set them in the
    ``configureOptions()`` method of a form type class. The ``data_class``
    option is implied by the class itself and can't be set here.

``#[FormField]`` accepts three arguments:

``type`` (default: ``null``)
    The fully-qualified class name of the field type. When it's ``null``, the
    type is :ref:`guessed <form-type-guessing>` from the property.

``options`` (default: ``[]``)
    The options of the field.

``name`` (default: ``null``)
    The name of the field in the form, which defaults to the property name.
    When it's set, the ``property_path`` option of the field points to the
    property, so you can't also define the ``property_path`` option.

Allowed Option Values
~~~~~~~~~~~~~~~~~~~~~

Options are read when the container is compiled and stored in it, so their
values can only be scalars, ``null``, arrays, enums and service references.
Closures and other objects aren't allowed. To pass a service (for example, as
part of a callable) use a
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
            // the callable is the label() method of the TagLabeler service
            'choice_label' => [new Reference(TagLabeler::class), 'label'],
            'multiple' => true,
        ])]
        public array $tags = [];
    }

Values are kept as written, so a ``%`` character in an option (e.g. in a
label) isn't considered a container parameter.

Options that depend on the request or on other runtime data can't be defined
in the attributes. Set them in a
:ref:`form type extension <form-attributes-type-extension>` instead.

Since attributes are read when the container is compiled, mistakes such as
using a field type that doesn't exist or defining two fields with the same
name make the compilation fail, instead of failing on the first request that
uses the form.

.. _form-attributes-usage:

Using the Form
--------------

In a controller, pass the class name to ``createForm()``::

    $form = $this->createForm(UserDto::class, $user);

The class can also be the type of a field in another form. In a class using
the attributes, use it as the type of a ``#[FormField]``, alone or as the entry
type of a collection::

    // src/Dto/CustomerDto.php
    namespace App\Dto;

    use Symfony\Component\Form\Attribute\AsFormType;
    use Symfony\Component\Form\Attribute\FormField;
    use Symfony\Component\Form\Extension\Core\Type\CollectionType;

    #[AsFormType]
    class CustomerDto
    {
        #[FormField]
        public ?string $name = null;

        #[FormField(AddressDto::class)]
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

The block prefix of the form is created from the class name (e.g. ``user_dto``
for the ``UserDto`` class), so :doc:`form themes </form/form_themes>` can
target it with blocks such as ``user_dto_widget``.

Inheritance
-----------

When a data class extends another class, the fields of the parent class are
also part of the form. How they are added depends on whether the parent class
has the ``#[AsFormType]`` attribute.

If the parent class has the ``#[AsFormType]`` attribute, it's a form type of
its own. The closest ancestor with this attribute becomes the parent type of
the form, so its fields are added first and its block prefix is also used when
rendering the form. The parent class can be abstract::

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

    // src/Dto/CarDto.php
    namespace App\Dto;

    use Symfony\Component\Form\Attribute\AsFormType;
    use Symfony\Component\Form\Attribute\FormField;
    use Symfony\Component\Form\Extension\Core\Type\IntegerType;

    // the form has two fields: "name" (from VehicleDto), then "seats"
    #[AsFormType]
    class CarDto extends VehicleDto
    {
        #[FormField(IntegerType::class)]
        public ?int $seats = null;
    }

If the parent class doesn't have the ``#[AsFormType]`` attribute, its
``#[FormField]`` properties are read as if they were declared in the child
class. This includes properties defined in traits and private properties
(which, as in any form, need a getter and a setter). Properties are read from
the topmost class down to the child class, and a property redeclared in a child
class is read from that child class::

    // src/Dto/PersonDto.php
    namespace App\Dto;

    use Symfony\Component\Form\Attribute\FormField;

    abstract class PersonDto
    {
        #[FormField]
        public ?string $name = null;
    }

    // src/Dto/SpeakerDto.php
    namespace App\Dto;

    use Symfony\Component\Form\Attribute\AsFormType;
    use Symfony\Component\Form\Attribute\FormField;

    // the form has two fields: "name" (from PersonDto), then "bio"
    #[AsFormType]
    class SpeakerDto extends PersonDto
    {
        #[FormField]
        public ?string $bio = null;
    }

Validation
----------

The data class is validated like the ``data_class`` of any other form, so you
can add :doc:`validation constraints </validation>` next to the
``#[FormField]`` attributes::

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
checked when the property that holds it has the
:doc:`Valid </reference/constraints/Valid>` constraint::

    // src/Dto/CustomerDto.php
    namespace App\Dto;

    use Symfony\Component\Form\Attribute\AsFormType;
    use Symfony\Component\Form\Attribute\FormField;
    use Symfony\Component\Validator\Constraints as Assert;

    #[AsFormType]
    class CustomerDto
    {
        // ...

        #[FormField(AddressDto::class)]
        #[Assert\Valid]
        public ?AddressDto $address = null;
    }

.. _form-attributes-type-extension:

Adding Behavior with Type Extensions
------------------------------------

The attributes are meant for forms that only describe fields. Any behavior,
such as :doc:`form events </form/events>`,
:doc:`data transformers </form/data_transformers>` or fields added at runtime,
belongs in a :doc:`form type extension </form/create_form_type_extension>`
that extends the data class. The extension is a regular service, so it can use
dependency injection::

    // src/Form/Extension/UserDtoExtension.php
    namespace App\Form\Extension;

    use App\Dto\UserDto;
    use Symfony\Component\Form\AbstractTypeExtension;
    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    use Symfony\Component\Form\FormBuilderInterface;

    class UserDtoExtension extends AbstractTypeExtension
    {
        public static function getExtendedTypes(): iterable
        {
            // use the data class as the extended type
            yield UserDto::class;
        }

        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder->add('agreeTerms', CheckboxType::class, ['mapped' => false]);
        }
    }

Debugging the Forms
-------------------

The ``debug:form`` command lists the classes that use ``#[AsFormType]`` in a
section called "Data class form types". Pass a class name to the command to
display the options of its form:

.. code-block:: terminal

    # lists all form types, including the data class form types
    $ php bin/console debug:form

    # displays the options of the form created from the UserDto class
    $ php bin/console debug:form "App\Dto\UserDto"
