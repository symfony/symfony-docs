.. index::
   single: Form; Form testing

How to Unit Test your Forms
===========================

The Form Component consists of 3 core objects: a form type (implementing
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
    ``Symfony\Component\Form\Tests\Core\Extension\Type``.

The Basics
----------

The simplest ``TypeTestCase`` implementation looks like the following::

    // src/Acme/TestBundle/Tests/Form/Type/TestedTypeTests.php
    namespace Acme\TestBundle\Tests\Form\Type;

    use Acme\TestBundle\Form\Type\TestedType;
    use Acme\TestBundle\Model\TestObject;
    use Symfony\Component\Form\Test\TypeTestCase;

    class TestedTypeTest extends TypeTestCase
    {
        public function testSubmitValidData()
        {
            $formData = array(
                'test' => 'test',
                'test2' => 'test2',
            );

            $type = new TestedType();
            $form = $this->factory->create($type);

            $object = new TestObject();
            $object->fromArray($formData);

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

So, what does it test? Let's explain it line by line.

First you verify if the ``FormType`` compiles. This includes basic class
inheritance, the ``buildForm`` function and options resolution. This should
be the first test you write::

    $type = new TestedType();
    $form = $this->factory->create($type);

This test checks that none of your data transformers used by the form
failed. The :method:`Symfony\\Component\\Form\\FormInterface::isSynchronized``
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

Adding a Type your Form depends on
----------------------------------

Your form may depend on other types that are defined as services. It
might look like this::

    // src/Acme/TestBundle/Form/Type/TestedType.php

    // ... the buildForm method
    $builder->add('acme_test_child_type');

To create your form correctly, you need to make the type available to the
form factory in your test. The easiest way is to register it manually
before creating the parent form using PreloadedExtension class::

    // src/Acme/TestBundle/Tests/Form/Type/TestedTypeTests.php
    namespace Acme\TestBundle\Tests\Form\Type;

    use Acme\TestBundle\Form\Type\TestedType;
    use Acme\TestBundle\Model\TestObject;
    use Symfony\Component\Form\Test\TypeTestCase;
    use Symfony\Component\Form\PreloadedExtension;

    class TestedTypeTest extends TypeTestCase
    {
        protected function getExtensions()
        {
            $childType = new TestChildType();
            return array(new PreloadedExtension(array(
                $childType->getName() => $childType,
            ), array()));
        }

        public function testSubmitValidData()
        {
            $type = new TestedType();
            $form = $this->factory->create($type);

            // ... your test
        }
    }

.. caution::

    Make sure the child type you add is well tested. Otherwise you may
    be getting errors that are not related to the form you are currently
    testing but to its children.

Adding custom Extensions
------------------------

It often happens that you use some options that are added by
:doc:`form extensions</cookbook/form/create_form_type_extension>`. One of the
cases may be the ``ValidatorExtension`` with its ``invalid_message`` option.
The ``TypeTestCase`` loads only the core form extension so an "Invalid option"
exception will be raised if you try to use it for testing a class that depends
on other extensions. You need add those extensions to the factory object::

    // src/Acme/TestBundle/Tests/Form/Type/TestedTypeTests.php
    namespace Acme\TestBundle\Tests\Form\Type;

    use Acme\TestBundle\Form\Type\TestedType;
    use Acme\TestBundle\Model\TestObject;
    use Symfony\Component\Form\Test\TypeTestCase;

    class TestedTypeTest extends TypeTestCase
    {
        protected function setUp()
        {
            parent::setUp();

            $this->factory = Forms::createFormFactoryBuilder()
                ->addTypeExtension(
                    new FormTypeValidatorExtension(
                        $this->getMock('Symfony\Component\Validator\ValidatorInterface')
                    )
                )
                ->addTypeGuesser(
                    $this->getMockBuilder(
                        'Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser'
                    )
                        ->disableOriginalConstructor()
                        ->getMock()
                )
                ->getFormFactory();

            $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
            $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
        }

        // ... your tests
    }

Testing against different Sets of Data
--------------------------------------

If you are not familiar yet with PHPUnit's `data providers`_, this might be
a good opportunity to use them::

    // src/Acme/TestBundle/Tests/Form/Type/TestedTypeTests.php
    namespace Acme\TestBundle\Tests\Form\Type;

    use Acme\TestBundle\Form\Type\TestedType;
    use Acme\TestBundle\Model\TestObject;
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

.. _`data providers`: http://www.phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers
