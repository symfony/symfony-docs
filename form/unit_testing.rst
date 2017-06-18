.. index::
   single: Form; Form testing

How to Unit Test your Forms
===========================

The Form component consists of 3 core objects: a form type (implementing
:class:`Symfony\\Component\\Form\\FormTypeInterface`), the
:class:`Symfony\\Component\\Form\\Form` and the
:class:`Symfony\\Component\\Form\\FormView`.

The only class that is usually manipulated by programmers is the form type class
which serves as a form blueprint. It is used to generate the ``Form`` and the
``FormView``. You could test it directly by mocking its interactions with the
factory but it would be complex. It is better to pass it to FormFactory like it
is done in a real application. It is simple to bootstrap and you can trust
the Symfony components enough to use them as a testing base.

There is already a class that you can benefit from for simple FormTypes
testing: :class:`Symfony\\Component\\Form\\Test\\TypeTestCase`. It is used to
test the core types and you can use it to test your types too.

.. versionadded:: 2.3
    The ``TypeTestCase`` has moved to the ``Symfony\Component\Form\Test``
    namespace in 2.3. Previously, the class was located in
    ``Symfony\Component\Form\Tests\Extension\Core\Type``.

.. note::

    Depending on the way you installed your Symfony or Symfony Form component
    the tests may not be downloaded. Use the ``--prefer-source`` option with
    Composer if this is the case.

The Basics
----------

The simplest ``TypeTestCase`` implementation looks like the following::

    // src/AppBundle/Tests/Form/Type/TestedTypeTest.php
    namespace AppBundle\Tests\Form\Type;

    use AppBundle\Form\Type\TestedType;
    use AppBundle\Model\TestObject;
    use Symfony\Component\Form\Test\TypeTestCase;

    class TestedTypeTest extends TypeTestCase
    {
        public function testSubmitValidData()
        {
            $formData = array(
                'test' => 'test',
                'test2' => 'test2',
            );

            $form = $this->factory->create(TestedType::class);

            $object = TestObject::fromArray($formData);

            // submit the data to the form directly
            $form->submit($formData);

            $this->assertTrue($form->isSynchronized());
            $this->assertEquals($object, $form->getData());

            $view = $form->createView();
            $children = $view->children;

            foreach (array_keys($formData) as $key) {
                $this->assertArrayHasKey($key, $children);
            }
        }
    }

So, what does it test? Here comes a detailed explanation.

First you verify if the ``FormType`` compiles. This includes basic class
inheritance, the ``buildForm()`` function and options resolution. This should
be the first test you write::

    $form = $this->factory->create(TestedType::class);

This test checks that none of your data transformers used by the form
failed. The :method:`Symfony\\Component\\Form\\FormInterface::isSynchronized`
method is only set to ``false`` if a data transformer throws an exception::

    $form->submit($formData);
    $this->assertTrue($form->isSynchronized());

.. note::

    Don't test the validation: it is applied by a listener that is not
    active in the test case and it relies on validation configuration.
    Instead, unit test your custom constraints directly.

Next, verify the submission and mapping of the form. The test below
checks if all the fields are correctly specified::

    $this->assertEquals($object, $form->getData());

Finally, check the creation of the ``FormView``. You should check if all
widgets you want to display are available in the children property::

    $view = $form->createView();
    $children = $view->children;

    foreach (array_keys($formData) as $key) {
        $this->assertArrayHasKey($key, $children);
    }

Testings Types from the Service Container
-----------------------------------------

Your form may be used as a service, as it depends on other services (e.g. the
Doctrine entity manager). In these cases, using the above code won't work, as
the Form component just instantiates the form type without passing any
arguments to the constructor.

To solve this, you have to mock the injected dependencies, instantiate your own
form type and use the :class:`Symfony\\Component\\Form\\PreloadedExtension` to
make sure the ``FormRegistry`` uses the created instance::

    // src/AppBundle/Tests/Form/Type/TestedTypeTests.php
    namespace AppBundle\Tests\Form\Type;

    use AppBundle\Form\Type\TestedType;
    use Doctrine\Common\Persistence\ObjectManager;
    use Symfony\Component\Form\PreloadedExtension;
    use Symfony\Component\Form\Test\TypeTestCase;
    // ...

    class TestedTypeTest extends TypeTestCase
    {
        private $entityManager;

        protected function setUp()
        {
            // mock any dependencies
            $this->entityManager = $this->createMock(ObjectManager::class);

            parent::setUp();
        }

        protected function getExtensions()
        {
            // create a type instance with the mocked dependencies
            $type = new TestedType($this->entityManager);

            return array(
                // register the type instances with the PreloadedExtension
                new PreloadedExtension(array($type), array()),
            );
        }

        public function testSubmitValidData()
        {
            // Instead of creating a new instance, the one created in
            // getExtensions() will be used.
            $form = $this->factory->create(TestedType::class);

            // ... your test
        }
    }

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

    // src/AppBundle/Tests/Form/Type/TestedTypeTests.php
    namespace AppBundle\Tests\Form\Type;

    // ...
    use AppBundle\Form\Type\TestedType;
    use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
    use Symfony\Component\Form\Form;
    use Symfony\Component\Validator\ConstraintViolationList;
    use Symfony\Component\Validator\Mapping\ClassMetadata;
    use Symfony\Component\Validator\Validator\ValidatorInterface;

    class TestedTypeTest extends TypeTestCase
    {
        private $validator;

        protected function getExtensions()
        {
            $this->validator = $this->createMock(ValidatorInterface::class);
            // use getMock() on PHPUnit 5.3 or below
            // $this->validator = $this->getMock(ValidatorInterface::class);
            $this->validator
                ->method('validate')
                ->will($this->returnValue(new ConstraintViolationList()));
            $this->validator
                ->method('getMetadataFor')
                ->will($this->returnValue(new ClassMetadata(Form::class)));

            return array(
                new ValidatorExtension($this->validator),
            );
        }

        // ... your tests
    }

Testing against Different Sets of Data
--------------------------------------

If you are not familiar yet with PHPUnit's `data providers`_, this might be
a good opportunity to use them::

    // src/AppBundle/Tests/Form/Type/TestedTypeTests.php
    namespace AppBundle\Tests\Form\Type;

    use AppBundle\Form\Type\TestedType;
    use Symfony\Component\Form\Test\TypeTestCase;

    class TestedTypeTest extends TypeTestCase
    {
        /**
         * @dataProvider getValidTestData
         */
        public function testForm($data)
        {
            // ... your test
        }

        public function getValidTestData()
        {
            return array(
                array(
                    'data' => array(
                        'test' => 'test',
                        'test2' => 'test2',
                    ),
                ),
                array(
                    'data' => array(),
                ),
                array(
                    'data' => array(
                        'test' => null,
                        'test2' => null,
                    ),
                ),
            );
        }
    }

The code above will run your test three times with 3 different sets of
data. This allows for decoupling the test fixtures from the tests and
easily testing against multiple sets of data.

You can also pass another argument, such as a boolean if the form has to
be synchronized with the given set of data or not etc.

.. _`data providers`: https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers
