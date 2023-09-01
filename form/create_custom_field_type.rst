How to Create a Custom Form Field Type
======================================

Symfony comes with :doc:`tens of form types </reference/forms/types>` (called
"form fields" in other projects) ready to use in your applications. However,
it's common to create custom form types to solve specific purposes in your
projects.

Creating Form Types Based on Symfony Built-in Types
---------------------------------------------------

The easiest way to create a form type is to base it on one of the
:doc:`existing form types </reference/forms/types>`. Imagine that your project
displays a list of "shipping options" as a ``<select>`` HTML element. This can
be implemented with a :doc:`ChoiceType </reference/forms/types/choice>` where the
``choices`` option is set to the list of available shipping options.

However, if you use the same form type in several forms, repeating the list of
``choices`` every time you use it quickly becomes boring. In this example, a
better solution is to create a custom form type based on ``ChoiceType``. The
custom type looks and behaves like a ``ChoiceType`` but the list of choices is
already populated with the shipping options so you don't need to define them.

Form types are PHP classes that implement :class:`Symfony\\Component\\Form\\FormTypeInterface`,
but you should instead extend from :class:`Symfony\\Component\\Form\\AbstractType`,
which already implements that interface and provides some utilities.
By convention they are stored in the ``src/Form/Type/`` directory::

    // src/Form/Type/ShippingType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class ShippingType extends AbstractType
    {
        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'choices' => [
                    'Standard Shipping' => 'standard',
                    'Expedited Shipping' => 'expedited',
                    'Priority Shipping' => 'priority',
                ],
            ]);
        }

        public function getParent(): string
        {
            return ChoiceType::class;
        }
    }

``getParent()`` tells Symfony to take ``ChoiceType`` as a starting point,
then ``configureOptions()`` overrides some of its options. (All methods of the
``FormTypeInterface`` are explained in detail later in this article.)
The resulting form type is a choice field with predefined choices.

Now you can add this form type when :doc:`creating Symfony forms </forms>`::

    // src/Form/Type/OrderType.php
    namespace App\Form\Type;

    use App\Form\Type\ShippingType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;

    class OrderType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                // ...
                ->add('shipping', ShippingType::class)
            ;
        }

        // ...
    }

That's all. The ``shipping`` form field will be rendered correctly in any
template because it reuses the templating logic defined by its parent type
``ChoiceType``. If you prefer, you can also define a template for your custom
types, as explained later in this article.

Creating Form Types Created From Scratch
----------------------------------------

Some form types are so specific to your projects that they cannot be based on
any :doc:`existing form types </reference/forms/types>` because they are too
different. Consider an application that wants to reuse in different forms the
following set of fields as the "postal address":

.. raw:: html

    <object data="/_images/form/form-custom-type-postal-address.svg" type="image/svg+xml"
        alt="A wireframe of the custom field type, showing five text inputs: two address lines, the City, the State and the ZIP code."
    ></object>

As explained above, form types are PHP classes that implement
:class:`Symfony\\Component\\Form\\FormTypeInterface`, although it's more
convenient to extend instead from :class:`Symfony\\Component\\Form\\AbstractType`::

    // src/Form/Type/PostalAddressType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\FormType;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class PostalAddressType extends AbstractType
    {
        // ...
    }

These are the most important methods that a form type class can define:

.. _form-type-methods-explanation:

``getParent()``
    If your custom type is based on another type (i.e. they share some
    functionality), add this method to return the fully-qualified class name
    of that original type. Do not use PHP inheritance for this.
    Symfony will call all the form type methods (``buildForm()``,
    ``buildView()``, etc.) and type extensions of the parent before
    calling the ones defined in your custom type.

    Otherwise, if your custom type is build from scratch, you can omit ``getParent()``.

    By default, the ``AbstractType`` class returns the generic
    :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType`
    type, which is the root parent for all form types in the Form component.

``configureOptions()``
    It defines the options configurable when using the form type, which are also
    the options that can be used in the following methods. Options are inherited
    from parent types and parent type extensions, but you can create any custom
    option you need.

``buildForm()``
    It configures the current form and may add nested fields. It's the same
    method used when
    :ref:`creating Symfony form classes <creating-forms-in-classes>`.

``buildView()``
    It sets any extra variables you'll need when rendering the field in a form
    theme template.

``finishView()``
    Same as ``buildView()``. This is useful only if your form type consists of
    many fields (i.e. A ``ChoiceType`` composed of many radio or checkboxes),
    as this method will allow accessing child views with
    ``$view['child_name']``. For any other use case, it's recommended to use
    ``buildView()`` instead.

Defining the Form Type
~~~~~~~~~~~~~~~~~~~~~~

Start by adding the ``buildForm()`` method to configure all the types included
in the postal address. For the moment, all fields are of type ``TextType``::

    // src/Form/Type/PostalAddressType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;

    class PostalAddressType extends AbstractType
    {
        // ...

        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('addressLine1', TextType::class, [
                    'help' => 'Street address, P.O. box, company name',
                ])
                ->add('addressLine2', TextType::class, [
                    'help' => 'Apartment, suite, unit, building, floor',
                ])
                ->add('city', TextType::class)
                ->add('state', TextType::class, [
                    'label' => 'State',
                ])
                ->add('zipCode', TextType::class, [
                    'label' => 'ZIP Code',
                ])
            ;
        }
    }

.. tip::

    Run the following command to verify that the form type was successfully
    registered in the application:

    .. code-block:: terminal

        $ php bin/console debug:form

This form type is ready to use it inside other forms and all its fields will be
correctly rendered in any template::

    // src/Form/Type/OrderType.php
    namespace App\Form\Type;

    use App\Form\Type\PostalAddressType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;

    class OrderType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                // ...
                ->add('address', PostalAddressType::class)
            ;
        }

        // ...
    }

However, the real power of custom form types is achieved with custom form
options (to make them flexible) and with custom templates (to make them look
better).

.. _form-type-config-options:

Adding Configuration Options for the Form Type
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Imagine that your project requires to make the ``PostalAddressType``
configurable in two ways:

* In addition to "address line 1" and "address line 2", some addresses should be
  allowed to display an "address line 3" to store extended address information;
* Instead of displaying a free text input, some addresses should be able to
  restrict the possible states to a given list.

This is solved with "form type options", which allow to configure the behavior
of the form types. The options are defined in the ``configureOptions()`` method
and you can use all the :doc:`OptionsResolver component features </components/options_resolver>`
to define, validate and process their values::

    // src/Form/Type/PostalAddressType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class PostalAddressType extends AbstractType
    {
        // ...

        public function configureOptions(OptionsResolver $resolver): void
        {
            // this defines the available options and their default values when
            // they are not configured explicitly when using the form type
            $resolver->setDefaults([
                'allowed_states' => null,
                'is_extended_address' => false,
            ]);

            // optionally you can also restrict the options type or types (to get
            // automatic type validation and useful error messages for end users)
            $resolver->setAllowedTypes('allowed_states', ['null', 'string', 'array']);
            $resolver->setAllowedTypes('is_extended_address', 'bool');

            // optionally you can transform the given values for the options to
            // simplify the further processing of those options
            $resolver->setNormalizer('allowed_states', static function (Options $options, $states): ?array
            {
                if (null === $states) {
                    return $states;
                }

                if (is_string($states)) {
                    $states = (array) $states;
                }

                return array_combine(array_values($states), array_values($states));
            });
        }
    }

Now you can configure these options when using the form type::

    // src/Form/Type/OrderType.php
    namespace App\Form\Type;

    // ...

    class OrderType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                // ...
                ->add('address', PostalAddressType::class, [
                    'is_extended_address' => true,
                    'allowed_states' => ['CA', 'FL', 'TX'],
                    // in this example, this config would also be valid:
                    // 'allowed_states' => 'CA',
                ])
            ;
        }

        // ...
    }

The last step is to use these options when building the form::

    // src/Form/Type/PostalAddressType.php
    namespace App\Form\Type;

    // ...

    class PostalAddressType extends AbstractType
    {
        // ...

        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            // ...

            if (true === $options['is_extended_address']) {
                $builder->add('addressLine3', TextType::class, [
                    'help' => 'Extended address info',
                ]);
            }

            if (null !== $options['allowed_states']) {
                $builder->add('state', ChoiceType::class, [
                    'choices' => $options['allowed_states'],
                ]);
            } else {
                $builder->add('state', TextType::class, [
                    'label' => 'State/Province/Region',
                ]);
            }
        }
    }

Creating the Form Type Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, custom form types will be rendered using the
:doc:`form themes </form/form_themes>` configured in the application. However,
for some types you may prefer to create a custom template in order to customize
how they look or their HTML structure.

First, create a new Twig template anywhere in the application to store the
fragments used to render the types:

.. code-block:: twig

    {# templates/form/custom_types.html.twig #}

    {# ... here you will add the Twig code ... #}

Then, update the :ref:`form_themes option <reference-twig-tag-form-theme>` to
add this new template at the beginning of the list (the first one overrides the
rest of files):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            form_themes:
                - 'form/custom_types.html.twig'
                - '...'

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <twig:form-theme>form/custom_types.html.twig</twig:form-theme>
                <twig:form-theme>...</twig:form-theme>
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig): void {
            $twig->formThemes([
                'form/custom_types.html.twig',
                '...',
            ]);
        };

The last step is to create the actual Twig template that will render the type.
The template contents depend on which HTML, CSS and JavaScript frameworks and
libraries are used in your application:

.. code-block:: html+twig

    {# templates/form/custom_types.html.twig #}
    {% block postal_address_row %}
        {% for child in form.children|filter(child => not child.rendered) %}
            <div class="form-group">
                {{ form_label(child) }}
                {{ form_widget(child) }}
                {{ form_help(child) }}
                {{ form_errors(child) }}
            </div>
        {% endfor %}
    {% endblock %}

The first part of the Twig block name (e.g. ``postal_address``) comes from the
class name (``PostalAddressType`` -> ``postal_address``). This can be controlled
by overriding the ``getBlockPrefix()`` method in ``PostalAddressType``. The
second part of the Twig block name (e.g. ``_row``) defines which form type part
is being rendered (row, widget, help, errors, etc.)

The article about form themes explains the
:ref:`form fragment naming rules <form-fragment-naming>` in detail. These
are some examples of Twig block names for the postal address type:

.. raw:: html

    <object data="/_images/form/form-custom-type-postal-address-fragment-names.svg" type="image/svg+xml"
        alt="The wireframe with some block names highlighted, these are also listed below the image."
    ></object>

``postal_address_row``
    The full form type block.
``postal_address_addressLine1_help``
    The help message block below the first address line.
``postal_address_state_widget``
    The text input widget for the State field.
``postal_address_zipCode_label``
    The label block of the ZIP Code field.

.. caution::

    When the name of your form class matches any of the built-in field types,
    your form might not be rendered correctly. A form type named
    ``App\Form\PasswordType`` will have the same block name as the built-in
    ``PasswordType`` and won't be rendered correctly. Override the
    ``getBlockPrefix()`` method to return a unique block prefix (e.g.
    ``app_password``) to avoid collisions.

Passing Variables to the Form Type Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony passes a series of variables to the template used to render the form
type. You can also pass your own variables, which can be based on the options
defined by the form or be completely independent::


    // src/Form/Type/PostalAddressType.php
    namespace App\Form\Type;

    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\Form\FormView;
    // ...

    class PostalAddressType extends AbstractType
    {
        public function __construct(
            private EntityManagerInterface $entityManager,
        ) {
        }

        // ...

        public function buildView(FormView $view, FormInterface $form, array $options): void
        {
            // pass the form type option directly to the template
            $view->vars['isExtendedAddress'] = $options['is_extended_address'];

            // make a database query to find possible notifications related to postal addresses (e.g. to
            // display dynamic messages such as 'Delivery to XX and YY states will be added next week!')
            $view->vars['notification'] = $this->entityManager->find('...');
        }
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
this example will already work! Otherwise, :ref:`create a service <service-container-creating-service>`
for this form class and :doc:`tag it </service_container/tags>` with ``form.type``.

The variables added in ``buildView()`` are available in the form type template
as any other regular Twig variable:

.. code-block:: html+twig

    {# templates/form/custom_types.html.twig #}
    {% block postal_address_row %}
        {# ... #}

        {% if isExtendedAddress %}
            {# ... #}
        {% endif %}

        {% if notification is not empty %}
            <div class="alert alert-primary" role="alert">
                {{ notification }}
            </div>
        {% endif %}
    {% endblock %}
