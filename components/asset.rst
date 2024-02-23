The Asset Component
===================

    The Asset component manages URL generation and versioning of web assets such
    as CSS stylesheets, JavaScript files and image files.

In the past, it was common for web applications to hard-code the URLs of web assets.
For example:

.. code-block:: html

    <link rel="stylesheet" type="text/css" href="/css/main.css">

    <!-- ... -->

    <a href="/"><img src="/images/logo.png" alt="logo"></a>

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
* **Moving assets' location** is cumbersome and error-prone: it requires you to
  carefully update the URLs of all assets included in all templates. The Asset
  component allows to move assets effortlessly just by changing the base path
  value associated with the package of assets;
* **It's nearly impossible to use multiple CDNs**: this technique requires
  you to change the URL of the asset randomly for each request. The Asset component
  provides out-of-the-box support for any number of multiple CDNs, both regular
  (``http://``) and secure (``https://``).

Installation
------------

.. code-block:: terminal

    $ composer require symfony/asset

.. include:: /components/require_autoload.rst.inc

Usage
-----

.. _asset-packages:

Asset Packages
~~~~~~~~~~~~~~

The Asset component manages assets through packages. A package groups all the
assets which share the same properties: versioning strategy, base path, CDN hosts,
etc. In the following basic example, a package is created to manage assets without
any versioning::

    use Symfony\Component\Asset\Package;
    use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

    $package = new Package(new EmptyVersionStrategy());

    // Absolute path
    echo $package->getUrl('/image.png');
    // result: /image.png

    // Relative path
    echo $package->getUrl('image.png');
    // result: image.png

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

    // Absolute path
    echo $package->getUrl('/image.png');
    // result: /image.png?v1

    // Relative path
    echo $package->getUrl('image.png');
    // result: image.png?v1

In case you want to modify the version format, pass a ``sprintf``-compatible
format string as the second argument of the ``StaticVersionStrategy``
constructor::

    // puts the 'version' word before the version value
    $package = new Package(new StaticVersionStrategy('v1', '%s?version=%s'));

    echo $package->getUrl('/image.png');
    // result: /image.png?version=v1

    // puts the asset version before its path
    $package = new Package(new StaticVersionStrategy('v1', '%2$s/%1$s'));

    echo $package->getUrl('/image.png');
    // result: /v1/image.png

    echo $package->getUrl('image.png');
    // result: v1/image.png

JSON File Manifest
..................

A popular strategy to manage asset versioning, which is used by tools such as
`Webpack`_, is to generate a JSON file mapping all source file names to their
corresponding output file:

.. code-block:: json

    {
        "css/app.css": "build/css/app.b916426ea1d10021f3f17ce8031f93c2.css",
        "js/app.js": "build/js/app.13630905267b809161e71d0f8a0c017b.js",
        "...": "..."
    }

In those cases, use the
:class:`Symfony\\Component\\Asset\\VersionStrategy\\JsonManifestVersionStrategy`::

    use Symfony\Component\Asset\Package;
    use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;

    // assumes the JSON file above is called "rev-manifest.json"
    $package = new Package(new JsonManifestVersionStrategy(__DIR__.'/rev-manifest.json'));

    echo $package->getUrl('css/app.css');
    // result: build/css/app.b916426ea1d10021f3f17ce8031f93c2.css

If you request an asset that is *not found* in the ``rev-manifest.json`` file,
the original - *unmodified* - asset path will be returned. The ``$strictMode``
argument helps debug issues because it throws an exception when the asset is not
listed in the manifest::

    use Symfony\Component\Asset\Package;
    use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;

    // The value of $strictMode can be specific per environment "true" for debugging and "false" for stability.
    $strictMode = true;
    // assumes the JSON file above is called "rev-manifest.json"
    $package = new Package(new JsonManifestVersionStrategy(__DIR__.'/rev-manifest.json', null, $strictMode));

    echo $package->getUrl('not-found.css');
    // error:

If your JSON file is not on your local filesystem but is accessible over HTTP,
use the :class:`Symfony\\Component\\Asset\\VersionStrategy\\JsonManifestVersionStrategy`
with the :doc:`HttpClient component </http_client>`::

    use Symfony\Component\Asset\Package;
    use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
    use Symfony\Component\HttpClient\HttpClient;

    $httpClient = HttpClient::create();
    $manifestUrl = 'https://cdn.example.com/rev-manifest.json';
    $package = new Package(new JsonManifestVersionStrategy($manifestUrl, $httpClient));

Custom Version Strategies
.........................

Use the :class:`Symfony\\Component\\Asset\\VersionStrategy\\VersionStrategyInterface`
to define your own versioning strategy. For example, your application may need
to append the current date to all its web assets in order to bust the cache
every day::

    use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

    class DateVersionStrategy implements VersionStrategyInterface
    {
        private string $version;

        public function __construct()
        {
            $this->version = date('Ymd');
        }

        public function getVersion(string $path): string
        {
            return $this->version;
        }

        public function applyVersion(string $path): string
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

    $pathPackage = new PathPackage('/static/images', new StaticVersionStrategy('v1'));

    echo $pathPackage->getUrl('logo.png');
    // result: /static/images/logo.png?v1

    // Base path is ignored when using absolute paths
    echo $pathPackage->getUrl('/logo.png');
    // result: /logo.png?v1

Request Context Aware Assets
............................

If you are also using the :doc:`HttpFoundation </components/http_foundation>`
component in your project (for instance, in a Symfony application), the ``PathPackage``
class can take into account the context of the current request::

    use Symfony\Component\Asset\Context\RequestStackContext;
    use Symfony\Component\Asset\PathPackage;
    // ...

    $pathPackage = new PathPackage(
        '/static/images',
        new StaticVersionStrategy('v1'),
        new RequestStackContext($requestStack)
    );

    echo $pathPackage->getUrl('logo.png');
    // result: /somewhere/static/images/logo.png?v1

    // Both "base path" and "base url" are ignored when using absolute path for asset
    echo $pathPackage->getUrl('/logo.png');
    // result: /logo.png?v1

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

    $urlPackage = new UrlPackage(
        'https://static.example.com/images/',
        new StaticVersionStrategy('v1')
    );

    echo $urlPackage->getUrl('/logo.png');
    // result: https://static.example.com/images/logo.png?v1

You can also pass a schema-agnostic URL::

    use Symfony\Component\Asset\UrlPackage;
    // ...

    $urlPackage = new UrlPackage(
        '//static.example.com/images/',
        new StaticVersionStrategy('v1')
    );

    echo $urlPackage->getUrl('/logo.png');
    // result: //static.example.com/images/logo.png?v1

This is useful because assets will automatically be requested via HTTPS if
a visitor is viewing your site in https. If you want to use this, make sure
that your CDN host supports HTTPS.

In case you serve assets from more than one domain to improve application
performance, pass an array of URLs as the first argument to the ``UrlPackage``
constructor::

    use Symfony\Component\Asset\UrlPackage;
    // ...

    $urls = [
        'https://static1.example.com/images/',
        'https://static2.example.com/images/',
    ];
    $urlPackage = new UrlPackage($urls, new StaticVersionStrategy('v1'));

    echo $urlPackage->getUrl('/logo.png');
    // result: https://static1.example.com/images/logo.png?v1
    echo $urlPackage->getUrl('/icon.png');
    // result: https://static2.example.com/images/icon.png?v1

For each asset, one of the URLs will be randomly used. But, the selection
is deterministic, meaning that each asset will always be served by the same
domain. This behavior simplifies the management of HTTP cache.

Request Context Aware Assets
............................

Similarly to application-relative assets, absolute assets can also take into
account the context of the current request. In this case, only the request
scheme is considered, in order to select the appropriate base URL (HTTPs or
protocol-relative URLs for HTTPs requests, any base URL for HTTP requests)::

    use Symfony\Component\Asset\Context\RequestStackContext;
    use Symfony\Component\Asset\UrlPackage;
    // ...

    $urlPackage = new UrlPackage(
        ['http://example.com/', 'https://example.com/'],
        new StaticVersionStrategy('v1'),
        new RequestStackContext($requestStack)
    );

    echo $urlPackage->getUrl('/logo.png');
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
    use Symfony\Component\Asset\Packages;
    use Symfony\Component\Asset\PathPackage;
    use Symfony\Component\Asset\UrlPackage;
    // ...

    $versionStrategy = new StaticVersionStrategy('v1');

    $defaultPackage = new Package($versionStrategy);

    $namedPackages = [
        'img' => new UrlPackage('https://img.example.com/', $versionStrategy),
        'doc' => new PathPackage('/somewhere/deep/for/documents', $versionStrategy),
    ];

    $packages = new Packages($defaultPackage, $namedPackages);

The ``Packages`` class allows to define a default package, which will be applied
to assets that don't define the name of the package to use. In addition, this
application defines a package named ``img`` to serve images from an external
domain and a ``doc`` package to avoid repeating long paths when linking to a
document inside a template::

    echo $packages->getUrl('/main.css');
    // result: /main.css?v1

    echo $packages->getUrl('/logo.png', 'img');
    // result: https://img.example.com/logo.png?v1

    echo $packages->getUrl('resume.pdf', 'doc');
    // result: /somewhere/deep/for/documents/resume.pdf?v1

Local Files and Other Protocols
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to HTTP this component supports other protocols (such as ``file://``
and ``ftp://``). This allows for example to serve local files in order to
improve performance::

    use Symfony\Component\Asset\UrlPackage;
    // ...

    $localPackage = new UrlPackage(
        'file:///path/to/images/',
        new EmptyVersionStrategy()
    );

    $ftpPackage = new UrlPackage(
        'ftp://example.com/images/',
        new EmptyVersionStrategy()
    );

    echo $localPackage->getUrl('/logo.png');
    // result: file:///path/to/images/logo.png

    echo $ftpPackage->getUrl('/logo.png');
    // result: ftp://example.com/images/logo.png

Learn more
----------

* :doc:`How to manage CSS and JavaScript assets in Symfony applications </frontend>`
* :doc:`WebLink component </web_link>` to preload assets using HTTP/2.

.. _`Webpack`: https://webpack.js.org/
