.. index::
   single: Form; Custom choice type

How to Create a Custom Choice Field Type
========================================

Symfony :doc:`ChoiceType </reference/forms/types/choice>` is a very useful type
that deals with a list of selected options.
The Form component already provides many different choice types, like the
intl types (:doc:`LanguageType </reference/forms/types/language>`, ...) and the
:doc:`EntityType </reference/forms/types/entity>` which loads the choices from
a set of Doctrine entities.

It's also common to want to re-use the same list of choices for different fields.
Creating a custom "choice" field is a great solution - something like::

    use App\Form\Type\CategoryChoiceType;

    // ... from any type
    $builder
        ->add('category', CategoryChoiceType::class, [
            // ... some inherited or custom options for that type
        ])
        // ...
    ;


Creating a Type With Static Custom Choices
------------------------------------------

To create a custom choice type when choices are static, you can do the
following::

    // src/Form/Type/CategoryChoiceType.php
    namespace App\Form\Type;

    use App\Domain\Model;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class CategoryChoiceType extends AbstractType
    {
        /**
         * {@inheritdoc}
         */
        public function getParent()
        {
            // inherits all options, form and view configuration
            // to create expanded or multiple choice lists
            return ChoiceType::class;
        }

        /**
         * {@inheritdoc}
         */
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver
                // Use whatever way you want to get the choices - Model::getCategories() is just an example
                ->setDefault('choices', Model::getCategories())

                // ... override more choice options or define new ones
            ;
        }
    }

.. caution::

     The ``getParent()`` method is used instead of ``extends``.
     This allows the type to inherit from both ``FormType`` and ``ChoiceType``.

Loading Lazily Static Custom Choices
------------------------------------

Sometimes, the callable to define the ``choices`` option can be a heavy process
that could be prevented when the submitted data is optional and empty.
Sometimes it can depend on other options.

The solution is to load the choices lazily using the ``choice_loader`` option,
which accepts a callback::

    use Symfony\Component\Form\ChoiceList\ChoiceList;
    use Symfony\Component\OptionsResolver\Options;

    $resolver
        // use this option instead of the "choices" option
        ->setDefault('choice_loader', ChoiceList::lazy($this, static function() {
            return Model::getCategories();
        }))

        // or if it depends on other options
        ->setDefault('some_option', 'some_default')
        ->setDefault('choice_loader', function(Options $options) {
            $someOption = $options['some_option'];

            return ChoiceList::lazy($this, static function() use ($someOption) {
                return Model::getCategories($someOption);
            }, $someOption);
        }))
    ;

.. note::

    The ``ChoiceList::lazy()`` method creates a cached
    :class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\CallbackChoiceLoader`
    object. The first argument ``$this`` is the type configuring the form, and
    a third argument ``$vary`` can be used as array to pass any value that
    makes the loaded choices different.

Creating a Type With Dynamic Choices
------------------------------------

When loading choices is complex, a callback is not enough and a "real" service
is needed. Fortunately, the Form component provides a
:class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\ChoiceLoaderInterface`.
You can pass any instance to the ``choice_loader`` option to handle things
any way you need. For example, you could leverage this new power to load
categories from an HTTP API. The easiest way is to extend the
:class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\AbstractChoiceLoader`
class, which already implements the interface and avoids triggering your logic
when it is not needed (e.g when the form is submitted empty and valid).
This could look like this::

    // src/Form/ChoiceList/AcmeCategoryLoader.php.
    namespace App\Form\ChoiceList;

    use App\Api\AcmeApi;
    use Symfony\Component\Form\ChoiceList\Loader\AbstractChoiceLoader;

    class AcmeCategoryLoader extends AbstractChoiceLoader
    {
        // this must be passed by the type
        // this loader won't be registered as service
        private $api;
        // define more options if needed
        private $someOption;

        public function __construct(AcmeApi $api, string $someOption)
        {
            $this->api = $api;
            $this->someOption = $someOption;
        }

        protected function loadChoices(): iterable
        {
            return $this->api->loadCategories($this->someOption));
        }

        protected function doLoadChoicesForValues(array $values): array
        {
            return $this->api->loadCategoriesForNames($values, $this->someOption);
        }

        protected function doLoadValuesForChoices(array $choices): array
        {
            $values = [];

            // ... compute string values that must be submitted

            return $values;
        }
    }

Here we implement three protected methods:

``loadChoices(): iterable``

    This method is abstract and is the only one that needs to be implemented.
    It is called when the list is fully loaded (i.e when rendering the view).
    It must return an array or a traversable object, keys are default labels
    unless the :ref:`choice_label <reference-form-choice-label>` option is
    defined.
    Choices can be grouped with keys as group name and nested iterable choices
    in alternative to the :ref:`group_by <reference-form-group-by>` option.

``doLoadChoicesForValues(array $values): array``

    Optional, to improve performance this method is called when the data is
    submitted. You can then load the choices partially, by using the submitted
    values passed as only argument.
    The list is fully loaded by default.

``doLoadValuesForChoices(array $choices): array``

    Optional, as alternative to the
    :ref:`choice_value <reference-form-choice-value>` option.
    You can implement this method to return the string values partially, the
    initial choices are passed as only argument.
    The list is fully loaded by default unless the ``choice_value`` option is
    defined.

Then you need to update the form type to use the new loader instead::

    // src/Form/Type/CategoryChoiceType.php;

    // ... same as before
    use App\Api\AcmeApi;
    use App\Form\ChoiceList\AcmeCategoryLoader;

    class CategoryChoiceType extends AbstractType
    {
        // using the default configuration, the type is a service
        // so the api will be autowired
        private $api;

        public function __construct(AcmeApi $api)
        {
            $this->api = $api;
        }

        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver
                // ... same as before
                // but use the custom loader instead
                ->setDefault('choice_loader', function(Options $options) {
                    $someOption = $options['some_option'];

                    return ChoiceList::loader($this, new AcmeCategoryLoader(
                        $this->api,
                        $someOption
                    ), $someOption);
                })
            ;
        }
    }

Creating a Type With Custom Entities
------------------------------------

When you need to reuse a same set of options with the
:class:`Symfony\\Bridge\\Doctrine\\Form\\Type\\EntityType`, you may need to do
the same as before, with some minor differences::

    // src/Form/Type/CategoryChoiceType.php;

    // ...

    use App\Entity\AcmeCategory;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;

    class CategoryChoiceType extends AbstractType
    {
        public function getParent()
        {
            return EntityType::class;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver
                // can now override options from both entity and choice types
                ->setDefault('class', AcmeCategory::class)

                // you can also customize the "query_builder" option
                ->setDefault('some_option', 'some_default')
                ->setDefault('query_builder', static function(Options $options) {
                    $someOption = $options['some_option'];

                    return static function(AcmeCategoryRepository $repository) use ($someOption) {
                        return $repository->createQueryBuilderWithSomeOption($someOption);
                    };
                })
            ;
        }
    }

Customize Templates
-------------------

Read ":doc:`/form/create_custom_field_type`" on how to customize the form
themes for your new choice field type.
