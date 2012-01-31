format
~~~~~~

**type**: ``integer`` or ``string`` **default**: ``IntlDateFormatter::MEDIUM``

Option passed to the ``IntlDateFormatter`` class, used to transform user input
into the proper format. This is critical when the `widget`_ option is
set to ``single_text``, and will define how the user will input the data.
By default, the format is determined based on the current user locale; you
can override it by passing the format as a string.

For more information on valid formats, see `Date/Time Format Syntax`_. For
example, to render a single text box that expects the user to end ``yyyy-MM-dd``,
use the following options::

    $builder->add('date_created', 'date', array(
        'widget' => 'single_text',
        'format' => 'yyyy-MM-dd',
    ));

.. _`Date/Time Format Syntax`: http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
