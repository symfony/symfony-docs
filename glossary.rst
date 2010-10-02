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

    Environment
        *Environments* are different sets of configuration, that share the same
        PHP code. So basically, environments and configuration are synonyms. For
        each application, Symfony provides three default environments:
        *production* (``prod``), *test* (``test``), and *development* (``dev``).
