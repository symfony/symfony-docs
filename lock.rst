Dealing with Concurrency with Locks
===================================

When a program runs concurrently, some parts of code that modify shared
resources should not be accessed by multiple processes at the same time.
Symfony's :doc:`Lock component </components/lock>` provides a locking mechanism to ensure
that only one process is running the critical section of code at any point in
time to prevent race conditions from happening.

The following example shows a typical usage of the lock::

    $lock = $lockFactory->createLock('pdf-creation');
    if (!$lock->acquire()) {
        return;
    }

    // critical section of code
    $service->method();

    $lock->release();

Installing
----------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the Lock component:

.. code-block:: terminal

    $ composer require symfony/lock

Configuring
-----------

By default, Symfony provides a :ref:`Semaphore <lock-store-semaphore>`
when available, or a :ref:`Flock <lock-store-flock>` otherwise. You can configure
this behavior by using the ``lock`` key as follows:

.. note::

    Since Symfony 8.1, the ``flock`` and ``semaphore`` stores are automatically
    scoped by ``kernel.project_id``, preventing lock collisions between multiple
    applications running on the same server.

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
            lock: 'rediss://r1.docker?ssl[verify_peer]=1&ssl[cafile]=...'
            lock: 'zookeeper://z1.docker'
            lock: 'zookeeper://z1.docker,z2.docker'
            lock: 'zookeeper://localhost01,localhost02:2181'
            lock: 'sqlite:///%kernel.project_dir%/var/lock.db'
            lock: 'mysql:host=127.0.0.1;dbname=app'
            lock: 'pgsql:host=127.0.0.1;dbname=app'
            lock: 'pgsql+advisory:host=127.0.0.1;dbname=app'
            lock: 'sqlsrv:server=127.0.0.1;Database=app'
            lock: 'oci:host=127.0.0.1;dbname=app'
            lock: 'mongodb://127.0.0.1/app?collection=lock'
            lock: 'dynamodb://127.0.0.1/lock'
            lock: '%env(LOCK_DSN)%'
            # using an existing service
            lock: 'snc_redis.default'

            # named locks
            lock:
                invoice: ['semaphore', 'redis://r2.docker']
                report: 'semaphore'

    .. code-block:: php

        // config/packages/lock.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'lock' => null,
                'lock' => 'flock',
                'lock' => 'flock:///path/to/file',
                'lock' => 'semaphore',
                'lock' => 'memcached://m1.docker',
                'lock' => 'memcached://m1.docker,memcached://m2.docker',
                'lock' => 'redis://r1.docker',
                'lock' => 'redis://r1.docker,redis://r2.docker',
                'lock' => 'rediss://r1.docker?ssl[verify_peer]=1&ssl[cafile]=...',
                'lock' => 'zookeeper://z1.docker',
                'lock' => 'zookeeper://z1.docker,z2.docker',
                'lock' => 'zookeeper://localhost01,localhost02:2181',
                'lock' => 'sqlite:///%kernel.project_dir%/var/lock.db',
                'lock' => 'mysql:host=127.0.0.1;dbname=app',
                'lock' => 'pgsql:host=127.0.0.1;dbname=app',
                'lock' => 'pgsql+advisory:host=127.0.0.1;dbname=app',
                'lock' => 'sqlsrv:server=127.0.0.1;Database=app',
                'lock' => 'oci:host=127.0.0.1;dbname=app',
                'lock' => 'mongodb://127.0.0.1/app?collection=lock',
                'lock' => 'dynamodb://127.0.0.1/lock',
                'lock' => env('LOCK_DSN'),
                // using an existing service
                'lock' => 'snc_redis.default',
                // named locks
                'lock' => [
                    'invoice' => ['semaphore', 'redis://r2.docker'],
                    'report' => 'semaphore',
                ],
            ],
        ]);

Locking a Resource
------------------

To lock the default resource, autowire the lock factory using
:class:`Symfony\\Component\\Lock\\LockFactory`::

    // src/Controller/PdfController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Lock\LockFactory;

    class PdfController extends AbstractController
    {
        #[Route('/download/terms-of-use.pdf')]
        public function downloadPdf(LockFactory $factory, MyPdfGeneratorService $pdf): Response
        {
            $lock = $factory->createLock('pdf-creation');
            $lock->acquire(true);

            // heavy computation
            $myPdf = $pdf->getOrCreatePdf();

            $lock->release();

            // ...
        }
    }

.. warning::

    The same instance of ``LockInterface`` won't block when calling ``acquire``
    multiple times inside the same process. When several services use the
    same lock, inject the ``LockFactory`` instead to create a separate lock
    instance for each service.

Locking a Dynamic Resource
--------------------------

Sometimes the application is able to cut the resource into small pieces in order
to lock a small subset of processes and let others through. The previous example
showed how to lock the ``$pdf->getOrCreatePdf()`` call for everybody,
now let's see how to lock a ``$pdf->getOrCreatePdf($version)`` call only for
processes asking for the same ``$version``::

    // src/Controller/PdfController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Lock\LockFactory;

    class PdfController extends AbstractController
    {
        #[Route('/download/{version}/terms-of-use.pdf')]
        public function downloadPdf($version, LockFactory $lockFactory, MyPdfGeneratorService $pdf): Response
        {
            $lock = $lockFactory->createLock('pdf-creation-'.$version);
            $lock->acquire(true);

            // heavy computation
            $myPdf = $pdf->getOrCreatePdf($version);

            $lock->release();

            // ...
        }
    }

.. _lock-named-locks:

Naming Locks
------------

If the application needs different kinds of stores alongside each other, Symfony
provides :ref:`named locks <reference-lock-resources-name>`:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/lock.yaml
        framework:
            lock:
                invoice: ['semaphore', 'redis://r2.docker']
                report: 'semaphore'

    .. code-block:: php

        // config/packages/lock.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'lock' => [
                    'invoice' => ['semaphore', 'redis://r2.docker'],
                    'report' => 'semaphore',
                ],
            ],
        ]);

After having configured one or more named locks, use the ``#[Target]``
attribute to inject a specific lock factory in any service or controller.
Symfony creates a target with the same name as the lock.

For example, to inject the ``invoice`` lock defined earlier::

    use Symfony\Component\DependencyInjection\Attribute\Target;
    use Symfony\Component\Lock\LockFactory;

    class SomeService
    {
        public function __construct(
            #[Target('invoice')] private LockFactory $lockFactory
        ) {
            // ...
        }
    }
