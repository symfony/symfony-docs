.. index::
    single: Web Services; REST API

How to Create a REST Web Service in a Symfony2 Controller
=========================================================

A REST controller must be able to list, retrieve, create and update a resource.
This resource can be anything from a Doctrine entity to a domain object or a network device.

.. note::

    There is plenty of way to implement to REST API with Symfony2, we will show you how to implement
    a simple API with core components but you can also look at `FOSRestBundle`_ if you are looking
    at building a large API with hypermedia, versionning, automatic routing and more.

Setting up the routing
----------------------

Dealing with response code
--------------------------

Exposing resources in different formats
---------------------------------------

Updating Doctrine entities
--------------------------


.. _`FOSRestBundle`:     https://github.com/FriendsOfSymfony/FOSRestBundle
