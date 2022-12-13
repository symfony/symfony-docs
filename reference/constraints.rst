Validation Constraints Reference
================================

.. toctree::
   :maxdepth: 1
   :hidden:

   constraints/NotBlank
   constraints/Blank
   constraints/NotNull
   constraints/IsNull
   constraints/IsTrue
   constraints/IsFalse
   constraints/Type

   constraints/Email
   constraints/ExpressionSyntax
   constraints/Length
   constraints/Url
   constraints/Regex
   constraints/Hostname
   constraints/Ip
   constraints/Uuid
   constraints/Ulid
   constraints/Json

   constraints/EqualTo
   constraints/NotEqualTo
   constraints/IdenticalTo
   constraints/NotIdenticalTo
   constraints/LessThan
   constraints/LessThanOrEqual
   constraints/GreaterThan
   constraints/GreaterThanOrEqual
   constraints/Range
   constraints/DivisibleBy
   constraints/Unique

   constraints/Positive
   constraints/PositiveOrZero
   constraints/Negative
   constraints/NegativeOrZero

   constraints/Date
   constraints/DateTime
   constraints/Time
   constraints/Timezone

   constraints/Choice
   constraints/Collection
   constraints/Count
   constraints/UniqueEntity
   constraints/Language
   constraints/Locale
   constraints/Country

   constraints/File
   constraints/Image

   constraints/CardScheme
   constraints/Currency
   constraints/Luhn
   constraints/Iban
   constraints/Bic
   constraints/Isbn
   constraints/Issn
   constraints/Isin

   constraints/AtLeastOneOf
   constraints/Sequentially
   constraints/Compound
   constraints/Callback
   constraints/Expression
   constraints/All
   constraints/UserPassword
   constraints/NotCompromisedPassword
   constraints/Valid
   constraints/Traverse
   constraints/CssColor

The Validator is designed to validate objects against *constraints*.
In real life, a constraint could be: "The cake must not be burned". In
Symfony, constraints are similar: They are assertions that a condition is
true.

Supported Constraints
---------------------

The following constraints are natively available in Symfony:

.. include:: /reference/constraints/map.rst.inc
