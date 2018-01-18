format
~~~~~~

**type**: ``integer`` or ``string`` **default**: `IntlDateFormatter::MEDIUM`_
(or ``yyyy-MM-dd`` if `widget`_ is ``single_text``)

Option passed to the ``IntlDateFormatter`` class, used to transform user
input into the proper format. This is critical when the `widget`_ option
is set to ``single_text`` and will define how the user will input the data.
By default, the format is determined based on the current user locale: meaning
that *the expected format will be different for different users*. You can
override it by passing the format as a string.

For more information on valid formats, see `Date/Time Format Syntax`_::

    use Symfony\Component\Form\Extension\Core\Type\DateType;
    // ...

    $builder->add('date_created', DateType::class, array(
        'widget' => 'single_text',
        // this is actually the default format for single_text
        'format' => 'yyyy-MM-dd',
    ));

.. note::

    If you want your field to be rendered as an HTML5 "date" field, you
    have to use a ``single_text`` widget with the ``yyyy-MM-dd`` format
    (the `RFC 3339`_ format) which is the default value if you use the
    ``single_text`` widget.

.. _`Date/Time Format Syntax`: http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
.. _`IntlDateFormatter::MEDIUM`: http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
.. _`RFC 3339`: http://tools.ietf.org/html/rfc3339
