.. index::
   single: Validator; Custom validator testing

How to Unit Test your custom constraint
=======================================

.. caution::

    This article is intended for developers who create
    :doc:`custom constraint </validation/custom_constraint>`. If you are using
    the :doc:`built-in Symfony constraints </validation>` or the constraints
    provided by third-party bundles, you don't need to unit test them.

The Validator component consists of 2 core objects while dealing with a custom validator.
- a constraint (extending:class:`Symfony\\Component\\Validator\\Constraint`)
- and the validator (extending:class:`Symfony\\Component\\Validator\\ConstraintValidator`).

.. note::

    Depending on the way you installed your Symfony or Symfony Validator component
    the tests may not be downloaded. Use the ``--prefer-source`` option with
    Composer if this is the case.

The case of example
-------------------

The classic Order - Products example::

    <?php

    class Product
    {
        /** @var string */
        private $type;

        public function __construct(string $type)
        {
            $this->type = $type;
        }

        /**
         * @return string
         */
        public function getType(): string
        {
            return $this->type;
        }
    }

    class Order
    {
        /** @var Product[] */
        private $products;

        public function __construct()
        {
            $this->products = [];
        }

        public function addProduct(Product $product): void
        {
            $this->products[] = $product;
        }

        public function getProducts(): array
        {
            return $this->products;
        }
    }

Let's imagine we want a constraint to check there is less product with same type than a specific number.

The Basics
----------

The constraint class
********************


Basically your job here is to test available options of your constraint.

Our constraint class await a max number, so let's define it.

The constraint class could look like this::

    class LimitProductTypePerOrder extends \Symfony\Component\Validator\Constraint
    {
        public $message = 'There is {{ count }} products with the type "{{ type }}", but the limit is {{ max }}.';
        public $max;

        public function __construct(array $options)
        {
            parent::__construct($options);
            if (!is_int($this->max)) {
                throw new InvalidArgumentException('The max value must be an integer');
            }

            if ($this->max <= 0) {
                throw new InvalidArgumentException('The max value must be strictly positive');
            }
        }
    }

Here you want to verify that the given options to your constraint are correct.
It's mainly a variable type checking, but it could depends of your application too:
::

    class LimitProductTypePerOrderTest extends \PHPUnit\Framework\TestCase
    {
        public function testItAllowMaxInt()
        {
            $constraint = new LimitProductTypePerOrder(['max' => 1]);
            $this->assertEquals(1, $constraint->max);
        }

        public function testItThrowIfMaxIsNotAnInt()
        {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('The max value must be an integer');
            new LimitProductTypePerOrder(['max' => 'abcde']);
        }

        public function testItThrowIfMaxIsNegative()
        {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('The max value must be positive');
            new LimitProductTypePerOrder(['max' => -2]);
        }
    }


Here you want to unit test your custom validator logic. Symfony provide a class ``ConstraintValidatorTestCase`` used internally for testing constraints available by default.
This class avoid code duplication and simplify unit testing of your custom constraint.

It is possible to access to the validator with the ``$this->validator`` property from parent class.

You can use few methods to assert violations during your test

-  ``assertNoViolation()``
-  ``buildViolation($constraint->message)->assertRaised();`` // Don't forget the ->assertRaised(); otherwise your tests will fail.


The Validator class
************************
In this class you will write your domain validation logic:
::

    class LimitProductTypePerOrderValidator extends \Symfony\Component\Validator\ConstraintValidator
    {
        public function validate($order, \Symfony\Component\Validator\Constraint $constraint)
        {
            if (!$constraint instanceof LimitProductTypePerOrder) return;
            if (!$order instanceof Order) return;

            $countPerType = [];
            foreach ($order->getProducts() as $product) {
                if (!isset($countPerType[$product->getType()])) $countPerType[$product->getType()] = 0;

                $countPerType[$product->getType()] = $countPerType[$product->getType()] +=1;
            }

            $errors = array_filter($countPerType, function($count) use($constraint) {
                return $count > $constraint->max;
            });

            foreach ($errors as $productType => $count) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ max }}', $constraint->max)
                    ->setParameter('{{ count }}', $count)
                    ->setParameter('{{ type }}', $productType)
                    ->addViolation();
            }
        }
    }

The Validator test class
************************
In this class you will test your custom validator domain logic:
::


    class LimitProductTypePerOrderValidatorTest extends ConstraintValidatorTestCase
    {
        /** @var Order|\Prophecy\Prophecy\ObjectProphecy */
        private $order;

        protected function setUp(): void
        {
            parent::setUp(); // This is important
            $this->order = $this->prophesize(Order::class);
        }

        protected function createValidator()
        {
            return new LimitProductTypePerOrderValidator();
        }

        public function testItRunOnlyTheGoodConstraintType()
        {
            $randomConstraint = new \Symfony\Component\Validator\Constraint();
            $this->validator->validate($this->order->reveal(), $randomConstraint);

            $this->order->getProducts()->shouldNotBeCalled();
            $this->assertNoViolation();
        }

        public function testAddViolationIfMoreProductsWithSameTypeThanMax()
        {
            $product1 = $this->productMock('my_type');
            $product2 = $this->productMock('my_type');
            $this->order->getProducts()->willReturn([$product1, $product2]);

            $constraint = new LimitProductTypePerOrder(['max' => 1]);
            $this->validator->validate($this->order->reveal(), $constraint);

            $this->buildViolation($constraint->message)
                ->setParameter('{{ max }}', 1)
                ->setParameter('{{ count }}', 2)
                ->setParameter('{{ type }}', 'my_type')
                ->assertRaised();
        }

        public function testItDontAddViolation()
        {
            $product1 = $this->productMock('symfony');
            $product2 = $this->productMock('is');
            $product3 = $this->productMock('awesome');
            $product4 = $this->productMock('!');
            $this->order->getProducts()->willReturn([$product1, $product2, $product3, $product4]);

            $constraint = new LimitProductTypePerOrder(['max' => 1]);
            $this->validator->validate($this->order->reveal(), $constraint);

            $this->assertNoViolation();
        }

        private function productMock(string $type)
        {
            $productMock = $this->prophesize(Product::class);
            $productMock->getType()->willReturn($type);
            return $productMock->reveal();
        }
    }

