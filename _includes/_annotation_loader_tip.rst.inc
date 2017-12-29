.. note::

    In order to use the annotation loader, you should have installed the
    ``doctrine/annotations`` and ``doctrine/cache`` packages with Composer.

.. tip::

    Annotation classes aren't loaded automatically, so you must load them
    like Symfony does in the ``app/autoload.php`` file::

        use Composer\Autoload\ClassLoader;
        use Doctrine\Common\Annotations\AnnotationRegistry;

        /** @var ClassLoader $loader */
        $loader = require __DIR__.'/../vendor/autoload.php';

        AnnotationRegistry::registerLoader([$loader, 'loadClass']);

        return $loader;
