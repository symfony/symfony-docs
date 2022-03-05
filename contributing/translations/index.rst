Contributing Translations
=========================

Some Symfony Components include certain messages that must be translated to
different languages. For example, if a user submits a form with a wrong value in
a :doc:`TimezoneType </reference/forms/types/timezone>` field, Symfony shows the
following error message by default: "This value is not a valid timezone."

These messages are translated into tens of languages thanks to the Symfony
community. Symfony adds new messages on a regular basis, so this is an ongoing
translation process and you can help us by providing the missing translations.

How to Contribute a Translation
-------------------------------

Imagine that you can speak both English and Swedish and want to check if there's
some missing Swedish translations to contribute them.

**Step 1.** Translations are contributed to the oldest maintained branch of the
Symfony repository. Visit the `Symfony Releases`_ page to find out which is the
current oldest maintained branch.

Then, you need to either download or browse that Symfony version contents:

* If you know Git and prefer the command console, clone the Symfony repository
  and check out the oldest maintained branch (read the
  :doc:`Symfony Documentation contribution guide </contributing/documentation/overview>`
  if you want to learn about this process);
* If you prefer to use a web based interface, visit
  `https://github.com/symfony/symfony <https://github.com/symfony/symfony>`_
  and switch to the oldest maintained branch.

**Step 2.** Check out if there's some missing translation in your language by
checking these directories:

* ``src/Symfony/Component/Form/Resources/translations/``
* ``src/Symfony/Component/Security/Core/Resources/translations/``
* ``src/Symfony/Component/Validator/Resources/translations/``

Symfony uses the :ref:`XLIFF format <best-practice-internationalization>` to
store translations. In this example, you are looking for missing Swedish
translations, so you should look for files called ``*.sv.xlf``.

.. note::

    If there's no XLIFF file for your language yet, create it yourself
    duplicating the original English file (e.g. ``validators.en.xlf``).

**Step 3.** Contribute the missing translations. To do that, compare the file
in your language to the equivalent file in English.

Imagine that you open the ``validators.sv.xlf`` and see this at the end of the file:

.. code-block:: xml

    <!-- src/Symfony/Component/Validator/Resources/translations/validators.sv.xlf -->

    <!-- ... -->
    <trans-unit id="91">
        <source>This value should be either negative or zero.</source>
        <target>Detta värde bör vara antingen negativt eller noll.</target>
    </trans-unit>
    <trans-unit id="92">
        <source>This value is not a valid timezone.</source>
        <target>Detta värde är inte en giltig tidszon.</target>
    </trans-unit>

If you open the equivalent ``validators.en.xlf`` file, you can see that the
English file has more messages to translate:

.. code-block:: xml

    <!-- src/Symfony/Component/Validator/Resources/translations/validators.en.xlf -->

    <!-- ... -->
    <trans-unit id="91">
        <source>This value should be either negative or zero.</source>
        <target>This value should be either negative or zero.</target>
    </trans-unit>
    <trans-unit id="92">
        <source>This value is not a valid timezone.</source>
        <target>This value is not a valid timezone.</target>
    </trans-unit>
    <trans-unit id="93">
        <source>This password has been leaked in a data breach, it must not be used. Please use another password.</source>
        <target>This password has been leaked in a data breach, it must not be used. Please use another password.</target>
    </trans-unit>
    <trans-unit id="94">
        <source>This value should be between {{ min }} and {{ max }}.</source>
        <target>This value should be between {{ min }} and {{ max }}.</target>
    </trans-unit>

The messages with ``id=93`` and ``id=94`` are missing in the Swedish file.
Copy and paste the messages from the English file, translate the content
inside the ``<target>`` tag and save the changes.

**Step 4.** Make the pull request against the
`https://github.com/symfony/symfony <https://github.com/symfony/symfony>`_ repository.
If you need help, check the other Symfony guides about
:doc:`contributing code or docs </contributing/index>` because the process is
the same.

.. _`Symfony Releases`: https://symfony.com/releases
