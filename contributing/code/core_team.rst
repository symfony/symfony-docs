Symfony Core Team
=================

This document states the rules that govern the Symfony Core group. These rules
are effective upon publication of this document and all Symfony Core members
must adhere to said rules and protocol.

Core Organization
-----------------

Symfony Core members are divided into three groups. Each member can only belong
to one group at a time. The privileges granted to a group are automatically
granted to all higher priority groups.

The Symfony Core groups, in descending order of priority, are as follows:

1. **Project Leader**

* Elects members in any other group;
* Merges pull requests in all Symfony repositories.

2. **Mergers**

* Merge pull requests for the component or components on which they have been
  granted privileges.

3. **Deciders**

* Decide to merge or reject a pull request.

Active Core Members
~~~~~~~~~~~~~~~~~~~

.. role:: leader
.. role:: merger
.. role:: decider

* **Project Leader**:

  * **Fabien Potencier** (`fabpot`_).

* **Mergers** (``@symfony/mergers`` on GitHub):

  * **Bernhard Schussek** (`webmozart`_) can merge into the Form_,
    Validator_, Icu_, Intl_, Locale_, OptionsResolver_ and PropertyAccess_
    components;

  * **Tobias Schultze** (`Tobion`_) can merge into the Routing_,
    OptionsResolver_ and PropertyAccess_ components;

  * **Romain Neutron** (`romainneutron`_) can merge into the
    Process_ component;

  * **Nicolas Grekas** (`nicolas-grekas`_) can merge into the Debug_
    component, the VarDumper_ component and the DebugBundle_;

  * **Christophe Coevoet** (`stof`_) can merge into the BrowserKit_,
    Config_, Console_, DependencyInjection_, DomCrawler_, EventDispatcher_,
    HttpFoundation_, HttpKernel_, Serializer_, Stopwatch_, DoctrineBridge_,
    MonologBridge_, and TwigBridge_ components;

  * **Kévin Dunglas** (`dunglas`_) can merge into the Serializer_
    component;

  * **Abdellatif AitBoudad** (`aitboudad`_) can merge into the Translation_
    component;

  * **Jakub Zalas** (`jakzal`_) can merge into the DomCrawler_ component;

  * **Christian Flothmann** (`xabbuh`_) can merge into the Yaml_ component;

  * **Javier Eguiluz** (`javiereguiluz`_) can merge into the WebProfilerBundle_
    bundle.

* **Deciders** (``@symfony/deciders`` on GitHub):

  * **Jordi Boggiano** (`seldaek`_);
  * **Lukas Kahwe Smith** (`lsmith77`_);
  * **Ryan Weaver** (`weaverryan`_).

Core Membership Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~

At present, new Symfony Core membership applications are not accepted.

Core Membership Revocation
~~~~~~~~~~~~~~~~~~~~~~~~~~

A Symfony Core membership can be revoked for any of the following reasons:

* Refusal to follow the rules and policies stated in this document;
* Lack of activity for the past six months;
* Willful negligence or intent to harm the Symfony project;
* Upon decision of the **Project Leader**.

Should new Symfony Core memberships be accepted in the future, revoked
members must wait at least 12 months before re-applying.

Code Development Rules
----------------------

Symfony project development is based on pull requests proposed by any member
of the Symfony community. Pull request acceptance or rejection is decided based
on the votes cast by the Symfony Core members.

Pull Request Voting Policy
~~~~~~~~~~~~~~~~~~~~~~~~~~

* ``-1`` votes must always be justified by technical and objective reasons;

* ``+1`` votes do not require justification, unless there is at least one
  ``-1`` vote;

* Core members can change their votes as many times as they desire
  during the course of a pull request discussion;

* Core members are not allowed to vote on their own pull requests.

Pull Request Merging Policy
~~~~~~~~~~~~~~~~~~~~~~~~~~~

A pull request **can be merged** if:

* It is a minor change [1]_;

* Enough time was given for peer reviews (at least 2 days for "regular"
  pull requests, and 4 days for pull requests with "a significant impact");

* At least the component's **Merger** or two other Core members voted ``+1``
  and no Core member voted ``-1``.

Pull Request Merging Process
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

All code must be committed to the repository through pull requests, except for
minor changes [1]_ which can be committed directly to the repository.

**Mergers** must always use the command-line ``gh`` tool provided by the
**Project Leader** to merge the pull requests.

Release Policy
~~~~~~~~~~~~~~

The **Project Leader** is also the release manager for every Symfony version.

Symfony Core Rules and Protocol Amendments
------------------------------------------

The rules described in this document may be amended at anytime at the
discretion of the **Project Leader**.


.. [1] Minor changes comprise typos, DocBlock fixes, code standards
       violations, and minor CSS, JavaScript and HTML modifications.

.. _BrowserKit: https://github.com/symfony/browser-kit
.. _Config: https://github.com/symfony/config
.. _Console: https://github.com/symfony/console
.. _Debug: https://github.com/symfony/debug
.. _DebugBundle: https://github.com/symfony/debug-bundle
.. _DependencyInjection: https://github.com/symfony/dependency-injection
.. _DoctrineBridge: https://github.com/symfony/doctrine-bridge
.. _EventDispatcher: https://github.com/symfony/event-dispatcher
.. _DomCrawler: https://github.com/symfony/dom-crawler
.. _Form: https://github.com/symfony/form
.. _HttpFoundation: https://github.com/symfony/http-foundation
.. _HttpKernel: https://github.com/symfony/http-kernel
.. _Icu: https://github.com/symfony/icu
.. _Intl: https://github.com/symfony/intl
.. _Locale: https://github.com/symfony/locale
.. _MonologBridge: https://github.com/symfony/monolog-bridge
.. _OptionsResolver: https://github.com/symfony/options-resolver
.. _Process: https://github.com/symfony/process
.. _PropertyAccess: https://github.com/symfony/property-access
.. _Routing: https://github.com/symfony/routing
.. _Serializer: https://github.com/symfony/serializer
.. _Translation: https://github.com/symfony/translation
.. _Stopwatch: https://github.com/symfony/stopwatch
.. _TwigBridge: https://github.com/symfony/twig-bridge
.. _Validator: https://github.com/symfony/validator
.. _VarDumper: https://github.com/symfony/var-dumper
.. _Yaml: https://github.com/symfony/yaml
.. _WebProfilerBundle: https://github.com/symfony/web-profiler-bundle
.. _`fabpot`: https://github.com/fabpot/
.. _`webmozart`: https://github.com/webmozart/
.. _`Tobion`: https://github.com/Tobion/
.. _`romainneutron`: https://github.com/romainneutron/
.. _`nicolas-grekas`: https://github.com/nicolas-grekas/
.. _`stof`: https://github.com/stof/
.. _`dunglas`: https://github.com/dunglas/
.. _`jakzal`: https://github.com/jakzal/
.. _`Seldaek`: https://github.com/Seldaek/
.. _`lsmith77`: https://github.com/lsmith77/
.. _`weaverryan`: https://github.com/weaverryan/
.. _`aitboudad`: https://github.com/aitboudad/
.. _`xabbuh`: https://github.com/xabbuh/
.. _`javiereguiluz`: https://github.com/javiereguiluz/
