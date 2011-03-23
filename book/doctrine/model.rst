.. index::
   single: Model

Model, not the fashion kind
===========================

If you wanted to learn more about fashion and super-models, then this
section won't be helpful to you.

The Model we talk about in this section is the one used in
Model-View-Controller applications.

.. note::

   Model-View-Controller (MVC) is an application design pattern, that
   was originally introduced by Trygve Reenskaug for the Smalltalk
   platform. The main idea of MVC is separating presentation from the
   data and separating controller from presentation. This kind of
   separation let's each part of the application focus on exactly one
   goal. Controller focuses on changing the data of the Model, Model
   exposes its data for the View, while View is focused on creating
   Model representations.

In a typical MVC application, Controller prepares Model (or Models) for
the View based on the Request.

For example, when a user goes to our blog homepage, user's browser sends
Request, that is passed to the Controller responsible for rendering
posts. Controller calculates start and limit for the posts we're going
to display on the page, retrieves ``Post`` Models from the database and
passes that array to the View. The View renders HTML that is interpreted
by the browser.

What is a Model anyway
----------------------

Model is what the "M" in "MVC_" stands for. It is one of the three
whales of an MVC application. A model is responsible for changing its
internal state based on requests from the :doc:`controller
</quick_tour/the_controller>` and giving its current state information
to the :doc:`view </guides/templating/index>`. It is the main
application logic container.

For example, if you are building a blog, then you'll have a ``Post``
model, if you're building a content management system, then you will
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

It is obvious that the above class is very simple and testable, yet its
mostly complete and will fulfill all the needs of a simple blogging
engine.

This is it, now you know what a Model in Symfony2 is - it is any class
that you want to save into some sort of data storage mechanism and
retrieve later. The rest of the chapter is dedicated to explaining how
to interact with the database.

Databases and Symfony2
----------------------

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
   non-relational data storage mechanism available. One of such
   mechanisms is document oriented databases, for which we invented a
   new term "ODM" or "Object Document Mapping".

Going forward, we will be focusing on `Doctrine2 ORM`_ and Doctrine2
`MongoDB ODM`_ (which serves as an ODM for MongoDB_ - a popular document
store) as both have the deepest integration with Symfony2 at the time of
this writing.

Model is not a table
--------------------

The perception of a model class as a database table, and each individual
instance as a row was popularized by the Ruby on Rails framework. It
might be a good way of thinking about model and it will get you far
enough, if you're exposing a simple `CRUD`_ (create, retrieve, update,
delete) interface in your application that is.

This approach actually causes a lot of troubles once you're past the
CRUD part of your application and are trying to add more business logic.
Here are the common limitations of the above-described approach:

* Designing schema before software that will utilize it is like picking
  a hole before you've identified the peg you have - it might fit, but
  most probably won't.

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

ActiveRecord deprecated
-----------------------

ActiveRecord, while being well-known and widely recognized pattern is
not the pattern used in Doctrine2. This decision is based on a number of
reasons:

* Inheritance hell - base class for anything seems like the easiest
  solution. It fees natural to enhance functionality of an object, by
  inheriting it from another object. This approach, however, is very
  limiting as it forces a class into some inheritance tree, forbidding
  it from inheriting another class. This is especially bad as PHP is a
  single inheritance language with no horizontal code-reuse mechanism
  like Mixins or Multiple inheritance.

* Mixing of concerns - Users are not ActiveRecords. Application classes
  should not even know they are being stored in some sort of a database,
  as that fact shouldn't affect their behavior.

* Testability - testability and isolation are crucial for modern
  software projects. When instantiating an object, one doesn't expect a
  database connection to be opened. Ability of an object to talk to a
  database from virtually anywhere, is not only illogical, but can also
  be dangerous when an unaware developer begins to execute ``->save()``
  calls from a template layer for example.

* Performance - a very important aspect of ActiveRecord is performance,
  or rather inability to have such thing. Using transactions and
  in-memory object change tracking, Doctrine2 minimizes the
  communication with the database, saving not only database execution
  time, but also expensive network communication.

Conclusion
----------

Thanks to Doctrine2, Model is now probably the simplest concept in
Symfony2, it is in your complete control and not limited by persistence
specifics.

By teaming up with Doctrine2 to keep your code relieved of persistence
details, Symfony2 makes building database aware applications even
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