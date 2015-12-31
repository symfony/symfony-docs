.. index::
   single: Form; Custom field type

How to Create a Custom Form Field Type
======================================

Symfony comes with a bunch of core field types available for building forms.
However there are situations where you may want to create a custom form field
type for a specific purpose. This recipe assumes you need a field definition
that holds a person's gender, based on the existing choice field. This section
explains how the field is defined, how you can customize its layout and finally,
how you can register it for use in your application.

Defining the Field Type
-----------------------

In order to create the custom field type, first you have to create the class
representing the field. In this situation the class holding the field type
will be called ``GenderType`` and the file will be stored in the default location
for form fields, which is ``<BundleName>\Form\Type``. Make sure the field extends
:class:`Symfony\\Component\\Form\\AbstractType`::

    // src/AppBundle/Form/Type/GenderType.php
    namespace AppBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

    class GenderType extends AbstractType
    {
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'choices' => array(
                    'm' => 'Male',
                    'f' => 'Female',
                )
            ));
        }

        public function getParent()
        {
            return ChoiceType::class;
        }
    }

.. tip::

    The location of this file is not important - the ``Form\Type`` directory
    is just a convention.

.. versionadded:: 2.8
    In 2.8, the ``getName()`` method was removed. Now, fields are always referred
    to by their fully-qualified class name.

Here, the return value of the ``getParent`` function indicates that you're
extending the ``ChoiceType`` field. This means that, by default, you inherit
all of the logic and rendering of that field type. To see some of the logic,
check out the `ChoiceType`_ class. There are three methods that are particularly
important:

``buildForm()``
    Each field type has a ``buildForm`` method, which is where
    you configure and build any field(s). Notice that this is the same method
    you use to setup *your* forms, and it works the same here.

``buildView()``
    This method is used to set any extra variables you'll
    need when rendering your field in a template. For example, in `ChoiceType`_,
    a ``multiple`` variable is set and used in the template to set (or not
    set) the ``multiple`` attribute on the ``select`` field. See `Creating a Template for the Field`_
    for more details.

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

The goal of this field was to extend the choice type to enable selection of
a gender. This is achieved by fixing the ``choices`` to a list of possible
genders.

Creating a Template for the Field
---------------------------------

Each field type is rendered by a template fragment, which is determined in part by
the class name of your type. For more information, see
:ref:`cookbook-form-customization-form-themes`.

.. note::

    The first part of the prefix (e.g. ``gender``) comes from the class name
    (``GenderType`` -> ``gender``). This can be controlled by overriding ``getBlockPrefix()``
    in ``GenderType``.

In this case, since the parent field is ``ChoiceType``, you don't *need* to do
any work as the custom field type will automatically be rendered like a ``ChoiceType``.
But for the sake of this example, suppose that when your field is "expanded"
(i.e. radio buttons or checkboxes, instead of a select field), you want to
always render it in a ``ul`` element. In your form theme template (see above
link for details), create a ``gender_widget`` block to handle this:

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/Form/fields.html.twig #}
        {% block gender_widget %}
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

        <!-- app/Resources/views/Form/gender_widget.html.php -->
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
    be ``gender_widget`` (see :ref:`cookbook-form-customization-form-themes`).
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
            <twig:config>
                <twig:form-theme>form/fields.html.twig</twig:form-theme>
            </twig:config>

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
    use AppBundle\Form\Type\GenderType;

    class AuthorType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('gender_code', GenderType::class, array(
                'placeholder' => 'Choose a gender',
            ));
        }
    }

But this only works because the ``GenderType`` is very simple. What if
the gender codes were stored in configuration or in a database? The next
section explains how more complex field types solve this problem.

.. _form-cookbook-form-field-service:

Creating your Field Type as a Service
-------------------------------------

So far, this entry has assumed that you have a very simple custom field type.
But if you need access to configuration, a database connection, or some other
service, then you'll want to register your custom type as a service. For
example, suppose that you're storing the gender parameters in configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        parameters:
            genders:
                m: Male
                f: Female

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <parameters>
            <parameter key="genders" type="collection">
                <parameter key="m">Male</parameter>
                <parameter key="f">Female</parameter>
            </parameter>
        </parameters>

    .. code-block:: php

        // app/config/config.php
        $container->setParameter('genders.m', 'Male');
        $container->setParameter('genders.f', 'Female');

To use the parameter, define your custom field type as a service, injecting
the ``genders`` parameter value as the first argument to its to-be-created
``__construct`` function:

.. configuration-block::

    .. code-block:: yaml

        # src/AppBundle/Resources/config/services.yml
        services:
            app.form.type.gender:
                class: AppBundle\Form\Type\GenderType
                arguments:
                    - '%genders%'
                tags:
                    - { name: form.type }

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/services.xml -->
        <service id="app.form.type.gender" class="AppBundle\Form\Type\GenderType">
            <argument>%genders%</argument>
            <tag name="form.type" />
        </service>

    .. code-block:: php

        // src/AppBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $container
            ->setDefinition('app.form.type.gender', new Definition(
                'AppBundle\Form\Type\GenderType',
                array('%genders%')
            ))
            ->addTag('form.type')
        ;

.. tip::

    Make sure the services file is being imported. See :ref:`service-container-imports-directive`
    for details.

First, add a ``__construct`` method to ``GenderType``, which receives the gender
configuration::

    // src/AppBundle/Form/Type/GenderType.php
    namespace AppBundle\Form\Type;

    use Symfony\Component\OptionsResolver\OptionsResolver;

    // ...

    // ...
    class GenderType extends AbstractType
    {
        private $genderChoices;

        public function __construct(array $genderChoices)
        {
            $this->genderChoices = $genderChoices;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'choices' => $this->genderChoices,
            ));
        }

        // ...
    }

Great! The ``GenderType`` is now fueled by the configuration parameters and
registered as a service. Because you used the ``form.type`` alias in its configuration,
your service will be used instead of creating a *new* ``GenderType``. In other words,
your controller *does not need to change*, it still looks like this::

    // src/AppBundle/Form/Type/AuthorType.php
    namespace AppBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use AppBundle\Form\Type\GenderType;

    class AuthorType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('gender_code', GenderType::class, array(
                'placeholder' => 'Choose a gender',
            ));
        }
    }

Have fun!

.. _`ChoiceType`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Extension/Core/Type/ChoiceType.php
.. _`FieldType`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Extension/Core/Type/FieldType.php
