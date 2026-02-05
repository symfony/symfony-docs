.. index::
   single: Form; Custom choice type

How to Create a Custom Choice Field Type
=========================================

The :doc:`ChoiceType </reference/forms/types/choice>` is one of the most
powerful form types in Symfony. When you need to reuse the same set of choices
across multiple forms, creating a custom choice type avoids repetition and
centralizes your logic::

    use App\Form\Type\CategoryType;

    $builder->add('category', CategoryType::class);

Static Choices
--------------

When the list of choices is known ahead of time, create a type that inherits
from ``ChoiceType`` and sets the ``choices`` option::

    // src/Form/Type/CategoryType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class CategoryType extends AbstractType
    {
        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'choices' => [
                    'Electronics' => 'electronics',
                    'Books' => 'books',
                    'Clothing' => 'clothing',
                ],
            ]);
        }

        public function getParent(): string
        {
            return ChoiceType::class;
        }
    }

The ``getParent()`` method tells Symfony to use ``ChoiceType`` as the base,
inheriting all its options (``expanded``, ``multiple``, ``placeholder``, etc.)
and rendering logic.

.. tip::

    Read the :doc:`/form/create_custom_field_type` article for more details
    about ``getParent()`` and other methods of the form type interface.

Lazy-Loading Choices
--------------------

When loading choices is expensive (e.g. querying a database or calling an
external service), you can defer the loading until the choices are actually
needed by using the ``choice_loader`` option. This avoids the overhead when
the form is submitted with valid data and no rendering is required::

    // src/Form/Type/CategoryType.php
    namespace App\Form\Type;

    use App\Repository\CategoryRepository;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\ChoiceList\ChoiceList;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class CategoryType extends AbstractType
    {
        public function __construct(
            private CategoryRepository $categoryRepository,
        ) {
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'choice_loader' => ChoiceList::lazy($this, function (): array {
                    return $this->categoryRepository->findAllAsChoices();
                }),
            ]);
        }

        public function getParent(): string
        {
            return ChoiceType::class;
        }
    }

The :method:`Symfony\\Component\\Form\\ChoiceList\\ChoiceList::lazy` method
wraps the callback in a
:class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\CallbackChoiceLoader`
and caches the result. The first argument (``$this``) identifies the type for
caching purposes.

When the loaded choices depend on other form options, pass a ``$vary`` argument
to ensure a separate cache entry per combination::

    use Symfony\Component\Form\ChoiceList\ChoiceList;
    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'is_active' => true,
            'choice_loader' => function (Options $options) {
                $isActive = $options['is_active'];

                return ChoiceList::lazy($this, function () use ($isActive): array {
                    return $this->categoryRepository->findByActive($isActive);
                }, [$isActive]);
            },
        ]);
    }

Creating a Custom Choice Loader
--------------------------------

When the loading logic is too complex for a simple callback, implement a
dedicated choice loader. The easiest way is to extend
:class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\AbstractChoiceLoader`,
which handles caching and avoids loading choices unnecessarily (e.g. when the
form is submitted empty)::

    // src/Form/ChoiceList/ApiCategoryLoader.php
    namespace App\Form\ChoiceList;

    use App\Service\CategoryApiClient;
    use Symfony\Component\Form\ChoiceList\Loader\AbstractChoiceLoader;

    class ApiCategoryLoader extends AbstractChoiceLoader
    {
        public function __construct(
            private CategoryApiClient $api,
            private string $locale,
        ) {
        }

        protected function loadChoices(): iterable
        {
            // called when the full list is needed (e.g. rendering the form)
            return $this->api->fetchCategories($this->locale);
        }
    }

The ``loadChoices()`` method must return an iterable. Keys are used as labels
unless the :ref:`choice_label <reference-form-choice-label>` option is set.
You can also return grouped choices by using nested arrays where keys are group
names.

For better performance on form submission, you can override two additional
methods:

``doLoadChoicesForValues(array $values)``
    Called when the form is submitted. You can load only the choices matching
    the submitted values instead of the full list.

``doLoadValuesForChoices(array $choices)``
    Returns the string values for the given choices. This is an alternative to
    the :ref:`choice_value <reference-form-choice-value>` option.

::

    protected function doLoadChoicesForValues(array $values): array
    {
        // only load the submitted categories instead of all of them
        return $this->api->fetchCategoriesByIds($values, $this->locale);
    }

Then use the custom loader in your form type with
:method:`Symfony\\Component\\Form\\ChoiceList\\ChoiceList::loader` for proper
caching::

    // src/Form/Type/CategoryType.php
    namespace App\Form\Type;

    use App\Form\ChoiceList\ApiCategoryLoader;
    use App\Service\CategoryApiClient;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\ChoiceList\ChoiceList;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class CategoryType extends AbstractType
    {
        public function __construct(
            private CategoryApiClient $api,
        ) {
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'locale' => 'en',
                'choice_loader' => function (Options $options) {
                    return ChoiceList::loader(
                        $this,
                        new ApiCategoryLoader($this->api, $options['locale']),
                        [$options['locale']]
                    );
                },
            ]);
        }

        public function getParent(): string
        {
            return ChoiceType::class;
        }
    }

Using ``choice_lazy`` for Large Datasets
-----------------------------------------

When dealing with a very large number of choices (e.g. thousands of users),
rendering them all in a ``<select>`` element is impractical. Set the
``choice_lazy`` option to ``true`` to only load the choices that are preset as
default values or submitted by the user::

    $builder->add('user', CategoryType::class, [
        'choice_lazy' => true,
    ]);

The form will not render the full list of choices. You are responsible for
providing a JavaScript-based UI (e.g. an autocomplete widget) that lets users
search and select choices dynamically.

.. versionadded:: 7.2

    The ``choice_lazy`` option was introduced in Symfony 7.2.

Reusing EntityType Choices
--------------------------

When your choices come from Doctrine entities, extend
:doc:`EntityType </reference/forms/types/entity>` instead of ``ChoiceType``::

    // src/Form/Type/CategoryType.php
    namespace App\Form\Type;

    use App\Entity\Category;
    use App\Repository\CategoryRepository;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class CategoryType extends AbstractType
    {
        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'class' => Category::class,
                'only_active' => true,
                'query_builder' => function (Options $options) {
                    $onlyActive = $options['only_active'];

                    return function (CategoryRepository $repository) use ($onlyActive) {
                        $qb = $repository->createQueryBuilder('c')
                            ->orderBy('c.name', 'ASC');

                        if ($onlyActive) {
                            $qb->andWhere('c.active = :active')
                                ->setParameter('active', true);
                        }

                        return $qb;
                    };
                },
            ]);

            $resolver->setAllowedTypes('only_active', 'bool');
        }

        public function getParent(): string
        {
            return EntityType::class;
        }
    }

This inherits all ``EntityType`` and ``ChoiceType`` options, so you can use
``expanded``, ``multiple``, ``choice_label``, etc.
