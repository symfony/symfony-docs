.. index::
   single: Form; Custom field type

How to Create a Custom Form Field Type
======================================

Symfony comes with a bunch of core field types available for building forms.
However there are situations where we want to create a custom form field
type for a specific purpose. This recipe assumes we need a field definition
that holds a person's gender, based on the existing choice field. This section
explains how the field is defined, how we can customize its layout and finally,
how we can register it for use in our application.

Defining the Field Type
-----------------------

In order to create the custom field type, first we have to create the class
representing the field. In our situation the class holding the field type
will be called `GenderType` and the file will be stored in the default location
for form fields, which is ``<BundleName>\Form\Type``. Make sure the field extends
:class:`Symfony\\Component\\Form\\AbstractType`::

    // src/Acme/DemoBundle/Form/Type/GenderType.php
    namespace Acme\DemoBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilder;

    class GenderType extends AbstractType
    {
        public function getDefaultOptions(array $options)
        {
            return array(
                'choices' => array(
                    'm' => 'Male',
                    'f' => 'Female',
                )
            );
        }

        public function getParent(array $options)
        {
            return 'choice';
        }

        public function getName()
        {
            return 'gender';
        }
    }

.. tip::

    The location of this file is not important - the ``Form\Type`` directory
    is just a convention.

Here, the return value of the ``getParent`` function indicates that we're
extending the ``choice`` field type. This means that, by default, we inherit
all of the logic and rendering of that field type. To see some of the logic,
check out the `ChoiceType`_ class. There are three methods that are particularly
important:

* ``buildForm()`` - Each field type has a ``buildForm`` method, which is where
  you configure and build any field(s). Notice that this is the same method
  you use to setup *your* forms, and it works the same here.

* ``buildView()`` - This method is used to set any extra variables you'll
  need when rendering your field in a template. For example, in `ChoiceType`_,
  a ``multiple`` variable is set and used in the template to set (or not
  set) the ``multiple`` attribute on the ``select`` field. See `Creating a Template for the Field`_
  for more details.

* ``getDefaultOptions()`` - This defines options for your form type that
  can be used in ``buildForm()`` and ``buildView()``. There are a lot of
  options common to all fields (see `FieldType`_), but you can create any
  others that you need here.

.. tip::

    If you're creating a field that consists of many fields, then be sure
    to set your "parent" type as ``form`` or something that extends ``form``.
    Also, if you need to modify the "view" of any of your child types from
    your parent type, use the ``buildViewBottomUp()`` method.

The ``getName()`` method returns an identifier which should be unique in
your application. This is used in various places, such as when customizing
how your form type will be rendered.

The goal of our field was to extend the choice type to enable selection of
a gender. This is achieved by fixing the ``choices`` to a list of possible
genders.

Creating a Template for the Field
---------------------------------

Each field type is rendered by a template fragment, which is determined in
part by the value of your ``getName()`` method. For more information, see
:ref:`cookbook-form-customization-form-themes`.

In this case, since our parent field is ``choice``, we don't *need* to do
any work as our custom field type will automatically be rendered like a ``choice``
type. But for the sake of this example, let's suppose that when our field
is "expanded" (i.e. radio buttons or checkboxes, instead of a select field),
we want to always render it in a ``ul`` element. In your form theme template
(see above link for details), create a ``gender_widget`` block to handle this:

.. code-block:: html+jinja

    {# src/Acme/DemoBundle/Resources/views/Form/fields.html.twig #}
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

.. note::

    Make sure the correct widget prefix is used. In this example the name should
    be ``gender_widget``, according to the value returned by ``getName``.
    Further, the main config file should point to the custom form template
    so that it's used when rendering all forms.

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            form:
                resources:
                    - 'AcmeDemoBundle:Form:fields.html.twig'

Using the Field Type
--------------------

You can now use your custom field type immediately, simply by creating a
new instance of the type in one of your forms::

    // src/Acme/DemoBundle/Form/Type/AuthorType.php
    namespace Acme\DemoBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilder;

    class AuthorType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('gender_code', new GenderType(), array(
                'empty_value' => 'Choose a gender',
            ));
        }
    }

But this only works because the ``GenderType()`` is very simple. What if
the gender codes were stored in configuration or in a database? The next
section explains how more complex field types solve this problem.

Creating your Field Type as a Service
-------------------------------------

So far, this entry has assumed that you have a very simple custom field type.
But if you need access to configuration, a database connection, or some other
service, then you'll want to register your custom type as a service. For
example, suppose that we're storing the gender parameters in configuration:

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

To use the parameter, we'll define our custom field type as a service, injecting
the ``genders`` parameter value as the first argument to its to-be-created
``__construct`` function:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/config/services.yml
        services:
            form.type.gender:
                class: Acme\DemoBundle\Form\Type\GenderType
                arguments:
                    - "%genders%"
                tags:
                    - { name: form.type, alias: gender }

    .. code-block:: xml

        <!-- src/Acme/DemoBundle/Resources/config/services.xml -->
        <service id="form.type.gender" class="Acme\DemoBundle\Form\Type\GenderType">
            <argument>%genders%</argument>
            <tag name="form.type" alias="gender" />
        </service>

.. tip::

    Make sure the services file is being imported. See :ref:`service-container-imports-directive`
    for details.

Be sure that the ``alias`` attribute of the tag corresponds with the value
returned by the ``getName`` method defined earlier. We'll see the importance
of this in a moment when we use the custom field type. But first, add a ``__construct``
argument to ``GenderType``, which receives the gender configuration::

    // src/Acme/DemoBundle/Form/Type/GenderType.php
    namespace Acme\DemoBundle\Form\Type;
    // ...

    class GenderType extends AbstractType
    {
        private $genderChoices;
        
        public function __construct(array $genderChoices)
        {
            $this->genderChoices = $genderChoices;
        }
    
        public function getDefaultOptions(array $options)
        {
            return array(
                'choices' => $this->genderChoices,
            );
        }
        
        // ...
    }

Great! The ``GenderType`` is now fueled by the configuration parameters and
registered as a service. And because we used the ``form.type`` alias in its
configuration, using the field is now much easier::

    // src/Acme/DemoBundle/Form/Type/AuthorType.php
    namespace Acme\DemoBundle\Form\Type;
    // ...

    class AuthorType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('gender_code', 'gender', array(
                'empty_value' => 'Choose a gender',
            ));
        }
    }

Notice that instead of instantiating a new instance, we can just refer to
it by the alias used in our service configuration, ``gender``. Have fun!

.. _`ChoiceType`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Extension/Core/Type/ChoiceType.php
.. _`FieldType`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Form/Extension/Core/Type/FieldType.php
