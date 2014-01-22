.. index::
   single: Doctrine; Custom DQL functions

How to Register Custom DQL Functions
====================================

Doctrine allows you to specify custom DQL functions. For more information
on this topic, read Doctrine's cookbook article "`DQL User Defined Functions`_".

In Symfony, you can register your custom DQL functions as follows:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine:
            orm:
                # ...
                dql:
                    string_functions:
                        test_string: Acme\HelloBundle\DQL\StringFunction
                        second_string: Acme\HelloBundle\DQL\SecondStringFunction
                    numeric_functions:
                        test_numeric: Acme\HelloBundle\DQL\NumericFunction
                    datetime_functions:
                        test_datetime: Acme\HelloBundle\DQL\DatetimeFunction

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:orm>
                    <!-- ... -->
                    <doctrine:dql>
                        <doctrine:string-function name="test_string>Acme\HelloBundle\DQL\StringFunction</doctrine:string-function>
                        <doctrine:string-function name="second_string>Acme\HelloBundle\DQL\SecondStringFunction</doctrine:string-function>
                        <doctrine:numeric-function name="test_numeric>Acme\HelloBundle\DQL\NumericFunction</doctrine:numeric-function>
                        <doctrine:datetime-function name="test_datetime>Acme\HelloBundle\DQL\DatetimeFunction</doctrine:datetime-function>
                    </doctrine:dql>
                </doctrine:orm>
            </doctrine:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine', array(
            'orm' => array(
                // ...
                'dql' => array(
                    'string_functions' => array(
                        'test_string'   => 'Acme\HelloBundle\DQL\StringFunction',
                        'second_string' => 'Acme\HelloBundle\DQL\SecondStringFunction',
                    ),
                    'numeric_functions' => array(
                        'test_numeric' => 'Acme\HelloBundle\DQL\NumericFunction',
                    ),
                    'datetime_functions' => array(
                        'test_datetime' => 'Acme\HelloBundle\DQL\DatetimeFunction',
                    ),
                ),
            ),
        ));

.. _`DQL User Defined Functions`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/dql-user-defined-functions.html
