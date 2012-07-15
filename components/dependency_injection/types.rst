.. index::
   single: Dependency Injection; Injection types

Types of Injection
==================

Making a class's dependencies explicit and requiring that they be injected
into it is a good way of making a class more reusable, testable and decoupled
from others.

There are several ways that the dependencies can be injected. Each injection
point has advantages and disadvantages to consider, as well as different ways
of working with them when using the service container.

Constructor Injection
---------------------

The most common way to inject dependencies is via a class's constructor.
To do this you need to add an argument to the constructor signature to accept
the dependency::

    class NewsletterManager
    {
        protected $mailer;

        public function __construct(Mailer $mailer)
        {
            $this->mailer = $mailer;
        }

        // ...
    }

You can specify what service you would like to inject into this in the
service container configuration:

.. configuration-block::

    .. code-block:: yaml

       services:
            my_mailer:
                # ...
            newsletter_manager:
                class:     NewsletterManager
                arguments: [@my_mailer]

    .. code-block:: xml

        <services>
            <service id="my_mailer" ... >
              <!-- ... -->
            </service>
            <service id="newsletter_manager" class="NewsletterManager">
                <argument type="service" id="my_mailer"/>
            </service>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setDefinition('my_mailer', ...);
        $container->setDefinition('newsletter_manager', new Definition(
            'NewsletterManager',
            array(new Reference('my_mailer'))
        ));

.. tip::

    Type hinting the injected object means that you can be sure that a suitable
    dependency has been injected. By type-hinting, you'll get a clear error
    immediately if an unsuitable dependency is injected. By type hinting
    using an interface rather than a class you can make the choice of dependency
    more flexible. And assuming you only use methods defined in the interface,
    you can gain that flexibility and still safely use the object.

There are several advantages to using constructor injection:

* If the dependency is a requirement and the class cannot work without it
  then injecting it via the constructor ensures it is present when the class
  is used as the class cannot be constructed without it. 

* The constructor is only ever called once when the object is created, so you
  can be sure that the dependency will not change during the object's lifetime.

These advantages do mean that constructor injection is not suitable for working
with optional dependencies. It is also more difficult to use in combination
with class hierarchies: if a class uses constructor injection then extending it 
and overriding the constructor becomes problematic.

Setter Injection
----------------

Another possible injection point into a class is by adding a setter method that
accepts the dependency::

    class NewsletterManager
    {
        protected $mailer;

        public function setMailer(Mailer $mailer)
        {
            $this->mailer = $mailer;
        }

        // ...
    }

.. configuration-block::

    .. code-block:: yaml

       services:
            my_mailer:
                # ...
            newsletter_manager:
                class:     NewsletterManager
                calls:
                    - [ setMailer, [ @my_mailer ] ]

    .. code-block:: xml

        <services>
            <service id="my_mailer" ... >
              <!-- ... -->
            </service>
            <service id="newsletter_manager" class="NewsletterManager">
                <call method="setMailer">
                     <argument type="service" id="my_mailer" />
                </call>
            </service>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setDefinition('my_mailer', ...);
        $container->setDefinition('newsletter_manager', new Definition(
            'NewsletterManager'
        ))->addMethodCall('setMailer', array(new Reference('my_mailer')));

This time the advantages are:

* Setter injection works well with optional dependencies. If you do not need
  the dependency, then just do not call the setter.

* You can call the setter multiple times. This is particularly useful if the
  method adds the dependency to a collection. You can then have a variable number
  of dependencies.

The disadvantages of setter injection are:

* The setter can be called more than just at the time of construction so
  you cannot be sure the dependency is not replaced during the lifetime of the
  object (except by explicitly writing the setter method to check if has already been
  called).

* You cannot be sure the setter will be called and so you need to add checks
  that any required dependencies are injected.

Property Injection
------------------

Another possibility is just setting public fields of the class directly::

    class NewsletterManager
    {
        public $mailer;

        // ...
    }

.. configuration-block::

    .. code-block:: yaml

       services:
            my_mailer:
                # ...
            newsletter_manager:
                class:     NewsletterManager
                properties:
                    mailer: @my_mailer

    .. code-block:: xml

        <services>
            <service id="my_mailer" ... >
              <!-- ... -->
            </service>
            <service id="newsletter_manager" class="NewsletterManager">
                <property name="mailer" type="service" id="my_mailer" />
            </service>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setDefinition('my_mailer', ...);
        $container->setDefinition('newsletter_manager', new Definition(
            'NewsletterManager'
        ))->setProperty('mailer', new Reference('my_mailer')));


There are mainly only disadvantages to using property injection, it is similar
to setter injection but with these additional important problems:

* You cannot control when the dependency is set at all, it can be changed
  at any point in the object's lifetime.

* You cannot use type hinting so you cannot be sure what dependency is injected
  except by writing into the class code to explicitly test the class instance
  before using it.

But, it is useful to know that this can be done with the service container,
especially if you are working with code that is out of your control, such
as in a third party library, which uses public properties for its dependencies.

