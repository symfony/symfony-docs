Contributing Translations
=========================

Some Symfony components ship messages that are shown to end users, such as
validation errors (*"This value is not a valid timezone."*). These messages are
translated into dozens of languages by the community, and new messages are added
regularly, so there are always missing translations you can help with.

.. tip::

    **Quick reference**

    Translations live in the ``symfony/symfony`` repository as XLIFF files
    (``*.<locale>.xlf``). Find the messages missing in your language, translate
    the ``<target>`` tags, and open a pull request against the **oldest
    maintained branch**.

How to Contribute a Translation
-------------------------------

Imagine you speak English and Swedish and want to add the missing Swedish
translations.

**Step 1.** Translations are always contributed to the **oldest maintained
branch** (check the `Symfony Releases`_ page). Browse or check out that branch of
the `symfony/symfony`_ repository.

**Step 2.** Translation files use the :ref:`XLIFF format <best-practice-internationalization>`
and live in these directories:

* ``src/Symfony/Component/Form/Resources/translations/``
* ``src/Symfony/Component/Security/Core/Resources/translations/``
* ``src/Symfony/Component/Validator/Resources/translations/``

For Swedish, look at the ``*.sv.xlf`` files. If the file for your language
doesn't exist yet, create it by copying the English one (e.g. ``validators.en.xlf``).

.. tip::

    The `Symfony Translations Status`_ site shows, per language, which
    translations are still missing.

**Step 3.** Compare your language file with the English one and add the missing
messages. Each entry keeps the English ``<source>`` and translates the
``<target>``:

.. code-block:: xml

    <trans-unit id="94">
        <source>This value should be between {{ min }} and {{ max }}.</source>
        <target>Detta värde bör vara mellan {{ min }} och {{ max }}.</target>
    </trans-unit>

**Step 4.** Open a pull request against the `symfony/symfony`_ repository. The
process is the same as for :doc:`code or docs </contributing/index>`.

.. _`Symfony Releases`: https://symfony.com/releases
.. _`symfony/symfony`: https://github.com/symfony/symfony
.. _`Symfony Translations Status`: https://symfony-translations.nyholm.tech/
