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

First, create the form type extension class extending from
:class:`Symfony\\Component\\Form\\AbstractTypeExtension` (you can implement
:class:`Symfony\\Component\\Form\\FormTypeExtensionInterface` instead if you prefer)::

    // src/Form/Extension/ImageTypeExtension.php
    namespace App\Form\Extension;

    use Symfony\Component\Form\AbstractTypeExtension;
    use Symfony\Component\Form\Extension\Core\Type\FileType;

    class ImageTypeExtension extends AbstractTypeExtension
    {
        /**
         * Returns an array of extended types.
         */
        public static function getExtendedTypes(): iterable
        {
            // return [FormType::class] to modify (nearly) every field in the system
            return [FileType::class];
        }
    }

The only method you **must** implement is ``getExtendedTypes()``, which is used
to configure *which* field types you want to modify.

Depending on your use case, you may need to override some of the following methods:

* ``buildForm()``
* ``buildView()``
* ``configureOptions()``
* ``finishView()``

For more information on what those methods do, see the
:ref:`custom form field type <form-type-methods-explanation>` article.

Registering your Form Type Extension as a Service
-------------------------------------------------

Form type extensions must be :ref:`registered as services <service-container-creating-service>`
and :doc:`tagged </service_container/tags>` with the ``form.type_extension`` tag.
If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
this is already done for you, thanks to :ref:`autoconfiguration <services-autoconfigure>`.

.. tip::

    There is an optional tag attribute called ``priority``, which defaults to
    ``0`` and controls the order in which the form type extensions are loaded
    (the higher the priority, the earlier an extension is loaded). This is
    useful when you need to guarantee that one extension is loaded before or
    after another extension. Using this attribute requires you to add the
    service configuration explicitly.

Once the extension is registered, any method that you've overridden (e.g.
``buildForm()``) will be called whenever *any* field of the given type
(``FileType``) is built.

.. tip::

    Run the following command to verify that the form type extension was
    successfully registered in the application:

    .. code-block:: terminal

        $ php bin/console debug:form

Adding the extension Business Logic
-----------------------------------

The goal of your extension is to display a nice image next to file input
(when the underlying model contains images). For that purpose, suppose that
you use an approach similar to the one described in
:doc:`How to handle File Uploads with Doctrine </controller/upload_file>`:
you have a Media model with a path property, corresponding to the image path in
the database::

    // src/Entity/Media.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class Media
    {
        // ...

        /**
         * @var string The path - typically stored in the database
         */
        private string $path;

        // ...

        public function getWebPath(): string
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

    // src/Form/Extension/ImageTypeExtension.php
    namespace App\Form\Extension;

    use Symfony\Component\Form\AbstractTypeExtension;
    use Symfony\Component\Form\Extension\Core\Type\FileType;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\Form\FormView;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\PropertyAccess\PropertyAccess;

    class ImageTypeExtension extends AbstractTypeExtension
    {
        public static function getExtendedTypes(): iterable
        {
            // return [FormType::class] to modify (nearly) every field in the system
            return [FileType::class];
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            // makes it legal for FileType fields to have an image_property option
            $resolver->setDefined(['image_property']);
        }

        public function buildView(FormView $view, FormInterface $form, array $options): void
        {
            if (isset($options['image_property'])) {
                // this will be whatever class/entity is bound to your form (e.g. Media)
                $parentData = $form->getParent()->getData();

                $imageUrl = null;
                if (null !== $parentData) {
                    $accessor = PropertyAccess::createPropertyAccessor();
                    $imageUrl = $accessor->getValue($parentData, $options['image_property']);
                }

                // sets an "image_url" variable that will be available when rendering this field
                $view->vars['image_url'] = $imageUrl;
            }
        }

    }

Override the File Widget Template Fragment
------------------------------------------

Each field type is rendered by a template fragment. Those template fragments
can be overridden in order to customize form rendering. For more information,
you can refer to the :ref:`form fragment naming <form-fragment-naming>` rules.

In your extension class, you added a new variable (``image_url``), but
you still need to take advantage of this new variable in your templates.
Specifically, you need to override the ``file_widget`` block:

.. code-block:: html+twig

    {# templates/form/fields.html.twig #}
    {% extends 'form_div_layout.html.twig' %}

    {% block file_widget %}
        {{ block('form_widget') }}
        {% if image_url is defined and image_url is not null %}
            <img src="{{ asset(image_url) }}"/>
        {% endif %}
    {% endblock %}

Be sure to :ref:`configure this form theme template <forms-theming-global>` so that
the form system sees it.

Using the Form Type Extension
-----------------------------

From now on, when adding a field of type ``FileType::class`` to your form, you can
specify an ``image_property`` option that will be used to display an image
next to the file field. For example::

    // src/Form/Type/MediaType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\FileType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;

    class MediaType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('name', TextType::class)
                ->add('file', FileType::class, ['image_property' => 'webPath']);
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

Another option is to return multiple form types in the ``getExtendedTypes()``
method to extend all of them::

    // src/Form/Extension/DateTimeExtension.php
    namespace App\Form\Extension;
    // ...
    use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
    use Symfony\Component\Form\Extension\Core\Type\DateType;
    use Symfony\Component\Form\Extension\Core\Type\TimeType;

    class DateTimeExtension extends AbstractTypeExtension
    {
        // ...

        public static function getExtendedTypes(): iterable
        {
            return [DateTimeType::class, DateType::class, TimeType::class];
        }
    }
