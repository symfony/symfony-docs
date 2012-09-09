.. index::
   single: Form; Field type extension

How to Create a Field Type Extension
======================================

:doc:`Custom form field types <create_custom_field_type>` are great when
you need field types with a specific purpose, such as a gender selector,
or a VAT number input.

But sometimes, you don't really need to add new field types - you want
to add features on top of existing field types. This is where Field Type
Extensions come in.

Field Type Extensions have 2 main use cases :

#. You want to add a **generic feature to several field types** (such as
   adding a "help" field to every field type)
#. You want to add a **specific feature to a single field type** (such
   as adding a "download" feature to the "file" field type)

In both those cases, it might be possible to achieve your goal with custom
form rendering, or custom form field types. But using field type extensions
can be cleaner (by limiting the amount of business logic in templates)
and more flexible (you can add several type extensions to a single form
type).

Field Type Extensions can achieve most of what custom field types can do,
but instead of being field types of their own, **they plug into existing
field types**.

Imagine that you manage a ``Media`` entity, and that each media is associated
to a file. Your ``Media`` form uses a file type, but when editing the entity,
you would like to see its image automatically rendered next to the file
input.

You could of course do by fine-tuning your edition form template. But form
type extensions allow you to do this in a nice DRY fashion.

Defining the Field Type Extension
---------------------------------

Our first task will be to create the field type extension class. Let's
call it ``ImageTypeExtension``. We will store the class in a file called
``ImageTypeExtension.php``, in the ``<BundleName>\Form\Type`` directory.

When creating a form type extension, you can either implements the
:class:`Symfony\\Component\\Form\\FormTypeExtensionInterface` interface,
or extends the :class:`Symfony\\Component\\Form\\AbstractTypeExtension`
class. Most of the time, you will end up extending the abstract class ;
that's what we will do in this tutorial.::

    // src/Acme/DemoBundle/Form/Type/ImageTypeExtension.php
    namespace Acme\DemoBundle\Form\Type;

    use Symfony\Component\Form\AbstractTypeExtension;
    use Symfony\Component\Form\FormTypeExtensionInterface;

    class ImageTypeExtension extends AbstractTypeExtension
    {

        /**
         * Returns the name of the type being extended.
         *
         * @return string The name of the type being extended
         */
        public function getExtendedType()
        {
            return 'file';
        }

    }

The only method you **must** implement is the ``getExtendedType`` function.
It is used to indicate the name of the form type that will be extended
by your extension.

.. tip::

    The value you return in the ``getExtendedType`` method corresponds
    to the value returned by the ``getName`` method in the form type class
    you wish to extend.

In adition to the ``getExtendedType`` function, you will probably want
to override one of the following methods :

* ``buildForm()``

* ``buildView()``

* ``setDefaultOptions()``

* ``finishView()``

For more information on what those methods do, you can refer to the
:doc:`Creating Custom Field Types </cookbook/form/create_custom_field_type>`
cookbook article.

Creating your Field Type as a Service
-------------------------------------

The next step is to make Symfony aware of your form extension. All you
need to do is to declare it as a service by using the ``form.type_extension``
tag :

.. configuration-block::

    .. code-block:: yaml

        services:
            acme_demo_bundle.image_type_extension:
                class: Acme\DemoBundle\Form\Type\ImageTypeExtension
                tags:
                    - { name: form.type_extension, alias: file }

    .. code-block:: xml

        <service id="acme_demo_bundle.image_type_extension" class="Acme\DemoBundle\Form\Type\ImageTypeExtension">
            <tag name="form.type_extension" alias="file" />
        </service>

    .. code-block:: php

        $container
            ->register('acme_demo_bundle.image_type_extension', 'Acme\DemoBundle\Form\Type\ImageTypeExtension')
            ->addTag('form.type_extension', array('alias' => 'file'))
        ;

The ``alias`` key of the tag is the type of field that this extension should
be applied to. In our case, as we want to extend the ``file`` field type,
we will use ``file`` as an alias.

Adding the extension business logic
-----------------------------------

The goal of our extension is to display a nice image next to file field
types containing image files. For that purpose, we will assume that we
use an approach similar to the one described in
:doc:`How to handle File Uploads with Doctrine </cookbook/doctrine/file_uploads>` :
we have a Media model with a file property (corresponding to the file field
in the form) and a path property (corresponding to the image path in the
database).::

    // src/Acme/DemoBundle/Entity/Media.php
    namespace Acme\DemoBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Validator\Constraints as Assert;

    /**
     * @ORM\Entity
     * @ORM\Table
     */
    class Media
    {

        // ...

        /**
         * @var string
         *
         * @ORM\Column(name="path", type="string", length=255)
         */
        private $path;

        /**
         * @var \Symfony\Component\HttpFoundation\File\UploadedFile
         * @Assert\File(maxSize="2M")
         */
        public $file;

        // ...

        /**
         * Get the image url
         *
         * @return null|string
         */
        public function getWebPath()
        {
            // return the full image url, to be used in templates for example
        }

Our field type extension class will need to do two things :

1) Override the ``setDefaultOptions`` method in order to add an image_path
   option
2) Override the ``buildView`` method in order to pass the image url to
   the view

The logic is the following : when adding a form field of type ``file``,
we will be able to specify a new option : ``image_path``. This option will
tell the file field how to get the actual image url in order to display
it in the view.::

    // src/Acme/DemoBundle/Form/Type/ImageTypeExtension.php
    namespace Acme\DemoBundle\Form\Type;

    use Symfony\Component\Form\AbstractTypeExtension;
    use Symfony\Component\Form\FormTypeExtensionInterface;
    use Symfony\Component\Form\FormView;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;
    use Symfony\Component\Form\Util\PropertyPath;

    class ImageTypeExtension extends AbstractTypeExtension
    {

        /**
         * Returns the name of the type being extended.
         *
         * @return string The name of the type being extended
         */
        public function getExtendedType()
        {
            return 'file';
        }

        /**
         * Add the image_path option
         *
         * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
         */
        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setOptional(array('image_path'));
        }

        /**
         * Pass the image url to the view
         *
         * @param \Symfony\Component\Form\FormView $view
         * @param \Symfony\Component\Form\FormInterface $form
         * @param array $options
         */
        public function buildView(FormView $view, FormInterface $form, array $options)
        {
            if (array_key_exists('image_path', $options)) {
                $parentData = $form->getParent()->getData();

                $propertyPath = new PropertyPath($options['image_path']);
                $imageUrl = $propertyPath->getValue($parentData);
                $view->set('image_url', $imageUrl);
            }
        }

    }

Override the file widget template fragment
------------------------------------------

Each field type is rendered by a template fragment. Those template fragments
can be overriden in order to customize form rendering ; for more information,
see :ref:`cookbook-form-customization-form-themes`.

In our extension class, we have added a new variable (``image_url``), but
we still need to take advantage of this new variable in our templates.
We need to override the ``file_widget`` block :

.. code-block:: html+jinja

    {# src/Acme/DemoBundle/Resources/views/Form/fields.html.twig #}
    {% extends 'form_div_layout.html.twig' %}

    {% block file_widget %}
    {% spaceless %}

    {{ block('form_widget') }}
    {% if image_url is not null %}
        <img src="{{ asset(image_url) }}"/>
    {% endif %}

    {% endspaceless %}
    {% endblock %}

.. note::

    You will need to change your config file or to explicitly specify how
    you want your form to be themed in order for Symfony to use your overriden
    block. See :ref:`cookbook-form-customization-form-themes` for more
    information.

Using the Field Type Extension
------------------------------

From now on, when adding a field of type ``file`` in your form, you can
specify an ``image_path`` option that will be used to display an image
next to the file field. As an example : ::

    // src/Acme/DemoBundle/Form/Type/MediaType.php
    namespace Acme\DemoBundle\Form;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;

    class MediaType extends AbstractType
    {

        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('name', 'text')
                ->add('file', 'file', array('image_path' => 'webPath'));
        }

        public function getName()
        {
            return 'media';
        }
    }

When displaying the form, if the underlying model has already been associated
with an image, you will see it displayed next to the file input.