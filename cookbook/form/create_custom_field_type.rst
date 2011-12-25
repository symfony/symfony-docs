.. index::
   single: Form; Custom field type

How to Create a Custom Form Field Type
======================================

Symfony comes with a bunch of core field types available for building forms.
However there are situations where we want to create a custom form field
type for a specific purpose. In this recipe we assume we need a field definition
that holds a person's gender, based on the existing choice field. This section
explains how the field is defined, how we can customize its layout and finally,
how we can register it to use it in our application.

Defining the Field Type
--------------------------

In order to create the custom field type, first we have to create the class
representing the field. In our situation the class holding the field type
will be called GenderType and the file will be stored in the default location
for form fields, which is <BundleName>\Form\Type. Make sure the field extends
AbstractType.

.. code-block:: php

    # src/Acme/DemoBundle/Form/Type/GenderType.php

    namespace Acme\DemoBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilder;

    class GenderType extends AbstractType
    {
        /**
         * {@inheritdoc}
         */
        public function getDefaultOptions(array $options)
        {
            return array(
                'choice_list' => new GenderChoiceList(),
            );
        }

        /**
         * {@inheritdoc}
         */
        public function getParent(array $options)
        {
            return 'choice';
        }

        /**
         * {@inheritdoc}
         */
        public function getName()
        {
            return 'gender';
        }

    }

Here the return value of the getParent function indicates that the choice
field is extended. The getName method returns an identifier which is used
to prevent conflicts with other types. For shared bundles, a good practice
is to start the type name with the bundle alias.

The goal of our field was to extend the choice type to enable selection of
a gender. This is achieved by fixing the choice_list to a list of possible
genders. As the choice field uses a ChoiceList for building its list, we
create one that provides this information.

.. code-block:: php

    # src/Acme/DemoBundle/Form/ChoiceList/GenderChoiceList.php

    namespace Acme\DemoBundle\Form\ChoiceList;

    use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;

    class GenderChoiceList implements ChoiceListInterface
    {
      public function getChoices()
      {
        return array(
              'm' => 'male',
              'f' => 'female',
        );
      }
    }

Creating a Template for the Field
---------------------------------

In most situations the custom form field has a specific layout instead of
the layout generated from the contained fields. In this case, we want to show
the firstName and lastName of our field in-line and show possible validation
errors of both fields on top. We start by creating a Twig template that extends
form_div_layout.html.twig (if it does not exist yet) and add our new field.

.. code-block:: html+jinja

    # src/Acme/DemoBundle/Resources/Form/fields.html.twig

    {% block gender_widget %}
        <div {{ block('widget_container_attributes') }}>
            {{ form_errors(form) }}
            {{ form_widget(form) }}
        </div>
    {% endblock %}

Make sure the correct widget prefix is used. In this example the name should
be gender_widget, according to the value returned by getName. Further,
the main config file should point to the custom fields template. Otherwise
the default form template will be used.

.. code-block:: yaml

    # app/config/config.yml

    twig:
        form:
            resources:
                - 'AcmeDemoBundle:Form:fields.html.twig'

Registering the Field Type
--------------------------

Now that we have defined the new field type we need to register it to be
able to use it in our forms. This is achieved by adding it as a new service.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/config/resources.yml

        form.type.gender:
            class: Acme\DemoBundle\Form\Type\FullnameType
            tags:
              - { name: form.type, alias: gender }

    .. code-block:: xml

        # src/Acme/DemoBundle/Resources/config/resources.xml

        <service id="form.type.gender" class="Acme\DemoBundle\Form\Type\GenderType">
            <tag name="form.type" alias="gender" />
        </service>

Make sure that the alias tag corresponds with the value returned by the getName
method defined in our custom form class.

And voila, now we have defined a basic form field that we can use and further
extend and customize.