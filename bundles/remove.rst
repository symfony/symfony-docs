.. index::
    single: Bundle; Removing a bundle

How to Remove a Bundle
======================

.. _unregister-the-bundle-in-the-appkernel:
.. _remove-bundle-configuration:
.. _remove-bundle-routing:
.. _remove-the-bundle-from-the-filesystem:

1. Uninstall the Bundle
-----------------------

Execute this command to remove the Composer package associated with the bundle:

.. code-block:: terminal

    $ composer remove <package-name>

    # the package name is the same used when you installed it. Example:
    # composer remove friendsofsymfony/user-bundle

If your application uses :doc:`Symfony Flex </setup/flex>`, this command will
also unregister the bundle from the application and remove its configuration
files, environment variables defined by the bundle, etc. Otherwise, you need to
remove all that manually:

* Unregister the bundle in the application removing it from the
  ``config/bundles.php`` file;
* Remove any configuration file related to the bundle from the ``config/packages/``
  directories (the files will be called like the bundle; e.g. ``fos_user.yaml``
  for FOSUserBundle);
* Remove any routes file related to the bundle from the ``config/routes/``
  directories (the file names will also match the bundle name; e.g.
  ``config/routes/fos_js_routing.yaml`` for FOSJsRoutingBundle).

2. Remove Bundle Assets
-----------------------

If the bundle included web assets, remove them from the ``public/bundles/``
directory (e.g. ``public/bundles/nelmioapidoc/`` for the NelmioApiDocBundle).

3. Remove Integration in other Bundles
--------------------------------------

Some bundles rely on other bundles; if you remove one of the two, the other will
probably not work. In most cases, when one bundle relies on another, it means
that it uses some services from it.

Look for the bundle alias or PHP namespace (e.g. ``sonata.admin`` or
``Sonata\AdminBundle\`` for bundles depending on SonataAdminBundle) to find the
dependent bundles.

.. tip::

    If a third party bundle relies on another bundle, you can find that bundle
    mentioned in the ``composer.json`` file included in the bundle directory.
