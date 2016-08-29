.. index::
    single: Translation; Debug
    single: Translation; Missing Messages
    single: Translation; Unused Messages

How to Find Missing or Unused Translation Messages
==================================================

When maintaining an application or bundle, you may add or remove translation
messages and forget to update the message catalogues. The ``debug:translation``
command helps you to find these missing or unused translation messages.

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

    $view['translator']->transChoice('Symfony2 is great', 1);

.. caution::

    The extractors are not able to inspect the messages translated outside
    templates which means that translator usages in form labels or inside
    your controllers won't be detected. Dynamic translations involving variables
    or expressions are not detected in templates, which means this example
    won't be analyzed:

    .. code-block:: jinja

        {% set message = 'Symfony2 is great' %}
        {{ message|trans }}

Suppose your application's default_locale is ``fr`` and you have configured
``en`` as the fallback locale (see :ref:`translation-configuration` and
:ref:`translation-fallback` for how to configure these). And suppose
you've already setup some translations for the ``fr`` locale inside an AcmeDemoBundle:

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


    .. code-block:: yaml

        # src/Acme/AcmeDemoBundle/Resources/translations/messages.fr.yml
        Symfony2 is great: J'aime Symfony2

    .. code-block:: php

        // src/Acme/AcmeDemoBundle/Resources/translations/messages.fr.php
        return array(
            'Symfony2 is great' => 'J\'aime Symfony2',
        );

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

    .. code-block:: yaml

        # src/Acme/AcmeDemoBundle/Resources/translations/messages.en.yml
        Symfony2 is great: Symfony2 is great

    .. code-block:: php

        // src/Acme/AcmeDemoBundle/Resources/translations/messages.en.php
        return array(
            'Symfony2 is great' => 'Symfony2 is great',
        );

To inspect all messages in the ``fr`` locale for the AcmeDemoBundle, run:

.. code-block:: terminal

    $ php app/console debug:translation fr AcmeDemoBundle

You will get this output:

.. image:: /_images/translation/debug_1.png
    :align: center

.. versionadded:: 2.6
    Prior to Symfony 2.6, this command was called ``translation:debug``.

It shows you a table with the result when translating the message in the ``fr``
locale and the result when the fallback locale ``en`` would be used. On top
of that, it will also show you when the translation is the same as the fallback
translation (this could indicate that the message was not correctly translated).
Furthermore, it indicates that the message ``Symfony2 is great`` is unused
because it is translated, but you haven't used it anywhere yet.

Now, if you translate the message in one of your templates, you will get this
output:

.. image:: /_images/translation/debug_2.png
    :align: center

The state is empty which means the message is translated in the ``fr`` locale
and used in one or more templates.

If you delete the message ``Symfony2 is great`` from your translation file
for the ``fr`` locale and run the command, you will get:

.. image:: /_images/translation/debug_3.png
    :align: center

The state indicates the message is missing because it is not translated in
the ``fr`` locale but it is still used in the template. Moreover, the message
in the ``fr`` locale equals to the message in the ``en`` locale. This is a
special case because the untranslated message id equals its translation in
the ``en`` locale.

If you copy the content of the translation file in the ``en`` locale, to the
translation file in the ``fr`` locale and run the command, you will get:

.. image:: /_images/translation/debug_4.png
    :align: center

You can see that the translations of the message are identical in the ``fr``
and ``en`` locales which means this message was probably copied from French
to English and maybe you forgot to translate it.

By default all domains are inspected, but it is possible to specify a single
domain:

.. code-block:: terminal

    $ php app/console debug:translation en AcmeDemoBundle --domain=messages

When bundles have a lot of messages, it is useful to display only the unused
or only the missing messages, by using the ``--only-unused`` or ``--only-missing``
switches:

.. code-block:: terminal

    $ php app/console debug:translation en AcmeDemoBundle --only-unused
    $ php app/console debug:translation en AcmeDemoBundle --only-missing
