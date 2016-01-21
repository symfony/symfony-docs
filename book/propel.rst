.. index::
   single: Propel

Databases and Propel
====================

Propel is an open-source Object-Relational Mapping (ORM) for PHP which
implements the `ActiveRecord pattern`_. It allows you to access your database
using a set of objects, providing a simple API for storing and retrieving data.
Propel uses PDO as an abstraction layer and code generation to remove the
burden of runtime introspection.

A few years ago, Propel was a very popular alternative to Doctrine. However, its
popularity has rapidly declined and that's why the Symfony book no longer includes
the Propel documentation. Read the `official PropelBundle documentation`_ to learn
how to integrate Propel into your Symfony projects.

.. _`ActiveRecord pattern`: https://en.wikipedia.org/wiki/Active_record_pattern
.. _`official PropelBundle documentation`: https://github.com/propelorm/PropelBundle/blob/1.4/Resources/doc/index.markdown
