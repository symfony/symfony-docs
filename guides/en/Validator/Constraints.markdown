Constraints
===========

The Validator is designed to validate objects against different *constraints*.
In real life, a constraint would be: "The cake must not be burned". In
Symfony2, constraints are very similar: They are assertions that a specific
condition is true.

Constraints can be put on properties of a class, on public getters and on the
class itself. Property and getter constraints obviously can only be used to
validate a single value. Class constraints, on the other hand, can validate
the whole state of an object at once, with all its properties and methods.

>**NOTE**
>As "getter" the validator accepts any method with the prefix "get" or "is."

Supported Constraints
---------------------

The following constraints are natively available in Symfony2:

  * [AssertFalse](Constraints/AssertFalse)
  * [AssertTrue](Constraints/AssertTrue)
  * [AssertType](Constraints/AssertType)
  * [Choice](Constraints/Choice)
  * [Collection](Constraints/Collection)
  * [Date](Constraints/Date)
  * [DateTime](Constraints/DateTime)
  * [Email](Constraints/Email)
  * [File](Constraints/File)
  * [Max](Constraints/Max)
  * [MaxLength](Constraints/MaxLength)
  * [Min](Constraints/Min)
  * [MinLength](Constraints/MinLength)
  * [NotBlank](Constraints/NotBlank)
  * [NotNull](Constraints/NotNull)
  * [Regex](Constraints/Regex)
  * [Time](Constraints/Time)
  * [Url](Constraints/Url)
  * [Valid](Constraints/Valid)
  
Constraint Options
------------------

TODO
  
  




