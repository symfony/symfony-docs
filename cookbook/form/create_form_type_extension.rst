.. index::
   single: Form; Form type extension

How to Create a Form Type Extension
===================================

:doc:`Custom form field types <create_custom_field_type>` are great when
you need field types with a specific purpose, such as a gender selector,
or a VAT number input.

But sometimes, you don't really need to add new field types - you want
to add features on top of existing types. This is where form type
extensions come in.

Form type extensions have 2 main use-cases:

#. You want to add a **specific feature to a single type** (such
   as adding a "download" feature to the ``FileType`` field type);
#. You want to add a **generic feature to several types** (such as
   adding a "help" text to every "input text"-like type).

It might be possible to achieve your goal with custom form rendering or custom
form field types. But using form type extensions can be cleaner (by limiting the
amount of business logic in templates) and more flexible (you can add several
type extensions to a single form type).

Form type extensions can achieve most of what custom field types can do,
but instead of being field types of their own, **they plug into existing types**.

Imagine that you manage a ``Media`` entity, and that each media is associated
to a file. Your ``Media`` form uses a file type, but when editing the entity,
you would like to see its image automatically rendered next to the file
input.

You could of course do this by customizing how this field is rendered in a
template. But field type extensions allow you to do this in a nice DRY fashion.

Defining the Form Type Extension
--------------------------------

Your first task will be to create the form type extension class (called ``ImageTypeExtension``
in this article). By standard, form extensions usually live in the ``Form\Extension``
directory of one of your bundles.

When creating a form type extension, you can either implement the
:class:`Symfony\\Component\\Form\\FormTypeExtensionInterface` interface
or extend the :class:`Symfony\\Component\\Form\\AbstractTypeExtension`
class. In most cases, it's easier to extend the abstract class::

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
            return FileType::class;
        }
    }

The only method you **must** implement is the ``getExtendedType`` function.
It is used to indicate the name of the form type that will be extended
by your extension.

.. tip::

    The value you return in the ``getExtendedType`` method corresponds
    to the fully qualified class name of the form type class you wish to
    extend.

In addition to the ``getExtendedType`` function, you will probably want
to override one of the following methods:

* ``buildForm()``

* ``buildView()``

* ``configureOptions()``

* ``finishView()``

For more information on what those methods do, you can refer to the
:doc:`Creating Custom Field Types </cookbook/form/create_custom_field_type>`
cookbook article.

Registering your Form Type Extension as a Service
-------------------------------------------------

The next step is to make Symfony aware of your extension. All you
need to do is to declare it as a service by using the ``form.type_extension``
tag:

.. configuration-block::

    .. code-block:: yaml

        services:
            app.image_type_extension:
                class: AppBundle\Form\Extension\ImageTypeExtension
                tags:
                    - { name: form.type_extension, extended_type: Symfony\Component\Form\Extension\Core\Type\FileType }

    .. code-block:: xml

        <service id="app.image_type_extension"
            class="AppBundle\Form\Extension\ImageTypeExtension"
        >
            <tag name="form.type_extension" extended-type="Symfony\Component\Form\Extension\Core\Type\FileType" />
        </service>

    .. code-block:: php

        $container
            ->register(
                'app.image_type_extension',
                'AppBundle\Form\Extension\ImageTypeExtension'
            )
            ->addTag('form.type_extension', array('extended_type' => 'Symfony\Component\Form\Extension\Core\Type\FileType'));

.. versionadded:: 2.8
    The ``extended_type`` option is new in Symfony 2.8. Before, the option was
    called ``alias``. If you're a bundle author and need to support multiple Symfony
    versions, specify *both* ``extended_type`` and ``alias`` (having both will prevent
    any deprecation warnings).

The ``extended_type`` key of the tag is the type of field that this extension should
be applied to. In your case, as you want to extend the ``Symfony\Component\Form\Extension\Core\Type\FileType``
field type, you will use that as the ``extended_type``.

Adding the extension Business Logic
-----------------------------------

The goal of your extension is to display nice images next to file inputs
(when the underlying model contains images). For that purpose, suppose that
you use an approach similar to the one described in
:doc:`How to handle File Uploads with Doctrine </cookbook/doctrine/file_uploads>`:
you have a Media model with a file property (corresponding to the file field
in the form) and a path property (corresponding to the image path in the
database)::

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

        /**
         * @var \Symfony\Component\HttpFoundation\File\UploadedFile
         * @Assert\File(maxSize="2M")
         */
        public $file;

        // ...

        /**
         * Get the image URL
         *
         * @return null|string
         */
        public function getWebPath()
        {
            // ... $webPath being the full image URL, to be used in templates

            return $webPath;
        }
    }

Your form type extension class will need to do two things in order to extend
the ``FileType::class`` form type:

#. Override the ``configureOptions`` method in order to add an ``image_path``
   option;
#. Override the ``buildForm`` and ``buildView`` methods in order to pass the image
   URL to the view.

The logic is the following: when adding a form field of type ``FileType::class``,
you will be able to specify a new option: ``image_path``. This option will
tell the file field how to get the actual image URL in order to display
it in the view::

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
        /**
         * Returns the name of the type being extended.
         *
         * @return string The name of the type being extended
         */
        public function getExtendedType()
        {
            return FileType::class;
        }

        /**
         * Add the image_path option
         *
         * @param OptionsResolver $resolver
         */
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefined(array('image_path'));
        }

        /**
         * Pass the image URL to the view
         *
         * @param FormView $view
         * @param FormInterface $form
         * @param array $options
         */
        public function buildView(FormView $view, FormInterface $form, array $options)
        {
            if (array_key_exists('image_path', $options)) {
                $parentData = $form->getParent()->getData();

                if (null !== $parentData) {
                    $accessor = PropertyAccess::createPropertyAccessor();
                    $imageUrl = $accessor->getValue($parentData, $options['image_path']);
                } else {
                     $imageUrl = null;
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
you can refer to the :ref:`cookbook-form-customization-form-themes` article.

In your extension class, you have added a new variable (``image_url``), but
you still need to take advantage of this new variable in your templates.
Specifically, you need to override the ``file_widget`` block:

.. configuration-block::

    .. code-block:: html+twig

        {# src/AppBundle/Resources/views/Form/fields.html.twig #}
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

        <!-- src/AppBundle/Resources/views/Form/file_widget.html.php -->
        <?php echo $view['form']->widget($form) ?>
        <?php if (null !== $image_url): ?>
            <img src="<?php echo $view['assets']->getUrl($image_url) ?>"/>
        <?php endif ?>

.. note::

    You will need to change your config file or explicitly specify how
    you want your form to be themed in order for Symfony to use your overridden
    block. See :ref:`cookbook-form-customization-form-themes` for more
    information.

Using the Form Type Extension
-----------------------------

From now on, when adding a field of type ``FileType::class`` in your form, you can
specify an ``image_path`` option that will be used to display an image
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
                ->add('file', FileType::class, array('image_path' => 'webPath'));
        }
    }

When displaying the form, if the underlying model has already been associated
with an image, you will see it displayed next to the file input.

Generic Form Type Extensions
----------------------------

You can modify several form types at once by specifying their common parent
(:doc:`/reference/forms/types`). For example, several form types natively
available in Symfony inherit from the ``TextType`` form type (such as ``email``,
``SearchType``, ``UrlType``, etc.). A form type extension applying to ``TextType``
(i.e. whose ``getExtendedType`` method returns ``TextType::class``) would apply
to all of these form types.

In the same way, since **most** form types natively available in Symfony inherit
from the ``FormType`` form type, a form type extension applying to ``FormType``
would apply to all of these.  A notable exception are the ``ButtonType`` form
types. Also keep in mind that a custom form type which extends neither the
``FormType`` nor the ``ButtonType`` type could always be created.
