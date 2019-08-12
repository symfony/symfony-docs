.. index::
    single: Console Helpers; Table

Table
=====

When building a console application it may be useful to display tabular data:

.. code-block:: terminal

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

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Helper\Table;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    // ...

    class SomeCommand extends Command
    {
        public function execute(InputInterface $input, OutputInterface $output)
        {
            $table = new Table($output);
            $table
                ->setHeaders(['ISBN', 'Title', 'Author'])
                ->setRows([
                    ['99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'],
                    ['9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'],
                    ['960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'],
                    ['80-902734-1-6', 'And Then There Were None', 'Agatha Christie'],
                ])
            ;
            $table->render();
        }
    }

You can add a table separator anywhere in the output by passing an instance of
:class:`Symfony\\Component\\Console\\Helper\\TableSeparator` as a row::

    use Symfony\Component\Console\Helper\TableSeparator;

    $table->setRows([
        ['99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'],
        ['9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'],
        new TableSeparator(),
        ['960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'],
        ['80-902734-1-6', 'And Then There Were None', 'Agatha Christie'],
    ]);

.. code-block:: terminal

    +---------------+--------------------------+------------------+
    | ISBN          | Title                    | Author           |
    +---------------+--------------------------+------------------+
    | 99921-58-10-7 | Divine Comedy            | Dante Alighieri  |
    | 9971-5-0210-0 | A Tale of Two Cities     | Charles Dickens  |
    +---------------+--------------------------+------------------+
    | 960-425-059-0 | The Lord of the Rings    | J. R. R. Tolkien |
    | 80-902734-1-6 | And Then There Were None | Agatha Christie  |
    +---------------+--------------------------+------------------+

You can optionally display titles at the top and the bottom of the table::

    // ...
    $table->setHeaderTitle('Books')
    $table->setFooterTitle('Page 1/2')
    $table->render();

.. code-block:: terminal

    +---------------+----------- Books --------+------------------+
    | ISBN          | Title                    | Author           |
    +---------------+--------------------------+------------------+
    | 99921-58-10-7 | Divine Comedy            | Dante Alighieri  |
    | 9971-5-0210-0 | A Tale of Two Cities     | Charles Dickens  |
    +---------------+--------------------------+------------------+
    | 960-425-059-0 | The Lord of the Rings    | J. R. R. Tolkien |
    | 80-902734-1-6 | And Then There Were None | Agatha Christie  |
    +---------------+--------- Page 1/2 -------+------------------+

By default, the width of the columns is calculated automatically based on their
contents. Use the :method:`Symfony\\Component\\Console\\Helper\\Table::setColumnWidths`
method to set the column widths explicitly::

    // ...
    $table->setColumnWidths([10, 0, 30]);
    $table->render();

In this example, the first column width will be ``10``, the last column width
will be ``30`` and the second column width will be calculated automatically
because of the ``0`` value.

You can also set the width individually for each column with the
:method:`Symfony\\Component\\Console\\Helper\\Table::setColumnWidth` method.
Its first argument is the column index (starting from ``0``) and the second
argument is the column width::

    // ...
    $table->setColumnWidth(0, 10);
    $table->setColumnWidth(2, 30);
    $table->render();

The output of this command will be:

.. code-block:: terminal

    +---------------+--------------------------+--------------------------------+
    | ISBN          | Title                    | Author                         |
    +---------------+--------------------------+--------------------------------+
    | 99921-58-10-7 | Divine Comedy            | Dante Alighieri                |
    | 9971-5-0210-0 | A Tale of Two Cities     | Charles Dickens                |
    +---------------+--------------------------+--------------------------------+
    | 960-425-059-0 | The Lord of the Rings    | J. R. R. Tolkien               |
    | 80-902734-1-6 | And Then There Were None | Agatha Christie                |
    +---------------+--------------------------+--------------------------------+

Note that the defined column widths are always considered as the minimum column
widths. If the contents don't fit, the given column width is increased up to the
longest content length. That's why in the previous example the first column has
a ``13`` character length although the user defined ``10`` as its width.

If you prefer to wrap long contents in multiple rows, use the
:method:`Symfony\\Component\\Console\\Helper\\Table::setColumnMaxWidth` method::

    // ...
    $table->setColumnMaxWidth(0, 5);
    $table->setColumnMaxWidth(1, 10);
    $table->render();

The output of this command will be:

.. code-block:: terminal

    +-------+------------+--------------------------------+
    | ISBN  | Title      | Author                         |
    +-------+------------+--------------------------------+
    | 99921 | Divine Com | Dante Alighieri                |
    | -58-1 | edy        |                                |
    | 0-7   |            |                                |
    |                (the rest of rows...)                |
    +-------+------------+--------------------------------+

The table style can be changed to any built-in styles via
:method:`Symfony\\Component\\Console\\Helper\\Table::setStyle`::

    // same as calling nothing
    $table->setStyle('default');

    // changes the default style to compact
    $table->setStyle('compact');
    $table->render();

This code results in:

.. code-block:: terminal

     ISBN          Title                    Author
     99921-58-10-7 Divine Comedy            Dante Alighieri
     9971-5-0210-0 A Tale of Two Cities     Charles Dickens
     960-425-059-0 The Lord of the Rings    J. R. R. Tolkien
     80-902734-1-6 And Then There Were None Agatha Christie

You can also set the style to ``borderless``::

    $table->setStyle('borderless');
    $table->render();

which outputs:

.. code-block:: terminal

     =============== ========================== ==================
      ISBN            Title                      Author
     =============== ========================== ==================
      99921-58-10-7   Divine Comedy              Dante Alighieri
      9971-5-0210-0   A Tale of Two Cities       Charles Dickens
      960-425-059-0   The Lord of the Rings      J. R. R. Tolkien
      80-902734-1-6   And Then There Were None   Agatha Christie
     =============== ========================== ==================

You can also set the style to ``box``::

    $table->setStyle('box');
    $table->render();

which outputs:

.. code-block:: text

    ┌───────────────┬──────────────────────────┬──────────────────┐
    │ ISBN          │ Title                    │ Author           │
    ├───────────────┼──────────────────────────┼──────────────────┤
    │ 99921-58-10-7 │ Divine Comedy            │ Dante Alighieri  │
    │ 9971-5-0210-0 │ A Tale of Two Cities     │ Charles Dickens  │
    │ 960-425-059-0 │ The Lord of the Rings    │ J. R. R. Tolkien │
    │ 80-902734-1-6 │ And Then There Were None │ Agatha Christie  │
    └───────────────┴──────────────────────────┴──────────────────┘

You can also set the style to ``box-double``::

    $table->setStyle('box-double');
    $table->render();

which outputs:

.. code-block:: text

    ╔═══════════════╤══════════════════════════╤══════════════════╗
    ║ ISBN          │ Title                    │ Author           ║
    ╠═══════════════╪══════════════════════════╪══════════════════╣
    ║ 99921-58-10-7 │ Divine Comedy            │ Dante Alighieri  ║
    ║ 9971-5-0210-0 │ A Tale of Two Cities     │ Charles Dickens  ║
    ║ 960-425-059-0 │ The Lord of the Rings    │ J. R. R. Tolkien ║
    ║ 80-902734-1-6 │ And Then There Were None │ Agatha Christie  ║
    ╚═══════════════╧══════════════════════════╧══════════════════╝

If the built-in styles do not fit your need, define your own::

    use Symfony\Component\Console\Helper\TableStyle;

    // by default, this is based on the default style
    $tableStyle = new TableStyle();

    // customizes the style
    $tableStyle
        ->setDefaultCrossingChars('<fg=magenta>|</>')
        ->setVerticalBorderChars('<fg=magenta>-</>')
        ->setDefaultCrossingChar(' ')
    ;

    // uses the custom style for this table
    $table->setStyle($tableStyle);

Here is a full list of things you can customize:

*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setPaddingChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setDefaultCrossingChars`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setVerticalBorderChars`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setCrossingChars`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setDefaultCrossingChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setCellHeaderFormat`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setCellRowFormat`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setBorderFormat`
*  :method:`Symfony\\Component\\Console\\Helper\\TableStyle::setPadType`

.. tip::

    You can also register a style globally::

        // registers the style under the colorful name
        Table::setStyleDefinition('colorful', $tableStyle);

        // applies the custom style for the given table
        $table->setStyle('colorful');

    This method can also be used to override a built-in style.

Spanning Multiple Columns and Rows
----------------------------------

To make a table cell that spans multiple columns you can use a :class:`Symfony\\Component\\Console\\Helper\\TableCell`::

    use Symfony\Component\Console\Helper\Table;
    use Symfony\Component\Console\Helper\TableCell;
    use Symfony\Component\Console\Helper\TableSeparator;

    $table = new Table($output);
    $table
        ->setHeaders(['ISBN', 'Title', 'Author'])
        ->setRows([
            ['99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'],
            new TableSeparator(),
            [new TableCell('This value spans 3 columns.', ['colspan' => 3])],
        ])
    ;
    $table->render();

This results in:

.. code-block:: terminal

    +---------------+---------------+-----------------+
    | ISBN          | Title         | Author          |
    +---------------+---------------+-----------------+
    | 99921-58-10-7 | Divine Comedy | Dante Alighieri |
    +---------------+---------------+-----------------+
    | This value spans 3 columns.                     |
    +---------------+---------------+-----------------+

.. tip::

    You can create a multiple-line page title using a header cell that spans
    the entire table width::

        $table->setHeaders([
            [new TableCell('Main table title', ['colspan' => 3])],
            ['ISBN', 'Title', 'Author'],
        ])
        // ...

    This generates:

    .. code-block:: terminal

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
        ->setHeaders(['ISBN', 'Title', 'Author'])
        ->setRows([
            [
                '978-0521567817',
                'De Monarchia',
                new TableCell("Dante Alighieri\nspans multiple rows", ['rowspan' => 2]),
            ],
            ['978-0804169127', 'Divine Comedy'],
        ])
    ;
    $table->render();

This outputs:

.. code-block:: terminal

    +----------------+---------------+---------------------+
    | ISBN           | Title         | Author              |
    +----------------+---------------+---------------------+
    | 978-0521567817 | De Monarchia  | Dante Alighieri     |
    | 978-0804169127 | Divine Comedy | spans multiple rows |
    +----------------+---------------+---------------------+

You can use the ``colspan`` and ``rowspan`` options at the same time which allows
you to create any table layout you may wish.

.. _console-modify-rendered-tables:

Modifying Rendered Tables
-------------------------

The ``render()`` method requires passing the entire table contents. However,
sometimes that information is not available beforehand because it's generated
dynamically. In those cases, use the
:method:`Symfony\\Component\\Console\\Helper\\Table::appendRow` method, which
takes the same arguments as the ``addRow()`` method, to add rows at the bottom
of an already rendered table.

The only requirement to append rows is that the table must be rendered inside a
:ref:`Console output section <console-output-sections>`::

    use Symfony\Component\Console\Helper\Table;
    // ...

    class SomeCommand extends Command
    {
        public function execute(InputInterface $input, OutputInterface $output)
        {
            $section = $output->section();
            $table = new Table($section);

            $table->addRow(['Love']);
            $table->render();

            $table->appendRow(['Symfony']);
        }
    }

This will display the following table in the terminal:

.. code-block:: terminal

    +---------+
    | Love    |
    | Symfony |
    +---------+
