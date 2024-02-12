Migrating an Existing Application to Symfony
============================================

When you have an existing application that was not built with Symfony,
you might want to move over parts of that application without rewriting
the existing logic completely. For those cases there is a pattern called
`Strangler Fig Application`_. The basic idea of this pattern is to create a
new application that gradually takes over functionality from an existing
application. This migration approach can be implemented with Symfony in
various ways and has some benefits over a rewrite such as being able
to introduce new features in the existing application and reducing risk
by avoiding a "big bang"-release for the new application.

.. admonition:: Screencast
    :class: screencast

    The topic of migrating from an existing application towards Symfony is
    sometimes discussed during conferences. For example the talk
    `Modernizing with Symfony`_ reiterates some of the points from this page.

Prerequisites
-------------

Before you start introducing Symfony to the existing application, you have to
ensure certain requirements are met by your existing application and
environment.  Making the decisions and preparing the environment before
starting the migration process is crucial for its success.

.. note::

    The following steps do not require you to have the new Symfony
    application in place and in fact it might be safer to introduce these
    changes beforehand in your existing application.

Choosing the Target Symfony Version
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Most importantly, this means that you will have to decide which version you
are aiming to migrate to, either a current stable release or the long
term support version (LTS). The main difference is, how frequently you
will need to upgrade in order to use a supported version. In the context
of a migration, other factors, such as the supported PHP-version or
support for libraries/bundles you use, may have a strong impact as well.
Using the most recent, stable release will likely give you more features,
but it will also require you to update more frequently to ensure you will
get support for bug fixes and security patches and you will have to work
faster on fixing deprecations to be able to upgrade.

.. tip::

    When upgrading to Symfony you might be tempted to also use
    :ref:`Flex <symfony-flex>`. Please keep in mind that it primarily
    focuses on bootstrapping a new Symfony application according to best
    practices regarding the directory structure. When you work in the
    constraints of an existing application you might not be able to
    follow these constraints, making Flex less useful.

First of all your environment needs to be able to support the minimum
requirements for both applications. In other words, when the Symfony
release you aim to use requires PHP 7.1 and your existing application
does not yet support this PHP version, you will probably have to upgrade
your legacy project. Use the ``check:requirements`` command to check if your
server meets the :ref:`technical requirements for running Symfony applications <symfony-tech-requirements>`
and compare them with your current application's environment to make sure you
are able to run both applications on the same system. Having a test
system, that is as close to the production environment as possible,
where you can just install a new Symfony project next to the existing one
and check if it is working will give you an even more reliable result.

.. tip::

    If your current project is running on an older PHP version such as
    PHP 5.x upgrading to a recent version will give you a performance
    boost without having to change your code.

Setting up Composer
~~~~~~~~~~~~~~~~~~~

Another point you will have to look out for is conflicts between
dependencies in both applications. This is especially important if your
existing application already uses Symfony components or libraries commonly
used in Symfony applications such as Doctrine ORM or Twig.
A good way for ensuring compatibility is to use the same ``composer.json``
for both project's dependencies.

Once you have introduced composer for managing your project's dependencies
you can use its autoloader to ensure you do not run into any conflicts due
to custom autoloading from your existing framework. This usually entails
adding an `autoload`_-section to your ``composer.json`` and configuring it
based on your application and replacing your custom logic with something
like this::

    require __DIR__.'/vendor/autoload.php';

Removing Global State from the Legacy Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In older PHP applications it was quite common to rely on global state and
even mutate it during runtime. This might have side effects on the newly
introduced Symfony application. In other words code relying on globals
in the existing application should be refactored to allow for both systems
to work simultaneously. Since relying on global state is considered an
anti-pattern nowadays you might want to start working on this even before
doing any integration.

Setting up the Environment
~~~~~~~~~~~~~~~~~~~~~~~~~~

There might be additional steps you need to take depending on the libraries
you use, the original framework your project is based on and most importantly
the age of the project as PHP itself underwent many improvements throughout
the years that your code might not have caught on to, yet. As long as both
your existing code and a new Symfony project can run in parallel on the
same system you are on a good way. All these steps do not require you to
introduce Symfony just yet and will already open up some opportunities for
modernizing your existing code.

Establishing a Safety Net for Regressions
-----------------------------------------

Before you can safely make changes to the existing code, you must ensure that
nothing will break. One reason for choosing to migrate is making sure that the
application is in a state where it can run at all times. The best way for
ensuring a working state is to establish automated tests.

It is quite common for an existing application to either not have a test suite
at all or have low code coverage. Introducing unit tests for this code is
likely not cost effective as the old code might be replaced with functionality
from Symfony components or might be adapted to the new application.
Additionally legacy code tends to be hard to write tests for, making the process
slow and cumbersome.

Instead of providing low level tests, that ensure each class works as expected, it
might makes sense to write high level tests ensuring that at least anything user
facing works on at least a superficial level. These kinds of tests are commonly
called End-to-End tests, because they cover the whole application from what the
user sees in the browser down to the very code that is being run and connected
services like a database. To automate this you have to make sure that you can
get a test instance of your system running as easily as possible and making
sure that external systems do not change your production environment, e.g.
provide a separate test database with (anonymized) data from a production
system or being able to setup a new schema with a basic dataset for your test
environment. Since these tests do not rely as much on isolating testable code
and instead look at the interconnected system, writing them is usually easier
and more productive when doing a migration. You can then limit your effort on
writing lower level tests on parts of the code that you have to change or
replace in the new application making sure it is testable right from the start.

There are tools aimed at End-to-End testing you can use such as
`Symfony Panther`_ or you can write :doc:`functional tests </testing>`
in the new Symfony application as soon as the initial setup is completed.
For example you can add so called Smoke Tests, which only ensure a certain
path is accessible by checking the HTTP status code returned or looking for
a text snippet from the page.

Introducing Symfony to the Existing Application
-----------------------------------------------

The following instructions only provide an outline of common tasks for
setting up a Symfony application that falls back to a legacy application
whenever a route is not accessible. Your mileage may vary and likely you
will need to adjust some of this or even provide additional configuration
or retrofitting to make it work with your application. This guide is not
supposed to be comprehensive and instead aims to be a starting point.

.. tip::

    If you get stuck or need additional help you can reach out to the
    :doc:`Symfony community </contributing/community/index>` whenever you need
    concrete feedback on an issue you are facing.

Booting Symfony in a Front Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When looking at how a typical PHP application is bootstrapped there are
two major approaches. Nowadays most frameworks provide a so called
front controller which acts as an entrypoint. No matter which URL-path
in your application you are going to, every request is being sent to
this front controller, which then determines which parts of your
application to load, e.g. which controller and action to call. This is
also the approach that Symfony takes with ``public/index.php`` being
the front controller. Especially in older applications it was common
that different paths were handled by different PHP files.

In any case you have to create a ``public/index.php`` that will start
your Symfony application by either copying the file from the
``FrameworkBundle``-recipe or by using Flex and requiring the
FrameworkBundle. You will also likely have to update your web server
(e.g. Apache or nginx) to always use this front controller. You can
look at :doc:`Web Server Configuration </setup/web_server_configuration>`
for examples on how this might look. For example when using Apache you can
use Rewrite Rules to ensure PHP files are ignored and instead only index.php
is called:

.. code-block:: apache

    RewriteEngine On

    RewriteCond %{REQUEST_URI}::$1 ^(/.+)/(.*)::\2$
    RewriteRule ^(.*) - [E=BASE:%1]

    RewriteCond %{ENV:REDIRECT_STATUS} ^$
    RewriteRule ^index\.php(?:/(.*)|$) %{ENV:BASE}/$1 [R=301,L]

    RewriteRule ^index\.php - [L]

    RewriteCond %{REQUEST_FILENAME} -f
    RewriteCond %{REQUEST_FILENAME} !^.+\.php$
    RewriteRule ^ - [L]

    RewriteRule ^ %{ENV:BASE}/index.php [L]

This change will make sure that from now on your Symfony application is
the first one handling all requests. The next step is to make sure that
your existing application is started and taking over whenever Symfony
can not yet handle a path previously managed by the existing application.

From this point, many tactics are possible and every project requires its
unique approach for migration. This guide shows two examples of commonly used
approaches, which you can use as a base for your own approach:

* `Front Controller with Legacy Bridge`_, which leaves the legacy application
  untouched and allows migrating it in phases to the Symfony application.
* `Legacy Route Loader`_, where the legacy application is integrated in phases
  into Symfony, with a fully integrated final result.

Front Controller with Legacy Bridge
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Once you have a running Symfony application that takes over all requests,
falling back to your legacy application is done by extending the original front
controller script with some logic for going to your legacy system. The file
could look something like this::

    // public/index.php
    use App\Kernel;
    use App\LegacyBridge;
    use Symfony\Component\Dotenv\Dotenv;
    use Symfony\Component\ErrorHandler\Debug;
    use Symfony\Component\HttpFoundation\Request;

    require dirname(__DIR__).'/vendor/autoload.php';

    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

    /*
     * The kernel will always be available globally, allowing you to
     * access it from your existing application and through it the
     * service container. This allows for introducing new features in
     * the existing application.
     */
    global $kernel;

    if ($_SERVER['APP_DEBUG']) {
        umask(0000);

        Debug::enable();
    }

    if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
        Request::setTrustedProxies(
          explode(',', $trustedProxies),
          Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
        );
    }

    if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
        Request::setTrustedHosts([$trustedHosts]);
    }

    $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);

    if (false === $response->isNotFound()) {
        // Symfony successfully handled the route.
        $response->send();
    } else {
        LegacyBridge::handleRequest($request, $response, __DIR__);
    }

    $kernel->terminate($request, $response);

There are 2 major deviations from the original file:

Line 18
  First of all, ``$kernel`` is made globally available. This allows you to use
  Symfony features inside your existing application and gives access to
  services configured in our Symfony application. This helps you prepare your
  own code to work better within the Symfony application before you transition
  it over. For instance, by replacing outdated or redundant libraries with
  Symfony components.

Line 41 - 46
  If Symfony handled the response, it is sent; otherwise, the ``LegacyBridge``
  handles the request.

This legacy bridge is responsible for figuring out which file should be loaded
in order to process the old application logic. This can either be a front
controller similar to Symfony's ``public/index.php`` or a specific script file
based on the current route. The basic outline of this LegacyBridge could look
somewhat like this::

    // src/LegacyBridge.php
    namespace App;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    class LegacyBridge
    {

        /**
         * Map the incoming request to the right file. This is the
         * key function of the LegacyBridge.
         *
         * Sample code only. Your implementation will vary, depending on the
         * architecture of the legacy code and how it's executed.
         *
         * If your mapping is complicated, you may want to write unit tests
         * to verify your logic, hence this is public static.
         */
        public static function getLegacyScript(Request $request): string
        {
            $requestPathInfo = $request->getPathInfo();
            $legacyRoot = __DIR__ . '/../';

            // Map a route to a legacy script:
            if ($requestPathInfo == '/customer/') {
                return "{$legacyRoot}src/customers/list.php";
            }

            // Map a direct file call, e.g. an ajax call:
            if ($requestPathInfo == 'inc/ajax_cust_details.php') {
                return "{$legacyRoot}inc/ajax_cust_details.php";
            }

            // ... etc.

            throw new \Exception("Unhandled legacy mapping for $requestPathInfo");
        }

        public static function handleRequest(Request $request, Response $response, string $publicDirectory): void
        {
            $legacyScriptFilename = LegacyBridge::getLegacyScript($request);

            // Possibly (re-)set some env vars (e.g. to handle forms
            // posting to PHP_SELF):
            $p = $request->getPathInfo();
            $_SERVER['PHP_SELF'] = $p;
            $_SERVER['SCRIPT_NAME'] = $p;
            $_SERVER['SCRIPT_FILENAME'] = $legacyScriptFilename;

            require $legacyScriptFilename;
        }
    }

This is the most generic approach you can take, that is likely to work
no matter what your previous system was. You might have to account for
certain "quirks", but since your original application is only started
after Symfony finished handling the request you reduced the chances
for side effects and any interference.

Since the old script is called in the global variable scope it will reduce side
effects on the old code which can sometimes require variables from the global
scope. At the same time, because your Symfony application will always be
booted first, you can access the container via the ``$kernel`` variable and
then fetch any service (using :method:`Symfony\\Component\\HttpKernel\\KernelInterface::getContainer`).
This can be helpful if you want to introduce new features to your legacy
application, without switching over the whole action to the new application.
For example, you could now use the Symfony Translator in your old application
or instead of using your old database logic, you could use Doctrine to refactor
old queries. This will also allow you to incrementally improve the legacy code
making it easier to transition it over to the new Symfony application.

The major downside is, that both systems are not well integrated
into each other leading to some redundancies and possibly duplicated code.
For example, since the Symfony application is already done handling the
request you can not take advantage of kernel events or utilize Symfony's
routing for determining which legacy script to call.

Legacy Route Loader
~~~~~~~~~~~~~~~~~~~

The major difference to the LegacyBridge-approach from before is, that the
logic is moved inside the Symfony application. It removes some of the
redundancies and allows us to also interact with parts of the legacy
application from inside Symfony, instead of just the other way around.

.. tip::

    The following route loader is just a generic example that you might
    have to tweak for your legacy application. You can familiarize
    yourself with the concepts by reading up on it in :doc:`Routing </routing>`.

The legacy route loader is :doc:`a custom route loader </routing/custom_route_loader>`.
The legacy route loader has a similar functionality as the previous
LegacyBridge, but it is a service that is registered inside Symfony's Routing
component::

    // src/Legacy/LegacyRouteLoader.php
    namespace App\Legacy;

    use Symfony\Component\Config\Loader\Loader;
    use Symfony\Component\Routing\Route;
    use Symfony\Component\Routing\RouteCollection;

    class LegacyRouteLoader extends Loader
    {
        // ...

        public function load($resource, $type = null): RouteCollection
        {
            $collection = new RouteCollection();
            $finder = new Finder();
            $finder->files()->name('*.php');

            /** @var SplFileInfo $legacyScriptFile */
            foreach ($finder->in($this->webDir) as $legacyScriptFile) {
                // This assumes all legacy files use ".php" as extension
                $filename = basename($legacyScriptFile->getRelativePathname(), '.php');
                $routeName = sprintf('app.legacy.%s', str_replace('/', '__', $filename));

                $collection->add($routeName, new Route($legacyScriptFile->getRelativePathname(), [
                    '_controller' => 'App\Controller\LegacyController::loadLegacyScript',
                    'requestPath' => '/' . $legacyScriptFile->getRelativePathname(),
                    'legacyScript' => $legacyScriptFile->getPathname(),
                ]));
            }

            return $collection;
        }
    }

You will also have to register the loader in your application's
``routing.yaml`` as described in the documentation for
:doc:`Custom Route Loaders </routing/custom_route_loader>`.
Depending on your configuration, you might also have to tag the service with
``routing.loader``. Afterwards you should be able to see all the legacy routes
in your route configuration, e.g. when you call the ``debug:router``-command:

.. code-block:: terminal

    $ php bin/console debug:router

In order to use these routes you will need to create a controller that handles
these routes. You might have noticed the ``_controller`` attribute in the
previous code example, which tells Symfony which Controller to call whenever it
tries to access one of our legacy routes. The controller itself can then use the
other route attributes (i.e. ``requestPath`` and ``legacyScript``) to determine
which script to call and wrap the output in a response class::

    // src/Controller/LegacyController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\StreamedResponse;

    class LegacyController
    {
        public function loadLegacyScript(string $requestPath, string $legacyScript): StreamedResponse
        {
            return new StreamedResponse(
                function () use ($requestPath, $legacyScript): void {
                    $_SERVER['PHP_SELF'] = $requestPath;
                    $_SERVER['SCRIPT_NAME'] = $requestPath;
                    $_SERVER['SCRIPT_FILENAME'] = $legacyScript;

                    chdir(dirname($legacyScript));

                    require $legacyScript;
                }
            );
        }
    }

This controller will set some server variables that might be needed by
the legacy application. This will simulate the legacy script being called
directly, in case it relies on these variables (e.g. when determining
relative paths or file names). Finally the action requires the old script,
which essentially calls the original script as before, but it runs inside
our current application scope, instead of the global scope.

There are some risks to this approach, as it is no longer run in the global
scope. However, since the legacy code now runs inside a controller action, you gain
access to many functionalities from the new Symfony application, including the
chance to use Symfony's event lifecycle. For instance, this allows you to
transition the authentication and authorization of the legacy application over
to the Symfony application using the Security component and its firewalls.

.. _`Strangler Fig Application`: https://martinfowler.com/bliki/StranglerFigApplication.html
.. _`autoload`: https://getcomposer.org/doc/04-schema.md#autoload
.. _`Modernizing with Symfony`: https://youtu.be/YzyiZNY9htQ
.. _`Symfony Panther`: https://github.com/symfony/panther
