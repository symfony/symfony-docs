.. index::
   single: Form; Custom field type

How to Create a Custom Form Field Type
======================================

Symfony comes with a bunch of core field types available for building forms. However there are situations where we want to create a custom form field type for a specific purpose. In this recipe we assume we need a field definition that holds a person's full name, consisting of two inputs: the first name and the last name. Of course we can achieve this by using two separate fields, but let's assume we want to have a single field definition holding this information.

In order to create the custom field type, first we have to create the class representing the field. In our situation the class holding the field type will be called FullnameType and the file will be stored in the default location for form fields, which is <BundleName>\Form\Extension\Type. Make sure the field extends AbstractType.

.. code-block:: php

    # src/Acme/DemoBundle/Form/Extension/Type/FullnameType.php

    namespace Acme\DemoBundle\Form\Extension\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilder;

    class FullnameType extends AbstractType
    {
        /**
         * {@inheritdoc}
         */
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('firstName', 'text', array(
                'attr' => array(
                    'class' => 'firstName'
                )
            ));
            $builder->add('lastName', 'text', array(
                'attr' => array(
                    'class' => 'lastName'
                )
            ));
        }

        /**
         * {@inheritdoc}
         */
        public function getName()
        {
            return 'fullname';
        }

    }

Here we can see from the contents of the buildForm function that the field itself is composed of two fields holding the first name and the last name. This will lead to two separate inputs within your field. Of course the layout of your field can be further customized to give it the look and feel of a single field.

Now that we have defined the new field type we need to register it to be able to use it in our forms. This is achieved by adding it as a new service.

.. code-block:: yaml

    # src/Acme/DemoBundle/Resources/config/resources.yml

    form.type.fullname:
        class: Acme\DemoBundle\Form\Extension\Type\FullnameType
        tags:
          - { name: form.type, alias: fullname }

And voila, now we have defined a basic form field that we can use and further extend and customize.