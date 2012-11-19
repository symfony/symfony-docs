.. index::
   single: Form; Translate field labels

How to Translate field labels easily
===================================

Translate the field labels in each form can be a tedious task, 
especially if we use **"Keyword Messages"**. 
For example, if we had two different forms with a field named "order", 
the default translation keyword would be exactly the same. 
On the other hand, if We need to write different translations depending the form, 
we need to change the default label in one of them.

This is why you can use a simple solution:
:doc:`Create a Form Type Extension </cookbook/form/create_form_type_extension>` that allows you 
to manipulate the labels and automatically create unique keys::

    // src/Acme/DemoBundle/Form/Extension/LabelTranslationExtension.php
    namespace Acme\DemoBundle\Form\Extension;

    use Symfony\Component\Form\AbstractTypeExtension;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\Form\FormView;

    class LabelTranslationExtension extends AbstractTypeExtension
    {
        /**
         * Manipulates the label.
         *
         * @param \Symfony\Component\Form\FormView $view
         * @param \Symfony\Component\Form\FormInterface $form
         * @param array $options
         */
        public function finishView(FormView $view, FormInterface $form, array $options)
        {
            $view->vars['label'] = str_replace('_', '.', $view->vars['id']);
        }   

        /**
         * Returns the name of the type being extended.
         *
         * @return string The name of the type being extended
         */
        public function getExtendedType()
        {
            return 'form';
        }
    }

Now, declare it as a **service**:

.. configuration-block::

    .. code-block:: yaml

        services:
            acme_demo_bundle.label_translation_extension:
                class: Acme\DemoBundle\Form\Type\LabelTranslationExtension
                tags:
                    - { name: form.type_extension, alias: form }

    .. code-block:: xml

        <service id="acme_demo_bundle.label_translation_extension" class="Acme\DemoBundle\Form\Type\LabelTranslationExtension">
            <tag name="form.type_extension" alias="form" />
        </service>

    .. code-block:: php

        $container
            ->register('acme_demo_bundle.label_translation_extension', 'Acme\DemoBundle\Form\Type\LabelTranslationExtension')
            ->addTag('form.type_extension', array('alias' => 'form'));


.. configuration-block::

    .. code-block:: xml

        <!-- messages.fr.xliff -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>acme.demobundle.exampletype.order</source>
                        <target>Order</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // messages.fr.php
        return array(
            'acme.demobundle.exampletype.order' => 'Order',
        );

        .. code-block:: yaml

        # messages.fr.yml
        acme:
            demobundle:
                exampletype:
                    order: Order

.. note::

    As we see in the code, the most comfortable and clean code is YAML version.

