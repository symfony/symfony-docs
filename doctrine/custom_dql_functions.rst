How to Register custom DQL Functions
====================================

Doctrine allows you to specify custom DQL functions. For more information
on this topic, read Doctrine's cookbook article `DQL User Defined Functions`_.

In Symfony, you can register your custom DQL functions as follows:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            orm:
                # ...
                dql:
                    string_functions:
                        test_string: App\DQL\StringFunction
                        second_string: App\DQL\SecondStringFunction
                    numeric_functions:
                        test_numeric: App\DQL\NumericFunction
                    datetime_functions:
                        test_datetime: App\DQL\DatetimeFunction

    .. code-block:: php

        // config/packages/doctrine.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\DQL\DatetimeFunction;
        use App\DQL\NumericFunction;
        use App\DQL\SecondStringFunction;
        use App\DQL\StringFunction;

        return App::config([
            'doctrine' => [
                'orm' => [
                    'dql' => [
                        'string_functions' => [
                            'test_string' => StringFunction::class,
                            'second_string' => SecondStringFunction::class,
                        ],
                        'numeric_functions' => [
                            'test_numeric' => NumericFunction::class,
                        ],
                        'datetime_functions' => [
                            'test_datetime' => DatetimeFunction::class,
                            ],
                    ],
                ],
            ],
        ]);

.. note::

    In case the ``entity_managers`` were named explicitly, configuring the functions with the
    ORM directly will trigger the exception ``Unrecognized option "dql" under "doctrine.orm"``.
    The ``dql`` configuration block must be defined under the named entity manager.

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/doctrine.yaml
            doctrine:
                orm:
                    # ...
                    entity_managers:
                        example_manager:
                            # Place your functions here
                            dql:
                                datetime_functions:
                                    test_datetime: App\DQL\DatetimeFunction

        .. code-block:: php

            // config/packages/doctrine.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use App\DQL\DatetimeFunction;

            return App::config([
                'doctrine' => [
                    'orm' => [
                        'entity_managers' => [
                            'example_manager' => [
                                // Place your functions here
                                'dql' => [
                                    'datetime_functions' => [
                                        'test_datetime' => DatetimeFunction::class,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

.. warning::

    DQL functions are instantiated by Doctrine outside of the Symfony
    :doc:`service container </service_container>` so you can't inject services
    or parameters into a custom DQL function.

.. _`DQL User Defined Functions`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/cookbook/dql-user-defined-functions.html
