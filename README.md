<p align="center"><a href="https://symfony.com" target="_blank">
  <img src="https://symfony.com/logos/symfony_black_02.svg">
</a></p>

<h3 align="center">
  The official Symfony Documentation
</h3>

<p align="center">
  <a href="https://symfony.com/doc/current/index.html">
    Online version
  </a>
  <span> | </span>
  <a href="https://symfony.com/components">
    Components
  </a>
  <span> | </span>
  <a href="https://symfonycasts.com">
    Screencasts
  </a>
</p>

Contributing
------------

We love contributors! For more information on how you can contribute, please read
the [Symfony Docs Contributing Guide](https://symfony.com/doc/current/contributing/documentation/overview.html).

> [!IMPORTANT]
> Use `6.4` branch as the base of your pull requests, unless you are documenting a
> feature that was introduced *after* Symfony 6.4 (e.g. in Symfony 6.3).

Build Documentation Locally
---------------------------

This is not needed for contributing, but it's useful if you would like to debug some
issue in the docs or if you want to read Symfony Documentation offline.

```bash
$ git clone git@github.com:symfony/symfony-docs.git

$ cd symfony-docs/
$ cd _build/

$ composer install

$ php build.php
```

After generating docs, serve them with the internal PHP server:

```bash
$ php -S localhost:8000 -t output/
```

Browse `http://localhost:8000` to read the docs.
