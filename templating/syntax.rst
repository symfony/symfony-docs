.. index::
    single: Templating; Linting
    single: Twig; Linting
    single: Templating; Syntax Check
    single: Twig; Syntax Check

How to Check the Syntax of Your Twig Templates
==============================================

You can check for syntax errors in Twig templates using the ``lint:twig``
console command:

.. code-block:: terminal

    # You can check by filename:
    $ php app/console lint:twig app/Resources/views/article/recent_list.html.twig

    # or by directory:
    $ php app/console lint:twig app/Resources/views
