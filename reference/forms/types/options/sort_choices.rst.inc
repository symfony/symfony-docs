``sort_choices``
~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

.. versionadded:: 8.2

    The ``sort_choices`` option was introduced in Symfony 8.2.

Set this option to ``true`` to sort the choices by their label, as displayed to
the user. This is useful when labels are translated, because their alphabetical
order depends on the user's language::

    use App\Enum\Sport;
    use Symfony\Component\Form\Extension\Core\Type\EnumType;
    // ...

    $builder->add('sport', EnumType::class, [
        'class' => Sport::class,
        // without this option, cases are listed in the order they are declared;
        // with it, French users see: Aviron, Équitation, Zumba
        'sort_choices' => true,
    ]);

Labels are sorted after translating them, using the collation rules of the
locale: accented and uppercase letters are not pushed to the end of the list,
and numbers are compared by their value (``Level 2`` goes before ``Level 10``).
The locale is the one set in the ``choice_translation_locale`` option, if any,
or the locale of the translator otherwise.

The groups created with the ``group_by`` option are sorted too, as well as the
choices inside each group. Preferred choices are sorted separately and are still
displayed first. The placeholder and any choice whose value is an empty string
always stay at the top. When the ``expanded`` option is ``true``, the radio
buttons or checkboxes are rendered in the sorted order.

This option requires the ``intl`` PHP extension.
