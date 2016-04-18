.. index::
   single: Translations

Translations
============

In this chapter, you'll learn how to use the
:class:`Symfony\\Component\\Translation\\Translator` component and it's
``translator`` service.

The term "internationalization" (often abbreviated `i18n`_) refers to the
process of abstracting strings and other locale-specific pieces out of
application into a layer where they can be translated and converted based
on the user's locale (refers roughly to the user's language and country).
For text, this means wrapping each with a function capable of translating
th **text called a message** into the language of the user::

    // text will *always* print out in English
    var_dump('Hello World');

    // text can be translated into the end-user's language or
    // default to English
    var_dump($translator->trans('Hello World'));

Overall, the translation process has several steps we will go through:

#. Enable and configure Symfony's ``translator`` service;

#. Abstract strings (i.e. "messages") by wrapping them in calls to the
   ``translator``;

#. Create translation resources, which are usually files for each supported
   locale that translate each message in the application;

#. Determine, set and manage the user's locale for the request and optionally
   on the user's entire session.

.. seealso::

    The learn about locals read :ref:`book-translation-locales` section of
    this chapter.

    To learn even more on the Translation component you can read
    :doc:`Translation component documentation </components/translation/usage>`.

.. index::
   single: Translations; Configuration
   single: Translations; Fallback locale

.. _book-translation-configuration:
.. _book-translation-fallback-locale:

Configuration and Fallback Locale
---------------------------------

Translations are handled by a ``translator`` service that uses the
user's locale to lookup and return translated messages. Before using it,
``translator`` service needs to be enabled in default application
configuration file ``app/config/config.yml``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            translator: { fallbacks: [en] }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:translator>
                    <framework:fallback>en</framework:fallback>
                </framework:translator>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'translator' => array('fallbacks' => array('en')),
        ));

The fallback locales can also be set by calling
:method:`Symfony\\Component\\Translation\\Translator::setFallbackLocales`
method of the ``translator`` service::

    $translator->setFallbackLocales(array('en'));

.. index::
   single: Translation; Translation resources and message catalogs

.. _book-translation-resources:

Translation Resources and Message Catalogs
------------------------------------------

For translations to work, you need to tell Symfony how to translate the message
via a **"translation resource"**, which is usually a file that contains a
collection of translations for a given locale. This "dictionary" of translations
can be created in several different formats, XLIFF being the recommended format.

Translation files consist of a series of id-translation pairs for the given
*domain* and *locale*.

This example illustrates the two different philosophies when creating
messages to be translated::

    $translator->trans('Symfony is great');

    $translator->trans('symfony.great');

In the first method, messages are written in the language of the default
locale (English in this case). That message is then used as the "id"
when creating translations.

In the second method, messages are actually "keywords" that convey the
idea of the message. The keyword message is then used as the "id" for
any translations. In this case, translations must be made for the default
locale (i.e. to translate ``symfony.great`` to ``Symfony is great``).

.. configuration-block::

    .. code-block:: xml

        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="symfony_is_great">
                        <source>Symfony is great</source>
                        <target>J'aime Symfony</target>
                    </trans-unit>
                    <trans-unit id="symfony.great">
                        <source>symfony.great</source>
                        <target>J'aime Symfony</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: yaml

        Symfony is great: J'aime Symfony
        symfony.great:    J'aime Symfony

    .. code-block:: php

        return array(
            'Symfony is great' => 'J\'aime Symfony',
            'symfony.great'    => 'J\'aime Symfony',
        );

Translation files are parsed, stored inside **message catalogs** (e.g. big
collections) and loaded by one of the Loader classes. One message catalog is
like a dictionary of translations for a specific locale thus, which catalog is
loaded is defined by the ``locale``. Messages from the
:ref:`fallback locale <book-translation-fallback>` are also loaded and added to
the catalog if they don't already exist. The end result is a large "dictionary"
of translations.

If the message is located in the catalog, the translation is returned. If
not, the translator returns the original message.

For the above example, if the language of the user's locale is French
(e.g. ``fr_FR`` or ``fr_BE``), the message will be translated into
``J'aime Symfony``. If user's locale is something else other then French or
English the translator will return the original message.

.. index::
   single: Translation; Translation resources locations

.. _book-translation-resource-locations:

Translation Resources Locations
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony looks for message files (i.e. translations) in the following locations:

* the ``app/Resources/translations`` directory;

* the ``app/Resources/{BUNDLE_NAME}/translations`` directory;

* the ``Resources/translations/`` directory inside of any bundle.

The locations are listed here with the highest priority first. That is, you can
override the translation messages of a bundle in any of the top two directories.
The override mechanism works at a key level: only the overridden keys need
to be listed in a higher priority message file. When a key is not found
in a message file, the translator will automatically fall back to the lower
priority message files.

.. note::

    You can also store translations in a database, or any other storage by
    providing a custom class implementing the
    :class:`Symfony\\Component\\Translation\\Loader\\LoaderInterface` interface.
    See the :ref:`dic-tags-translation-loader` tag for more information.

.. index::
   single: Translation; Translation resources naming convention

Translation Resources Naming Convention
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The filename of the translation files is also important. Each message file
must be named according to the following path::

.. code-block:: text

    domain.locale.loader

* **domain**: An optional way to organize messages into groups (e.g. ``admin``,
  ``navigation`` or the default ``messages``) - see :ref:`book-translation-domains`
  section of this chapter;

* **locale**: The locale that the translations are for (e.g. ``en_GB``, ``en``);

* **loader**: Which Loader Class should Symfony use to load and parse the file
  (e.g. ``xlf``, ``php``, ``yml``, etc). The loader can be the name of any
  registered loader. By default, Symfony provides many loaders, including:

    * ``xlf``: XLIFF file;
    * ``php``: PHP file;
    * ``yml``: YAML file.

  The choice of which loader to use is entirely up to you and is a matter of
  taste. The recommended option is to use ``xlf`` for translations.
  For more options, see :ref:`component-translator-message-catalogs` section of
  the Translation component documentation.

.. caution::

    Each time you create a *new* translation resource (or install a bundle
    that includes a translation resource), be sure to clear your cache so
    that Symfony can discover the new translation resources:

    .. code-block:: bash

        $ php app/console cache:clear

.. index::
   single: Translation; From inside a controller

.. _book-translation-basic:

Translation from Inside Controllers
-----------------------------------

Messages can be translated from inside controllers or from inside templates.

To translate a block of text called a *message* from inside a controller, the
:method:`Symfony\\Component\\Translation\\Translator::trans` method of the
``translator`` service is used. Method wraps a message that needs to be
translated::

    // ...
    use Symfony\Component\HttpFoundation\Response;

    public function indexAction()
    {
        $translated = $this->get('translator')->trans('Symfony is great');

        return new Response($translated);
    }

When this code is executed, Symfony will attempt to translate the message
"Symfony is great" based on the ``locale`` of the user.

Message Placeholders
~~~~~~~~~~~~~~~~~~~~

Sometimes, a message containing a variable needs to be translated::

    use Symfony\Component\HttpFoundation\Response;

    public function indexAction($name)
    {
        $translated = $this->get('translator')->trans('Hello '.$name);

        return new Response($translated);
    }

However, creating a translation for this string is impossible since the
translator will try to look up the exact message, including the variable
portions (e.g. *"Hello Ryan"* or *"Hello Fabien"*).

For details on how to handle this situation, see
:ref:`component-translation-placeholders` section of the Translation component
documentation.

Pluralization
~~~~~~~~~~~~~

Another complication is when you have translations that may or may not be
plural, based on some variable:

.. code-block:: text

    There is one apple.
    There are 5 apples.

To handle this, use the
:method:`Symfony\\Component\\Translation\\Translator::transChoice` method
of the ``translator`` service::

    $translator->transChoice(
        'There is one apple|There are %count% apples',
        10,
        array('%count%' => 10)
    );

The second argument (``10`` in this example) is the *number* of objects being
described and is used to determine which translation to use and also to populate
the ``%count%`` placeholder.

Based on the given number, the translator chooses the right plural form.
In English, most words have a singular form when there is exactly one object
and a plural form for all other numbers (0, 2, 3...). So, if ``count`` is
``1``, the translator will use the first string (``There is one apple``)
as the translation. Otherwise it will use ``There are %count% apples``.

For more details on how to handle pluralization, see
:ref:`component-translation-pluralization` section of the Translation component
documentation.

The Translation Process
~~~~~~~~~~~~~~~~~~~~~~~

To actually translate the message, Symfony uses a simple process:

#. The ``locale`` of the current user, which is stored on the request is
   determined;

#. A catalog (e.g. big collection) of translated messages is loaded from
   translation resources defined for the ``locale`` (e.g. ``fr_FR``). Messages
   from the :ref:`fallback locale <book-translation-fallback>` are also loaded
   and added to the catalog if they don't already exist. The end result is a
   large "dictionary" of translations.

#. If the message is located in the catalog, the translation is returned. If
   not, the translator returns the original message.

You start this process by calling ``trans()`` or ``transChoice()``. Then, the
Translator looks for the exact string inside the appropriate message catalog
and returns it (if it exists).

.. index::
   single: Translation; From inside a template

Translations from Inside a Template
-----------------------------------

Most of the time, translation occurs in templates. Symfony provides native
support for both Twig and PHP templates.

.. index::
   single: Translation; From inside a template - Twig

.. _book-translation-twig-template:
.. _book-translation-tags:

Twig Templates
~~~~~~~~~~~~~~

Symfony provides two specialized Twig tags ``trans`` and ``transchoice`` to
help with message translation of *static blocks of text*:

.. code-block:: twig

    {% trans %}Hello %name%{% endtrans %}

    {% transchoice count %}
        {0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples
    {% endtranschoice %}

The ``transchoice`` tag automatically gets the ``%count%`` variable from
the current context and passes it to the translator. This mechanism only
works when you use a placeholder following the ``%var%`` pattern.

.. caution::

    The ``%var%`` notation of placeholders is required when translating in
    Twig templates using the tag.

.. tip::

    If you need to use the percent character (``%``) in a string, escape it by
    doubling it: ``{% trans %}Percent: %percent%%%{% endtrans %}``

.. _book-translation-filters:

The ``trans`` and ``transchoice`` filters can be used to translate *variable
texts* and complex expressions:

.. code-block:: twig

    {{ message|trans }}

    {{ message|trans({'%name%': 'Fabien'}, "app") }}

    {{ message|transchoice(5) }}

    {{ message|transchoice(5, {'%name%': 'Fabien'}, 'app') }}

.. note::

    Using the translation tags or filters have the same effect, but with
    one subtle difference: **automatic output escaping is only applied to
    translations using a filter**. In other words, if you need to be sure
    that your translated message is *not* output escaped, you must apply
    the ``raw`` filter after the translation filter:

    .. code-block:: twig

            {# text translated between tags is never escaped #}
            {% trans %}
                <h3>foo</h3>
            {% endtrans %}

            {% set message = '<h3>foo</h3>' %}

            {# strings and variables translated via a filter are escaped by default #}
            {{ message|trans|raw }}
            {{ '<h3>bar</h3>'|trans|raw }}

.. _book-translation-twig-template-domain:

You can also specify the message domain and pass some additional variables:

.. code-block:: twig

    {% trans with {'%name%': 'Fabien'} from "app" %}Hello %name%{% endtrans %}

    {% trans with {'%name%': 'Fabien'} from "app" into "fr" %}Hello %name%{% endtrans %}

    {% transchoice count with {'%name%': 'Fabien'} from "app" %}
        {0} %name%, there are no apples|{1} %name%, there is one apple|]1,Inf[ %name%, there are %count% apples
    {% endtranschoice %}

You can set the translation domain for an entire Twig template with a single tag:

.. code-block:: twig

    {% trans_default_domain "app" %}

Note that this only influences the current template, not any "included"
template (in order to avoid side effects).

.. versionadded:: 2.1
    The ``trans_default_domain`` tag was introduced in Symfony 2.1.

.. seealso::
    To learn about domains read :ref:`book-translation-domains` section of this
    chapter.

.. index::
   single: Translation; From inside a template - PHP

.. _book-translation-PHP-template:

PHP Templates
~~~~~~~~~~~~~

The translator service is accessible in PHP templates through the
``translator`` helper:

.. code-block:: html+php

    <?php echo $view['translator']->trans('Symfony is great') ?>

    <?php echo $view['translator']->transChoice(
        '{0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
        10,
        array('%count%' => 10)
    ) ?>

.. _book-translation-constraint-messages:

Translating Constraint Messages - Form Component
------------------------------------------------

If you're using Form component with validation constraints then the translation
of error messages is covered by ``validators`` domain.

.. seealso::
    To learn about domains read :ref:`book-translation-domains` section of this
    chapter.

To start, suppose you've created a plain-old-PHP object that you need to use
somewhere in your application::

    // src/AppBundle/Entity/Author.php
    namespace AppBundle\Entity;

    class Author
    {
        public $name;
    }

For example, to guarantee that the ``$name`` property is not empty, add the
following constraint with the ``message`` option set to translation key::

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\NotBlank(message = "author.name.not_blank")
             */
            public $name;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                name:
                    - NotBlank: { message: 'author.name.not_blank' }

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="name">
                    <constraint name="NotBlank">
                        <option name="message">author.name.not_blank</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\NotBlank;

        class Author
        {
            public $name;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('name', new NotBlank(array(
                    'message' => 'author.name.not_blank',
                )));
            }
        }

Finally, create a translation file under the ``validators`` domain for the constraint
messages, typically in the ``Resources/translations/`` directory of the
bundle.

.. configuration-block::

    .. code-block:: xml

        <!-- validators.en.xlf -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="author.name.not_blank">
                        <source>author.name.not_blank</source>
                        <target>Please enter an author name.</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: yaml

        # validators.en.yml
        author.name.not_blank: Please enter an author name.

    .. code-block:: php

        // validators.en.php
        return array(
            'author.name.not_blank' => 'Please enter an author name.',
        );

Translating Database Content
----------------------------

The translation of database content should be handled by Doctrine through
the `Translatable Extension`_ or the `Translatable Behavior`_ (PHP 5.4+).
For more information, see the documentation for these libraries.

.. _book-translation-locales:

Locales
-------
The term *locale* refers roughly to the user's language and country. It
can be any string that your application uses to manage translations and
other format differences (e.g. currency format). The `ISO 639-1`_
*language* code, an underscore (``_``), then the `ISO 3166-1 alpha-2`_
*country* code (e.g. ``fr_FR`` for French/France) is recommended.

The locale used in translations is the one stored on the ``Request`` object.
This is typically set via a ``_locale`` attribute on your routes.

.. _book-translation-fallback:

Fallback Translation Locales
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Imagine that the user's locale is ``fr_FR`` and that you're translating the
key ``Symfony is great``. To find the French translation, Symfony actually
checks translation resources for several locales:

#. First, Symfony looks for the translation in a ``fr_FR`` translation resource
   (e.g. ``messages.fr_FR.xlf``);

#. If it wasn't found, Symfony looks for the translation in a ``fr`` translation
   resource (e.g. ``messages.fr.xlf``);

#. If the translation still isn't found, Symfony uses the ``fallbacks``
   configuration parameter set in default application configuration file
   ``app/config/config.yml`` (see :ref:`book-translation-configuration` section
   of this chapter) to returns the original message.

.. _book-translation-user-locale:

User's Locale and ``Request`` object
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The locale of the current user is stored in the request and is accessible
via the ``Request`` object::

    use Symfony\Component\HttpFoundation\Request;

    public function indexAction(Request $request)
    {
        $locale = $request->getLocale();
    }

To set the user's locale on the entire session, you may want to create a custom
event listener so that it's set before any other parts of the system (i.e. the
translator) need it::

        public function onKernelRequest(GetResponseEvent $event)
        {
            $request = $event->getRequest();

            // some logic to determine the $locale
            $request->getSession()->set('_locale', $locale);
        }

Setting the locale using ``$request->setLocale()`` in the controller is too
late to affect the translator. Either set the locale via a listener (like above),
the URL (see next) or call ``setLocale()`` directly on the ``translator`` service.

Read :doc:`/cookbook/session/locale_sticky_session` cookbook article for more
on the topic.

.. _book-translation-locale-url:

User's Locale and URL
~~~~~~~~~~~~~~~~~~~~~

Since you can store the locale of the user in the session, it may be tempting
to use the same URL to display a resource in different languages based
on the user's locale. For example, ``http://www.example.com/contact`` could
show content in English for one user and French for another user. Unfortunately,
this violates a fundamental rule of the Web: that a particular URL returns
the same resource regardless of the user. To further muddy the problem, which
version of the content would be indexed by search engines?

A better policy is to include the locale in the URL. This is fully-supported
by the routing system using the special ``_locale`` parameter:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/ContactController.php

        // ...
        class ContactController extends Controller
        {
            /**
             * @Route("/{_locale}/contact", name="contact"
             *     requirements={
             *           "_locale": "en|fr|de"
             * })
             */
            public function indexAction($_locale)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        contact:
            path:     /{_locale}/contact
            defaults: { _controller: AppBundle:Contact:index }
            requirements:
                _locale: en|fr|de

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" path="/{_locale}/contact">
                <default key="_controller">AppBundle:Contact:index</default>
                <requirement key="_locale">en|fr|de</requirement>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('contact', new Route(
            '/{_locale}/contact',
            array(
                '_controller' => 'AppBundle:Contact:index',
            ),
            array(
                '_locale'     => 'en|fr|de',
            )
        ));

        return $collection;

For incoming requests, the ``{_locale}`` portion of the URL is matched against
the regular expression ``(en|fr|de)``.

.. tip::

    Read :doc:`/cookbook/routing/service_container_parameters` to learn how to
    avoid hardcoding the ``_locale`` requirement in all your routes.

When using the special ``_locale`` parameter in a route, the matched locale
will *automatically be set on the Request* and can be retrieved via the
:method:`Symfony\\Component\\HttpFoundation\\Request::getLocale` method of the
`Request` object.
In other words, if a user visits the URI ``/fr/contact``, the locale ``fr`` will
automatically be set as the locale for the current request.

.. seealso::

    For information about special routing parameters like ``{_locale}`` see
    :ref:`book-special-routing-parameters` section of the Routing chapter.

.. index::
   single: Translations; Default locale

.. _book-translation-default-locale:

Default Locale
~~~~~~~~~~~~~~

What if the user's locale hasn't been determined? You can guarantee that a
locale is set on each user's request.

To do so use ``default_locale`` configuration parameter for the FrameworkBundle
in the default application configuration file ``app/config/config.yml``::

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            default_locale: en

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config default-locale="en" />
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'default_locale' => 'en',
        ));

.. versionadded:: 2.1
     The ``default_locale`` parameter was defined under the session key
     originally, however, as of 2.1 this has been moved. This is because the
     locale is now set on the request instead of the session.

.. _book-translation-domains:

Domains
-------

As you've seen, message files are organized into the different locales that
they translate. The message files can also be organized further into "domains".

The default domain is ``messages``.
If you're using Form component with validation constraints then the translation
of error messages is covered by ``validators`` domain.

When translating strings that are not in the default domain, you must specify
the domain as the third argument of
:method:`Symfony\\Component\\Translation\\Translator::trans` or
:method:`Symfony\\Component\\Translation\\Translator::transChoice` method::

    $translator->trans('Symfony is great', array(), 'admin');

Symfony will now look for the message in the ``admin`` domain of the specified
locale.

Summary
-------

With the Symfony Translation component, creating an internationalized application
no longer needs to be a painful process and boils down to just a few basic
steps:

* Abstract messages in your application by wrapping each in either the
  :method:`Symfony\\Component\\Translation\\Translator::trans` or
  :method:`Symfony\\Component\\Translation\\Translator::transChoice` methods;

* Translate each message into multiple locales by creating translation message
  files. Symfony discovers and processes each file because its name follows
  a specific convention;

* Manage the user's locale, which is stored on the request, but can also
  be set on the user's session.

To learn even more on the Translation component you can read
:doc:`Translation component documentation </components/translation/usage>`.

.. _`i18n`: https://en.wikipedia.org/wiki/Internationalization_and_localization
.. _`ISO 639-1`: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
.. _`Translatable Extension`: http://atlantic18.github.io/DoctrineExtensions/doc/translatable.html
.. _`Translatable Behavior`: https://github.com/KnpLabs/DoctrineBehaviors
