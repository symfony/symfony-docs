.. index::
   single: Performance

Using bootstrap files
=====================

To ensure optimal flexibility and code reuse Symfony2 applications will leverage
a verity of classes and 3rd party components. Loading all of these classes from
separate files in each request can result in quite some overhead. To reduce this
overhead the Symfony2 standard edition provides a script to generate a so called
`bootstrap file`_ consisting of multiple classes definitions in a single file.
This will reduce disc IO quite a bit.

Note there are two disadvantages from using such a bootstrap file:

* the file needs to be regenerated whenever any of the original sources change
* when debugging one will need to place break points inside the bootstrap file

.. _`bootstrap file`: https://github.com/symfony/symfony-standard/blob/master/bin/build_bootstrap

Bootstrap files and byte code caches
------------------------------------

Even when using a byte code cache the performance will improve by using a bootstrap
file, since there will be less files to monitor for changes. Of course if this
feature is disabled in the byte code cache there is no longer a reason to use
a bootstrap file.


