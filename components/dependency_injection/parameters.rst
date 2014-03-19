.. index::
   single: DependencyInjection; Parameters

Introduction to Parameters
==========================

You can define parameters in the service container which can then be used
directly or as part of service definitions. This can help to separate out
values that you will want to change more regularly.

Getting and Setting Container Parameters
----------------------------------------

Working with container parameters is straightforward using the container's
accessor methods for parameters. You can check if a parameter has been defined
in the container with::

     $container->hasParameter('mailer.transport');

You can retrieve a parameter set in the container with::

    $container->getParameter('mailer.transport');

and set a parameter in the container with::

    $container->setParameter('mailer.transport', 'sendmail');

.. caution::

    The used ``.`` notation is just a
    :ref:`Symfony convention <service-naming-conventions>` to make parameters
    easier to read. Parameters are just flat key-value elements, they can't be
    organized into a nested array

.. note::

    You can only set a parameter before the container is compiled. To learn
    more about compiling the container see
    :doc:`/components/dependency_injection/compilation`.

Parameters in Configuration Files
---------------------------------

You can also use the ``parameters`` section of a config file to set parameters:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            mailer.transport: sendmail

    .. code-block:: xml

        <parameters>
            <parameter key="mailer.transport">sendmail</parameter>
        </parameters>

    .. code-block:: php

        $container->setParameter('mailer.transport', 'sendmail');

As well as retrieving the parameter values directly from the container you
can use them in the config files. You can refer to parameters elsewhere by
surrounding them with percent (``%``) signs, e.g. ``%mailer.transport%``.
One use for this is to inject the values into your services. This allows
you to configure different versions of services between applications or multiple
services based on the same class but configured differently within a single
application. You could inject the choice of mail transport into the ``Mailer``
class directly. But declaring it as a parameter makes it easier to change
rather than being tied up and hidden with the service definition:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            mailer.transport: sendmail

        services:
            mailer:
                class:     Mailer
                arguments: ['%mailer.transport%']

    .. code-block:: xml

        <parameters>
            <parameter key="mailer.transport">sendmail</parameter>
        </parameters>

        <services>
            <service id="mailer" class="Mailer">
                <argument>%mailer.transport%</argument>
            </service>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('mailer.transport', 'sendmail');
        $container
            ->register('mailer', 'Mailer')
            ->addArgument('%mailer.transport%');

.. caution::

    The values between ``parameter`` tags in XML configuration files are not
    trimmed.

    This means that the following configuration sample will have the value
    ``\n    sendmail\n``:

    .. code-block:: xml

        <parameter key="mailer.transport">
            sendmail
        </parameter>

    In some cases (for constants or class names), this could throw errors. In
    order to prevent this, you must always inline your parameters as follow:

    .. code-block:: xml

        <parameter key="mailer.transport">sendmail</parameter>

If you were using this elsewhere as well, then you would only need to change
the parameter value in one place if needed.

You can also use the parameters in the service definition, for example,
making the class of a service a parameter:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            mailer.transport: sendmail
            mailer.class: Mailer

        services:
            mailer:
                class:     "%mailer.class%"
                arguments: ["%mailer.transport%"]

    .. code-block:: xml

        <parameters>
            <parameter key="mailer.transport">sendmail</parameter>
            <parameter key="mailer.class">Mailer</parameter>
        </parameters>

        <services>
            <service id="mailer" class="%mailer.class%">
                <argument>%mailer.transport%</argument>
            </service>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('mailer.transport', 'sendmail');
        $container->setParameter('mailer.class', 'Mailer');
        $container
            ->register('mailer', '%mailer.class%')
            ->addArgument('%mailer.transport%');

        $container
            ->register('newsletter_manager', 'NewsletterManager')
            ->addMethodCall('setMailer', array(new Reference('mailer')));

.. note::

    The percent sign inside a parameter or argument, as part of the string, must
    be escaped with another percent sign:

    .. configuration-block::

        .. code-block:: yaml

            arguments: ['http://symfony.com/?foo=%%s&bar=%%d']

        .. code-block:: xml

            <argument type="string">http://symfony.com/?foo=%%s&bar=%%d</argument>

        .. code-block:: php

            ->addArgument('http://symfony.com/?foo=%%s&bar=%%d');

.. _component-di-parameters-array:

Array Parameters
----------------

Parameters do not need to be flat strings, they can also contain array values.
For the XML format, you need to use the ``type="collection"`` attribute for
all parameters that are arrays.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        parameters:
            my_mailer.gateways:
                - mail1
                - mail2
                - mail3
            my_multilang.language_fallback:
                en:
                    - en
                    - fr
                fr:
                    - fr
                    - en

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <parameters>
            <parameter key="my_mailer.gateways" type="collection">
                <parameter>mail1</parameter>
                <parameter>mail2</parameter>
                <parameter>mail3</parameter>
            </parameter>
            <parameter key="my_multilang.language_fallback" type="collection">
                <parameter key="en" type="collection">
                    <parameter>en</parameter>
                    <parameter>fr</parameter>
                </parameter>
                <parameter key="fr" type="collection">
                    <parameter>fr</parameter>
                    <parameter>en</parameter>
                </parameter>
            </parameter>
        </parameters>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('my_mailer.gateways', array('mail1', 'mail2', 'mail3'));
        $container->setParameter('my_multilang.language_fallback', array(
            'en' => array('en', 'fr'),
            'fr' => array('fr', 'en'),
        ));

.. _component-di-parameters-constants:

Constants as Parameters
-----------------------

The container also has support for setting PHP constants as parameters. To
take advantage of this feature, map the name of your constant to a parameter
key, and define the type as ``constant``.

.. configuration-block::

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8"?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

            <parameters>
                <parameter key="global.constant.value" type="constant">GLOBAL_CONSTANT</parameter>
                <parameter key="my_class.constant.value" type="constant">My_Class::CONSTANT_NAME</parameter>
            </parameters>
        </container>

    .. code-block:: php

            $container->setParameter('global.constant.value', GLOBAL_CONSTANT);
            $container->setParameter('my_class.constant.value', My_Class::CONSTANT_NAME);

.. note::

    This does not work for YAML configuration. If you're using YAML, you can
    import an XML file to take advantage of this functionality:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            imports:
                - { resource: parameters.xml }

PHP Keywords in XML
-------------------

By default, ``true``, ``false`` and ``null`` in XML are converted to the PHP
keywords (respectively ``true``, ``false`` and ``null``):

.. code-block:: xml

    <parameters>
        <parameter key="mailer.send_all_in_once">false</parameter>
    </parameters>

    <!-- after parsing
    $container->getParameter('mailer.send_all_in_once'); // returns false
    -->

To disable this behavior, use the ``string`` type:

.. code-block:: xml

    <parameters>
        <parameter key="mailer.some_parameter" type="string">true</parameter>
    </parameters>

    <!-- after parsing
    $container->getParameter('mailer.some_parameter'); // returns "true"
    -->

.. note::

    This is not available for YAML and PHP, because they already have built-in
    support for the PHP keywords.

Syntax for Referencing Services
-------------------------------

You can of course also reference services, which looks a bit different in
each format. You can configure the behavior if the referenced service does
not exist. By default, an exception is thrown when a non-existent service
is referenced.

YAML
~~~~

Start the string with  ``@`` or ``@?`` to reference a service in YAML.

* ``@mailer`` references the ``mailer`` service. If the service does not
  exist, an exception will be thrown;
* ``@?mailer`` references the ``mailer`` service. If the service does not
  exist, it will be ignored;

.. code-block:: yaml

    parameters:
        # if 'my_mailer' service isn't defined, an exception will be raised
        foo: @my_mailer

        # if 'my_logger' service isn't defined, 'bar' will be null
        bar: @?my_logger

.. tip::

    Use ``@@`` to escape the ``@`` symbol in YAML. ``@@mailer`` will be
    converted into the string ``"@mailer"`` instead of referencing the
    ``mailer`` service.

XML
~~~

In XML, use the ``service`` type. The behavior if the service does not exist
can be specified using the ``on-invalid`` argument. By default, an exception
is thrown. Valid values for ``on-invalid`` are ``null`` (uses ``null`` in place
of the missing service) or ``ignored`` (very similar, except if used on a
method call, the method call is removed).

.. code-block:: xml

    <parameters>
        <!-- if 'my_mailer' service isn't defined, an exception will be raised -->
        <parameter key="foo" type="service" id="my_mailer" />

        <!-- if 'my_logger' service isn't defined, 'bar' will be null -->
        <parameter key="bar" type="service" id="my_logger" on-invalid="null" />
    </parameters>

PHP
~~~

In PHP, you can use the
:class:`Symfony\\Component\\DependencyInjection\\Reference` class to reference
a service. The invalid behavior is configured using the second constructor
argument and constants from
:class:`Symfony\\Component\\DependencyInjection\\ContainerInterface`.

.. code-block:: php

    use Symfony\Component\DependencyInjection\Reference;

    // if 'my_mailer' service isn't defined, an exception will be raised
    $container->setParameter('foo', new Reference('my_mailer'));

    // if 'my_logger' service isn't defined, 'bar' will be null
    $container->setParameter('bar', new Reference('my_logger',
        ContainerInterface::NULL_ON_INVALID_REFERENCE
    ));
