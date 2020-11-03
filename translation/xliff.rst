The XLIFF format
================

Most professional translation tools support XLIFF_. These files use the XML
format and are supported by Symfony by default. Besides supporting
:doc:`all Symfony translation features </translation>`, the XLIFF format also
has some specific features.

Adding Notes to Translation Contents
------------------------------------

Sometimes translators need additional context to better decide how to translate
some content. This context can be provided with notes, which are a collection of
comments used to store end user readable information. The only format that
supports loading and dumping notes is XLIFF version 2.

If the XLIFF 2.0 document contains ``<notes>`` nodes, they are automatically
loaded/dumped inside a Symfony application:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <xliff xmlns="urn:oasis:names:tc:xliff:document:2.1" version="2.1"
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

.. _XLIFF: http://docs.oasis-open.org/xliff/xliff-core/v2.1/xliff-core-v2.1.html
