Bundle Best Practices
=====================

A bundle is a directory that has a well-defined structure and can host
anything from classes to controllers and web resources. Even if bundles are
very flexible, you should follow some best practices if you want to distribute
them.

Bundle Name
-----------

A bundle is also a PHP namespace, composed of several segments:

  * The **main namespace**: either `Bundle`, for reusable bundles, or
    `Application` for application specific bundles;
  * The **vendor namespace** (optional for `Application` bundles): something
    unique to you or your company (like `Sensio`);
  * *(optional)* The **category namespace(s)** to better organize a large set of
    bundles;
  * The **bundle name**.

>**CAUTION**
>The vendor namespace and the category namespaces are only possible as of
>Symfony2 PR3.

The bundle name must follow the following rules:

  * Use only alphanumeric characters and underscores;
  * Use a camelCased name;
  * Use a descriptive and short name (no more than 2 words);
  * Prefix the name with the concatenation of the vendor and category
    namespaces;
  * Suffix the name with `Bundle`.

Some good bundle names:

 | Namespace                         | Bundle Name              |
 | --------------------------------- | ------------------------ |
 | `Bundle\Sensio\BlogBundle`        | `SensioBlogBundle`       |
 | `Bundle\Sensio\Social\BlogBundle` | `SensioSocialBlogBundle` |
 | `Application\BlogBundle`          | `BlogBundle`             |

Directory Structure
-------------------

The basic directory structure of a `HelloBundle` bundle must read as follows:

    XXX/...
        HelloBundle/
            HelloBundle.php
            Controller/
            Resources/
                meta/
                    LICENSE
                config/
                doc/
                    01-Overview.markdown
                views/
                web/
            Tests/

The `XXX` directory(ies) reflects the namespace structure of the bundle.

The following files are mandatory:

  * `HelloBundle.php`;
  * `Resources/meta/LICENSE`: The full license for the code;
  * `Resources/doc/01-Overview.markdown`: A quick overview of the features
    provided by the bundle.

>**NOTE**
>These conventions ensure that automated tools can rely on this default
>structure to work.

The depth of sub-directories should be kept to the minimal for most used
classes and files (2 levels at a maximum). More levels can be defined for
non-strategic, less-used files.

The bundle directory is read-only. If you need to write temporary files, store
them under the `cache/` or `log/` directory of the host application. Tools can
generate files in the bundle directory structure, but only if the generated
files are going to be part of the repository.

The following classes and files have specific emplacements:

  | Type                      | Directory           |
  | ------------------------- | ------------------- |
  | Controllers               | `Controller/`       |
  | Templates                 | `Resources/views/`  |
  | Unit and Functional Tests | `Tests/`            |
  | Web Resources             | `Resources/web/`    |
  | Configuration             | `Resources/config/` |
  | Commands                  | `Command/`          |

Classes
-------

The bundle directory structure is used as the namespace hierarchy. For
instance, a `HelloController` controller is stored in
`Bundle/HelloBundle/Controller/HelloController.php` and the fully qualified
class name is `Bundle\HelloBundle\Controller\HelloController`.

All classes and files must follow the Symfony2 coding [standards][1].

Some classes should be seen as facades and should be as short as possible,
like Commands, Helpers, Listeners, and Controllers.

Classes that connects to the Event Dispatcher should have a name that ends
with `Listener`.

Exceptions classes should be stored in an `Exception` sub-namespace.

Vendors
-------

A bundle must not embed third-party libraries. It should rely on the standard
Symfony2 autoloading instead.

Tests
-----

A bundle should come with a test suite written with PHPUnit and stored under
the `Tests/` directory. Tests should follow the following principles:

  * The test suite must be executable with a simple `phpunit` command run from
    a sample application;
  * The functional tests should only be used to test the response output and
    some profiling information if you have some;
  * The code coverage should at least covers 95% of the code base.

>**NOTE**
>A test suite must not contain `AllTests.php` scripts, but must rely on the
>existence of a `phpunit.xml.dist` file.

Documentation
-------------

All classes and functions must come with full PHPDoc.

Extensive documentation should also be provided in the Markdown format, under
the `Resources/doc/` directory; the `Resources/doc/01-Overview.markdown` file
is the only mandatory file.

Templates
---------

If a bundle provides templates, they should be defined in plain PHP. A bundle
must not provide a main layout, but extends a default `base` template (which
must provide two slots: `content` and `head`).

>**NOTE**
>The only other template engine supported is Twig, but only for specific
>cases.

Configuration
-------------

Configuration must be done via the Symfony2 built-in [mechanism][2]. A bundle
should provide all its default configurations in XML.

[1]: http://www.symfony-reloaded.org/contributing/Code/Standards
[2]: http://www.symfony-reloaded.org/guides/Bundles/Configuration
