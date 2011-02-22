.. index::
   single: Model

Model
=====

Before you start reading on how to setup your database connections and
save/retrieve objects to/from the database, it is important that we set
some common language and concepts. This section is dedicated to
explaining what the Model is in Symfony2 and how it is to be used.

Introduction
------------

Model is what the "M" in "MVC_" stands for. It is one of the three
whales of an MVC application. A model is responsible for changing its
internal state based on requests from :doc:`controller
</quick_tour/the_controller>` and giving its current state information
to the :doc:`view </guides/templating/index>`. It is the main
application logic container. For example, if you are building a blog,
then you'll have a ``Post`` model, if you're building a content
management system, then you will need a ``Page`` model.

It is worth noting that Symfony2 doesn't come with an ORM or database
abstraction library of its own, this is just not the problem Symfony2 is
meant to solve. However, it provides deep integration with libraries
like Doctrine_ and Propel_, letting you use whichever one you like best.

.. note::

   The acronym "ORM" stands for "Object Relational Mapping" and
   represents a programming technique of converting data between
   incompatible type systems. Say we have a ``Post``, it is stored like
   a set of columns in a database, but is represented by an instance of
   class ``Post`` in our application. The process of transformation of
   data into an object is called object relation mapping. We will also
   see that this term is slightly outdated as it is used in dealing with
   relational database management systems, nowadays there are tons of
   non-relational data storage mechanism available. One of such is
   document oriented databases, hence a new term we invented "ODM" or
   "Object Document Mapping".

Going forward, we will be focusing on `Doctrine2 ORM`_ and Doctrine2
`MongoDB ODM`_ (which serves as an ODM for MongoDB_ - a popular document
store) as both have the deepest integration with Symfony2 at the time of
this writing.

Model is not a table
--------------------

The perception of a model class as a database table, and each individual
instance as a row was popularized by the `Ruby on Rails`_ framework. It
might be a good way of thinking about model and it will get you far
enough, if you're exposing a simple `CRUD`_ (create, retrieve, update,
delete) interface in your application that is.

This approach actually causes a lot of troubles once you're past the
CRUD part of your application and are trying to add more business logic.
Here are the common limitations of the above-described approach:

* Designing schema before software that will utilize it is like picking
  a hole before you've identified the peg you have - it might fit, but
  most probably not.
* Database must be tailored to fit your application's needs, not the
  other way around.
* Some data storage engines don't have notion of tables, rows or even
  schema, which makes it hard to use them, if your perception of a model
  is that it represents a table.
* Keeping database schema in your head while designing your application
  domain is problematic, and following the rule of the lowest common
  denominator will give you the worst of both worlds.

`Doctrine2 ORM`_ is designed to remove the need to keep database
structure in mind and let you concentrate on writing the cleanest
possible models, that will satisfy your domain needs. It lets you design
your class/objects and interaction between them, allowing you to
postpone persistence decisions until you're ready.

Paradigm shift
--------------

With the introduction of Doctrine2, some of the core paradigms have
shifted. `Domain Driven Design`_ teaches us that objects are best
modeled, when modeled after their real-world prototypes. E.g. a ``Car``
object is best modeled to contain ``Engine``, four instances of
``Tire``, etc. and should be produced by ``CarFactory``, that knows how
to assemble all the parts together. Domain driven design deserves a book
in its own, as the concept it rather broad, however, for the purposes of
this guide it should be clear, that a car cannot start by itself, there
must be an external impulse to this. In a similar manner, a model cannot
save itself without and external impulse, therefore the following piece
of code violates DDD and will be troublesome to redesign in a clean,
testable way.

.. code-block:: php

   <?php

   $post->save();

Hence, Doctrine2 is not your typical `Active Record`_ anymore, instead
its an implementation of a different set of patterns, most importantly -
`Data Mapper`_ and `Unit Of Work`_, that is why in Doctrine2 we would
do:

.. code-block:: php

   <?php

   $manager = //... get object manager instance

   $manager->persist($post);
   $manager->flush();

We will soon find out what an "object manager" is. The described
paradigm shift lets us get rid of any base classes and static
dependencies, any object, given it has state, can be saved into a
database for later retrieval. More than that, once persisted, an object
is managed by an object manager, until the manager gets explicitly
cleared, that means, that all object interactions happen in memory
without ever going to the database until the ``$manager->flush()`` is
called. Needless to say, that this kind of approach lets you worry about
database and query optimizations even less, as all queries are as lazy
as possible by default and their execution is deferred until the latest
possible moment.

.. _Ruby on Rails: http://rubyonrails.org/
.. _Doctrine: http://www.doctrine-project.org/
.. _Propel: http://www.propelorm.org/
.. _Doctrine2 DBAL: http://www.doctrine-project.org/projects/dbal
.. _Doctrine2 ORM: http://www.doctrine-project.org/projects/orm
.. _MongoDB ODM: http://www.doctrine-project.org/projects/mongodb_odm
.. _MongoDB: http://www.mongodb.org
.. _Domain Driven Design: http://domaindrivendesign.org/
.. _Active Record: http://martinfowler.com/eaaCatalog/activeRecord.html
.. _Data Mapper: http://martinfowler.com/eaaCatalog/dataMapper.html
.. _Unit Of Work: http://martinfowler.com/eaaCatalog/unitOfWork.html
.. _CRUD: http://en.wikipedia.org/wiki/Create,_read,_update_and_delete
.. _MVC: http://en.wikipedia.org/wiki/Model-View-Controller