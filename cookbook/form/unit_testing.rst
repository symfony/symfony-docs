.. index::
   single: Form; Form testing

Testing forms
=============

The form component consists of 3 core objects: a FormType (extending
:class:`Symfony\\Component\\Form\\AbstractType`), the
:class:`Symfony\\Component\\Form\\Form` and the
:class:`Symfony\\Component\\Form\\FormView`.

The only class that is usually manipulated by programmers is the FormType class
which serves as a form blueprint. It is used to generate the Form and the
FormView. You could test it directly by mocking its interactions with the
factory but it would be complex. It is better to pass it to FormFactory like it
is done in a real application. It is simple to bootstrap and we trust Symfony
components enough to use them as a testing base.

There is already a class that you can benefit from for simple FormTypes
testing, the
:class:`Symfony\\Component\\Form\\Tests\\Extension\\Core\\Type\\TypeTestCase`.
It is used to test the core types and you can use it to test yours too.

.. note::
    
    Depending on the way you installed your Symfony or Symfony Form Component
    the tests may not be downloaded. Use the --prefer-source or --dev options
    with composer if this is the case.

The Basics
----------

The simplest TypeTestCase implementation looks like the following::

    // src/Acme/TestBundle/Tests/Form/Type/TestedTypeTests.php
    namespace Acme\TestBundle\Tests\Form\Type;

    use Acme\TestBundle\Form\Type\TestedType;
    use Acme\TestBundle\Model\TestObject;
    use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

    class TestedTypeTest extends TypeTestCase
    {
        public function testBindValidData()
        {
            $formData = array(
                'test' => 'test',
                'test2' => 'test2',
            );

            $type = new TestedType();
            $form = $this->factory->create($type);

            $object = new TestObject();
            $object->fromArray($formData)

            $form->bind($formData);

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

First we verify if the FormType compiles. This includes basic class
inheritance, the buildForm function and options resolution. This should
be the first test you write::

    $type = new TestedType();
    $form = $this->factory->create($type);


This test checks if none of your DataTransformers used by the form
failed. The isSynchronized is only set to false if a DataTransformer
throws an exception::

    $form->bind($formData);
    $this->assertTrue($form->isSynchronized());

.. note::

    We don't check the validation – it is done by a listener that is not
    active in the test case and it relies on validation configuration.
    You would need to bootstrap the whole kernel to do it. Write
    separate tests to check your validators.

Next we verify the binding and mapping of the form. The test below
checks if all the fields are correctly specified::

    $this->assertEquals($object, $form->getData());

At last we check the creation of the FormView. You should check if all
widgets you want to display are available in the children property::

    $view = $form->createView();
    $children = $view->children;

    foreach (array_keys($formData) as $key) {
        $this->assertArrayHasKey($key, $children);
    }

Adding a Type your form depends on
----------------------------------

Your form may depend on other types that are defined as services. It
would be defined like this::

    // src/Acme/TestBundle/Form/Type/TestedType.php

    // ... the buildForm method
    $builder->add('acme_test_child_type');

To create your form correctly you need to make the type available to the
form factory in your test. The easiest way is to register it manually
before creating the parent form::

    // src/Acme/TestBundle/Tests/Form/Type/TestedTypeTests.php
    namespace Acme\TestBundle\Tests\Form\Type;

    use Acme\TestBundle\Form\Type\TestedType;
    use Acme\TestBundle\Model\TestObject;
    use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

    class TestedTypeTest extends TypeTestCase
    {
        public function testBindValidData()
        {
            $this->factory->addType(new TestChildType());

            $type = new TestedType();
            $form = $this->factory->create($type);
            
            // ... your test
        }
    }

.. caution::

    Make sure the child type you add is well tested. Otherwise you may
    be getting errors that are not related to the form you are currently
    testing but to its children.

Adding custom extensions
------------------------

It often happens that you use some options that are added by form
extensions.  One of the cases may be the ValidatorExtension with its
invalid_message option.  The TypeTestCase loads only the core Form
Extension so an “Invalid option” exception will be raised if you try to
use it for testing a class that depends on other extensions. You need
add the dependencies to the Factory object::

    // src/Acme/TestBundle/Tests/Form/Type/TestedTypeTests.php
    namespace Acme\TestBundle\Tests\Form\Type;

    use Acme\TestBundle\Form\Type\TestedType;
    use Acme\TestBundle\Model\TestObject;
    use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

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

Testing against different sets of data
--------------------------------------

If you are not familiar yet with PHPUnit's `data providers`_ it would be
a good opportunity to use them:: 

    // src/Acme/TestBundle/Tests/Form/Type/TestedTypeTests.php
    namespace Acme\TestBundle\Tests\Form\Type;

    use Acme\TestBundle\Form\Type\TestedType;
    use Acme\TestBundle\Model\TestObject;
    use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

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

.. _`data providers`: http://www.phpunit.de/manual/3.7/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers
