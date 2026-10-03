``sort_choices``
~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

.. versionadded:: 8.2

    The ``sort_choices`` option was introduced in Symfony 8.2.

Set this option to ``true`` to sort the choices by their translated label, in
the alphabetical order of the locale. This is useful when that order depends on
the language of the user, for example with the cases of an enum, which are
otherwise listed in the order they are declared::

    use App\Enum\Sport;
    use Symfony\Component\Form\Extension\Core\Type\EnumType;
    // ...

    $builder->add('sport', EnumType::class, [
        'class' => Sport::class,
        'sort_choices' => true,
    ]);

The labels are translated with the ``choice_translation_domain`` option (they
are compared as they are when translation is disabled), and then compared with
the collation rules of the locale, which take accents, letter case and numbers
into account: ``Aviron``, ``Équitation``, ``Zumba`` and ``Level 1``,
``Level 2``, ``Level 10`` are listed in this order. The locale is the one of
the ``choice_translation_locale`` option when the field type has it (as
``CountryType`` and ``LanguageType`` do), and the locale of the translator
otherwise.

The sorting also applies to:

* the groups created with the ``group_by`` option, and to the choices inside
  each group;
* the preferred choices, which are sorted separately and still listed first;
* the radio buttons and checkboxes rendered when the ``expanded`` option is
  ``true``.

The placeholder, and a choice whose value is an empty string, always stay first.

.. note::

    This option requires the ``intl`` PHP extension. Setting it to ``true``
    without that extension throws a
    :class:`Symfony\\Component\\Form\\Exception\\LogicException`.
