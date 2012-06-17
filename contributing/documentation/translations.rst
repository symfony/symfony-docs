Translations
============

The Symfony2 documentation is written in English and many people are involved
in the translation process.

Contributing
------------

First, become familiar with the :doc:`markup language <format>` used by the
documentation.

Then, subscribe to the `Symfony docs mailing-list`_, as collaboration happens
there.

Finally, find the *master* repository for the language you want to contribute
for. Here is the list of the official *master* repositories:

* *English*:  https://github.com/symfony/symfony-docs
* *French*:   https://github.com/symfony-fr/symfony-docs-fr
* *Italian*:  https://github.com/garak/symfony-docs-it
* *Japanese*: https://github.com/symfony-japan/symfony-docs-ja
* *Polish*:   https://github.com/ampluso/symfony-docs-pl
* *Romanian*: https://github.com/sebio/symfony-docs-ro
* *Russian*:  https://github.com/avalanche123/symfony-docs-ru
* *Spanish*:  https://github.com/gitnacho/symfony-docs-es
* *Turkish*:  https://github.com/symfony-tr/symfony-docs-tr

.. note::

    If you want to contribute translations for a new language, read the
    :ref:`dedicated section <translations-adding-a-new-language>`.

Joining the Translation Team
----------------------------

If you want to help translating some documents for your language or fix some
bugs, consider joining us; it's a very easy process:

* Introduce yourself on the `Symfony docs mailing-list`_;
* *(optional)* Ask which documents you can work on;
* Fork the *master* repository for your language (click the "Fork" button on
  the GitHub page);
* Translate some documents;
* Ask for a pull request (click on the "Pull Request" from your page on
  GitHub);
* The team manager accepts your modifications and merges them into the master
  repository;
* The documentation website is updated every other night from the master
  repository.

.. _translations-adding-a-new-language:

Adding a new Language
---------------------

This section gives some guidelines for starting the translation of the
Symfony2 documentation for a new language.

As starting a translation is a lot of work, talk about your plan on the
`Symfony docs mailing-list`_ and try to find motivated people willing to help.

When the team is ready, nominate a team manager; he will be responsible for
the *master* repository.

Create the repository and copy the *English* documents.

The team can now start the translation process.

When the team is confident that the repository is in a consistent and stable
state (everything is translated, or non-translated documents have been removed
from the toctrees -- files named ``index.rst`` and ``map.rst.inc``), the team
manager can ask that the repository is added to the list of official *master*
repositories by sending an email to Fabien (fabien at symfony.com).

Maintenance
-----------

Translation does not end when everything is translated. The documentation is a
moving target (new documents are added, bugs are fixed, paragraphs are
reorganized, ...). The translation team need to closely follow the English
repository and apply changes to the translated documents as soon as possible.

.. caution::

    Non maintained languages are removed from the official list of
    repositories as obsolete documentation is dangerous.

.. _Symfony docs mailing-list: http://groups.google.com/group/symfony-docs
