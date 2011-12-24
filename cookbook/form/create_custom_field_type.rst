.. index::
   single: Form; Custom field type

How to Create a Custom Form Field Type
======================================

Symfony comes with a bunch of core field types available for building forms.
However there are situations where we want to create a custom form field
type for a specific purpose. In this recipe we assume we need a field definition
that holds a person's full name, consisting of two inputs: the first name
and the last name. Of course we can achieve this by using two separate fields,
but let's assume we want to have a single field definition holding this
information. This section explains how the field is defined, how we can customize
its layout and finally, how we can register it to use it in our application.

Defining the Field Type
--------------------------

In order to create the custom field type, first we have to create the class
representing the field. In our situation the class holding the field type
will be called FullnameType and the file will be stored in the default location
for form fields, which is <BundleName>\Form\Type. Make sure the field extends
AbstractType.

.. code-block:: php

    # src/Acme/DemoBundle/Form/Type/FullnameType.php

    namespace Acme\DemoBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilder;

    class FullnameType extends AbstractType
    {
        /**
         * {@inheritdoc}
         */
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder
                ->add('firstName', 'text', array(
                    'attr' => array(
                        'class' => 'firstName'
                    )
                ))
                ->add('lastName', 'text', array(
                    'attr' => array(
                        'class' => 'lastName'
                    )
                ))
            ;
        }

        /**
         * {@inheritdoc}
         */
        public function getName()
        {
            return 'fullname';
        }

    }

Here we can see from the contents of the buildForm function that the field
itself is composed of two fields holding the first name and the last name.
This will lead to two separate inputs within your field.

The getName method returns an identifier for the type which is used
to prevent conflicts with other types. For shared bundles, a good practice
is to start the type name with the bundle alias.

Creating a Template for the Field
---------------------------------

In most situations the custom form field has a specific layout instead of
the layout generated from the contained fields. In this case, we want to show
the firstName and lastName of our field in-line and show possible validation
errors of both fields on top. We start by creating a Twig template that extends
form_div_layout.html.twig (if it does not exist yet) and add our new field.

.. code-block:: html+jinja

    # src/Acme/DemoBundle/Resources/Form/fields.html.twig

    {% extends 'form_div_layout.html.twig' %}

    {% block fullname_widget %}
        <div {{ block('widget_container_attributes') }}>
            {{ form_errors(form.firstName) }}
            {{ form_errors(form.lastName) }}
            {{ form_widget(form.firstName) }} {{ form_widget(form.lastName) }}
        </div>
    {% endblock %}

Make sure the correct widget prefix is used. In this example the name should
be fullname_widget, according to the value returned by getName. Further,
the configuration should point to the custom fields template. Otherwise
the default form template will be used.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/config/resources.yml

        twig:
            form:
                resources:
                    - 'AcmeDemoBundle:Form:fields.html.twig'

    .. code-block:: xml

        # src/Acme/DemoBundle/Resources/config/resources.xml

        <twig:config ...>
                <twig:form>
                    <resource>AcmeDemoBundle:Form:fields.html.twig</resource>
                </twig:form>
                <!-- ... -->
        </twig:config>

Registering the Field Type
--------------------------

Now that we have defined the new field type we need to register it to be
able to use it in our forms. This is achieved by adding it as a new service.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/config/resources.yml

        form.type.fullname:
            class: Acme\DemoBundle\Form\Type\FullnameType
            tags:
              - { name: form.type, alias: fullname }

    .. code-block:: xml

        # src/Acme/DemoBundle/Resources/config/resources.xml

        <service id="form.type.fullname" class="Acme\DemoBundle\Form\Type\FullnameType">
            <tag name="form.type" alias="fullname" />
        </service>

Make sure that the alias tag corresponds with the value returned by the getName
method defined in our custom form class.

And voila, now we have defined a basic form field that we can use and further
extend and customize.