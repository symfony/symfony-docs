The Symfony Framework Best Practices
====================================

This article describes the **best practices for developing web applications with
Symfony** that fit the philosophy envisioned by the original Symfony creators.

If you don't agree with some of these recommendations, they might be a good
**starting point** that you can then **extend and fit to your specific needs**.
You can even ignore them completely and continue using your own best practices
and methodologies. Symfony is flexible enough to adapt to your needs.

This article assumes that you already have experience developing Symfony
applications. If you don't, read first the :doc:`Getting Started </setup>`
section of the documentation.

.. tip::

    Symfony provides a sample application called `Symfony Demo`_ that follows
    all these best practices, so you can experience them in practice.

Creating the Project
--------------------

Use the Symfony Binary to Create Symfony Applications
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The Symfony binary is an executable command created in your machine when you
`download Symfony`_. It provides multiple utilities, including the simplest way
to create new Symfony applications:

.. code-block:: terminal

    $ symfony new my_project_directory

Under the hood, this Symfony binary command executes the needed `Composer`_
command to :ref:`create a new Symfony application <creating-symfony-applications>`
based on the current stable version.

Use the Default Directory Structure
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Unless your project follows a development practice that imposes a certain
directory structure, follow the default Symfony directory structure. It's flat,
self-explanatory and not coupled to Symfony:

.. code-block:: text

    your_project/
    ├─ assets/
    ├─ bin/
    │  └─ console
    ├─ config/
    │  ├─ packages/
    │  └─ services.yaml
    ├─ migrations/
    ├─ public/
    │  ├─ build/
    │  └─ index.php
    ├─ src/
    │  ├─ Kernel.php
    │  ├─ Command/
    │  ├─ Controller/
    │  ├─ DataFixtures/
    │  ├─ Entity/
    │  ├─ EventSubscriber/
    │  ├─ Form/
    │  ├─ Repository/
    │  ├─ Security/
    │  └─ Twig/
    ├─ templates/
    ├─ tests/
    ├─ translations/
    ├─ var/
    │  ├─ cache/
    │  └─ log/
    └─ vendor/

Configuration
-------------

Use Environment Variables for Infrastructure Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The values of these options change from one machine to another (e.g. from your
development machine to the production server), but they don't modify the
application behavior.

:ref:`Use env vars in your project <config-env-vars>` to define these options
and create multiple ``.env`` files to :ref:`configure env vars per environment <config-dot-env>`.

.. _use-secret-for-sensitive-information:

Use Secrets for Sensitive Information
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When your application has sensitive configuration, like an API key, you should
store those securely via :doc:`Symfony’s secrets management system </configuration/secrets>`.

Use Parameters for Application Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

These are the options used to modify the application behavior, such as the sender
of email notifications, or the enabled `feature toggles`_. Their value doesn't
change per machine, so don't define them as environment variables.

Define these options as :ref:`parameters <configuration-parameters>` in the
``config/services.yaml`` file. You can override these options per
:ref:`environment <configuration-environments>` in the ``config/services_dev.yaml``
and ``config/services_prod.yaml`` files.

Use Short and Prefixed Parameter Names
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Consider using ``app.`` as the prefix of your :ref:`parameters <configuration-parameters>`
to avoid collisions with Symfony and third-party bundles/libraries parameters.
Then, use just one or two words to describe the purpose of the parameter:

.. code-block:: yaml

    # config/services.yaml
    parameters:
        # don't do this: 'dir' is too generic, and it doesn't convey any meaning
        app.dir: '...'
        # do this: short but easy to understand names
        app.contents_dir: '...'
        # it's OK to use dots, underscores, dashes or nothing, but always
        # be consistent and use the same format for all the parameters
        app.dir.contents: '...'
        app.contents-dir: '...'

Use Constants to Define Options that Rarely Change
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Configuration options like the number of items to display in some listing rarely
change. Instead of defining them as :ref:`configuration parameters <configuration-parameters>`,
define them as PHP constants in the related classes. Example::

    // src/Entity/Post.php
    namespace App\Entity;

    class Post
    {
        public const NUMBER_OF_ITEMS = 10;

        // ...
    }

The main advantage of constants is that you can use them everywhere, including
Twig templates and Doctrine entities, whereas parameters are only available
from places with access to the :doc:`service container </service_container>`.

The only notable disadvantage of using constants for this kind of configuration
values is that it's complicated to redefine their values in your tests.

Business Logic
--------------

Don't Create any Bundle to Organize your Application Logic
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When Symfony 2.0 was released, applications used :doc:`bundles </bundles>` to
divide their code into logical features: UserBundle, ProductBundle,
InvoiceBundle, etc. However, a bundle is meant to be something that can be
reused as a stand-alone piece of software.

If you need to reuse some feature in your projects, create a bundle for it (in a
private repository, do not make it publicly available). For the rest of your
application code, use PHP namespaces to organize code instead of bundles.

Use Autowiring to Automate the Configuration of Application Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:doc:`Service autowiring </service_container/autowiring>` is a feature that
reads the type-hints on your constructor (or other methods) and automatically
passes the correct services to each method, making it unnecessary to configure
services explicitly and simplifying the application maintenance.

Use it in combination with :ref:`service autoconfiguration <services-autoconfigure>`
to also add :doc:`service tags </service_container/tags>` to the services
needing them, such as Twig extensions, event subscribers, etc.

Services Should be Private Whenever Possible
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:ref:`Make services private <container-public>` to prevent you from accessing
those services via ``$container->get()``. Instead, you will need to use proper
dependency injection.

Use the YAML Format to Configure your own Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you use the :ref:`default services.yaml configuration <service-container-services-load-example>`,
most services will be configured automatically. However, in some edge cases
you'll need to configure services (or parts of them) manually.

YAML is the format recommended configuring services because it's friendly to
newcomers and concise, but Symfony also supports XML and PHP configuration.

Use Attributes to Define the Doctrine Entity Mapping
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Doctrine entities are plain PHP objects that you store in some "database".
Doctrine only knows about your entities through the mapping metadata configured
for your model classes.

Doctrine supports several metadata formats, but it's recommended to use PHP
attributes because they are by far the most convenient and agile way of setting
up and looking for mapping information.

If your PHP version doesn't support attributes yet, use annotations, which is
similar but requires installing some extra dependencies in your project.

Controllers
-----------

Make your Controller Extend the ``AbstractController`` Base Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony provides a :ref:`base controller <the-base-controller-classes-services>`
which includes shortcuts for the most common needs such as rendering templates
or checking security permissions.

Extending your controllers from this base controller couples your application
to Symfony. Coupling is generally wrong, but it may be OK in this case because
controllers shouldn't contain any business logic. Controllers should contain
nothing more than a few lines of *glue-code*, so you are not coupling the
important parts of your application.

.. _best-practice-controller-annotations:

Use Attributes or Annotations to Configure Routing, Caching, and Security
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Using attributes or annotations for routing, caching, and security simplifies
configuration. You don't need to browse several files created with different
formats (YAML, XML, PHP): all the configuration is just where you  require it,
and it only uses one format.

Use Dependency Injection to Get Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you extend the base ``AbstractController``, you can only get access to the most
common services (e.g ``twig``, ``router``, ``doctrine``, etc.), directly from the
container via ``$this->container->get()``.
Instead, you must use dependency injection to fetch services by
:ref:`type-hinting action method arguments <controller-accessing-services>` or
constructor arguments.

Use Entity Value Resolvers If They Are Convenient
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you're using :doc:`Doctrine </doctrine>`, then you can *optionally* use
the :ref:`EntityValueResolver <doctrine-entity-value-resolver>` to
automatically query for an entity and pass it as an argument to your
controller. It will also show a 404 page if no entity can be found.

If the logic to get an entity from a route variable is more complex, instead of
configuring the EntityValueResolver, it's better to make the Doctrine query
inside the controller (e.g. by calling to a :doc:`Doctrine repository method </doctrine>`).

Templates
---------

Use Snake Case for Template Names and Variables
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Use lowercase snake_case for template names, directories, and variables (e.g.
``user_profile`` instead of ``userProfile`` and ``product/edit_form.html.twig``
instead of ``Product/EditForm.html.twig``).

Prefix Template Fragments with an Underscore
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Template fragments, also called *"partial templates"*, allow to
:ref:`reuse template contents <templates-reuse-contents>`. Prefix their names
with an underscore to better differentiate them from complete templates (e.g.
``_user_metadata.html.twig`` or ``_caution_message.html.twig``).

Forms
-----

Define your Forms as PHP Classes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Creating :ref:`forms in classes <creating-forms-in-classes>` allows reusing
them in different parts of the application. Besides, not creating forms in
controllers simplifies the code and maintenance of the controllers.

Add Form Buttons in Templates
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Form classes should be agnostic to where they will be used. For example, the
button of a form used to both create and edit items should change from "Add new"
to "Save changes" depending on where it's used.

Instead of adding buttons in form classes or the controllers, it's recommended
to add buttons in the templates. This also improves the separation of concerns
because the button styling (CSS class and other attributes) is defined in the
template instead of in a PHP class.

However, if you create a :doc:`form with multiple submit buttons </form/multiple_buttons>`
you should define them in the controller instead of the template. Otherwise, you
won't be able to check which button was clicked when handling the form in the controller.

Define Validation Constraints on the Underlying Object
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Attaching :doc:`validation constraints </reference/constraints>` to form fields
instead of to the mapped object prevents the validation from being reused in
other forms or other places where the object is used.

.. _best-practice-handle-form:

Use a Single Action to Render and Process the Form
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:ref:`Rendering forms <rendering-forms>` and :ref:`processing forms <processing-forms>`
are two of the main tasks when handling forms. Both are too similar (most of the
time, almost identical), so it's much simpler to let a single controller action
handle both.

.. _best-practice-internationalization:

Internationalization
--------------------

Use the XLIFF Format for Your Translation Files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Of all the translation formats supported by Symfony (PHP, Qt, ``.po``, ``.mo``,
JSON, CSV, INI, etc.), ``XLIFF`` and ``gettext`` have the best support in the tools used
by professional translators. And since it's based on XML, you can validate ``XLIFF``
file contents as you write them.

Symfony also supports notes in XLIFF files, making them more user-friendly for
translators. At the end, good translations are all about context, and these
XLIFF notes allow you to define that context.

Use Keys for Translations Instead of Content Strings
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Using keys simplifies the management of the translation files because you can
change the original contents in templates, controllers, and services without
having to update all the translation files.

Keys should always describe their *purpose* and *not* their location. For
example, if a form has a field with the label "Username", then a nice key
would be ``label.username``, *not* ``edit_form.label.username``.

Security
--------

Define a Single Firewall
~~~~~~~~~~~~~~~~~~~~~~~~

Unless you have two legitimately different authentication systems and users
(e.g. form login for the main site and a token system for your API only), it's
recommended to have only one firewall to keep things simple.

Additionally, you should use the ``anonymous`` key under your firewall. If you
require users to be logged in for different sections of your site, use the
:doc:`access_control </security/access_control>` option.

Use the ``auto`` Password Hasher
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The :ref:`auto password hasher <reference-security-encoder-auto>` automatically
selects the best possible encoder/hasher depending on your PHP installation.
Currently, the default auto hasher is ``bcrypt``.

Use Voters to Implement Fine-grained Security Restrictions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your security logic is complex, you should create custom
:doc:`security voters </security/voters>` instead of defining long expressions
inside the ``#[Security]`` attribute.

Web Assets
----------

Use Webpack Encore to Process Web Assets
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Web assets are things like CSS, JavaScript, and image files that make the
frontend of your site look and work great. `Webpack`_ is the leading JavaScript
module bundler that compiles, transforms and packages assets for usage in a browser.

:doc:`Webpack Encore </frontend>` is a JavaScript library that gets rid of most
of Webpack complexity without hiding any of its features or distorting its usage
and philosophy. It was created for Symfony applications, but it works
for any application using any technology.

Tests
-----

Smoke Test your URLs
~~~~~~~~~~~~~~~~~~~~

In software engineering, `smoke testing`_ consists of *"preliminary testing to
reveal simple failures severe enough to reject a prospective software release"*.
Using `PHPUnit data providers`_ you can define a functional test that
checks that all application URLs load successfully::

    // tests/ApplicationAvailabilityFunctionalTest.php
    namespace App\Tests;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class ApplicationAvailabilityFunctionalTest extends WebTestCase
    {
        /**
         * @dataProvider urlProvider
         */
        public function testPageIsSuccessful($url)
        {
            $client = self::createClient();
            $client->request('GET', $url);

            $this->assertResponseIsSuccessful();
        }

        public function urlProvider()
        {
            yield ['/'];
            yield ['/posts'];
            yield ['/post/fixture-post-1'];
            yield ['/blog/category/fixture-category'];
            yield ['/archives'];
            // ...
        }
    }

Add this test while creating your application because it requires little effort
and checks that none of your pages returns an error. Later, you'll add more
specific tests for each page.

.. _hardcode-urls-in-a-functional-test:

Hard-code URLs in a Functional Test
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In Symfony applications, it's recommended to :ref:`generate URLs <routing-generating-urls>`
using routes to automatically update all links when a URL changes. However, if a
public URL changes, users won't be able to browse it unless you set up a
redirection to the new URL.

That's why it's recommended to use raw URLs in tests instead of generating them
from routes. Whenever a route changes, tests will fail, and you'll know that
you must set up a redirection.

.. _`Symfony Demo`: https://github.com/symfony/demo
.. _`download Symfony`: https://symfony.com/download
.. _`Composer`: https://getcomposer.org/
.. _`feature toggles`: https://en.wikipedia.org/wiki/Feature_toggle
.. _`smoke testing`: https://en.wikipedia.org/wiki/Smoke_testing_(software)
.. _`Webpack`: https://webpack.js.org/
.. _`PHPUnit data providers`: https://docs.phpunit.de/en/9.5/writing-tests-for-phpunit.html#data-providers
