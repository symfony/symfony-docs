.. index::
   single: Bundles

.. _page-creation-bundles:

The Bundle System
=================

.. caution::

    In Symfony versions prior to 4.0, it was recommended to organize your own
    application code using bundles. This is no longer recommended and bundles
    should only be used to share code and features between multiple applications.

A bundle is similar to a plugin in other software, but even better. The core
features of Symfony framework are implemented with bundles (FrameworkBundle,
SecurityBundle, DebugBundle, etc.) They are also used to add new features in
your application via `third-party bundles`_.

Bundles used in your applications must be enabled per
:doc:`environment </configuration/environments>` in the ``config/bundles.php``
file::

    // config/bundles.php
    return [
        // 'all' means that the bundle is enabled for any Symfony environment
        Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
        Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
        Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
        Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
        Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class => ['all' => true],
        Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
        Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
        // this bundle is enabled only in 'dev'  and 'test', so you can't use it in 'prod'
        Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    ];

.. tip::

   In a default Symfony application that uses :doc:`Symfony Flex </setup/flex>`,
   bundles are enabled/disabled automatically for you when installing/removing
   them, so you don't need to look at or edit this ``bundles.php`` file.

Learn more
----------

* :doc:`/bundles/override`
* :doc:`/bundles/best_practices`
* :doc:`/bundles/configuration`
* :doc:`/bundles/extension`
* :doc:`/bundles/prepend_extension`

.. _`third-party bundles`: https://github.com/search?q=topic%3Asymfony-bundle&type=Repositories
