<p align="center">
  <a href="https://symfony.com/doc/current/index.html" target="_blank">
    <img src="https://symfony.com/logos/symfony_black_02.svg">
  </a>
</p>

<h3 align="center">The official Symfony documentation.</h3>

<p align="center">
  <a href="https://symfony.com/doc/current/index.html">
    Online version
  </a>
  <span> | </span>
  <a href="https://symfonycasts.com">
    Screencasts
  </a>
  <span> | </span>
  <a href="https://symfony.com/doc/current/contributing/documentation/overview.html">
    Contributing
  </a>
  <span> | </span>
  <a href="https://github.com/symfony/symfony">
    Code repository
  </a>
</p>

Contributing
------------

We love contributors! For more information on how you can contribute to the
Symfony documentation, please read
[Contributing to the Documentation](https://symfony.com/doc/current/contributing/documentation/overview.html)

> **Note**
> Unless you are documenting a feature that was introduced *after* Symfony 3.4
> (e.g. in Symfony 4.4), all pull requests must be based on the **3.4** branch,
> **not** the master or older branches.

SymfonyCloud
------------

Thanks to [SymfonyCloud](https://symfony.com/cloud) for providing an integration
server where Pull Requests are built and can be reviewed by contributors.

Docker
------

You can build the documentation project locally with these commands:

```bash
# build the image...
$ docker build . -t symfony-docs

# ...and start the local web server
# (if it's already in use, change the '8080' port by any other port)
$ docker run --rm -p 8080:80 symfony-docs
```

You can now read the docs at http://127.0.0.1:8080 (if you use a virtual
machine, browse its IP instead of localhost; e.g. `http://192.168.99.100:8080`).
