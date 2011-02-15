:orphan:

Glossary
========

.. glossary::

    Project
        A *Project* is a directory composed of an Application, a set of
        bundles, vendor libraries, an autoloader, and web front controller
        scripts.

    Application
        An *Application* is a directory containing the *configuration* for a
        given set of Bundles.

    Bundle
        A *Bundle* is a structured set of files (PHP files, stylesheets,
        JavaScripts, images, ...) that *implements* a single feature (a blog,
        a forum, ...) and which can be easily shared with other developers.

    Front Controller
        A *Front Controller* is a short PHP that lives in the web directory
        of your project. Typically, *all* requests are handled by executing
        the same front controller, whose job is to bootstrap the Symfony
        application.

    Service
        A *Service* is a generic term for any PHP object that performs a
        specific task. A service is usually used "globally", such as a database
        connection object or an object that delivers email messages. In Symfony2,
        services are often configured and retrieved from the service container.
        An application that has many decoupled services is said to follow
        a `service-oriented architecture`_

    Service Container
        A *Service Container*, also known as a *Dependency Injection Container*,
        is a special object that manages the instantiation of services inside
        an application. Instead of creating services directly, the developer
        *trains* the service container (via configuration) on how to create
        the services. The service container takes care of lazily instantiating
        and injecting dependent services.

.. _`service-oriented architecture`: http://wikipedia.org/wiki/Service-oriented_architecture
