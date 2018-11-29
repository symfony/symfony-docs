Symfony Documentation
=====================

This documentation is rendered online at https://symfony.com/doc/current/

Contributing
------------

>**Note**
>Unless you're documenting a feature that was introduced *after* Symfony 2.8
>(e.g. in Symfony 3.4), all pull requests must be based off of the **2.8** branch,
>**not** the master or older branches.

We love contributors! For more information on how you can contribute to the
Symfony documentation, please read
[Contributing to the Documentation](https://symfony.com/doc/current/contributing/documentation/overview.html)

Platform.sh
-----------

Pull requests are automatically built by [Platform.sh](https://platform.sh).

Docker
------

You can build the doc locally with these commands:

```bash
# build the image...
$ docker build . -t symfony-docs

# ...and serve it locally on http//:127.0.0.1:8080
# (if it's already in use, change the '8080' port by any other port)
$ docker run --rm -p 8080:80 symfony-docs
```
