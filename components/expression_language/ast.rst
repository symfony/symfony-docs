.. index::
    single: AST; ExpressionLanguage

Dumping and Manipulating the AST of Expressions
===============================================

In computer science, `AST`_ (*Abstract Syntax Trees*) are a tree representation
of the structure of source code written in a programming language. The
expressions created with the ExpressionLanguage component are strings, which
make them difficult to manipulate or inspect.

A better approach is to dump the AST of those expressions using the ``dump()``
method. This turns the original string expression into a set of PHP classes
describing the operations of that expression::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $language = new ExpressionLanguage();
    $ast = $language->dump('1 + 2');
    // $ast = new BinaryNode('+', new ConstantNode(1), new ConstantNode(2));

    $ast = $language->dump('"a" not in ["a", "b"]');
    // $ast = new BinaryNode('not in', new ConstantNode('a'), new ArrayNode(new ConstantNode('a'), new ConstantNode('b')));

    $ast = $language->dump('foo[0]');
    // $ast = new GetAttrNode(new NameNode('foo'), new ConstantNode(0));

Manipulating the AST
--------------------

.. TODO: https://github.com/symfony/symfony/pull/19060

.. _`AST`: https://en.wikipedia.org/wiki/Abstract_syntax_tree
