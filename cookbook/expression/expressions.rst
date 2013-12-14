.. index::
   single: Expressions in the Framework

How to use Expressions in Security, Routing, Services, and Validation
=====================================================================

.. versionadded:: 2.4
    The expression functionality was introduced in Symfony 2.4.

In Symfony 2.4, a powerful :doc:`ExpressionLanguage </components/expression_language/introduction>`
component was added to Symfony. This allows us to add highly customized
logic inside configuration.

The Symfony Framework leverages expressions out of the box in the following
ways:

* :ref:`Configuring services <book-services-expressions>`;
* :ref:`Route matching conditions <book-routing-conditions>`;
* :ref:`Checking security <book-security-expressions>` and
  :ref:`access controls with allow_if <book-security-allow-if>`;
* :doc:`Validation </reference/constraints/Expression>`.

For more information about how to create and work with expressions, see
:doc:`/components/expression_language/syntax`.
