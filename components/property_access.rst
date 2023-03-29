The PropertyAccess Component
============================

    The PropertyAccess component provides functions to read and write from/to an
    object or array using a simple string notation.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/property-access

.. include:: /components/require_autoload.rst.inc

Usage
-----

The entry point of this component is the
:method:`Symfony\\Component\\PropertyAccess\\PropertyAccess::createPropertyAccessor`
factory. This factory will create a new instance of the
:class:`Symfony\\Component\\PropertyAccess\\PropertyAccessor` class with the
default configuration::

    use Symfony\Component\PropertyAccess\PropertyAccess;

    $propertyAccessor = PropertyAccess::createPropertyAccessor();

Reading from Arrays
-------------------

You can read an array with the
:method:`Symfony\\Component\\PropertyAccess\\PropertyAccessor::getValue` method.
This is done using the index notation that is used in PHP::

    // ...
    $person = [
        'first_name' => 'Wouter',
    ];

    var_dump($propertyAccessor->getValue($person, '[first_name]')); // 'Wouter'
    var_dump($propertyAccessor->getValue($person, '[age]')); // null

As you can see, the method will return ``null`` if the index does not exist.
But you can change this behavior with the
:method:`Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder::enableExceptionOnInvalidIndex`
method::

    // ...
    $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
        ->enableExceptionOnInvalidIndex()
        ->getPropertyAccessor();

    $person = [
        'first_name' => 'Wouter',
    ];

    // instead of returning null, the code now throws an exception of type
    // Symfony\Component\PropertyAccess\Exception\NoSuchIndexException
    $value = $propertyAccessor->getValue($person, '[age]');

    // You can avoid the exception by adding the nullsafe operator
    $value = $propertyAccessor->getValue($person, '[age?]');

You can also use multi dimensional arrays::

    // ...
    $persons = [
        [
            'first_name' => 'Wouter',
        ],
        [
            'first_name' => 'Ryan',
        ],
    ];

    var_dump($propertyAccessor->getValue($persons, '[0][first_name]')); // 'Wouter'
    var_dump($propertyAccessor->getValue($persons, '[1][first_name]')); // 'Ryan'

.. tip::

    If the key of the array contains a dot ``.`` or a left square bracket ``[``,
    you must escape those characters with a backslash. In the above example,
    if the array key was ``first.name`` instead of ``first_name``, you should
    access its value as follows::

        var_dump($propertyAccessor->getValue($persons, '[0][first\.name]')); // 'Wouter'
        var_dump($propertyAccessor->getValue($persons, '[1][first\.name]')); // 'Ryan'

    Right square brackets ``]`` don't need to be escaped in array keys.

    .. versionadded:: 6.3

        Escaping dots and left square brackets in a property path was
        introduced in Symfony 6.3.

Reading from Objects
--------------------

The ``getValue()`` method is a very robust method, and you can see all of its
features when working with objects.

Accessing public Properties
~~~~~~~~~~~~~~~~~~~~~~~~~~~

To read from properties, use the "dot" notation::

    // ...
    $person = new Person();
    $person->firstName = 'Wouter';

    var_dump($propertyAccessor->getValue($person, 'firstName')); // 'Wouter'

    $child = new Person();
    $child->firstName = 'Bar';
    $person->children = [$child];

    var_dump($propertyAccessor->getValue($person, 'children[0].firstName')); // 'Bar'

.. caution::

    Accessing public properties is the last option used by ``PropertyAccessor``.
    It tries to access the value using the below methods first before using
    the property directly. For example, if you have a public property that
    has a getter method, it will use the getter.

Using Getters
~~~~~~~~~~~~~

The ``getValue()`` method also supports reading using getters. The method will
be created using common naming conventions for getters. It transforms the
property name to camelCase (``first_name`` becomes ``FirstName``) and prefixes
it with ``get``. So the actual method becomes ``getFirstName()``::

    // ...
    class Person
    {
        private $firstName = 'Wouter';

        public function getFirstName()
        {
            return $this->firstName;
        }
    }

    $person = new Person();

    var_dump($propertyAccessor->getValue($person, 'first_name')); // 'Wouter'

Using Hassers/Issers
~~~~~~~~~~~~~~~~~~~~

And it doesn't even stop there. If there is no getter found, the accessor will
look for an isser or hasser. This method is created using the same way as
getters, this means that you can do something like this::

    // ...
    class Person
    {
        private $author = true;
        private $children = [];

        public function isAuthor()
        {
            return $this->author;
        }

        public function hasChildren()
        {
            return 0 !== count($this->children);
        }
    }

    $person = new Person();

    if ($propertyAccessor->getValue($person, 'author')) {
        var_dump('This person is an author');
    }
    if ($propertyAccessor->getValue($person, 'children')) {
        var_dump('This person has children');
    }

This will produce: ``This person is an author``

Accessing a non Existing Property Path
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default a :class:`Symfony\\Component\\PropertyAccess\\Exception\\NoSuchPropertyException`
is thrown if the property path passed to :method:`Symfony\\Component\\PropertyAccess\\PropertyAccessor::getValue`
does not exist. You can change this behavior using the
:method:`Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder::disableExceptionOnInvalidPropertyPath`
method::

    // ...
    class Person
    {
        public $name;
    }

    $person = new Person();

    $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
        ->disableExceptionOnInvalidPropertyPath()
        ->getPropertyAccessor();

    // instead of throwing an exception the following code returns null
    $value = $propertyAccessor->getValue($person, 'birthday');

Accessing Nullable Property Paths
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Consider the following PHP code::

    class Person
    {
    }

    class Comment
    {
        public ?Person $person = null;
        public string $message;
    }

    $comment = new Comment();
    $comment->message = 'test';

Given that ``$person`` is nullable, an object graph like ``comment.person.profile``
will trigger an exception when the ``$person`` property is ``null``. The solution
is to mark all nullable properties with the nullsafe operator (``?``)::

    // This code throws an exception of type
    // Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException
    var_dump($propertyAccessor->getValue($comment, 'person.firstname'));

    // If a property marked with the nullsafe operator is null, the expression is
    // no longer evaluated and null is returned immediately without throwing an exception
    var_dump($propertyAccessor->getValue($comment, 'person?.firstname')); // null

.. versionadded:: 6.2

    The ``?`` nullsafe operator was introduced in Symfony 6.2.

.. _components-property-access-magic-get:

Magic ``__get()`` Method
~~~~~~~~~~~~~~~~~~~~~~~~

The ``getValue()`` method can also use the magic ``__get()`` method::

    // ...
    class Person
    {
        private $children = [
            'Wouter' => [...],
        ];

        public function __get($id)
        {
            return $this->children[$id];
        }
    }

    $person = new Person();

    var_dump($propertyAccessor->getValue($person, 'Wouter')); // [...]

.. note::

    The ``__get()`` method support is enabled by default.
    See `Enable other Features`_ if you want to disable it.

.. _components-property-access-magic-call:

Magic ``__call()`` Method
~~~~~~~~~~~~~~~~~~~~~~~~~

Lastly, ``getValue()`` can use the magic ``__call()`` method, but you need to
enable this feature by using :class:`Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder`::

    // ...
    class Person
    {
        private $children = [
            'wouter' => [...],
        ];

        public function __call($name, $args)
        {
            $property = lcfirst(substr($name, 3));
            if ('get' === substr($name, 0, 3)) {
                return $this->children[$property] ?? null;
            } elseif ('set' === substr($name, 0, 3)) {
                $value = 1 == count($args) ? $args[0] : null;
                $this->children[$property] = $value;
            }
        }
    }

    $person = new Person();

    // enables PHP __call() magic method
    $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
        ->enableMagicCall()
        ->getPropertyAccessor();

    var_dump($propertyAccessor->getValue($person, 'wouter')); // [...]

.. caution::

    The ``__call()`` feature is disabled by default, you can enable it by calling
    :method:`Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder::enableMagicCall`
    see `Enable other Features`_.

Writing to Arrays
-----------------

The ``PropertyAccessor`` class can do more than just read an array, it can
also write to an array. This can be achieved using the
:method:`Symfony\\Component\\PropertyAccess\\PropertyAccessor::setValue` method::

    // ...
    $person = [];

    $propertyAccessor->setValue($person, '[first_name]', 'Wouter');

    var_dump($propertyAccessor->getValue($person, '[first_name]')); // 'Wouter'
    // or
    // var_dump($person['first_name']); // 'Wouter'

.. _components-property-access-writing-to-objects:

Writing to Objects
------------------

The ``setValue()`` method has the same features as the ``getValue()`` method. You
can use setters, the magic ``__set()`` method or properties to set values::

    // ...
    class Person
    {
        public $firstName;
        private $lastName;
        private $children = [];

        public function setLastName($name)
        {
            $this->lastName = $name;
        }

        public function getLastName()
        {
            return $this->lastName;
        }

        public function getChildren()
        {
            return $this->children;
        }

        public function __set($property, $value)
        {
            $this->$property = $value;
        }
    }

    $person = new Person();

    $propertyAccessor->setValue($person, 'firstName', 'Wouter');
    $propertyAccessor->setValue($person, 'lastName', 'de Jong'); // setLastName is called
    $propertyAccessor->setValue($person, 'children', [new Person()]); // __set is called

    var_dump($person->firstName); // 'Wouter'
    var_dump($person->getLastName()); // 'de Jong'
    var_dump($person->getChildren()); // [Person()];

You can also use ``__call()`` to set values but you need to enable the feature,
see `Enable other Features`_::

    // ...
    class Person
    {
        private $children = [];

        public function __call($name, $args)
        {
            $property = lcfirst(substr($name, 3));
            if ('get' === substr($name, 0, 3)) {
                return $this->children[$property] ?? null;
            } elseif ('set' === substr($name, 0, 3)) {
                $value = 1 == count($args) ? $args[0] : null;
                $this->children[$property] = $value;
            }
        }

    }

    $person = new Person();

    // Enable magic __call
    $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
        ->enableMagicCall()
        ->getPropertyAccessor();

    $propertyAccessor->setValue($person, 'wouter', [...]);

    var_dump($person->getWouter()); // [...]

.. note::

    The ``__set()`` method support is enabled by default.
    See `Enable other Features`_ if you want to disable it.

Writing to Array Properties
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``PropertyAccessor`` class allows to update the content of arrays stored in
properties through *adder* and *remover* methods::

    // ...
    class Person
    {
        /**
         * @var string[]
         */
        private $children = [];

        public function getChildren(): array
        {
            return $this->children;
        }

        public function addChild(string $name): void
        {
            $this->children[$name] = $name;
        }

        public function removeChild(string $name): void
        {
            unset($this->children[$name]);
        }
    }

    $person = new Person();
    $propertyAccessor->setValue($person, 'children', ['kevin', 'wouter']);

    var_dump($person->getChildren()); // ['kevin', 'wouter']

The PropertyAccess component checks for methods called ``add<SingularOfThePropertyName>()``
and ``remove<SingularOfThePropertyName>()``. Both methods must be defined.
For instance, in the previous example, the component looks for the ``addChild()``
and ``removeChild()`` methods to access the ``children`` property.
`The String component`_ inflector is used to find the singular of a property name.

If available, *adder* and *remover* methods have priority over a *setter* method.

Using non-standard adder/remover methods
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes, adder and remover methods don't use the standard ``add`` or ``remove`` prefix, like in this example::

    // ...
    class PeopleList
    {
        // ...

        public function joinPeople(string $people): void
        {
            $this->peoples[] = $people;
        }

        public function leavePeople(string $people): void
        {
            foreach ($this->peoples as $id => $item) {
                if ($people === $item) {
                    unset($this->peoples[$id]);
                    break;
                }
            }
        }
    }

    use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
    use Symfony\Component\PropertyAccess\PropertyAccessor;

    $list = new PeopleList();
    $reflectionExtractor = new ReflectionExtractor(null, null, ['join', 'leave']);
    $propertyAccessor = new PropertyAccessor(PropertyAccessor::DISALLOW_MAGIC_METHODS, PropertyAccessor::THROW_ON_INVALID_PROPERTY_PATH, null, $reflectionExtractor, $reflectionExtractor);
    $propertyAccessor->setValue($person, 'peoples', ['kevin', 'wouter']);

    var_dump($person->getPeoples()); // ['kevin', 'wouter']

Instead of calling ``add<SingularOfThePropertyName>()`` and ``remove<SingularOfThePropertyName>()``, the PropertyAccess
component will call ``join<SingularOfThePropertyName>()`` and ``leave<SingularOfThePropertyName>()`` methods.

Checking Property Paths
-----------------------

When you want to check whether
:method:`Symfony\\Component\\PropertyAccess\\PropertyAccessor::getValue` can
safely be called without actually calling that method, you can use
:method:`Symfony\\Component\\PropertyAccess\\PropertyAccessor::isReadable` instead::

    $person = new Person();

    if ($propertyAccessor->isReadable($person, 'firstName')) {
        // ...
    }

The same is possible for :method:`Symfony\\Component\\PropertyAccess\\PropertyAccessor::setValue`:
Call the :method:`Symfony\\Component\\PropertyAccess\\PropertyAccessor::isWritable`
method to find out whether a property path can be updated::

    $person = new Person();

    if ($propertyAccessor->isWritable($person, 'firstName')) {
        // ...
    }

Mixing Objects and Arrays
-------------------------

You can also mix objects and arrays::

    // ...
    class Person
    {
        public $firstName;
        private $children = [];

        public function setChildren($children)
        {
            $this->children = $children;
        }

        public function getChildren()
        {
            return $this->children;
        }
    }

    $person = new Person();

    $propertyAccessor->setValue($person, 'children[0]', new Person);
    // equal to $person->getChildren()[0] = new Person()

    $propertyAccessor->setValue($person, 'children[0].firstName', 'Wouter');
    // equal to $person->getChildren()[0]->firstName = 'Wouter'

    var_dump('Hello '.$propertyAccessor->getValue($person, 'children[0].firstName')); // 'Wouter'
    // equal to $person->getChildren()[0]->firstName

Enable other Features
~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\PropertyAccess\\PropertyAccessor` can be
configured to enable extra features. To do that you could use the
:class:`Symfony\\Component\\PropertyAccess\\PropertyAccessorBuilder`::

    // ...
    $propertyAccessorBuilder = PropertyAccess::createPropertyAccessorBuilder();

    $propertyAccessorBuilder->enableMagicCall(); // enables magic __call
    $propertyAccessorBuilder->enableMagicGet(); // enables magic __get
    $propertyAccessorBuilder->enableMagicSet(); // enables magic __set
    $propertyAccessorBuilder->enableMagicMethods(); // enables magic __get, __set and __call

    $propertyAccessorBuilder->disableMagicCall(); // disables magic __call
    $propertyAccessorBuilder->disableMagicGet(); // disables magic __get
    $propertyAccessorBuilder->disableMagicSet(); // disables magic __set
    $propertyAccessorBuilder->disableMagicMethods(); // disables magic __get, __set and __call

    // checks if magic __call, __get or __set handling are enabled
    $propertyAccessorBuilder->isMagicCallEnabled(); // true or false
    $propertyAccessorBuilder->isMagicGetEnabled(); // true or false
    $propertyAccessorBuilder->isMagicSetEnabled(); // true or false

    // At the end get the configured property accessor
    $propertyAccessor = $propertyAccessorBuilder->getPropertyAccessor();

    // Or all in one
    $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
        ->enableMagicCall()
        ->getPropertyAccessor();

Or you can pass parameters directly to the constructor (not the recommended way)::

    // enable handling of magic __call, __set but not __get:
    $propertyAccessor = new PropertyAccessor(PropertyAccessor::MAGIC_CALL | PropertyAccessor::MAGIC_SET);

.. _`The String component`: https://github.com/symfony/string
