.. index::
   single: Serializer 
   single: Components; Serializer

The Serializer Component
====================

   The Serializer Component is meant to be used to turn Objects into a
   specific format (XML, JSON, Yaml, ...) and the other way around.

In order to do so, the Serializer Components follows the following
simple schema.

.. image:: /images/components/serializer/serializer_workflow.png

As you can see in the picture above, an array is used as a man in
the middle. This way, Encoders will only deal with turning specific
**formats** into **arrays** and vice versa, and Normalizer will deal with
turning specific **objects** into **arrays** and vice versa.

Also, it's clear from the graph the meaning of the following terms: *encode*,
*decode*, *normalize*, *denormalize*, *serialize* and *deserialize*.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Serializer);
* Install it via PEAR ( `pear.symfony.com/Serializer`);
* Install it via Composer (`symfony/serializer` on Packagist).

Encoders
--------
