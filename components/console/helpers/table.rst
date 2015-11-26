.. index::
    single: Console Helpers; Table

Table
=====

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

    use Symfony\Component\Console\Helper\Table;
    // ...

    class SomeCommand extends Command
    {
        public function execute(InputInterface $input, OutputInterface $output)
        {
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
        }
    }

You can add a table separator anywhere in the output by passing an instance of
:class:`Symfony\\Component\\Console\\Helper\\TableSeparator` as a row::

    use Symfony\Component\Console\Helper\TableSeparator;

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

    use Symfony\Component\Console\Helper\TableStyle;

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

Spanning Multiple Columns and Rows
----------------------------------

.. versionadded:: 2.7
    Spanning multiple columns and rows was introduced in Symfony 2.7.

To make a table cell that spans multiple columns you can use a :class:`Symfony\\Component\\Console\\Helper\\TableCell`::

    use Symfony\Component\Console\Helper\Table;
    use Symfony\Component\Console\Helper\TableSeparator;
    use Symfony\Component\Console\Helper\TableCell;

    $table = new Table($output);
    $table
        ->setHeaders(array('ISBN', 'Title', 'Author'))
        ->setRows(array(
            array('99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'),
            new TableSeparator(),
            array(new TableCell('This value spans 3 columns.', array('colspan' => 3))),
        ))
    ;
    $table->render();

This results in:

.. code-block:: text

    +---------------+---------------+-----------------+
    | ISBN          | Title         | Author          |
    +---------------+---------------+-----------------+
    | 99921-58-10-7 | Divine Comedy | Dante Alighieri |
    +---------------+---------------+-----------------+
    | This value spans 3 columns.                     |
    +---------------+---------------+-----------------+

.. tip::

    You can create a multiple-line page title using a header cell that spans
    the enire table width::

        $table->setHeaders(array(
            array(new TableCell('Main table title', array('colspan' => 3))),
            array('ISBN', 'Title', 'Author'),
        ))
        // ...

    This generates:

    .. code-block:: text

        +-------+-------+--------+
        | Main table title       |
        +-------+-------+--------+
        | ISBN  | Title | Author |
        +-------+-------+--------+
        | ...                    |
        +-------+-------+--------+

In a similar way you can span multiple rows::

    use Symfony\Component\Console\Helper\Table;
    use Symfony\Component\Console\Helper\TableCell;

    $table = new Table($output);
    $table
        ->setHeaders(array('ISBN', 'Title', 'Author'))
        ->setRows(array(
            array(
                '978-0521567817',
                'De Monarchia',
                new TableCell("Dante Alighieri\nspans multiple rows", array('rowspan' => 2)),
            ),
            array('978-0804169127', 'Divine Comedy'),
        ))
    ;
    $table->render();

This outputs:

.. code-block:: text

    +----------------+---------------+---------------------+
    | ISBN           | Title         | Author              |
    +----------------+---------------+---------------------+
    | 978-0521567817 | De Monarchia  | Dante Alighieri     |
    | 978-0804169127 | Divine Comedy | spans multiple rows |
    +----------------+---------------+---------------------+

You can use the ``colspan`` and ``rowspan`` options at the same time which allows
you to create any table layout you may wish.
