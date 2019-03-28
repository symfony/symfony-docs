.. index::
   single: Mercure
   single: Components; Mercure

The Mercure Component
=====================

    `Mercure`_ is an open protocol allowing to push data updates to web
    browsers and other HTTP clients in a convenient, fast, reliable
    and battery-friendly way.
    It is especially useful to publish real-time updates of resources served
    through web APIs, to reactive web and mobile applications.

The Mercure Component implements the "publisher" part of the Mercure Protocol.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/mercure

Alternatively, you can clone the `<https://github.com/symfony/mercure>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

The following example shows the component in action::

    // change these values accordingly to your hub installation
    define('HUB_URL', 'https://demo.mercure.rocks/hub');
    define('JWT', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyJmb28iLCJiYXIiXSwicHVibGlzaCI6WyJmb28iXX19.LRLvirgONK13JgacQ_VbcjySbVhkSmHy3IznH3tA9PM');

    use Symfony\Component\Mercure\Publisher;
    use Symfony\Component\Mercure\Update;
    use Symfony\Component\Mercure\Jwt\StaticJwtProvide;

    $publisher = new Publisher(HUB_URL, new StaticJwtProvide(JWT));
    // Serialize the update, and dispatch it to the hub, that will broadcast it to the clients
    $id = $publisher(new Update('https://example.com/books/1.jsonld', 'Hi from Symfony!', ['target1', 'target2']));

Read the full :doc:`Mercure integration documentation </mercure>` to learn
about all the features of this component and its integration with the Symfony
framework.

.. _`Mercure`: https://mercure.rocks
