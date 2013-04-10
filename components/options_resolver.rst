.. index::
    single: Options Resolver
    single: Components; OptionsResolver

The OptionsResolver Component
=============================

    The OptionsResolver Component helps you configure objects with option
    arrays. It supports default values, option constraints and lazy options.

Installation
------------

You can install the component in several different ways:

* Use the official Git repository (https://github.com/symfony/OptionsResolver
* :doc:`Install it via Composer</components/using_components>` (``symfony/options-resolver`` on `Packagist`_)

Usage
-----

Imagine you have a ``Person`` class which has 2 options: ``firstName`` and
``lastName``. These options are going to be handled by the OptionsResolver
Component.

First, create the ``Person`` class::

    class Person
    {
        protected $options;

        public function __construct(array $options = array())
        {
        }
    }

You could of course set the ``$options`` value directly on the property. Instead,
use the :class:`Symfony\\Component\\OptionsResolver\\OptionsResolver` class
and let it resolve the options by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::resolve`.
The advantages of doing this will become more obvious as you continue::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    // ...
    public function __construct(array $options = array())
    {
        $resolver = new OptionsResolver();

        $this->options = $resolver->resolve($options);
    }

The ``$options`` property is an instance of
:class:`Symfony\\Component\\OptionsResolver\\Options`, which implements
:phpclass:`ArrayAccess`, :phpclass:`Iterator` and :phpclass:`Countable`. That
means you can handle it just like a normal array::

    // ...
    public function getFirstName()
    {
        return $this->options['firstName'];
    }

    public function getFullName()
    {
        $name = $this->options['firstName'];

        if (isset($this->options['lastName'])) {
            $name .= ' '.$this->options['lastName'];
        }

        return $name;
    }

Now, try to actually use the class::

    $person = new Person(array(
        'firstName' => 'Wouter',
        'lastName'  => 'de Jong',
    ));

    echo $person->getFirstName();

Right now, you'll receive a 
:class:`Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException`,
which tells you that the options ``firstName`` and ``lastName`` do not exist.
This is because you need to configure the ``OptionsResolver`` first, so it
knows which options should be resolved.

.. tip::

    To check if an option exists, you can use the
    :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isKnown`
    function.

A best practice is to put the configuration in a method (e.g.
``setDefaultOptions``). You call this method in the constructor to configure
the ``OptionsResolver`` class::

    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class Person
    {
        protected $options;

        public function __construct(array $options = array())
        {
            $resolver = new OptionsResolver();
            $this->setDefaultOptions($resolver);

            $this->options = $resolver->resolve($options);
        }

        protected function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            // ... configure the resolver, you will learn this in the sections below
        }
    }

Required Options
----------------

Suppose the ``firstName`` option is required: the class can't work without
it. You can set the required options by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setRequired`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('firstName'));
    }

You are now able to use the class without errors::

    $person = new Person(array(
        'firstName' => 'Wouter',
    ));

    echo $person->getFirstName(); // 'Wouter'

If you don't pass a required option, a
:class:`Symfony\\Component\\OptionsResolver\\Exception\\MissingOptionsException`
will be thrown.

To determine if an option is required, you can use the
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isRequired`
method.

Optional Options
----------------

Sometimes, an option can be optional (e.g. the ``lastName`` option in the
``Person`` class). You can configure these options by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setOptional`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setOptional(array('lastName'));
    }

Set Default Values
------------------

Most of the optional options have a default value. You can configure these
options by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setDefaults`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setDefaults(array(
            'age' => 0,
        ));
    }

The default age will be ``0`` now. When the user specifies an age, it gets
replaced. You don't need to configure ``age`` as an optional option. The
``OptionsResolver`` already knows that options with a default value are
optional.

The ``OptionsResolver`` component also has an
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::replaceDefaults`
method. This can be used to override the previous default value. The closure
that is passed has 2 parameters:

* ``$options`` (an :class:`Symfony\\Component\\OptionsResolver\\Options`
  instance), with all the default options
* ``$value``, the previous set default value

Default Values that depend on another Option
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you add a ``gender`` option to the ``Person`` class, whose default
value you guess based on the first name. You can do that easily by using a
Closure as the default value::

    use Symfony\Component\OptionsResolver\Options;

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setDefaults(array(
            'gender' => function (Options $options) {
                if (GenderGuesser::isMale($options['firstName'])) {
                    return 'male';
                }
                
                return 'female';
            },
        ));
    }

.. caution::

    The first argument of the Closure must be typehinted as ``Options``,
    otherwise it is considered as the value.

Configure allowed Values
------------------------

Not all values are valid values for options. For instance, the ``gender``
option can only be ``female`` or ``male``. You can configure these allowed
values by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setAllowedValues`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setAllowedValues(array(
            'gender' => array('male', 'female'),
        ));
    }

There is also an
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::addAllowedValues`
method, which you can use if you want to add an allowed value to the previous
set allowed values.

Configure allowed Types
~~~~~~~~~~~~~~~~~~~~~~~

You can also specify allowed types. For instance, the ``firstName`` option can
be anything, but it must be a string. You can configure these types by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setAllowedTypes`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setAllowedTypes(array(
            'firstName' => 'string',
        ));
    }

Possible types are the one associated with the ``is_*`` php functions or a
class name. You can also pass an array of types as the value. For instance,
``array('null', 'string')`` allows ``firstName`` to be ``null`` or a
``string``.

There is also an
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::addAllowedTypes`
method, which you can use to add an allowed type to the previous allowed types.

Normalize the Options
---------------------

Some values need to be normalized before you can use them. For instance, the
``firstName`` should always start with an uppercase letter. To do that, you can
write normalizers. These Closures will be executed after all options are
passed and return the normalized value. You can configure these normalizers by
calling
:method:`Symfony\\Components\\OptionsResolver\\OptionsResolver::setNormalizers`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setNormalizers(array(
            'firstName' => function (Options $options, $value) {
                return ucfirst($value);
            },
        ));
    }

You see that the closure also get an ``$options`` parameter. Sometimes, you
need to use the other options for normalizing.

.. _Packagist: https://packagist.org/packages/symfony/options-resolver
