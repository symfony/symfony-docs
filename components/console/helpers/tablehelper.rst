.. index::
    single: Console Helpers; Table Helper

Table Helper
============

.. versionadded:: 2.3
    The ``table`` helper was introduced in Symfony 2.3.

.. caution::

    The Table Helper was deprecated in Symfony 2.5 and will be removed in
    Symfony 3.0. You should now use the
    :doc:`Table </components/console/helpers/table>` class instead which is
    more powerful.

When building a console application it may be useful to display tabular data:

.. image:: /images/components/console/table.png

To display a table, use the :class:`Symfony\\Component\\Console\\Helper\\TableHelper`,
set headers, rows and render::

    $table = $this->getHelper('table');
    $table
        ->setHeaders(array('ISBN', 'Title', 'Author'))
        ->setRows(array(
            array('99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'),
            array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'),
            array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
            array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
        ))
    ;
    $table->render($output);

The table layout can be customized as well. There are two ways to customize
table rendering: using named layouts or by customizing rendering options.

Customize Table Layout using Named Layouts
------------------------------------------

The Table helper ships with three preconfigured table layouts:

* ``TableHelper::LAYOUT_DEFAULT``

* ``TableHelper::LAYOUT_BORDERLESS``

* ``TableHelper::LAYOUT_COMPACT``

Layout can be set using :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setLayout` method.

Customize Table Layout using Rendering Options
----------------------------------------------

You can also control table rendering by setting custom rendering option values:

*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setPaddingChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setHorizontalBorderChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setVerticalBorderChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setCrossingChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setCellHeaderFormat`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setCellRowFormat`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setBorderFormat`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setPadType`
