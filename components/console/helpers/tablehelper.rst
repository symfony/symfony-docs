.. index::
    single: Console Helpers; Table Helper

Table Helper
============

.. versionadded:: 2.3
    The ``table`` helper was added in Symfony 2.3.

When building a console application it may be useful to display tabular data:

.. image:: /images/components/console/table.png

To display table, use the :class:`Symfony\\Component\\Console\\Helper\\TableHelper`,
set headers, rows and render::

    $table = $app->getHelperSet()->get('table');
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

Table layout can be customized as well. There are two ways to customize table rendering:
using named layouts or by customizing rendering options.

Customize Table Layout using Named Layouts
------------------------------------------

Table helper is shipped with two preconfigured table layouts:

* ``TableHelper::LAYOUT_DEFAULT``

* ``TableHelper::LAYOUT_BORDERLESS``

Layout can be set using :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setLayout` method.

Customize Table Layout using Rendering Options
----------------------------------------------

You can control table rendering by setting custom rendering option values:

*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setPaddingChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setHorizontalBorderChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setVerticalBorderChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setVrossingChar`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setVellHeaderFormat`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setVellRowFormat`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setBorderFormat`
*  :method:`Symfony\\Component\\Console\\Helper\\TableHelper::setPadType`
