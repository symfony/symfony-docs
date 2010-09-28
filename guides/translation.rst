Translations
============

The :namespace:`Symfony\\Component\\Translation` Component provides a way to
internationalize all the messages in your application.

A *message* can be any string you want to internationalize. Messages are
categorized by locale and domain.

A *domain* allows you to better organized your messages in a given locale (it
can be any string; by default, all messages are stored under the ``messages``
domain).

A *locale* can be any string, but we recommended to use the ISO639-1 language
code, followed by a underscore (``_``), followed by the ISO3166 country code
(use ``fr_FR`` for French/France for instance).

Configuration
-------------

Before using the translator features, enable it in your configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        app.config:
            translator: { fallback: en }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <app:config>
            <app:translator fallback="en" />
        </app:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('app', 'config', array(
            'translator' => array('fallback' => 'en'),
        ));

The ``fallback`` attribute defines the fallback locale when a translation does
not exist in the user locale.

.. tip::
    When a translation does not exist for a locale, the translator tries to
    find the translation for the language (``fr`` when the locale is ``fr_FR``
    for instance); if it also fails, it looks for a translation for the
    fallback locale.

The locale used in translations is the one stored in the user session.

Translations
------------

Translations are available through the ``translator`` service
(:class:`Symfony\\Component\\Translation\\Translator`). Use the
:method:`Symfony\\Component\\Translation\\Translator::trans` method to
translate a message::

    $t = $this['translator']->trans('Symfony2 is great!');

If you have placeholders in strings, pass their values as the second
argument::

    $t = $this['translator']->trans('Symfony2 is {{ what }}!', array('{{ what }}' => 'great'));

.. note::
    The placeholders can have any form, but using the ``{{ var }}`` notation
    allows the message to be used in Twig templates.

By default, the translator looks for messages in the default ``messages``
domain. Override it via the third argument::

    $t = $this['translator']->trans('Symfony2 is great!', array(), 'applications');

Catalogues
----------

Translations are stored on the filesystem and discovered by Symfony2, thanks
to some conventions.

Store translations for messages found in a bundle under the
``Resources/translations/`` directory; and override them under the
``app/translations/`` directory.

Each message file must be named according to the following pattern:
``domain.locale.loader`` (the domain name, followed by a dot (``.``), followed
by the locale name, followed by a dot (``.``), followed by the loader name.)

The loader can be the name of any registered loader. By default, Symfony2
provides the following loaders:

* ``php``:   PHP file;
* ``xliff``: XLIFF file;
* ``yaml``:  YAML file.

Each file consists of pairs of id/translation strings for the given domain and
locale. The id can be the message in the main locale of your application of a
unique identifier:

.. configuration-block::

    .. code-block:: xml

        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Symfony2 is great</source>
                        <target>J'aime Symfony2</target>
                    </trans-unit>
                    <trans-unit id="2">
                        <source>symfony.great</source>
                        <target>J'aime Symfony2</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        return array(
            'Symfony2 is great' => 'J\'aime Symfony2',
            'symfony.great'     => 'J\'aime Symfony2',
        );

.. note::
    You can also store translations in a database, or any other storage by
    providing a custom
    :class:`Symfony\\Component\\Translation\\Loader\\LoaderInterface` class.
    See below to learn how to register custom loaders.

Pluralization
-------------

Message pluralization is a tough topic as the rules can be quite complex. For
instance, here is the mathematic representation of the Russian pluralization
rules::

    (($number % 10 == 1) && ($number % 100 != 11)) ? 0 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 1 : 2);

As you can see, in Russian, you can have three different plural forms, based
on this algorithm. For each form, the plural is different, and so the
translation is also different. In such a case, you can provide all
pluralization forms as strings separated by pipes (``|``)::

    'There is one apple|There are {{ count }} apples'

Based on a given number, the translator chooses the right plural form. If
``count`` is ``1``, the translator will use the first string (``There is one
apple``) as the translation, if not, it will use ``There are {{ count }}
apples``.

Here is the French translation::

    'Il y a {{ count }} pomme|Il y a {{ count }} pommes'

Even if the string looks similar (it is made of two sub-strings separated by a
pipe), the French rules are different: the first form (no plural) is used when
``count`` is ``0`` or ``1``. So, the translator will automatically use the
first string (``Il y a {{ count }} pomme``) when ``count`` is ``0`` or ``1``.

The rules are quite simple for English and French, but for Russian, you'd
better have a hint to know which rule matches which string. To help
translators, you can optionally "tag" each string like this::

    'one: There is one apple|some: There are {{ count }} apples'

    'none_or_one: Il y a {{ count }} pomme|some: Il y a {{ count }} pommes'

The tags are really only hints for translators to help them understand the
context of the translation (note that the tags do not need to be the same in
the original message and in the translated one).

.. tip:
    As tags are optional, the translator doesn't use them (the translator will
    only get a string based on its position in the string).

Sometimes, you want a different translation for specific cases (for ``0``, or
when the count is large enough, when the count is negative, ...). For such
cases, you can use explicit math intervals::

    '{0} There is no apples|{1} There is one apple|]1,19] There are {{ count }} apples|[20,Inf] There are many apples'

You can also mix explicit math rules and standard rules. The position for
standard rules is defined after removing the explicit rules::

    '{0} There is no apples|[20,Inf] There are many apples|There is one apple|a_few: There are {{ count }} apples'

An :class:`Symfony\\Component\\Translator\\Interval` can represent a finite set
of numbers::

    {1,2,3,4}

Or numbers between two other numbers::

    [1, +Inf]
    ]-1,2[

The left delimiter can be ``[`` (inclusive) or ``]`` (exclusive). The right
delimiter can be ``[`` (exclusive) or ``]`` (inclusive). Beside numbers, you
can use ``-Inf`` and ``+Inf`` for the infinite.

.. note::
    Symfony uses the `ISO 31-11`_ for intervals notation.

The translator
:method:`Symfony\\Component\\Translation\\Translator::transChoice` method
knows how to deal with plural::

    $t = $this['translator']->transChoice(
        '{0} There is no apples|{1} There is one apple|]1,Inf] There are {{ count }} apples',
        10,
        array('{{ count }}' => 10)
    );

Notice that the second argument is the number to use to determine which plural
string to use.

Translations in Templates
-------------------------

Most of the time, translation occurs in templates. Symfony2 provides native
support for both PHP and Twig templates.

PHP Templates
~~~~~~~~~~~~~

The translator service is accessible in PHP templates through the
``translator`` helper:

.. code-block:: html+php

    <?php echo $view['translator']->trans('Symfony2 is great!') ?>

    <?php echo $view['translator']->transChoice(
        '{0} There is no apples|{1} There is one apple|]1,Inf] There are {{ count }} apples',
        10,
        array('{{ count }}' => 10)
    ) ?>

Twig Templates
~~~~~~~~~~~~~~

Symfony2 provides specialized Twig tags (``trans`` and ``transChoice``) to
help with message translation:

.. code-block:: jinja

    {% trans "Symfony2 is great!" %}

    {% trans %}
        Foo {{ name }}
    {% endtrans %}

    {% transchoice count %}
        {0} There is no apples|{1} There is one apple|]1,Inf] There are {{ count }} apples
    {% endtranschoice %}

The ``transChoice`` tag automatically get the variables from the current
context and pass them to the translator. This mechanism only works when you
use placeholder using the ``{{ var }}`` pattern.

You can also specify the message domain:

.. code-block:: jinja

    {% trans "Foo {{ name }}" from app %}

    {% trans from app %}
        Foo {{ name }}
    {% endtrans %}

    {% transchoice count from app %}
        {0} There is no apples|{1} There is one apple|]1,Inf] There are {{ count }} apples
    {% endtranschoice %}

.. _translation_loader_tag:

Enabling Custom Loaders
-----------------------

To enable a custom loader, add it as a regular service in one of your
configuration, tag it with ``translation.loader`` and define an ``alias``
attribute (for filesystem based loaders, the alias is the file extension you
must use to reference the loader):

.. configuration-block::

    .. code-block:: yaml

        services:
            translation.loader.your_helper_name:
                class: Fully\Qualified\Loader\Class\Name
                tags:
                    - { name: translation.loader, alias: alias_name }

    .. code-block:: xml

        <service id="translation.loader.your_helper_name" class="Fully\Qualified\Loader\Class\Name">
            <tag name="translation.loader" alias="alias_name" />
        </service>

    .. code-block:: php

        $container
            ->register('translation.loader.your_helper_name', 'Fully\Qualified\Loader\Class\Name')
            ->addTag('translation.loader', array('alias' => 'alias_name'))
        ;

.. _ISO 31-11: http://en.wikipedia.org/wiki/Interval_%28mathematics%29#The_ISO_notation
