.. index::
   single: Dependency Injection; Parameters

Introduction to Parameters
=================================

You can define parameters in the service container which can then be used
directly or as part of service definitions. This can help to separate out
values that you will want to change more regularly.

Getting and Setting Container Parameters
----------------------------------------

Working with container parameters is straight forward using the container's
accessor methods for parameters. You can check if a parameter has been defined
in the container with::

     $container->hasParameter('mailer.transport');

You can retrieve parameters set in the container with::

    $container->getParameter('mailer.transport');

and set a parameter in the container with::

    $container->setParameter('mailer.transport', 'sendmail');

.. note::

    You can only set a parameter before the container is compiled. To learn
    more about compiling the container see
    :doc:`/components/dependency_injection/compilation`

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
can use them in the config files. You can refer to parameters elsewhere in
the config files by surrounding them with percent (``%``) signs, e.g.
``%mailer.transport%``. One use is for this is to inject the values into your
services. This allows you to configure different versions of services between
applications or multiple services based on the same class but configured
differently within a single application. You could inject the choice of mail
transport into the ``Mailer`` class directly but by making it a parameter it
makes it easier to change rather than being tied up with the service definition:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            mailer.transport: sendmail

        services:
            mailer:
                class:     Mailer
                arguments: [%mailer.transport%]

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

If we were using this elsewhere as well, then it would only need changing
in one place if a different transport was required.

You can also use the parameters in the service definition, for example,
making the class of a service a parameter:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            mailer.transport: sendmail
            mailer.class: Mailer

        services:
            mailer:
                class:     %mailer.class%
                arguments: [%mailer.transport%]

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

    .. code-block:: xml

        <argument type="string">http://symfony.com/?foo=%%s&bar=%%d</argument>

Array Parameters
----------------

Parameters do not need to be flat strings, they can also be arrays. For the XML
format, you need to use the type="collection" attribute for all parameters that are
arrays.

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

Constants as Parameters
-----------------------

The container also has support for setting PHP constants as parameters. To
take advantage of this feature, map the name of your constant  to a parameter
key, and define the type as ``constant``.

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8"?>

    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

        <parameters>
            <parameter key="global.constant.value" type="constant">GLOBAL_CONSTANT</parameter>
            <parameter key="my_class.constant.value" type="constant">My_Class::CONSTANT_NAME</parameter>
        </parameters>
    </container>

.. note::

    This only works for XML configuration. If you're *not* using XML, simply
    import an XML file to take advantage of this functionality:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            imports:
                - { resource: parameters.xml }

        .. code-block:: php

            // app/config/config.php
            $loader->import('parameters.xml');

