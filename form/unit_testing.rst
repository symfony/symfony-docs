How to Unit Test your Forms
===========================

.. caution::

    This article is intended for developers who create
    :doc:`custom form types </form/create_custom_field_type>`. If you are using
    the :doc:`built-in Symfony form types </reference/forms/types>` or the form
    types provided by third-party bundles, you don't need to unit test them.

The Form component consists of 3 core objects: a form type (implementing
:class:`Symfony\\Component\\Form\\FormTypeInterface`), the
:class:`Symfony\\Component\\Form\\Form` and the
:class:`Symfony\\Component\\Form\\FormView`.

The only class that is usually manipulated by programmers is the form type class
which serves as a form blueprint. It is used to generate the ``Form`` and the
``FormView``. You could test it directly by mocking its interactions with the
factory but it would be complex. It is better to pass it to FormFactory like it
is done in a real application. It is easier to bootstrap and you can trust
the Symfony components enough to use them as a testing base.

There is already a class that you can benefit from for testing:
:class:`Symfony\\Component\\Form\\Test\\TypeTestCase`. It is used to test the
core types and you can use it to test your types too.

.. note::

    Depending on the way you installed your Symfony or Symfony Form component
    the tests may not be downloaded. Use the ``--prefer-source`` option with
    Composer if this is the case.

The Basics
----------

The simplest ``TypeTestCase`` implementation looks like the following::

    // tests/Form/Type/TestedTypeTest.php
    namespace App\Tests\Form\Type;

    use App\Form\Type\TestedType;
    use App\Model\TestObject;
    use Symfony\Component\Form\Test\TypeTestCase;

    class TestedTypeTest extends TypeTestCase
    {
        public function testSubmitValidData(): void
        {
            $formData = [
                'test' => 'test',
                'test2' => 'test2',
            ];

            $model = new TestObject();
            // $model will retrieve data from the form submission; pass it as the second argument
            $form = $this->factory->create(TestedType::class, $model);

            $expected = new TestObject();
            // ...populate $expected properties with the data stored in $formData

            // submit the data to the form directly
            $form->submit($formData);

            // This check ensures there are no transformation failures
            $this->assertTrue($form->isSynchronized());

            // check that $model was modified as expected when the form was submitted
            $this->assertEquals($expected, $model);
        }

        public function testCustomFormView(): void
        {
            $formData = new TestObject();
            // ... prepare the data as you need

            // The initial data may be used to compute custom view variables
            $view = $this->factory->create(TestedType::class, $formData)
                ->createView();

            $this->assertArrayHasKey('custom_var', $view->vars);
            $this->assertSame('expected value', $view->vars['custom_var']);
        }
    }

So, what does it test? Here comes a detailed explanation.

First you verify if the ``FormType`` compiles. This includes basic class
inheritance, the ``buildForm()`` method and options resolution. This should
be the first test you write::

    $form = $this->factory->create(TestedType::class, $formData);

This test checks that none of your data transformers used by the form
produces an error. The :method:`Symfony\\Component\\Form\\FormInterface::isSynchronized`
method is only set to ``false`` if a data transformer throws an exception::

    $form->submit($formData);
    $this->assertTrue($form->isSynchronized());

.. note::

    Don't test the validation: it is applied by a listener that is not
    active in the test case and it relies on validation configuration.
    Instead, unit test your custom constraints directly or read how
    to :ref:`add custom extensions <form_unit_testing-adding_custom_extensions>`
    in the last section of this page.

Next, verify the submission and mapping of the form. The test below checks if
all the fields are correctly specified::

    $this->assertEquals($expected, $formData);

Finally, check the creation of the ``FormView``. You can check that a custom
variable exists and will be available in your form themes::

    $this->assertArrayHasKey('custom_var', $view->vars);
    $this->assertSame('expected value', $view->vars['custom_var']);

.. tip::

    Use `PHPUnit data providers`_ to test multiple form conditions using
    the same test code.

.. caution::

    When your type relies on the ``EntityType``, you should register the
    :class:`Symfony\\Bridge\\Doctrine\\Form\\DoctrineOrmExtension`, which will
    need to mock the ``ManagerRegistry``.

    However, If you cannot use a mock to write your test, you should extend
    the ``KernelTestCase`` instead and use the ``form.factory`` service to
    create the form.

Testing Types Registered as Services
------------------------------------

Your form may be used as a service, as it depends on other services (e.g. the
Doctrine entity manager). In these cases, using the above code won't work, as
the Form component instantiates the form type without passing any arguments
to the constructor.

To solve this, you have to mock the injected dependencies, instantiate your own
form type and use the :class:`Symfony\\Component\\Form\\PreloadedExtension` to
make sure the ``FormRegistry`` uses the created instance::

    // tests/Form/Type/TestedTypeTest.php
    namespace App\Tests\Form\Type;

    use App\Form\Type\TestedType;
    use Doctrine\Persistence\ObjectManager;
    use Symfony\Component\Form\PreloadedExtension;
    use Symfony\Component\Form\Test\TypeTestCase;
    // ...

    class TestedTypeTest extends TypeTestCase
    {
        private MockObject|ObjectManager $objectManager;

        protected function setUp(): void
        {
            // mock any dependencies
            $this->objectManager = $this->createMock(ObjectManager::class);

            parent::setUp();
        }

        protected function getExtensions(): array
        {
            // create a type instance with the mocked dependencies
            $type = new TestedType($this->objectManager);

            return [
                // register the type instances with the PreloadedExtension
                new PreloadedExtension([$type], []),
            ];
        }

        public function testSubmitValidData(): void
        {
            // ...

            // Instead of creating a new instance, the one created in
            // getExtensions() will be used.
            $form = $this->factory->create(TestedType::class, $formData);

            // ... your test
        }
    }

.. _form_unit_testing-adding_custom_extensions:

Adding Custom Extensions
------------------------

It often happens that you use some options that are added by
:doc:`form extensions </form/create_form_type_extension>`. One of the
cases may be the ``ValidatorExtension`` with its ``invalid_message`` option.
The ``TypeTestCase`` only loads the core form extension, which means an
:class:`Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException`
will be raised if you try to test a class that depends on other extensions.
The :method:`Symfony\\Component\\Form\\Test\\TypeTestCase::getExtensions` method
allows you to return a list of extensions to register::

    // tests/Form/Type/TestedTypeTest.php
    namespace App\Tests\Form\Type;

    // ...
    use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
    use Symfony\Component\Validator\Validation;

    class TestedTypeTest extends TypeTestCase
    {
        protected function getExtensions(): array
        {
            $validator = Validation::createValidator();

            // or if you also need to read constraints from annotations
            $validator = Validation::createValidatorBuilder()
                ->enableAttributeMapping()
                ->getValidator();

            return [
                new ValidatorExtension($validator),
            ];
        }

        // ... your tests
    }

.. note::

    By default only the
    :class:`Symfony\\Component\\Form\\Extension\\Core\\CoreExtension` is
    registered in tests. You can find other extensions from the Form component
    in the ``Symfony\Component\Form\Extension`` namespace.

It is also possible to load custom form types, form type extensions or type
guessers using the :method:`Symfony\\Component\\Form\\Test\\FormIntegrationTestCase::getTypes`,
:method:`Symfony\\Component\\Form\\Test\\FormIntegrationTestCase::getTypeExtensions`
and :method:`Symfony\\Component\\Form\\Test\\FormIntegrationTestCase::getTypeGuessers`
methods.

.. _`PHPUnit data providers`: https://docs.phpunit.de/en/9.6/writing-tests-for-phpunit.html#data-providers
