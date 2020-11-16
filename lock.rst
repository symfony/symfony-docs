.. index::
    single: Lock

Dealing with Concurrency with Locks
===================================

When a program runs concurrently, some part of code which modify shared
resources should not be accessed by multiple processes at the same time.
Symfony's :doc:`Lock component </components/lock>` provides a locking mechanism to ensure
that only one process is running the critical section of code at any point of
time to prevent race condition from happening.

The following example shows a typical usage of the lock::

    $lock = $lockFactory->createLock('pdf-invoice-generation');
    if (!$lock->acquire()) {
        return;
    }

    // critical section of code
    $service->method();

    $lock->release();

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the Lock component:

.. code-block:: terminal

    $ composer require symfony/lock

Configuring Lock with FrameworkBundle
-------------------------------------

By default, Symfony provides a :ref:`Semaphore <lock-store-semaphore>`
when available, or a :ref:`Flock <lock-store-flock>` otherwise. You can configure
this behavior by using the ``lock`` key like:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/lock.yaml
        framework:
            lock: ~
            lock: 'flock'
            lock: 'flock:///path/to/file'
            lock: 'semaphore'
            lock: 'memcached://m1.docker'
            lock: ['memcached://m1.docker', 'memcached://m2.docker']
            lock: 'redis://r1.docker'
            lock: ['redis://r1.docker', 'redis://r2.docker']
            lock: 'zookeeper://z1.docker'
            lock: 'zookeeper://z1.docker,z2.docker'
            lock: 'sqlite:///%kernel.project_dir%/var/lock.db'
            lock: 'mysql:host=127.0.0.1;dbname=lock'
            lock: 'pgsql:host=127.0.0.1;dbname=lock'
            lock: 'sqlsrv:server=localhost;Database=test'
            lock: 'oci:host=localhost;dbname=test'
            lock: '%env(LOCK_DSN)%'

            # named locks
            lock:
                invoice: ['semaphore', 'redis://r2.docker']
                report: 'semaphore'

    .. code-block:: xml

        <!-- config/packages/lock.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:lock>
                    <framework:resource>flock</framework:resource>

                    <framework:resource>flock:///path/to/file</framework:resource>

                    <framework:resource>semaphore</framework:resource>

                    <framework:resource>memcached://m1.docker</framework:resource>

                    <framework:resource>memcached://m1.docker</framework:resource>
                    <framework:resource>memcached://m2.docker</framework:resource>

                    <framework:resource>redis://r1.docker</framework:resource>

                    <framework:resource>redis://r1.docker</framework:resource>
                    <framework:resource>redis://r2.docker</framework:resource>

                    <framework:resource>zookeeper://z1.docker</framework:resource>

                    <framework:resource>zookeeper://z1.docker,z2.docker</framework:resource>

                    <framework:resource>sqlite:///%kernel.project_dir%/var/lock.db</framework:resource>

                    <framework:resource>mysql:host=127.0.0.1;dbname=lock</framework:resource>

                    <framework:resource>pgsql:host=127.0.0.1;dbname=lock</framework:resource>

                    <framework:resource>sqlsrv:server=localhost;Database=test</framework:resource>

                    <framework:resource>oci:host=localhost;dbname=test</framework:resource>

                    <framework:resource>%env(LOCK_DSN)%</framework:resource>

                    <!-- named locks -->
                    <framework:resource name="invoice">semaphore</framework:resource>
                    <framework:resource name="invoice">redis://r2.docker</framework:resource>
                    <framework:resource name="report">semaphore</framework:resource>
                </framework:lock>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/lock.php
        $container->loadFromExtension('framework', [
            'lock' => null,
            'lock' => 'flock',
            'lock' => 'flock:///path/to/file',
            'lock' => 'semaphore',
            'lock' => 'memcached://m1.docker',
            'lock' => ['memcached://m1.docker', 'memcached://m2.docker'],
            'lock' => 'redis://r1.docker',
            'lock' => ['redis://r1.docker', 'redis://r2.docker'],
            'lock' => 'zookeeper://z1.docker',
            'lock' => 'zookeeper://z1.docker,z2.docker',
            'lock' => 'sqlite:///%kernel.project_dir%/var/lock.db',
            'lock' => 'mysql:host=127.0.0.1;dbname=lock',
            'lock' => 'pgsql:host=127.0.0.1;dbname=lock',
            'lock' => 'sqlsrv:server=localhost;Database=test',
            'lock' => 'oci:host=localhost;dbname=test',
            'lock' => '%env(LOCK_DSN)%',

            // named locks
            'lock' => [
                'invoice' => ['semaphore', 'redis://r2.docker'],
                'report' => 'semaphore',
            ],
        ]);

Locking a Resource
------------------

To lock the default resource, autowire the lock using
:class:`Symfony\\Component\\Lock\\LockInterface` (service id ``lock``)::

    // src/Controller/PdfController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Lock\LockInterface;

    class PdfController extends AbstractController
    {
        /**
         * @Route("/download/terms-of-use.pdf")
         */
        public function downloadPdf(LockInterface $lock, MyPdfGeneratorService $pdf)
        {
            $lock->acquire(true);

            // heavy computation
            $myPdf = $pdf->getOrCreatePdf();

            $lock->release();

            // ...
        }
    }

.. caution::

    The same instance of ``LockInterface`` won't block when calling ``acquire``
    multiple times inside the same process. When several services use the
    same lock, inject the ``LockFactory`` instead to create a separate lock
    instance for each service.

Locking a Dynamic Resource
--------------------------

Sometimes the application is able to cut the resource into small pieces in order
to lock a small subset of process and let other through. In our previous example
with see how to lock the ``$pdf->getOrCreatePdf('terms-of-use')`` for everybody,
now let's see how to lock ``$pdf->getOrCreatePdf($version)`` only for
processes asking for the same ``$version``::

    // src/Controller/PdfController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Lock\LockFactory;

    class PdfController extends AbstractController
    {
        /**
         * @Route("/download/{version}/terms-of-use.pdf")
         */
        public function downloadPdf($version, LockFactory $lockFactory, MyPdfGeneratorService $pdf)
        {
            $lock = $lockFactory->createLock($version);
            $lock->acquire(true);

            // heavy computation
            $myPdf = $pdf->getOrCreatePdf($version);

            $lock->release();

            // ...
        }
    }

Named Lock
----------

If the application needs different kind of Stores alongside each other, Symfony
provides :ref:`named lock <reference-lock-resources-name>`:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/lock.yaml
        framework:
            lock:
                invoice: ['semaphore', 'redis://r2.docker']
                report: 'semaphore'

    .. code-block:: xml

        <!-- config/packages/lock.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:lock>
                    <framework:resource name="invoice">semaphore</framework:resource>
                    <framework:resource name="invoice">redis://r2.docker</framework:resource>
                    <framework:resource name="report">semaphore</framework:resource>
                </framework:lock>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/lock.php
        $container->loadFromExtension('framework', [
            'lock' => [
                'invoice' => ['semaphore', 'redis://r2.docker'],
                'report' => 'semaphore',
            ],
        ]);

Each name becomes a service where the service id suffixed by the name of the
lock (e.g. ``lock.invoice``). An autowiring alias is also created for each lock
using the camel case version of its name suffixed by ``Lock`` - e.g. ``invoice``
can be injected automatically by naming the argument ``$invoiceLock`` and
type-hinting it with :class:`Symfony\\Component\\Lock\\LockInterface`.

Symfony also provide a corresponding factory and store following the same rules
(e.g. ``invoice`` generates a ``lock.invoice.factory`` and
``lock.invoice.store``, both can be injected automatically by naming
respectively ``$invoiceLockFactory`` and ``$invoiceLockStore`` and type-hinted
with :class:`Symfony\\Component\\Lock\\LockFactory` and
:class:`Symfony\\Component\\Lock\\PersistingStoreInterface`)

Blocking Store
--------------

If you want to use the ``RetryTillSaveStore`` for :ref:`non-blocking locks <lock-blocking-locks>`,
you can do it by :doc:`decorating the store </service_container/service_decoration>` service:

.. code-block:: yaml

    lock.default.retry_till_save.store:
        class: Symfony\Component\Lock\Store\RetryTillSaveStore
        decorates: lock.default.store
        arguments: ['@lock.default.retry_till_save.store.inner', 100, 50]
