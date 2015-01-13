.. index::
    single: Profiling; Matchers

How to Use Matchers to Enable the Profiler Conditionally
========================================================

By default, the profiler is only activated in the development environment. But
it's imaginable that a developer may want to see the profiler even in
production. Another situation may be that you want to show the profiler only
when an admin has logged in. You can enable the profiler in these situations
by using matchers.

Using the built-in Matcher
--------------------------

Symfony provides a
:class:`built-in matcher <Symfony\\Component\\HttpFoundation\\RequestMatcher>`
which can match paths and IPs. For example, if you want to only show the
profiler when accessing the page with the ``168.0.0.1`` IP, then you can
use this configuration:

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
        <framework:config>
            <framework:profiler
                ip="168.0.0.1"
            />
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'profiler' => array(
                'ip' => '168.0.0.1',
            ),
        ));

You can also set a ``path`` option to define the path on which the profiler
should be enabled. For instance, setting it to ``^/admin/`` will enable the
profiler only for the ``/admin/`` URLs.

Creating a custom Matcher
-------------------------

You can also create a custom matcher. This is a service that checks whether
the profiler should be enabled or not. To create that service, create a class
which implements
:class:`Symfony\\Component\\HttpFoundation\\RequestMatcherInterface`. This
interface requires one method:
:method:`Symfony\\Component\\HttpFoundation\\RequestMatcherInterface::matches`.
This method returns false to disable the profiler and true to enable the
profiler.

To enable the profiler when a ``ROLE_SUPER_ADMIN`` is logged in, you can use
something like::

    // src/Acme/DemoBundle/Profiler/SuperAdminMatcher.php
    namespace Acme\DemoBundle\Profiler;

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

Then, you need to configure the service:

.. configuration-block::

    .. code-block:: yaml

        services:
            acme_demo.profiler.matcher.super_admin:
                class: Acme\DemoBundle\Profiler\SuperAdminMatcher
                arguments: ["@security.authorization_checker"]

    .. code-block:: xml

        <services>
            <service id="acme_demo.profiler.matcher.super_admin"
                class="Acme\DemoBundle\Profiler\SuperAdminMatcher">
                <argument type="service" id="security.authorization_checker" />
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('acme_demo.profiler.matcher.super_admin', new Definition(
            'Acme\DemoBundle\Profiler\SuperAdminMatcher',
            array(new Reference('security.authorization_checker'))
        );

Now the service is registered, the only thing left to do is configure the
profiler to use this service as the matcher:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            profiler:
                matcher:
                    service: acme_demo.profiler.matcher.super_admin

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <!-- ... -->
            <framework:profiler
                service="acme_demo.profiler.matcher.super_admin"
            />
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'profiler' => array(
                'service' => 'acme_demo.profiler.matcher.super_admin',
            ),
        ));
