.. index::
    single: Translation
    single: Components; Translation

The Translation Component
=========================

    The Translation component provides tools to internationalize your
    application.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Translation);
* :doc:`Install it via Composer</components/using_components>` (``symfony/translation`` on `Packagist`_).

Usage
-----

The :class:`Symfony\\Component\\Translation\\Translator` class is the main
entry point of the Translation component.

.. code-block:: php

    use Symfony\Component\Translation\Translator;
    use Symfony\Component\Translation\MessageSelector;
    use Symfony\Component\Translation\Loader\ArrayLoader;

    $translator = new Translator('fr_FR', new MessageSelector());
    $translator->addLoader('array', new ArrayLoader());
    $translator->addResource('array', array(
        'Hello World!' => 'Bonjour',
    ), 'fr_FR');
    
    echo $translator->trans('Hello World!');

Constructing the Translator
---------------------------

Before you can use the Translator, you need to configure it and load the
message catalogues.

Configuration
~~~~~~~~~~~~~

The constructor of the ``Translator`` class needs to arguments: The locale and
a :class:`Symfony\\Component\\Translation\\MessageSelector` to use when using
pluralization (more about that later)::

    use Symfony\Component\Translation\Translator;
    use Symfony\Component\Translation\MessageSelector;
    $translator = new Translator('fr_FR', new MessageSelector());

.. note::

    The locale set here is the default locale to use. You can override this
    locale when translating strings.

Loading Message Catalogues
~~~~~~~~~~~~~~~~~~~~~~~~~~

The messages are stored in message catalogues inside the ``Translator``
class. A message catalogue is like a dictionary of translations for a specific 
locale.

The Translation component uses Loader classes to load catalogues. You can load
multiple resources for the same locale, it will be combined into one
catalogue.

The component comes with some default Loaders and you can create your own
Loader too. The default loaders are:

* :class:`Symfony\\Component\\Translation\\Loader\\ArrayLoader` - to load
  catalogues from PHP arrays.
* :class:`Symfony\\Component\\Translation\\Loader\\CsvFileLoader` - to load
  catalogues from Csv files.
* :class:`Symfony\\Component\\Translation\\Loader\\PhpFileLoader` - to load
  catalogues from Php files.
* :class:`Symfony\\Component\\Translation\\Loader\\XliffFileLoader` - to load
  catalogues from Xliff files.
* :class:`Symfony\\Component\\Translation\\Loader\\YamlFileLoader` - to load
  catalogues from Yaml files (requires the :doc:`Yaml component</components/yaml>`).

All loaders, except the ``ArrayLoader``, requires the
:doc:`Config component</components/config/index>`.

At first, you should add a loader to the ``Translator``::

    // ...
    $translator->addLoader('array', new ArrayLoader());

The first argument is the key to which we can refer the loader in the translator
and the second argument is an instance of the loader itself. After this, you
can add your resources using the correct loader.

Loading Messages with the ``ArrayLoader``
.........................................

Loading messages can be done by calling
:method:`Symfony\\Component\\Translation\\Translator::addResource`. The first
argument is the loader name (the first argument of the ``addLoader``
method), the second is the resource and the third argument is the locale::

    // ...
    $translator->addResource('array', array(
        'Hello World!' => 'Bonjour',
    ), 'fr_FR');

Loading Messages with the File Loaders
......................................

If you use one of the file loaders, you also use the ``addResource`` method.
The only difference is that you put the file name as the second argument,
instead of an array::

    // ...
    $translator->addLoader('yaml', new YamlFileLoader());
    $translator->addResource('yaml', 'path/to/messages.fr.yml', 'fr_FR');

.. _basic-translation:

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
        $t = $this->get('translator')->trans('Symfony2 is great');

        return new Response($t);
    }

When this code is executed, Symfony2 will attempt to translate the message
"Symfony2 is great" based on the ``locale`` of the user. For this to work,
you need to tell Symfony2 how to translate the message via a "translation
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

* The ``locale`` of the current user, which is stored in the session, is determined;

* A catalog of translated messages is loaded from translation resources defined
  for the ``locale`` (e.g. ``fr_FR``). Messages from the fallback locale are
  also loaded and added to the catalog if they don't already exist. The end
  result is a large "dictionary" of translations. See `Message Catalogues`_
  for more details;

* If the message is located in the catalog, the translation is returned. If
  not, the translator returns the original message.

When using the ``trans()`` method, Symfony2 looks for the exact string inside
the appropriate message catalog and returns it (if it exists).

Message Placeholders
~~~~~~~~~~~~~~~~~~~~

Sometimes, a message containing a variable needs to be translated::

    // ...
    use Symfony\Component\HttpFoundation\Response;

    public function indexAction($name)
    {
        $t = $this->get('translator')->trans('Hello '.$name);

        return new Response($t);
    }

However, creating a translation for this string is impossible since the translator
will try to look up the exact message, including the variable portions
(e.g. "Hello Ryan" or "Hello Fabien"). Instead of writing a translation
for every possible iteration of the ``$name`` variable, you can replace the
variable with a "placeholder"::

    // ...
    use Symfony\Component\HttpFoundation\Response;

    public function indexAction($name)
    {
        $t = $this->get('translator')->trans(
            'Hello %name%',
            array('%name%' => $name)
        );

        return new Response($t);
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
        'Hello %name%': Bonjour %name%

.. note::

    The placeholders can take on any form as the full message is reconstructed
    using the PHP :phpfunction:`strtr function<strtr>`. However, the ``%var%`` notation is
    required when translating in Twig templates, and is overall a sensible
    convention to follow.

As you've seen, creating a translation is a two-step process:

#. Abstract the message that needs to be translated by processing it through
   the ``Translator``.

#. Create a translation for the message in each locale that you choose to
   support.

The second step is done by creating message catalogues that define the translations
for any number of different locales.

Creating Translations
~~~~~~~~~~~~~~~~~~~~~

The act of creating translation files is an important part of "localization"
(often abbreviated `L10n`_). Translation files consist of a series of
id-translation pairs for the given domain and locale. The source is the identifier
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
    messages to be translated::

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
    in every translation file if you decide that the message should actually
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
                        'great'   => 'Symfony2 is great',
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

As you've seen, message files are organized into the different locales that
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
you must specify the domain as the third argument of ``trans()``::

    $this->get('translator')->trans('Symfony2 is great', array(), 'admin');

Symfony2 will now look for the message in the ``admin`` domain of the user's
locale.

Fallback and Default Locale
~~~~~~~~~~~~~~~~~~~~~~~~~~~

If the locale hasn't been set explicitly in the session, the ``fallback_locale``
configuration parameter will be used by the ``Translator``. The parameter
defaults to ``en`` (see `Configuration`_).

Alternatively, you can guarantee that a locale is set on the user's session
by defining a ``default_locale`` for the session service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session: { default_locale: en }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <framework:session default-locale="en" />
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'session' => array('default_locale' => 'en'),
        ));

Pluralization
-------------

Message pluralization is a tough topic as the rules can be quite complex. For
instance, here is the mathematic representation of the Russian pluralization
rules::

    (($number % 10 == 1) && ($number % 100 != 11))
        ? 0
        : ((($number % 10 >= 2)
            && ($number % 10 <= 4)
            && (($number % 100 < 10)
            || ($number % 100 >= 20)))
                ? 1
                : 2
    );

As you can see, in Russian, you can have three different plural forms, each
given an index of 0, 1 or 2. For each form, the plural is different, and
so the translation is also different.

When a translation has different forms due to pluralization, you can provide
all the forms as a string separated by a pipe (``|``)::

    'There is one apple|There are %count% apples'

To translate pluralized messages, use the
:method:`Symfony\\Component\\Translation\\Translator::transChoice` method::

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

.. tip::

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

Forcing the Translator Locale
-----------------------------

When translating a message, Symfony2 uses the locale from the user's session
or the ``fallback`` locale if necessary. You can also manually specify the
locale to use for translation::

    $this->get('translator')->trans(
        'Symfony2 is great',
        array(),
        'messages',
        'fr_FR'
    );

    $this->get('translator')->transChoice(
        '{0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
        10,
        array('%count%' => 10),
        'messages',
        'fr_FR'
    );

Translating Database Content
----------------------------

The translation of database content should be handled by Doctrine through
the `Translatable Extension`_. For more information, see the documentation
for that library.

.. _Packagist: https://packagist.org/packages/symfony/translation
.. _`L10n`: http://en.wikipedia.org/wiki/Internationalization_and_localization
.. _`ISO 31-11`: http://en.wikipedia.org/wiki/Interval_(mathematics)#Notations_for_intervals
.. _`Translatable Extension`: https://github.com/l3pp4rd/DoctrineExtensions
