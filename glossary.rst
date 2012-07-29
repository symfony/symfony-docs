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
        A *Front Controller* is a short PHP script that lives in the web directory
        of your project. Typically, *all* requests are handled by executing
        the same front controller, whose job is to bootstrap the Symfony
        application.

   Controller
        A *controller* is a PHP function that houses all the logic necessary
        to return a ``Response`` object that represents a particular page.
        Typically, a route is mapped to a controller, which then uses information
        from the request to process information, perform actions, and ultimately
        construct and return a ``Response`` object.

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
        and injecting dependent services. See :doc:`/book/service_container` 
        chapter.

   HTTP Specification
        The *Http Specification* is a document that describes the Hypertext
        Transfer Protocol - a set of rules laying out the classic client-server
        request-response communication. The specification defines the format
        used for a request and response as well as the possible HTTP headers
        that each may have. For more information, read the `Http Wikipedia`_
        article or the `HTTP 1.1 RFC`_.

   Environment
        An environment is a string (e.g. ``prod`` or ``dev``) that corresponds
        to a specific set of configuration. The same application can be run
        on the same machine using different configuration by running the application
        in different environments. This is useful as it allows a single application
        to have a ``dev`` environment built for debugging and a ``prod`` environment
        that's optimized for speed.

   Vendor
        A *vendor* is a supplier of PHP libraries and bundles including Symfony2
        itself. Despite the usual commercial connotations of the word, vendors
        in Symfony often (even usually) include free software. Any library you
        add to your Symfony2 project should go in the ``vendor`` directory. See
        :ref:`The Architecture: Using Vendors <using-vendors>`.

   Acme
        *Acme* is a sample company name used in Symfony demos and documentation.
        It's used as a namespace where you would normally use your own company's
        name (e.g. ``Acme\BlogBundle``).

   Action
        An *action* is a PHP function or method that executes, for example,
        when a given route is matched. The term action is synonymous with
        *controller*, though a controller may also refer to an entire PHP
        class that includes several actions. See the :doc:`Controller Chapter </book/controller>`.

   Asset
        An *asset* is any non-executable, static component of a web application,
        including CSS, JavaScript, images and video. Assets may be placed
        directly in the project's ``web`` directory, or published from a :term:`Bundle`
        to the web directory using the ``assets:install`` console task.

   Kernel
        The *Kernel* is the core of Symfony2. The Kernel object handles HTTP
        requests using all the bundles and libraries registered to it. See
        :ref:`The Architecture: The Application Directory <the-app-dir>` and the
        :doc:`/book/internals` chapter.

   Firewall
        In Symfony2, a *Firewall* doesn't have to do with networking. Instead,
        it defines the authentication mechanisms (i.e. it handles the process
        of determining the identity of your users), either for the whole
        application or for just a part of it. See the
        :doc:`/book/security` chapters.

   Yaml
        *YAML* is a recursive acronym for "YAML Ain't a Markup Language". It's a
        lightweight, humane data serialization language used extensively in
        Symfony2's configuration files.  See the :doc:`/components/yaml` 
        chapter.


.. _`service-oriented architecture`: http://wikipedia.org/wiki/Service-oriented_architecture
.. _`HTTP Wikipedia`: http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
.. _`HTTP 1.1 RFC`: http://www.w3.org/Protocols/rfc2616/rfc2616.html
