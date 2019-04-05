Symfony Documentation
=====================

This documentation is rendered online at https://symfony.com/doc/current/

Contributing
------------

> **Note**
> Unless you're documenting a feature that was introduced *after* Symfony 3.4
> (e.g. in Symfony 4.2), all pull requests must be based off of the **3.4** branch,
> **not** the master or older branches.

We love contributors! For more information on how you can contribute to the
Symfony documentation, please read
[Contributing to the Documentation](https://symfony.com/doc/current/contributing/documentation/overview.html)

SymfonyCloud
------------

Pull requests are automatically built by [SymfonyCloud](https://symfony.com/cloud).

Docker
------

You can build the doc locally with these commands:

```bash
# build the image...
$ docker build . -t symfony-docs

# ...and start the local web server
# (if it's already in use, change the '8080' port by any other port)
$ docker run --rm -p 8080:80 symfony-docs
```

You can now read the docs at http://127.0.0.1:8080 (if you use a virtual
machine, browse its IP instead of localhost; e.g. `http://192.168.99.100:8080`).
