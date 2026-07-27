The XLIFF format
================

Most professional translation tools support XLIFF_. These files use the XML
format and are supported by Symfony by default. Besides supporting
:doc:`all Symfony translation features </translation>`, the XLIFF format also
has some specific features.

Keeping the Original Source Text
--------------------------------

.. versionadded:: 8.2

    Preserving the ``<source>`` element when loading and dumping XLIFF files was
    introduced in Symfony 8.2. Previously, the dumpers overwrote it with the
    translation key.

When using :ref:`keyword messages <translation-real-vs-keyword-messages>`, the
translation key is stored in the ``resname`` attribute (XLIFF 1.2) or the ``name``
attribute (XLIFF 2.0), so the ``<source>`` element can hold the text in the
original language:

.. code-block:: xml

    <trans-unit id="7cbcc07" resname="navbar.home">
        <source>Home</source>
        <target>Accueil</target>
    </trans-unit>

Symfony stores that text in the message metadata when loading the file and writes it
back when dumping it (e.g. when running the ``translation:extract --force`` or
``translation:pull`` commands). Symfony doesn't add this text to the files it
generates itself, so this only affects files written by hand or by external
translation tools.

.. note::

    In XLIFF 2.0, keys longer than 80 characters can't be stored in the ``name``
    attribute, so the ``<source>`` element keeps the key and the original text
    isn't preserved.

Adding Notes to Translation Contents
------------------------------------

Sometimes translators need additional context to better decide how to translate
some content. This context can be provided with notes, which are a collection of
comments used to store end user readable information. The only format that
supports loading and dumping notes is XLIFF version 2.

.. versionadded:: 8.1

    Support for XLIFF versions 2.1 and 2.2 was introduced in Symfony 8.1.
    Previously, only XLIFF 1.2 and 2.0 were supported.

If the XLIFF 2 document contains ``<notes>`` nodes, they are automatically
loaded/dumped inside a Symfony application:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0"
        srcLang="fr-FR" trgLang="en-US">
        <file id="messages.en_US">
            <unit id="LCa0a2j" name="original-content">
                <notes>
                    <note category="state">new</note>
                    <note category="approved">true</note>
                    <note category="section" priority="1">user login</note>
                </notes>
                <segment state="translated" subState="Some custom value">
                    <source>original-content</source>
                    <target>translated-content</target>
                </segment>
            </unit>
        </file>
    </xliff>

Plural, Gender and Select (PGS)
-------------------------------

.. versionadded:: 8.1

    Support for the XLIFF PGS module was introduced in Symfony 8.1.

XLIFF 2.2 introduces the `PGS module`_ (Plural, Gender and Select) to handle
plural, gender and select variations directly in the translation file. When the
XLIFF loader encounters PGS attributes, it automatically converts them into
ICU MessageFormat strings and registers them in the ``+intl-icu`` domain.

.. seealso::

    Read more about :doc:`using ICU message format in Symfony </reference/formats/message_format>`.

Here is an example of a plural translation using PGS:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <xliff xmlns="urn:oasis:names:tc:xliff:document:2.0"
        xmlns:pgs="urn:oasis:names:tc:xliff:pgs:1.0"
        version="2.2" srcLang="en" trgLang="fr">
        <file id="f1">
            <unit id="tu1" name="file_deleted" pgs:switch="plural:file_count">
                <segment id="seg1" pgs:case="0">
                    <source>You deleted no file.</source>
                    <target>Vous n'avez supprimé aucun fichier.</target>
                </segment>
                <segment id="seg2" pgs:case="1">
                    <source>You deleted one file.</source>
                    <target>Vous avez supprimé un fichier.</target>
                </segment>
                <segment id="seg3" pgs:case="other">
                    <source>You deleted <ph id="1" disp="file_count"/> files.</source>
                    <target>Vous avez supprimé <ph id="1" disp="file_count"/> fichiers.</target>
                </segment>
            </unit>
        </file>
    </xliff>

The ``pgs:switch`` attribute on the ``<unit>`` element declares the variable
name and the switch type (``plural``, ``gender`` or ``select``). Each
``<segment>`` uses ``pgs:case`` to define the matching value. The ``<ph>``
(placeholder) elements with a ``disp`` attribute are converted to ICU
placeholders.

Gender variations work the same way:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <xliff xmlns="urn:oasis:names:tc:xliff:document:2.0"
        xmlns:pgs="urn:oasis:names:tc:xliff:pgs:1.0"
        version="2.2" srcLang="en" trgLang="fr">
        <file id="f1">
            <unit id="tu1" name="party_invite" pgs:switch="gender:host_gender">
                <segment id="seg1" pgs:case="feminine">
                    <source>You are invited to her party</source>
                    <target>Vous êtes invité à sa fête</target>
                </segment>
                <segment id="seg2" pgs:case="masculine">
                    <source>You are invited to his party</source>
                    <target>Vous êtes invité à sa fête</target>
                </segment>
                <segment id="seg3" pgs:case="other">
                    <source>You are invited to their party</source>
                    <target>Vous êtes invité à leur fête</target>
                </segment>
            </unit>
        </file>
    </xliff>

.. tip::

    Multiple switches can be combined in a single ``pgs:switch`` attribute
    (e.g. ``pgs:switch="gender:host_gender plural:guest_count"``). Symfony
    nests the ICU structures automatically.

.. _XLIFF: https://docs.oasis-open.org/xliff/
.. _PGS module: https://docs.oasis-open.org/xliff/xliff-core/v2.2/xliff-core-v2.2.html
