.. index::
    single: OptionsResolver
    single: Components; OptionsResolver

The OptionsResolver Component
=============================

    The OptionsResolver component is an improved replacement for the
    :phpfunction:`array_replace` PHP function. It allows you to create an
    options system with required options, defaults, validation (type, value),
    normalization and more.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/options-resolver

.. include:: /components/require_autoload.rst.inc

Usage
-----

Imagine you have a ``Mailer`` class which has four options: ``host``,
``username``, ``password`` and ``port``::

    class Mailer
    {
        protected $options;

        public function __construct(array $options = [])
        {
            $this->options = $options;
        }
    }

When accessing the ``$options``, you need to add some boilerplate code to
check which options are set::

    class Mailer
    {
        // ...
        public function sendMail($from, $to)
        {
            $mail = ...;

            $mail->setHost($this->options['host'] ?? 'smtp.example.org');
            $mail->setUsername($this->options['username'] ?? 'user');
            $mail->setPassword($this->options['password'] ?? 'pa$$word');
            $mail->setPort($this->options['port'] ?? 25);

            // ...
        }
    }

Also, the default values of the options are buried in the business logic of your
code. Use the :phpfunction:`array_replace` to fix that::

    class Mailer
    {
        // ...

        public function __construct(array $options = [])
        {
            $this->options = array_replace([
                'host'     => 'smtp.example.org',
                'username' => 'user',
                'password' => 'pa$$word',
                'port'     => 25,
            ], $options);
        }
    }

Now all four options are guaranteed to be set, but you could still make an error
like the following when using the ``Mailer`` class::

    $mailer = new Mailer([
        'usernme' => 'johndoe',  // 'username' is wrongly spelled as 'usernme'
    ]);

No error will be shown. In the best case, the bug will appear during testing,
but the developer will spend time looking for the problem. In the worst case,
the bug might not appear until it's deployed to the live system.

Fortunately, the :class:`Symfony\\Component\\OptionsResolver\\OptionsResolver`
class helps you to fix this problem::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    class Mailer
    {
        // ...

        public function __construct(array $options = [])
        {
            $resolver = new OptionsResolver();
            $resolver->setDefaults([
                'host'     => 'smtp.example.org',
                'username' => 'user',
                'password' => 'pa$$word',
                'port'     => 25,
            ]);

            $this->options = $resolver->resolve($options);
        }
    }

Like before, all options will be guaranteed to be set. Additionally, an
:class:`Symfony\\Component\\OptionsResolver\\Exception\\UndefinedOptionsException`
is thrown if an unknown option is passed::

    $mailer = new Mailer([
        'usernme' => 'johndoe',
    ]);

    // UndefinedOptionsException: The option "usernme" does not exist.
    // Known options are: "host", "password", "port", "username"

The rest of your code can access the values of the options without boilerplate
code::

    // ...
    class Mailer
    {
        // ...

        public function sendMail($from, $to)
        {
            $mail = ...;
            $mail->setHost($this->options['host']);
            $mail->setUsername($this->options['username']);
            $mail->setPassword($this->options['password']);
            $mail->setPort($this->options['port']);
            // ...
        }
    }

It's a good practice to split the option configuration into a separate method::

    // ...
    class Mailer
    {
        // ...

        public function __construct(array $options = [])
        {
            $resolver = new OptionsResolver();
            $this->configureOptions($resolver);

            $this->options = $resolver->resolve($options);
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'host'       => 'smtp.example.org',
                'username'   => 'user',
                'password'   => 'pa$$word',
                'port'       => 25,
                'encryption' => null,
            ]);
        }
    }

First, your code becomes easier to read, especially if the constructor does more
than processing options. Second, sub-classes may now override the
``configureOptions()`` method to adjust the configuration of the options::

    // ...
    class GoogleMailer extends Mailer
    {
        public function configureOptions(OptionsResolver $resolver)
        {
            parent::configureOptions($resolver);

            $resolver->setDefaults([
                'host' => 'smtp.google.com',
                'encryption' => 'ssl',
            ]);
        }
    }

Required Options
~~~~~~~~~~~~~~~~

If an option must be set by the caller, pass that option to
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setRequired`.
For example, to make the ``host`` option required, you can do::

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setRequired('host');
        }
    }

If you omit a required option, a
:class:`Symfony\\Component\\OptionsResolver\\Exception\\MissingOptionsException`
will be thrown::

    $mailer = new Mailer();

    // MissingOptionsException: The required option "host" is missing.

The :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setRequired`
method accepts a single name or an array of option names if you have more than
one required option::

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setRequired(['host', 'username', 'password']);
        }
    }

Use :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isRequired` to find
out if an option is required. You can use
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::getRequiredOptions` to
retrieve the names of all required options::

    // ...
    class GoogleMailer extends Mailer
    {
        public function configureOptions(OptionsResolver $resolver)
        {
            parent::configureOptions($resolver);

            if ($resolver->isRequired('host')) {
                // ...
            }

            $requiredOptions = $resolver->getRequiredOptions();
        }
    }

If you want to check whether a required option is still missing from the default
options, you can use :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isMissing`.
The difference between this and :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isRequired`
is that this method will return false if a required option has already
been set::

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setRequired('host');
        }
    }

    // ...
    class GoogleMailer extends Mailer
    {
        public function configureOptions(OptionsResolver $resolver)
        {
            parent::configureOptions($resolver);

            $resolver->isRequired('host');
            // => true

            $resolver->isMissing('host');
            // => true

            $resolver->setDefault('host', 'smtp.google.com');

            $resolver->isRequired('host');
            // => true

            $resolver->isMissing('host');
            // => false
        }
    }

The method :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::getMissingOptions`
lets you access the names of all missing options.

Type Validation
~~~~~~~~~~~~~~~

You can run additional checks on the options to make sure they were passed
correctly. To validate the types of the options, call
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setAllowedTypes`::

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...

            // specify one allowed type
            $resolver->setAllowedTypes('host', 'string');

            // specify multiple allowed types
            $resolver->setAllowedTypes('port', ['null', 'int']);

            // check all items in an array recursively for a type
            $resolver->setAllowedTypes('dates', 'DateTime[]');
            $resolver->setAllowedTypes('ports', 'int[]');
        }
    }

You can pass any type for which an ``is_<type>()`` function is defined in PHP.
You may also pass fully qualified class or interface names (which is checked
using ``instanceof``). Additionally, you can validate all items in an array
recursively by suffixing the type with ``[]``.

If you pass an invalid option now, an
:class:`Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException`
is thrown::

    $mailer = new Mailer([
        'host' => 25,
    ]);

    // InvalidOptionsException: The option "host" with value "25" is
    // expected to be of type "string"

In sub-classes, you can use :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::addAllowedTypes`
to add additional allowed types without erasing the ones already set.

Value Validation
~~~~~~~~~~~~~~~~

Some options can only take one of a fixed list of predefined values. For
example, suppose the ``Mailer`` class has a ``transport`` option which can be
one of ``sendmail``, ``mail`` and ``smtp``. Use the method
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setAllowedValues`
to verify that the passed option contains one of these values::

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setDefault('transport', 'sendmail');
            $resolver->setAllowedValues('transport', ['sendmail', 'mail', 'smtp']);
        }
    }

If you pass an invalid transport, an
:class:`Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException`
is thrown::

    $mailer = new Mailer([
        'transport' => 'send-mail',
    ]);

    // InvalidOptionsException: The option "transport" has the value
    // "send-mail", but is expected to be one of "sendmail", "mail", "smtp"

For options with more complicated validation schemes, pass a closure which
returns ``true`` for acceptable values and ``false`` for invalid values::

    // ...
    $resolver->setAllowedValues('transport', function ($value) {
        // return true or false
    });

In sub-classes, you can use :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::addAllowedValues`
to add additional allowed values without erasing the ones already set.

Option Normalization
~~~~~~~~~~~~~~~~~~~~

Sometimes, option values need to be normalized before you can use them. For
instance, assume that the ``host`` should always start with ``http://``. To do
that, you can write normalizers. Normalizers are executed after validating an
option. You can configure a normalizer by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setNormalizer`::

    use Symfony\Component\OptionsResolver\Options;

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...

            $resolver->setNormalizer('host', function (Options $options, $value) {
                if ('http://' !== substr($value, 0, 7)) {
                    $value = 'http://'.$value;
                }

                return $value;
            });
        }
    }

The normalizer receives the actual ``$value`` and returns the normalized form.
You see that the closure also takes an ``$options`` parameter. This is useful
if you need to use other options during normalization::

    // ...
    class Mailer
    {
        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setNormalizer('host', function (Options $options, $value) {
                if ('http://' !== substr($value, 0, 7) && 'https://' !== substr($value, 0, 8)) {
                    if ('ssl' === $options['encryption']) {
                        $value = 'https://'.$value;
                    } else {
                        $value = 'http://'.$value;
                    }
                }

                return $value;
            });
        }
    }

To normalize a new allowed value in sub-classes that are being normalized
in parent classes use :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::addNormalizer`.
This way, the ``$value`` argument will receive the previously normalized
value, otherwise you can prepend the new normalizer by passing ``true`` as
third argument.

.. versionadded:: 4.3

    The ``addNormalizer()`` method was introduced in Symfony 4.3.

Default Values that Depend on another Option
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you want to set the default value of the ``port`` option based on the
encryption chosen by the user of the ``Mailer`` class. More precisely, you want
to set the port to ``465`` if SSL is used and to ``25`` otherwise.

You can implement this feature by passing a closure as the default value of
the ``port`` option. The closure receives the options as argument. Based on
these options, you can return the desired default value::

    use Symfony\Component\OptionsResolver\Options;

    // ...
    class Mailer
    {
        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setDefault('encryption', null);

            $resolver->setDefault('port', function (Options $options) {
                if ('ssl' === $options['encryption']) {
                    return 465;
                }

                return 25;
            });
        }
    }

.. caution::

    The argument of the callable must be type hinted as ``Options``. Otherwise,
    the callable itself is considered as the default value of the option.

.. note::

    The closure is only executed if the ``port`` option isn't set by the user
    or overwritten in a sub-class.

A previously set default value can be accessed by adding a second argument to
the closure::

    // ...
    class Mailer
    {
        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setDefaults([
                'encryption' => null,
                'host' => 'example.org',
            ]);
        }
    }

    class GoogleMailer extends Mailer
    {
        public function configureOptions(OptionsResolver $resolver)
        {
            parent::configureOptions($resolver);

            $resolver->setDefault('host', function (Options $options, $previousValue) {
                if ('ssl' === $options['encryption']) {
                    return 'secure.example.org'
                }

                // Take default value configured in the base class
                return $previousValue;
            });
        }
    }

As seen in the example, this feature is mostly useful if you want to reuse the
default values set in parent classes in sub-classes.

Options without Default Values
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In some cases, it is useful to define an option without setting a default value.
This is useful if you need to know whether or not the user *actually* set
an option or not. For example, if you set the default value for an option,
it's not possible to know whether the user passed this value or if it comes
from the default::

    // ...
    class Mailer
    {
        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setDefault('port', 25);
        }

        // ...
        public function sendMail($from, $to)
        {
            // Is this the default value or did the caller of the class really
            // set the port to 25?
            if (25 === $this->options['port']) {
                // ...
            }
        }
    }

You can use :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setDefined`
to define an option without setting a default value. Then the option will only
be included in the resolved options if it was actually passed to
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::resolve`::

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setDefined('port');
        }

        // ...
        public function sendMail($from, $to)
        {
            if (array_key_exists('port', $this->options)) {
                echo 'Set!';
            } else {
                echo 'Not Set!';
            }
        }
    }

    $mailer = new Mailer();
    $mailer->sendMail($from, $to);
    // => Not Set!

    $mailer = new Mailer([
        'port' => 25,
    ]);
    $mailer->sendMail($from, $to);
    // => Set!

You can also pass an array of option names if you want to define multiple
options in one go::

    // ...
    class Mailer
    {
        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setDefined(['port', 'encryption']);
        }
    }

The methods :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isDefined`
and :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::getDefinedOptions`
let you find out which options are defined::

    // ...
    class GoogleMailer extends Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            parent::configureOptions($resolver);

            if ($resolver->isDefined('host')) {
                // One of the following was called:

                // $resolver->setDefault('host', ...);
                // $resolver->setRequired('host');
                // $resolver->setDefined('host');
            }

            $definedOptions = $resolver->getDefinedOptions();
        }
    }

Nested Options
~~~~~~~~~~~~~~

Suppose you have an option named ``spool`` which has two sub-options ``type``
and ``path``. Instead of defining it as a simple array of values, you can pass a
closure as the default value of the ``spool`` option with a
:class:`Symfony\\Component\\OptionsResolver\\OptionsResolver` argument. Based on
this instance, you can define the options under ``spool`` and its desired
default value::

    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefault('spool', function (OptionsResolver $spoolResolver) {
                $spoolResolver->setDefaults([
                    'type' => 'file',
                    'path' => '/path/to/spool',
                ]);
                $spoolResolver->setAllowedValues('type', ['file', 'memory']);
                $spoolResolver->setAllowedTypes('path', 'string');
            });
        }

        public function sendMail($from, $to)
        {
            if ('memory' === $this->options['spool']['type']) {
                // ...
            }
        }
    }

    $mailer = new Mailer([
        'spool' => [
            'type' => 'memory',
        ],
    ]);

Nested options also support required options, validation (type, value) and
normalization of their values. If the default value of a nested option depends
on another option defined in the parent level, add a second ``Options`` argument
to the closure to access to them::

    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefault('sandbox', false);
            $resolver->setDefault('spool', function (OptionsResolver $spoolResolver, Options $parent) {
                $spoolResolver->setDefaults([
                    'type' => $parent['sandbox'] ? 'memory' : 'file',
                    // ...
                ]);
            });
        }
    }

.. caution::

    The arguments of the closure must be type hinted as ``OptionsResolver`` and
    ``Options`` respectively. Otherwise, the closure itself is considered as the
    default value of the option.

In same way, parent options can access to the nested options as normal arrays::

    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefault('spool', function (OptionsResolver $spoolResolver) {
                $spoolResolver->setDefaults([
                    'type' => 'file',
                    // ...
                ]);
            });
            $resolver->setDefault('profiling', function (Options $options) {
                return 'file' === $options['spool']['type'];
            });
        }
    }

.. note::

    The fact that an option is defined as nested means that you must pass
    an array of values to resolve it at runtime.

Deprecating the Option
~~~~~~~~~~~~~~~~~~~~~~

Once an option is outdated or you decided not to maintain it anymore, you can
deprecate it using the :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setDeprecated`
method::

    $resolver
        ->setDefined(['hostname', 'host'])
        // this outputs the following generic deprecation message:
        // The option "hostname" is deprecated.
        ->setDeprecated('hostname')

        // you can also pass a custom deprecation message
        ->setDeprecated('hostname', 'The option "hostname" is deprecated, use "host" instead.')
    ;

.. note::

    The deprecation message will be triggered only if the option is being used
    somewhere, either its value is provided by the user or the option is evaluated
    within closures of lazy options and normalizers.

.. note::

    When using an option deprecated by you in your own library, you can pass
    ``false`` as the second argument of the
    :method:`Symfony\\Component\\OptionsResolver\\Options::offsetGet()` method
    to not trigger the deprecation warning.

Instead of passing the message, you may also pass a closure which returns
a string (the deprecation message) or an empty string to ignore the deprecation.
This closure is useful to only deprecate some of the allowed types or values of
the option::

    $resolver
        ->setDefault('encryption', null)
        ->setDefault('port', null)
        ->setAllowedTypes('port', ['null', 'int'])
        ->setDeprecated('port', function (Options $options, $value) {
            if (null === $value) {
                return 'Passing "null" to option "port" is deprecated, pass an integer instead.';
            }

            // deprecation may also depend on another option
            if ('ssl' === $options['encryption'] && 456 !== $value) {
                return 'Passing a different port than "456" when the "encryption" option is set to "ssl" is deprecated.';
            }

            return '';
        })
    ;

.. note::

    Deprecation based on the value is triggered only when the option is provided
    by the user.

This closure receives as argument the value of the option after validating it
and before normalizing it when the option is being resolved.

Performance Tweaks
~~~~~~~~~~~~~~~~~~

With the current implementation, the ``configureOptions()`` method will be
called for every single instance of the ``Mailer`` class. Depending on the
amount of option configuration and the number of created instances, this may add
noticeable overhead to your application. If that overhead becomes a problem, you
can change your code to do the configuration only once per class::

    // ...
    class Mailer
    {
        private static $resolversByClass = [];

        protected $options;

        public function __construct(array $options = [])
        {
            // What type of Mailer is this, a Mailer, a GoogleMailer, ... ?
            $class = get_class($this);

            // Was configureOptions() executed before for this class?
            if (!isset(self::$resolversByClass[$class])) {
                self::$resolversByClass[$class] = new OptionsResolver();
                $this->configureOptions(self::$resolversByClass[$class]);
            }

            $this->options = self::$resolversByClass[$class]->resolve($options);
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
        }
    }

Now the :class:`Symfony\\Component\\OptionsResolver\\OptionsResolver` instance
will be created once per class and reused from that on. Be aware that this may
lead to memory leaks in long-running applications, if the default options contain
references to objects or object graphs. If that's the case for you, implement a
method ``clearOptionsConfig()`` and call it periodically::

    // ...
    class Mailer
    {
        private static $resolversByClass = [];

        public static function clearOptionsConfig()
        {
            self::$resolversByClass = [];
        }

        // ...
    }

That's it! You now have all the tools and knowledge needed to process
options in your code.

.. _Packagist: https://packagist.org/packages/symfony/options-resolver
.. _CHANGELOG: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/OptionsResolver/CHANGELOG.md#260
