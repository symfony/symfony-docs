:orphan:

Glossary
========

.. glossary::
   :sorted:

   Distribution
        A *Distribution* is a package made of the Symfony2 Components, a
        selection of bundles, a sensible directory structure, a default
        configuration, and an optional configuration system.

   Project
        A *Project* is a directory composed of an Application, a set of
        bundles, vendor libraries, an autoloader, and web front controller
        scripts.

   Application
        An *Application* is a directory containing the *configuration* for a
        given set of Bundles.

   Bundle
        A *Bundle* is a directory containing a set of files (PHP files,
        stylesheets, JavaScripts, images, ...) that *implement* a single
        feature (a blog, a forum, etc). In Symfony2, (*almost*) everything
        lives inside a bundle. (see :ref:`page-creation-bundles`)

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
        a `service-oriented architecture`_.

   Service Container
        A *Service Container*, also known as a *Dependency Injection Container*,
        is a special object that manages the instantiation of services inside
        an application. Instead of creating services directly, the developer
        *trains* the service container (via configuration) on how to create
        the services. The service container takes care of lazily instantiating
        and injecting dependent services.

   HTTP Specification
        The *Http Specification* is a document that describes the Hypertext
        Transfer Protocol - a set of rules laying out the classic client-server
        request-response communication. The specification defines the format
        used for a request and response as well as the possible HTTP headers
        that each may have. For more information, read the `Http Wikipedia`_
        article or the `HTTP 1.1 RFC`_.

.. _`service-oriented architecture`: http://wikipedia.org/wiki/Service-oriented_architecture
.. _`HTTP Wikipedia`: http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
.. _`HTTP 1.1 RFC`: http://www.w3.org/Protocols/rfc2616/rfc2616.html
