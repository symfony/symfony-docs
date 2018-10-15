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

If you have Docker 17.05+ installed, you can build an image.

```
$ docker build . -t symfony-docs
```

The built image may be served locally on [http//:127.0.0.1](http//:127.0.0.1) by running the command below.

```
$ docker run --rm -p80:80 symfony-docs
```


