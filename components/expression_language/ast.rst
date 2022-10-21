.. index::
    single: AST; ExpressionLanguage
    single: AST; Abstract Syntax Tree

Dumping and Manipulating the AST of Expressions
===============================================

It’s difficult to manipulate or inspect the expressions created with the ExpressionLanguage
component, because the expressions are plain strings. A better approach is to
turn those expressions into an AST. In computer science, `AST`_ (*Abstract
Syntax Tree*) is *"a tree representation of the structure of source code written
in a programming language"*. In Symfony, a ExpressionLanguage AST is a set of
nodes that contain PHP classes representing the given expression.

Dumping the AST
---------------

Call the :method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::getNodes`
method after parsing any expression to get its AST::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $ast = (new ExpressionLanguage())
        ->parse('1 + 2', [])
        ->getNodes()
    ;

    // dump the AST nodes for inspection
    var_dump($ast);

    // dump the AST nodes as a string representation
    $astAsString = $ast->dump();

Manipulating the AST
--------------------

The nodes of the AST can also be dumped into a PHP array of nodes to allow
manipulating them. Call the :method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::toArray`
method to turn the AST into an array::

    // ...

    $astAsArray = (new ExpressionLanguage())
        ->parse('1 + 2', [])
        ->getNodes()
        ->toArray()
    ;

.. _`AST`: https://en.wikipedia.org/wiki/Abstract_syntax_tree
