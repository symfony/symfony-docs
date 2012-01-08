.. index::
   single: Translations

Translations
============

The term "internationalization" (often abbreviated `i18n`_) refers to the process
of abstracting strings and other locale-specific pieces out of your application
and into a layer where they can be translated and converted based on the user's
locale (i.e. language and country). For text, this means wrapping each with a
function capable of translating the text (or "message") into the language of
the user::

    // text will *always* print out in English
    echo 'Hello World';

    // text can be translated into the end-user's language or default to English
    echo $translator->trans('Hello World');

.. note::

    The term *locale* refers roughly to the user's language and country. It
    can be any string that your application then uses to manage translations
    and other format differences (e.g. currency format). We recommended the
    ISO639-1 *language* code, an underscore (``_``), then the ISO3166 *country*
    code (e.g. ``fr_FR`` for French/France).

In this chapter, we'll learn how to prepare an application to support multiple
locales and then how to create translations for multiple locales. Overall,
the process has several common steps:

1. Enable and configure Symfony's ``Translation`` component;

2. Abstract strings (i.e. "messages") by wrapping them in calls to the ``Translator``;

3. Create translation resources for each supported locale that translate
   each message in the application;

4. Determine, set and manage the user's locale for the request and optionally
   on the user's entire session.

.. index::
   single: Translations; Configuration

Configuration
-------------

Translations are handled by a ``Translator`` :term:`service` that uses the
user's locale to lookup and return translated messages. Before using it,
enable the ``Translator`` in your configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            translator: { fallback: en }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <framework:translator fallback="en" />
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'translator' => array('fallback' => 'en'),
        ));

The ``fallback`` option defines the fallback locale when a translation does
not exist in the user's locale.

.. tip::

    When a translation does not exist for a locale, the translator first tries
    to find the translation for the language (``fr`` if the locale is
    ``fr_FR`` for instance). If this also fails, it looks for a translation
    using the fallback locale.

The locale used in translations is the one stored on the request. This is
typically set via a ``_locale`` attribute on your routes (see :ref:`book-translation-locale-url`).

.. index::
   single: Translations; Basic translation

Basic Translation
-----------------

Translation of text is done through the  ``translator`` service
(:class:`Symfony\\Component\\Translation\\Translator`). To translate a block
of text (called a *message*), use the
:method:`Symfony\\Component\\Translation\\Translator::trans` method. Suppose,
for example, that we're translating a simple message from inside a controller:

.. code-block:: php

    public function indexAction()
    {
        $t = $this->get('translator')->trans('Symfony2 is great');

        return new Response($t);
    }

When this code is executed, Symfony2 will attempt to translate the message
"Symfony2 is great" based on the ``locale`` of the user. For this to work,
we need to tell Symfony2 how to translate the message via a "translation
resource", which is a collection of message translations for a given locale.
This "dictionary" of translations can be created in several different formats,
XLIFF being the recommended format:

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

Now, if the language of the user's locale is French (e.g. ``fr_FR`` or ``fr_BE``),
the message will be translated into ``J'aime Symfony2``.

The Translation Process
~~~~~~~~~~~~~~~~~~~~~~~

To actually translate the message, Symfony2 uses a simple process:

* The ``locale`` of the current user, which is stored on the request (or
  stored as ``_locale`` on the session), is determined;

* A catalog of translated messages is loaded from translation resources defined
  for the ``locale`` (e.g. ``fr_FR``). Messages from the fallback locale are
  also loaded and added to the catalog if they don't already exist. The end
  result is a large "dictionary" of translations. See `Message Catalogues`_
  for more details;

* If the message is located in the catalog, the translation is returned. If
  not, the translator returns the original message.

When using the ``trans()`` method, Symfony2 looks for the exact string inside
the appropriate message catalog and returns it (if it exists).

.. index::
   single: Translations; Message placeholders

Message Placeholders
~~~~~~~~~~~~~~~~~~~~

Sometimes, a message containing a variable needs to be translated:

.. code-block:: php

    public function indexAction($name)
    {
        $t = $this->get('translator')->trans('Hello '.$name);

        return new Response($t);
    }

However, creating a translation for this string is impossible since the translator
will try to look up the exact message, including the variable portions
(e.g. "Hello Ryan" or "Hello Fabien"). Instead of writing a translation
for every possible iteration of the ``$name`` variable, we can replace the
variable with a "placeholder":

.. code-block:: php

    public function indexAction($name)
    {
        $t = $this->get('translator')->trans('Hello %name%', array('%name%' => $name));

        new Response($t);
    }

Symfony2 will now look for a translation of the raw message (``Hello %name%``)
and *then* replace the placeholders with their values. Creating a translation
is done just as before:

.. configuration-block::

    .. code-block:: xml

        <!-- messages.fr.xliff -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Hello %name%</source>
                        <target>Bonjour %name%</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // messages.fr.php
        return array(
            'Hello %name%' => 'Bonjour %name%',
        );

    .. code-block:: yaml

        # messages.fr.yml
        'Hello %name%': Hello %name%

.. note::

    The placeholders can take on any form as the full message is reconstructed
    using the PHP `strtr function`_. However, the ``%var%`` notation is
    required when translating in Twig templates, and is overall a sensible
    convention to follow.

As we've seen, creating a translation is a two-step process:

1. Abstract the message that needs to be translated by processing it through
   the ``Translator``.

2. Create a translation for the message in each locale that you choose to
   support.

The second step is done by creating message catalogues that define the translations
for any number of different locales.

.. index::
   single: Translations; Message catalogues

Message Catalogues
------------------

When a message is translated, Symfony2 compiles a message catalogue for the
user's locale and looks in it for a translation of the message. A message
catalogue is like a dictionary of translations for a specific locale. For
example, the catalogue for the ``fr_FR`` locale might contain the following
translation:

    Symfony2 is Great => J'aime Symfony2

It's the responsibility of the developer (or translator) of an internationalized
application to create these translations. Translations are stored on the
filesystem and discovered by Symfony, thanks to some conventions.

.. tip::

    Each time you create a *new* translation resource (or install a bundle
    that includes a translation resource), be sure to clear your cache so
    that Symfony can discover the new translation resource:
    
    .. code-block:: bash
    
        php app/console cache:clear

.. index::
   single: Translations; Translation resource locations

Translation Locations and Naming Conventions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony2 looks for message files (i.e. translations) in two locations:

* For messages found in a bundle, the corresponding message files should
  live in the ``Resources/translations/`` directory of the bundle;

* To override any bundle translations, place message files in the
  ``app/Resources/translations`` directory.

The filename of the translations is also important as Symfony2 uses a convention
to determine details about the translations. Each message file must be named
according to the following pattern: ``domain.locale.loader``:

* **domain**: An optional way to organize messages into groups (e.g. ``admin``,
  ``navigation`` or the default ``messages``) - see `Using Message Domains`_;

* **locale**: The locale that the translations are for (e.g. ``en_GB``, ``en``, etc);

* **loader**: How Symfony2 should load and parse the file (e.g. ``xliff``,
  ``php`` or ``yml``).

The loader can be the name of any registered loader. By default, Symfony
provides the following loaders:

* ``xliff``: XLIFF file;
* ``php``:   PHP file;
* ``yml``:  YAML file.

The choice of which loader to use is entirely up to you and is a matter of
taste.

.. note::

    You can also store translations in a database, or any other storage by
    providing a custom class implementing the
    :class:`Symfony\\Component\\Translation\\Loader\\LoaderInterface` interface.
    See :doc:`Custom Translation Loaders </cookbook/translation/custom_loader>`
    below to learn how to register custom loaders.

.. index::
   single: Translations; Creating translation resources

Creating Translations
~~~~~~~~~~~~~~~~~~~~~

The act of creating translation files is an important part of "localization"
(often abbreviated `L10n`_). Translation files consist of a series of
id-translation pairs for the given domain and locale. The id is the identifier
for the individual translation, and can be the message in the main locale (e.g.
"Symfony is great") of your application or a unique identifier (e.g.
"symfony2.great" - see the sidebar below):

.. configuration-block::

    .. code-block:: xml

        <!-- src/Acme/DemoBundle/Resources/translations/messages.fr.xliff -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Symfony2 is great</source>
                        <target>J'aime Symfony2</target>
                    </trans-unit>
                    <trans-unit id="2">
                        <source>symfony2.great</source>
                        <target>J'aime Symfony2</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // src/Acme/DemoBundle/Resources/translations/messages.fr.php
        return array(
            'Symfony2 is great' => 'J\'aime Symfony2',
            'symfony2.great'    => 'J\'aime Symfony2',
        );

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/translations/messages.fr.yml
        Symfony2 is great: J'aime Symfony2
        symfony2.great:    J'aime Symfony2

Symfony2 will discover these files and use them when translating either
"Symfony2 is great" or "symfony2.great" into a French language locale (e.g.
``fr_FR`` or ``fr_BE``).

.. sidebar:: Using Real or Keyword Messages

    This example illustrates the two different philosophies when creating
    messages to be translated:

    .. code-block:: php

        $t = $translator->trans('Symfony2 is great');

        $t = $translator->trans('symfony2.great');

    In the first method, messages are written in the language of the default
    locale (English in this case). That message is then used as the "id"
    when creating translations.

    In the second method, messages are actually "keywords" that convey the
    idea of the message. The keyword message is then used as the "id" for
    any translations. In this case, translations must be made for the default
    locale (i.e. to translate ``symfony2.great`` to ``Symfony2 is great``).

    The second method is handy because the message key won't need to be changed
    in every translation file if we decide that the message should actually
    read "Symfony2 is really great" in the default locale.

    The choice of which method to use is entirely up to you, but the "keyword"
    format is often recommended. 

    Additionally, the ``php`` and ``yaml`` file formats support nested ids to
    avoid repeating yourself if you use keywords instead of real text for your
    ids:

    .. configuration-block::

        .. code-block:: yaml

            symfony2:
                is:
                    great: Symfony2 is great
                    amazing: Symfony2 is amazing
                has:
                    bundles: Symfony2 has bundles
            user:
                login: Login

        .. code-block:: php

            return array(
                'symfony2' => array(
                    'is' => array(
                        'great' => 'Symfony2 is great',
                        'amazing' => 'Symfony2 is amazing',
                    ),
                    'has' => array(
                        'bundles' => 'Symfony2 has bundles',
                    ),
                ),
                'user' => array(
                    'login' => 'Login',
                ),
            );

    The multiple levels are flattened into single id/translation pairs by
    adding a dot (.) between every level, therefore the above examples are
    equivalent to the following:

    .. configuration-block::

        .. code-block:: yaml

            symfony2.is.great: Symfony2 is great
            symfony2.is.amazing: Symfony2 is amazing
            symfony2.has.bundles: Symfony2 has bundles
            user.login: Login

        .. code-block:: php

            return array(
                'symfony2.is.great' => 'Symfony2 is great',
                'symfony2.is.amazing' => 'Symfony2 is amazing',
                'symfony2.has.bundles' => 'Symfony2 has bundles',
                'user.login' => 'Login',
            );

.. index::
   single: Translations; Message domains

Using Message Domains
---------------------

As we've seen, message files are organized into the different locales that
they translate. The message files can also be organized further into "domains".
When creating message files, the domain is the first portion of the filename.
The default domain is ``messages``. For example, suppose that, for organization,
translations were split into three different domains: ``messages``, ``admin``
and ``navigation``. The French translation would have the following message
files:

* ``messages.fr.xliff``
* ``admin.fr.xliff``
* ``navigation.fr.xliff``

When translating strings that are not in the default domain (``messages``),
you must specify the domain as the third argument of ``trans()``:

.. code-block:: php

    $this->get('translator')->trans('Symfony2 is great', array(), 'admin');

Symfony2 will now look for the message in the ``admin`` domain of the user's
locale.

.. index::
   single: Translations; User's locale

Handling the User's Locale
--------------------------

The locale of the current user is stored in the request and is accessible
via the ``request`` object:

.. code-block:: php

    // access the reqest object in a standard controller
    $request = $this->getRequest();

    $locale = $request->getLocale();

    $request->setLocale('en_US');

.. index::
   single: Translations; Fallback and default locale

It is also possible to store the locale in the session instead of on a per 
request basis. If you do this, each subsequent request will have this locale.

.. code-block:: php

    $this->get('session')->set('_locale', 'en_US');

See the :ref:`.. _book-translation-locale-url:` section below about setting
the locale via routing.

Fallback and Default Locale
~~~~~~~~~~~~~~~~~~~~~~~~~~~

If the locale hasn't been set explicitly in the session, the ``fallback_locale``
configuration parameter will be used by the ``Translator``. The parameter
defaults to ``en`` (see `Configuration`_).

Alternatively, you can guarantee that a locale is set on each user's request
by defining a ``default_locale`` for the framework:

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

.. versionadded:: 2.1

     The ``default_locale`` parameter was defined under the session key
     originally, however, as of 2.1 this has been moved. This is because the 
     locale is now set on the request instead of the session.

.. _book-translation-locale-url:

The Locale and the URL
~~~~~~~~~~~~~~~~~~~~~~

Since you can store the locale of the user is in the session, it may be tempting
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
            pattern:   /{_locale}/contact
            defaults:  { _controller: AcmeDemoBundle:Contact:index, _locale: en }
            requirements:
                _locale: en|fr|de

    .. code-block:: xml

        <route id="contact" pattern="/{_locale}/contact">
            <default key="_controller">AcmeDemoBundle:Contact:index</default>
            <default key="_locale">en</default>
            <requirement key="_locale">en|fr|de</requirement>
        </route>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('contact', new Route('/{_locale}/contact', array(
            '_controller' => 'AcmeDemoBundle:Contact:index',
            '_locale'     => 'en',
        ), array(
            '_locale'     => 'en|fr|de'
        )));

        return $collection;

When using the special `_locale` parameter in a route, the matched locale
will *automatically be set on the user's session*. In other words, if a user
visits the URI ``/fr/contact``, the locale ``fr`` will automatically be set
as the locale for the user's session.

You can now use the user's locale to create routes to other translated pages
in your application.

.. index::
   single: Translations; Pluralization

Pluralization
-------------

Message pluralization is a tough topic as the rules can be quite complex. For
instance, here is the mathematic representation of the Russian pluralization
rules::

    (($number % 10 == 1) && ($number % 100 != 11)) ? 0 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 1 : 2);

As you can see, in Russian, you can have three different plural forms, each
given an index of 0, 1 or 2. For each form, the plural is different, and
so the translation is also different.

When a translation has different forms due to pluralization, you can provide
all the forms as a string separated by a pipe (``|``)::

    'There is one apple|There are %count% apples'

To translate pluralized messages, use the
:method:`Symfony\\Component\\Translation\\Translator::transChoice` method:

.. code-block:: php

    $t = $this->get('translator')->transChoice(
        'There is one apple|There are %count% apples',
        10,
        array('%count%' => 10)
    );

The second argument (``10`` in this example), is the *number* of objects being
described and is used to determine which translation to use and also to populate
the ``%count%`` placeholder.

Based on the given number, the translator chooses the right plural form.
In English, most words have a singular form when there is exactly one object
and a plural form for all other numbers (0, 2, 3...). So, if ``count`` is
``1``, the translator will use the first string (``There is one apple``)
as the translation. Otherwise it will use ``There are %count% apples``.

Here is the French translation::

    'Il y a %count% pomme|Il y a %count% pommes'

Even if the string looks similar (it is made of two sub-strings separated by a
pipe), the French rules are different: the first form (no plural) is used when
``count`` is ``0`` or ``1``. So, the translator will automatically use the
first string (``Il y a %count% pomme``) when ``count`` is ``0`` or ``1``.

Each locale has its own set of rules, with some having as many as six different
plural forms with complex rules behind which numbers map to which plural form.
The rules are quite simple for English and French, but for Russian, you'd
may want a hint to know which rule matches which string. To help translators,
you can optionally "tag" each string::

    'one: There is one apple|some: There are %count% apples'

    'none_or_one: Il y a %count% pomme|some: Il y a %count% pommes'

The tags are really only hints for translators and don't affect the logic
used to determine which plural form to use. The tags can be any descriptive
string that ends with a colon (``:``). The tags also do not need to be the
same in the original message as in the translated one.

.. tip:

    As tags are optional, the translator doesn't use them (the translator will
    only get a string based on its position in the string).

Explicit Interval Pluralization
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The easiest way to pluralize a message is to let Symfony2 use internal logic
to choose which string to use based on a given number. Sometimes, you'll
need more control or want a different translation for specific cases (for
``0``, or when the count is negative, for example). For such cases, you can
use explicit math intervals::

    '{0} There are no apples|{1} There is one apple|]1,19] There are %count% apples|[20,Inf] There are many apples'

The intervals follow the `ISO 31-11`_ notation. The above string specifies
four different intervals: exactly ``0``, exactly ``1``, ``2-19``, and ``20``
and higher.

You can also mix explicit math rules and standard rules. In this case, if
the count is not matched by a specific interval, the standard rules take
effect after removing the explicit rules::

    '{0} There are no apples|[20,Inf] There are many apples|There is one apple|a_few: There are %count% apples'

For example, for ``1`` apple, the standard rule ``There is one apple`` will
be used. For ``2-19`` apples, the second standard rule ``There are %count%
apples`` will be selected.

An :class:`Symfony\\Component\\Translation\\Interval` can represent a finite set
of numbers::

    {1,2,3,4}

Or numbers between two other numbers::

    [1, +Inf[
    ]-1,2[

The left delimiter can be ``[`` (inclusive) or ``]`` (exclusive). The right
delimiter can be ``[`` (exclusive) or ``]`` (inclusive). Beside numbers, you
can use ``-Inf`` and ``+Inf`` for the infinite.

.. index::
   single: Translations; In templates

Translations in Templates
-------------------------

Most of the time, translation occurs in templates. Symfony2 provides native
support for both Twig and PHP templates.

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

.. tip::

    If you need to use the percent character (``%``) in a string, escape it by
    doubling it: ``{% trans %}Percent: %percent%%%{% endtrans %}``

You can also specify the message domain and pass some additional variables:

.. code-block:: jinja

    {% trans with {'%name%': 'Fabien'} from "app" %}Hello %name%{% endtrans %}

    {% trans with {'%name%': 'Fabien'} from "app" into "fr" %}Hello %name%{% endtrans %}

    {% transchoice count with {'%name%': 'Fabien'} from "app" %}
        {0} There is no apples|{1} There is one apple|]1,Inf] There are %count% apples
    {% endtranschoice %}

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
    variables translated using a filter. In other words, if you need to
    be sure that your translated variable is *not* output escaped, you must
    apply the raw filter after the translation filter:

    .. code-block:: jinja

            {# text translated between tags is never escaped #}
            {% trans %}
                <h3>foo</h3>
            {% endtrans %}

            {% set message = '<h3>foo</h3>' %}

            {# a variable translated via a filter is escaped by default #}
            {{ message|trans|raw }}

            {# but static strings are never escaped #}
            {{ '<h3>foo</h3>'|trans }}

PHP Templates
~~~~~~~~~~~~~

The translator service is accessible in PHP templates through the
``translator`` helper:

.. code-block:: html+php

    <?php echo $view['translator']->trans('Symfony2 is great') ?>

    <?php echo $view['translator']->transChoice(
        '{0} There is no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
        10,
        array('%count%' => 10)
    ) ?>

Forcing the Translator Locale
-----------------------------

When translating a message, Symfony2 uses the locale from the current request
or the ``fallback`` locale if necessary. You can also manually specify the
locale to use for translation:

.. code-block:: php

    $this->get('translator')->trans(
        'Symfony2 is great',
        array(),
        'messages',
        'fr_FR',
    );

    $this->get('translator')->trans(
        '{0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
        10,
        array('%count%' => 10),
        'messages',
        'fr_FR',
    );

Translating Database Content
----------------------------

The translation of database content should be handled by Doctrine through
the `Translatable Extension`_. For more information, see the documentation
for that library.

Summary
-------

With the Symfony2 Translation component, creating an internationalized application
no longer needs to be a painful process and boils down to just a few basic
steps:

* Abstract messages in your application by wrapping each in either the
  :method:`Symfony\\Component\\Translation\\Translator::trans` or
  :method:`Symfony\\Component\\Translation\\Translator::transChoice` methods;

* Translate each message into multiple locales by creating translation message
  files. Symfony2 discovers and processes each file because its name follows
  a specific convention;

* Manage the user's locale, which is stored on the request, but can also
  be set once the user's session.

.. _`i18n`: http://en.wikipedia.org/wiki/Internationalization_and_localization
.. _`L10n`: http://en.wikipedia.org/wiki/Internationalization_and_localization
.. _`strtr function`: http://www.php.net/manual/en/function.strtr.php
.. _`ISO 31-11`: http://en.wikipedia.org/wiki/Interval_%28mathematics%29#The_ISO_notation
.. _`Translatable Extension`: https://github.com/l3pp4rd/DoctrineExtensions
