.. index::
    single: AST; ExpressionLanguage

Dumping and Manipulating the AST of Expressions
===============================================

In computer science, `AST`_ (*Abstract Syntax Trees*) is *"a tree representation
of the structure of source code written in a programming language"*.

Manipulating or inspecting the expressions created with the ExpressionLanguage
component is difficult because they are plain strings. A better approach is to
turn those expressions into an AST, which is a set of nodes that contain PHP
classes.

Dumping the AST
---------------

Call the ``getNodes()`` method after parsing any expression to get its AST::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $ast = (new ExpressionLanguage())
        ->parse('1 + 2')
        ->getNodes()
    ;

    // dump the AST nodes for inspection
    var_dump($ast);

    // dump the AST nodes as a string representation
    $astAsString = $ast->dump();

Manipulating the AST
--------------------

The nodes of the AST can also be dumped into a PHP array of nodes to allow
manipulating them. Call the ``toArray()`` method to turn the AST into an array::

    // ...

    $astAsArray = (new ExpressionLanguage())
        ->parse('1 + 2')
        ->getNodes()
        ->toArray()
    ;

.. _`AST`: https://en.wikipedia.org/wiki/Abstract_syntax_tree
