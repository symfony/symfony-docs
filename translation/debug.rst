.. index::
    single: Translation; Debug
    single: Translation; Missing Messages
    single: Translation; Unused Messages

How to Find Missing or Unused Translation Messages
==================================================

When maintaining an application or bundle, you may add or remove translation
messages and forget to update the message catalogs. The ``debug:translation``
command helps you to find these missing or unused translation messages templates:

.. code-block:: twig

    {# messages can be found when using the trans filter and tag #}
    {% trans %}Symfony is great{% endtrans %}

    {{ 'Symfony is great'|trans }}

.. caution::

    The extractors can't find messages translated outside templates (like form
    labels or controllers) unless using :ref:`translatable-objects` or calling
    the ``trans()`` method on a translator. Dynamic translations using variables
    or expressions in templates are not detected either:

    .. code-block:: twig

        {# this translation uses a Twig variable, so it won't be detected #}
        {% set message = 'Symfony is great' %}
        {{ message|trans }}

Suppose your application's default_locale is ``fr`` and you have configured
``en`` as the fallback locale (see :ref:`translation-configuration` and
:ref:`translation-fallback` for how to configure these). And suppose
you've already setup some translations for the ``fr`` locale:

.. configuration-block::

    .. code-block:: xml

        <!-- translations/messages.fr.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Symfony is great</source>
                        <target>J'aime Symfony</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: yaml

        # translations/messages.fr.yaml
        Symfony is great: J'aime Symfony

    .. code-block:: php

        // translations/messages.fr.php
        return [
            'Symfony is great' => 'J\'aime Symfony',
        ];

and for the ``en`` locale:

.. configuration-block::

    .. code-block:: xml

        <!-- translations/messages.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Symfony is great</source>
                        <target>Symfony is great</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: yaml

        # translations/messages.en.yaml
        Symfony is great: Symfony is great

    .. code-block:: php

        // translations/messages.en.php
        return [
            'Symfony is great' => 'Symfony is great',
        ];

To inspect all messages in the ``fr`` locale for the application, run:

.. code-block:: terminal

    $ php bin/console debug:translation fr

    ---------  ------------------  ----------------------  -------------------------------
     State      Id                  Message Preview (fr)    Fallback Message Preview (en)
    ---------  ------------------  ----------------------  -------------------------------
     unused     Symfony is great    J'aime Symfony          Symfony is great
    ---------  ------------------  ----------------------  -------------------------------

It shows you a table with the result when translating the message in the ``fr``
locale and the result when the fallback locale ``en`` would be used. On top
of that, it will also show you when the translation is the same as the fallback
translation (this could indicate that the message was not correctly translated).
Furthermore, it indicates that the message ``Symfony is great`` is unused
because it is translated, but you haven't used it anywhere yet.

Now, if you translate the message in one of your templates, you will get this
output:

.. code-block:: terminal

    $ php bin/console debug:translation fr

    ---------  ------------------  ----------------------  -------------------------------
     State      Id                  Message Preview (fr)    Fallback Message Preview (en)
    ---------  ------------------  ----------------------  -------------------------------
                Symfony is great    J'aime Symfony          Symfony is great
    ---------  ------------------  ----------------------  -------------------------------

The state is empty which means the message is translated in the ``fr`` locale
and used in one or more templates.

If you delete the message ``Symfony is great`` from your translation file
for the ``fr`` locale and run the command, you will get:

.. code-block:: terminal

    $ php bin/console debug:translation fr

    ---------  ------------------  ----------------------  -------------------------------
     State      Id                  Message Preview (fr)    Fallback Message Preview (en)
    ---------  ------------------  ----------------------  -------------------------------
     missing    Symfony is great    Symfony is great        Symfony is great
    ---------  ------------------  ----------------------  -------------------------------

The state indicates the message is missing because it is not translated in
the ``fr`` locale but it is still used in the template. Moreover, the message
in the ``fr`` locale equals to the message in the ``en`` locale. This is a
special case because the untranslated message id equals its translation in
the ``en`` locale.

If you copy the content of the translation file in the ``en`` locale to the
translation file in the ``fr`` locale and run the command, you will get:

.. code-block:: terminal

    $ php bin/console debug:translation fr

    ----------  ------------------  ----------------------  -------------------------------
     State       Id                  Message Preview (fr)    Fallback Message Preview (en)
    ----------  ------------------  ----------------------  -------------------------------
     fallback    Symfony is great    Symfony is great        Symfony is great
    ----------  ------------------  ----------------------  -------------------------------

You can see that the translations of the message are identical in the ``fr``
and ``en`` locales which means this message was probably copied from English
to French and maybe you forgot to translate it.

By default, all domains are inspected, but it is possible to specify a single
domain:

.. code-block:: terminal

    $ php bin/console debug:translation en --domain=messages

When the application has a lot of messages, it is useful to display only the
unused or only the missing messages, by using the ``--only-unused`` or
``--only-missing`` options:

.. code-block:: terminal

    $ php bin/console debug:translation en --only-unused
    $ php bin/console debug:translation en --only-missing

Debug Command Exit Codes
------------------------

The exit code of the ``debug:translation`` command changes depending on the
status of the translations. Use the following public constants to check it::

    use Symfony\Bundle\FrameworkBundle\Command\TranslationDebugCommand;

    // generic failure (e.g. there are no translations)
    TranslationDebugCommand::EXIT_CODE_GENERAL_ERROR;

    // there are missing translations
    TranslationDebugCommand::EXIT_CODE_MISSING;

    // there are unused translations
    TranslationDebugCommand::EXIT_CODE_UNUSED;

    // some translations are using the fallback translation
    TranslationDebugCommand::EXIT_CODE_FALLBACK;

These constants are defined as "bit masks", so you can combine them as follows::

    if (TranslationDebugCommand::EXIT_CODE_MISSING | TranslationDebugCommand::EXIT_CODE_UNUSED) {
        // ... there are missing and/or unused translations
    }
