.. index::
    single: Console Helpers; Table

Table
=====

.. versionadded:: 2.5
    The ``Table`` class was introduced in Symfony 2.5 as a replacement for the
    :doc:`Table Helper </components/console/helpers/tablehelper>`.

When building a console application it may be useful to display tabular data:

.. code-block:: text

    +---------------+--------------------------+------------------+
    | ISBN          | Title                    | Author           |
    +---------------+--------------------------+------------------+
    | 99921-58-10-7 | Divine Comedy            | Dante Alighieri  |
    | 9971-5-0210-0 | A Tale of Two Cities     | Charles Dickens  |
    | 960-425-059-0 | The Lord of the Rings    | J. R. R. Tolkien |
    | 80-902734-1-6 | And Then There Were None | Agatha Christie  |
    +---------------+--------------------------+------------------+

To display a table, use :class:`Symfony\\Component\\Console\\Helper\\Table`,
set the headers, set the rows and then render the table::

    use Symfony\Component\Helper\Table;

    $table = new Table($output);
    $table
        ->setHeaders(array('ISBN', 'Title', 'Author'))
        ->setRows(array(
            array('99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'),
            array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'),
            array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
            array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
        ))
    ;
    $table->render();

You can add a table separator anywhere in the output by passing an instance of
:class:`Symfony\\Component\\Console\\Helper\\TableSeparator` as a row::

    use Symfony\Component\Helper\TableSeparator;

    $table->setRows(array(
        array('99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'),
        array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'),
        new TableSeparator(),
        array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
        array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
    ));

.. code-block:: text

    +---------------+--------------------------+------------------+
    | ISBN          | Title                    | Author           |
    +---------------+--------------------------+------------------+
    | 99921-58-10-7 | Divine Comedy            | Dante Alighieri  |
    | 9971-5-0210-0 | A Tale of Two Cities     | Charles Dickens  |
    +---------------+--------------------------+------------------+
    | 960-425-059-0 | The Lord of the Rings    | J. R. R. Tolkien |
    | 80-902734-1-6 | And Then There Were None | Agatha Christie  |
    +---------------+--------------------------+------------------+

The table style can be changed to any built-in styles via
:method:`Symfony\\Component\\Console\\Helper\\Table::setStyle`::

    // same as calling nothing
    $table->setStyle('default');

    // changes the default style to compact
    $table->setStyle('compact');
    $table->render();

This code results in:

.. code-block:: text

     ISBN          Title                    Author
     99921-58-10-7 Divine Comedy            Dante Alighieri
     9971-5-0210-0 A Tale of Two Cities     Charles Dickens
     960-425-059-0 The Lord of the Rings    J. R. R. Tolkien
     80-902734-1-6 And Then There Were None Agatha Christie

You can also set the style to ``borderless``::

    $table->setStyle('borderless');
    $table->render();

which outputs:

.. code-block:: text

     =============== ========================== ==================
      ISBN            Title                      Author
     =============== ========================== ==================
      99921-58-10-7   Divine Comedy              Dante Alighieri
      9971-5-0210-0   A Tale of Two Cities       Charles Dickens
      960-425-059-0   The Lord of the Rings      J. R. R. Tolkien
      80-902734-1-6   And Then There Were None   Agatha Christie
     =============== ========================== ==================

If the built-in styles do not fit your need, define your own::

    use Symfony\Component\Helper\TableStyle;

    // by default, this is based on the default style
    $style = new TableStyle();

    // customize the style
    $style
        ->setHorizontalBorderChar('<fg=magenta>|</>')
        ->setVerticalBorderChar('<fg=magenta>-</>')
        ->setCrossingChar(' ')
    ;

    // use the style for this table
    $table->setStyle($style);

Here is a full list of things you can customize:

*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setPaddingChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setHorizontalBorderChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setVerticalBorderChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setCrossingChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setCellHeaderFormat`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setCellRowFormat`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setBorderFormat`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setPadType`

.. tip::

    You can also register a style globally::

        // register the style under the colorful name
        Table::setStyleDefinition('colorful', $style);

        // use it for a table
        $table->setStyle('colorful');

    This method can also be used to override a built-in style.
