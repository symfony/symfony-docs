.. index::
   single: Form; Custom field type

How to Create a Custom Form Field Type
======================================

Symfony comes with a bunch of core field types available for building forms.
However there are situations where you may want to create a custom form field
type for a specific purpose. This recipe assumes you need a field definition
that holds a shipping option, based on the existing choice field. This section
explains how the field is defined, how you can customize its layout and finally,
how you can register it for use in your application.

Defining the Field Type
-----------------------

In order to create the custom field type, first you have to create the class
representing the field. In this situation the class holding the field type
will be called ``ShippingType`` and the file will be stored in the default location
for form fields, which is ``<BundleName>\Form\Type``. Make sure the field extends
:class:`Symfony\\Component\\Form\\AbstractType`::

    // src/AppBundle/Form/Type/ShippingType.php
    namespace AppBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class ShippingType extends AbstractType
    {
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'choices' => array(
                    'Standard Shipping' => 'standard',
                    'Expedited Shipping' => 'expedited',
                    'Priority Shipping' => 'priority',
                ),
                'choices_as_values' => true,
            ));
        }

        public function getParent()
        {
            return 'choice';
        }

        public function getName()
        {
            return 'app_shipping';
        }
    }

.. tip::

    The location of this file is not important - the ``Form\Type`` directory
    is just a convention.

Here, the return value of the ``getParent()`` function indicates that you're
extending the ``choice`` field type. This means that, by default, you inherit
all of the logic and rendering of that field type. To see some of the logic,
check out the `ChoiceType`_ class. There are three methods that are particularly
important:

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

.. versionadded:: 2.7
    The ``configureOptions()`` method was introduced in Symfony 2.7. Previously,
    the method was called ``setDefaultOptions()``.

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

The ``getName()`` method returns an identifier which should be unique in
your application. This is used in various places, such as when customizing
how your form type will be rendered.

The goal of this field was to extend the choice type to enable selection of the
shipping type. This is achieved by fixing the ``choices`` to a list of available
shipping options.

Creating a Template for the Field
---------------------------------

Each field type is rendered by a template fragment, which is determined in
part by the value of your ``getName()`` method. For more information, see
:ref:`form-customization-form-themes`.

In this case, since the parent field is ``choice``, you don't *need* to do
any work as the custom field type will automatically be rendered like a ``choice``
type. But for the sake of this example, suppose that when your field is "expanded"
(i.e. radio buttons or checkboxes, instead of a select field), you want to
always render it in a ``ul`` element. In your form theme template (see above
link for details), create a ``shipping_widget`` block to handle this:

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/form/fields.html.twig #}
        {% block shipping_widget %}
            {% spaceless %}
                {% if expanded %}
                    <ul {{ block('widget_container_attributes') }}>
                    {% for child in form %}
                        <li>
                            {{ form_widget(child) }}
                            {{ form_label(child) }}
                        </li>
                    {% endfor %}
                    </ul>
                {% else %}
                    {# just let the choice widget render the select tag #}
                    {{ block('choice_widget') }}
                {% endif %}
            {% endspaceless %}
        {% endblock %}

    .. code-block:: html+php

        <!-- app/Resources/views/form/shipping_widget.html.php -->
        <?php if ($expanded) : ?>
            <ul <?php $view['form']->block($form, 'widget_container_attributes') ?>>
            <?php foreach ($form as $child) : ?>
                <li>
                    <?php echo $view['form']->widget($child) ?>
                    <?php echo $view['form']->label($child) ?>
                </li>
            <?php endforeach ?>
            </ul>
        <?php else : ?>
            <!-- just let the choice widget render the select tag -->
            <?php echo $view['form']->renderBlock('choice_widget') ?>
        <?php endif ?>

.. note::

    Make sure the correct widget prefix is used. In this example the name should
    be ``shipping_widget``, according to the value returned by ``getName()``.
    Further, the main config file should point to the custom form template
    so that it's used when rendering all forms.

    When using Twig this is:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            twig:
                form_themes:
                    - 'form/fields.html.twig'

        .. code-block:: xml

            <!-- app/config/config.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:twig="http://symfony.com/schema/dic/twig"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/twig
                    http://symfony.com/schema/dic/twig/twig-1.0.xsd">

                <twig:config>
                    <twig:form-theme>form/fields.html.twig</twig:form-theme>
                </twig:config>
            </container>

        .. code-block:: php

            // app/config/config.php
            $container->loadFromExtension('twig', array(
                'form_themes' => array(
                    'form/fields.html.twig',
                ),
            ));

    For the PHP templating engine, your configuration should look like this:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            framework:
                templating:
                    form:
                        resources:
                            - ':form:fields.html.php'

        .. code-block:: xml

            <!-- app/config/config.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <framework:config>
                    <framework:templating>
                        <framework:form>
                            <framework:resource>:form:fields.html.php</twig:resource>
                        </framework:form>
                    </framework:templating>
                </framework:config>
            </container>

        .. code-block:: php

            // app/config/config.php
            $container->loadFromExtension('framework', array(
                'templating' => array(
                    'form' => array(
                        'resources' => array(
                            ':form:fields.html.php',
                        ),
                    ),
                ),
            ));

Using the Field Type
--------------------

You can now use your custom field type immediately, simply by creating a
new instance of the type in one of your forms::

    // src/AppBundle/Form/Type/AuthorType.php
    namespace AppBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;

    class OrderType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('shipping_code', new ShippingType(), array(
                'placeholder' => 'Choose a delivery option',
            ));
        }
    }

But this only works because the ``ShippingType()`` is very simple. What if
the shipping codes were stored in configuration or in a database? The next
section explains how more complex field types solve this problem.

.. versionadded:: 2.6
    The ``placeholder`` option was introduced in Symfony 2.6 and replaces
    ``empty_value``, which is available prior to 2.6.

.. _form-field-service:

Creating your Field Type as a Service
-------------------------------------

So far, this entry has assumed that you have a very simple custom field type.
But if you need access to configuration, a database connection, or some other
service, then you'll want to register your custom type as a service. For
example, suppose that you're storing the shipping parameters in configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        parameters:
            shipping_options:
                standard: Standard Shipping
                expedited: Expedited Shipping
                priority: Priority Shipping

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="shipping_options" type="collection">
                    <parameter key="standard">Standard Shipping</parameter>
                    <parameter key="expedited">Expedited Shipping</parameter>
                    <parameter key="priority">Priority Shipping</parameter>
                </parameter>
            </parameters>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->setParameter('shipping_options', array(
            'standard' => 'Standard Shipping',
            'expedited' => 'Expedited Shipping',
            'priority' => 'Priority Shipping',
        ));

To use the parameter, define your custom field type as a service, injecting the
``shipping_options`` parameter value as the first argument to its to-be-created
``__construct()`` function:

.. configuration-block::

    .. code-block:: yaml

        # src/AppBundle/Resources/config/services.yml
        services:
            app.form.type.shipping:
                class: AppBundle\Form\Type\ShippingType
                arguments:
                    - '%shipping_options%'
                tags:
                    - { name: form.type, alias: app_shipping }

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.form.type.shipping" class="AppBundle\Form\Type\ShippingType">
                    <argument>%shipping_options%</argument>
                    <tag name="form.type" alias="app_shipping" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // src/AppBundle/Resources/config/services.php
        use AppBundle\Form\Type\ShippingType;

        $container->register('app.form.type.shipping', ShippingType::class)
            ->addArgument('%shipping_options%')
            ->addTag('form.type', array(
                'alias' => 'app_shipping',
            ));

.. tip::

    Make sure the services file is being imported. See :ref:`service-container-imports-directive`
    for details.

Be sure that the ``alias`` attribute of the tag corresponds with the value
returned by the ``getName()`` method defined earlier. You'll see the importance
of this in a moment when you use the custom field type. But first, add a ``__construct``
method to ``ShippingType``, which receives the shipping configuration::

    // src/AppBundle/Form/Type/ShippingType.php
    namespace AppBundle\Form\Type;

    use Symfony\Component\OptionsResolver\OptionsResolver;

    // ...

    // ...
    class ShippingType extends AbstractType
    {
        private $shippingOptions;

        public function __construct(array $shippingOptions)
        {
            $this->shippingOptions = $shippingOptions;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'choices' => array_flip($this->shippingOptions),
                'choices_as_values' => true,
            ));
        }

        // ...
    }

Great! The ``ShippingType`` is now fueled by the configuration parameters and
registered as a service. Additionally, because you used the ``form.type`` tag in its
configuration, using the field is now much easier::

    // src/AppBundle/Form/Type/OrderType.php
    namespace AppBundle\Form\Type;

    use Symfony\Component\Form\FormBuilderInterface;

    // ...

    class OrderType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('shipping_code', 'app_shipping', array(
                'placeholder' => 'Choose a delivery option',
            ));
        }
    }

Notice that instead of instantiating a new instance, you can just refer to
it by the alias used in your service configuration, ``app_shipping``. Have fun!

.. _`ChoiceType`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Extension/Core/Type/ChoiceType.php
.. _`FieldType`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Extension/Core/Type/FieldType.php
