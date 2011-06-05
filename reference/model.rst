.. index::
   single: Model

Introduction to the "Model"
===========================

If you wanted to learn more about fashion models and supermodels, then this
section won't be helpful to you. But if you're looking to learn about the
model - the layer in your application that manages data - then keep reading.
The Model description in this section is the one used when talking about
*Model-View-Controller* applications.

.. note::

   Model-View-Controller (MVC) is an application design pattern
   originally introduced by Trygve Reenskaug for the Smalltalk
   platform. The main idea of MVC is separating presentation from the
   data and separating the controller from presentation. This kind of
   separation lets each part of the application focus on exactly one
   goal. The controller focuses on changing the data of the Model, the Model
   exposes its data for the View, and the View focuses on creating
   representations of the Model (e.g. an HTML page displaying "Blog Posts").

For example, when a user goes to your blog homepage, the user's browser sends
a request, which is passed to the Controller responsible for rendering
posts. The Controller calculates which posts should be displayed, retrieves
``Post`` Models from the database and passes that array to the View. The
View renders HTML that is interpreted by the browser.

What is a Model anyway?
-----------------------

The *Model* is what the "M" in "MVC_" stands for. It is one of the three
whales of an MVC application. A model is responsible for changing its
internal state based on requests from the :doc:`controller
</quick_tour/the_controller>` and giving its current state information
to the :doc:`view </book/templating>`. It is the main
application logic container.

For example, if you are building a blog, then you'll have a ``Post``
model. If you're building a content management system, then you will
need a ``Page`` model.

.. code-block:: php
    
    <?php
    
    namespace Blog;
    
    class Post
    {
        private $title;
        private $body;
        private $createdAt;
        private $updatedAt;
        
        public function __construct($title, $body)
        {
            $this->title     = $title;
            $this->body      = $body;
            $this->createdAt = new \DateTime();
        }
        
        public function setTitle($title)
        {
            $this->title     = $title;
            $this->updatedAt = new \DateTime();
        }
        
        public function setBody($body)
        {
            $this->body      = $body;
            $this->updatedAt = new \DateTime();
        }
        
        public function getTitle()
        {
            return $this->title;
        }
        
        public function getBody()
        {
            return $this->body;
        }
    }

It is obvious that the above class is very simple and testable, yet it's
mostly complete and will fulfill all the needs of a simple blogging
engine.

That's it! You now know what a Model in Symfony2 is: it is any class
that you want to save into some sort of data storage mechanism and
retrieve later. The rest of the chapter is dedicated to explaining how
to interact with the database.

Databases and Symfony2
----------------------

It is worth noting that Symfony2 doesn't come with an object relational
mapper (ORM) or database abstraction layer (DBAL) of its own; this is
just not the problem Symfony2 is meant to solve. However, Symfony2 provides
deep integration with libraries like Doctrine_ and Propel_, which *do*
provide ORM and DBAL packages, letting you use whichever one you like best.

.. note::

   The acronym "ORM" stands for "Object Relational Mapping" and
   represents a programming technique of converting data between
   incompatible type systems. Say we have a ``Post``, which is stored as
   a set of columns in a database, but represented by an instance of
   class ``Post`` in your application. The process of transforming
   the database table into an object is called *object relation mapping*.
   We will also see that this term is slightly outdated, as it is used in
   dealing with relational database management systems. Nowadays there are
   tons of non-relational data storage mechanism available. One such mechanism
   is the *document oriented database* (e.g. MongoDB), which uses a
   new term, "Object Document Mapping" or "ODM".

Going forward, you'll learn about the `Doctrine2 ORM`_ and Doctrine2
`MongoDB ODM`_ (which serves as an ODM for MongoDB_ - a popular document
store) as both have the deepest integration with Symfony2 at the time of
this writing.

A Model is not a Table
----------------------

The perception of a model class as a database table, where each object
instance represents a single row, was popularized by the Ruby on Rails
framework and the `Active Record`_ design pattern. This is a good way of first
thinking about the model layer of your application, especially if you're
exposing a simple `CRUD`_ (Create, Retrieve, Update, Delete) interface
for modifying the data of a model.

But this approach can actually cause problems once you're past the CRUD part
of your application and start adding more business logic. Here are
the common limitations of the above-described approach:

* Designing a schema before the actual software that will utilize it is
  like digging a hole before knowing what you need to bury.
  The item might fit in the hole you dig, but what if you're burying a
  large firetruck? This requires an entirely different approach if you want
  to do the job efficiently.

* A database should be tailored to fit your application's needs, not the
  other way around.

* Some data storage engines (like document databases) don't have a notion
  of tables, rows or even a schema, making it hard to use them if your
  perception of a model is that which represents a table.

* Keeping database schema in your head while designing your application
  domain is problematic, and following the rule of the lowest common
  denominator will give you the worst of both worlds.

The `Doctrine2 ORM`_ is designed to remove the need to keep database
structure in mind and let you concentrate on writing the cleanest
possible models that will satisfy your business needs. It lets you design
your classes and their interactions first, before requiring you to even
think about *how* to persist your data.

Paradigm Shift
--------------

With the introduction of Doctrine2, some of the core paradigms have
shifted. `Domain Driven Design`_ (DDD) teaches us that objects are best
modeled when modeled after their real-world prototypes. For example a ``Car``
object is best modeled to contain ``Engine``, four instances of
``Tire``, etc. and should be produced by ``CarFactory`` - something that
knows how to assemble all the parts together. Domain driven design deserves
a book in its own, as the concept is rather broad. However, for the purposes
of this chapter, it should be clear that a car cannot start by itself, there
must be an external impulse to start it. In a similar manner, a model cannot
save itself without an external impulse, therefore the following piece of
code violates DDD and will be troublesome to redesign in a clean, testable way.

.. code-block:: php

   $post->save();

Hence, Doctrine2 is not your typical `Active Record`_ implementation anymore.
Instead Doctrine2 uses a different set of patterns, most importantly the
`Data Mapper`_ and `Unit Of Work`_ patterns. The following example shows
how to save an entity with Doctrine2:

.. code-block:: php

   $manager = //... get object manager instance

   $manager->persist($post);
   $manager->flush();

The "object manager" is a central object provided by Doctrine whose job
is to persist objects. You'll soon learn much more about this service.
This paradigm shift lets us get rid of any base classes (e.g. ``Post``
doesn't need to extend a base class) and static dependencies. Any object
can be saved into a database for later retrieval. More than that, once persisted,
an object is managed by the object manager until the manager is explicitly
cleared. This means all object interactions happen in memory,
without ever going to the database until ``$manager->flush()`` is
called. Needless to say, this provides an instant database and query
optimization compared to most other object persistence patterns, as all
queries are as lazy as possible (i.e. their execution is deferred until the
latest possible moment).

A very important aspect of the Active Record pattern is performance, or
rather, the *difficulty* in building a performant system. By using transactions
and in-memory object change tracking, Doctrine2 minimizes communication
with the database, saving not only database execution time, but also
expensive network communication.

Conclusion
----------

Thanks to Doctrine2, the Model is now probably the simplest concept in
Symfony2: it is in your complete control and not limited by persistence
specifics.

By teaming up with Doctrine2 to keep your code relieved of persistence
details, Symfony2 makes building database-aware applications even
simpler. Application code stays clean, which will decrease development
time and improve understandability of the code.

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