.. index::
   single: Asset
   single: Components; Asset

The Asset Component
===================

   The Asset component manages URL generation and versioning of web assets such
   as CSS stylesheets, JavaScript files and image files.

In the past, it was common for web applications to hardcode URLs of web assets.
For example:

.. code-block:: html

    <link rel="stylesheet" type="text/css" href="/css/main.css">

    <!-- ... -->

    <a href="/"><img src="/images/logo.png"></a>

This practice is no longer recommended unless the web application is extremely
simple. Hardcoding URLs can be a disadvantage because:

* **Templates get verbose**: you have to write the full path for each
  asset. When using the Asset component, you can group assets in packages to
  avoid repeating the common part of their path;
* **Versioning is difficult**: it has to be custom managed for each
  application. Adding a version (e.g. ``main.css?v=5``) to the asset URLs
  is essential for some applications because it allows you to control how
  the assets are cached. The Asset component allows you to define different
  versioning strategies for each package;
* **Moving assets location** is cumbersome and error-prone: it requires you to
  carefully update the URLs of all assets included in all templates. The Asset
  component allows to move assets effortlessly just by changing the base path
  value associated with the package of assets;
* **It's nearly impossible to use multiple CDNs**: this technique requires
  you to change the URL of the asset randomly for each request. The Asset component
  provides out-of-the-box support for any number of multiple CDNs, both regular
  (``http://``) and secure (``https://``).

Installation
------------

You can install the component in two different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/asset`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/asset).

Usage
-----

Asset Packages
~~~~~~~~~~~~~~

The Asset component manages assets through packages. A package groups all the
assets which share the same properties: versioning strategy, base path, CDN hosts,
etc. In the following basic example, a package is created to manage assets without
any versioning::

    use Symfony\Component\Asset\Package;
    use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

    $package = new Package(new EmptyVersionStrategy());

    echo $package->getUrl('/image.png');
    // result: /image.png

Packages implement :class:`Symfony\\Component\\Asset\\PackageInterface`,
which defines the following two methods:

:method:`Symfony\\Component\\Asset\\PackageInterface::getVersion`
    Returns the asset version for an asset.

:method:`Symfony\\Component\\Asset\\PackageInterface::getUrl`
    Returns an absolute or root-relative public path.

With a package, you can:

A) :ref:`version the assets <component-assets-versioning>`;
B) set a :ref:`common base path <component-assets-path-package>` (e.g. ``/css``)
   for the assets;
C) :ref:`configure a CDN <component-assets-cdn>` for the assets

.. _component-assets-versioning:

Versioned Assets
~~~~~~~~~~~~~~~~

One of the main features of the Asset component is the ability to manage
the versioning of the application's assets. Asset versions are commonly used
to control how these assets are cached.

Instead of relying on a simple version mechanism, the Asset component allows
you to define advanced versioning strategies via PHP classes. The two built-in
strategies are the :class:`Symfony\\Component\\Asset\\VersionStrategy\\EmptyVersionStrategy`,
which doesn't add any version to the asset and :class:`Symfony\\Component\\Asset\\VersionStrategy\\StaticVersionStrategy`,
which allows you to set the version with a format string.

In this example, the ``StaticVersionStrategy`` is used to append the ``v1``
suffix to any asset path::

    use Symfony\Component\Asset\Package;
    use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;

    $package = new Package(new StaticVersionStrategy('v1'));

    echo $package->getUrl('/image.png');
    // result: /image.png?v1

In case you want to modify the version format, pass a sprintf-compatible format
string as the second argument of the ``StaticVersionStrategy`` constructor::

    // put the 'version' word before the version value
    $package = new Package(new StaticVersionStrategy('v1', '%s?version=%s'));

    echo $package->getUrl('/image.png');
    // result: /image.png?version=v1

    // put the asset version before its path
    $package = new Package(new StaticVersionStrategy('v1', '%2$s/%1$s'));

    echo $package->getUrl('/image.png');
    // result: /v1/image.png

Custom Version Strategies
.........................

Use the :class:`Symfony\\Component\\Asset\\VersionStrategy\\VersionStrategyInterface`
to define your own versioning strategy. For example, your application may need
to append the current date to all its web assets in order to bust the cache
every day::

    use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

    class DateVersionStrategy implements VersionStrategyInterface
    {
        private $version;

        public function __construct()
        {
            $this->version = date('Ymd');
        }

        public function getVersion($path)
        {
            return $this->version;
        }

        public function applyVersion($path)
        {
            return sprintf('%s?v=%s', $path, $this->getVersion($path));
        }
    }

.. _component-assets-path-package:

Grouped Assets
~~~~~~~~~~~~~~

Often, many assets live under a common path (e.g. ``/static/images``). If
that's your case, replace the default :class:`Symfony\\Component\\Asset\\Package`
class with :class:`Symfony\\Component\\Asset\\PathPackage` to avoid repeating
that path over and over again::

    use Symfony\Component\Asset\PathPackage;
    // ...

    $package = new PathPackage('/static/images', new StaticVersionStrategy('v1'));

    echo $package->getUrl('/logo.png');
    // result: /static/images/logo.png?v1

Request Context Aware Assets
............................

If you are also using the :doc:`HttpFoundation </components/http_foundation/introduction>`
component in your project (for instance, in a Symfony application), the ``PathPackage``
class can take into account the context of the current request::

    use Symfony\Component\Asset\PathPackage;
    use Symfony\Component\Asset\Context\RequestStackContext;
    // ...

    $package = new PathPackage(
        '/static/images',
        new StaticVersionStrategy('v1'),
        new RequestStackContext($requestStack)
    );

    echo $package->getUrl('/logo.png');
    // result: /somewhere/static/images/logo.png?v1

Now that the request context is set, the ``PathPackage`` will prepend the
current request base URL. So, for example, if your entire site is hosted under
the ``/somewhere`` directory of your web server root directory and the configured
base path is ``/static/images``, all paths will be prefixed with
``/somewhere/static/images``.

.. _component-assets-cdn:

Absolute Assets and CDNs
~~~~~~~~~~~~~~~~~~~~~~~~

Applications that host their assets on different domains and CDNs (*Content
Delivery Networks*) should use the :class:`Symfony\\Component\\Asset\\UrlPackage`
class to generate absolute URLs for their assets::

    use Symfony\Component\Asset\UrlPackage;
    // ...

    $package = new UrlPackage(
        'http://static.example.com/images/',
        new StaticVersionStrategy('v1')
    );

    echo $package->getUrl('/logo.png');
    // result: http://static.example.com/images/logo.png?v1

You can also pass a schema-agnostic URL::

    use Symfony\Component\Asset\UrlPackage;
    // ...

    $package = new UrlPackage(
        '//static.example.com/images/',
        new StaticVersionStrategy('v1')
    );

    echo $package->getUrl('/logo.png');
    // result: //static.example.com/images/logo.png?v1

This is useful because assets will automatically be requested via HTTPS if
a visitor is viewing your site in https. Just make sure that your CDN host
supports https.

In case you serve assets from more than one domain to improve application
performance, pass an array of URLs as the first argument to the ``UrlPackage``
constructor::

    use Symfony\Component\Asset\UrlPackage;
    // ...

    $urls = array(
        '//static1.example.com/images/',
        '//static2.example.com/images/',
    );
    $package = new UrlPackage($urls, new StaticVersionStrategy('v1'));

    echo $package->getUrl('/logo.png');
    // result: http://static1.example.com/images/logo.png?v1
    echo $package->getUrl('/icon.png');
    // result: http://static2.example.com/images/icon.png?v1

For each asset, one of the URLs will be randomly used. But, the selection
is deterministic, meaning that each asset will be always served by the same
domain. This behavior simplifies the management of HTTP cache.

Request Context Aware Assets
............................

Similarly to application-relative assets, absolute assets can also take into
account the context of the current request. In this case, only the request
scheme is considered, in order to select the appropriate base URL (HTTPs or
protocol-relative URLs for HTTPs requests, any base URL for HTTP requests)::

    use Symfony\Component\Asset\UrlPackage;
    use Symfony\Component\Asset\Context\RequestStackContext;
    // ...

    $package = new UrlPackage(
        array('http://example.com/', 'https://example.com/'),
        new StaticVersionStrategy('v1'),
        new RequestStackContext($requestStack)
    );

    echo $package->getUrl('/logo.png');
    // assuming the RequestStackContext says that we are on a secure host
    // result: https://example.com/logo.png?v1

Named Packages
~~~~~~~~~~~~~~

Applications that manage lots of different assets may need to group them in
packages with the same versioning strategy and base path. The Asset component
includes a :class:`Symfony\\Component\\Asset\\Packages` class to simplify
management of several packages.

In the following example, all packages use the same versioning strategy, but
they all have different base paths::

    use Symfony\Component\Asset\Package;
    use Symfony\Component\Asset\PathPackage;
    use Symfony\Component\Asset\UrlPackage;
    use Symfony\Component\Asset\Packages;
    // ...

    $versionStrategy = new StaticVersionStrategy('v1');

    $defaultPackage = new Package($versionStrategy);

    $namedPackages = array(
        'img' => new UrlPackage('http://img.example.com/', $versionStrategy),
        'doc' => new PathPackage('/somewhere/deep/for/documents', $versionStrategy),
    );

    $packages = new Packages($defaultPackage, $namedPackages)

The ``Packages`` class allows to define a default package, which will be applied
to assets that don't define the name of package to use. In addition, this
application defines a package named ``img`` to serve images from an external
domain and a ``doc`` package to avoid repeating long paths when linking to a
document inside a template::

    echo $packages->getUrl('/main.css');
    // result: /main.css?v1

    echo $packages->getUrl('/logo.png', 'img');
    // result: http://img.example.com/logo.png?v1

    echo $packages->getUrl('/resume.pdf', 'doc');
    // result: /somewhere/deep/for/documents/resume.pdf?v1

.. _Packagist: https://packagist.org/packages/symfony/asset
