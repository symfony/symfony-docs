How To Configure and Use Flex Private Recipe Repositories
=========================================================

Since the `release of version 1.16`_ of ``symfony/flex``, you can build your own
private Symfony Flex recipe repositories, and seamlessly integrate them into the
``composer`` package installation and maintenance process.

This is particularly useful when you have private bundles or packages that must
perform their own installation tasks. To do this, you need to complete several steps:

* Create a private repository;
* Create your private recipes;
* Create an index to the recipes;
* Store your recipes in the private repository;
* Grant ``composer`` access to the private repository;
* Configure your project's ``composer.json`` file; and
* Install the recipes in your project.

.. _create-a-private-github-repository:

Create a Private Repository
---------------------------

GitHub
~~~~~~

Log in to your GitHub.com account, click your account icon in the top-right
corner, and select **Your Repositories**. Then click the **New** button, fill in
the **repository name**, select the **Private** radio button, and click the
**Create Repository** button.

Gitlab
~~~~~~

Log in to your Gitlab.com account, click the **New project** button, select
**Create blank project**, fill in the **Project name**, select the **Private**
radio button, and click the **Create project** button.

Create Your Private Recipes
---------------------------

A ``symfony/flex`` recipe is a JSON file that has the following structure:

.. code-block:: json

    {
        "manifests": {
            "acme/package-name": {
                "manifest": {
                },
                "ref": "7405f3af1312d1f9121afed4dddef636c6c7ff00"
            }
        }
    }

If your package is a private Symfony bundle, you will have the following in the recipe:

.. code-block:: json

    {
        "manifests": {
            "acme/private-bundle": {
                "manifest": {
                    "bundles": {
                        "Acme\\PrivateBundle\\AcmePrivateBundle": [
                            "all"
                        ]
                    }
                },
                "ref": "7405f3af1312d1f9121afed4dddef636c6c7ff00"
            }
        }
    }

Replace ``acme`` and ``private-bundle`` with your own private bundle details.
The ``"ref"`` entry is a random 40-character string used by ``composer`` to
determine if your recipe was modified. Every time that you make changes to your
recipe, you also need to generate a new ``"ref"`` value.

.. tip::

    Use the following PHP script to generate a random ``"ref"`` value::

        echo bin2hex(random_bytes(20));

The ``"all"`` entry tells ``symfony/flex`` to create an entry in your project's
``bundles.php`` file for all environments. To load your bundle only for the
``dev`` environment, replace ``"all"`` with ``"dev"``.

The name of your recipe JSON file must conform to the following convention,
where ``1.0`` is the version number of your bundle (replace ``acme`` and
``private-bundle`` with your own private bundle or package details):

    ``acme.private-bundle.1.0.json``

You will probably also want ``symfony/flex`` to create configuration files for
your bundle or package in the project's ``/config/packages`` directory. To do
that, change the recipe JSON file as follows:

.. code-block:: json

    {
        "manifests": {
            "acme/private-bundle": {
                "manifest": {
                    "bundles": {
                        "Acme\\PrivateBundle\\AcmePrivateBundle": [
                            "all"
                        ]
                    },
                    "copy-from-recipe": {
                        "config/": "%CONFIG_DIR%"
                    }
                },
                "files": {
                    "config/packages/acme_private.yaml": {
                        "contents": [
                            "acme_private:",
                            "    encode: true",
                            ""
                        ],
                        "executable": false
                    }
                },
                "ref": "7405f3af1312d1f9121afed4dddef636c6c7ff00"
            }
        }
    }

For more examples of what you can include in a recipe file, browse the
`Symfony recipe files`_.

Create an Index to the Recipes
------------------------------

The next step is to create an ``index.json`` file, which will contain entries
for all your private recipes, and other general configuration information.

GitHub
~~~~~~

The ``index.json`` file has the following format:

.. code-block:: json

    {
        "recipes": {
            "acme/private-bundle": [
                "1.0"
            ]
        },
        "branch": "main",
        "is_contrib": true,
        "_links": {
            "repository": "github.com/your-github-account-name/your-recipes-repository",
            "origin_template": "{package}:{version}@github.com/your-github-account-name/your-recipes-repository:main",
            "recipe_template": "https://api.github.com/repos/your-github-account-name/your-recipes-repository/contents/{package_dotted}.{version}.json"
        }
    }

Create an entry in ``"recipes"`` for each of your bundle recipes. Replace
``your-github-account-name`` and ``your-recipes-repository`` with your own details.

Gitlab
~~~~~~

The ``index.json`` file has the following format:

.. code-block:: json

    {
        "recipes": {
            "acme/private-bundle": [
                "1.0"
            ]
        },
        "branch": "main",
        "is_contrib": true,
        "_links": {
            "repository": "gitlab.com/your-gitlab-account-name/your-recipes-repository",
            "origin_template": "{package}:{version}@gitlab.com/your-gitlab-account-name/your-recipes-repository:main",
            "recipe_template": "https://gitlab.com/api/v4/projects/your-gitlab-project-id/repository/files/{package_dotted}.{version}.json/raw?ref=main"
        }
    }

Create an entry in ``"recipes"`` for each of your bundle recipes. Replace
``your-gitlab-account-name``, ``your-gitlab-repository`` and ``your-gitlab-project-id``
with your own details.

Store Your Recipes in the Private Repository
--------------------------------------------

Upload the recipe ``.json`` file(s) and the ``index.json`` file into the root
directory of your private repository.

Grant ``composer`` Access to the Private Repository
---------------------------------------------------

GitHub
~~~~~~

In your GitHub account, click your account icon in the top-right corner, select
``Settings`` and ``Developer Settings``. Then select ``Personal Access Tokens``.

Generate a new access token with ``Full control of private repositories``
privileges. Copy the access token value, switch to the terminal of your local
computer, and execute the following command:

.. code-block:: terminal

    $ composer config --global --auth github-oauth.github.com [token]

Replace ``[token]`` with the value of your GitHub personal access token.

Gitlab
~~~~~~

In your Gitlab account, click your account icon in the top-right corner, select
``Preferences`` and ``Access Tokens``.

Generate a new personal access token with ``read_api`` and ``read_repository``
scopes. Copy the access token value, switch to the terminal of your local
computer, and execute the following command:

.. code-block:: terminal

    $ composer config --global --auth gitlab-token.gitlab.com [token]

Replace ``[token]`` with the value of your Gitlab personal access token.

Configure Your Project's ``composer.json`` File
-----------------------------------------------

GitHub
~~~~~~

Add the following to your project's ``composer.json`` file:

.. code-block:: json

    {
        "extra": {
            "symfony": {
                "endpoint": [
                    "https://api.github.com/repos/your-github-account-name/your-recipes-repository/contents/index.json",
                    "flex://defaults"
                ]
            }
        }
    }

Replace ``your-github-account-name`` and ``your-recipes-repository`` with your own details.

.. tip::

    The ``extra.symfony`` key will most probably already exist in your
    ``composer.json``. In that case, add the ``"endpoint"`` key to the existing
    ``extra.symfony`` entry.

.. tip::

    The ``endpoint`` URL **must** point to ``https://api.github.com/repos`` and
    **not** to ``https://www.github.com``.

Gitlab
~~~~~~

Add the following to your project's ``composer.json`` file:

.. code-block:: json

    {
        "extra": {
            "symfony": {
                "endpoint": [
                    "https://gitlab.com/api/v4/projects/your-gitlab-project-id/repository/files/index.json/raw?ref=main",
                    "flex://defaults"
                ]
            }
        }
    }

Replace ``your-gitlab-project-id`` with your own details.

.. tip::

    The ``extra.symfony`` key will most probably already exist in your
    ``composer.json``. In that case, add the ``"endpoint"`` key to the existing
    ``extra.symfony`` entry.

Install the Recipes in Your Project
-----------------------------------

If your private bundles/packages have not yet been installed in your project,
run the following command:

.. code-block:: terminal

    $ composer update

If the private bundles/packages have already been installed and you just want to
install the new private recipes, run the following command:

.. code-block:: terminal

    $ composer recipes

.. _`release of version 1.16`: https://github.com/symfony/cli
.. _`Symfony recipe files`: https://github.com/symfony/recipes/tree/flex/main
