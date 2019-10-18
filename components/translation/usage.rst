.. index::
    single: Translation; Usage

Using the Translator
====================

Imagine you want to translate the string *"Symfony is great"* into French::

    use Symfony\Component\Translation\Loader\ArrayLoader;
    use Symfony\Component\Translation\Translator;

    $translator = new Translator('fr_FR');
    $translator->addLoader('array', new ArrayLoader());
    $translator->addResource('array', [
        'Symfony is great!' => 'Symfony est super !',
    ], 'fr_FR');

    var_dump($translator->trans('Symfony is great!'));

In this example, the message *"Symfony is great!"* will be translated into
the locale set in the constructor (``fr_FR``) if the message exists in one of
the message catalogs.

Creating Translations
---------------------

The act of creating translation files is an important part of "localization"
(often abbreviated `L10n`_). Translation files consist of a series of
id-translation pairs for the given domain and locale. The source is the identifier
for the individual translation, and can be the message in the main locale (e.g.
*"Symfony is great"*) of your application or a unique identifier (e.g.
``symfony.great`` - see the sidebar below).

Translation files can be created in several formats, XLIFF being the
recommended format. These files are parsed by one of the loader classes.

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

        return [
            'Symfony is great' => 'J\'aime Symfony',
            'symfony.great'    => 'J\'aime Symfony',
        ];

.. _translation-real-vs-keyword-messages:

.. sidebar:: Using Real or Keyword Messages

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

    The second method is handy because the message key won't need to be changed
    in every translation file if you decide that the message should actually
    read "Symfony is really great" in the default locale.

    The choice of which method to use is entirely up to you, but the "keyword"
    format is often recommended for multi-language applications, whereas for
    shared bundles that contain translation resources we recommend the real
    message, so your application can choose to disable the translator layer
    and you will see a readable message.

    Additionally, the ``php`` and ``yaml`` file formats support nested ids to
    avoid repeating yourself if you use keywords instead of real text for your
    ids:

    .. configuration-block::

        .. code-block:: yaml

            symfony:
                is:
                    great: Symfony is great
                    amazing: Symfony is amazing
                has:
                    bundles: Symfony has bundles
            user:
                login: Login

        .. code-block:: php

            [
                'symfony' => [
                    'is' => [
                        'great'   => 'Symfony is great',
                        'amazing' => 'Symfony is amazing',
                    ],
                    'has' => [
                        'bundles' => 'Symfony has bundles',
                    ],
                ],
                'user' => [
                    'login' => 'Login',
                ],
            ];

    The multiple levels are flattened into single id/translation pairs by
    adding a dot (``.``) between every level, therefore the above examples are
    equivalent to the following:

    .. configuration-block::

        .. code-block:: yaml

            symfony.is.great: Symfony is great
            symfony.is.amazing: Symfony is amazing
            symfony.has.bundles: Symfony has bundles
            user.login: Login

        .. code-block:: php

            return [
                'symfony.is.great'    => 'Symfony is great',
                'symfony.is.amazing'  => 'Symfony is amazing',
                'symfony.has.bundles' => 'Symfony has bundles',
                'user.login'          => 'Login',
            ];

Forcing the Translator Locale
-----------------------------

When translating a message, the Translator uses the specified locale or the
``fallback`` locale if necessary. You can also manually specify the locale to
use for translation::

    $translator->trans(
        'Symfony is great',
        [],
        'messages',
        'fr_FR'
    );

    $translator->transChoice(
        '{0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
        10,
        [],
        'messages',
        'fr_FR'
    );

.. note::

    Starting from Symfony 3.2, the third argument of ``transChoice()`` is
    optional when the only placeholder in use is ``%count%``. In previous
    Symfony versions you needed to always define it::

        $translator->transChoice(
            '{0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
            10,
            ['%count%' => 10],
            'messages',
            'fr_FR'
        );

Retrieving the Message Catalogue
--------------------------------

In case you want to use the same translation catalogue outside your application
(e.g. use translation on the client side), it's possible to fetch raw translation
messages. Specify the required locale::

    $catalogue = $translator->getCatalogue('fr_FR');
    $messages = $catalogue->all();
    while ($catalogue = $catalogue->getFallbackCatalogue()) {
        $messages = array_replace_recursive($catalogue->all(), $messages);
    }

The ``$messages`` variable will have the following structure::

    [
        'messages' => [
            'Hello world' => 'Bonjour tout le monde',
        ],
        'validators' => [
            'Value should not be empty' => 'Valeur ne doit pas être vide',
            'Value is too long' => 'Valeur est trop long',
        ],
    ];

Adding Notes to Translation Contents
------------------------------------

Sometimes translators need additional context to better decide how to translate
some content. This context can be provided with notes, which are a collection of
comments used to store end user readable information. The only format that
supports loading and dumping notes is XLIFF version 2.0.

If the XLIFF 2.0 document contains ``<notes>`` nodes, they are automatically
loaded/dumped when using this component inside a Symfony application:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0"
        srcLang="fr-FR" trgLang="en-US">
        <file id="messages.en_US">
            <unit id="LCa0a2j" name="original-content">
                <notes>
                    <note category="state">new</note>
                    <note category="approved">true</note>
                    <note category="section" priority="1">user login</note>
                </notes>
                <segment>
                    <source>original-content</source>
                    <target>translated-content</target>
                </segment>
            </unit>
        </file>
    </xliff>

When using the standalone Translation component, call the ``setMetadata()``
method of the catalogue and pass the notes as arrays. This is for example the
code needed to generate the previous XLIFF file::

    use Symfony\Component\Translation\Dumper\XliffFileDumper;
    use Symfony\Component\Translation\MessageCatalogue;

    $catalogue = new MessageCatalogue('en_US');
    $catalogue->add([
        'original-content' => 'translated-content',
    ]);
    $catalogue->setMetadata('original-content', ['notes' => [
        ['category' => 'state', 'content' => 'new'],
        ['category' => 'approved', 'content' => 'true'],
        ['category' => 'section', 'content' => 'user login', 'priority' => '1'],
    ]]);

    $dumper = new XliffFileDumper();
    $dumper->formatCatalogue($catalogue, 'messages', [
        'default_locale' => 'fr_FR',
        'xliff_version' => '2.0'
    ]);

.. _`L10n`: https://en.wikipedia.org/wiki/Internationalization_and_localization
