.. index::
   single: Form; Custom field type

How to Create a Custom Form Field Type
======================================

Symfony comes with a bunch of core field types available for building forms.
However there are situations where you may want to create a custom form field
type for a specific purpose. This article assumes you need a field definition
that holds a shipping option, based on the existing choice field. This section
explains how the field is defined and how you can customize its layout.

Defining the Field Type
-----------------------

In order to create the custom field type, first you have to create the class
representing the field. In this situation the class holding the field type
will be called ``ShippingType`` and the file will be stored in the default location
for form fields, which is ``App\Form\Type``.

All field types must implement the :class:`Symfony\\Component\\Form\\FormTypeInterface`,
but you should instead extend from :class:`Symfony\\Component\\Form\\AbstractType`,
which already implements that interface and provides some utilities::

    // src/Form/Type/ShippingType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

    class ShippingType extends AbstractType
    {
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'choices' => [
                    'Standard Shipping' => 'standard',
                    'Expedited Shipping' => 'expedited',
                    'Priority Shipping' => 'priority',
                ],
            ]);
        }

        public function getParent()
        {
            return ChoiceType::class;
        }
    }

.. tip::

    The location of this file is not important - the ``Form\Type`` directory
    is just a convention.

Here, the return value of the ``getParent()`` function indicates that you're
extending the ``ChoiceType`` field. This means that, by default, you inherit
all of the logic and rendering of that field type. To see some of the logic,
check out the `ChoiceType`_ class.

.. note::

    The PHP class extension mechanism and the Symfony form field extension
    mechanism are not the same. The parent type returned in ``getParent()`` is
    what Symfony uses to build and manage the field type. Making the PHP class
    extend from ``AbstractType`` is only a convenience way of implementing the
    required ``FormTypeInterface``.

There are three methods that are particularly important:

.. _form-type-methods-explanation:

``buildForm()``
    Each field type has a ``buildForm()`` method, which is where
    you configure and build any field(s). Notice that this is the same method
    you use to setup *your* forms, and it works the same here.

``buildView()``
    This method is used to set any extra variables you'll
    need when rendering your field in a template. For example, in `ChoiceType`_,
    a ``multiple`` variable is set and used in the template to set (or not
    set) the ``multiple`` attribute on the ``select`` field. See
    `Creating a Template for the Field`_ for more details.

``configureOptions()``
    This defines options for your form type that
    can be used in ``buildForm()`` and ``buildView()``. There are a lot of
    options common to all fields (see :doc:`/reference/forms/types/form`),
    but you can create any others that you need here.

.. tip::

    If you're creating a field that consists of many fields, then be sure
    to set your "parent" type as ``form`` or something that extends ``form``.
    Also, if you need to modify the "view" of any of your child types from
    your parent type, use the ``finishView()`` method.

The goal of this field was to extend the choice type to enable selection of the
shipping type. This is achieved by fixing the ``choices`` to a list of available
shipping options.

.. tip::

    If the purpose of this new form type was to customize the rendering of some
    fields only, skip this step and use ``block_name`` or ``block_prefix`` options
    instead to :ref:`define a custom form fragment name <form-fragment-custom-naming>`.

.. tip::

    Run the following command to verify that the form type was successfully
    registered in the application:

    .. code-block:: terminal

        $ php bin/console debug:form

Creating a Template for the Field
---------------------------------

Each field type is rendered by a template fragment whose name is determined in
part by the class name of your type. Read the :ref:`from fragment naming <form-fragment-naming>`
rules for more details.

.. note::

    The first part of the prefix (e.g. ``shipping``) comes from the class name
    (``ShippingType`` -> ``shipping``). This can be controlled by overriding ``getBlockPrefix()``
    in ``ShippingType``.

.. caution::

    When the name of your form class matches any of the built-in field types,
    your form might not be rendered correctly. A form type named
    ``App\Form\PasswordType`` will have the same block name as the
    built-in ``PasswordType`` and won't be rendered correctly. Override the
    ``getBlockPrefix()`` method to return a unique block prefix (e.g.
    ``app_password``) to avoid collisions.

In this case, since the parent field is ``ChoiceType``, you don't *need* to do
any work as the custom field type will automatically be rendered like a ``ChoiceType``.
But for the sake of this example, suppose that when your field is "expanded"
(i.e. radio buttons or checkboxes, instead of a select field), you want to
always render it in a ``ul`` element. In your form theme template (see above
link for details), create a ``shipping_widget`` block to handle this:

.. code-block:: html+twig

    {# templates/form/fields.html.twig #}
    {% block shipping_widget %}
        {% spaceless %}
            {% if expanded %}
                <ul {{ block('widget_container_attributes') }}>
                    {% for child in form if not child.rendered %}
                        <li>
                            {{ form_widget(child) }}
                            {{ form_label(child) }}
                        </li>
                    {% endfor %}
                </ul>
            {% else %}
                {# let the choice widget render the select tag #}
                {{ block('choice_widget') }}
            {% endif %}
        {% endspaceless %}
    {% endblock %}

.. note::

    Symfony 4.2 deprecated calling ``FormRenderer::searchAndRenderBlock`` for
    fields that have already been rendered. That's why the previous example
    includes the ``... if not child.rendered`` statement.

.. tip::

    You can further customize the template used to render each children of the
    choice type. The block to override in that case is named "block name" +
    ``_entry_`` + "element name" (``label``, ``errors`` or ``widget``) (e.g. to
    customize the labels of the children of the Shipping widget you'd need to
    define ``{% block shipping_entry_label %} ... {% endblock %}``).

.. note::

    Make sure the correct widget prefix is used. In this example the name should
    be ``shipping_widget`` (see :ref:`form fragment naming <form-fragment-naming>`
    rules). Further, the main config file should point to the custom form template
    so that it's used when rendering all forms.

    When using Twig this is:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/twig.yaml
            twig:
                form_themes:
                    - 'form/fields.html.twig'

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
                    <twig:form-theme>form/fields.html.twig</twig:form-theme>
                </twig:config>
            </container>

        .. code-block:: php

            // config/packages/twig.php
            $container->loadFromExtension('twig', [
                'form_themes' => [
                    'form/fields.html.twig',
                ],
            ]);

    For the PHP templating engine, your configuration should look like this:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/framework.yaml
            framework:
                templating:
                    form:
                        resources:
                            - ':form:fields.html.php'

        .. code-block:: xml

            <!-- config/packages/framework.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <framework:config>
                    <framework:templating>
                        <framework:form>
                            <framework:resource>:form:fields.html.php</twig:resource>
                        </framework:form>
                    </framework:templating>
                </framework:config>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            $container->loadFromExtension('framework', [
                'templating' => [
                    'form' => [
                        'resources' => [
                            ':form:fields.html.php',
                        ],
                    ],
                ],
            ]);

Using the Field Type
--------------------

You can now use your custom field type by creating a new instance of the type in
one of your forms::

    // src/Form/Type/OrderType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use App\Form\Type\ShippingType;

    class OrderType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('shipping_code', ShippingType::class, [
                'placeholder' => 'Choose a delivery option',
            ]);
        }
    }

But this only works because the ``ShippingType()`` is very simple. What if
the shipping codes were stored in configuration or in a database? The next
section explains how more complex field types solve this problem.

.. _form-field-service:
.. _creating-your-field-type-as-a-service:

Accessing Services and Config
-----------------------------

If you need to access :doc:`services </service_container>` from your form class,
add a ``__construct()`` method like normal::

    // src/Form/Type/ShippingType.php
    namespace App\Form\Type;

    // ...
    use Doctrine\ORM\EntityManagerInterface;

    class ShippingType extends AbstractType
    {
        private $entityManager;

        public function __construct(EntityManagerInterface $entityManager)
        {
            $this->entityManager = $entityManager;
        }

        // use $this->entityManager down anywhere you want ...
    }

If you're using the default ``services.yaml`` configuration (i.e. services from the
``Form/`` are loaded and ``autoconfigure`` is enabled), this will already work!
See :ref:`service-container-creating-service` for more details.

.. tip::

    If you're not using :ref:`autoconfigure <services-autoconfigure>`, make sure
    to :doc:`tag </service_container/tags>` your service with ``form.type``.

Have fun!

.. _`ChoiceType`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Extension/Core/Type/ChoiceType.php
.. _`FieldType`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Extension/Core/Type/FieldType.php
