The XLIFF format
================

Most professional translation tools support XLIFF_. These files use the XML
format and are supported by Symfony by default. Besides supporting
:doc:`all Symfony translation features </translation>`, the XLIFF format also
has some specific features.

Keeping the Original Source Text
--------------------------------

.. versionadded:: 8.2

    Preserving the ``<source>`` element when loading and dumping XLIFF files
    was introduced in Symfony 8.2. Previously, the dumpers replaced its
    contents with the translation key.

When using :ref:`keyword messages <translation-real-vs-keyword-messages>`, the
translation key is commonly stored in the ``resname`` attribute (XLIFF 1.2) or
the ``name`` attribute (XLIFF 2.0), while the ``<source>`` element contains the
actual contents in the original language:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">
        <file source-language="en" target-language="fr" datatype="plaintext" original="file.ext">
            <body>
                <trans-unit id="navbar.home" resname="navbar.home">
                    <source>Home</source>
                    <target>Accueil</target>
                </trans-unit>
            </body>
        </file>
    </xliff>

When loading such a file, Symfony stores the source text in the catalogue
metadata under the ``source`` key. When dumping the catalogue back to XLIFF
(e.g. when running the ``translation:extract --force`` or ``translation:pull``
commands), the ``<source>`` element keeps that text instead of being
overwritten with the translation key.

.. note::

    XLIFF 2.0 files can't store keys longer than 80 characters in the ``name``
    attribute. In that case, the dumped ``<source>`` element contains the
    translation key itself (which is used as the fallback key when loading the
    file) and the original source text is not preserved.

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
