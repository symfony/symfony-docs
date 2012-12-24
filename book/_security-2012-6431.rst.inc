.. note::

    Since Symfony 2.0.20/2.1.5, the Twig ``render`` tag now takes an absolute url
    instead of a controller logical path. This fixes an important security
    issue (`CVE-2012-6431`_) reported on the official blog. If your application
    uses an older version of Symfony or still uses the previous ``render`` tag
    syntax, you should upgrade as soon as possible.

.. _`CVE-2012-6431`: http://symfony.com/blog/security-release-symfony-2-0-20-and-2-1-5-released