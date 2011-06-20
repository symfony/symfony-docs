.. index::
   single: Performance; Bootstrap files

Using Bootstrap Files
=====================

To ensure optimal flexibility and code reuse, Symfony2 applications leverage
a variety of classes and 3rd party components. Loading all of these classes
from separate files on each request can result in some overhead. To reduce
this overhead, the Symfony2 standard edition provides a script to generate
a so called `bootstrap file`_, consisting of multiple classes definitions
in a single file. This will reduce disc IO quite a bit.

Note there are two disadvantages when using such a bootstrap file:

* the file needs to be regenerated whenever any of the original sources change
  (i.e. when you update the Symfony2 source or vendor libraries);

* when debugging, one will need to place break points inside the bootstrap file.

Bootstrap Files and Byte Code Caches
------------------------------------

Even when using a byte code cache, performance will improve when using a bootstrap
file since there will be less files to monitor for changes. Of course if this
feature is disabled in the byte code cache (e.g. ``apc.stat=0`` in APC), there
is no longer a reason to use a bootstrap file.

.. _`bootstrap file`: https://github.com/sensio/SensioDistributionBundle/blob/master/Resources/bin/build_bootstrap.php