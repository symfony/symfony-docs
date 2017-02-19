.. index::
   single: Doctrine; Custom DQL functions

How to Register custom DQL Functions
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
                        test_string: AppBundle\DQL\StringFunction
                        second_string: AppBundle\DQL\SecondStringFunction
                    numeric_functions:
                        test_numeric: AppBundle\DQL\NumericFunction
                    datetime_functions:
                        test_datetime: AppBundle\DQL\DatetimeFunction

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
                        <doctrine:string-function name="test_string">AppBundle\DQL\StringFunction</doctrine:string-function>
                        <doctrine:string-function name="second_string">AppBundle\DQL\SecondStringFunction</doctrine:string-function>
                        <doctrine:numeric-function name="test_numeric">AppBundle\DQL\NumericFunction</doctrine:numeric-function>
                        <doctrine:datetime-function name="test_datetime">AppBundle\DQL\DatetimeFunction</doctrine:datetime-function>
                    </doctrine:dql>
                </doctrine:orm>
            </doctrine:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        use AppBundle\DQL\StringFunction;
        use AppBundle\DQL\SecondStringFunction;
        use AppBundle\DQL\NumericFunction;
        use AppBundle\DQL\DatetimeFunction;

        $container->loadFromExtension('doctrine', array(
            'orm' => array(
                // ...
                'dql' => array(
                    'string_functions' => array(
                        'test_string'   => StringFunction::class,
                        'second_string' => SecondStringFunction::class,
                    ),
                    'numeric_functions' => array(
                        'test_numeric' => NumericFunction::class,
                    ),
                    'datetime_functions' => array(
                        'test_datetime' => DatetimeFunction::class,
                    ),
                ),
            ),
        ));

.. note::

    In case the ``entity_managers`` were named explicitly, configuring the functions with the
    orm directly will trigger the exception `Unrecognized option "dql" under "doctrine.orm"`.
    The ``dql`` configuration block must be defined under the named entity manager.

    .. configuration-block::

	.. code-block:: yaml

	    # app/config/config.yml
	    doctrine:
		orm:
		    # ...
		    entity_managers:
			example_manager:
			    # Place your functions here
			    dql:
				datetime_functions:
				    test_datetime: AppBundle\DQL\DatetimeFunction

        .. code-block:: xml

            # app/config/config.xml
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/doctrine
                    http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

                <doctrine:config>
                    <doctrine:orm>
                        <!-- ... -->

                        <doctrine:entity-manager name="example_manager">
                            <!-- place your functions here -->
                            <doctrine:dql>
                                <doctrine:datetime-function name="test_datetime">
                                    AppBundle\DQL\DatetimeFunction
                                </doctrine:datetime-function>
                            </doctrine:dql>
                        </doctrine:entity-manager>
                    </doctrine:orm>
                </doctrine:config>
            </container>

        .. code-block:: php

            // app/config/config.php
            use AppBundle\DQL\DatetimeFunction;

            $container->loadFromExtension('doctrine', array(
                'doctrine' => array(
                    'orm' => array(
                        // ...
                        'entity_managers' => array(
                            'example_manager' => array(
                                // place your functions here
                                'dql' => array(
                                    'datetime_functions' => array(
                                        'test_datetime' => DatetimeFunction::class,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ));

.. _`DQL User Defined Functions`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/dql-user-defined-functions.html
