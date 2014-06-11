.. index::
   single: Translations

Translations
============

The term "internationalization" (often abbreviated `i18n`_) refers to the
process of abstracting strings and other locale-specific pieces out of your
application into a layer where they can be translated and converted based
on the user's locale (i.e. language and country). For text, this means
wrapping each with a function capable of translating the text (or "message")
into the language of the user::

    // text will *always* print out in English
    echo 'Hello World';

    // text can be translated into the end-user's language or
    // default to English
    echo $translator->trans('Hello World');

.. note::

    The term *locale* refers roughly to the user's language and country. It
    can be any string that your application uses to manage translations and
    other format differences (e.g. currency format). The `ISO 639-1`_
    *language* code, an underscore (``_``), then the `ISO 3166-1 alpha-2`_
    *country* code (e.g. ``fr_FR`` for French/France) is recommended.

In this chapter, you'll learn how to use the Translation component in the
Symfony2 framework. You can read the
:doc:`Translation component documentation </components/translation/usage>`
to learn even more. Overall, the process has several steps:

#. :ref:`Enable and configure <book-translation-configuration>` Symfony's
   translation service;

#. Abstract strings (i.e. "messages") by wrapping them in calls to the
   ``Translator`` (":ref:`book-translation-basic`");

#. :ref:`Create translation resources/files <book-translation-resources>`
   for each supported locale that translate each message in the application;

#. Determine, :ref:`set and manage the user's locale <book-translation-user-locale>`
   for the request and optionally
   :doc:`on the user's entire session </cookbook/session/locale_sticky_session>`.

.. _book-translation-configuration:

Configuration
-------------

Translations are handled by a ``translator`` :term:`service` that uses the
user's locale to lookup and return translated messages. Before using it,
enable the ``translator`` in your configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            translator: { fallback: en }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:translator fallback="en" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'translator' => array('fallback' => 'en'),
        ));

See :ref:`book-translation-fallback` for details on the ``fallback`` key
and what Symfony does when it doesn't find a translation.

The locale used in translations is the one stored on the request. This is
typically set via a ``_locale`` attribute on your routes (see :ref:`book-translation-locale-url`).

.. _book-translation-basic:

Basic Translation
-----------------

Translation of text is done through the  ``translator`` service
(:class:`Symfony\\Component\\Translation\\Translator`). To translate a block
of text (called a *message*), use the
:method:`Symfony\\Component\\Translation\\Translator::trans` method. Suppose,
for example, that you're translating a simple message from inside a controller::

    // ...
    use Symfony\Component\HttpFoundation\Response;

    public function indexAction()
    {
        $translated = $this->get('translator')->trans('Symfony2 is great');

        return new Response($translated);
    }

.. _book-translation-resources:

When this code is executed, Symfony2 will attempt to translate the message
"Symfony2 is great" based on the ``locale`` of the user. For this to work,
you need to tell Symfony2 how to translate the message via a "translation
resource", which is usually a file that contains a collection of translations
for a given locale. This "dictionary" of translations can be created in several
different formats, XLIFF being the recommended format:

.. configuration-block::

    .. code-block:: xml

        <!-- messages.fr.xliff -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Symfony2 is great</source>
                        <target>J'aime Symfony2</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // messages.fr.php
        return array(
            'Symfony2 is great' => 'J\'aime Symfony2',
        );

    .. code-block:: yaml

        # messages.fr.yml
        Symfony2 is great: J'aime Symfony2

For information on where these files should be located, see
:ref:`book-translation-resource-locations`.

Now, if the language of the user's locale is French (e.g. ``fr_FR`` or ``fr_BE``),
the message will be translated into ``J'aime Symfony2``. You can also translate
the message inside your :ref:`templates <book-translation-tags>`.

The Translation Process
~~~~~~~~~~~~~~~~~~~~~~~

To actually translate the message, Symfony2 uses a simple process:

* The ``locale`` of the current user, which is stored on the request is determined;

* A catalog (e.g. big collection) of translated messages is loaded from translation
  resources defined for the ``locale`` (e.g. ``fr_FR``). Messages from the
  :ref:`fallback locale <book-translation-fallback>` are also loaded and
  added to the catalog if they don't already exist. The end result is a large
  "dictionary" of translations.

* If the message is located in the catalog, the translation is returned. If
  not, the translator returns the original message.

When using the ``trans()`` method, Symfony2 looks for the exact string inside
the appropriate message catalog and returns it (if it exists).

Message Placeholders
--------------------

Sometimes, a message containing a variable needs to be translated::

    use Symfony\Component\HttpFoundation\Response;

    public function indexAction($name)
    {
        $translated = $this->get('translator')->trans('Hello '.$name);

        return new Response($translated);
    }

However, creating a translation for this string is impossible since the translator
will try to look up the exact message, including the variable portions
(e.g. *"Hello Ryan"* or *"Hello Fabien"*).

For details on how to handle this situation, see :ref:`component-translation-placeholders`
in the components documentation. For how to do this in templates, see :ref:`book-translation-tags`.

Pluralization
-------------

Another complication is when you have translations that may or may not be
plural, based on some variable:

.. code-block:: text

    There is one apple.
    There are 5 apples.

To handle this, use the :method:`Symfony\\Component\\Translation\\Translator::transChoice`
method or the ``transchoice`` tag/filter in your :ref:`template <book-translation-tags>`.

For much more information, see :ref:`component-translation-pluralization`
in the Translation component documentation.

Translations in Templates
-------------------------

Most of the time, translation occurs in templates. Symfony2 provides native
support for both Twig and PHP templates.

.. _book-translation-tags:

Twig Templates
~~~~~~~~~~~~~~

Symfony2 provides specialized Twig tags (``trans`` and ``transchoice``) to
help with message translation of *static blocks of text*:

.. code-block:: jinja

    {% trans %}Hello %name%{% endtrans %}

    {% transchoice count %}
        {0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples
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

You can also specify the message domain and pass some additional variables:

.. code-block:: jinja

    {% trans with {'%name%': 'Fabien'} from "app" %}Hello %name%{% endtrans %}

    {% trans with {'%name%': 'Fabien'} from "app" into "fr" %}Hello %name%{% endtrans %}

    {% transchoice count with {'%name%': 'Fabien'} from "app" %}
        {0} %name%, there are no apples|{1} %name%, there is one apple|]1,Inf] %name%, there are %count% apples
    {% endtranschoice %}

.. _book-translation-filters:

The ``trans`` and ``transchoice`` filters can be used to translate *variable
texts* and complex expressions:

.. code-block:: jinja

    {{ message|trans }}

    {{ message|transchoice(5) }}

    {{ message|trans({'%name%': 'Fabien'}, "app") }}

    {{ message|transchoice(5, {'%name%': 'Fabien'}, 'app') }}

.. tip::

    Using the translation tags or filters have the same effect, but with
    one subtle difference: automatic output escaping is only applied to
    translations using a filter. In other words, if you need to be sure
    that your translated message is *not* output escaped, you must apply
    the ``raw`` filter after the translation filter:

    .. code-block:: jinja

            {# text translated between tags is never escaped #}
            {% trans %}
                <h3>foo</h3>
            {% endtrans %}

            {% set message = '<h3>foo</h3>' %}

            {# strings and variables translated via a filter are escaped by default #}
            {{ message|trans|raw }}
            {{ '<h3>bar</h3>'|trans|raw }}

.. tip::

    You can set the translation domain for an entire Twig template with a single tag:

    .. code-block:: jinja

           {% trans_default_domain "app" %}

    Note that this only influences the current template, not any "included"
    template (in order to avoid side effects).

PHP Templates
~~~~~~~~~~~~~

The translator service is accessible in PHP templates through the
``translator`` helper:

.. code-block:: html+php

    <?php echo $view['translator']->trans('Symfony2 is great') ?>

    <?php echo $view['translator']->transChoice(
        '{0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
        10,
        array('%count%' => 10)
    ) ?>

.. _book-translation-resource-locations:

Translation Resource/File Names and Locations
---------------------------------------------

Symfony2 looks for message files (i.e. translations) in the following locations:

* the ``app/Resources/translations`` directory;

* the ``app/Resources/<bundle name>/translations`` directory;

* the ``Resources/translations/`` directory inside of any bundle.

The locations are listed here with the highest priority first. That is, you can
override the translation messages of a bundle in any of the top 2 directories.

The override mechanism works at a key level: only the overridden keys need
to be listed in a higher priority message file. When a key is not found
in a message file, the translator will automatically fall back to the lower
priority message files.

The filename of the translation files is also important: each message file
must be named according to the following path: ``domain.locale.loader``:

* **domain**: An optional way to organize messages into groups (e.g. ``admin``,
  ``navigation`` or the default ``messages``) - see :ref:`using-message-domains`;

* **locale**: The locale that the translations are for (e.g. ``en_GB``, ``en``, etc);

* **loader**: How Symfony2 should load and parse the file (e.g. ``xliff``,
  ``php``, ``yml``, etc).

The loader can be the name of any registered loader. By default, Symfony
provides many loaders, including:

* ``xliff``: XLIFF file;
* ``php``: PHP file;
* ``yml``: YAML file.

The choice of which loader to use is entirely up to you and is a matter of
taste. For more options, see :ref:`component-translator-message-catalogs`.

.. note::

    You can also store translations in a database, or any other storage by
    providing a custom class implementing the
    :class:`Symfony\\Component\\Translation\\Loader\\LoaderInterface` interface.
    See the :ref:`dic-tags-translation-loader` tag for more information.

.. caution::

    Each time you create a *new* translation resource (or install a bundle
    that includes a translation resource), be sure to clear your cache so
    that Symfony can discover the new translation resources:

    .. code-block:: bash

        $ php app/console cache:clear

.. _book-translation-fallback:

Fallback Translation Locales
----------------------------

Imagine that the user's locale is ``fr_FR`` and that you're translating the
key ``Symfony2 is great``. To find the French translation, Symfony actually
checks translation resources for several different locales:

1. First, Symfony looks for the translation in a ``fr_FR`` translation resource
   (e.g. ``messages.fr_FR.xliff``);

2. If it wasn't found, Symfony looks for the translation in a ``fr`` translation
   resource (e.g. ``messages.fr.xliff``);

3. If the translation still isn't found, Symfony uses the ``fallback`` configuration
   parameter, which defaults to ``en`` (see `Configuration`_).

.. _book-translation-user-locale:

Handling the User's Locale
--------------------------

The locale of the current user is stored in the request and is accessible
via the ``request`` object::

    use Symfony\Component\HttpFoundation\Request;

    public function indexAction(Request $request)
    {
        $locale = $request->getLocale();

        $request->setLocale('en_US');
    }

.. tip::

    Read :doc:`/cookbook/session/locale_sticky_session` to learn, how to store
    the user's locale in the session.

.. index::
   single: Translations; Fallback and default locale

See the :ref:`book-translation-locale-url` section below about setting the
locale via routing.

.. _book-translation-locale-url:

The Locale and the URL
~~~~~~~~~~~~~~~~~~~~~~

Since you can store the locale of the user in the session, it may be tempting
to use the same URL to display a resource in many different languages based
on the user's locale. For example, ``http://www.example.com/contact`` could
show content in English for one user and French for another user. Unfortunately,
this violates a fundamental rule of the Web: that a particular URL returns
the same resource regardless of the user. To further muddy the problem, which
version of the content would be indexed by search engines?

A better policy is to include the locale in the URL. This is fully-supported
by the routing system using the special ``_locale`` parameter:

.. configuration-block::

    .. code-block:: yaml

        contact:
            path:     /{_locale}/contact
            defaults: { _controller: AcmeDemoBundle:Contact:index }
            requirements:
                _locale: en|fr|de

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" path="/{_locale}/contact">
                <default key="_controller">AcmeDemoBundle:Contact:index</default>
                <requirement key="_locale">en|fr|de</requirement>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('contact', new Route(
            '/{_locale}/contact',
            array(
                '_controller' => 'AcmeDemoBundle:Contact:index',
            ),
            array(
                '_locale'     => 'en|fr|de',
            )
        ));

        return $collection;

When using the special ``_locale`` parameter in a route, the matched locale
will *automatically be set on the Request* and can be retrieved via the
:method:`Symfony\\Component\\HttpFoundation\\Request::getLocale` method.
In other words, if a user
visits the URI ``/fr/contact``, the locale ``fr`` will automatically be set
as the locale for the current request.

You can now use the locale to create routes to other translated pages
in your application.

Setting a default Locale
~~~~~~~~~~~~~~~~~~~~~~~~

What if the user's locale hasn't been determined? You can guarantee that a
locale is set on each user's request by defining a ``default_locale`` for
the framework:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            default_locale: en

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <framework:default-locale>en</framework:default-locale>
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'default_locale' => 'en',
        ));

.. _book-translation-constraint-messages:

Translating Constraint Messages
-------------------------------

If you're using validation constraints with the form framework, then translating
the error messages is easy: simply create a translation resource for the
``validators`` :ref:`domain <using-message-domains>`.

To start, suppose you've created a plain-old-PHP object that you need to
use somewhere in your application::

    // src/Acme/BlogBundle/Entity/Author.php
    namespace Acme\BlogBundle\Entity;

    class Author
    {
        public $name;
    }

Add constraints though any of the supported methods. Set the message option to the
translation source text. For example, to guarantee that the ``$name`` property is
not empty, add the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                name:
                    - NotBlank: { message: "author.name.not_blank" }

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\NotBlank(message = "author.name.not_blank")
             */
            public $name;
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\BlogBundle\Entity\Author">
                <property name="name">
                    <constraint name="NotBlank">
                        <option name="message">author.name.not_blank</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/BlogBundle/Entity/Author.php

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

Create a translation file under the ``validators`` catalog for the constraint
messages, typically in the ``Resources/translations/`` directory of the
bundle.

.. configuration-block::

    .. code-block:: xml

        <!-- validators.en.xliff -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>author.name.not_blank</source>
                        <target>Please enter an author name.</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // validators.en.php
        return array(
            'author.name.not_blank' => 'Please enter an author name.',
        );

    .. code-block:: yaml

        # validators.en.yml
        author.name.not_blank: Please enter an author name.

Translating Database Content
----------------------------

The translation of database content should be handled by Doctrine through
the `Translatable Extension`_ or the `Translatable Behavior`_ (PHP 5.4+).
For more information, see the documentation for these libraries.

Debugging Translations
----------------------

.. versionadded:: 2.5
    The ``translation:debug`` command was introduced in Symfony 2.5.

When maintaining a bundle, you may use or remove the usage of a translation
message without updating all message catalogues. The ``translation:debug``
command helps you to find these missing or unused translation messages for a
given locale. It shows you a table with the result when translating the
message in the given locale and the result when the fallback would be used.
On top of that, it also shows you when the translation is the same as the
fallback translation (this could indicate that the message was not correctly
translated).

Thanks to the messages extractors, the command will detect the translation
tag or filter usages in Twig templates:

.. code-block:: jinja

    {% trans %}Symfony2 is great{% endtrans %}

    {{ 'Symfony2 is great'|trans }}

    {{ 'Symfony2 is great'|transchoice(1) }}

    {% transchoice 1 %}Symfony2 is great{% endtranschoice %}

It will also detect the following translator usages in PHP templates:

.. code-block:: php

    $view['translator']->trans("Symfony2 is great");

    $view['translator']->trans('Symfony2 is great');

.. caution::

    The extractors are not able to inspect the messages translated outside templates which means
    that translator usages in form labels or inside your controllers won't be detected.
    Dynamic translations involving variables or expressions are not detected in templates,
    which means this example won't be analyzed:

    .. code-block:: jinja

        {% set message = 'Symfony2 is great' %}
        {{ message|trans }}

Suppose your application's default_locale is ``fr`` and you have configured ``en`` as the fallback locale
(see :ref:`book-translation-configuration` and :ref:`book-translation-fallback` for how to configure these).
And suppose you've already setup some translations for the ``fr`` locale inside an AcmeDemoBundle:

.. configuration-block::

    .. code-block:: xml

        <!-- src/Acme/AcmeDemoBundle/Resources/translations/messages.fr.xliff -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Symfony2 is great</source>
                        <target>J'aime Symfony2</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // src/Acme/AcmeDemoBundle/Resources/translations/messages.fr.php
        return array(
            'Symfony2 is great' => 'J\'aime Symfony2',
        );

    .. code-block:: yaml

        # src/Acme/AcmeDemoBundle/Resources/translations/messages.fr.yml
        Symfony2 is great: J'aime Symfony2

and for the ``en`` locale:

.. configuration-block::

    .. code-block:: xml

        <!-- src/Acme/AcmeDemoBundle/Resources/translations/messages.en.xliff -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Symfony2 is great</source>
                        <target>Symfony2 is great</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // src/Acme/AcmeDemoBundle/Resources/translations/messages.en.php
        return array(
            'Symfony2 is great' => 'Symfony2 is great',
        );

    .. code-block:: yaml

        # src/Acme/AcmeDemoBundle/Resources/translations/messages.en.yml
        Symfony2 is great: Symfony2 is great

To inspect all messages in the ``fr`` locale for the AcmeDemoBundle, run:

.. code-block:: bash

    $ php app/console translation:debug fr AcmeDemoBundle

You will get this output:

.. image:: /images/book/translation/debug_1.png
    :align: center

It indicates that the message ``Symfony2 is great`` is unused because it is translated,
but you haven't used it anywhere yet.

Now, if you translate the message in one of your templates, you will get this output:

.. image:: /images/book/translation/debug_2.png
    :align: center

The state is empty which means the message is translated in the ``fr`` locale and used in one or more templates.

If you delete the message ``Symfony2 is great`` from your translation file for the ``fr`` locale
and run the command, you will get:

.. image:: /images/book/translation/debug_3.png
    :align: center

The state indicates the message is missing because it is not translated in the ``fr`` locale
but it is still used in the template.
Moreover, the message in the ``fr`` locale equals to the message in the ``en`` locale.
This is a special case because the untranslated message id equals its translation in the ``en`` locale.

If you copy the content of the translation file in the ``en`` locale, to the translation file
in the ``fr`` locale and run the command, you will get:

.. image:: /images/book/translation/debug_4.png
    :align: center

You can see that the translations of the message are identical in the ``fr`` and ``en`` locales
which means this message was probably copied from French to English and maybe you forgot to translate it.

By default all domains are inspected, but it is possible to specify a single domain:

.. code-block:: bash

    $ php app/console translation:debug en AcmeDemoBundle --domain=messages

When bundles have a lot of messages, it is useful to display only the unused
or only the missing messages, by using the ``--only-unused`` or ``--only-missing`` switches:

.. code-block:: bash

    $ php app/console translation:debug en AcmeDemoBundle --only-unused
    $ php app/console translation:debug en AcmeDemoBundle --only-missing

Summary
-------

With the Symfony2 Translation component, creating an internationalized application
no longer needs to be a painful process and boils down to just a few basic
steps:

* Abstract messages in your application by wrapping each in either the
  :method:`Symfony\\Component\\Translation\\Translator::trans` or
  :method:`Symfony\\Component\\Translation\\Translator::transChoice` methods
  (learn about this in :doc:`/components/translation/usage`);

* Translate each message into multiple locales by creating translation message
  files. Symfony2 discovers and processes each file because its name follows
  a specific convention;

* Manage the user's locale, which is stored on the request, but can also
  be set on the user's session.

.. _`i18n`: http://en.wikipedia.org/wiki/Internationalization_and_localization
.. _`ISO 3166-1 alpha-2`: http://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
.. _`ISO 639-1`: http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
.. _`Translatable Extension`: https://github.com/l3pp4rd/DoctrineExtensions
.. _`Translatable Behavior`: https://github.com/KnpLabs/DoctrineBehaviors
