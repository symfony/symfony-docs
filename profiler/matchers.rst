.. index::
    single: Profiling; Matchers

How to Use Matchers to Enable the Profiler Conditionally
========================================================

The Symfony profiler is only activated in the development environment to not hurt
your application performance. However, sometimes it may be useful to conditionally
enable the profiler in the production environment to assist you in debugging
issues. This behavior is implemented with the **Request Matchers**.

Using the built-in Matcher
--------------------------

A request matcher is a class that checks whether a given ``Request`` instance
matches a set of conditions. Symfony provides a
:class:`built-in matcher <Symfony\\Component\\HttpFoundation\\RequestMatcher>`
which matches paths and IPs. For example, if you want to only show the profiler
when accessing the page with the ``168.0.0.1`` IP, then you can use this
configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            profiler:
                matcher:
                    ip: 168.0.0.1

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-Instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >

            <framework:config>
                <!-- ... -->
                <framework:profiler>
                    <framework:matcher ip="168.0.0.1" />
                </framework:profiler>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'profiler' => array(
                'matcher' => array(
                    'ip' => '168.0.0.1',
                )
            ),
        ));

You can also set a ``path`` option to define the path on which the profiler
should be enabled. For instance, setting it to ``^/admin/`` will enable the
profiler only for the URLs which start with ``/admin/``.

Creating a Custom Matcher
-------------------------

Leveraging the concept of Request Matchers you can define a custom matcher to
enable the profiler conditionally in your application. To do so, create a class
which implements
:class:`Symfony\\Component\\HttpFoundation\\RequestMatcherInterface`. This
interface requires one method:
:method:`Symfony\\Component\\HttpFoundation\\RequestMatcherInterface::matches`.
This method returns ``false`` when the request doesn't match the conditions and
``true`` otherwise. Therefore, the custom matcher must return ``false`` to
disable the profiler and ``true`` to enable it.

Suppose that the profiler must be enabled whenever a user with a
``ROLE_SUPER_ADMIN`` is logged in. This is the only code needed for that custom
matcher::

    // src/AppBundle/Profiler/SuperAdminMatcher.php
    namespace AppBundle\Profiler;

    use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\RequestMatcherInterface;

    class SuperAdminMatcher implements RequestMatcherInterface
    {
        protected $authorizationChecker;

        public function __construct(AuthorizationCheckerInterface $authorizationChecker)
        {
            $this->authorizationChecker = $authorizationChecker;
        }

        public function matches(Request $request)
        {
            return $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');
        }
    }

.. versionadded:: 2.6
    The :class:`Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationCheckerInterface` was
    introduced in Symfony 2.6. Prior, you had to use the ``isGranted()`` method of
    :class:`Symfony\\Component\\Security\\Core\\SecurityContextInterface`.

Then, configure a new service and set it as ``private`` because the application
won't use it directly:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.super_admin_matcher:
                class: AppBundle\Profiler\SuperAdminMatcher
                arguments: ['@security.authorization_checker']
                public: false

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <services>
            <service id="app.profiler.matcher.super_admin"
                class="AppBundle\Profiler\SuperAdminMatcher" public="false">
                <argument type="service" id="security.authorization_checker" />
            </service>
        </services>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Profiler\SuperAdminMatcher;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register('app.super_admin_matcher', SuperAdminMatcher::class)
            ->addArgument(new Reference('security.authorization_checker'))
            ->setPublic(false);

.. versionadded:: 2.6
    The ``security.authorization_checker`` service was introduced in Symfony 2.6. Prior
    to Symfony 2.6, you had to use the ``isGranted()`` method of the ``security.context`` service.

Once the service is registered, the only thing left to do is configure the
profiler to use this service as the matcher:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            profiler:
                matcher:
                    service: app.super_admin_matcher

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-Instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >

            <framework:config>
                <!-- ... -->
                <framework:profiler>
                    <framework:matcher service="app.super_admin_matcher" />
                </framework:profiler>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'profiler' => array(
                'matcher' => array(
                    'service' => 'app.super_admin_matcher',
                )
            ),
        ));
