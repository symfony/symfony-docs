.. index::
    single: Options Resolver
    single: Components; OptionsResolver

The OptionsResolver Component
=============================

    The OptionsResolver Component helps you configure objects with option
    arrays. It supports default values, option constraints and lazy options.

Installation
------------

You can install the component in 2 different ways:

* Use the official Git repository (https://github.com/symfony/OptionsResolver
* :doc:`Install it via Composer </components/using_components>` (``symfony/options-resolver`` on `Packagist`_)

Usage
-----

Imagine you have a ``Mailer`` class which has 2 options: ``host`` and
``password``. These options are going to be handled by the OptionsResolver
Component.

First, create the ``Mailer`` class::

    class Mailer
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
    public function getHost()
    {
        return $this->options['host'];
    }

    public function getPassword()
    {
        return $this->options['password'];
    }

Configuring the OptionsResolver
-------------------------------

Now, try to actually use the class::

    $mailer = new Mailer(array(
        'host'     => 'smtp.example.org',
        'password' => 'pa$$word',
    ));

    echo $mailer->getPassword();

Right now, you'll receive a
:class:`Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException`,
which tells you that the options ``host`` and ``password`` do not exist.
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

    class Mailer
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
~~~~~~~~~~~~~~~~

The ``host`` option is required: the class can't work without it. You can set
the required options by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setRequired`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('host'));
    }

You are now able to use the class without errors::

    $mailer = new Mailer(array(
        'host' => 'smtp.example.org',
    ));

    echo $mailer->getHost(); // 'smtp.example.org'

If you don't pass a required option, a
:class:`Symfony\\Component\\OptionsResolver\\Exception\\MissingOptionsException`
will be thrown.

To determine if an option is required, you can use the
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isRequired`
method.

Optional Options
~~~~~~~~~~~~~~~~

Sometimes, an option can be optional (e.g. the ``password`` option in the
``Mailer`` class). You can configure these options by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setOptional`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setOptional(array('password'));
    }

Set Default Values
~~~~~~~~~~~~~~~~~~

Most of the optional options have a default value. You can configure these
options by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setDefaults`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setDefaults(array(
            'username' => 'root',
        ));
    }

This would add a third option - ``username`` - and give it a default value
of ``root``. If the user passes in a ``username`` option, that value will
override this default. You don't need to configure ``username`` as an optional
option. The ``OptionsResolver`` already knows that options with a default
value are optional.

The ``OptionsResolver`` component also has an
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::replaceDefaults`
method. This can be used to override the previous default value. The closure
that is passed has 2 parameters:

* ``$options`` (an :class:`Symfony\\Component\\OptionsResolver\\Options`
  instance), with all the default options
* ``$value``, the previous set default value

Default Values that depend on another Option
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you add a ``port`` option to the ``Mailer`` class, whose default
value you guess based on the host. You can do that easily by using a
Closure as the default value::

    use Symfony\Component\OptionsResolver\Options;

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setDefaults(array(
            'port' => function (Options $options) {
                if (in_array($options['host'], array('127.0.0.1', 'localhost')) {
                    return 80;
                }

                return 25;
            },
        ));
    }

.. caution::

    The first argument of the Closure must be typehinted as ``Options``,
    otherwise it is considered as the value.

Configure allowed Values
~~~~~~~~~~~~~~~~~~~~~~~~

Not all values are valid values for options. Suppose the ``Mailer`` class has
a ``transport`` option, it can only be one of ``sendmail``, ``mail`` or
``smtp``. You can configure these allowed values by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setAllowedValues`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setAllowedValues(array(
            'transport' => array('sendmail', 'mail', 'smtp'),
        ));
    }

There is also an
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::addAllowedValues`
method, which you can use if you want to add an allowed value to the previously
set allowed values.

Configure allowed Types
~~~~~~~~~~~~~~~~~~~~~~~

You can also specify allowed types. For instance, the ``port`` option can
be anything, but it must be an integer. You can configure these types by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setAllowedTypes`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setAllowedTypes(array(
            'port' => 'integer',
        ));
    }

Possible types are the ones associated with the ``is_*`` php functions or a
class name. You can also pass an array of types as the value. For instance,
``array('null', 'string')`` allows ``port`` to be ``null`` or a ``string``.

There is also an
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::addAllowedTypes`
method, which you can use to add an allowed type to the previous allowed types.

Normalize the Options
~~~~~~~~~~~~~~~~~~~~~

Some values need to be normalized before you can use them. For instance,
pretend that the ``host`` should always start with ``http://``. To do that,
you can write normalizers. These Closures will be executed after all options
are passed and should return the normalized value. You can configure these
normalizers by calling
:method:`Symfony\\Components\\OptionsResolver\\OptionsResolver::setNormalizers`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setNormalizers(array(
            'host' => function (Options $options, $value) {
                if ('http://' !== substr($value, 0, 7)) {
                    $value = 'http://'.$value;
                }

                return $value;
            },
        ));
    }

You see that the closure also gets an ``$options`` parameter. Sometimes, you
need to use the other options for normalizing::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setNormalizers(array(
            'host' => function (Options $options, $value) {
                if (!in_array(substr($value, 0, 7), array('http://', 'https://')) {
                    if ($options['ssl']) {
                        $value = 'https://'.$value;
                    } else {
                        $value = 'http://'.$value;
                    }
                }

                return $value;
            },
        ));
    }

.. _Packagist: https://packagist.org/packages/symfony/options-resolver
