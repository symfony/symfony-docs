.. index::
   single: Form; Form type extension

How to Create a Form Type Extension
===================================

Form type extensions are *incredibly* powerful: they allow you to *modify* any
existing form field types across the entire system.

They have 2 main use-cases:

#. You want to add a **specific feature to a single form type** (such
   as adding a "download" feature to the ``FileType`` field type);
#. You want to add a **generic feature to several types** (such as
   adding a "help" text to every "input text"-like type).

Imagine that you have a ``Media`` entity, and that each media is associated
to a file. Your ``Media`` form uses a file type, but when editing the entity,
you would like to see its image automatically rendered next to the file
input.

Defining the Form Type Extension
--------------------------------

First, create the form type extension class::

    // src/AppBundle/Form/Extension/ImageTypeExtension.php
    namespace AppBundle\Form\Extension;

    use Symfony\Component\Form\AbstractTypeExtension;
    use Symfony\Component\Form\Extension\Core\Type\FileType;

    class ImageTypeExtension extends AbstractTypeExtension
    {
        /**
         * Returns the name of the type being extended.
         *
         * @return string The name of the type being extended
         */
        public function getExtendedType()
        {
            // use FormType::class to modify (nearly) every field in the system
            return FileType::class;
        }
    }

The only method you **must** implement is the ``getExtendedType()`` function.
This is used to configure *which* field or field types you want to modify.

In addition to the ``getExtendedType()`` function, you will probably want
to override one of the following methods:

* ``buildForm()``

* ``buildView()``

* ``configureOptions()``

* ``finishView()``

For more information on what those methods do, see the
:ref:`custom form field type <form-type-methods-explanation>` article.

Registering your Form Type Extension as a Service
-------------------------------------------------

The next step is to make Symfony aware of your extension. Do this by registering
your class as a service and using the  ``form.type_extension`` tag:

.. configuration-block::

    .. code-block:: yaml

        services:
            # ...

            AppBundle\Form\Extension\ImageTypeExtension:
                tags:
                    - { name: form.type_extension, extended_type: Symfony\Component\Form\Extension\Core\Type\FileType }

    .. code-block:: xml

        <service id="AppBundle\Form\Extension\ImageTypeExtension">
            <tag name="form.type_extension" extended-type="Symfony\Component\Form\Extension\Core\Type\FileType" />
        </service>

    .. code-block:: php

        use AppBundle\Form\Extension\ImageTypeExtension;
        use Symfony\Component\Form\Extension\Core\Type\FileType;

        $container->autowire(ImageTypeExtension::class)
            ->addTag('form.type_extension', array(
                'extended_type' => FileType::class
            ))
        ;

The ``extended_type`` key of the tag must match the class you're returning from
the ``getExtendedType()`` method. As *soon* as you do this, any method that you've
overridden (e.g. ``buildForm()``) will be called whenever *any* field of the given
type (``FileType``) is built. Let's see an example next.

.. versionadded:: 3.3
    Prior to Symfony 3.3, you needed to define type extension services as ``public``.
    Starting from Symfony 3.3, you can also define them as ``private``.

.. tip::

    There is an optional tag attribute called ``priority``, which 
    defaults to ``0`` and controls the order in which the form  
    type extensions are loaded (the higher the priority, the earlier 
    an extension is loaded). This is useful when you need to guarantee 
    that one extension is loaded before or after another extension.

    .. versionadded:: 3.2
        The ``priority`` attribute was introduced in Symfony 3.2.

Adding the extension Business Logic
-----------------------------------

The goal of your extension is to display a nice image next to file input
(when the underlying model contains images). For that purpose, suppose that
you use an approach similar to the one described in
:doc:`How to handle File Uploads with Doctrine </controller/upload_file>`:
you have a Media model with a path property, corresponding to the image path in
the database::

    // src/AppBundle/Entity/Media.php
    namespace AppBundle\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class Media
    {
        // ...

        /**
         * @var string The path - typically stored in the database
         */
        private $path;

        // ...

        public function getWebPath()
        {
            // ... $webPath being the full image URL, to be used in templates

            return $webPath;
        }
    }

Your form type extension class will need to do two things in order to extend
the ``FileType::class`` form type:

#. Override the ``configureOptions()`` method so that any ``FileType`` field can
   have an  ``image_property`` option;
#. Override the ``buildView()`` methods to pass the image URL to the view.

For example::

    // src/AppBundle/Form/Extension/ImageTypeExtension.php
    namespace AppBundle\Form\Extension;

    use Symfony\Component\Form\AbstractTypeExtension;
    use Symfony\Component\Form\FormView;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\PropertyAccess\PropertyAccess;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Form\Extension\Core\Type\FileType;

    class ImageTypeExtension extends AbstractTypeExtension
    {
        public function getExtendedType()
        {
            return FileType::class;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            // makes it legal for FileType fields to have an image_property option
            $resolver->setDefined(array('image_property'));
        }

        public function buildView(FormView $view, FormInterface $form, array $options)
        {
            if (isset($options['image_property'])) {
                // this will be whatever class/entity is bound to your form (e.g. Media)
                $parentData = $form->getParent()->getData();

                $imageUrl = null;
                if (null !== $parentData) {
                    $accessor = PropertyAccess::createPropertyAccessor();
                    $imageUrl = $accessor->getValue($parentData, $options['image_property']);
                }

                // set an "image_url" variable that will be available when rendering this field
                $view->vars['image_url'] = $imageUrl;
            }
        }

    }

Override the File Widget Template Fragment
------------------------------------------

Each field type is rendered by a template fragment. Those template fragments
can be overridden in order to customize form rendering. For more information,
you can refer to the :ref:`form-customization-form-themes` article.

In your extension class, you added a new variable (``image_url``), but
you still need to take advantage of this new variable in your templates.
Specifically, you need to override the ``file_widget`` block:

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/fields.html.twig #}
        {% extends 'form_div_layout.html.twig' %}

        {% block file_widget %}
            {% spaceless %}

            {{ block('form_widget') }}
            {% if image_url is not null %}
                <img src="{{ asset(image_url) }}"/>
            {% endif %}

            {% endspaceless %}
        {% endblock %}

    .. code-block:: html+php

        <!-- app/Resources/file_widget.html.php -->
        <?php echo $view['form']->widget($form) ?>
        <?php if (null !== $image_url): ?>
            <img src="<?php echo $view['assets']->getUrl($image_url) ?>"/>
        <?php endif ?>

Be sure to :ref:`configure this form theme template <forms-theming-global>` so that
the form system sees it.

Using the Form Type Extension
-----------------------------

From now on, when adding a field of type ``FileType::class`` to your form, you can
specify an ``image_property`` option that will be used to display an image
next to the file field. For example::

    // src/AppBundle/Form/Type/MediaType.php
    namespace AppBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\FileType;

    class MediaType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('name', TextType::class)
                ->add('file', FileType::class, array('image_property' => 'webPath'));
        }
    }

When displaying the form, if the underlying model has already been associated
with an image, you will see it displayed next to the file input.

Generic Form Type Extensions
----------------------------

You can modify several form types at once by specifying their common parent
(:doc:`/reference/forms/types`). For example, several form types inherit from the
``TextType`` form type (such as ``EmailType``, ``SearchType``, ``UrlType``, etc.).
A form type extension applying to ``TextType`` (i.e. whose ``getExtendedType()``
method returns ``TextType::class``) would apply to all of these form types.

In the same way, since **most** form types natively available in Symfony inherit
from the ``FormType`` form type, a form type extension applying to ``FormType``
would apply to all of these (notable exceptions are the ``ButtonType`` form
types). Also keep in mind that if you created (or are using) a *custom* form type,
it's possible that it does *not* extend ``FormType``, and so your form type extension
may not be applied to it.
