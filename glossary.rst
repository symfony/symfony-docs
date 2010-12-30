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