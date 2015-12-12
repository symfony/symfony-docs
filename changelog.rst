.. index::
    single: CHANGELOG

The Documentation Changelog
===========================

This documentation is always changing: All new features need new documentation
and bugs/typos get fixed. This article holds all important changes of the
documentation.

.. tip::

    Do you also want to participate in the Symfony Documentation? Take a look
    at the ":doc:`/contributing/documentation/overview`" article.

November, 2015
--------------

New Documentation
~~~~~~~~~~~~~~~~~

* `#5917 <https://github.com/symfony/symfony-docs/pull/5917>`_ [3.0][Cookbook] Use the 3.0 directory structure (WouterJ)
* `#5916 <https://github.com/symfony/symfony-docs/pull/5916>`_ [3.0][Best Practices][Quick Tour] Use the 3.0 directory structure (WouterJ)
* `#5913 <https://github.com/symfony/symfony-docs/pull/5913>`_ [3.0][Book] Use the 3.0 directory structure (WouterJ)
* `#5907 <https://github.com/symfony/symfony-docs/pull/5907>`_ Updating some places to use the new CustomUserMessageAuthenticationException (weaverryan)
* `#5922 <https://github.com/symfony/symfony-docs/pull/5922>`_ Added minimal cookbook article about the shared flag (WouterJ)
* `#5908 <https://github.com/symfony/symfony-docs/pull/5908>`_ Voter update (weaverryan)
* `#5909 <https://github.com/symfony/symfony-docs/pull/5909>`_ More 2.8 form updates (weaverryan)
* `#5927 <https://github.com/symfony/symfony-docs/pull/5927>`_ Use path() and url() PHP templating helpers (WouterJ)
* `#5926 <https://github.com/symfony/symfony-docs/pull/5926>`_ Update voter section of best practices (WouterJ)
* `#5921 <https://github.com/symfony/symfony-docs/pull/5921>`_ [2.8] Document some Security changes (WouterJ)
* `#5834 <https://github.com/symfony/symfony-docs/pull/5834>`_ Updated form aliases to FQCNs for forms in book and component (hiddewie)
* `#5265 <https://github.com/symfony/symfony-docs/pull/5265>`_ Documentation for the new Guard authentication style (weaverryan)
* `#5899 <https://github.com/symfony/symfony-docs/pull/5899>`_ Adding the MicroKernel article (weaverryan)
* `#5893 <https://github.com/symfony/symfony-docs/pull/5893>`_ Added a note about the use of _format query parameter (javiereguiluz)
* `#5891 <https://github.com/symfony/symfony-docs/pull/5891>`_ Removed the comments about the is_granted() issues in non-secure pages (javiereguiluz)
* `#5876 <https://github.com/symfony/symfony-docs/pull/5876>`_ Symfony 2.7 Form choice option update (aivus, althaus, weaverryan)
* `#5861 <https://github.com/symfony/symfony-docs/pull/5861>`_ Updated Table Console helper for spanning cols and rows (hiddewie)
* `#5835 <https://github.com/symfony/symfony-docs/pull/5835>`_ Updated CssSelector code example to use the new Converter (hiddewie)
* `#5816 <https://github.com/symfony/symfony-docs/pull/5816>`_ Merge branches (nicolas-grekas, snoek09, WouterJ, xabbuh)
* `#5804 <https://github.com/symfony/symfony-docs/pull/5804>`_ Added documentation for dnsMessage option (BenjaminPaap)
* `#5774 <https://github.com/symfony/symfony-docs/pull/5774>`_ Show a more real example in data collectors doc (WouterJ)
* `#5735 <https://github.com/symfony/symfony-docs/pull/5735>`_ [Contributing][Code] do not distinguish regular classes and API classes (xabbuh)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

* `#5903 <https://github.com/symfony/symfony-docs/pull/5903>`_ Update front controller (nurolopher)
* `#5768 <https://github.com/symfony/symfony-docs/pull/5768>`_ Removed "http_basic" config from the login form cookbook (javiereguiluz)
* `#5863 <https://github.com/symfony/symfony-docs/pull/5863>`_ Correct useAttributeAsKey usage (danrot)
* `#5833 <https://github.com/symfony/symfony-docs/pull/5833>`_ Fixed whitelist delivery of swiftmailer (hiddewie)
* `#5815 <https://github.com/symfony/symfony-docs/pull/5815>`_ fix constraint names (xabbuh)
* `#5793 <https://github.com/symfony/symfony-docs/pull/5793>`_ Callback Validation Constraint: Remove reference to deprecated option (ceithir)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

* `#5931 <https://github.com/symfony/symfony-docs/pull/5931>`_ [#5875] Fixed link description, list of common media types (Douglas Naphas)
* `#5923 <https://github.com/symfony/symfony-docs/pull/5923>`_ Remove information about request service deps of core services (WouterJ)
* `#5911 <https://github.com/symfony/symfony-docs/pull/5911>`_ Wrap all strings containing @ in quotes in Yaml (WouterJ)
* `#5889 <https://github.com/symfony/symfony-docs/pull/5889>`_ Always use "main" as the default firewall name (to match Symfony Standard Edition) (javiereguiluz)
* `#5888 <https://github.com/symfony/symfony-docs/pull/5888>`_ Removed the use of ContainerAware class (javiereguiluz)
* `#5625 <https://github.com/symfony/symfony-docs/pull/5625>`_ Tell about SYMFONY__TEMPLATING__HELPER__CODE__FILE_LINK_FORMAT (nicolas-grekas)
* `#5896 <https://github.com/symfony/symfony-docs/pull/5896>`_ [Book][Templating] Update absolute URL asset to match 2.7 (lemoinem)
* `#5828 <https://github.com/symfony/symfony-docs/pull/5828>`_ move the getEntityManager, only get it if needed (OskarStark)
* `#5900 <https://github.com/symfony/symfony-docs/pull/5900>`_ Added new security advisories to the docs (fabpot)
* `#5897 <https://github.com/symfony/symfony-docs/pull/5897>`_ Fixed some wrong line number references in doctrine.rst (DigNative)
* `#5895 <https://github.com/symfony/symfony-docs/pull/5895>`_ Update debug_formatter.rst (strannik-06)
* `#5883 <https://github.com/symfony/symfony-docs/pull/5883>`_ Book: Update Service Container Documentation (zanderbaldwin)
* `#5868 <https://github.com/symfony/symfony-docs/pull/5868>`_ [2.8] Make screenshots with the new profiler/web dev toolbar design (WouterJ)
* `#5862 <https://github.com/symfony/symfony-docs/pull/5862>`_ Fixes done automatically by the docbot (WouterJ)
* `#5851 <https://github.com/symfony/symfony-docs/pull/5851>`_ updated sentence (OskarStark)
* `#5870 <https://github.com/symfony/symfony-docs/pull/5870>`_ Update securing_services.rst (aruku)
* `#5859 <https://github.com/symfony/symfony-docs/pull/5859>`_ Use Twig highlighter instead of Jinja (WouterJ)
* `#5866 <https://github.com/symfony/symfony-docs/pull/5866>`_ Fixed little typo with a twig example (artf)
* `#5849 <https://github.com/symfony/symfony-docs/pull/5849>`_ Clarified ambiguous wording (ThomasLandauer)
* `#5826 <https://github.com/symfony/symfony-docs/pull/5826>`_ "setup" is a noun or adjective, "set up" is the verb (carlos-granados)
* `#5816 <https://github.com/symfony/symfony-docs/pull/5816>`_ Merge branches (nicolas-grekas, snoek09, WouterJ, xabbuh)
* `#5813 <https://github.com/symfony/symfony-docs/pull/5813>`_ use constants to choose generated URL type (xabbuh)
* `#5808 <https://github.com/symfony/symfony-docs/pull/5808>`_ Reworded the explanation about flash messages (javiereguiluz)
* `#5809 <https://github.com/symfony/symfony-docs/pull/5809>`_ Minor fix (javiereguiluz)
* `#5807 <https://github.com/symfony/symfony-docs/pull/5807>`_ Minor rewordings for the "deprecated" service option (javiereguiluz)
* `#5805 <https://github.com/symfony/symfony-docs/pull/5805>`_ Mentioned the BETA and RC support for the Symfony Installer (javiereguiluz)
* `#5781 <https://github.com/symfony/symfony-docs/pull/5781>`_ Added annotations example to Linking to Pages examples (carlos-granados)
* `#5780 <https://github.com/symfony/symfony-docs/pull/5780>`_ Clarify when we are talking about PHP and Twig (carlos-granados)
* `#5767 <https://github.com/symfony/symfony-docs/pull/5767>`_ [Cookbook][Security] clarify description of the getPosition() method (xabbuh)
* `#5731 <https://github.com/symfony/symfony-docs/pull/5731>`_ [Cookbook][Security] update versionadded directive to match the content (xabbuh)
* `#5681 <https://github.com/symfony/symfony-docs/pull/5681>`_ Update storage.rst (jls2933)
* `#5363 <https://github.com/symfony/symfony-docs/pull/5363>`_ Added description on how to enable the security:check command through… (bizmate)
* `#5841 <https://github.com/symfony/symfony-docs/pull/5841>`_ [Cookbook][Psr7] fix zend-diactoros Packagist link (xabbuh)
* `#5850 <https://github.com/symfony/symfony-docs/pull/5850>`_ Fixed typo (tobiassjosten)
* `#5852 <https://github.com/symfony/symfony-docs/pull/5852>`_ Fix doc for 2.6+, `server:start` replace `...:run` (Kevinrob)
* `#5837 <https://github.com/symfony/symfony-docs/pull/5837>`_ Corrected link to ConEmu (dritter)


October, 2015
-------------

New Documentation
~~~~~~~~~~~~~~~~~

* `#5345 <https://github.com/symfony/symfony-docs/pull/5345>`_ Adding information about empty files sent using BinaryFileResponse. (kherge)
* `#5214 <https://github.com/symfony/symfony-docs/pull/5214>`_ [WIP] Reworking most of the registration form: (weaverryan)
* `#5051 <https://github.com/symfony/symfony-docs/pull/5051>`_ Rename CollectionType entry options (WouterJ)
* `#5677 <https://github.com/symfony/symfony-docs/pull/5677>`_ replacing deprecated usage of True, False, Null validators in docs (Tim Stamp)
* `#5314 <https://github.com/symfony/symfony-docs/pull/5314>`_ Documented the useAttributeAsKey() method (javiereguiluz)
* `#5377 <https://github.com/symfony/symfony-docs/pull/5377>`_ Added a cookbook section about event subscribers (beni0888, javiereguiluz)
* `#5623 <https://github.com/symfony/symfony-docs/pull/5623>`_ [Validator] added BIC validator (mvhirsch)
* `#5689 <https://github.com/symfony/symfony-docs/pull/5689>`_ [DI] Add some documentation for the deprecation feature (Taluu)
* `#5592 <https://github.com/symfony/symfony-docs/pull/5592>`_ Updated the article about data collectors (javiereguiluz)
* `#5745 <https://github.com/symfony/symfony-docs/pull/5745>`_ [Translation] Ability to format a message catalogue without actually writing it. (aitboudad)
* `#5702 <https://github.com/symfony/symfony-docs/pull/5702>`_ Added a reference to the Foundation form theme (totophe)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

* `#5795 <https://github.com/symfony/symfony-docs/pull/5795>`_ Fix typo in UserType class (Dorozhko-Anton)
* `#5758 <https://github.com/symfony/symfony-docs/pull/5758>`_ symlink issues with php-fpm (kendrick-k)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

* `#5843 <https://github.com/symfony/symfony-docs/pull/5843>`_ Fixed the YAML syntax for service references (javiereguiluz)
* `#5797 <https://github.com/symfony/symfony-docs/pull/5797>`_ [Process] use ProcessFailedException instead of RuntimeException. (aitboudad)
* `#5812 <https://github.com/symfony/symfony-docs/pull/5812>`_ Remove duplicate and confusing info about testing error pages (carlos-granados)
* `#5821 <https://github.com/symfony/symfony-docs/pull/5821>`_ Minor fixes in the HttpFoundation introduction article (javiereguiluz)
* `#5822 <https://github.com/symfony/symfony-docs/pull/5822>`_ Fixed a syntax issue (javiereguiluz)
* `#5817 <https://github.com/symfony/symfony-docs/pull/5817>`_ fix version for `entry_options` and `entry_type` (craue)
* `#5796 <https://github.com/symfony/symfony-docs/pull/5796>`_ Fix for #5783 (BenjaminPaap)
* `#5810 <https://github.com/symfony/symfony-docs/pull/5810>`_ Fixed a typo (javiereguiluz)
* `#5784 <https://github.com/symfony/symfony-docs/pull/5784>`_ Add fe80::1 (j-d)
* `#5799 <https://github.com/symfony/symfony-docs/pull/5799>`_ make file path consitent with other articles (OskarStark)
* `#5794 <https://github.com/symfony/symfony-docs/pull/5794>`_ Minor tweaks for the registration form article (javiereguiluz)
* `#5801 <https://github.com/symfony/symfony-docs/pull/5801>`_ namespace fix (OskarStark)
* `#5792 <https://github.com/symfony/symfony-docs/pull/5792>`_ [Cookbook][EventDispatcher] fix build (xabbuh)
* `#5787 <https://github.com/symfony/symfony-docs/pull/5787>`_ Definition Tweaks - see #5314 (weaverryan)
* `#5777 <https://github.com/symfony/symfony-docs/pull/5777>`_ Update links (thewilkybarkid)
* `#5775 <https://github.com/symfony/symfony-docs/pull/5775>`_ Misspelling (carlos-granados)
* `#5664 <https://github.com/symfony/symfony-docs/pull/5664>`_ Info about implicit session start (ThomasLandauer)
* `#5744 <https://github.com/symfony/symfony-docs/pull/5744>`_ translations have been removed from symfony.com (xabbuh)
* `#5771 <https://github.com/symfony/symfony-docs/pull/5771>`_ Remove not existing response constant (amansilla)
* `#5761 <https://github.com/symfony/symfony-docs/pull/5761>`_ [DX] [Security] Renamed key to secret (SongoQ)
* `#5766 <https://github.com/symfony/symfony-docs/pull/5766>`_ Fixed two typos (ThomasLandauer)
* `#5733 <https://github.com/symfony/symfony-docs/pull/5733>`_ [Components][OptionsResolver] adding type hint to normalizer callback (xabbuh)
* `#5561 <https://github.com/symfony/symfony-docs/pull/5561>`_ Change default value of cookie_httponly (jderusse)
* `#5678 <https://github.com/symfony/symfony-docs/pull/5678>`_ Update HttpFoundation note after recent changes in routing component (senkal)
* `#5643 <https://github.com/symfony/symfony-docs/pull/5643>`_ Document how to customize the prototype (daFish, WouterJ)
* `#5584 <https://github.com/symfony/symfony-docs/pull/5584>`_ Add DebugBundle config reference (WouterJ)
* `#5753 <https://github.com/symfony/symfony-docs/pull/5753>`_ configureOptions(...) : protected => public (lucascherifi)
* `#5750 <https://github.com/symfony/symfony-docs/pull/5750>`_ fix YAML syntax highlighting (xabbuh)
* `#5749 <https://github.com/symfony/symfony-docs/pull/5749>`_ complete Swiftmailer XML examples (xabbuh)
* `#5730 <https://github.com/symfony/symfony-docs/pull/5730>`_ Remove documentation of deprecated console shell (Tobion)
* `#5726 <https://github.com/symfony/symfony-docs/pull/5726>`_ Document the support of Mintty for colors (stof)
* `#5708 <https://github.com/symfony/symfony-docs/pull/5708>`_ Added caution to call createView after handleRequest (WouterJ)
* `#5640 <https://github.com/symfony/symfony-docs/pull/5640>`_ Update controller.rst clarifying automatic deletion for flash messages (miguelvilata)
* `#5578 <https://github.com/symfony/symfony-docs/pull/5578>`_ Add supported branches in platform.sh section (WouterJ)
* `#5468 <https://github.com/symfony/symfony-docs/pull/5468>`_ [Cookbook][Templating] Add note about cache warming namespaced twig templates (kbond)
* `#5684 <https://github.com/symfony/symfony-docs/pull/5684>`_ Fix delivery_whitelist regex (gonzalovilaseca)
* `#5742 <https://github.com/symfony/symfony-docs/pull/5742>`_ incorrect: severity is an array key here and not a constant (lbayerl)


September, 2015
---------------

New Documentation
~~~~~~~~~~~~~~~~~

* `#5555 <https://github.com/symfony/symfony-docs/pull/5555>`_ added result yaml and xml from example code (OskarStark)
* `#5631 <https://github.com/symfony/symfony-docs/pull/5631>`_ Updated the Quick Tour to the latest changes introduced by Symfony (javiereguiluz)
* `#5497 <https://github.com/symfony/symfony-docs/pull/5497>`_ Simplified the Quick tour explanation about Symfony Installation (DQNEO)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

* `#5629 <https://github.com/symfony/symfony-docs/pull/5629>`_ Fixing web user permission (BenoitLeveque)
* `#5673 <https://github.com/symfony/symfony-docs/pull/5673>`_ Update http_cache.rst (szyszka90)
* `#5666 <https://github.com/symfony/symfony-docs/pull/5666>`_ Fix EntityManager namespace (JhonnyL)
* `#5656 <https://github.com/symfony/symfony-docs/pull/5656>`_ Fix monolog line formatter in logging cookbook example. (vmarquez)
* `#5507 <https://github.com/symfony/symfony-docs/pull/5507>`_ Path fixed (carlosreig)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

* `#5740 <https://github.com/symfony/symfony-docs/pull/5740>`_ Fix typo in PdoSessionHandler Documentation (tobemedia)
* `#5719 <https://github.com/symfony/symfony-docs/pull/5719>`_ changed repo names to the new ones (fabpot)
* `#5227 <https://github.com/symfony/symfony-docs/pull/5227>`_ [Cookbook] Fix doc on Generic Form Type Extensions (lemoinem)
* `#5703 <https://github.com/symfony/symfony-docs/pull/5703>`_ comment old logic (OskarStark)
* `#5683 <https://github.com/symfony/symfony-docs/pull/5683>`_ Improve the demo-warning. (GuGuss)
* `#5690 <https://github.com/symfony/symfony-docs/pull/5690>`_ Updated the release process image (javiereguiluz)
* `#5188 <https://github.com/symfony/symfony-docs/pull/5188>`_ Updated Cookies & Caching section (lukey78)
* `#5710 <https://github.com/symfony/symfony-docs/pull/5710>`_ Fix grammar mistake in security.rst (zatikbalazs)
* `#5706 <https://github.com/symfony/symfony-docs/pull/5706>`_ Update assetic.rst (Acinonux)
* `#5705 <https://github.com/symfony/symfony-docs/pull/5705>`_ Update assetic.rst (Acinonux)
* `#5685 <https://github.com/symfony/symfony-docs/pull/5685>`_ Fix indentation in some annotations (iamdto)
* `#5704 <https://github.com/symfony/symfony-docs/pull/5704>`_ Fix typo in translation.rst (zatikbalazs)
* `#5701 <https://github.com/symfony/symfony-docs/pull/5701>`_ Update testing.rst (hansallis)
* `#5711 <https://github.com/symfony/symfony-docs/pull/5711>`_ removed service call from controller (sloba88)
* `#5692 <https://github.com/symfony/symfony-docs/pull/5692>`_ Made a sentence slightly more english (GTheron)
* `#5715 <https://github.com/symfony/symfony-docs/pull/5715>`_ Add missing code tag (zatikbalazs)
* `#5720 <https://github.com/symfony/symfony-docs/pull/5720>`_ adding closing tag (InfoTracer)
* `#5714 <https://github.com/symfony/symfony-docs/pull/5714>`_ Remove unnecessary word from http_cache.rst (zatikbalazs)
* `#5680 <https://github.com/symfony/symfony-docs/pull/5680>`_ fix grammar mistake (greg0ire)
* `#5682 <https://github.com/symfony/symfony-docs/pull/5682>`_ Fix grammar and CS (iamdto)
* `#5652 <https://github.com/symfony/symfony-docs/pull/5652>`_ Do not use dynamic REQUEST_URI from $_SERVER as base url (senkal)
* `#5654 <https://github.com/symfony/symfony-docs/pull/5654>`_ Doc about new way of running tests (nicolas-grekas)
* `#5598 <https://github.com/symfony/symfony-docs/pull/5598>`_ [Cookbook][Security] proofread comments in voter article (xabbuh)
* `#5560 <https://github.com/symfony/symfony-docs/pull/5560>`_ [2.3] [Contributing] [CS] Added missing docblocks in code snippet (phansys)
* `#5674 <https://github.com/symfony/symfony-docs/pull/5674>`_ Update cookbook entries with best practices (JhonnyL)
* `#5675 <https://github.com/symfony/symfony-docs/pull/5675>`_ [Contributing] add a link to the testing section (xabbuh)
* `#5669 <https://github.com/symfony/symfony-docs/pull/5669>`_ Better explanation of implicit exception response status code (hvt)
* `#5651 <https://github.com/symfony/symfony-docs/pull/5651>`_ [Reference][Constraints] follow best practices in the constraints reference (xabbuh)
* `#5648 <https://github.com/symfony/symfony-docs/pull/5648>`_ Minor fixes for the QuestionHelper documentation (javiereguiluz)
* `#5641 <https://github.com/symfony/symfony-docs/pull/5641>`_ Move important information out of versionadded (WouterJ)
* `#5619 <https://github.com/symfony/symfony-docs/pull/5619>`_ Remove a caution note about StringUtils::equals() which is no longer true (javiereguiluz)
* `#5571 <https://github.com/symfony/symfony-docs/pull/5571>`_ Some small fixes for upload files article (WouterJ)
* `#5660 <https://github.com/symfony/symfony-docs/pull/5660>`_ Improved "Community Reviews" page (webmozart)


August, 2015
------------

New Documentation
~~~~~~~~~~~~~~~~~

* `#5480 <https://github.com/symfony/symfony-docs/pull/5480>`_ Added page "Community Reviews" (webmozart)
* `#5595 <https://github.com/symfony/symfony-docs/pull/5595>`_ Improve humanize filter documentation (bocharsky-bw)
* `#5319 <https://github.com/symfony/symfony-docs/pull/5319>`_ [Console] Command Lifecycle explications (94noni)
* `#5394 <https://github.com/symfony/symfony-docs/pull/5394>`_ Fix Major upgrade article for 2.7.1 changes (WouterJ)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

* `#5589 <https://github.com/symfony/symfony-docs/pull/5589>`_ [Cookbook][Session] fix default expiry field name (xabbuh)
* `#5607 <https://github.com/symfony/symfony-docs/pull/5607>`_ Fix (sebastianbergmann)
* `#5608 <https://github.com/symfony/symfony-docs/pull/5608>`_ updated validation.rst (issei-m)
* `#5449 <https://github.com/symfony/symfony-docs/pull/5449>`_ Ensure that the entity is updated. (yceruto)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

* `#5553 <https://github.com/symfony/symfony-docs/pull/5553>`_ Fix all broken links/permanent redirects/removed anchors (WouterJ)
* `#5650 <https://github.com/symfony/symfony-docs/pull/5650>`_ [RFR] fixing typo and removing duplicated lines in Config component doc  (salahm)
* `#5635 <https://github.com/symfony/symfony-docs/pull/5635>`_ Fix minor problems in book/page_creation.rst (fabschurt)
* `#5579 <https://github.com/symfony/symfony-docs/pull/5579>`_ [3.0] Remove mentions of Symfony1 (WouterJ)
* `#5647 <https://github.com/symfony/symfony-docs/pull/5647>`_ don't ignore the _exts directory anymore (xabbuh)
* `#5587 <https://github.com/symfony/symfony-docs/pull/5587>`_ [2.6] Don't use deprecated features (WouterJ)
* `#5637 <https://github.com/symfony/symfony-docs/pull/5637>`_ Add QueryBuilder vs DQL section (bocharsky-bw)
* `#5645 <https://github.com/symfony/symfony-docs/pull/5645>`_ Updated Constraint reference with best practices (WouterJ)
* `#5646 <https://github.com/symfony/symfony-docs/pull/5646>`_ Moved comment to the right place (mickaelandrieu)
* `#5649 <https://github.com/symfony/symfony-docs/pull/5649>`_ [RFR] Fixing typo in Symfony version for ButtonType (salahm)
* `#5606 <https://github.com/symfony/symfony-docs/pull/5606>`_ Use symfony.com theme on Platform.sh builds (WouterJ)
* `#5644 <https://github.com/symfony/symfony-docs/pull/5644>`_ Update page_creation.rst (jeromenadaud)
* `#5593 <https://github.com/symfony/symfony-docs/pull/5593>`_ Updated the profiler matchers article (javiereguiluz)
* `#5522 <https://github.com/symfony/symfony-docs/pull/5522>`_ [create_framework] Add missing extract() 2nd arg (kenjis)
* `#5597 <https://github.com/symfony/symfony-docs/pull/5597>`_ [CreateFramework] don't override existing variables (xabbuh)
* `#5628 <https://github.com/symfony/symfony-docs/pull/5628>`_ Updated the installation chapter (javiereguiluz)
* `#5638 <https://github.com/symfony/symfony-docs/pull/5638>`_ Update page_creation.rst (jeromenadaud)
* `#5636 <https://github.com/symfony/symfony-docs/pull/5636>`_ Fixed typo in web-assets.rst (nielsvermaut)
* `#5633 <https://github.com/symfony/symfony-docs/pull/5633>`_ Upgrade Platform.sh configuration snippet. (GuGuss)
* `#5620 <https://github.com/symfony/symfony-docs/pull/5620>`_ Changed the recommendation about the LICENSE file for third-party bundles (javiereguiluz)
* `#5617 <https://github.com/symfony/symfony-docs/pull/5617>`_ Add Body tag to see the web debug toolbar (rmed19)
* `#5594 <https://github.com/symfony/symfony-docs/pull/5594>`_ Missing --no-interaction flag? (alexwybraniec)
* `#5613 <https://github.com/symfony/symfony-docs/pull/5613>`_ Remove unneeded backtick (fabschurt)
* `#5622 <https://github.com/symfony/symfony-docs/pull/5622>`_ typo fix in pre authenticated (Maxime Douailin)
* `#5624 <https://github.com/symfony/symfony-docs/pull/5624>`_ the_architecture: Fix syntax error (kainjow)
* `#5609 <https://github.com/symfony/symfony-docs/pull/5609>`_ Add a missing backtick (fabschurt)
* `#5312 <https://github.com/symfony/symfony-docs/pull/5312>`_ Some fixes for bundle best practices (WouterJ)
* `#5601 <https://github.com/symfony/symfony-docs/pull/5601>`_ Update lazy_services.rst (baziak3)
* `#5591 <https://github.com/symfony/symfony-docs/pull/5591>`_ Update templating.rst: lint:twig instead of twig:lint in 2.7 (alexwybraniec)


July, 2015
----------

New Documentation
~~~~~~~~~~~~~~~~~

* `#5374 <https://github.com/symfony/symfony-docs/pull/5374>`_ Remove deprecated parameters (norkunas)
* `#5533 <https://github.com/symfony/symfony-docs/pull/5533>`_ Replace Capifony with Capistrano/symfony (mojzis)
* `#5543 <https://github.com/symfony/symfony-docs/pull/5543>`_ Add deprecation notice to "choice_list" option of ChoiceType (XitasoChris)
* `#5521 <https://github.com/symfony/symfony-docs/pull/5521>`_ [Cookbook][WebServer] #5504 add a tip for the --force option (vincentaubert)
* `#5516 <https://github.com/symfony/symfony-docs/pull/5516>`_ Added a note about session data size in PdoSessionHandler (javiereguiluz)
* `#5499 <https://github.com/symfony/symfony-docs/pull/5499>`_ The "property" option of DoctrineType was deprecated. (XWB)
* `#5491 <https://github.com/symfony/symfony-docs/pull/5491>`_ added composer info (OskarStark)
* `#5478 <https://github.com/symfony/symfony-docs/pull/5478>`_ Add cookbook article for using MongoDB to store session data (stevenmusumeche)
* `#5472 <https://github.com/symfony/symfony-docs/pull/5472>`_ Added a tip about hashing the result of nextBytes() (javiereguiluz)
* `#5458 <https://github.com/symfony/symfony-docs/pull/5458>`_ HTML5 range documentation (harikt)
* `#5453 <https://github.com/symfony/symfony-docs/pull/5453>`_ Cleanup security voters cookbook recipes (WouterJ)
* `#5444 <https://github.com/symfony/symfony-docs/pull/5444>`_ Documented the "auto_alias" feature (javiereguiluz)
* `#5201 <https://github.com/symfony/symfony-docs/pull/5201>`_ [Book][Routing] Add example about how to match multiple methods (xelaris)
* `#5430 <https://github.com/symfony/symfony-docs/pull/5430>`_ Pr/5085 (sjagr, javiereguiluz)
* `#5456 <https://github.com/symfony/symfony-docs/pull/5456>`_ Completely re-reading the data transformers chapter (weaverryan)
* `#5426 <https://github.com/symfony/symfony-docs/pull/5426>`_ Documented the checkDNS option of the Url validator (saro0h, javiereguiluz)
* `#5333 <https://github.com/symfony/symfony-docs/pull/5333>`_ [FrameworkBundle] Update serializer configuration reference (dunglas)
* `#5424 <https://github.com/symfony/symfony-docs/pull/5424>`_ Integrate the "Create your own framework" tutorial (fabpot, lyrixx, jdreesen, catchamonkey, gnugat, andreia, Arnaud Kleinpeter, willdurand, amitayh, nanocom, hrbonz, Pedro Gimenez, ubick, dirkaholic, bamarni, revollat, javiereguiluz)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

* `#5567 <https://github.com/symfony/symfony-docs/pull/5567>`_ Change Sql Field name because it's reserved (rmed19)
* `#5528 <https://github.com/symfony/symfony-docs/pull/5528>`_ [reate_framework] Fix mock $matcher (kenjis)
* `#5501 <https://github.com/symfony/symfony-docs/pull/5501>`_ Fix typo in url for PHPUnit test coverage report (TrueGit)
* `#5501 <https://github.com/symfony/symfony-docs/pull/5501>`_ Fix typo in url for PHPUnit test coverage report (TrueGit)
* `#5461 <https://github.com/symfony/symfony-docs/pull/5461>`_ Rework quick tour big picture (smatejic, DQNEO, xabbuh)
* `#5488 <https://github.com/symfony/symfony-docs/pull/5488>`_ fix #5487 (emillosanti)
* `#5496 <https://github.com/symfony/symfony-docs/pull/5496>`_ Security voters fixes (german.bortoli)
* `#5424 <https://github.com/symfony/symfony-docs/pull/5424>`_ Integrate the "Create your own framework" tutorial (fabpot, lyrixx, jdreesen, catchamonkey, gnugat, andreia, Arnaud Kleinpeter, willdurand, amitayh, nanocom, hrbonz, Pedro Gimenez, ubick, dirkaholic, bamarni, revollat, javiereguiluz)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

* `#5575 <https://github.com/symfony/symfony-docs/pull/5575>`_ Move some articles from wrong sections (sylvaincombes, WouterJ)
* `#5580 <https://github.com/symfony/symfony-docs/pull/5580>`_ Additional User check in voter class (weaverryan)
* `#5573 <https://github.com/symfony/symfony-docs/pull/5573>`_ fix YAML syntax highlighting (xabbuh)
* `#5564 <https://github.com/symfony/symfony-docs/pull/5564>`_ Improve and simplify the contributing instructions about tests (javiereguiluz)
* `#5498 <https://github.com/symfony/symfony-docs/pull/5498>`_ [WIP] Added caution notes about the deprecation of container scopes (javiereguiluz)
* `#5550 <https://github.com/symfony/symfony-docs/pull/5550>`_ [docbot] Reviewed some component chapters (WouterJ)
* `#5556 <https://github.com/symfony/symfony-docs/pull/5556>`_ Fix typo Esi in part create framework (nicolasdewez)
* `#5568 <https://github.com/symfony/symfony-docs/pull/5568>`_ [Create Framework] Fix extract calls (replaces #5522) (kenjis)
* `#5548 <https://github.com/symfony/symfony-docs/pull/5548>`_ use the include() Twig function instead of the tag (xabbuh)
* `#5542 <https://github.com/symfony/symfony-docs/pull/5542>`_ [Cookbook][Email] add missing versionadded directive (xabbuh)
* `#5476 <https://github.com/symfony/symfony-docs/pull/5476>`_ [Cookbook][Security] some additional tweaks for the voter cookbook (xabbuh)
* `#5413 <https://github.com/symfony/symfony-docs/pull/5413>`_ Fix doc about deprecations policy (nicolas-grekas)
* `#5557 <https://github.com/symfony/symfony-docs/pull/5557>`_ [2.3] [Contributing] Added note about empty returns (phansys)
* `#5492 <https://github.com/symfony/symfony-docs/pull/5492>`_ updated tree for front controller (OskarStark)
* `#5536 <https://github.com/symfony/symfony-docs/pull/5536>`_ Removed reference to remove HTTPS off from nginx configuration (wjzijderveld)
* `#5545 <https://github.com/symfony/symfony-docs/pull/5545>`_ Misc. improvements in the Console component introduction (javiereguiluz)
* `#5512 <https://github.com/symfony/symfony-docs/pull/5512>`_ [Cookbook] Backport PSR-7 bridge docs to 2.3 (dunglas, weaverryan)
* `#5494 <https://github.com/symfony/symfony-docs/pull/5494>`_ updated tree (OskarStark)
* `#5490 <https://github.com/symfony/symfony-docs/pull/5490>`_ changed headline (OskarStark)
* `#5479 <https://github.com/symfony/symfony-docs/pull/5479>`_ Update http-foundation.rst (jezemery)
* `#5552 <https://github.com/symfony/symfony-docs/pull/5552>`_ rename $input to $greetInput (Xosofox)
* `#5544 <https://github.com/symfony/symfony-docs/pull/5544>`_ [components][expression_language] Fix the wrong constructor for SerializedParsedExpression (zerustech)
* `#5537 <https://github.com/symfony/symfony-docs/pull/5537>`_ Update design patter of Event Dispatcher (almacbe)
* `#5546 <https://github.com/symfony/symfony-docs/pull/5546>`_ A bunch of doc fixes again (WouterJ)
* `#5486 <https://github.com/symfony/symfony-docs/pull/5486>`_ review all Security code blocks (xabbuh)
* `#5538 <https://github.com/symfony/symfony-docs/pull/5538>`_ Update email.rst (TisLars)
* `#5529 <https://github.com/symfony/symfony-docs/pull/5529>`_ [Cookbook][upload_file] Fix :methods: to remove doubled braces (bicpi)
* `#5455 <https://github.com/symfony/symfony-docs/pull/5455>`_ Improve travis build speed (WouterJ)
* `#5442 <https://github.com/symfony/symfony-docs/pull/5442>`_ Improved the explanation about the verbosity levels of the console (javiereguiluz)
* `#5523 <https://github.com/symfony/symfony-docs/pull/5523>`_ Custom voter example, fix missing curly brace (snroki)
* `#5524 <https://github.com/symfony/symfony-docs/pull/5524>`_ TYPO: missing closing parantheses of the array (listerical85)
* `#5519 <https://github.com/symfony/symfony-docs/pull/5519>`_ Prepare Platform.sh configuration files. (GuGuss)
* `#5443 <https://github.com/symfony/symfony-docs/pull/5443>`_ Added a note about the implementation of the verbosity semantic methods (javiereguiluz)
* `#5518 <https://github.com/symfony/symfony-docs/pull/5518>`_ Minor grammar fix. (maxolasersquad)
* `#5520 <https://github.com/symfony/symfony-docs/pull/5520>`_ Fix RST (kenjis)
* `#5429 <https://github.com/symfony/symfony-docs/pull/5429>`_ Promote Symfony's builtin serializer instead of JMS (javiereguiluz)
* `#5427 <https://github.com/symfony/symfony-docs/pull/5427>`_ Cookbook grammar and style fixes (frne, javiereguiluz)
* `#5505 <https://github.com/symfony/symfony-docs/pull/5505>`_ [Cookbook][Form] some tweaks to the data transformers chapter (xabbuh)
* `#5352 <https://github.com/symfony/symfony-docs/pull/5352>`_ Update http_fundamentals.rst (wouthoekstra)
* `#5471 <https://github.com/symfony/symfony-docs/pull/5471>`_ Updated the Symfony Versions Roadmap image (javiereguiluz)
* `#5511 <https://github.com/symfony/symfony-docs/pull/5511>`_ [HttpKernel] Fix use statement (dunglas)
* `#5510 <https://github.com/symfony/symfony-docs/pull/5510>`_ [PSR-7] Fix Diactoros link (dunglas)
* `#5506 <https://github.com/symfony/symfony-docs/pull/5506>`_ Fixes small typo in data transformers cookbook (catchamonkey)
* `#5425 <https://github.com/symfony/symfony-docs/pull/5425>`_ Added a caution note about invoking other commands (kix, javiereguiluz)
* `#5367 <https://github.com/symfony/symfony-docs/pull/5367>`_ Split Security into Authentication & Authorization (iltar)
* `#5485 <https://github.com/symfony/symfony-docs/pull/5485>`_ Fix invalid phpunit URLs (norkunas)
* `#5473 <https://github.com/symfony/symfony-docs/pull/5473>`_ --dev is default and causes a warning (DQNEO)
* `#5474 <https://github.com/symfony/symfony-docs/pull/5474>`_ typo in components/translation/instruction.rst (beesofts)


June, 2015
----------

New Documentation
~~~~~~~~~~~~~~~~~

* `#5423 <https://github.com/symfony/symfony-docs/pull/5423>`_ [Security] add & update doc entries on AbstractVoter implementation (Inoryy, javiereguiluz)
* `#5409 <https://github.com/symfony/symfony-docs/pull/5409>`_ [Reference] document new Doctrine APC cache service (xabbuh)
* `#5401 <https://github.com/symfony/symfony-docs/pull/5401>`_ Added some more docs about the remember me feature (WouterJ)
* `#5384 <https://github.com/symfony/symfony-docs/pull/5384>`_ Added information about the new date handling in the comparison constraints and Range (webmozart, javiereguiluz)
* `#5382 <https://github.com/symfony/symfony-docs/pull/5382>`_ Added support for standard Forwarded header (tony-co, javiereguiluz)
* `#5361 <https://github.com/symfony/symfony-docs/pull/5361>`_ Document security.switch_user event (Rvanlaak)
* `#5332 <https://github.com/symfony/symfony-docs/pull/5332>`_ [Serializer] ObjectNormalizer, object_to_populate doc. Minor enhancements. (dunglas)
* `#5335 <https://github.com/symfony/symfony-docs/pull/5335>`_ [Serializer] Updated the cookbook. (dunglas)
* `#5313 <https://github.com/symfony/symfony-docs/pull/5313>`_ Documented the overridden form options (javiereguiluz)
* `#5360 <https://github.com/symfony/symfony-docs/pull/5360>`_ [Serializer] Array Denormalization (derrabus)
* `#5307 <https://github.com/symfony/symfony-docs/pull/5307>`_ Update data_transformers.rst (zebba)
* `#5186 <https://github.com/symfony/symfony-docs/pull/5186>`_ Added a new article about using/installing unstable Symfony versions (javiereguiluz)
* `#5166 <https://github.com/symfony/symfony-docs/pull/5166>`_ Proposed a new article about using pure PHP libraries with Assetic (javiereguiluz)
* `#5416 <https://github.com/symfony/symfony-docs/pull/5416>`_ fix for Symfony 2.7 (DQNEO)
* `#5014 <https://github.com/symfony/symfony-docs/pull/5014>`_ Updated the best practices article for reusable bundles (javiereguiluz)
* `#5435 <https://github.com/symfony/symfony-docs/pull/5435>`_ Added information about the four sub-components of Security component (javiereguiluz)
* `#5368 <https://github.com/symfony/symfony-docs/pull/5368>`_ added examples for squashing (OskarStark)
* `#5428 <https://github.com/symfony/symfony-docs/pull/5428>`_ Improved description of choice_list option (adamziel, javiereguiluz)
* `#5336 <https://github.com/symfony/symfony-docs/pull/5336>`_ Adding a paragraph about updating multiple packages during an update (weaverryan)
* `#5375 <https://github.com/symfony/symfony-docs/pull/5375>`_ Added a new cookbook about file uploading (javiereguiluz)
* `#5385 <https://github.com/symfony/symfony-docs/pull/5385>`_ Added a note about the need to require Composer's autoload file (javiereguiluz)
* `#5386 <https://github.com/symfony/symfony-docs/pull/5386>`_ Re-write of Page Creation (weaverryan)
* `#5355 <https://github.com/symfony/symfony-docs/pull/5355>`_ Added a mention to the Symfony Demo application (javiereguiluz)
* `#5331 <https://github.com/symfony/symfony-docs/pull/5331>`_ [PSR-7] Bridge documentation (dunglas)
* `#5373 <https://github.com/symfony/symfony-docs/pull/5373>`_ Added mentions to some popular (and useful) Symfony bundles (javiereguiluz)
* `#4354 <https://github.com/symfony/symfony-docs/pull/4354>`_ [WCM] Added depreciation note for the cascade_validation constraint (peterrehm)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

* `#5415 <https://github.com/symfony/symfony-docs/pull/5415>`_ Updating for AppBundle and purposefully \*not\* doing work on configure (weaverryan)
* `#5407 <https://github.com/symfony/symfony-docs/pull/5407>`_ Change PhpStormOpener to PhpStormProtocol (King2500)
* `#5450 <https://github.com/symfony/symfony-docs/pull/5450>`_ Fixing "Undefined method" error in code example (nebkam)
* `#5454 <https://github.com/symfony/symfony-docs/pull/5454>`_ Changed dump() to var_dump() (WouterJ)
* `#5417 <https://github.com/symfony/symfony-docs/pull/5417>`_ Add use statement for InputDefinition (harikt)
* `#5420 <https://github.com/symfony/symfony-docs/pull/5420>`_ Fix invalid method name (bocharsky-bw)
* `#5431 <https://github.com/symfony/symfony-docs/pull/5431>`_ Updated the code to display flash messages (aykin, javiereguiluz)
* `#5418 <https://github.com/symfony/symfony-docs/pull/5418>`_ Import Psr LogLevel (harikt)
* `#5438 <https://github.com/symfony/symfony-docs/pull/5438>`_ Fixed 404 at Configuring Sessions and Save Handlers (2.3 branch) (suzuki)
* `#5412 <https://github.com/symfony/symfony-docs/pull/5412>`_ Update serializer.rst (mantulo)
* `#5397 <https://github.com/symfony/symfony-docs/pull/5397>`_ Escape backslash in error message (WouterJ)
* `#5379 <https://github.com/symfony/symfony-docs/pull/5379>`_ [Cookbook][Console] don't use BufferedOutput on Symfony 2.3 (xabbuh)
* `#5400 <https://github.com/symfony/symfony-docs/pull/5400>`_ Fix after install URL and new photo since AcmeDemoBundle is not part … (smatejic)
* `#5350 <https://github.com/symfony/symfony-docs/pull/5350>`_ [Form][2.3] fix `validation_groups` typos (craue)
* `#5358 <https://github.com/symfony/symfony-docs/pull/5358>`_ Fix typo in description (martyshka)
* `#5356 <https://github.com/symfony/symfony-docs/pull/5356>`_ [Form] Fixed typo about _token field name for CSRF protection (JMLamodiere)
* `#5362 <https://github.com/symfony/symfony-docs/pull/5362>`_ Fix invalid endtag (norkunas)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

* `#5467 <https://github.com/symfony/symfony-docs/pull/5467>`_ use HTTPS for links to symfony.com (xabbuh)
* `#5466 <https://github.com/symfony/symfony-docs/pull/5466>`_ data transformers cookbook service definition typo (intrepion)
* `#5414 <https://github.com/symfony/symfony-docs/pull/5414>`_ Rewrite sentence about fingers crossed handler action level (snoek09)
* `#5402 <https://github.com/symfony/symfony-docs/pull/5402>`_ [Contributing] [Standards] Added entry for Yoda conditions (phansys)
* `#5369 <https://github.com/symfony/symfony-docs/pull/5369>`_ Remove the Propel book chapter and explain why we do that (javiereguiluz)
* `#5460 <https://github.com/symfony/symfony-docs/pull/5460>`_ Finish #5291: Bootstrap form theme and checkboxes (anacicconi, WouterJ)
* `#5457 <https://github.com/symfony/symfony-docs/pull/5457>`_ [Cookbook][Assetic] complete a sentence (xabbuh)
* `#5398 <https://github.com/symfony/symfony-docs/pull/5398>`_ Quick review of the remember me article (WouterJ)
* `#5399 <https://github.com/symfony/symfony-docs/pull/5399>`_ Quick review of Form login chapter (WouterJ)
* `#5403 <https://github.com/symfony/symfony-docs/pull/5403>`_ [Contributing] [Standards] Added entry for identical comparison (phansys)
* `#5392 <https://github.com/symfony/symfony-docs/pull/5392>`_ Wrap the table creation inside the class extending Command, so users … (harikt)
* `#5378 <https://github.com/symfony/symfony-docs/pull/5378>`_ [Cookbook][Controller] use the jinja lexer to render Twig code (xabbuh)
* `#5421 <https://github.com/symfony/symfony-docs/pull/5421>`_ Update the name of the branch for new BC features (Restless-ET)
* `#5441 <https://github.com/symfony/symfony-docs/pull/5441>`_ [Contributing] remove mailing list and forum references (xabbuh)
* `#5433 <https://github.com/symfony/symfony-docs/pull/5433>`_ Warn users of older PHP versions Crawler might not decode HTML entities properly (jakzal, javiereguiluz)
* `#5293 <https://github.com/symfony/symfony-docs/pull/5293>`_ [Translation] Add note about how to override translation in chi… (zebba)
* `#5290 <https://github.com/symfony/symfony-docs/pull/5290>`_ Overriding 3rd party bundles (anacicconi)
* `#5242 <https://github.com/symfony/symfony-docs/pull/5242>`_ Update load_balancer_reverse_proxy.rst (urg)
* `#5381 <https://github.com/symfony/symfony-docs/pull/5381>`_ remove Yoda condition (greg0ire)
* `#5452 <https://github.com/symfony/symfony-docs/pull/5452>`_ [#5388] change echo and print in examples (snoek09)
* `#5451 <https://github.com/symfony/symfony-docs/pull/5451>`_ [#5388] change echo and print in examples (snoek09)
* `#3782 <https://github.com/symfony/symfony-docs/pull/3782>`_ [Form] Deprecate read_only option (snoob)
* `#5432 <https://github.com/symfony/symfony-docs/pull/5432>`_ removed squashing stuff. fixes #5368 (OskarStark)
* `#5383 <https://github.com/symfony/symfony-docs/pull/5383>`_ Reword a paragraph about service configurations (richardudovich)
* `#5389 <https://github.com/symfony/symfony-docs/pull/5389>`_ Updates to security.rst (HexTitan)
* `#5408 <https://github.com/symfony/symfony-docs/pull/5408>`_ typo (larsborn)
* `#5406 <https://github.com/symfony/symfony-docs/pull/5406>`_ Update yaml_format.rst (marcel-burkhard)
* `#5396 <https://github.com/symfony/symfony-docs/pull/5396>`_ [Cookbook][Bundles] fix a typo (xabbuh)
* `#5288 <https://github.com/symfony/symfony-docs/pull/5288>`_ Constraints - empty strings and null values (anacicconi)
* `#5284 <https://github.com/symfony/symfony-docs/pull/5284>`_ Split advanced container configuration article (WouterJ)
* `#5342 <https://github.com/symfony/symfony-docs/pull/5342>`_ [Cookbook][Bundles] clarify bundle installation instructions (xabbuh)
* `#5321 <https://github.com/symfony/symfony-docs/pull/5321>`_ Use the reserved domains example.com and example.org (javiereguiluz)
* `#5095 <https://github.com/symfony/symfony-docs/pull/5095>`_ Reviewed the Bundles cookbook articles (javiereguiluz)
* `#4947 <https://github.com/symfony/symfony-docs/pull/4947>`_ [Components][ClassLoader] remove DebugClassLoader (xabbuh)
* `#5365 <https://github.com/symfony/symfony-docs/pull/5365>`_ Finish #4967: Code style standardization on form type options (mimol91)
* `#5034 <https://github.com/symfony/symfony-docs/pull/5034>`_ Update the_big_picture.rst (oldskool)
* `#5351 <https://github.com/symfony/symfony-docs/pull/5351>`_ [Finder] minor CS fix (dunglas)
* `#5344 <https://github.com/symfony/symfony-docs/pull/5344>`_ [Book] Finish #4776 and #4782 (ifdattic)
* `#5348 <https://github.com/symfony/symfony-docs/pull/5348>`_ Fix list format (bicpi)
* `#5357 <https://github.com/symfony/symfony-docs/pull/5357>`_ [Form] Replace deprecated form_enctype by form_start (JMLamodiere)
* `#5359 <https://github.com/symfony/symfony-docs/pull/5359>`_ Bumped version of proxy manager to stable release (peterrehm)


May, 2015
---------

New Documentation
~~~~~~~~~~~~~~~~~

* `#5329 <https://github.com/symfony/symfony-docs/pull/5329>`_ Adding a new entry about deprecation warnings (weaverryan)
* `#4604 <https://github.com/symfony/symfony-docs/pull/4604>`_ Making the channel handler more useful by showing it on the prod environment (weaverryan)
* `#5155 <https://github.com/symfony/symfony-docs/pull/5155>`_ Documented upgrading path for a major version (WouterJ)
* `#5127 <https://github.com/symfony/symfony-docs/pull/5127>`_ [VarDumper] Add doc for assertDump\* assertions (nicolas-grekas)
* `#5137 <https://github.com/symfony/symfony-docs/pull/5137>`_ Added a note about the rotating_file monolog handler (javiereguiluz)
* `#5283 <https://github.com/symfony/symfony-docs/pull/5283>`_ [BestPractices] restructured text format for the installation instructions template (xabbuh)
* `#5298 <https://github.com/symfony/symfony-docs/pull/5298>`_ Completed framework config (WouterJ)
* `#5255 <https://github.com/symfony/symfony-docs/pull/5255>`_ [Cookbook] Use configured user provider instead of injection (mvar)
* `#5216 <https://github.com/symfony/symfony-docs/pull/5216>`_ [Cookbook] [Deployment] Added note about Nginx (phansys)
* `#5169 <https://github.com/symfony/symfony-docs/pull/5169>`_ Removed synchronized services from Symfony 2.7 docs (javiereguiluz)
* `#5117 <https://github.com/symfony/symfony-docs/pull/5117>`_ Complete review of the "Customize Error Pages" cookbook article (javiereguiluz)
* `#5115 <https://github.com/symfony/symfony-docs/pull/5115>`_ Flesh out twig-template for custom data-collector (Darien Hager)
* `#5106 <https://github.com/symfony/symfony-docs/pull/5106>`_ [VarDumper] upgrade doc to 2.7 wither interface (nicolas-grekas)
* `#4728 <https://github.com/symfony/symfony-docs/pull/4728>`_ Add Session Cache Limiting section for NativeSessionStorage (mrclay)
* `#4084 <https://github.com/symfony/symfony-docs/pull/4084>`_ [Book][Forms] describe the allow_extra_fields form option (xabbuh)
* `#5294 <https://github.com/symfony/symfony-docs/pull/5294>`_ Tweaks to bower entry - specifically committing deps (weaverryan)
* `#5062 <https://github.com/symfony/symfony-docs/pull/5062>`_ Cookbook about Command in Application with AnsiToHtml (Rvanlaak)
* `#4901 <https://github.com/symfony/symfony-docs/pull/4901>`_ Removed the Internals chapter from the Symfony book (javiereguiluz)
* `#4807 <https://github.com/symfony/symfony-docs/pull/4807>`_ [2.7] bumped min PHP version to 5.3.9 (xelaris)
* `#4790 <https://github.com/symfony/symfony-docs/pull/4790>`_ [Cookbook][Routing] Update custom_route_loader.rst (xelaris)
* `#5159 <https://github.com/symfony/symfony-docs/pull/5159>`_ Added an article explaining how to use Bower in Symfony (WouterJ)
* `#4700 <https://github.com/symfony/symfony-docs/pull/4700>`_ add informations how to create a custom doctrine mapping (timglabisch)
* `#4675 <https://github.com/symfony/symfony-docs/pull/4675>`_ [Serializer] Doc for groups support (dunglas)
* `#5164 <https://github.com/symfony/symfony-docs/pull/5164>`_ Added information about the Symfony Demo application (javiereguiluz)
* `#5100 <https://github.com/symfony/symfony-docs/pull/5100>`_ Change MySQL UTF-8 examples to use utf8mb4 (DHager, Darien Hager)
* `#5088 <https://github.com/symfony/symfony-docs/pull/5088>`_ [Cookbook] Custom compile steps on Heroku (bicpi)
* `#5005 <https://github.com/symfony/symfony-docs/pull/5005>`_ Renamed precision option to scale (WouterJ)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

* `#5324 <https://github.com/symfony/symfony-docs/pull/5324>`_ 5259 improve 'Testing Documentation' in contributing guide  (snoek09)
* `#5328 <https://github.com/symfony/symfony-docs/pull/5328>`_ Update create_form_type_extension.rst (jackdelin)
* `#5305 <https://github.com/symfony/symfony-docs/pull/5305>`_ [BestPractices][Security] revert #5271 on the 2.6 branch (xabbuh)
* `#5251 <https://github.com/symfony/symfony-docs/pull/5251>`_ [Cookbook][Controller] replace docs for removed `forward()` method (xabbuh)
* `#5237 <https://github.com/symfony/symfony-docs/pull/5237>`_ Update authentication.rst (taavit)
* `#5299 <https://github.com/symfony/symfony-docs/pull/5299>`_ Command controller tweaks to #5062 (weaverryan)
* `#5297 <https://github.com/symfony/symfony-docs/pull/5297>`_ Kernel Events Proofreading after #4901 (weaverryan)
* `#5296 <https://github.com/symfony/symfony-docs/pull/5296>`_ Fix link to Zend Soap (peterkokot)
* `#5266 <https://github.com/symfony/symfony-docs/pull/5266>`_ Update heroku.rst (nickbyfleet)
* `#5270 <https://github.com/symfony/symfony-docs/pull/5270>`_ Use OptionsResolver (tacman)
* `#5271 <https://github.com/symfony/symfony-docs/pull/5271>`_ Fix nonexistent controller method (amansilla)
* `#4615 <https://github.com/symfony/symfony-docs/pull/4615>`_ Update NotBlank to reflect the actual validation (DRvanR)
* `#5249 <https://github.com/symfony/symfony-docs/pull/5249>`_ [security][form login] fix translations for the security messages. (aitboudad)
* `#5247 <https://github.com/symfony/symfony-docs/pull/5247>`_ [2.7] [Serializer] fixes the order of the Serializer constructor arguments. (hhamon)
* `#5220 <https://github.com/symfony/symfony-docs/pull/5220>`_ Fix example namespace (lepiaf)
* `#5203 <https://github.com/symfony/symfony-docs/pull/5203>`_ Order has one param without spaces (carlosbuenosvinos)
* `#4273 <https://github.com/symfony/symfony-docs/pull/4273>`_ - fix doctrine version in How to Provide Model Classes for several Doctrine Implementations cookbook

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

* `#5343 <https://github.com/symfony/symfony-docs/pull/5343>`_ [Reference][Forms] reorder index to match the description order (xabbuh)
* `#5309 <https://github.com/symfony/symfony-docs/pull/5309>`_ [Cookbook][Controller] few tweaks to the error pages article (xabbuh)
* `#5311 <https://github.com/symfony/symfony-docs/pull/5311>`_ Moved sections to be equal to index list (WouterJ)
* `#5326 <https://github.com/symfony/symfony-docs/pull/5326>`_ Fixed code intentation (lyrixx)
* `#5327 <https://github.com/symfony/symfony-docs/pull/5327>`_ [Platform] Made things more obvious and copy/paste friendly (lyrixx)
* `#5338 <https://github.com/symfony/symfony-docs/pull/5338>`_ Text in index.html.twig for The Big Picture wrong (BT643)
* `#5341 <https://github.com/symfony/symfony-docs/pull/5341>`_ fixed typo and added additional hit for NullOutput() (kuldipem)
* `#5302 <https://github.com/symfony/symfony-docs/pull/5302>`_ Place DQL in front of QueryBuilder (alfonsomga)
* `#5276 <https://github.com/symfony/symfony-docs/pull/5276>`_ Better illustrate what the "user mistake" is. (diamondsea)
* `#5304 <https://github.com/symfony/symfony-docs/pull/5304>`_ Proofreading Javier's excellent updates - in some places, shortening some things (weaverryan)
* `#5263 <https://github.com/symfony/symfony-docs/pull/5263>`_ Let docbot review the form docs (WouterJ)
* `#5280 <https://github.com/symfony/symfony-docs/pull/5280>`_ Rebase #4633 (seangallavan)
* `#5241 <https://github.com/symfony/symfony-docs/pull/5241>`_ [Components][Form] apply some fixes to the Form events chapter (xabbuh)
* `#5233 <https://github.com/symfony/symfony-docs/pull/5233>`_ Improve Choice Validation Constraint Example (huebs)
* `#5228 <https://github.com/symfony/symfony-docs/pull/5228>`_ Clarify `query_builder` closure return type (kix)
* `#5165 <https://github.com/symfony/symfony-docs/pull/5165>`_ Minor changes to match the Symfony Demo reference application (javiereguiluz)
* `#5281 <https://github.com/symfony/symfony-docs/pull/5281>`_ store templates under app/Resources/views (xabbuh)
* `#5267 <https://github.com/symfony/symfony-docs/pull/5267>`_ fix infinity upper bound (xabbuh)
* `#5277 <https://github.com/symfony/symfony-docs/pull/5277>`_ always refer to getcomposer.org through HTTPS (xabbuh)
* `#4671 <https://github.com/symfony/symfony-docs/pull/4671>`_ consistent spelling (xabbuh)
* `#4255 <https://github.com/symfony/symfony-docs/pull/4255>`_ Updated autoload standard to PSR-4. (phansys)
* `#5278 <https://github.com/symfony/symfony-docs/pull/5278>`_ remove unnecessary code (karion)
* `#5262 <https://github.com/symfony/symfony-docs/pull/5262>`_ Update Routes in the Getting Started documentation (BT643)
* `#5178 <https://github.com/symfony/symfony-docs/pull/5178>`_ Usage of denyAccessUnlessGranted in the controller (94noni)
* `#5229 <https://github.com/symfony/symfony-docs/pull/5229>`_ Remove mention of \*.class parameters from conventions (jvasseur)
* `#5250 <https://github.com/symfony/symfony-docs/pull/5250>`_ [Cookbook][Logging] use straightforward instead of straigt forward (xabbuh)
* `#5257 <https://github.com/symfony/symfony-docs/pull/5257>`_ Let docbot review the constraint docs (WouterJ)
* `#5222 <https://github.com/symfony/symfony-docs/pull/5222>`_ Update service_container.rst (assoum891)
* `#5221 <https://github.com/symfony/symfony-docs/pull/5221>`_ Update Uglifyjs.rst (assoum891)
* `#5219 <https://github.com/symfony/symfony-docs/pull/5219>`_ Fix contradicting merging policy rules (lscholten)
* `#5217 <https://github.com/symfony/symfony-docs/pull/5217>`_ Update _payload-option.rst.inc (bvleur)
* `#5226 <https://github.com/symfony/symfony-docs/pull/5226>`_ Update http_cache.rst (assoum891)
* `#5238 <https://github.com/symfony/symfony-docs/pull/5238>`_ Fixed typo and removed outdated imports (nomack84)
* `#5240 <https://github.com/symfony/symfony-docs/pull/5240>`_ [Cookbook][Email] revert #4808 (xabbuh)


April, 2015
-----------

New Documentation
~~~~~~~~~~~~~~~~~

- `387ebc0 <https://github.com/symfony/symfony-docs/commit/387ebc0b84cb813f45f76161f3cfb81c38f9a6fa>`_ #5109 Improved the explanation about the "secret" configuration parameter (javiereguiluz)
- `cac0a9c <https://github.com/symfony/symfony-docs/commit/cac0a9cbf50e4d77822e792a5276a16b40f0ca3b>`_ #5207 Updated the cookbook about Composer installation (javiereguiluz)
- `b5dd5a1 <https://github.com/symfony/symfony-docs/commit/b5dd5a1f11a52c4bce3292e8bc7fabbfb4e5c148>`_ #5206 [Cookbook][Security] Replace deprecated csrf_provider service (xelaris)
- `99e2034 <https://github.com/symfony/symfony-docs/commit/99e2034d4272fe4921c3627c0d494b9b1df85e7f>`_ #5195 Add missing caching drivers (mhor)
- `b90c7cb <https://github.com/symfony/symfony-docs/commit/b90c7cbffda822f54b33d91e1da4c6b844bfe872>`_ #5078 [Cookbook] Add warning about Composer dev deps on Heroku (bicpi)
- `55730c4 <https://github.com/symfony/symfony-docs/commit/55730c4c0e619c3918d131c6cb910bd17ccc9a0b>`_ #5021 Explained the "Remember Me" firewall options (javiereguiluz)
- `45ba71b <https://github.com/symfony/symfony-docs/commit/45ba71b2b3eef0ab544b33888d47e19787552fa6>`_ #4811 Simplified some Symfony installation instructions (javiereguiluz)
- `c4a5661 <https://github.com/symfony/symfony-docs/commit/c4a56618bbf7f3d60c6e10e6029c60e185f31756>`_ #5060 Adds note on new validation files scanned in 2.7 (GromNaN)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `6641b4b <https://github.com/symfony/symfony-docs/commit/6641b4bea913d2e9ea24acb720bb3e52e3793341>`_ #5202 added missing tab (martinbertinat)
- `49f6b2a <https://github.com/symfony/symfony-docs/commit/49f6b2ac35be86ac5cbc215570362c0b7be9db2b>`_ #5211 Rebase #5182 (Balamung)
- `318bb8a <https://github.com/symfony/symfony-docs/commit/318bb8af38f5f1824295af1e2c2bafa01b35f438>`_ #5187 Fixing a bad bcrypt string using http://www.bcrypt-generator.com/ (weaverryan)
- `6fb2eea <https://github.com/symfony/symfony-docs/commit/6fb2eeae5abdf4f401c58755681f63232c104419>`_ #5162 Fix misplelled XliffFileLoader class in the Using Domains (Nicola Pietroluongo)
- `402b586 <https://github.com/symfony/symfony-docs/commit/402b586c49227b998447ad6b3fa82dcbeb9ca47b>`_ #5162 Fix misplelled XliffFileLoader class in the Using Message Domains (Nicola Pietroluongo)
- `8fc3d6c <https://github.com/symfony/symfony-docs/commit/8fc3d6c9bc33563fabe17230e600281d47b547e6>`_ #5149 Fixed loadUserByUsername method coding errors (Linas Merkevicius)
- `2a1d2bb <https://github.com/symfony/symfony-docs/commit/2a1d2bb3b3a97e5add310509ab38dd638caf177d>`_ #5153 [Book] app_dev with php built-in web server (manelselles)
- `c6e6d28 <https://github.com/symfony/symfony-docs/commit/c6e6d28659e7ae3f7b1ef5c50653b7f0cb8b7bf1>`_ #5061 Trim default is false in password field (raziel057)
- `5880f38 <https://github.com/symfony/symfony-docs/commit/5880f38e66683f67cecfa736105c03bccec707db>`_ #5126 Fix a typo in ProgressBar usage example (kamazee)
- `65c1669 <https://github.com/symfony/symfony-docs/commit/65c166967a148cdd45a6f305c8cb68f2dd452eae>`_ #5124 #3412 correct overridden option name of timezone (alexandr-kalenyuk)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `0b7f89b <https://github.com/symfony/symfony-docs/commit/0b7f89be099ffced22e20de8edef04d3faff8df1>`_ #4868 Remove horizontal scrollbar (ifdattic)
- `c166fdf <https://github.com/symfony/symfony-docs/commit/c166fdf9d77caedef16235ab8b6ed23ce8b59840>`_ #5212 Fixed typo. (pcky)
- `134268e <https://github.com/symfony/symfony-docs/commit/134268e595140c367a017e816b56972c9d91de99>`_ #5209 [Reference] Fix order of config blocks (xelaris)
- `c6dc4ea <https://github.com/symfony/symfony-docs/commit/c6dc4eac507c4aa89f9df2ec119e5b8e75d38ff2>`_ #5200 Added missing link in "Sections" (sfdumi)
- `8b25e6e <https://github.com/symfony/symfony-docs/commit/8b25e6e03d580447ece793572744be7e7243c355>`_ #5198 Link twig constant function (davidfuhr)
- `2d6d78c <https://github.com/symfony/symfony-docs/commit/2d6d78ccdfbe4584fa167c2d8f05bf1fe0ea756c>`_ #5194 Fix priority range values for event listeners. (chtipepere)
- `914345a <https://github.com/symfony/symfony-docs/commit/914345a2d767e2457ce34679cc17d0d09bd5ba0f>`_ #5191 Fixed inconsistency (jperovic)
- `c2d1f3d <https://github.com/symfony/symfony-docs/commit/c2d1f3db6ebf83e69d2eb47fa70ab6e2041d368a>`_ #5190 Change '.xliff' extensions to '.xlf' (xelaris)
- `32b874a <https://github.com/symfony/symfony-docs/commit/32b874aa2ba9fbcacfb416f6622964a51377bb58>`_ #5189 [Quick Tour] Fixed things found by the docbot (WouterJ)
- `20ac2a6 <https://github.com/symfony/symfony-docs/commit/20ac2a688c868fdf45c20ffe670bee9f196c4b3e>`_ #5174 [CookBook] [configuration_organization] Use $this->getRootDir() instead of __DIR__ (manelselles)
- `eacb71b <https://github.com/symfony/symfony-docs/commit/eacb71bef91b582c86871273335cd906ad1185ea>`_ #5173 Use $this->getRootDir() instead of __DIR__ (manelselles)
- `16e0849 <https://github.com/symfony/symfony-docs/commit/16e08492d4a5f51f67bd3f1db60a1c4cd1f48fa6>`_ #5184 Removing a section about Roles that I think has no real use-case (weaverryan)
- `2948d6e <https://github.com/symfony/symfony-docs/commit/2948d6ec3a18afc953ca7db8f54bf86e42784790>`_ #5185 Fix broken link in security chapter (iboved)
- `a4f290e <https://github.com/symfony/symfony-docs/commit/a4f290e060fce19f1837861b38c6956fbf839a78>`_ #5172 [Contributing][Code] add missing security advisories (xabbuh)
- `2b7ddcd <https://github.com/symfony/symfony-docs/commit/2b7ddcdc47cf304d43b22ccba4b4a54c559a485b>`_ #5167 Add version 2.8 to the release roadmap (Maks3w)
- `404d0b3 <https://github.com/symfony/symfony-docs/commit/404d0b3ae674c300b334276e8612ea1efe789a10>`_ #5161 Use correct Session namespace (JhonnyL)
- `c778178 <https://github.com/symfony/symfony-docs/commit/c778178707e8e3bce5a613c84ce01efc2407b678>`_ #5098 Reviewed Configuration cookbook articles (javiereguiluz)
- `d9e1690 <https://github.com/symfony/symfony-docs/commit/d9e169015d1a102165ced03efc36ea72c5abf011>`_ #5096 Reviewed Cache cookbook articles (javiereguiluz)
- `c40b618 <https://github.com/symfony/symfony-docs/commit/c40b6181812d23a32f6155c83066e6fa405b0747>`_ #5065 [Reference] fix code block order (xabbuh)
- `73ccc8b <https://github.com/symfony/symfony-docs/commit/73ccc8b4044b9b892ff70e8782a68eaeeab09573>`_ #5160 Update process.rst (sfdumi)
- `ab01d08 <https://github.com/symfony/symfony-docs/commit/ab01d0815bfa62eb678d75d43e8658d73d546b06>`_ #5141 Removed remaining setDefaultOptions usage (WouterJ)
- `0dc6204 <https://github.com/symfony/symfony-docs/commit/0dc620420293c8d969ca5f0a1e83ca02a80e7b25>`_ #5143 Rebased #4747 (ifdattic)
- `b467e23 <https://github.com/symfony/symfony-docs/commit/b467e23847b6340a14d5e3589431984f9d78a211>`_ #5147 Add missing word in bundles best practice description (jbafford)
- `bf1e44b <https://github.com/symfony/symfony-docs/commit/bf1e44bec5178dfbdea2a87d7af3fd50369f08a1>`_ #5150 [Cookbook] Update serializer.rst (xelaris)
- `bec695a <https://github.com/symfony/symfony-docs/commit/bec695a0b079ed2936e782495f008640dd44a37c>`_ #5144 [Cookbook][Deployment] fix references to Platform.sh documentation (xabbuh)
- `b73346a <https://github.com/symfony/symfony-docs/commit/b73346a3c5b1f04781641f65c896950cf37f0967>`_ #5145 Update introduction.rst (cafferata)
- `7f39e87 <https://github.com/symfony/symfony-docs/commit/7f39e87e9a1cdd1998eddb36920f04f5ebeff962>`_ #5073 [Cookbook] Add note about possible 404 error on Heroku (bicpi)
- `fbdc177 <https://github.com/symfony/symfony-docs/commit/fbdc17780920ce0862171359547485be7feddd70>`_ #5057 Add a link to Multiple User Providers (thePanz)
- `526c880 <https://github.com/symfony/symfony-docs/commit/526c880f121f85b4bedddb7dfbcfe6d293ac1fca>`_ #5132 [Components][DependencyInjection] fix wrong disable of factories (sstok)
- `b19ded6 <https://github.com/symfony/symfony-docs/commit/b19ded62ce04d8a00b3c8619545bf7cde8476392>`_ #5130 [Cookbook][Security] Fiyed typo in entity_provider.rst (althaus)
- `87c39b7 <https://github.com/symfony/symfony-docs/commit/87c39b723df086b520792d72b6b68339920087ce>`_ #5129 Fix to Twig asset function packageName argument (ockcyp)
- `1d443c0 <https://github.com/symfony/symfony-docs/commit/1d443c018bf6a3988d667f068da996a76ed5b7f0>`_ #5128 [VarDumper] little optim (lyrixx)

March, 2015
-----------

New Documentation
~~~~~~~~~~~~~~~~~

- `25d2f54 <https://github.com/symfony/symfony-docs/commit/25d2f54b344923bfc5cda349c7293f0ff764989e>`_ #4958 Add Twitter Bootstrap form theme example (bocharsky-bw)
- `8ac6fed <https://github.com/symfony/symfony-docs/commit/8ac6fedb4223e2e0d1ace5085dac9c8afacc1d1b>`_ #5093 Added a new best practices for custom form field types (javiereguiluz)
- `50cd620 <https://github.com/symfony/symfony-docs/commit/50cd62050e752cc6a0d8e52b36c8c58dd73fc652>`_ #4892 Add possible values for widget_type (XitasoChris)
- `ade7ba4 <https://github.com/symfony/symfony-docs/commit/ade7ba4af112c9c7d7ee34e3cc2f09de01d5d578>`_ #4842 Add meaning of yellow icon for number of queries (XitasoChris)
- `fa10f1c <https://github.com/symfony/symfony-docs/commit/fa10f1c695b233bb072ee54e4aab2c19a25eb56c>`_ #5083 Proofreading and updating entity_provider for readability (weaverryan)
- `e36faec <https://github.com/symfony/symfony-docs/commit/e36faeca4f4a23a9196162ec44fcee4432908cfa>`_ #5099 Rebase of #4989 (solazs, weaverryan)
- `65dd03b <https://github.com/symfony/symfony-docs/commit/65dd03bd60665b2c9305d5306427842c16c4696e>`_ #5056   [Reference] Add missing option delivery_whitelist (TerjeBr)
- `c2f21e6 <https://github.com/symfony/symfony-docs/commit/c2f21e622a12b37031d99431efcac4b0c14d5d00>`_ #5050 [OptionsResolver] Fixed deprecated functionality usage (WouterJ)
- `3405c42 <https://github.com/symfony/symfony-docs/commit/3405c42180a5302a49c5f5e2cb1676df665a226d>`_ #5046 Rebased "add shortcut methods" (Cydonia7, WouterJ)
- `b138a50 <https://github.com/symfony/symfony-docs/commit/b138a50ef1b6d57baab912e8161ffcbf20a69ed0>`_ #5032 Minor improvement for symfony-installer with LTS (94noni)
- `5261e79 <https://github.com/symfony/symfony-docs/commit/5261e79f47ed3c3cf6d0d2f04a179b30978e83a8>`_ #5033 adding table for controller as a service (dbu)
- `d6c0cb7 <https://github.com/symfony/symfony-docs/commit/d6c0cb74701cab7fdc05596083ed34fa6c3063df>`_ #5028 Finish #4308: Documentation for the new PropertyNormalizer (mnapoli, WouterJ)
- `ccabc95 <https://github.com/symfony/symfony-docs/commit/ccabc95e3571785445822498ebf13c7b5a0811e4>`_ #5023 Added a note about data transformers not being applied with inherit_data option set (javiereguiluz)
- `65a33c0 <https://github.com/symfony/symfony-docs/commit/65a33c0b7020067bd82a78339e2b3682e5e1b898>`_ #5020 Added a commented config useful when you use symlinks (javiereguiluz)
- `1dbed80 <https://github.com/symfony/symfony-docs/commit/1dbed80d4672b545280637c65f5e589963469d89>`_ #5017 Added a note about the server_version DBAL option (javiereguiluz)
- `86abdde <https://github.com/symfony/symfony-docs/commit/86abddea8a0e320666d184b50f9b082027c8fa1d>`_ #5015 Added an example about how to get the impersonating user object (javiereguiluz)
- `c6db525 <https://github.com/symfony/symfony-docs/commit/c6db525b0a28cf79bef7175b609388250787a7be>`_ #5010 Added a note about the Symfony versions affected by ICU problems (javiereguiluz)
- `3c76623 <https://github.com/symfony/symfony-docs/commit/3c76623bd1053503f70d14c68a24daf1455af2c8>`_ #5008 Added a note about how to enable http_method_override for caching kernels (javiereguiluz)
- `22eee86 <https://github.com/symfony/symfony-docs/commit/22eee86b18ca2c38080cc117b2a78e5ed0e7cc6c>`_ #4987 Added the documentation for the new Asset component (javiereguiluz)
- `3fb19ce <https://github.com/symfony/symfony-docs/commit/3fb19cee8792e1d98a32b107e473aa9f971f61df>`_ #4959 Add excluded_ajax_paths new parameter in v2.6 (bocharsky-bw)
- `78733c3 <https://github.com/symfony/symfony-docs/commit/78733c365f8f4ce364baad1b89c455e74f326304>`_ #4941 Adding a section to emailing showing off absolute_url (weaverryan)
- `325354e <https://github.com/symfony/symfony-docs/commit/325354ef82bc62bea241a41e8560c0992e79f598>`_ #4903 Reworded the explanation about when a lock is released (javiereguiluz)
- `d76f046 <https://github.com/symfony/symfony-docs/commit/d76f04610db35e97fd7820a2eae11884da158b82>`_ #4875 Added chapter about the locale based on the user entity (peterrehm)
- `0d1e97e <https://github.com/symfony/symfony-docs/commit/0d1e97e502ff3de6467dbce387928589f0f010dd>`_ #4834 [translator] use the new fallbacks option. (aitboudad)
- `9846d97 <https://github.com/symfony/symfony-docs/commit/9846d9780d8d825857b6f675d4b20728f4e559bd>`_ #5001 Best practices template names (WouterJ)
- `8e93786 <https://github.com/symfony/symfony-docs/commit/8e93786b1a1c2f9d6e49773afd0a1e15d9dcba12>`_ #4779 Update book to comply with best practices, round 3 (WouterJ)
- `dbdb408 <https://github.com/symfony/symfony-docs/commit/dbdb40841fee1b0a60037e3f8aa64b1c04c71e97>`_ #4724 [Reference][Constraints] document the validation payload option (xabbuh)
- `f8e2e19 <https://github.com/symfony/symfony-docs/commit/f8e2e194757b704082b91dc30391079bf08e04ab>`_ #4692 [Serializer] Name Converter (dunglas)
- `24c4f42 <https://github.com/symfony/symfony-docs/commit/24c4f429728a0f2f00a1804035d08c30d1d1e0dc>`_ #4732 [Reference] add missing reference options and descriptions (xabbuh)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `6ba6ffd <https://github.com/symfony/symfony-docs/commit/6ba6ffdd75a36b4e1072e1a01d3765a590a06249>`_ #5058 Fix: assets_version instead of asset_version (sebastianblum)
- `edf9b78 <https://github.com/symfony/symfony-docs/commit/edf9b78f03e8f184e7cc0a8f6e7c3c5586db814f>`_ #5118 Update logger.rst (jdecoster)
- `adf5b90 <https://github.com/symfony/symfony-docs/commit/adf5b907a3cf171846ff4cc397fbf118ba8a2663>`_ #5110 [Serializer] Fix class name (iamluc)
- `d65880f <https://github.com/symfony/symfony-docs/commit/d65880f734daf9a982ec9754b903a213c89489e9>`_ #5092 Fixed a minor error introduced by the new redirectToRoute() method (javiereguiluz)
- `206e613 <https://github.com/symfony/symfony-docs/commit/206e6131f0618971f99aac18a4e434b2485a056f>`_ #4304 [DX] Suggest a hint to any auth-check (larsborn)
- `df9c3f4 <https://github.com/symfony/symfony-docs/commit/df9c3f47aa208154dcdef295ddd0ab134ebf52ca>`_ #5053 Correct RegisterListenersPass namespace (hacfi)
- `893ffad <https://github.com/symfony/symfony-docs/commit/893ffadf7d769ac8a7557a1ddad484537423364a>`_ #5041 Fixed variable name in : Reference -> validation constraints -> count -> PHP (aminemat)
- `42ba278 <https://github.com/symfony/symfony-docs/commit/42ba278d89c2a4b19384814a468d1c4b282ece80>`_ #5037 Finish 4644: Update the_controller.rst (teggen, WouterJ)
- `e9b9376 <https://github.com/symfony/symfony-docs/commit/e9b9376b8dc9dd78b9b7d61885681eee95bb02bb>`_ #5009 Reworded the explanation about optional command options (javiereguiluz)
- `f9901d5 <https://github.com/symfony/symfony-docs/commit/f9901d5eb1e9c0dcdcd87bb7ba0adf0605e13a8c>`_ #5000 Fixed case where service definition is actually an alias (Xavier Coureau)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `e7cdb2b <https://github.com/symfony/symfony-docs/commit/e7cdb2b0501591a14856676b12fd4e902c7b7d53>`_ #5121 As per twigphp/Twig#472, automatic escaping is not yet available (Ocramius)
- `bce3f04 <https://github.com/symfony/symfony-docs/commit/bce3f04450b81782869009e7570f7ac4c3a94ba1>`_ #5114 [Cookbook][Templating] Use best practice to put templates in app/ dir (WouterJ)
- `d43b845 <https://github.com/symfony/symfony-docs/commit/d43b84572a7eab5ccc4fef0a8164b42b6b3c9b0a>`_ #5116 Fixes for 2.3 branch (ifdattic)
- `eef413b <https://github.com/symfony/symfony-docs/commit/eef413b927f976bda959fed33d113eeff0da0262>`_ #5090 Quick review of the Security book chapter (WouterJ)
- `b07c0f4 <https://github.com/symfony/symfony-docs/commit/b07c0f4f5086cfd96a3f67dc07b909e75da39b80>`_ #5102 Removed duplicate "long"s in length constraint (ByStones)
- `c19598a <https://github.com/symfony/symfony-docs/commit/c19598a17ed049af2d77a67aa39e6edcb5459c25>`_ #5101 [Cookbook][Deployment] some tweaks to #4601 (xabbuh)
- `7e669c1 <https://github.com/symfony/symfony-docs/commit/7e669c1d868037e36dda49bf08d0114bd79aca85>`_ #5105 added Jakub as a merger for the DomCrawler component (fabpot)
- `2c3513e <https://github.com/symfony/symfony-docs/commit/2c3513ea6f84fc143b4cbd9ad44ee0f9e9ab4bbb>`_ #5097 added xabbuh to the list of the Symfony core team member (fabpot)
- `6b96470 <https://github.com/symfony/symfony-docs/commit/6b964703304f183541f80523a775c3d92d0cea77>`_ #5076 Better explain that form types should be unique in the application (javiereguiluz)
- `cdb9350 <https://github.com/symfony/symfony-docs/commit/cdb93506f59c173f1735eddb448cf940a073e085>`_ #5086 Use AppBundle instead of AcmeDemoBundle for consistency (snamor)
- `6719802 <https://github.com/symfony/symfony-docs/commit/67198022232f864cc7b23e3322105d9b4abe5d4b>`_ #5108 [Components][HttpKernel] fix typo in event flow diagrams (xabbuh)
- `d6a838a <https://github.com/symfony/symfony-docs/commit/d6a838a0611ba800ce25428c59106590fd194639>`_ #5082 Proofreading tweaks to asset component (weaverryan)
- `17a6863 <https://github.com/symfony/symfony-docs/commit/17a6863091250184bb0dd9ea1bdf254d359216d6>`_ #5094 Reviewed the Assetic cookbook articles (javiereguiluz)
- `ac9ba97 <https://github.com/symfony/symfony-docs/commit/ac9ba977659ba9a3ff5007cb38bc7e7f1aacaff4>`_ #4909 Remove horizontal scrollbar and other fixes (ifdattic)
- `51af15d <https://github.com/symfony/symfony-docs/commit/51af15de75b1104353abb285266fc534632bf002>`_ #5087 added Abdellatif as a core team member (fabpot)
- `a801d57 <https://github.com/symfony/symfony-docs/commit/a801d573aa418dcd8605441378d72b624602a6b0>`_ #4601 [Heroku] A few more tweaks to outline the steps (weaverryan)
- `b76ffad <https://github.com/symfony/symfony-docs/commit/b76ffad6df2f45fcdcc2238ed0289e610acd3f0e>`_ #4464 [BestPractices Removing micro-optimization note about @Template (weaverryan)
- `b3e204c <https://github.com/symfony/symfony-docs/commit/b3e204ce429303a06edecba0b0dc42e1cf1f0e9e>`_ #5079 [Contributing][Code] link to deciders' GitHub profiles (xabbuh)
- `33232a8 <https://github.com/symfony/symfony-docs/commit/33232a816fe77d4aa9422024be3fd6ce0ba4a634>`_ #5075 Removed an admonition that is no longer true for Symfony 2.6+ (javiereguiluz)
- `4307190 <https://github.com/symfony/symfony-docs/commit/43071905ea90448d44245fa64b1d9868db08d618>`_ #5072 Add missing use statement in Building Login Form doc (ockcyp)
- `9468b9a <https://github.com/symfony/symfony-docs/commit/9468b9a00b4de5f0ff339e69ee6432eb66bf066f>`_ #5071 Fixed incorrect plural form (Katharina Störmer)
- `63f1ca3 <https://github.com/symfony/symfony-docs/commit/63f1ca3fd137f0bfc18cc6856d7151d6c199de38>`_ #5066 [Reference] enclose data type with double backticks (xabbuh)
- `dc01076 <https://github.com/symfony/symfony-docs/commit/dc010766dff6e4e8d13f2b50b3aeda539f9b0559>`_ #5064 Updated documentation standards (code examples and English use) (javiereguiluz)
- `0d0c795 <https://github.com/symfony/symfony-docs/commit/0d0c79599de354928c41cc53dea8cf98a2d456b6>`_ #5047 Fix service id (JhonnyL)
- `2fe8f76 <https://github.com/symfony/symfony-docs/commit/2fe8f761106bf633c46f6530a3bdadb409278f04>`_ #5044 Minor improvement in the node types explanation (javiereguiluz)
- `9b1f5f1 <https://github.com/symfony/symfony-docs/commit/9b1f5f13e7a3e3724c58968b651e97fba6437420>`_ #5043 Switched the first example to a static constructor method (kix)
- `ce19196 <https://github.com/symfony/symfony-docs/commit/ce19196432bb6dfcebcb97c292599b62b1442add>`_ #5042 added some more components for Tobion as a merger (fabpot)
- `b8a11e1 <https://github.com/symfony/symfony-docs/commit/b8a11e11339b4df0dafa8d49219a264f10799f80>`_ #5036 A very quick reread of the Form Login article (WouterJ)
- `e94ec09 <https://github.com/symfony/symfony-docs/commit/e94ec09dd133175f02d041d7dae2d38e81435470>`_ #5035 reword to serves (cordoval)
- `5eb52e3 <https://github.com/symfony/symfony-docs/commit/5eb52e3f7c3b156ed29c119d7a0ef628d92ca74b>`_ #5031 Reworded the note about Windows console and output coloring (javiereguiluz)
- `df72862 <https://github.com/symfony/symfony-docs/commit/df7286242f31f9ca6ed85b8d29aa83d369086342>`_ #5030 Finish #4586: Update routing.rst (guangle)
- `93387bf <https://github.com/symfony/symfony-docs/commit/93387bf5b75bc19e5fae5de802673f6268569e4e>`_ #5029 Finish #4370: add a note about apc for php recent versions (ip512, WouterJ)
- `66cf990 <https://github.com/symfony/symfony-docs/commit/66cf9909e7dd9a5e9f68dcd594f102d9f074c555>`_ #5018 Added a note about the class option of the services defined via factories (javiereguiluz)
- `a89448d <https://github.com/symfony/symfony-docs/commit/a89448dcc649b8fa4465600f9cb486c38888d0e3>`_ #5012 #4032 improved comments about em option (raulfraile)
- `1c50386 <https://github.com/symfony/symfony-docs/commit/1c50386dd15653e79b3cf603eb7d9bbaeceb32bc>`_ #5011 tip for mapping definition (SrgSteak)
- `ce8744d <https://github.com/symfony/symfony-docs/commit/ce8744d6222dcc9693cffe401995177b2e209af6>`_ #5081 [Cookbook][Session] some language tweaks (xabbuh)
- `1ee04ba <https://github.com/symfony/symfony-docs/commit/1ee04ba48386318c0ebfe1616ffa56741315b149>`_ #5006 Added a note about log file sizes and the use of logrotate (javiereguiluz)
- `3be0081 <https://github.com/symfony/symfony-docs/commit/3be00819b3300d6ead36fa6793d93874173a4db3>`_ #4976 Improved sentence (edsonmedina)
- `a444220 <https://github.com/symfony/symfony-docs/commit/a4442209686f612f353b713cc1153a3e17383d25>`_ #4885 Fix typos (ifdattic)
- `482502d <https://github.com/symfony/symfony-docs/commit/482502d02b838a7c9bbd31df9e2244e9876ec3ed>`_ #4793 [Contributing] Several tweaks (xelaris)
- `a2395ef <https://github.com/symfony/symfony-docs/commit/a2395ef7c3945749a7b2d38135262eb70349aaaf>`_ #5054 [Changelog] fix changelog syntax (xabbuh)
- `6b66f03 <https://github.com/symfony/symfony-docs/commit/6b66f034e2efbf488ac2192c3666e32998badc6d>`_ #5003 Updated the generic Deployment article (javiereguiluz)
- `39a1487 <https://github.com/symfony/symfony-docs/commit/39a14875ad1ed8b4681bcb27288fc802d162c300>`_ #4999 Fixed semantic error (beni0888)

February, 2015
--------------

New Documentation
~~~~~~~~~~~~~~~~~

- `16dcf53 <https://github.com/symfony/symfony-docs/commit/16dcf5359b066f93ba7225cdb21632ca44db8e34>`_ #4980 [#4974] Added Twig loader priority Documentation (wizhippo)
- `a25da10 <https://github.com/symfony/symfony-docs/commit/a25da10d010fbd547239099b0fe1accd0fc9f6ca>`_ #4966 [#4231] Clarify that only the main command triggers events (riperez)
- `c6bea37 <https://github.com/symfony/symfony-docs/commit/c6bea3797288aa4ecb0f21b235d852bbde18d6b9>`_ #4957 Added a mention to the @Security annotation (javiereguiluz)
- `9cce63c <https://github.com/symfony/symfony-docs/commit/9cce63cbe3d2899bab00da14ce29a998e87cd14d>`_ #4924 [swiftmailer] Document whitelist option to email redirect (TerjeBr)
- `14a080f <https://github.com/symfony/symfony-docs/commit/14a080fe68cfffc86111894ce1e9b71fb03744ed>`_ #4907 Adjustments to PDO Session storage page (kbond)
- `f5ff45e <https://github.com/symfony/symfony-docs/commit/f5ff45eba45bb4d87f5b49959eb6b9f7051157c0>`_ #4712 Provide full test example (ifdattic)
- `5e83045 <https://github.com/symfony/symfony-docs/commit/5e83045ad129e0139ca01c3339717ebad4ee6acb>`_ #4657 Update assetic watch command (xtreamwayz)
- `d447b12 <https://github.com/symfony/symfony-docs/commit/d447b12d6eeb44db0befa8d30ebd2ba67a1880e9>`_ #4556 Updated twig reference with optimizations and paths (jzawadzki)
- `ed80100 <https://github.com/symfony/symfony-docs/commit/ed8010062f99ccde39b7b32ffa1ea7cfbcbfdd2f>`_ minor #4977 Unnecessary comma (edsonmedina)
- `018cf3f <https://github.com/symfony/symfony-docs/commit/018cf3fc6558f6b85993be8eacff9ba008eb9bb4>`_ #4661 Added a short cookbook about avoiding the automatic start of the sessions (javiereguiluz)
- `2305066 <https://github.com/symfony/symfony-docs/commit/23050662fa728edf3c6971bbeef15b2dd6339111>`_ #4902 Removed the Stable API chapter from the Symfony book (javiereguiluz)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `215cacf <https://github.com/symfony/symfony-docs/commit/215cacfc51e344c0862dc8ce3b15cf8bd7a694b4>`_ #4423 Fix description of ConfirmationQuestion (cxj)
- `ed80100 <https://github.com/symfony/symfony-docs/commit/ed8010062f99ccde39b7b32ffa1ea7cfbcbfdd2f>`_ minor #4977 Unnecessary comma (edsonmedina)
- `583ec92 <https://github.com/symfony/symfony-docs/commit/583ec927862be3b160b4616a55f8401a5760adbf>`_ #4984 Fix the example using SerializedParsedExpression (stof)
- `b0d9c5c <https://github.com/symfony/symfony-docs/commit/b0d9c5c639ef520076491aa9e688e9b4184a263e>`_ #4978 fix wrong header-line syntax (sstok)
- `6d65564 <https://github.com/symfony/symfony-docs/commit/6d655649d78b810cf3e459035a04671d40659791>`_ #4954 Fixed some syntax issues in Twig Reference (javiereguiluz)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `2a29225 <https://github.com/symfony/symfony-docs/commit/2a29225acb875a7aa524cf5508a66b8c60021fc8>`_ #4985 Fixed a typo (javiereguiluz)
- `f75bc2b <https://github.com/symfony/symfony-docs/commit/f75bc2bf6d52a5480ba056af06c59844ec8934ba>`_ #4972 Fix typos (ifdattic)
- `89e626f <https://github.com/symfony/symfony-docs/commit/89e626fb902d33b181cf531c638fba05bb5e577e>`_ #4952 symfony 2.7 requires at least php 5.3.9 (scaytrase)
- `9fab10b <https://github.com/symfony/symfony-docs/commit/9fab10bde02daae0af6f2848eb5888fa95026e76>`_ #4854 Removed no longer needed information about PHP 5.3 (WouterJ)
- `1726054 <https://github.com/symfony/symfony-docs/commit/1726054212bda98d4d63edb7f1364472fe91c7fe>`_ #4500 Link to standard edition  (harikt)
- `91ff6f8 <https://github.com/symfony/symfony-docs/commit/91ff6f8198d17eda5f837c5f1c1725b8030862a1>`_ #4329 ensure consistency with the note (greg0ire)
- `f4ab4b6 <https://github.com/symfony/symfony-docs/commit/f4ab4b65092c21642f36e9aec733c9348b4b9691>`_ #5002 Revert very bad merge (WouterJ)
- `e5dbd49 <https://github.com/symfony/symfony-docs/commit/e5dbd49deb1cc6d25d4b89c4967a6511192a22ec>`_ #4977 Unnecessary comma (edsonmedina)
- `ed80100 <https://github.com/symfony/symfony-docs/commit/ed8010062f99ccde39b7b32ffa1ea7cfbcbfdd2f>`_ #4977 Unnecessary comma (edsonmedina)
- `5d44987 <https://github.com/symfony/symfony-docs/commit/5d4498707f7fc72653fc135a3e3caee675901ee9>`_ #4991 Fixed typo and tweaked syntax. (cdvrooman)
- `b1aadbf <https://github.com/symfony/symfony-docs/commit/b1aadbf0ef75398b4d46618625153f87f4f1022e>`_ #4993 Bumped symfony version number to 2.6 in flat php composer.json example (TSchuermans)
- `3845c9c <https://github.com/symfony/symfony-docs/commit/3845c9c7ad4c23f76f2f1ba26bc650df72baf84d>`_ #4979 require_once path fixed (mvanmeerbeck)
- `96770aa <https://github.com/symfony/symfony-docs/commit/96770aa48406f139986f987ccc0bd277501bc4e2>`_ #4969 Add typehint (piotrantosik)
- `f97d01f <https://github.com/symfony/symfony-docs/commit/f97d01f26dabbbb11e4a6e8a0adc865e791dea0a>`_ #4995 [#4965] file extension fix (hansstevens)
- `c5647dd <https://github.com/symfony/symfony-docs/commit/c5647ddad3a13d32e1591e5fc08d9c9b70ed4239>`_ #4968 Fix typo (ifdattic)
- `c3218fc <https://github.com/symfony/symfony-docs/commit/c3218fced746bfd40c27aa75c5332eacb977fb4e>`_ #4962 cookbok/security/acl.rst (DaliusK)
- `72489a4 <https://github.com/symfony/symfony-docs/commit/72489a414f40fefca9553d0ffd8fabebbccf8d9e>`_ #4963 Normalize excluded_404s in monolog cookbook (jbafford)
- `0adb6f6 <https://github.com/symfony/symfony-docs/commit/0adb6f6a62625c9875be57d102c814721c5b83c8>`_ #4964 link to the cookbook article on avoiding to start a session (dbu)
- `5d8456f <https://github.com/symfony/symfony-docs/commit/5d8456fe3ca6f6bd30cf13d6317e0776c629ed0c>`_ #4955 Fixed wrong API link (dosten)
- `0a85053 <https://github.com/symfony/symfony-docs/commit/0a85053cb7344e4b0c1950a46c51e7f9a9683ac0>`_ #4950 Fixes for 2.3 branch (ifdattic)
- `d3d96e1 <https://github.com/symfony/symfony-docs/commit/d3d96e104dea4b670e873543f189b18f3c93d4c6>`_ #4951 fix characters in backported patch (xabbuh)
- `208904a <https://github.com/symfony/symfony-docs/commit/208904acc6d82b7e602360dbea66ea35d81d15c5>`_ #4949 Fixes for 2.3 branch (ifdattic)
- `6be214c <https://github.com/symfony/symfony-docs/commit/6be214c94aab16f477e29e2d3767e88195fa2c39>`_ #4948 Fixes for 2.6 branch (ifdattic)
- `42b44c4 <https://github.com/symfony/symfony-docs/commit/42b44c4eb75d7a53cf094fe8cef4f2a847ec83c1>`_ #4929 Remove block which doesn't make sense after best practices (ifdattic)
- `008c4de <https://github.com/symfony/symfony-docs/commit/008c4de467f1fcc17517d4c9c3a696c36cb3c0f8>`_ #4928 Change installation method order (ifdattic)
- `6f8b145 <https://github.com/symfony/symfony-docs/commit/6f8b14558aa8dceb1030f49b9fb47164ccd4b8ff>`_ #4904 Added a reference about including JS and CSS files in PHP templates (javiereguiluz)

January, 2015
-------------

New Documentation
~~~~~~~~~~~~~~~~~

- `b32accb <https://github.com/symfony/symfony-docs/commit/b32accbe3bf3cc8fd37f1d7668983531569e4020>`_ minor #4935 Fix typos (ifdattic)
- `ad74169 <https://github.com/symfony/symfony-docs/commit/ad7416975bfca530b75bbebd29baa89eeeae5e51>`_ #4628 Varnish cookbook session cookie handling (dbu)
- `50c5a9e <https://github.com/symfony/symfony-docs/commit/50c5a9e65de046fd8d719c7c7cc5233869f2643a>`_ #4895 Added configuration of the user provider (peterrehm)
- `4226fc2 <https://github.com/symfony/symfony-docs/commit/4226fc27a06aeb975af1b1aae1e6207a07bbbb6f>`_ #4883 Global dump (nicolas-grekas)
- `a57db5b <https://github.com/symfony/symfony-docs/commit/a57db5b1d240d5b6df1b5a8b077b280c17233420>`_ #4879 Documented true regex (WouterJ)
- `3bb7b61 <https://github.com/symfony/symfony-docs/commit/3bb7b61dde079611180a2bc4e12e70eac8caef51>`_ #4645 Remove note that's no longer the case (thewilkybarkid)
- `3293286 <https://github.com/symfony/symfony-docs/commit/3293286ac82c6adb0cc4938fce33fef17f5f7108>`_ #4801 [Cookbook][cache][varnish] be more precise about version differences (dbu)
- `572bf3b <https://github.com/symfony/symfony-docs/commit/572bf3b5da737731472f0760ee6105c72d76feb0>`_ #4800 [Cookbook][Security] Hint about createToken can return null (xelaris)
- `74d2e30 <https://github.com/symfony/symfony-docs/commit/74d2e3063c23dadfcecd9c5d3715127da68da128>`_ #4786 Replaced setDefaultOptions by the new configureOptions method (peterrehm)
- `528e8e1 <https://github.com/symfony/symfony-docs/commit/528e8e14aa690bf761d5ad4fa763593f856c6afb>`_ #4740 Use AppBundle whenever it's possible (javiereguiluz)
- `08e5ac9 <https://github.com/symfony/symfony-docs/commit/08e5ac990a3d8e50a834bf5d7bfc420b39f9083a>`_ #4658 Debug formatter tweaks (weaverryan)
- `cfad26c <https://github.com/symfony/symfony-docs/commit/cfad26c0b9227c3d43b4988b2c5e510625dd805c>`_ #4605 Adding a link to log things in the prod environment (weaverryan)
- `3643ec2 <https://github.com/symfony/symfony-docs/commit/3643ec224921b3a0ce6163f807e4b208aa718d58>`_ #4723 [Cookbook][Security] document the new AuthenticationUtils (xabbuh)
- `9742b92 <https://github.com/symfony/symfony-docs/commit/9742b9291e4b0f4ad4f1e8eff61261cc9598213f>`_ #4761 [Cookbook][Security] don't output message from AuthenticationException (xabbuh)
- `a23e7d2 <https://github.com/symfony/symfony-docs/commit/a23e7d2ec1b28afe2c3452d1bf5488d7558a478a>`_ #4643 How to override vendor directory location (gajdaw)
- `99aca45 <https://github.com/symfony/symfony-docs/commit/99aca4532681c1fbc5d85b2935145b3d4fe9934c>`_ #4749 [2.3][Book][Security] Add isPasswordValid doc as in 2.6 (xelaris)
- `d9935a3 <https://github.com/symfony/symfony-docs/commit/d9935a3f918791a65488a0cd5ca721482c76f09e>`_ #4141 Notes about caching pages with a CSRF Form (ricardclau)
- `207f2f0 <https://github.com/symfony/symfony-docs/commit/207f2f065e10a29172095c6b6f88a2d8fa071223>`_ #4711 [Reference] Add default_locale config description (xelaris)
- `1b0fe77 <https://github.com/symfony/symfony-docs/commit/1b0fe7735d4863223e8c4896b956b54d2541344e>`_ #4708 Change Apache php-fpm proxy configuration (TeLiXj)
- `7be0dc6 <https://github.com/symfony/symfony-docs/commit/7be0dc6977683cdfa1e34b6b465394b877c8f341>`_ #4681 adding note to assetic cache busting (weaverryan)
- `127ebc1 <https://github.com/symfony/symfony-docs/commit/127ebc1d45e2ccf3b29e23e9658cf984765d0899>`_ #4650 Documented the characters that provoke a YAML escaping string (javiereguiluz)
- `0c0b708 <https://github.com/symfony/symfony-docs/commit/0c0b708efa989560fb8b5e193c7b1a3f56eba195>`_ #4454 More concrete explanation of validation groups (peterrehm)
- `4fe4f65 <https://github.com/symfony/symfony-docs/commit/4fe4f652732be867c8eaf56cfb70ec465c6dda2a>`_ #4682 [Reference] document the `````2.5````` validation options (xabbuh)
- `144e5af <https://github.com/symfony/symfony-docs/commit/144e5afbfe44e096a0a743f144a07ac1c6c57696>`_ #4611 Adding a guide about upgrading (weaverryan)
- `01df3e7 <https://github.com/symfony/symfony-docs/commit/01df3e7db74ede4643a507e646b9b534bcf3b1a5>`_ #4626 clean up cache invalidation information on the cache chapter (dbu)
- `5f7ef85 <https://github.com/symfony/symfony-docs/commit/5f7ef8573b649c0c9688f113ff5b7f4b42c5559a>`_ #4651 Documented the security:check command (javiereguiluz)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `ea51aeb <https://github.com/symfony/symfony-docs/commit/ea51aeb4a7426fefe1a3b4b388c67e749c9b70ee>`_ #4926 Finish #4505: Fixed composer create-project command (windows) (Epskampie)
- `b32accb <https://github.com/symfony/symfony-docs/commit/b32accbe3bf3cc8fd37f1d7668983531569e4020>`_ minor #4935 Fix typos (ifdattic)
- `7e84533 <https://github.com/symfony/symfony-docs/commit/7e84533cde4e546c174f23cb390cd2e6ccd763ac>`_ #4886 [Best Pracitices] restore example in the "Service: No Class Parameter" section (u-voelkel)
- `a6b7d72 <https://github.com/symfony/symfony-docs/commit/a6b7d7208e672676d2bf522850c1b145b898669f>`_ #4861 Ifdattic's fixes (ifdattic)
- `8ef3477 <https://github.com/symfony/symfony-docs/commit/8ef3477299fe68944484697073ff1547179fdcfb>`_ #4856 [Components][Debug] fix DebugClassLoader namespace (xabbuh)
- `b9359a2 <https://github.com/symfony/symfony-docs/commit/b9359a2e22223ae4b4062075cd7ed5602ace9899>`_ #4905 Update routing.rst (IlhamiD)
- `9fee9ee <https://github.com/symfony/symfony-docs/commit/9fee9eed5366a1a4408821cfc950a34ea4c7fbce>`_ #4746 Revert #4651 for 2.3 branch (xelaris)
- `5940d52 <https://github.com/symfony/symfony-docs/commit/5940d5252b82db7bc247c8723e7761c5cfc9c84b>`_ #4735 [BestPractices] remove @Security annotation for Symfony 2.3 (xabbuh)
- `ce37b96 <https://github.com/symfony/symfony-docs/commit/ce37b96ba0565d0624a16c44c1447c248447158b>`_ #4771 [QuickTour] use the debug:router command name (xabbuh)
- `ffe3425 <https://github.com/symfony/symfony-docs/commit/ffe3425f6a0ef97be45f29608c6be02db24e98f9>`_ #4765 [Book][Forms] avoid the request service where possible (xabbuh)
- `36f2e1f <https://github.com/symfony/symfony-docs/commit/36f2e1f74b2d166b333cb07029b1fe6e929c2370>`_ #4757 [Components][ClassLoader] don't show deprecated usage of ``Yaml::parse()`` (xabbuh)
- `d8e8d75 <https://github.com/symfony/symfony-docs/commit/d8e8d75961ea0a77c74634a56b6d3237d00ca8a4>`_ #4756 [Components][Config] don't show deprecated usage of ``Yaml::parse()`` (xabbuh)
- `b143754 <https://github.com/symfony/symfony-docs/commit/b143754b22d1086ad58712147075bf1909836a55>`_ #4744 [Book][Security] Update code example to fit description (xelaris)
- `310f4ae <https://github.com/symfony/symfony-docs/commit/310f4ae6dda955fa5b0dbc1ab7744ef32bda54d5>`_ #4639 Update by_reference.rst.inc (docteurklein)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `2cff942 <https://github.com/symfony/symfony-docs/commit/2cff94272c8b1b9f12dab3dfe8f3cd076f833811>`_ #4878 [Book][Security] Remove out-dated anchor (xelaris)
- `a97646f <https://github.com/symfony/symfony-docs/commit/a97646feebb514fef132d31397a8ea956bed725f>`_ #4882 Remove horizontal scrollbar (ifdattic)
- `c24c787 <https://github.com/symfony/symfony-docs/commit/c24c787ab9f841c1ec8c6366659525fa8f83029b>`_ #4931 Remove horizontal scrollbar (ifdattic)
- `83696b8 <https://github.com/symfony/symfony-docs/commit/83696b8cc209db17775c9a09ddb83846a3267d27>`_ #4934 Fixes for 2.3 branch (ifdattic)
- `99d225b <https://github.com/symfony/symfony-docs/commit/99d225b525346ba7bc814086ab638e6b6b02a4ff>`_ #4943 Fixes for 2.3 branch (ifdattic)
- `3907af6 <https://github.com/symfony/symfony-docs/commit/3907af64e5fcdfc77cbbe7b2b50c2a46bfd76679>`_ #4944 Fix formatting (ifdattic)
- `137ba72 <https://github.com/symfony/symfony-docs/commit/137ba72abf814d176ff0a5100889832f66c0e404>`_ #4945 Fixes for 2.3 branch (ifdattic)
- `5a53e87 <https://github.com/symfony/symfony-docs/commit/5a53e8731deeeb6b92efb70725f6d2def211e0db>`_ #4946 Remove horizontal scrollbar (ifdattic)
- `b32accb <https://github.com/symfony/symfony-docs/commit/b32accbe3bf3cc8fd37f1d7668983531569e4020>`_ #4935 Fix typos (ifdattic)
- `04090c0 <https://github.com/symfony/symfony-docs/commit/04090c0f5d96660c17d65a8f779de75fc5cc360c>`_ #4936 fixed typo (issei-m)
- `0fa9cbd <https://github.com/symfony/symfony-docs/commit/0fa9cbd3800ad5526675113b5beb70315e0cf664>`_ #4937 Keeping documentation consistent (thecatontheflat)
- `3921d70 <https://github.com/symfony/symfony-docs/commit/3921d7049bbd5b207498277e7f7c92c33dbc0836>`_ #4918 Quick proofread of the email cookbook (weaverryan)
- `768650e <https://github.com/symfony/symfony-docs/commit/768650e7bde21e54e1c8d2acadad92bd4f99a2bb>`_ #4932 Add missing comma in array (ifdattic)
- `418a73b <https://github.com/symfony/symfony-docs/commit/418a73b45cf8f1d240e1fc0b6550cf1c071c0d8b>`_ #4922 Fix typo: missing space (ifdattic)
- `30ecdde <https://github.com/symfony/symfony-docs/commit/30ecdde11c9b78488250953c034a321e8227a843>`_ #4921 Fixes for 2.5 branch (ifdattic)
- `d1103a8 <https://github.com/symfony/symfony-docs/commit/d1103a80f9f5c2737944537f8c94634ad825be17>`_ #4919 Fix code examples (ifdattic)
- `20d80c3 <https://github.com/symfony/symfony-docs/commit/20d80c333dfe49f998815c2407ee4f737b70a2ef>`_ #4916 Fixes for 2.3 branch (ifdattic)
- `d7acccf <https://github.com/symfony/symfony-docs/commit/d7acccf89059fb5a246f76fc629adf25b0f06880>`_ #4914 Fix typo, remove horizontal scrollbar (ifdattic)
- `fc776ab <https://github.com/symfony/symfony-docs/commit/fc776ab1ae93db6bf25773734f51c9db1246fc4b>`_ #4894 Align methods in YAML example (ifdattic)
- `bd279f6 <https://github.com/symfony/symfony-docs/commit/bd279f6967aec73a23ff5dac7e54322552838836>`_ #4908 Set twig service as private (ifdattic)
- `37fd035 <https://github.com/symfony/symfony-docs/commit/37fd035bc4da0266d119532b21e26f21ddc84f0c>`_ #4899 Fix typo: looks => look (ifdattic)
- `fbaeecd <https://github.com/symfony/symfony-docs/commit/fbaeecddebd8ef808405d8ad53c29c41fae5c9b5>`_ #4898 added Kévin Dunglas as a merger for the Serializer component (fabpot)
- `7c66a8b <https://github.com/symfony/symfony-docs/commit/7c66a8b0b0f3c085abab04abcbdb6cb1e73a06d9>`_ #4893 Move annotations example to front (ifdattic)
- `2b7e5ee <https://github.com/symfony/symfony-docs/commit/2b7e5ee896c148b263a890541b8b2489e1aa0ee5>`_ #4891 fixed typo (acme -> app) (adiebler)
- `00981de <https://github.com/symfony/symfony-docs/commit/00981de76d0c7bd71f331c48f51b937a4dbccc52>`_ #4890 Fixed typo (beni0888)
- `dc87147 <https://github.com/symfony/symfony-docs/commit/dc87147e9b6343446265fc09c083f156a2afe310>`_ #4876 Remove horizontal scrollbar (ifdattic)
- `f5f3c1b <https://github.com/symfony/symfony-docs/commit/f5f3c1bfc1c131163b9e76082c50808e5be22330>`_ #4865 Removed literals for bundle names (WouterJ)
- `33914c9 <https://github.com/symfony/symfony-docs/commit/33914c9ec3090b2a28cefa120c4f83c7c4542b7b>`_ #4859 [Components][EventDispatcher] don't explain deprecated `````getName()````` method (xabbuh)
- `9a6d7b9 <https://github.com/symfony/symfony-docs/commit/9a6d7b9ec4b1d7e69f163af6bb17dcbc2a02a1f1>`_ #4831 Update override.rst (ifdattic)
- `f9c2d69 <https://github.com/symfony/symfony-docs/commit/f9c2d6939a8d5b91e6d1cdcd0f8d654ca0796389>`_ #4803 [Book][Translation] Added tip for routing params (xelaris)
- `2f41c9e <https://github.com/symfony/symfony-docs/commit/2f41c9e0aee1ac312774d6502c7591915e6a5d46>`_ #4887 Typo (XitasoChris)
- `3774a37 <https://github.com/symfony/symfony-docs/commit/3774a375bc5ccb32d04546f97bf65d74656575df>`_ #4881 Remove 'acme' (ifdattic)
- `d85fa76 <https://github.com/symfony/symfony-docs/commit/d85fa76f4f8493f809e7ad8d5e22fd8ca73c77ef>`_ #4880 Remove duplicate link, introduction.rst (Quberik)
- `6a15077 <https://github.com/symfony/symfony-docs/commit/6a150771e0679088ebc6c67bb22c9f99b63b109e>`_ #4874 Remove trailing whitespace (WouterJ)
- `80bef5a <https://github.com/symfony/symfony-docs/commit/80bef5a339ba8f52f607db7d7216f9f7ba61489b>`_ #4873 [BestPractices] fix typo (xabbuh)
- `6cffa4e <https://github.com/symfony/symfony-docs/commit/6cffa4e5f3136b2bd5c4e8d23d1fa1576a5ec7c4>`_ #4866 Remove horizontal scrollbar (ifdattic)
- `65b0822 <https://github.com/symfony/symfony-docs/commit/65b08224f3909fb97ab3e26483626c6663e26f4f>`_ #4798 Add version added note for the debug:event-dispatcher command (adamelso)
- `bcf1508 <https://github.com/symfony/symfony-docs/commit/bcf150860c81459f3accce4dca3e57eafb213e4d>`_ #4785 [Book][Security] add back old anchors (xabbuh)
- `4143076 <https://github.com/symfony/symfony-docs/commit/4143076e6b2f760b2df03dda6d5933fc54618b68>`_ #4872 [BestPractices] fix merge after removing @Security in 2.3 (xabbuh)
- `dc25c65 <https://github.com/symfony/symfony-docs/commit/dc25c65d445185b5328febe0248e16a3d88d5e0a>`_ #4769 [2.7] Removed 2.5 versionadded as its deprecated (WouterJ)
- `48835de <https://github.com/symfony/symfony-docs/commit/48835de6e61f7f72c7000423b23ec9379ab175f7>`_ #4767 [2.6] Removed 2.4 versionadded as version is deprecated (WouterJ)
- `240a981 <https://github.com/symfony/symfony-docs/commit/240a9815f41c9c1f96d8757b33147e32e5e1b029>`_ #4764 [Reference][Forms] move cautions to make them visible (xabbuh)
- `cf3d38a <https://github.com/symfony/symfony-docs/commit/cf3d38ad333f09fb324ed610c4f5452f4fda213b>`_ #4731 [Book][Testing] bump required PHPUnit version (xabbuh)
- `4f47dec <https://github.com/symfony/symfony-docs/commit/4f47decd9ffd404326ca0386d74d58380c9d8843>`_ #4837 Monolog Cookbook Typo Fix: "allows to" should be "allows you to" (mattjanssen)
- `c454fd2 <https://github.com/symfony/symfony-docs/commit/c454fd2dfa070b1b04c3e21c8036529671e3e8ff>`_ #4857 Add custom link labels where Cookbook articles titles looked wrong (javiereguiluz)
- `17989fd <https://github.com/symfony/symfony-docs/commit/17989fd4455bc7005eba2e1e5fe186270779a68f>`_ #4860 [Components][HttpKernel] replace API link for SwiftmailerBundle (xabbuh)
- `84839ba <https://github.com/symfony/symfony-docs/commit/84839baddd5a93b383619643dbbfe2973d3d11b1>`_ #4829 Fix code example (ifdattic)
- `e347ec8 <https://github.com/symfony/symfony-docs/commit/e347ec862a1d36195ef1e5cd6c8aafd0501f68ff>`_ #4819 Removed a leftover comma in security config sample (javiereguiluz)
- `11b9d23 <https://github.com/symfony/symfony-docs/commit/11b9d23a4815c306ddc2df1da170e35f61f1648b>`_ #4772 Tweaks to the new form csrf caching entry (weaverryan)
- `c04ed79 <https://github.com/symfony/symfony-docs/commit/c04ed796b4535c3bf6cf458544ad9882fb1efeab>`_ #4848 Fix typo: BLOG => BLOB (ifdattic)
- `f9c1389 <https://github.com/symfony/symfony-docs/commit/f9c138916771383481ac88309eb34a53bd308db9>`_ #4845 Update security.rst (meelijane)
- `9680ec0 <https://github.com/symfony/symfony-docs/commit/9680ec0b0c6eb0435072e119ae1be7612948f0dc>`_ #4844 Update routing.rst (bglamer)
- `c243d00 <https://github.com/symfony/symfony-docs/commit/c243d0040a156536d414c0efafa8808f9ccccb2a>`_ #4843 Fixed typo (beni0888)
- `5b91653 <https://github.com/symfony/symfony-docs/commit/5b91653af291ca8b4b2f3a48bc77d16c89726b3f>`_ #4843 Fixed typo (beni0888)
- `13ffb83 <https://github.com/symfony/symfony-docs/commit/13ffb835efffdaaed060a5e88866455608ddc703>`_ #4835 Fixed broken link (SofHad)
- `d2a67ac <https://github.com/symfony/symfony-docs/commit/d2a67acc9c57889ba89162c7fed419aaefdde141>`_ #4826 Fixed 404 page (SofHad)
- `f34fc2d <https://github.com/symfony/symfony-docs/commit/f34fc2d14a63b5b90e4f2ed9ed43013b2c0f9172>`_ #4825 Fixed the 404 not found error (SofHad)
- `467c538 <https://github.com/symfony/symfony-docs/commit/467c538dfe2ccdb434084b67e52cd1db048c9167>`_ #4824 fix SQL: table names (e-moe)
- `91a89b7 <https://github.com/symfony/symfony-docs/commit/91a89b7a9c5dfd14993c93f3b8fe77675792ad1d>`_ #4821 Fixed typo (SofHad)
- `f7179df <https://github.com/symfony/symfony-docs/commit/f7179df7b4f1447d1e52a2385a7ac130ce9e9be7>`_ #4818 [Routing] Removed deprecated usage (WouterJ)
- `82bce29 <https://github.com/symfony/symfony-docs/commit/82bce299125247afea9b1fe65d7f0e5d40ed4fdd>`_ #4815 Update translation.rst (ifdattic)
- `892586b <https://github.com/symfony/symfony-docs/commit/892586bf3ceb5c9f8c1e0f5e3cfa0a5100628161>`_ #4808 Email message instantiation changed to a more 'symfonysh' way. (alebo)
- `e913808 <https://github.com/symfony/symfony-docs/commit/e913808bbbf300fd437c63934ae295e597ba9b50>`_ #4802 [Cookbook][Routing] Fixed typo (xelaris)
- `6522145 <https://github.com/symfony/symfony-docs/commit/65221450ac9745d03b61463951a35be2f24d1576>`_ #4799 Fix markup (WouterJ)
- `a42e5b6 <https://github.com/symfony/symfony-docs/commit/a42e5b629eb095471e3929150d2a6a354504308d>`_ #4778 Update templating.rst (ifdattic)
- `bd7d246 <https://github.com/symfony/symfony-docs/commit/bd7d246dabd8fe08d336a22712b497b4b16d31cb>`_ #4752 [Book][Validation] clarify group validation (xabbuh)
- `236c26f <https://github.com/symfony/symfony-docs/commit/236c26fd7dc6f74d170b6cea409e57519dfd95d9>`_ #4796 Update service_container.rst (ifdattic)
- `f85c44c <https://github.com/symfony/symfony-docs/commit/f85c44c7b4c29e9f0a408427b7afaff9ba30aad5>`_ #4795 Remove horizontal scrollbar (ifdattic)
- `45189bb <https://github.com/symfony/symfony-docs/commit/45189bb477878af0faa24b6977d93b831dd97b2e>`_ #4792 [BestPractices] add filename to codeblock (xelaris)
- `fccea1d <https://github.com/symfony/symfony-docs/commit/fccea1dffc9662673162c1fd29c0276d52b29dd6>`_ #4791 Fix heading level in form_login_setup.rst (xelaris)
- `74c3a35 <https://github.com/symfony/symfony-docs/commit/74c3a35d04ec8c3b4a3e43d8081c22ceec72874d>`_ #4788 Controller is a callable (timglabisch)
- `eb56376 <https://github.com/symfony/symfony-docs/commit/eb563767f4eb7ab9b8273ea712d3eb4874a62488>`_ #4781 [Serializer] Bad variable name in example (arno14)
- `28571fc <https://github.com/symfony/symfony-docs/commit/28571fc7acf00d2bef565fa1bc172559a433592e>`_ #4780 Add missing semicolon (NightFox7)
- `32bd0b1 <https://github.com/symfony/symfony-docs/commit/32bd0b1f5b2d9c7a119f08ea038d039a9216f058>`_ #4777 Update templating.rst (ifdattic)
- `dc5d8f8 <https://github.com/symfony/symfony-docs/commit/dc5d8f8c2736a6fee74aa07a818e717bf799cf13>`_ #4760 Update routing.rst (ifdattic)
- `4e880c1 <https://github.com/symfony/symfony-docs/commit/4e880c1dba02924ccfef47049c2dd4dc14f2db65>`_ #4755 fix typo (xabbuh)
- `463c30b <https://github.com/symfony/symfony-docs/commit/463c30b8bbded334b646db083ae8f22d259ad14f>`_ #4751 [BestPractices] fix alignment of YAML values (xelaris)
- `1972757 <https://github.com/symfony/symfony-docs/commit/1972757afc62c7ade55d3775988dae654d88a16f>`_ #4775 Corrected validation information on inheritance (peterrehm)
- `f4f8621 <https://github.com/symfony/symfony-docs/commit/f4f8621ec02d91fe7947ab5c320337296e90185c>`_ #4762 [Cookbook][Configuration] update text to use SetHandler (not ProxyPassMatch) (xabbuh)
- `43543bb <https://github.com/symfony/symfony-docs/commit/43543bb0fbb058898ea7f531601754ea9e0074a3>`_ #4748 Re-reading private service section (weaverryan)
- `e447e70 <https://github.com/symfony/symfony-docs/commit/e447e70eb0a1cd79edbde4b3d249212892423fed>`_ #4743 [Book][Security] Fix typo and remove redundant sentence (xelaris)
- `97a9c7b <https://github.com/symfony/symfony-docs/commit/97a9c7bd18cab43d5703582755ba1bcb3732845c>`_ #4742 Formatting fix (WouterJ)
- `9819113 <https://github.com/symfony/symfony-docs/commit/981911384be4a04476eb7b00d426efded707ea5b>`_ #4702 Clarify tip for creating a new AppBundle (xelaris)
- `8f2fe87 <https://github.com/symfony/symfony-docs/commit/8f2fe870eb07dde5edc17482f9e1020ad39dc1dd>`_ #4683 [Reference] update the configuration reference (xabbuh)
- `e889813 <https://github.com/symfony/symfony-docs/commit/e889813b4c93d41713fb7382eb7ab4ce4bcb5660>`_ #4677 Add exception to console exception log (adrienbrault)
- `9958c41 <https://github.com/symfony/symfony-docs/commit/9958c41f8efdd143cba1b1e832c7d8b61aa15030>`_ #4656 Tried to clarify private services (WouterJ)
- `1d5966c <https://github.com/symfony/symfony-docs/commit/1d5966c422f190a7b227ca49d146d6d8af03ad7b>`_ #4703 Fix representation (ifdattic)
- `aa9d982 <https://github.com/symfony/symfony-docs/commit/aa9d9822833891cf8ff7d61fe6591d8d4c6eb06c>`_ #4697 Set twig service as private (ifdattic)
- `ece2c81 <https://github.com/symfony/symfony-docs/commit/ece2c818fb38223bf62aa5ea534d4d84e989ea3e>`_ #4722 Improve readability (ifdattic)
- `dcc9516 <https://github.com/symfony/symfony-docs/commit/dcc9516221ed92e14e849a1be6da5b39662e15b8>`_ #4725 Remove horizontal scrollbar (ifdattic)
- `3eb14aa <https://github.com/symfony/symfony-docs/commit/3eb14aad06ba9d55c582daebe01791414f047198>`_ #4727 Renamed example: "Acme\BlogBundle" -> "AppBundle" (muxator)
- `25dd825 <https://github.com/symfony/symfony-docs/commit/25dd8257deceb22d055bff366d4cb4f86919f5e1>`_ #4730 Fix typo: as => is (ifdattic)
- `760a441 <https://github.com/symfony/symfony-docs/commit/760a4415f7ce3789631d77fa11c0a17b1906a377>`_ #4734 [BestPractices] add missing comma (xabbuh)
- `caa2be6 <https://github.com/symfony/symfony-docs/commit/caa2be6c701b58f133d372959dbf69c96826000b>`_ #4737 [Book][Security] add missing versionadded directive (xabbuh)
- `8c1afb9 <https://github.com/symfony/symfony-docs/commit/8c1afb9591e948edcad48ec664ade53a721aa33b>`_ #4738 [Contributing][Code] update year in license (xabbuh)
- `4ad72d0 <https://github.com/symfony/symfony-docs/commit/4ad72d0146a62a698123a5daec6fa47b8efcc4ee>`_ #4741 use the doc role for internal links (jms85, xabbuh)
- `57fdea6 <https://github.com/symfony/symfony-docs/commit/57fdea615eaf720e72b977df0dec75f0c0437a7c>`_ #4729 Fixed typo in factories.rst (nietonfir)

December, 2014
--------------

New Documentation
~~~~~~~~~~~~~~~~~

- `00a13d6 <https://github.com/symfony/symfony-docs/commit/00a13d6bd618cc09b0957b1ff4d93b384dc85a78>`_ #4606 Completely re-reading the security book (weaverryan)
- `aa88f99 <https://github.com/symfony/symfony-docs/commit/aa88f99cfdf3b9807af372ab8b3ea4467942aebf>`_ #4609 Adding details about the changes to the PdoSessionHandler in 2.6 (weaverryan)
- `bd65c3c <https://github.com/symfony/symfony-docs/commit/bd65c3c1bd950419438061646edbb4b1453493a7>`_ #4673 [Reference] add validation config reference section (xabbuh)
- `55a32cf <https://github.com/symfony/symfony-docs/commit/55a32cfccb7abedf602d24f4c7ef68b81477c5ea>`_ #4173 use a global Composer installation (xabbuh)
- `c5e409b <https://github.com/symfony/symfony-docs/commit/c5e409b70060405732b2251ae92e0278fd5f5e3d>`_ #4526 Deploy Symfony application on Platform.sh. (GuGuss)
- `ddd56ea <https://github.com/symfony/symfony-docs/commit/ddd56ea119ad9a6283d1174b4ecf8d8b964af911>`_ #4449 Added cache_busting to default asset config (GeertDD)
- `c837ea1 <https://github.com/symfony/symfony-docs/commit/c837ea105494ce53049ab38dbeb0834904c4ec1f>`_ #4665 Documented the console environment variables (javiereguiluz)
- `0e45e29 <https://github.com/symfony/symfony-docs/commit/0e45e292f210aa162c6bfccfef11ccaa76026b7b>`_ #4655 Document new progressbar methods (javiereguiluz)
- `f4a7196 <https://github.com/symfony/symfony-docs/commit/f4a71967a8887ce6a6531294bfc001ddfedd9ade>`_ #4627 Rewrite the varnish cookbook article (dbu)
- `92a186d <https://github.com/symfony/symfony-docs/commit/92a186d8d02e2927d26c995c72eaefe246d6ae04>`_ #4654 Rewritten from scratch the chapter about installing Symfony (javiereguiluz)
- `90ef4ec <https://github.com/symfony/symfony-docs/commit/90ef4ec1daff25b3c0f12048b0036757593cefcd>`_ #4580 Updated installation instructions to use the new Symfony Installer (javiereguiluz)
- `f591e6e <https://github.com/symfony/symfony-docs/commit/f591e6e1d7b04faf5703dd76be10d2f8d5870dcd>`_ #4532 GetResponse*Events stop after a response was set (Lumbendil)
- `a09fd7b <https://github.com/symfony/symfony-docs/commit/a09fd7b25d26c21d9eff1f972c901652941af982>`_ #4485 Added documentation about the DebugFormatter helper (WouterJ)
- `d327bae <https://github.com/symfony/symfony-docs/commit/d327bae97c091233d5645114be84ebb47c57f99f>`_ #4557 Update pdo_session_storage.rst (spbentz)
- `71495e8 <https://github.com/symfony/symfony-docs/commit/71495e81eebd61fba8bdb93da5018163cb768fb3>`_ #4528 Update web_server_configuration.rst (thePanz)
- `3b9d60d <https://github.com/symfony/symfony-docs/commit/3b9d60d2b018d895804710d4e684b7cf8c6af59b>`_ #4517 [Reference] document configurable PropertyAccessor arguments (xabbuh)
- `9b330ef <https://github.com/symfony/symfony-docs/commit/9b330efdf38ee568cd7b6e5cf7aada74c9a3e511>`_ #4507 Comply with best practices, Round 2 (WouterJ)
- `39a36bc <https://github.com/symfony/symfony-docs/commit/39a36bcb82540e6b9670f9ca7a0e81f76e0c0535>`_ #4405 Finish 3744 (mickaelandrieu, xabbuh)
- `5363542 <https://github.com/symfony/symfony-docs/commit/53635425ee563415f762836972fa076269f9073b>`_ #4188 Updated documentation regarding the SecurityContext split (iltar)
- `f30f753 <https://github.com/symfony/symfony-docs/commit/f30f7536530108befb581745dbbb2524d7624c38>`_ #4050 [Translation] added logging capability. (aitboudad)
- `db35c42 <https://github.com/symfony/symfony-docs/commit/db35c4242a724325cb6ae7f0dbb42ed769ae1f88>`_ #4591 Instructions for setting SYMFONY_ENV on Heroku (dzuelke)
- `8bba316 <https://github.com/symfony/symfony-docs/commit/8bba31667333f654349f151e116593a9580c1c46>`_ #4457 [RFC] Clarification on formatting for bangs (!) (bryanagee)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `79db0b9 <https://github.com/symfony/symfony-docs/commit/79db0b9c6e8e03f65159c74c85bae4f630515718>`_ #4699 Use new security.authorization_checker service (xelaris)
- `9c819b4 <https://github.com/symfony/symfony-docs/commit/9c819b405f4cdafae8b74590bbbfd227c561a182>`_ #4713 [Security] Removed deprecated example about SecurityContext (iltar)
- `153565e <https://github.com/symfony/symfony-docs/commit/153565e108185904ad013f05ab65caa8c147df33>`_ #4707 [Cookbook] Fix XML example for RTE (dunglas)
- `cad4d3f <https://github.com/symfony/symfony-docs/commit/cad4d3f88bc3edffab2133f0689d03d4297ddeb4>`_ #4582 Completed the needed context to successfully test commands with Helpers (peterrehm)
- `a137918 <https://github.com/symfony/symfony-docs/commit/a137918e8d1a38b30d66c1166f7f8a4597c20e8d>`_ #4641 Add  missing autoload include in basic console application example (senkal)
- `0de8286 <https://github.com/symfony/symfony-docs/commit/0de8286b9a9073f335e300a19ba48e969ed30f6a>`_ #4513 [Contributing] update contribution guide for 2.7/3.0 (xabbuh)
- `8b611e2 <https://github.com/symfony/symfony-docs/commit/8b611e2258df776a10bce46be09215e3d3d003fc>`_ #4598 [ExpressionLanguage] add missing argument (xabbuh)
- `7ea4b10 <https://github.com/symfony/symfony-docs/commit/7ea4b108462ac43b5b3c371acb1f82ef0c8b4856>`_ #4646 Update the_controller.rst (teggen)
- `a2ea256 <https://github.com/symfony/symfony-docs/commit/a2ea256083cf54c1b712a489d17df1fefe98deb9>`_ #4637 fixed StringExpressionLanguageProvider code example #4636 (danieleorler)
- `63be343 <https://github.com/symfony/symfony-docs/commit/63be343e2aca5c24645a52f59ed8b0d30126d5ca>`_ #4630 [OptionsResolver] Fix namespace (xavren)
- `baf61a0 <https://github.com/symfony/symfony-docs/commit/baf61a06048a6901c4a9257b3d893413591b78eb>`_ #4623 [OptionsResolver] Fix Namespace link (xavren)
- `8246693 <https://github.com/symfony/symfony-docs/commit/82466930edbaa25e8810b9bc465fdaec937c2339>`_ #4613 Change refering block name from content to body (martin-cerny)
- `1750b9b <https://github.com/symfony/symfony-docs/commit/1750b9b80a778dc5bc52cb7ea451ec1c6d2fc977>`_ #4599 [Contributing] fix feature freeze dates (xabbuh)
- `8e2e988 <https://github.com/symfony/symfony-docs/commit/8e2e988122facccf6e79cf02c25ebc4ecccf18b1>`_ #4603 Replace form_enctype(form) with form_start(form). (xelaris)
- `7acf27c <https://github.com/symfony/symfony-docs/commit/7acf27c42853e366270149335cdc3bc522f28a1d>`_ #4552 required PHPUnit version in the docs should be updated to 4.2 (or later)... (jzawadzki)
- `df60ba7 <https://github.com/symfony/symfony-docs/commit/df60ba7d9e74abf5dc0e7ec601874503829358ee>`_ #4548 Remove ExpressionLanguage reference for 2.3 version (dangarzon)
- `727c92a <https://github.com/symfony/symfony-docs/commit/727c92a2aa10314df7cede068b87157cd77c2424>`_ #4594 Missing attribute 'original' (Marcelsj)
- `97a9c43 <https://github.com/symfony/symfony-docs/commit/97a9c43bd8822d273c2ee5378bb8ca6d7c6a3c44>`_ #4533 Add command to make symfony.phar executable. (xelaris)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `8bd694f <https://github.com/symfony/symfony-docs/commit/8bd694f4f0f51faf8b744a2b077a58031e8a9d61>`_ #4709 [Reference] fix wording (xabbuh)
- `1bd9ed4 <https://github.com/symfony/symfony-docs/commit/1bd9ed40132cd1f46daafd958b4b3fe79a563d09>`_ #4721 [Cookbook][Composer] fix note directive (xabbuh)
- `5055ef4 <https://github.com/symfony/symfony-docs/commit/5055ef46ec4d38c4f32351a5cf0c802cf35f210a>`_ #4715 Improve readability (ifdattic)
- `d3d6d22 <https://github.com/symfony/symfony-docs/commit/d3d6d2212f1321e1537cd98053412ca3710bbc91>`_ #4716 Fix typo: con => on (ifdattic)
- `afe8684 <https://github.com/symfony/symfony-docs/commit/afe86848ec3a2244690e17c4a68208ec4c1b535c>`_ #4720 Link fixed (kuldipem)
- `4b442a0 <https://github.com/symfony/symfony-docs/commit/4b442a0b572979c73e70e18d7c9edaf0a17e9ff5>`_ #4695 Misc changes (ifdattic)
- `0db36ea <https://github.com/symfony/symfony-docs/commit/0db36ea40e4536f0778ca6166b892bdfb9e00f4b>`_ #4706 Fix typo: than in Twig => than Twig templates (ifdattic)
- `94b833e <https://github.com/symfony/symfony-docs/commit/94b833ea46d1af54117249ecf9cee94150c37c13>`_ #4679 General grammar and style fixes in the book (frne)
- `3f3464f <https://github.com/symfony/symfony-docs/commit/3f3464f88769a0a3cd5f48d414140cd13d66350f>`_ #4689 Update form_customization.rst (rodrigorigotti)
- `8d32393 <https://github.com/symfony/symfony-docs/commit/8d3239334cb79126dde232c3eb68d85da2b49980>`_ #4691 replace "or" with "," (timglabisch)
- `9b4d747 <https://github.com/symfony/symfony-docs/commit/9b4d74796318065dadec40b10109f20fdfe3eb35>`_ #4670 Change PHPUnit link to avoid redirect to homepage (xelaris)
- `8ccffb0 <https://github.com/symfony/symfony-docs/commit/8ccffb08e503e0ff23e909edc3062447305e3701>`_ #4669 Harmonize PHPUnit version to 4.2 or above (xelaris)
- `84bf5e5 <https://github.com/symfony/symfony-docs/commit/84bf5e5c50a34f68038c02387250d05d9faf4cd0>`_ #4667 Remove redundant "default" connection (xelaris)
- `ceca63f <https://github.com/symfony/symfony-docs/commit/ceca63f2ef3d785652e6808045c9a459516ac788>`_ #4653 update ordered list syntax (xabbuh)
- `459875b <https://github.com/symfony/symfony-docs/commit/459875becb9ff243a0e5c9da1a45c247806cb3cd>`_ #4550 Ref #3903 - Normalize methods listings (ternel)
- `87365fa <https://github.com/symfony/symfony-docs/commit/87365fa1fc14bb86401a0f65125314ac76225b27>`_ #4648 Update forms.rst (keefekwan)
- `70f2ae8 <https://github.com/symfony/symfony-docs/commit/70f2ae8194b56f268400f7e51ed5fa3e3d29dfe7>`_ #4640 [Book] link to the API documentation (xabbuh)
- `95fc487 <https://github.com/symfony/symfony-docs/commit/95fc4874aa19666f026aee771b685c31775d51b1>`_ #4608 Removing some installation instructions (weaverryan)
- `96455e6 <https://github.com/symfony/symfony-docs/commit/96455e6c20a07de109ad5b6f97f726ad639ff195>`_ #4539 Normalization of method listings (pedronofuentes)
- `bd44e6b <https://github.com/symfony/symfony-docs/commit/bd44e6b607b15ae5c1e1b25c880d50b4ead79755>`_ #4664 Spelling mistake tens to tons (albabar)
- `3b6341a <https://github.com/symfony/symfony-docs/commit/3b6341a5ced3aa619a1014ab091fa171cd1af8e2>`_ #4663 Removed double `````firewall_restriction````` entry (vlad-ghita)
- `815e0bf <https://github.com/symfony/symfony-docs/commit/815e0bf97745c7cbd56852ed5ef5639dc3ddb309>`_ #4551 Normalize the method listings on version 2.5 (pedronofuentes)
- `48cc9cd <https://github.com/symfony/symfony-docs/commit/48cc9cdad1a97162de4cba83eb56284e899d9fcd>`_ #4647 Update controllers.rst (keefekwan)
- `2efed8c <https://github.com/symfony/symfony-docs/commit/2efed8cdafbf28c64fe6af32ce16ac325959c999>`_ #4660 Fix indentation of YAML example (xelaris)
- `b55ec30 <https://github.com/symfony/symfony-docs/commit/b55ec30b98fa64009fc223df652c71ea45136e63>`_ #4659 Fixed some code indentation (javiereguiluz)
- `18af18b <https://github.com/symfony/symfony-docs/commit/18af18b79a755a14b477f1ff3ea3cb8a15547411>`_ #4652 replace Symfony2 with Symfony (xabbuh)
- `a70c489 <https://github.com/symfony/symfony-docs/commit/a70c4890a57777c4ae0fe4d4a2e3562e13dab058>`_ #4649 Linked the PDO/DBAL Session article from the Doctrine category (javiereguiluz)
- `f672a66 <https://github.com/symfony/symfony-docs/commit/f672a66de496793fbe0e6b9b8712410dff576463>`_ #4625 Added '-ing' title ending to unify titles look (kix)
- `9600950 <https://github.com/symfony/symfony-docs/commit/96009506bcb888c2a221502a1c770dd15dba8baf>`_ #4617 [Filesystem] filesystem headlines match method names (xabbuh)
- `8b006bb <https://github.com/symfony/symfony-docs/commit/8b006bb4e75f40e43cbbe4ada279dad92134c159>`_ #4607 [Best Practices] readd mistakenly removed label (xabbuh)
- `7dcce1b <https://github.com/symfony/symfony-docs/commit/7dcce1b072618bbe14c64c599798ae0ec2fb7934>`_ #4585 When explaining how to install dependencies for running unit tests, (carlosbuenosvinos)
- `1c9270d <https://github.com/symfony/symfony-docs/commit/1c9270d10bfd5c29743d16f37444a6489d90a5b9>`_ #4568 Update Symfony reference to 2.6 version (dangarzon)
- `33ca697 <https://github.com/symfony/symfony-docs/commit/33ca697e663f7a8ed23d83e508d9c716d8117c7b>`_ #4561 Use the new build env on Travis (joshk)
- `107610e <https://github.com/symfony/symfony-docs/commit/107610e9df251c55ee835b2ed1fe88ee66e3521b>`_ #4531 [symfony] [Hackday] Fixed typos (pborreli)
- `3b1611d <https://github.com/symfony/symfony-docs/commit/3b1611d950f9296e0fe384d85a163ffb670fab9e>`_ #4519 remove service class parameters (xabbuh)
- `3bd17af <https://github.com/symfony/symfony-docs/commit/3bd17af2d7d8c69a15d1042b687af08b0286dbd8>`_ #4518 [Components][DependencyInjection] backport service factory improvements (xabbuh)
- `d203e5a <https://github.com/symfony/symfony-docs/commit/d203e5aaf7417e7264b30ef75cfa19e7e883942e>`_ #4495 [Best Practices][Business Logic] link to a bundle's current (not master) docs (xabbuh)
- `0a9c146 <https://github.com/symfony/symfony-docs/commit/0a9c1467aa858ba9407963cef8455488b2a31877>`_ #4422 Fix typos in code (ifdattic)
- `4f0051d <https://github.com/symfony/symfony-docs/commit/4f0051db9d14580ac7ddb3a235b7d74fb666355e>`_ #4574 fixed little typo (adridev)

November, 2014
--------------

New Documentation
~~~~~~~~~~~~~~~~~

- `33554fc <https://github.com/symfony/symfony-docs/commit/33554fcbb06408b222cb0c36af606f113c35afb0>`_ #4456 New validation API usage in Class Constraint Validator (skwi)
- `135aae6 <https://github.com/symfony/symfony-docs/commit/135aae6caa9508b12a2960579ea0af4dacd3e479>`_ #4433 Completely re-reading the controller chapter (weaverryan)
- `f748378 <https://github.com/symfony/symfony-docs/commit/f748378399689ba1b126821895f4c443c20cc568>`_ #4498 Use new factory syntax (WouterJ)
- `59f0374 <https://github.com/symfony/symfony-docs/commit/59f037432effc2ab243d40a16667d87865a1e45c>`_ #4490 Documented ExpressionLanguage extensibility (WouterJ)
- `ed241ab <https://github.com/symfony/symfony-docs/commit/ed241ab0d4d8afcc78bca39f9e5ee8168bf3a6cb>`_ #4487 Documented html5 option (WouterJ)
- `48a5af3 <https://github.com/symfony/symfony-docs/commit/48a5af3a057c075e3d86639857ecaaed561984c7>`_ #4486 Renamed empty_value to placeholder (WouterJ)
- `422e0f1 <https://github.com/symfony/symfony-docs/commit/422e0f12a4412f9e1668094eb19df4163bc6f2d8>`_ #4465 Modifying the best practice to use form_start() instead of <form (weaverryan, WouterJ)
- `0a21446 <https://github.com/symfony/symfony-docs/commit/0a21446430347724f8ef29aa51ff15fd0284f22e>`_ #4463 [BestPractices] Proposing that we make the service names *just* a little bit longer (weaverryan)
- `9a22865 <https://github.com/symfony/symfony-docs/commit/9a22865bd9e0ea68cb0ef74f762dd69f4d905db7>`_ #4446 [Book][Templating] refer to the VarDumper component for dump() (xabbuh)
- `ed5c61f <https://github.com/symfony/symfony-docs/commit/ed5c61faa37e06aa6c21b8036ecb32403b1b846c>`_ #4411 Added a reference to the Bootstrap 3 form theme (javiereguiluz)
- `766e01f <https://github.com/symfony/symfony-docs/commit/766e01fa0698203db0661b11cdf941daac657459>`_ #4169 [Components][Form] document $deep and $flatten of getErrors() (xabbuh)
- `1d88a1b <https://github.com/symfony/symfony-docs/commit/1d88a1b6b029d4dd2e14534f44e3b1b6b745caf9>`_ #4443 Added the release dates for the upcoming Symfony 3 versions (javiereguiluz)
- `3329bd2 <https://github.com/symfony/symfony-docs/commit/3329bd2e1fd17c2ad0ef3c7d3700922ba009ff5c>`_ #4424 [#4243] Tweaks to the new var-dumper component (weaverryan, nicolas-grekas)
- `9caea6f <https://github.com/symfony/symfony-docs/commit/9caea6f5b0a4e2e3ecfeb8f9e32199db2bdba47c>`_ #4336 [Form] Add entity manager instance support for em option (egeloen)
- `f2ab245 <https://github.com/symfony/symfony-docs/commit/f2ab245ac945e86fe4c6553efe37acb6556a23bd>`_ #4374 [WCM] Revamped the Quick Start tutorial (javiereguiluz)
- `2c190ed <https://github.com/symfony/symfony-docs/commit/2c190ed8af7517607931dca6da41b4f7ad987b6c>`_ #4427 Update most important book articles to follow the best practices (WouterJ)
- `12a09ab <https://github.com/symfony/symfony-docs/commit/12a09ab7806b4f57d109929fa03c770cc7e03169>`_ #4377 Added interlinking and fixed install template for reusable bundles (WouterJ)
- `8259d71 <https://github.com/symfony/symfony-docs/commit/8259d712997ef2b94cc6b4490c46e603db64bcd9>`_ #4425 Updating component usage to use composer require (weaverryan)
- `0e80aba <https://github.com/symfony/symfony-docs/commit/0e80aba9c96efc4c6c70c0920d679182571fe97e>`_ #4369 [reference][configuration][security]Added key_length for pbkdf2 encoder (Guillaume-Rossignol)
- `d1afa4d <https://github.com/symfony/symfony-docs/commit/d1afa4d2f5c5bafa25618152b33e8ca5330e082b>`_ #4243 [WIP] var-dumper component (nicolas-grekas)
- `5165419 <https://github.com/symfony/symfony-docs/commit/51654191bea12960f64ead9a00cf0c293532246a>`_ #4295 [Security] Hidden front controller for Nginx (phansys)
- `23f790a <https://github.com/symfony/symfony-docs/commit/23f790a3f2abee8dece1a0e631bf6ac5be1e28c2>`_ #4058 Skip console commands from event listeners (tPl0ch)
- `4b98d48 <https://github.com/symfony/symfony-docs/commit/4b98d48ba630851004df159256fb3fbbb0be79d4>`_ #3386 [Translation] added method to expose collected message (Grygir)
- `242d4f6 <https://github.com/symfony/symfony-docs/commit/242d4f68726ad3d6445ff97e05809ca2bebed176>`_ #4319 Documentation for debug:event-dispatcher command (matthieuauger)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `9d599a0 <https://github.com/symfony/symfony-docs/commit/9d599a054007474f1b65d2c7b04f634c5faf74d0>`_ minor #4544 #4273 - fix doctrine version in How to Provide Model Classes for several Doctrine Implementations cookbook (ternel)
- `6aabece <https://github.com/symfony/symfony-docs/commit/6aabece040cda7976a6b702bf4e7a8cd2818e007>`_ #4273 - fix doctrine version in How to Provide Model Classes for several Doctrine Implementations cookbook
- `e96ebd3 <https://github.com/symfony/symfony-docs/commit/e96ebd3a36ac73800bf31b6ddaa9a63ad37b3da4>`_ #4522 Add missing brackets to PropertyAccessor examples (loonytoons)
- `4f66d48 <https://github.com/symfony/symfony-docs/commit/4f66d4842d8c1d515f2c4430db889af021b1b0cf>`_ #4506 SetDescription required on Product entities (yearofthegus)
- `85bf906 <https://github.com/symfony/symfony-docs/commit/85bf906dfa3f0a09847db005e1f04216e080e4c1>`_ #4444 fix elseif statement (MightyBranch)
- `ad14e78 <https://github.com/symfony/symfony-docs/commit/ad14e7803ed8be7f066ee67c748d00c3ddaf3d8b>`_ #4494 Updated the Symfony Installer installation instructions (javiereguiluz)
- `7cc4287 <https://github.com/symfony/symfony-docs/commit/7cc42876e4ce134695d4b3069d27a8e6e927e5af>`_ #4442 replace doc role for bundle docs with external ref (xabbuh)
- `33bf462 <https://github.com/symfony/symfony-docs/commit/33bf4627545e212a10af5180b790428ebacf0ae3>`_ #4407 [Components][Console] array options need array default values (xabbuh)
- `2ab2e1f <https://github.com/symfony/symfony-docs/commit/2ab2e1f712c693f039297311c9396ef120a48ec1>`_ #4342 Reworded a misleading Doctrine explanation (javiereguiluz)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `a109c4b <https://github.com/symfony/symfony-docs/commit/a109c4b739e35bcf600868df801bd5cefb911cbc>`_ #4537 Update link to remove absolute URL (jms85, dangarzon)
- `05f5dba <https://github.com/symfony/symfony-docs/commit/05f5dbacac092554ab3398601be92120945cd221>`_ #4536 Add Ryan Weaver as 10th core team member (ifdattic)
- `7b1ff2a <https://github.com/symfony/symfony-docs/commit/7b1ff2a33964ee29adbd2e240a3bc382b9ca16a3>`_ #4554 Changed url to PHP-CS-FIXER repository (jzawadzki)
- `9d599a0 <https://github.com/symfony/symfony-docs/commit/9d599a054007474f1b65d2c7b04f634c5faf74d0>`_ #4544 bug #4273 - fix doctrine version in How to Provide Model Classes for several Doctrine Implementations cookbook (ternel)
- `7b3500c <https://github.com/symfony/symfony-docs/commit/7b3500ca43b1f411657f6aa37cd4d84aed8ff525>`_ #4542 Update conventions.rst (csuarez)
- `5aaba1e <https://github.com/symfony/symfony-docs/commit/5aaba1e336cc85254882c28f34389725d65636df>`_ #4529 Best Practices: Update link title to match cookbook article title (dangarzon)
- `ab8e7f5 <https://github.com/symfony/symfony-docs/commit/ab8e7f59ec106d5aade1c696f8731671455aee83>`_ #4530 Book: Update link title to match cookbook article title (dangarzon)
- `bf61658 <https://github.com/symfony/symfony-docs/commit/bf616581f4ccce6d355413595fb6fe69831fc553>`_ #4523 Add missing semicolons to PropertyAccess examples (loonytoons)
- `8beadce <https://github.com/symfony/symfony-docs/commit/8beadce92dbc15b1c40d443abbf33e2ae4b86007>`_ #4496 [Book][Security] link to a bundle's current (not master) docs (xabbuh)
- `43809b1 <https://github.com/symfony/symfony-docs/commit/43809b1f9a13fb4c20d8ac4d3b573fb73d614a28>`_ #4479 remove versionadded directives for old versions (xabbuh)
- `5db8386 <https://github.com/symfony/symfony-docs/commit/5db83862999bb2e8c2a30c6bc36cc0cf7a6a24a9>`_ #4462 [Reference] Fixed lots of things using the review bot (WouterJ)
- `dbfaac1 <https://github.com/symfony/symfony-docs/commit/dbfaac1f1b307a6143e3f8b5a1c326c0b330d662>`_ #4459 Fix up the final sentence to be a bit cleaner. (micheal)
- `3761e50 <https://github.com/symfony/symfony-docs/commit/3761e50b36cb274af2d0de72a129ad2010d9c5ca>`_ #4514 [Contributing][Documentation] typo fix (xabbuh)
- `21afb4c <https://github.com/symfony/symfony-docs/commit/21afb4c7c27d4737a5f6d471c88422303264d752>`_ #4445 Removed unnecessary use statement (Alex Salguero)
- `3969fd6 <https://github.com/symfony/symfony-docs/commit/3969fd6d3e85966973692961e2c6b13437c653f2>`_ #4432 [Reference][Twig] tweaks to the Twig reference (xabbuh)
- `188dd1f <https://github.com/symfony/symfony-docs/commit/188dd1f17f96c7d31950ba30314683fae0614ba3>`_ #4400 Continues #4307 (SamanShafigh, WouterJ)
- `c008733 <https://github.com/symfony/symfony-docs/commit/c008733b9ee8d7e12507ab2cf813d03808b49ab5>`_ #4399 Explain form() and form_widget() in form customization (oopsFrogs, WouterJ)
- `2139754 <https://github.com/symfony/symfony-docs/commit/2139754cbb0c7ef871daae30a7eff4205dc83794>`_ #4253 Adder and remover sidenote (kshishkin)
- `b81eb4d <https://github.com/symfony/symfony-docs/commit/b81eb4db59de238334b477a5fbdc1d336b82e4f0>`_ #4488 Terrible mistake! Comma instead of semicolon... (nuvolapl)
- `0ee3ae7 <https://github.com/symfony/symfony-docs/commit/0ee3ae74457af8082364fafa7b4b1d7d6a6eab17>`_ #4481 [Cookbook][Cache] add syntax highlighting for Varnish code blocks (xabbuh)
- `0577559 <https://github.com/symfony/symfony-docs/commit/057755984ebdf7c334ec69cce62b61f9daf296ae>`_ #4418 use the C lexer for Varnish config examples (xabbuh)
- `97d8f61 <https://github.com/symfony/symfony-docs/commit/97d8f616f02e49daeb93dfc5719be312a3317292>`_ #4403 Improved naming (WouterJ)
- `6298595 <https://github.com/symfony/symfony-docs/commit/6298595e44c6f1ce759114668000477fb5da5e4e>`_ #4453 Fixed make file (WouterJ)
- `0c7dd72 <https://github.com/symfony/symfony-docs/commit/0c7dd721e1b3ae633de989448e9ffa7c77d849cd>`_ #4475 Fixed typos (pborreli)
- `b847b2d <https://github.com/symfony/symfony-docs/commit/b847b2d8af6720117b4ebbd421ac0da15c769075>`_ #4480 Fix spelling (nurikabe)
- `0d91cc5 <https://github.com/symfony/symfony-docs/commit/0d91cc5dd90a5febc76d54ba16853db3aae9217c>`_ #4461 Update doctrine.rst (guiguiboy)
- `81fc1c6 <https://github.com/symfony/symfony-docs/commit/81fc1c6000bb64ce02868cee8e618041d0f9766f>`_ #4448 [Book][HTTP Cache] moved inlined URL to the bottom of the file (xabbuh)
- `6995b07 <https://github.com/symfony/symfony-docs/commit/6995b07f2330004f466da5f86b599acc402a4d72>`_ #4435 consistent table headlines (xabbuh)
- `0380d34 <https://github.com/symfony/symfony-docs/commit/0380d340674fc0e9776f30e0f737a6730ba9f232>`_ #4447 [Book] tweaks to #4427 (xabbuh)
- `eb0d8ac <https://github.com/symfony/symfony-docs/commit/eb0d8acc985417368878b77e6313f4b4a748f168>`_ #4441 Updated first code-block``::`` bash (Nitaco)
- `41bc061 <https://github.com/symfony/symfony-docs/commit/41bc061762b8cf73e8ef0d9317a1af68a2a4c564>`_ #4106 removed references to documentation from external sources (fabpot, WouterJ)
- `c9a8dff <https://github.com/symfony/symfony-docs/commit/c9a8dffbb352ba3b61003fabaa6cd2e9fe63b038>`_ #4352 [Best Practices] update best practices index (xabbuh)
- `8a93c95 <https://github.com/symfony/symfony-docs/commit/8a93c958a3ab0450cb841e36d1653b3c3d96706b>`_ #4437 Correct link to scopes page (mayeco)
- `91eb652 <https://github.com/symfony/symfony-docs/commit/91eb65253b854a65435d4d943507c5b736e6cce4>`_ #4438 Fix typo: Objected => Object (ifdattic)
- `5d6d0c2 <https://github.com/symfony/symfony-docs/commit/5d6d0c2c16178a214e67ffa2fbaac7879f53e54d>`_ #4436 remove semicolons in PHP templates (xabbuh)
- `97c4b2e <https://github.com/symfony/symfony-docs/commit/97c4b2e152c42fc5bb953e41666117fea6619b5e>`_ #4434 remove unused label (xabbuh)
- `4be6786 <https://github.com/symfony/symfony-docs/commit/4be678650bfcd4fe6ce208261485081fd9854bb1>`_ #4326 [Components][Form] Grammar improvement (fabschurt)
- `a27238e <https://github.com/symfony/symfony-docs/commit/a27238e1084c18692dd2e159fcf741ce07e32df9>`_ #4313 Improved and fixed twig reference (WouterJ)
- `1ce9dc5 <https://github.com/symfony/symfony-docs/commit/1ce9dc5bfbdedacd093f35282327f577a34bdb45>`_ #4398 A few small improvements to the EventDispatcher Component docs (GeertDD)
- `42abc66 <https://github.com/symfony/symfony-docs/commit/42abc66af0620c8f0bf0b2057764327c0cadc561>`_ #4421 [Best Practices] removed unused links in business-logic (77web)
- `61c0bc5 <https://github.com/symfony/symfony-docs/commit/61c0bc57dd5d4b6391dbd85585e4ed24fe3469af>`_ #4419 [DependencyInjection] Add missing space in code (michaelperrin)

October, 2014
-------------

New Documentation
~~~~~~~~~~~~~~~~~

- `d7ef1c7 <https://github.com/symfony/symfony-docs/commit/d7ef1c753e427480e225310a45bd4cf352c14dc3>`_ #4348 Updated information about handling validation of embedded forms to Valid... (peterrehm)
- `691b13d <https://github.com/symfony/symfony-docs/commit/691b13d686b64884b6e91eb6ffdf1e70bd5b8154>`_ #4340 [Cookbook][Web Server] add sidebar for the built-in server in VMs (xabbuh)
- `bd85865 <https://github.com/symfony/symfony-docs/commit/bd85865f4b5e64b72d00f6b4e29e9c2015cde01d>`_ #4299 [Serializer] Handle circular references. symfony/symfony#12098. (dunglas)
- `d79c48d <https://github.com/symfony/symfony-docs/commit/d79c48dfdd6fa7b92c19d5092f2171dc99ad0b24>`_ #4280 [Cookbook][Cache] Added config example for Varnish 4.0 (thierrymarianne)
- `5849f7f <https://github.com/symfony/symfony-docs/commit/5849f7f5a7c8c0f4652be44b86df903d9a0f5db9>`_ #4168 [Components][Form] describe how to access form errors (xabbuh)
- `c10e9c1 <https://github.com/symfony/symfony-docs/commit/c10e9c19f8d425598a8be0578df4c84b232ff214>`_ #4371 Added a code example for emailing on 4xx and 5xx errors without 404's (weaverryan)
- `1117741 <https://github.com/symfony/symfony-docs/commit/111774187843dbad1667199944549d8add944c73>`_ #4159 [WCM][OptionsResolver] Adjusted the OptionsResolver documentation to describe the 2.6 API (webmozart, peterrehm)
- `0c57939 <https://github.com/symfony/symfony-docs/commit/0c57939e2be3b900a4eb0afa6aaa5a6620c9d239>`_ #4327 First import of the "Official Best Practices" book (javiereguiluz)
- `2cd6646 <https://github.com/symfony/symfony-docs/commit/2cd6646eac53b88ccf4ee2e2834ed82dcae66ef2>`_ #4293 Document error page preview (Symfony ~2.6) (mpdude)
- `142c826 <https://github.com/symfony/symfony-docs/commit/142c8263e206716fcf6709589960997b60ec2080>`_ #4005 [Cookbook][Web server] description for running PHP's built-in web server in the background (xabbuh)
- `8dc90ef <https://github.com/symfony/symfony-docs/commit/8dc90efdff19a00c55ee0be187866b1f97c0b16f>`_ #4224 [Components][HttpKernel] outline implications of the kernel.terminate event (xabbuh)
- `d3b5ba2 <https://github.com/symfony/symfony-docs/commit/d3b5ba29f42bc3d3c73abb2420ad05a8dbac54cc>`_ #4085 [Component][Forms] add missing features introduced in 2.3 (xabbuh)
- `f433e64 <https://github.com/symfony/symfony-docs/commit/f433e64f5878b15feaa5b86aa374b58e32f633c6>`_ #4099 Composer installation verbosity tip (dannykopping)
- `f583a45 <https://github.com/symfony/symfony-docs/commit/f583a45443e7424ef4ba459131873015b4f70aea>`_ #4204 [Reference][Constraints] validate `````null````` (Expression constraint in 2.6) (xabbuh)
- `925a162 <https://github.com/symfony/symfony-docs/commit/925a162879990d28d6093df1af7dfd22fcb27890>`_ #4290 Updating library/bundle install docs to use "require" (weaverryan)
- `86c67e8 <https://github.com/symfony/symfony-docs/commit/86c67e8ef1f666ede1b7009b792f7a65e466201e>`_ #4233 2.5 Validation API changes (nicolassing, lashae, Rootie, weaverryan)
- `0f34bb8 <https://github.com/symfony/symfony-docs/commit/0f34bb861128116c2f8267575746fc01d5c2fbfc>`_ #3956 [Command] Added LockHelper (lyrixx)
- `278de83 <https://github.com/symfony/symfony-docs/commit/278de830f208184e4569c40d91c48d3154b7c09c>`_ #3930 [Console] Add Process Helper documentation (romainneutron)
- `44f570b <https://github.com/symfony/symfony-docs/commit/44f570b3e5efbc3a318beb789b87ecf69febdfa1>`_ #4294 Improve cookbook entry for error pages in 2.3~ (mpdude)
- `3b6c2b9 <https://github.com/symfony/symfony-docs/commit/3b6c2b97f1ff7304cc981a74bc521e91e0ed1873>`_ #4269 [Cookbook][External Parameters] Enhance content (bicpi)
- `25a17fe <https://github.com/symfony/symfony-docs/commit/25a17fee9cb8ce1b4190ef5899c492244796d7f8>`_ #4264 [#4003] A few more form_themes config changes (weaverryan)
- `5b65654 <https://github.com/symfony/symfony-docs/commit/5b656542e09477eb98b5374f71d6c56cba3b9227>`_ #3912 [Security] Added remote_user firewall info and documentation for pre authenticated firewalls (Maxime Douailin, mdouailin)
- `62bafad <https://github.com/symfony/symfony-docs/commit/62bafad2a1cb6718bde3ae7d7612e65bfd3fd123>`_ #4246 [Reference] add description for the `````validation_groups````` option (xabbuh)
- `5d505bb <https://github.com/symfony/symfony-docs/commit/5d505bba4205110860184f3486f786c969cb86f8>`_ #4206 Added note about ProgressBar changes (kbond)
- `c2342a7 <https://github.com/symfony/symfony-docs/commit/c2342a72d0bc237c951b035a02651a1a3ac84c90>`_ #4241 [Form] Added information about float choice lists (peterrehm)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `dde6919 <https://github.com/symfony/symfony-docs/commit/dde6919223413344718ddd52594a38cd007eb005>`_ #4390 Update custom_constraint.rst (luciantugui)
- `68a2c7b <https://github.com/symfony/symfony-docs/commit/68a2c7b87b779aad5cbf02ad196aeb89aa4d0ee5>`_ #4381 Updated Valid constraint reference (inso)
- `dbb25b9 <https://github.com/symfony/symfony-docs/commit/dbb25b97ef29f12d85e3d2fa9ba05dc897a64184>`_ #4379 [OptionsResolver] Fix wrong namespace in example (rybakit)
- `db01e57 <https://github.com/symfony/symfony-docs/commit/db01e57482a66f52501b07de0edfbda6ee195465>`_ #4362 Missing apostrophe in source example. (astery)
- `d49d51f <https://github.com/symfony/symfony-docs/commit/d49d51ff97ca74c5acaced86293f4c29639f0c84>`_ #4350 Removed extra parenthesis (sivolobov)
- `e6d7d8f <https://github.com/symfony/symfony-docs/commit/e6d7d8f12617cd097818bb9fc36484c141299f4f>`_ #4315 Update choice.rst (odolbeau)
- `1b15d57 <https://github.com/symfony/symfony-docs/commit/1b15d57eaca4d78f249e3ebca9abebd0eab41cd3>`_ #4300 [Components][PropertyAccess] Fix PropertyAccessorBuilder usage (Thierry Geindre)
- `061324f <https://github.com/symfony/symfony-docs/commit/061324f69e8418d5cc8f5bef28ac7b3618f44e8c>`_ #4297 [Cookbook][Doctrine] Fix typo in XML configuration for custom SQL functions (jdecool)
- `f81b7ad <https://github.com/symfony/symfony-docs/commit/f81b7ad00cc5e2cdb9fbaf36c91db566b54970ff>`_ #4292 Fixed broken external link to DemoController Test (danielsan)
- `9591a04 <https://github.com/symfony/symfony-docs/commit/9591a04b612869a7078dc104589628a2f6d77965>`_ #4284 change misleading language identifier (Kristof Van Cauwenbergh, kristofvc)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `217bf5f <https://github.com/symfony/symfony-docs/commit/217bf5f58630b79bca357c93efad6e311b7e3b48>`_ #4353 [Cookbook][Controller] fix route prefix in PHP code example (xabbuh)
- `a4f7d51 <https://github.com/symfony/symfony-docs/commit/a4f7d5112776002e5509b66a255cd07dd5476d79>`_ #4396 Corrected latin abbreviation (GeertDD)
- `ebf2927 <https://github.com/symfony/symfony-docs/commit/ebf29274fba3d3b39a867c0ff58dd5457ce36c5e>`_ #4387 Inline condition removed for easier reading (acidjames)
- `aa70028 <https://github.com/symfony/symfony-docs/commit/aa70028c2c436c65a1e332151fcd7fecc8cb6cd2>`_ #4375 Removed the redundant usage of layer. (micheal)
- `f3dd676 <https://github.com/symfony/symfony-docs/commit/f3dd6763a0e33c03300c06e2396eb28b0d949f43>`_ #4394 update Sphinx extension submodule reference (xabbuh)
- `6406a27 <https://github.com/symfony/symfony-docs/commit/6406a276f100f2f5ccc0d53f96914844d3d594ca>`_ #4391 Removed unused use UsernameNotFoundException (boekkooi)
- `9e03f2d <https://github.com/symfony/symfony-docs/commit/9e03f2dd00f3aa0df16cfa001610d57636eba93c>`_ #4388 Minor spelling fix (GeertDD)
- `4dfd607 <https://github.com/symfony/symfony-docs/commit/4dfd607ecdeb4867507c30bc59caecdb90a16966>`_ #4356 Remove incoherence between Doctrine and Propel introduction paragraphs (arnaugm)
- `1d71332 <https://github.com/symfony/symfony-docs/commit/1d71332030658d0bfe91752af8cb007f929457cd>`_ #4344 [Templating] Added a sentence that explains what a Template Helper is (iltar)
- `22b9b27 <https://github.com/symfony/symfony-docs/commit/22b9b27859939e3b35fd733436df6f15216c0adc>`_ #4372 Tweaks after proofreading the 2.6 OptionsResolver stuff (weaverryan, WouterJ)
- `9a76309 <https://github.com/symfony/symfony-docs/commit/9a76309a14c7eabe077111dfdc9ac74aab6593c9>`_ #4384 fix typo (kokoon)
- `eb752cc <https://github.com/symfony/symfony-docs/commit/eb752ccdd8050db4487f7d0f0711cad2691ecded>`_ #4363 Fixed sentence (WouterJ)
- `3e8aa59 <https://github.com/symfony/symfony-docs/commit/3e8aa59ba74ac1b1ec5938de8370bf449e3790dc>`_ #4376 Cleaned up javascript code (flip111)
- `06e7c5f <https://github.com/symfony/symfony-docs/commit/06e7c5f54a75e55d0cdb6ddf364aa8a17bbda22d>`_ #4364 changed submit button label (OskarStark)
- `d1810ca <https://github.com/symfony/symfony-docs/commit/d1810ca52c177c19fbc870ee733751427fcdba37>`_ #4357 fix Twig-extensions links (mhor)
- `e2e2915 <https://github.com/symfony/symfony-docs/commit/e2e29153aa2457bd8103aca1ef0459f3b2b7bb38>`_ #4359 Added missing closing parenthesis to example. (mattjanssen)
- `f1bb8bb <https://github.com/symfony/symfony-docs/commit/f1bb8bbdd0eb372e5fb880a9bd3a942ef8a84545>`_ #4358 Fixed link to documentation standards (sivolobov)
- `65c891d <https://github.com/symfony/symfony-docs/commit/65c891dd2403facb6d8df06520d458155e58e3a5>`_ #4355 Missing space (ErikSaunier)
- `7359cb4 <https://github.com/symfony/symfony-docs/commit/7359cb4d35091055683358a0674747f0acb2146c>`_ #4196 Clarified the bundle base template bit. (Veltar)
- `6ceb8cb <https://github.com/symfony/symfony-docs/commit/6ceb8cbf0d3e448f66d5ab822c4e68c33aef1ec3>`_ #4345 Correct capitalization for the Content-Type header (GeertDD)
- `3e4c92a <https://github.com/symfony/symfony-docs/commit/3e4c92a12cf794dc09ccb640c2b1928213e06d80>`_ #4104 Use ${APACHE_LOG_DIR} instead of /var/log/apache2 (xamgreen)
- `3da0776 <https://github.com/symfony/symfony-docs/commit/3da0776b502dd23b399c3e005a27e965dc70e46f>`_ #4338 ESI Variable Details Continuation (Farkie, weaverryan)
- `7f461d2 <https://github.com/symfony/symfony-docs/commit/7f461d23ebc2cabf52380ca85582a581b7b0482b>`_ #4325 [Components][Form] Correct a typo (fabschurt)
- `d162329 <https://github.com/symfony/symfony-docs/commit/d1623295fb561849de82815eebba7f9db4e82651>`_ #4276 [Components][HttpFoundation] Make a small grammatical adjustment (fabschurt)
- `69bfac1 <https://github.com/symfony/symfony-docs/commit/69bfac17420c3a690ae170df530990859256d850>`_ #4322 [Components][DependencyInjection] Correct a typo: replace "then" by "the" (fabschurt)
- `8073239 <https://github.com/symfony/symfony-docs/commit/80732390c57c0a65a37e62230a1d76f617831bdf>`_ #4318 [Cookbook][Bundles] Correct a typo: remove unnecessary "the" word (fabschurt)
- `228111b <https://github.com/symfony/symfony-docs/commit/228111be589210136bac35dd70f1920f68996f54>`_ #4316 Remove horizontal scrollbar (ifdattic)
- `34e22d6 <https://github.com/symfony/symfony-docs/commit/34e22d6039a5130a57bb9755eeccdb51dd0de613>`_ #4317 Remove horizontal scrollbar and change event name to follow conventions (ifdattic)
- `090afab <https://github.com/symfony/symfony-docs/commit/090afab6e873171f58f8e48abfa4d09361937884>`_ #4287 support Varnish in configuration blocks (xabbuh)
- `1603463 <https://github.com/symfony/symfony-docs/commit/16034633f498c23af8abce8edce266f8af36a010>`_ #4306 Improve readability (ifdattic)
- `e5fed9d <https://github.com/symfony/symfony-docs/commit/e5fed9d772e7cb7140b7d10eff5e3149c7074c87>`_ #4303 Fix spelling (nurikabe)
- `31d7905 <https://github.com/symfony/symfony-docs/commit/31d79055a700df631165107d5b0b948690c165be>`_ #4302 View documentation had a reference to the wrong twig template (milan)
- `ef11ef4 <https://github.com/symfony/symfony-docs/commit/ef11ef40d1cc3b33d481ffda386773314903d5d5>`_ #4250 Clarifying Bundle Best Practices is for *reusable* bundles (weaverryan)
- `430eabf <https://github.com/symfony/symfony-docs/commit/430eabf0e563247884b1702b4c5149c83c9c49e4>`_ #4298 Book HTTP Fundamentals routing example fixed with routing.xml file (peterkokot)
- `a535c9f <https://github.com/symfony/symfony-docs/commit/a535c9f7cc3dae9626fb633890662d9e78634284>`_ #4285 Update security.rst (placid2000)
- `7ab6df9 <https://github.com/symfony/symfony-docs/commit/7ab6df94b858a0a11276ccc9d40c25e00806caa5>`_ #4237 Finished #3886 (ahsio, WouterJ)
- `990b453 <https://github.com/symfony/symfony-docs/commit/990b4531f8ee636e3167537bd0dce7c88da16b98>`_ #4245 [Contributing] tweaks to the contribution chapter (xabbuh)

September, 2014
---------------

New Documentation
~~~~~~~~~~~~~~~~~

- `e8a1501 <https://github.com/symfony/symfony-docs/commit/e8a15017248748fa6638fc39ec2e10d255d5277d>`_ #4201 [Components][Process] `````mustRun()````` documentation (xabbuh)
- `eac0e51 <https://github.com/symfony/symfony-docs/commit/eac0e5101ef8cb142fd37810e7f4faf2396c84d5>`_ #4195 Added a note about the total deprecation of YUI (javiereguiluz)
- `e44c791 <https://github.com/symfony/symfony-docs/commit/e44c791b510f0439c9727642390c4184d0b94227>`_ #4047 Documented info method (WouterJ)
- `2962e14 <https://github.com/symfony/symfony-docs/commit/2962e146fe30b97403eb5d1c0336881cc0962586>`_ #4003 [Twig][Form] Moved twig.form.resources to a higher level (stefanosala)
- `d5d46ec <https://github.com/symfony/symfony-docs/commit/d5d46ec61e2bd851914550274d97f142c9397a93>`_ #4017 Clarify that route defaults don't need a placeholder (iamdto)
- `1d56da4 <https://github.com/symfony/symfony-docs/commit/1d56da4384b09298d8ae11b8a14f1ae62b82d5ff>`_ #4239 Remove redundant references to trusting HttpCache (thewilkybarkid)
- `c306b68 <https://github.com/symfony/symfony-docs/commit/c306b68d7d40d6d5ca815ea1af2a7bda63f4f060>`_ #4249 provide node path on configuration (desarrolla2)
- `9f0f14e <https://github.com/symfony/symfony-docs/commit/9f0f14e90b1e03c2bab40410edbe625c421460b2>`_ #4210 Move debug commands to debug namespace (matthieuauger)
- `9b4b36f <https://github.com/symfony/symfony-docs/commit/9b4b36fa5f653e621e86363cd060c693c308c62f>`_ #4236 Javiereguiluz bundle install instructions (WouterJ)
- `ea068c2 <https://github.com/symfony/symfony-docs/commit/ea068c22ffff653f020ea488b3e3e54d96d32949>`_ #4202 [Reference][Constraints] caution on `````null````` values in Expression constraint (xabbuh)
- `a578de9 <https://github.com/symfony/symfony-docs/commit/a578de99b2ba4a83605a530a76111a4cf4daf1fa>`_ #4223 Revamped the documentation about "Contributing Docs" (javiereguiluz)
- `de60dbe <https://github.com/symfony/symfony-docs/commit/de60dbed64c719792c0259248e4dc52aefd6b088>`_ #4182 Added note about exporting SYMFONY_ENV (jpb0104)
- `a8dc2bf <https://github.com/symfony/symfony-docs/commit/a8dc2bfe0f5cfcc5af24565e195d8293f45ee393>`_ #4166 Translation custom loaders (raulfraile)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `c289ac8 <https://github.com/symfony/symfony-docs/commit/c289ac8fabe363683c57953a9b60db3765e897cb>`_ #4279 Double-quotes instead of single quotes (UnexpectedValueException in Windows 8) (galdiolo)
- `5500e0b <https://github.com/symfony/symfony-docs/commit/5500e0b211633e65de693f0eca7dddfef52ee35e>`_ #4267 Fix error in bundle installation standard example (WouterJ)
- `082755d <https://github.com/symfony/symfony-docs/commit/082755dac2385250c11a8c6024ca2d74048bd654>`_ #4240 [Components][EventDispatcher] fix ContainerAwareEventDispatcher definition (xabbuh)
- `2319d6a <https://github.com/symfony/symfony-docs/commit/2319d6a391ee16f772ac34b5c4336680a26f8992>`_ #4213 Handle "constraints" option in form unit testing (sarcher)
- `c567707 <https://github.com/symfony/symfony-docs/commit/c5677076f81b1c13d6230332fef0d5727354b9af>`_ #4222 [Components][DependencyInjection] do not reference services in parameters (xabbuh)
- `02d1091 <https://github.com/symfony/symfony-docs/commit/02d1091e9795cfc773bf3061b61e3933f08c4e11>`_ #4209 Fix method for adding placholders in progressBar (danez)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `df16779 <https://github.com/symfony/symfony-docs/commit/df167799641899e679a4022dcd260d2f63035276>`_ #4226 add note about parameters in imports (xabbuh)
- `c332063 <https://github.com/symfony/symfony-docs/commit/c3320637e61f053af6ce279b9d792b6a793eea4e>`_ #4278 Missing word in DependencyInjection => Types of Injection (fabschurt)
- `287c7bf <https://github.com/symfony/symfony-docs/commit/287c7bf8679f434c84405950215cd5ef7742637b>`_ #4275 added Nicolas to the list of mergers for the new var dumper component (fabpot)
- `3a4e226 <https://github.com/symfony/symfony-docs/commit/3a4e22689d6e72edcd5f628a6f74fea633919021>`_ #4263 Fixed typo (zebba)
- `187c255 <https://github.com/symfony/symfony-docs/commit/187c25511ebe2d9109bb356d7f0e62f22a3abaaa>`_ #4259 Added feature freeze dates for Symfony versions (javiereguiluz)
- `efc1436 <https://github.com/symfony/symfony-docs/commit/efc1436e02600aefbd39546068b362a4d22800cc>`_ #4247 [Reference] link translation DIC tags to components section (xabbuh)
- `17addb1 <https://github.com/symfony/symfony-docs/commit/17addb112a2a0e5b5b233a61b3541dd1f8aa58b9>`_ #4238 Finished #3924 (WouterJ)
- `19a0c35 <https://github.com/symfony/symfony-docs/commit/19a0c35d08af7f8ff474d6a52fac88c5d7db24f8>`_ #4252 Removed unnecessary comma (allejo)
- `9fd91d6 <https://github.com/symfony/symfony-docs/commit/9fd91d60644d2111bdc3a7dfc872b90aa3a97ac7>`_ #4219 Cache needs be cleared (burki94)
- `025f02e <https://github.com/symfony/symfony-docs/commit/025f02e7917473cc9a885b41de1fe9ca945044b8>`_ #4220 Added a note about the side effects of enabling both PHP and Twig (javiereguiluz)
- `46fcb67 <https://github.com/symfony/symfony-docs/commit/46fcb678392e2f0c8f0bbe34a94f01429e256a4a>`_ #4218 Caution that roles should start with ``ROLE_`` (jrjohnson)
- `78eea60 <https://github.com/symfony/symfony-docs/commit/78eea60c0cc93849732f071801b39042feed3cbf>`_ #4077 Removed outdated translations from the official list (WouterJ)
- `2cf9e47 <https://github.com/symfony/symfony-docs/commit/2cf9e47f4c3399db3f0b4a2a11647b6292cbdee3>`_ #4171 Fixed version for composer install (zomberg)
- `5c62b36 <https://github.com/symfony/symfony-docs/commit/5c62b36f63a308b9d7be262dff9a02b89d94600d>`_ #4216 Update Collection.rst (azarzag)
- `8591b87 <https://github.com/symfony/symfony-docs/commit/8591b872facc7a6b4bd1447d5438fe68cd21e112>`_ #4215 Fixed code highlighting (WouterJ)
- `8f01195 <https://github.com/symfony/symfony-docs/commit/8f01195970f38f5494186b904f7c4f536cff0461>`_ #4212 Missing backtick, thanks to @Baptouuuu (WouterJ)
- `f276e34 <https://github.com/symfony/symfony-docs/commit/f276e348faf446e26be9871bf49caa14a1a05a40>`_ #4205 replace "Symfony2" with "Symfony" (xabbuh)
- `6db13ac <https://github.com/symfony/symfony-docs/commit/6db13ac022006f9746c5a97a63a6f1f02458edca>`_ #4208 Added a note about the lacking features of Yaml Component (javiereguiluz)
- `f8c6201 <https://github.com/symfony/symfony-docs/commit/f8c6201b5c24661d2545618e2667949fd53c3020>`_ #4200 Moved 'contributing' images to their own directory (javiereguiluz)
- `b4650fa <https://github.com/symfony/symfony-docs/commit/b4650fa7a3c9b7cef533c1174e7c9783528be247>`_ #4199 fix name of the Yaml component (xabbuh)
- `9d89bb0 <https://github.com/symfony/symfony-docs/commit/9d89bb030714617d42f80e06e11d7c5fecb7b1b6>`_ #4190 add link to form testing chapter in test section (xabbuh)

August, 2014
------------

New Documentation
~~~~~~~~~~~~~~~~~

- `bccb080 <https://github.com/symfony/symfony-docs/commit/bccb080bd7eeb30e3d3c48c656be0c696b0cec85>`_ #4140 [Cookbook][Logging] document multiple recipients in XML configs (xabbuh)
- `7a6e3d1 <https://github.com/symfony/symfony-docs/commit/7a6e3d19115a027bcd717916548accb9702b2fe9>`_ #4150 Added the schema_filter option to the reference (peterrehm)
- `be90d8a <https://github.com/symfony/symfony-docs/commit/be90d8a631f9ec39f2307959bb43dc23e36fcf5a>`_ #4142 [Cookbook][Configuration] tweaks for the web server configuration chapter (xabbuh)
- `5379f54 <https://github.com/symfony/symfony-docs/commit/5379f5402b47b77f970c034834ea65efdce9b3f3>`_ #4086 [Reference][Constraints] Added hint about attaching the expression constraint to a form field (peterrehm)
- `041105c <https://github.com/symfony/symfony-docs/commit/041105c438f5f5072cbe6ceb380212412a2c5ef6>`_ #3883 Removed redundant POST request exclusion info (ryancastle)
- `4f9fef6 <https://github.com/symfony/symfony-docs/commit/4f9fef61a96cfda6fa5c3ac228647ccbdbde8ea9>`_ #4000 [Cookbook] add cookbook article for the server:run command (xabbuh)
- `4ea4dfe <https://github.com/symfony/symfony-docs/commit/4ea4dfecd531a18d4a10a697f3af8b8b9f44c365>`_ #3915 [Cookbook][Configuration] documentation of Apache + PHP-FPM (xabbuh)
- `79cb4f1 <https://github.com/symfony/symfony-docs/commit/79cb4f1a74ec583ed6d12083d1d51f36b5e6fb9b>`_ #4069 document the namespace alias (dbu)
- `08bed5f <https://github.com/symfony/symfony-docs/commit/08bed5fd0d9239ef5d59ec06e1c6571198daf275>`_ #4128 Finished #3759 (WouterJ)
- `4d5adaa <https://github.com/symfony/symfony-docs/commit/4d5adaa4ccef36fbec631ad05ce7389cbd575ebd>`_ #4125 Added link to JSFiddle example (WouterJ)
- `75bda4b <https://github.com/symfony/symfony-docs/commit/75bda4bcdcf887e5a01a399bdf7790c72499a2e9>`_ #4124 Rebased #3965 (WouterJ)
- `e2f13a4 <https://github.com/symfony/symfony-docs/commit/e2f13a482884061a4254d3d2b0479f2aefcf8bb4>`_ #4039 [DomCrawler] Added node name getter (fejese)
- `3f92d5f <https://github.com/symfony/symfony-docs/commit/3f92d5f5e77d63c9328ead5403ea1b3d23d62ceb>`_ #3966 [Cookbook][Controller] Add note about invokable controller services (kbond)
- `fdb8a32 <https://github.com/symfony/symfony-docs/commit/fdb8a324b07c183cbda5e8e77ae2f59f2319a301>`_ #3950 [Components][EventDispatcher] describe the usage of the RegisterListenersPass (xabbuh)
- `7e09383 <https://github.com/symfony/symfony-docs/commit/7e093830741508805d548402561b443874403760>`_ #3940 Updated docs for Monolog "swift" handler in cookbook. (phansys)
- `9d7c999 <https://github.com/symfony/symfony-docs/commit/9d7c9994515a021d940dda7753d7b99916fa21d1>`_ #3895 [Validator] Support "maxSize" given in KiB (jeremy-derusse)
- `8adfe98 <https://github.com/symfony/symfony-docs/commit/8adfe98d822c905ac7877affa14ec5e447662fbe>`_ #3894 Rewrote Extension & Configuration docs (WouterJ)
- `cafea43 <https://github.com/symfony/symfony-docs/commit/cafea438624ea9e36639f48d38483c292a6dd476>`_ #3888 Updated the example used to explain page creation (javiereguiluz)
- `df0cf68 <https://github.com/symfony/symfony-docs/commit/df0cf68b6ab3b8974c03482349602833f4ec5387>`_ #3885 [RFR] Added "How to Organize Configuration Files" cookbook (javiereguiluz)
- `41116da <https://github.com/symfony/symfony-docs/commit/41116dae3d9e34852cc3ef5e105d23f5c1f67c63>`_ #4081 [Components][ClassLoader] documentation for the ClassMapGenerator class (xabbuh)
- `2b9cb7c <https://github.com/symfony/symfony-docs/commit/2b9cb7c210245cd6586fd82ece291a5d290113f5>`_ #4076 Fixed description of session storage of the ApiKeyAuthenticator (peterrehm)
- `35a0f66 <https://github.com/symfony/symfony-docs/commit/35a0f66254429dff1c79e20925969509c90aba0b>`_ #4102 Adding a new entry about reverse proxies in the framework (weaverryan)
- `95c2066 <https://github.com/symfony/symfony-docs/commit/95c20664ddc5bb2c8e059b9acf44a938c573c19e>`_ #4096 labels in submit buttons + new screenshot (ricardclau)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `5fac303 <https://github.com/symfony/symfony-docs/commit/5fac303e45053fc3fec61fc64b1a8b5a2b819af0>`_ #4165 Update voters.rst (gerryvdm)
- `4882b99 <https://github.com/symfony/symfony-docs/commit/4882b998e3284211616e721e924fbcba0c8c96ce>`_ #4164 Fixed minor typos. (ahsio)
- `eaaa35a <https://github.com/symfony/symfony-docs/commit/eaaa35af3b9d9753484bfc0d35fc07bc933fdb0d>`_ #4145 Fix documentation for group_sequence_provider (giosh94mhz)
- `155c3e8 <https://github.com/symfony/symfony-docs/commit/155c3e8829c822125f7e4c69497ad6237b618a57>`_ #4153 [Reference] fix namespace in Expression constraint (xabbuh)
- `2c93aa5 <https://github.com/symfony/symfony-docs/commit/2c93aa52114f62cb59053340b91998630316465d>`_ #4147 [Cookbook][Logging] add missing Monolog handler type in XML config (xabbuh)
- `53b2c2b <https://github.com/symfony/symfony-docs/commit/53b2c2be9f077afb5120cb3a3b39d58961073861>`_ #4139 cleaned up the code example (gondo)
- `b5c9f2a <https://github.com/symfony/symfony-docs/commit/b5c9f2ae0ab8b3761bf005e36c3a20df12ae4c0f>`_ #4138 fixed wrongly linked dependency (gondo)
- `b486b22 <https://github.com/symfony/symfony-docs/commit/b486b220d3993cd0b2b60a75c2a6ba986439f5c4>`_ #4131 Replaced old way of specifying http method by the new one (Baptouuuu)
- `93481d7 <https://github.com/symfony/symfony-docs/commit/93481d7b2236394939941809020f82005194844a>`_ #4120 Fix use mistakes (mbutkereit)
- `c0a0120 <https://github.com/symfony/symfony-docs/commit/c0a0120cb59ae97abc813425a68311b458dc722e>`_ #4119 Fix class name in ConsoleTerminateListener example (alOneh)
- `4629d8b <https://github.com/symfony/symfony-docs/commit/4629d8b5293b6f93897765b8317e5486d7b1e5b2>`_ #4116 Fixed the code snippets for the expression language functions (stof)
- `d699255 <https://github.com/symfony/symfony-docs/commit/d6992559f1a4e59fe4f29db1a1b5961266d085c5>`_ #4083 [Reference] field dependent empty_data option description (xabbuh)
- `3ffc20f <https://github.com/symfony/symfony-docs/commit/3ffc20fcee21b3df5b16e7d270634ac4e682d163>`_ #4103 [Cookbook][Forms] fix PHP template file name (xabbuh)
- `234fa36 <https://github.com/symfony/symfony-docs/commit/234fa364612d70da60ad3cb997fca0123db473e6>`_ #4095 Fix php template (piotrantosik)
- `01fb9f2 <https://github.com/symfony/symfony-docs/commit/01fb9f245975d8769ab6f27e9c25578270d55a29>`_ #4093 See #4091 (dannykopping)
- `8f3a261 <https://github.com/symfony/symfony-docs/commit/8f3a261d1d1e6d6378cd67612ad9227a10334723>`_ #4092 See #4091 (dannykopping)
- `7d39b03 <https://github.com/symfony/symfony-docs/commit/7d39b03b09d60ebb0d400f080079e6d2a2d55bdd>`_ #4079 Fixed typo in filesystem component (kohkimakimoto)
- `f0bde03 <https://github.com/symfony/symfony-docs/commit/f0bde034a600179024847fc54fa0dcdcdd9299be>`_ #4075 Fixed typo in the yml validation (timothymctim)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `e9d317a <https://github.com/symfony/symfony-docs/commit/e9d317a1fc653636605a207ed5cb2c4880860658>`_ #4160 [Reference] consistent & complete config examples (xabbuh)
- `3e68ee7 <https://github.com/symfony/symfony-docs/commit/3e68ee78c2a7288ba0bf9264833c22c3c1b28aff>`_ #4152 Adding 'attr' option to the Textarea options list (ronanguilloux)
- `a7f3297 <https://github.com/symfony/symfony-docs/commit/a7f329772d6cf47184e6a9fd35478107cd3d9e91>`_ #4136 [Reference] fix from suffix to prefix (xabbuh)
- `c4eb628 <https://github.com/symfony/symfony-docs/commit/c4eb628781d5cb487d15e7effed51efbe6f64f13>`_ #4130 A set of small typos (Baptouuuu)
- `236d8e0 <https://github.com/symfony/symfony-docs/commit/236d8e08b5d282948a42c169f0090faed66c42fc>`_ #4137 fixed directive syntax (WouterJ)
- `6e90520 <https://github.com/symfony/symfony-docs/commit/6e90520d4c8ea72a90d910b0adac917e33e5d963>`_ #4135 [#3940] Adding php example for an array of emails (weaverryan)
- `b37ee61 <https://github.com/symfony/symfony-docs/commit/b37ee61d264fa81811ff90e3f492708f0f70b58a>`_ #4132 Use proper way to reference a doc page for legacy sessions (Baptouuuu)
- `189a123 <https://github.com/symfony/symfony-docs/commit/189a1234bbf0630004c4ad76a9e535e382c354ce>`_ #4129 [Components] consistent & complete config examples (xabbuh)
- `5ab5246 <https://github.com/symfony/symfony-docs/commit/5ab5246c2499c2906239750f39a172a8afa496f4>`_ #4127 Second part of #3848 (WouterJ)
- `46f3108 <https://github.com/symfony/symfony-docs/commit/46f3108fee8f6a514b4376ad9dddb37aa6e5342d>`_ #4126 Rebased #3848 (WouterJ)
- `84e6e7f <https://github.com/symfony/symfony-docs/commit/84e6e7f69f7fc5693075012d69509ad437f455ff>`_ #4114 [Book] consistent and complete config examples (xabbuh)
- `03fcab1 <https://github.com/symfony/symfony-docs/commit/03fcab142743528643ee04895b2287420e2a1d76>`_ #4112 [Contributing][Documentation] add order of translation formats (xabbuh)
- `650120a <https://github.com/symfony/symfony-docs/commit/650120ada5c432d2a2a3f54386c88f180d0f43b3>`_ #4002 added Github teams for the core team (fabpot)
- `10792c3 <https://github.com/symfony/symfony-docs/commit/10792c330506ad2366147f36410233341e86701e>`_ #3959 [book][cache][tip] added cache annotations. (aitboudad)
- `ebaed21 <https://github.com/symfony/symfony-docs/commit/ebaed21ff67074d02d5812060fc9d5f85ec237e1>`_ #3944 Update dbal.rst (bpiepiora)
- `16e346a <https://github.com/symfony/symfony-docs/commit/16e346ad07c8139b20bbc10600ceed1563fbe1f2>`_ #3890 [Components][HttpFoundation] use a placeholder for the constructor arguments (xabbuh)
- `7bb4f34 <https://github.com/symfony/symfony-docs/commit/7bb4f3447d0b0a7bd524dfbb89dc4a17f8090a8b>`_ #4115 [Documentation] [Minor] Changes foobar.net in example.com (magnetik)
- `12d0b82 <https://github.com/symfony/symfony-docs/commit/12d0b825f158639928e0ea9acc9535cc4ffbd676>`_ #4113 tweaks to the new reverse proxy/load balancer chapter (xabbuh)
- `4cce133 <https://github.com/symfony/symfony-docs/commit/4cce133b5b55fce7cafa26257e530838f587a814>`_ #4057 Update introduction.rst (carltondickson)
- `26141d6 <https://github.com/symfony/symfony-docs/commit/26141d68dfda0959ad75e17c5db51de399f97975>`_ #4080 [Reference] order form type options alphabetically (xabbuh)
- `7806aa7 <https://github.com/symfony/symfony-docs/commit/7806aa7de543baf167d445dc5901121a2e460599>`_ #4117 Added a note about the automatic handling of the memory spool in the CLI (stof)
- `5959b6c <https://github.com/symfony/symfony-docs/commit/5959b6c64156e0e6dfca060412358b12c5ea117b>`_ #4101 [Contributing] extended Symfony 2.4 maintenance (xabbuh)
- `e2056ad <https://github.com/symfony/symfony-docs/commit/e2056ad7db522e117c5f310690c993a2a5a3a725>`_ #4072 [Contributing][Code] add note on Symfony SE forks for bug reports (xabbuh)
- `b8687dd <https://github.com/symfony/symfony-docs/commit/b8687dd452ac200adbc8aee347c9235329f1f265>`_ #4091 Put version into quotes, otherwise it fails in ZSH (dannykopping)
- `665c091 <https://github.com/symfony/symfony-docs/commit/665c0913e52470f4b4b8070a52462285cad62863>`_ #4087 Typo (tvlooy)
- `f95bbf3 <https://github.com/symfony/symfony-docs/commit/f95bbf34c990d3d7e06fa48d790f06ff5814563e>`_ #4023 [Cookbook][Security] usage of a non-default entity manager in an entity user provider (xabbuh)
- `27b1003 <https://github.com/symfony/symfony-docs/commit/27b10033d12a247f3af22333e7854d402ff3139d>`_ #4074 Fixed (again) a typo: Toolbet --> Toolbelt (javiereguiluz)
- `c97418f <https://github.com/symfony/symfony-docs/commit/c97418fdb030960c5f0dd10a635a4d49579c4216>`_ #4073 Reworded bundle requirement (WouterJ)
- `e5d5eb8 <https://github.com/symfony/symfony-docs/commit/e5d5eb8ae4307c17b3e2e157eafb4d5d32d0f557>`_ #4066 Update inherit_data_option.rst (Oylex)
- `9c08572 <https://github.com/symfony/symfony-docs/commit/9c08572fc55a8dff83ff7e3c985f3e66936d57af>`_ #4064 Fixed typo on tag service (saro0h)

July, 2014
----------

New Documentation
~~~~~~~~~~~~~~~~~

- `1b4c1c8 <https://github.com/symfony/symfony-docs/commit/1b4c1c86a3e4729e1a6ce226963ac05577b8ab8f>`_ #4045 Added a new "Deploying to Heroku Cloud" cookbook article (javiereguiluz)
- `f943eee <https://github.com/symfony/symfony-docs/commit/f943eee70cf81b90a598d94f400a3723216fe726>`_ #4009 Remove "Controllers extends ContainerAware" best practice (tgalopin)
- `eae9ad0 <https://github.com/symfony/symfony-docs/commit/eae9ad037ed402752238e4eb1c9c0b2fdc443d7d>`_ #3875 Added a note about customizing a form with more than one template (javiereguiluz)
- `2ae4f34 <https://github.com/symfony/symfony-docs/commit/2ae4f34a1e0afe699ccaacf2740440a0752a9a99>`_ #3746 [Validator] Disallow empty file in FileValidator (megazoll)
- `1938c2f <https://github.com/symfony/symfony-docs/commit/1938c2f9f7dbf770d694d1cc976fde5e519ba47c>`_ #3724 Updated ISBN validator docs (sprain)
- `7c71b18 <https://github.com/symfony/symfony-docs/commit/7c71b188f4fae43c1ff141b2d84f887b131d2b71>`_ #2952 Enabling profiler in test (danieledangeli)
- `d6787b7 <https://github.com/symfony/symfony-docs/commit/d6787b7b64ed6d20cafee0134a99e590bee6102b>`_ #3989 adde stof as a merger (fabpot)
- `4a9e49e <https://github.com/symfony/symfony-docs/commit/4a9e49ef142fdeeb5523dbdcf73503ac602373e7>`_ #3946 DQL custom functions on doctrine reference page (healdropper)
- `2b2d9d3 <https://github.com/symfony/symfony-docs/commit/2b2d9d348a7d9c46b9a5b04ef2cd088151668886>`_ #3972 Added PSR-4 to Class Loaders list (dosten)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `1b695b5 <https://github.com/symfony/symfony-docs/commit/1b695b52a3f8094daf18d212a124a6050f2fad1a>`_ #4063 fix parent form types (xabbuh)
- `7901005 <https://github.com/symfony/symfony-docs/commit/7901005f37d223cd4541648131bccc0e4bd687d5>`_ #4048 $this->request replaced by $request (danielsan)
- `f6123f1 <https://github.com/symfony/symfony-docs/commit/f6123f1bb06d624fc2d0506bcf6f4016c0b3e371>`_ #4031 Update form_events.rst (redstar504)
- `99932cf <https://github.com/symfony/symfony-docs/commit/99932cf0f98c15e0504a41867046b374305865d3>`_ #4010 [Console] Fixed documentation for ProgressBar (VasekPurchart)
- `06f8c31 <https://github.com/symfony/symfony-docs/commit/06f8c3157f76da36847a70d132c61971ac72dc1e>`_ #4012 Fix xml route configuration for routing condition (xavierbriand)
- `a2a628f <https://github.com/symfony/symfony-docs/commit/a2a628f0b2d60878041cd2a008f9ddfd9a471408>`_ #4025 added CVE 2014-4931 (fabpot)
- `a1435e5 <https://github.com/symfony/symfony-docs/commit/a1435e57fd95e14ffff171f797133a1c52569cb1>`_ #3998 [Console] Fixed QuestionHelper examples (florianv)
- `b32f9f2 <https://github.com/symfony/symfony-docs/commit/b32f9f2981ae3d63d547c07cee7f0cb03dc7db5a>`_ #3771 Fix function example in expression language component (raulfraile)
- `eb813a5 <https://github.com/symfony/symfony-docs/commit/eb813a563c564358ba82253ea667516cd3e7191d>`_ #3979 removed invalid processors option (ricoli)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `a4bdb97 <https://github.com/symfony/symfony-docs/commit/a4bdb97a92dd54709cd85be8e9998a6ced6974da>`_ #4070 Added a note about permissions in the Quick Tour (javiereguiluz)
- `a7fe00f <https://github.com/symfony/symfony-docs/commit/a7fe00fedea3ba89089a2de6fb54970cdbe2440e>`_ #4068 Remove diff info from cookbook/security/voters.rst (pmartelletti)
- `b3f15b2 <https://github.com/symfony/symfony-docs/commit/b3f15b2dc78c0593e6db09311c0c79eba68a97f0>`_ #4059 eraseCredentials method typo (danielsan)
- `44091b1 <https://github.com/symfony/symfony-docs/commit/44091b132c1007535ff3030a64126751486982cd>`_ #4053 Update doctrine.rst (sr972)
- `b06ad60 <https://github.com/symfony/symfony-docs/commit/b06ad6041164479c049ae4c202b13b3f248c3bed>`_ #4052 [Security] [Custom Provider] Use properties on WebserviceUser (entering)
- `a834a7e <https://github.com/symfony/symfony-docs/commit/a834a7e883e78005ed41a6cea0dcee058ed85b1f>`_ #4042 [Cookbook] apply headline guidelines to the cookbook articles (xabbuh)
- `f25faf3 <https://github.com/symfony/symfony-docs/commit/f25faf3ed3facd3812b743288465ee9ca25397a4>`_ #4046 Fixed a syntax error (javiereguiluz)
- `3c660d1 <https://github.com/symfony/symfony-docs/commit/3c660d13390cc1cf05e9d0be98bbce847002c05b>`_ #4044 Added editorconfig (WouterJ)
- `ae3ec04 <https://github.com/symfony/symfony-docs/commit/ae3ec048e8583082928bffadb079d1391e888f0f>`_ #4041 [Cookbook][Deployment] link to the deployment index (xabbuh)
- `2e4fc7f <https://github.com/symfony/symfony-docs/commit/2e4fc7f3a5dd4caa4ee5cbfade6989e71bcda123>`_ #4030 enclose YAML strings containing % with quotes (xabbuh)
- `9520d92 <https://github.com/symfony/symfony-docs/commit/9520d9299e6ddbc19985e0ed322349c70d89cdc7>`_ #4038 Update rendered tag (kirill-oficerov)
- `f5c2602 <https://github.com/symfony/symfony-docs/commit/f5c260219f8e7df969493df2fe2d2feb1fc97e19>`_ #4036 Update page_creation.rst (redstar504)
- `c2eda93 <https://github.com/symfony/symfony-docs/commit/c2eda939ad8681721cd1b12bcedadd1faf294725>`_ #4034 Update internals.rst (redstar504)
- `a5ad0df <https://github.com/symfony/symfony-docs/commit/a5ad0dfbf9ad2dcc84e7f9c6de73c8bad2a89e2f>`_ #4035 Update version in Rework your Patch section (yguedidi)
- `eed8d64 <https://github.com/symfony/symfony-docs/commit/eed8d646ad134bba1002f98800cab46d06241a05>`_ #4026 Updating Symfony version from 2.4 to 2.5 (danielsan)
- `12752c1 <https://github.com/symfony/symfony-docs/commit/12752c1decd5ec5a3bce960c094f45738122152f>`_ #4013 Removed wrong reference to cookbook (gquemener)
- `ec832dc <https://github.com/symfony/symfony-docs/commit/ec832dc4eadbe5afdfb5f39994b69748b733a85b>`_ #3994 [Console] Fix Console component $app to $this and use of getHelper() method (eko)
- `d8b037a <https://github.com/symfony/symfony-docs/commit/d8b037a989c1581ae671e81284d0d758254d8e1e>`_ #4019 Update twig_reference.rst (redstar504)
- `7ea87e6 <https://github.com/symfony/symfony-docs/commit/7ea87e63e3417c59e47972b9cf12ca76cce4b195>`_ #4016 Fixed the format of one letter-based list (javiereguiluz)
- `579a873 <https://github.com/symfony/symfony-docs/commit/579a8732299dd944998ab3fee4fa738a3c45cea2>`_ #4015 Fixed bad indenting (the list was treated as a blockquote) (javiereguiluz)
- `4669620 <https://github.com/symfony/symfony-docs/commit/466962051e0753557fc75d9d449083d68ae48992>`_ #4004 use GitHub instead of Github (xabbuh)
- `a3fe74f <https://github.com/symfony/symfony-docs/commit/a3fe74f4fad9f691dc570fb9234acd15bd6ccfdc>`_ #3993 [Console] Fix Console component getHelperSet()->get() to getHelper() (eko)
- `a41af7e <https://github.com/symfony/symfony-docs/commit/a41af7e0f4a10cf7a6230598fd955dab2559935e>`_ #3880 document the mysterious abc part of the header (greg0ire)
- `90773b0 <https://github.com/symfony/symfony-docs/commit/90773b03425388542d1b0face5368eb4c47ce3b8>`_ #3990 Move the section about collect: false to the cookbook entry (weaverryan)
- `2ae8281 <https://github.com/symfony/symfony-docs/commit/2ae82816556478c75ba9873589b2d77987156c36>`_ #3864 plug rules for static methods (cordoval)
- `d882cc0 <https://github.com/symfony/symfony-docs/commit/d882cc0e0b41e8c49187854d40dfb0963b429939>`_ #3988 fix typos. (yositani2002)
- `b67a059 <https://github.com/symfony/symfony-docs/commit/b67a059d5ff8c45482df135ed8dda5b3bcc81f3a>`_ #3986 Rebased #3982 - Some fixes (WouterJ)
- `801c756 <https://github.com/symfony/symfony-docs/commit/801c7565a3e6c0d440fb86f1b2fd4a076ff4399c>`_ #3977 [WCM] removed call to deprecated getRequest() method (Baptouuuu)
- `4c1d4ae <https://github.com/symfony/symfony-docs/commit/4c1d4ae506a7dc68fad57ddf6cc25c17a00169a4>`_ #3968 Proofreading the new Azure deployment article (weaverryan)

June, 2014
----------

New Documentation
~~~~~~~~~~~~~~~~~

- `5540e0b <https://github.com/symfony/symfony-docs/commit/5540e0b4a8dd455cb6ac4e149d1545385f4b203b>`_ #3963 [cookbook] [deployment] added cookbook showing how to deploy to the Microsoft Azure Website Cloud (hhamon)
- `6cba0f1 <https://github.com/symfony/symfony-docs/commit/6cba0f129056a4ac20f7a84cc069d7e726a090b4>`_ #3936 Varnish only takes into account max-age (gonzalovilaseca)
- `3c95af5 <https://github.com/symfony/symfony-docs/commit/3c95af5ebb26710f1e2f95bb9ded3abc7ea3b709>`_ #3928 Reorder page from simple to advanced (rebased) (clemens-tolboom)
- `350b805 <https://github.com/symfony/symfony-docs/commit/350b8055458e02c63cb2f24b3e261b6b334b30b1>`_ #3916 [Component][EventDispatcher] documentation for the TraceableEventDispatcher (xabbuh)
- `1702133 <https://github.com/symfony/symfony-docs/commit/17021333289c9de37ab7383dee4b94bdb04f4ea7>`_ #3913 [Cookbook][Security] Added doc for x509 pre authenticated listener (zefrog)
- `32b9058 <https://github.com/symfony/symfony-docs/commit/32b9058563637def77d2820d659f8d647b5b2e5b>`_ #3909 Update the CssSelector component documentation (stof)
- `23b51c8 <https://github.com/symfony/symfony-docs/commit/23b51c84e72a2a7692667fd449d3362ec6f542a1>`_ #3901 Bootstraped the standards for "Files and Directories" (javiereguiluz)
- `8931c36 <https://github.com/symfony/symfony-docs/commit/8931c362c2b256bb52507ca2d367b72eea421d84>`_ #3889 Fixed the section about getting services from a command (javiereguiluz)
- `9fddab6 <https://github.com/symfony/symfony-docs/commit/9fddab6e0cb6e7a023056e1f8456ebc3520e5cfb>`_ #3877 Added a note about configuring several paths under the same namespace (javiereguiluz)
- `eadf281 <https://github.com/symfony/symfony-docs/commit/eadf281096ccbdc69928d0f94a57428bcc906fde>`_ #3874 Updated the installation instructions for Symfony 2.5+ (javiereguiluz)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `aeffd12 <https://github.com/symfony/symfony-docs/commit/aeffd126b3dff698792fb39374d856b702990e1e>`_ #3961 Fixing php coding (mvhirsch)
- `84332ff <https://github.com/symfony/symfony-docs/commit/84332ff5b041ba09a9674c772cf203a573375038>`_ #3945 Fixed missing component name in namespaces (WouterJ)
- `d8329dc <https://github.com/symfony/symfony-docs/commit/d8329dc05523bfe17e7fcc24099122932e638ba3>`_ #3943 Fixing simple quotes in double quotes (ptitlazy)
- `04f4318 <https://github.com/symfony/symfony-docs/commit/04f43183dea62c1036c169392a81a353e516d8fe>`_ #3934 Move __construct after the repository assignment (cmodijk)
- `0626f2b <https://github.com/symfony/symfony-docs/commit/0626f2bda67fa34ac6039a400a9b23f5968d9e82>`_ #3897 Collection constraint (hhamon)
- `3387cb2 <https://github.com/symfony/symfony-docs/commit/3387cb2c79affaae9359ffdce929a2148f87de55>`_ #3871 Fix missing Front Controller (parthasarathigk)
- `8257be9 <https://github.com/symfony/symfony-docs/commit/8257be91df0a873fd3ea9277a2c7e308a579a4ce>`_ #3891 Fixed wrong method call. (cmfcmf)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `75ee6b4 <https://github.com/symfony/symfony-docs/commit/75ee6b4d62e7caf0932f6966b3fb8dfcefdb8335>`_ #3969 [cookbook] [deployment] removed marketing introduction in Azure Deployme... (hhamon)
- `02aeade <https://github.com/symfony/symfony-docs/commit/02aeade9a01155ca5e203f065008fd2ec2044cb4>`_ #3967 fix typo. (yositani2002)
- `208b0dc <https://github.com/symfony/symfony-docs/commit/208b0dc78b07f4dbabb4d3e7dd6b2c7f1ac4863a>`_ #3951 fix origin of AcmeDemoBundle (hice3000)
- `fba083e <https://github.com/symfony/symfony-docs/commit/fba083efaddd92ad782e453d43a62bdec053cfa0>`_ #3957 [Cookbook][Bundles] fix typos in the prepend extension chapter (xabbuh)
- `c444b5d <https://github.com/symfony/symfony-docs/commit/c444b5ddb2df7bede3a84f8fffa543892a629916>`_ #3948 update the Sphinx extensions to raise warnings when backslashes are not ... (xabbuh)
- `8fef7b7 <https://github.com/symfony/symfony-docs/commit/8fef7b7fd81ee12aca2d45ac59c2ef2da11528d0>`_ #3938 [Contributing][Documentation] don't render the list inside a blockquote (xabbuh)
- `b7a03f8 <https://github.com/symfony/symfony-docs/commit/b7a03f85e24b2d04faa832de151c5f02d9e864ba>`_ #3937 properly escape backslashes in class and method directives (xabbuh)
- `882471f <https://github.com/symfony/symfony-docs/commit/882471fc1dd6b39dd22a62361ebbaad9939f1c4f>`_ #3935 Typo (greg0ire)
- `222a014 <https://github.com/symfony/symfony-docs/commit/222a014164c005a38ff69a8b1ed3dd80612b6403>`_ #3933 render directory inside a code block (xabbuh)
- `0c2a9b3 <https://github.com/symfony/symfony-docs/commit/0c2a9b3c033666fd87feaf21ce23a92178e0c802>`_ #3931 [Component][EventDispatcher] 2.5 specific documentation for the TraceableEventDispatcher (xabbuh)
- `b31ea51 <https://github.com/symfony/symfony-docs/commit/b31ea51f524017bbfe7906f7b46a59dfe2ac9f48>`_ #3929 Update custom_authentication_provider.rst (verschoof)
- `7937864 <https://github.com/symfony/symfony-docs/commit/793786494d80f37dc0311520585f2699ab07a149>`_ #3927 [Cookbook][Security] Explicit 'your_user_provider' configuration parameter (zefrog)
- `26d00d0 <https://github.com/symfony/symfony-docs/commit/26d00d084386df90f625a1b7302e65974326008b>`_ #3925 Fixed the indentation of two code blocks (javiereguiluz)
- `351b2cf <https://github.com/symfony/symfony-docs/commit/351b2cf60575e4e2f1249c9c7ac7b19a0ecbe1b8>`_ #3922 update fabpot Sphinx extensions version (xabbuh)
- `3ddbe1b <https://github.com/symfony/symfony-docs/commit/3ddbe1be2a91426fa2b8665b5a33f9c8a5fd21eb>`_ #3923 Fixed the headers of one table (javiereguiluz)
- `35cbffc <https://github.com/symfony/symfony-docs/commit/35cbffcd90920494231d07a487deee1d9a8ae967>`_ #3920 [Components][Form] remove blank line to render the versionadded directive properly (xabbuh)
- `df9f31a <https://github.com/symfony/symfony-docs/commit/df9f31ae2dcceb8f51a1cf9fb497d5a027944b8f>`_ #3882 change version numbers in installation notes to be in line with the docu... (xabbuh)
- `ed496ae <https://github.com/symfony/symfony-docs/commit/ed496aeb401943f48c8c66ce45e57ae89cb186a4>`_ #3887 [Components][Form] add versionadded for the data collector form extension (xabbuh)
- `36337e7 <https://github.com/symfony/symfony-docs/commit/36337e7b44af6237ad43533742571c27d8976faa>`_ #3906 Blockquote introductions (xabbuh)
- `5e0e119 <https://github.com/symfony/symfony-docs/commit/5e0e1196cf1b7735e489b25fe2c2263fea13d8ff>`_ #3899 [RFR] Misc. fixes mostly related to formatting issues (javiereguiluz)
- `349cbeb <https://github.com/symfony/symfony-docs/commit/349cbeb161cb9d1eec9238a58fc21004a560ced9>`_ #3900 Fixed the formatting of the table headers (javiereguiluz)
- `1dc8b4a <https://github.com/symfony/symfony-docs/commit/1dc8b4af41b6e89a22ca79ed95c84955e1e8a1e6>`_ #3898 clarifying the need of a factory for auth-provider (leberknecht)
- `0c20141 <https://github.com/symfony/symfony-docs/commit/0c2014162aec345fc2665602ab0b0380cb50fe57>`_ #3896 Fixing comment typo for Doctrine findBy and findOneBy code example (beenanner)
- `b00573c <https://github.com/symfony/symfony-docs/commit/b00573c4be04f4181896127437aa3d09c4817669>`_ #3870 Fix wrong indentation for lists (WouterJ)

May, 2014
---------

New Documentation
~~~~~~~~~~~~~~~~~

- `4fd1b49 <https://github.com/symfony/symfony-docs/commit/4fd1b49bb54db0dc19aa231bf1949d78537eb851>`_ #3753 [DependencyInjection] Add documentation about service decoration (romainneutron)
- `f913dd7 <https://github.com/symfony/symfony-docs/commit/f913dd7a43fd9e29bbfb2f9a2130377a6a0b862d>`_ #3603 [Serializer] Support for is.* getters in GetSetMethodNormalizer (tiraeth)
- `e8511cb <https://github.com/symfony/symfony-docs/commit/e8511cb4e5ab104d00ca13d9ca43ef4a9bb7dedd>`_ #3776 Updated event_listener.rst (bfgasparin)
- `af8c20f <https://github.com/symfony/symfony-docs/commit/af8c20fa357695645a98570a916658688894eb6d>`_ #3818 [Form customization] added block_name example. (aitboudad)
- `c788325 <https://github.com/symfony/symfony-docs/commit/c78832551744ed1c6aa2d3eb48501e0e1039d073>`_ #3841 [Cookbook][Logging] register processor per handler and per channel (xabbuh)
- `979533a <https://github.com/symfony/symfony-docs/commit/979533aa4430a0db4c421744023028192e02cb3d>`_ #3839 document how to test actions (greg0ire)
- `d8aaac3 <https://github.com/symfony/symfony-docs/commit/d8aaac3de8c867f05ce3c1543ebeb75eda3816e9>`_ #3835 Updated framework.ide configuration (WouterJ)
- `a9648e8 <https://github.com/symfony/symfony-docs/commit/a9648e8e8fb71f53af8560fff76f0c29dcb47551>`_ #3742 [2.5][Templating] Add documentation about generating versioned URLs (romainneutron)
- `f665e14 <https://github.com/symfony/symfony-docs/commit/f665e14059f56f729b20448ade416b3e11a14be8>`_ #3704 [Form] Added documentation for Form Events (csarrazi)
- `14b9f14 <https://github.com/symfony/symfony-docs/commit/14b9f140aff2e8a8fe23a18181f94e1f5e0d8a9b>`_ #3777 added docs for the core team (fabpot)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `0649c21 <https://github.com/symfony/symfony-docs/commit/0649c212b3ea28c8890914912ffb2503bfdfdad0>`_ #3869 Add a missing argument to the PdoSessionHandler (jakzal)
- `259a2b7 <https://github.com/symfony/symfony-docs/commit/259a2b7a891ba9c2f271cbcee5bf0130a15c98ac>`_ #3866 [Book][Security]fixed Login when there is no session. (aitboudad)
- `9b7584f <https://github.com/symfony/symfony-docs/commit/9b7584f0501a34016c4ec20a1c439c25ac663f5b>`_ #3863 Error in XML (tvlooy)
- `0cb9c3b <https://github.com/symfony/symfony-docs/commit/0cb9c3bc3fb6d17a8fafd778abf8dc6981fc550d>`_ #3827 Update 'How to Create and store a Symfony2 Project in Git' (nicwortel)
- `4ed9a08 <https://github.com/symfony/symfony-docs/commit/4ed9a08f073aa515636dd8bb2a0279f7e39d1ab8>`_ #3830 Generate an APC prefix based on __FILE__ (trsteel88)
- `9a65412 <https://github.com/symfony/symfony-docs/commit/9a654126306b113b329b6965a700632c477a5585>`_ #3840 Update dialoghelper.rst (jdecoster)
- `1853fea <https://github.com/symfony/symfony-docs/commit/1853fea967cc1defc35afec179932581038ffae0>`_ #3716 Fix issue #3712 (umpirsky)
- `baa9759 <https://github.com/symfony/symfony-docs/commit/baa97592f26023828d14d6eb80a2368365d147d0>`_ #3791 Property access tweaks (weaverryan)
- `80d70a4 <https://github.com/symfony/symfony-docs/commit/80d70a4907e6e26784c63f6c41fae8e2d57b67db>`_ #3779 [Book][Security] constants are defined in the SecurityContextInterface (xabbuh)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `302fa82 <https://github.com/symfony/symfony-docs/commit/302fa8254f6ae1c4777e90db58503ef5d145816e>`_ #3872 Update hostname_pattern.rst (sofany)
- `50672f7 <https://github.com/symfony/symfony-docs/commit/50672f7768d7eb6ae7acf4d954902d913aa6a20e>`_ #3867 fixed missing info about FosUserBundle. (aitboudad)
- `3e3004f <https://github.com/symfony/symfony-docs/commit/3e3004fba7a634f25e7521cf0fc52117ee0e1a58>`_ #3865 Fixed link. (aitboudad)
- `b32ec15 <https://github.com/symfony/symfony-docs/commit/b32ec158602704de28325ca5ba5cd7a915d97af8>`_ #3856 Update voters_data_permission.rst (MarcomTeam)
- `bffe163 <https://github.com/symfony/symfony-docs/commit/bffe1635435b61a95ef26e433391ca8385984430>`_ #3859 Add filter cssrewrite (DOEO)
- `f617ff8 <https://github.com/symfony/symfony-docs/commit/f617ff88087b1f7ec2df55f9b6b63cc1f69b0a9d>`_ #3764 Update testing.rst (NAYZO)
- `3792fee <https://github.com/symfony/symfony-docs/commit/3792fee4a18cc1b411ca02c4909534e17ee22593>`_ #3858 Clarified Password Encoders example (WouterJ)
- `663d68c <https://github.com/symfony/symfony-docs/commit/663d68c034c41bd539064ae544b466c372cd7f5b>`_ #3857 Added little bit information about the route name (WouterJ)
- `797cbd5 <https://github.com/symfony/symfony-docs/commit/797cbd56193d0ba0c65dc652366b2d16c6bff58c>`_ #3794 Adds link to new QuestionHelper (weaverryan)
- `4211bff <https://github.com/symfony/symfony-docs/commit/4211bff395f613d8dd3630178da3208b328df2bc>`_ #3852 Fixed link and typo in type_guesser.rst (rpg600)
- `78ae7ec <https://github.com/symfony/symfony-docs/commit/78ae7ecc6d35cb7f0bc1e9124aac961f13113d02>`_ #3845 added link to /cookbook/security/force_https. (aitboudad)
- `6c69362 <https://github.com/symfony/symfony-docs/commit/6c693626ec93dfeb7e04529cc752e3a0dc9174e1>`_ #3846 [Routing][Loader] added JMSI18nRoutingBundle (aitboudad)
- `136864b <https://github.com/symfony/symfony-docs/commit/136864b7727820196014ef3247fdc1da02106eea>`_ #3844 [Components] Fixed some typos. (ahsio)
- `b0710bc <https://github.com/symfony/symfony-docs/commit/b0710bc58740f6e5fcc1b98b25088870adda8389>`_ #3842 Update dialoghelper.rst (bijsterdee)
- `9f1a354 <https://github.com/symfony/symfony-docs/commit/9f1a354fe63a90bf4f6672082fe8cb2dc7900178>`_ #3804 [Components][DependencyInjection] add note about a use case that requires to compile the container (xabbuh)
- `d92c522 <https://github.com/symfony/symfony-docs/commit/d92c5228e3460dd3bce024c180829fd13a623672>`_ #3769 Updated references to new Session() (scottwarren)
- `00f60a8 <https://github.com/symfony/symfony-docs/commit/00f60a8c622d0ffbf7603a6fe6878a805398cc71>`_ #3837 More asset version details (weaverryan)
- `681ddc8 <https://github.com/symfony/symfony-docs/commit/681ddc8299e16b2f9149042755efe3bed846efb6>`_ #3843 [Changelog] fix literal positions (xabbuh)
- `1aa79d5 <https://github.com/symfony/symfony-docs/commit/1aa79d5b0a25560eaad4358267154fb13c55a5c6>`_ #3834 fix the wording in versionadded directives (for the master branch) (xabbuh)
- `7288a33 <https://github.com/symfony/symfony-docs/commit/7288a337b1d86e9c00b3eb7f7d778d643d0d2802>`_ #3789 [Reference][Forms] Improvements to the form type (xabbuh)
- `72fae25 <https://github.com/symfony/symfony-docs/commit/72fae25898b37e6e6761ac9c52bee45520545332>`_ #3790 [Reference][Forms] move versionadded directives for form options directly below the option's headline (xabbuh)
- `b4d4ac3 <https://github.com/symfony/symfony-docs/commit/b4d4ac34d999a09556517431d91d3221a9fa5be3>`_ #3838 fix filename typo in cookbook/form/unit_testing.rst (hice3000)
- `0b06287 <https://github.com/symfony/symfony-docs/commit/0b06287e617d870dd7ea7173b7670893b7f6c6dc>`_ #3836 remove unnecessary rewrite from nginx conf (Burgov)
- `89d0dae <https://github.com/symfony/symfony-docs/commit/89d0daeed991b0400b05edb61121eaac6f9bc351>`_ #3833 fix the wording in versionadded directives (for the 2.4 branch) (xabbuh)
- `e58e39f <https://github.com/symfony/symfony-docs/commit/e58e39f8211791a7531eee71eb0af8b0cb8f9627>`_ #3832 fix the wording in versionadded directives (for the 2.3 branch) (xabbuh)
- `09d6ca1 <https://github.com/symfony/symfony-docs/commit/09d6ca1ee588982b2f6d067744b09ec911e1538a>`_ #3829 [Components] consistent headlines (xabbuh)
- `54e0882 <https://github.com/symfony/symfony-docs/commit/54e08822dd490e340aeefab5cd0e222077d56287>`_ #3828 [Contributing] consistent headlines (xabbuh)
- `b1336d7 <https://github.com/symfony/symfony-docs/commit/b1336d7ed2290c320f9199dcea0778d8af0755bb>`_ #3823 Added empty line after if statements (zomberg)
- `79b9fdc <https://github.com/symfony/symfony-docs/commit/79b9fdc325a49643aa5a42e2f59337acb5473de9>`_ #3822 Update voters_data_permission.rst (mimol91)
- `69cb7b8 <https://github.com/symfony/symfony-docs/commit/69cb7b8b8fe29b68bb7b153f582818ffac2f1c50>`_ #3821 Update custom_authentication_provider.rst (leberknecht)
- `9f602c4 <https://github.com/symfony/symfony-docs/commit/9f602c4a54414b235a616a6d13254c2cbe71d392>`_ #3820 Update page_creation.rst (adreeun)
- `52518c0 <https://github.com/symfony/symfony-docs/commit/52518c0a97b3d1b75bade2d3566e8029080a9d88>`_ #3819 Update csrf_in_login_form.rst (micheal)
- `1adfd9b <https://github.com/symfony/symfony-docs/commit/1adfd9b7b9d4d0e33cf0fd266d05d3cef36a2faa>`_ #3802 Add a note about which types can be used in Symfony (fabpot)
- `fa27ded <https://github.com/symfony/symfony-docs/commit/fa27ded5dfdef27d43f33c2f6b325f69acae87a6>`_ #3801 [Cookbook][Form] Fixed Typo & missing word. (ahsio)
- `127beed <https://github.com/symfony/symfony-docs/commit/127beedf880e51965ee723f2896c010c7906b339>`_ #3770 Update factories.rst (AlaaAttya)
- `822d985 <https://github.com/symfony/symfony-docs/commit/822d985c964f175380f697f175345ad6bbd63fda>`_ #3817 Update translation.rst (richardpi)
- `241d923 <https://github.com/symfony/symfony-docs/commit/241d9238e4cda9248bf5588433d9087cd5cd6d09>`_ #3813 [Reference][Forms]fix time field count. (yositani2002)
- `bc96f55 <https://github.com/symfony/symfony-docs/commit/bc96f55f27eda223b920206925ab9582f200f14a>`_ #3812 [Cookbook][Configuration] Fixed broken link. (ahsio)
- `5867327 <https://github.com/symfony/symfony-docs/commit/58673278fa53554bcedeabc7609bb19c3af12063>`_ #3809 Fixed typo (WouterJ)
- `678224e <https://github.com/symfony/symfony-docs/commit/678224ea7e0050e3060058571d1273eb96c97da3>`_ #3808 Fixed broken link in "Handling Authentication Failure" (stacyhorton)

April, 2014
-----------

New Documentation
~~~~~~~~~~~~~~~~~

- `322972e <https://github.com/symfony/symfony-docs/commit/322972e322be754d34171d75514fa6f31a6677c8>`_ #3803 [Book][Validation] configuration examples for the GroupSequenceProvider (xabbuh)
- `9e129bc <https://github.com/symfony/symfony-docs/commit/9e129bcf3079e852b08e3c4746c7ad05cd99b8df>`_ #3752 [Console] Add documentation for QuestionHelper (romainneutron)
- `64a924d <https://github.com/symfony/symfony-docs/commit/64a924da79c4ba00bebd94b8ebf1acec4c789145>`_ #3756 [WCM][Console] Add Process Helper documentation (romainneutron)
- `d4ca16a <https://github.com/symfony/symfony-docs/commit/d4ca16a7605697787424c5e09f0506c617880687>`_ #3743 Improve examples in parent services (WouterJ)
- `be4b9d3 <https://github.com/symfony/symfony-docs/commit/be4b9d3b8e3dc70cf161b70b72a20788fba93348>`_ #3729 Added documentation for the new ``PropertyAccessor::isReadable()`` and ``isWritable()`` methods (webmozart)
- `70a3893 <https://github.com/symfony/symfony-docs/commit/70a389392b4d0e9c4d1b65699810c9884ce8ef49>`_ #3774 [Book][Internals] add description for the kernel.finish_request event (xabbuh)
- `1934720 <https://github.com/symfony/symfony-docs/commit/19347205ce6d52cba91700f99c20ae951265f490>`_ #3461 [Form] Deprecated max_length and pattern options (stefanosala)
- `d611e77 <https://github.com/symfony/symfony-docs/commit/d611e77fa1408064c6f066c6acd9d8b4464198ef>`_ #3701 [Serializer] add documentation for serializer callbacks (cordoval)
- `80c645c <https://github.com/symfony/symfony-docs/commit/80c645caba36e80d269c513c67f3c305507685d4>`_ #3719 Fixed event listeners priority (tony-co)
- `c062d81 <https://github.com/symfony/symfony-docs/commit/c062d8176ef68c54af85817ec0d1c0e80544f153>`_ #3469 [Validator] - EmailConstraint reference (egulias)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `f801e2e <https://github.com/symfony/symfony-docs/commit/f801e2e3998ae3da050e774411c0a726918e117c>`_ #3805 Add missing autocomplete argument in askAndValidate method (ifdattic)
- `a81d367 <https://github.com/symfony/symfony-docs/commit/a81d367d6f96bc498a609eec3b553cd1a605e361>`_ #3786 replaceArguments should be setArguments (RobinvdVleuten)
- `33b64e1 <https://github.com/symfony/symfony-docs/commit/33b64e16a79757d4c4964c18c9df97977e524a1c>`_ #3788 Fix link for StopwatchEvent class (rpg600)
- `2ebabfb <https://github.com/symfony/symfony-docs/commit/2ebabfbb234c92d86a9311d11e7b8f94be046dfc>`_ #3792 Update commands_as_services.rst (mimol91)
- `529d4ce <https://github.com/symfony/symfony-docs/commit/529d4ce6d587a98b36564366058eb34babbbab81>`_ #3761 buildViewBottomUp has been renamed to finishView (Nyholm)
- `d743139 <https://github.com/symfony/symfony-docs/commit/d743139aed36813ba1dc4479290d6290cdc4316f>`_ #3768 the Locale component does not have elements tagged with @api (xabbuh)
- `2b8e44d <https://github.com/symfony/symfony-docs/commit/2b8e44d59ab7fca426be86afd30df6c98583d1e3>`_ #3747 Fix Image constraint class and validator link (weaverryan)
- `fa362ca <https://github.com/symfony/symfony-docs/commit/fa362caf410e876ca71070862abdb2df5242884a>`_ #3741 correct RuntimeException reference (shieldo)
- `d92545e <https://github.com/symfony/symfony-docs/commit/d92545e998d8999435e554ab03cbe6eee5f05e83>`_ #3734 [book] [testing] fixed the path of the phpunit.xml file (javiereguiluz)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `136f98c <https://github.com/symfony/symfony-docs/commit/136f98c897ab354441e292149d988a444bbbb9ee>`_ #3784 [Expression Langage] be consistent in "print/print out" uses (mickaelandrieu)
- `1094a13 <https://github.com/symfony/symfony-docs/commit/1094a13b2de0a7153e59f205aa57b93425da82cf>`_ #3807 Added some exceptions to the method order in CS (stof)
- `55442b5 <https://github.com/symfony/symfony-docs/commit/55442b55fb3efb2d59914724eec197ec17cb76e4>`_ #3800 Fixed another blockquote rendering issue (WouterJ)
- `969fd71 <https://github.com/symfony/symfony-docs/commit/969fd71a5fbef26a721784551d62dbd842d05ad4>`_ #3785 ensure that destination directories don't exist before creating them (xabbuh)
- `79322ff <https://github.com/symfony/symfony-docs/commit/79322fffd6e9ee739c1edab481bbe40ba3a67127>`_ #3799 Fix list to not render in a block quote (WouterJ)
- `1a6f730 <https://github.com/symfony/symfony-docs/commit/1a6f7301015e51c5ba255df289f4058c1ba1dc3c>`_ #3793 language tweak for the tip introduced in #3743 (xabbuh)
- `dda9e88 <https://github.com/symfony/symfony-docs/commit/dda9e88af29b866041b81251a1a44ed02b2c9ff4>`_ #3778 Adding information on internal reverse proxy (tcz)
- `d36bbd9 <https://github.com/symfony/symfony-docs/commit/d36bbd9418bdc2929745866d856448b7205a2676>`_ #3765 [WIP] make headlines consistent with our standards (xabbuh)
- `daa81a0 <https://github.com/symfony/symfony-docs/commit/daa81a0f34d342ece53b8656832fec6d4dfca657>`_ #3766 [Book] add note about services and the service container in the form cha... (xabbuh)
- `4529858 <https://github.com/symfony/symfony-docs/commit/45298580fada950a77536b142d903c3c4db11d0d>`_ #3767 [Book] link to the bc promise in the stable API description (xabbuh)
- `a5471b3 <https://github.com/symfony/symfony-docs/commit/a5471b34ad8618430962a31571f34e25259c3358>`_ #3775 Fixed variable naming (peterrehm)
- `703c2a6 <https://github.com/symfony/symfony-docs/commit/703c2a66282f7e42d6b2f74a71bd3dac28393984>`_ #3772 [Cookbook][Sessions] some language improvements (xabbuh)
- `3d30b56 <https://github.com/symfony/symfony-docs/commit/3d30b560db2a69d2f98d6ad2caf1ab29fecf9d94>`_ #3773 modify Symfony CMF configuration values in the build process so that the... (xabbuh)
- `cfd6d7c <https://github.com/symfony/symfony-docs/commit/cfd6d7c1f07ac80e557fb9eb358256a319acc8b9>`_ #3758 [Book][Routing] Fixed typo on PHP version of a route definition (saro0h)
- `cedfdce <https://github.com/symfony/symfony-docs/commit/cedfdce4bcb8e2e90be6352fbdf6274945cacda3>`_ #3757 Fixed a typo in the request formats configuration page (gquemener)
- `6bd134c <https://github.com/symfony/symfony-docs/commit/6bd134c00c79804a24d5210a74f342295014b847>`_ #3754 ignore more files and directories which are created when building the documentation (xabbuh)
- `610462e <https://github.com/symfony/symfony-docs/commit/610462e6025c9a7a779c08c5e579563317686b30>`_ #3755 [Cookbook][Security] Firewall resitrction tweaks, fix markup, add to toc (xabbuh)
- `0a21718 <https://github.com/symfony/symfony-docs/commit/0a21718883a7456a769824141fd0b405e29ea388>`_ #3695 Firewall backport (weaverryan)
- `54d6a9e <https://github.com/symfony/symfony-docs/commit/54d6a9e738f99db87ff7c20cc6b394de11641155>`_ #3736 [book] Misc. routing fixes (javiereguiluz)
- `f149dcf <https://github.com/symfony/symfony-docs/commit/f149dcf85efb5eb029a499413a80543be094a1df>`_ #3739 [book] [forms] misc. fixes and tweaks (javiereguiluz)
- `ce582ec <https://github.com/symfony/symfony-docs/commit/ce582ec9e072c07ebbba37c70da08692a6707e12>`_ #3735 [book] [controller] fixed the code of a session sample code (javiereguiluz)
- `499ba5c <https://github.com/symfony/symfony-docs/commit/499ba5c33b5a0c76c378f04dae01ea8c792973e5>`_ #3733 [book] [validation] fixed typos (javiereguiluz)
- `4d0ff8f <https://github.com/symfony/symfony-docs/commit/4d0ff8f8762ea7d8e19b04c21065269e3b4667ab>`_ #3732 Update routing.rst. Explain using url() v. path(). (ackerman)
- `44c6273 <https://github.com/symfony/symfony-docs/commit/44c6273ad86e8506463e23e88bcbac5f03e2a680>`_ #3727 Added a note about inlined private services (javiereguiluz)

March, 2014
-----------

New Documentation
~~~~~~~~~~~~~~~~~

- `3b640aa <https://github.com/symfony/symfony-docs/commit/3b640aa120ca6ff9c4c20bd95bfac142b65ee410>`_ #3644 made some small addition about our BC promise and semantic versioning (fabpot)
- `2d1ecd9 <https://github.com/symfony/symfony-docs/commit/2d1ecd9622109a06fd9f72b0ef987d3fcd7801c1>`_ #3525 Update file_uploads.rst (juanmf)
- `b1e8f56 <https://github.com/symfony/symfony-docs/commit/b1e8f566a20d029f657994ae8228bb5ef7eeb5b8>`_ #3368 The host parameter has to be in defaults, not requirements (MarieMinasyan)
- `b34fb64 <https://github.com/symfony/symfony-docs/commit/b34fb648a85f95bc3b071cf08b192d726b4b955a>`_ #3619 [Validator] Uuid constraint reference (colinodell)
- `d7027c0 <https://github.com/symfony/symfony-docs/commit/d7027c07ad7f4037e07d15944d8c2d5e389926a9>`_ #3418 [Validation] Add "hasser" support (bicpi)
- `4fd5fc1 <https://github.com/symfony/symfony-docs/commit/4fd5fc16c6225ed817402e39d10ca471d934d877>`_ #3539 [Stopwatch] Describe retrieval of StopwatchEvent (jochenvdv)
- `1908a15 <https://github.com/symfony/symfony-docs/commit/1908a15c39b5f7893b1fb0c6f4e28dad0edaf042>`_ #3696 [Console] Added standalone PSR-3 compliant logger (dunglas)
- `c75b1a7 <https://github.com/symfony/symfony-docs/commit/c75b1a7fafefcf2ac31a3321676c09083befb1c4>`_ #3621 [Console] Command as service (gnugat)
- `00a462a <https://github.com/symfony/symfony-docs/commit/00a462a0149c20bf2b469e0480ce19db95ace347>`_ minor #3658 Fix PSR coding standards error (ifdattic)
- `acf255d <https://github.com/symfony/symfony-docs/commit/acf255d6a723c59af44299f8266a854903ecf6a4>`_ #3328 [WIP] Travis integration (WouterJ)
- `450146e <https://github.com/symfony/symfony-docs/commit/450146e0f0fd4e2f629655f04919d58b7e6f7152>`_ #3681 Enhanced Firewall Restrictions docs (danez)
- `3e7028d <https://github.com/symfony/symfony-docs/commit/3e7028d8972fd71c1c683dd44dffe870a12e0c5b>`_ #3659 [Internals] Complete notification description for kernel.terminate (bicpi)
- `db3cde7 <https://github.com/symfony/symfony-docs/commit/db3cde7ddf7929da282cfc70e8a8b524ffb72f11>`_ #3124 Add note about the property attribute (Property Accessor) (raziel057)
- `5965ec8 <https://github.com/symfony/symfony-docs/commit/5965ec809237c5f0aab41b9d53cd928200517216>`_ #3420 [Cookbook][Configuration] add configuration cookbook handlig parameters in Configurator class (cordoval)
- `dcf8e6e <https://github.com/symfony/symfony-docs/commit/dcf8e6e2a8b4425195bd19399400e8de98cb8cee>`_ #3402 Added documentation about new requests formats configuration (gquemener)
- `a1050eb <https://github.com/symfony/symfony-docs/commit/a1050eba5cc08682a0cfda5a14122981c99a666a>`_ #3411 [Cookbook][Dynamic Form Modification] Add AJAX sample (bicpi)
- `842fd30 <https://github.com/symfony/symfony-docs/commit/842fd303545ab3e325ed83d94ef5800fbda6d29c>`_ #3683 [TwigBundle] Add documentation about generating absolute URL with the asset function (romainneutron)
- `fc1576a <https://github.com/symfony/symfony-docs/commit/fc1576aa1307a39c7d6c5a42ac7a42e2f968a38c>`_ #3664 [Process] Add doc for ``Process::disableOutput`` and ``Process::enableOutput`` (romainneutron)
- `3731e2e <https://github.com/symfony/symfony-docs/commit/3731e2ec5734be3e674cfd6989a8e5f2f01caffb>`_ #3686 Documentation of the new PSR-4 class loader. (derrabus)
- `5b915c2 <https://github.com/symfony/symfony-docs/commit/5b915c2848d7969924fedd27be9baf4c09b3e648>`_ #3629 Added documentation for translation:debug (florianv)
- `6951460 <https://github.com/symfony/symfony-docs/commit/6951460dd0f2bef4a87f53df88f4779649143c82>`_ #3601 Added documentation for missing ctype extension (slavafomin)
- `df63740 <https://github.com/symfony/symfony-docs/commit/df63740960702ab41bfd20a5c1f01e182a9b9702>`_ #3627 added docs for the new Table console helper (fabpot)
- `96bd81b <https://github.com/symfony/symfony-docs/commit/96bd81be2ccb12e8825cac23809921ef8de89cd4>`_ #3626 added documentation for the new Symfony 2.5 progress bar (fabpot)
- `b02c16a <https://github.com/symfony/symfony-docs/commit/b02c16aa4304b582a9be8de180544b901f794cfc>`_ #3565 added information on AuthenticationFailureHandlerInterface (samsamm777)
- `2657ee7 <https://github.com/symfony/symfony-docs/commit/2657ee78de23a16fe4423f2deffcc8fe3d0552c4>`_ #3597 Document how to create a custom type guesser (WouterJ)
- `5ad1599 <https://github.com/symfony/symfony-docs/commit/5ad1599bda2bde0fdff2762c9fcb852eb39c5b32>`_ #3577 Development of custom error pages is impractical if you need to set kernel.debug=false (mpdude)
- `3f4b319 <https://github.com/symfony/symfony-docs/commit/3f4b319f65f30ada4a57ad243072824d325e7f52>`_ #3610 [HttpFoundation] Add doc for ``Request::getContent()`` method (bicpi)
- `56bc266 <https://github.com/symfony/symfony-docs/commit/56bc2660041162c828cdad6c64a722a602c1f126>`_ #3589 Finishing the Templating component docs (WouterJ)
- `d881181 <https://github.com/symfony/symfony-docs/commit/d881181f88d090f86627a9dd7b5278a36087a63a>`_ #3588 Documented all form variables (WouterJ)
- `5cda1c7 <https://github.com/symfony/symfony-docs/commit/5cda1c7a7edd55d29867bfc383a63ebda1e8dc01>`_ #3311 Use KernelTestCase instead of WebTestCase for testing code only requiring the Container (johnkary)
- `e96e12d <https://github.com/symfony/symfony-docs/commit/e96e12d4c40e6205bf169db3c8545d6a3faa597d>`_ #3234 [Cookbook] New cookbok: How to use the Cloud to send Emails (bicpi)
- `d5d64ce <https://github.com/symfony/symfony-docs/commit/d5d64ce3a09062e3be9d6a248fdbf5f19f588cab>`_ #3436 [Reference][Form Types] Add missing docs for "action" and "method" option (bicpi)
- `3df34af <https://github.com/symfony/symfony-docs/commit/3df34afbe3d77c975bccc4b3c6f629db3bd537c3>`_ #3490 Tweaking Doctrine book chapter (WouterJ)
- `b9608a7 <https://github.com/symfony/symfony-docs/commit/b9608a777d7dd2316e26bf0f985fd4b3cb8cd810>`_ #3594 New Data Voter Article (continuation) (weaverryan)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `cad38ae <https://github.com/symfony/symfony-docs/commit/cad38ae81caad4a03ece9871ac188febb4809537>`_ #3721 tweaks to the Console logger (xabbuh)
- `06c56c1 <https://github.com/symfony/symfony-docs/commit/06c56c17160dba9ab53b5326f454e474d690be9e>`_ #3709 [Components][Security] Fix #3708 (bicpi)
- `aadc61d <https://github.com/symfony/symfony-docs/commit/aadc61d4e42a09461120cc940ee1add2ae5b95e5>`_ #3707 make method supportsClass() in custom voter compatible with the interface's documentation (xabbuh)
- `65150f9 <https://github.com/symfony/symfony-docs/commit/65150f989d84c3590aa43dc29b71856378bb2351>`_ #3637 Update render_without_controller.rst (94noni)
- `9fcccc7 <https://github.com/symfony/symfony-docs/commit/9fcccc734fdda2aa07ee2ced0da88c3c86f006a8>`_ #3634 Fix goal of “framework.profiler.only_exceptions“ option which profile on each exceptions on controller (not only 500) (stephpy)
- `9dd8d96 <https://github.com/symfony/symfony-docs/commit/9dd8d962ea0043ad446b096754b51d74992f80ed>`_ #3689 Fix cache warmer description (WouterJ)
- `6221f35 <https://github.com/symfony/symfony-docs/commit/6221f35df8558b273baaf6dcfc7dfa318e2c75c4>`_ #3671 miss extends keyword in define BlogController class (ghanbari)
- `4ce7a15 <https://github.com/symfony/symfony-docs/commit/4ce7a15e92a82541bee319206620cb175978844f>`_ #3543 Fix the definition of customizing form's global errors. (mtrojanowski)
- `5d4a3a4 <https://github.com/symfony/symfony-docs/commit/5d4a3a4859e00e8a9b07474ae7289e5a88b7e1ec>`_ #3343 [Testing] Fix phpunit test dir paths (bicpi)
- `badaae7 <https://github.com/symfony/symfony-docs/commit/badaae7d06f6730f37fe4051fa07fada32cf89d0>`_ #3622 [Components][Routing] Fix addPrefix() sample code (bicpi)
- `de0a5e1 <https://github.com/symfony/symfony-docs/commit/de0a5e1fc9baa62fa670b1486eedb9ae66b529ec>`_ #3665 [Cookbook][Test] fix sample code (inalgnu)
- `4ef746a <https://github.com/symfony/symfony-docs/commit/4ef746a10b373c7d7ce88bbc43fce8fe37c0dde2>`_ #3614 [Internals] Fix Profiler:find() arguments (bicpi)
- `0c41762 <https://github.com/symfony/symfony-docs/commit/0c41762a768c6b8979d6eb79256b65df762156fd>`_ #3600 [Security][Authentication] Fix instructions for creating password encoders (bicpi)
- `0ab1f24 <https://github.com/symfony/symfony-docs/commit/0ab1f24a8c418c0bc3c4330e1f725363e4fb61f7>`_ #3593 Clarified Default and ClassName groups (WouterJ)
- `178984b <https://github.com/symfony/symfony-docs/commit/178984bac0487875fbaec4ebfa471d34d6d9cb6f>`_ #3648 [Routing] Remove outdated tip about sticky locale (bicpi)
- `fc28453 <https://github.com/symfony/symfony-docs/commit/fc28453d4d09e7875f521a2a37f4e068ecc55aa2>`_ #3039 use DebugClassLoader class from Decomponent instead of the one from ... (xabbuh)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `abca098 <https://github.com/symfony/symfony-docs/commit/abca0980d80c12cd640757a64f6316dbf85dd125>`_ #3726 Minor tweaks after merging #3644 by @stof and @xabbuh (weaverryan)
- `d16be31 <https://github.com/symfony/symfony-docs/commit/d16be31547c57f01d454dea914ed38484efc5144>`_ #3725 Minor tweaks related to #3368 (weaverryan)
- `aa9bb25 <https://github.com/symfony/symfony-docs/commit/aa9bb2523286329432559d69103bcce091f6b643>`_ #3636 Update security.rst (nomack84)
- `78425c6 <https://github.com/symfony/symfony-docs/commit/78425c632b9d6a6b6c79f0b9ef386bc4f6a93330>`_ #3722 add "Commands as Services" chapter to the cookbook's map (xabbuh)
- `9f26da8 <https://github.com/symfony/symfony-docs/commit/9f26da860a642b228d8a9fa56ab3e93acf33339a>`_ #3720 [#3539] A backport of a sentence - the parts that apply to 2.3 (weaverryan)
- `4b611d6 <https://github.com/symfony/symfony-docs/commit/4b611d6bece7b9f11ce5124eff16be959d1afffc>`_ #3717 [master] Fixed versionadded blocks (WouterJ)
- `5a3ba1b <https://github.com/symfony/symfony-docs/commit/5a3ba1b89f9a0f8ed7de83c9a538ed7a735f900b>`_ #3715 change variable name to a better fitting one (xabbuh)
- `499eb6c <https://github.com/symfony/symfony-docs/commit/499eb6c1ea6dc2fe01376a7bd467ec55c30b1e56>`_ #3714 [2.4] Versionadded consistency (WouterJ)
- `e7580c0 <https://github.com/symfony/symfony-docs/commit/e7580c0110926585c892d76c0ff799bd7bcdc07e>`_ #3713 Updated versionadded directives to use "introduced" (WouterJ)
- `e15afe0 <https://github.com/symfony/symfony-docs/commit/e15afe0c5421ba0bff8723440bd57a07931661bc>`_ #3711 Simplified the Travis configuration (stof)
- `db1cda5 <https://github.com/symfony/symfony-docs/commit/db1cda52916e83c0e258e13b21728472cb43f6c9>`_ #3700 [Cookbook][Security] Firewall restrictions tweaks (xabbuh)
- `5035837 <https://github.com/symfony/symfony-docs/commit/5035837f46ed407cbabd11ad2c15098b349127e7>`_ #3706 Add support for nginx (guiditoito)
- `00a462a <https://github.com/symfony/symfony-docs/commit/00a462a0149c20bf2b469e0480ce19db95ace347>`_ #3658 Fix PSR coding standards error (ifdattic)
- `868de1e <https://github.com/symfony/symfony-docs/commit/868de1e9dfe4174f84e2d3d82e0aa0dafc559fb0>`_ #3698 Dynamic form modification cookbook: Fix inclusion of code  (michaelperrin)
- `15a9d25 <https://github.com/symfony/symfony-docs/commit/15a9d2586c41de992f31c79580bf0b39bae34dff>`_ #3697 [Console] Change Command namespaces (dunglas)
- `41b2eb8 <https://github.com/symfony/symfony-docs/commit/41b2eb88c10a9319e57c70c35319455e4fb21a11>`_ #3693 Tweak to Absolute URL generation (weaverryan)
- `bd473db <https://github.com/symfony/symfony-docs/commit/bd473db186bf9d1b6cef930cca49d0f640f90af5>`_ #3563 Add another tip to setup permissions (tony-co)
- `67129b1 <https://github.com/symfony/symfony-docs/commit/67129b1d72918c164eae9ea0c586898bce778286>`_ #3611 [Reference][Forms] add an introductory table containing all options of the basic form type (xabbuh)
- `fd8f7ae <https://github.com/symfony/symfony-docs/commit/fd8f7ae8b52322b0a2e4243641c4dbd751414d14>`_ #3694 fix the referenced documents names (xabbuh)
- `d617011 <https://github.com/symfony/symfony-docs/commit/d6170116af79aa1cc99413edc6cb7ad2b4683bf7>`_ #3657 Fix typos, remove trailing whitespace. (ifdattic)
- `1b4f6a6 <https://github.com/symfony/symfony-docs/commit/1b4f6a6344ecaa29478293767a21dd896fd22cf2>`_ #3656 Minimize horizontal scrolling, add missing characters, remove trailing whitespace. (ifdattic)
- `7c0c5d1 <https://github.com/symfony/symfony-docs/commit/7c0c5d1565a186dfa6be4d65c84d1e8eadf4b540>`_ #3653 Http cache validation rewording (weaverryan)
- `0fb2c5f <https://github.com/symfony/symfony-docs/commit/0fb2c5f87131212480eb5a7bc8795ba1df56e19a>`_ #3651 [Reference][Forms] remove the label_attr option which is not available in the button type (xabbuh)
- `69ac21b <https://github.com/symfony/symfony-docs/commit/69ac21bcc2c40df3211c6353d4476b6868ff6415>`_ #3642 Fixed some typos and formatting issues (javiereguiluz)
- `93c35d0 <https://github.com/symfony/symfony-docs/commit/93c35d01a5d8dac8aa4ac672f367b5679bef72a5>`_ #3641 Added some examples to the "services as parameters" section (javiereguiluz)
- `12a6676 <https://github.com/symfony/symfony-docs/commit/12a667625ea2233f3ec556eebe9d229f2c9b518d>`_ #3640 [minor] fixed one typo and one formatting issue (javiereguiluz)
- `9967b0c <https://github.com/symfony/symfony-docs/commit/9967b0c5e7c4094df74802696179e69c1e0e7d53>`_ #3638 [#3116] Fixing wrong table name - singular is used elsewhere (weaverryan)
- `4fbf1cd <https://github.com/symfony/symfony-docs/commit/4fbf1cdf65d8f7546f0cfd8fa36ada3f1fa18dab>`_ #3635 [QuickTour] close opened literals (xabbuh)
- `27b3410 <https://github.com/symfony/symfony-docs/commit/27b341017bdd1ac37966833e10660451e784d637>`_ #3692 [Book][Translations] fixing a code block (xabbuh)
- `2192c32 <https://github.com/symfony/symfony-docs/commit/2192c3274e7a64f3a5de1eab5a9a4cb6adf2be9e>`_ #3650 Fixing some build errors (xabbuh)
- `fa3f531 <https://github.com/symfony/symfony-docs/commit/fa3f531bd81446f2a7e6fd8f416abe334319177f>`_ #3677 [Reference][Forms] Remove variables section from tables (xabbuh)
- `cd6d1de <https://github.com/symfony/symfony-docs/commit/cd6d1de336eee177315c7b64874d1f872c45831c>`_ #3676 remove unnecessary code block directive (xabbuh)
- `07822b8 <https://github.com/symfony/symfony-docs/commit/07822b8309083dcaf13b8317f4d30ac486badf5a>`_ #3675 add missing code block directive (xabbuh)
- `739f43f <https://github.com/symfony/symfony-docs/commit/739f43fee9b3e4ddc091aa1ab452d6d1154ee467>`_ #3669 Fixed syntax highlighting (rvanlaarhoven)
- `1f384bc <https://github.com/symfony/symfony-docs/commit/1f384bc2bf8fef59f7cb97837a273d355a72fb75>`_ #3631 Added documentation for message option of the ``True`` constraint (naitsirch)
- `f6a41b9 <https://github.com/symfony/symfony-docs/commit/f6a41b9c0e67a7984ba87c8c323fb5484b439042>`_ #3630 Minor tweaks to form action/method (weaverryan)
- `ae755e0 <https://github.com/symfony/symfony-docs/commit/ae755e0cbb85d80b968ceebb3ff4164a52f9d0f3>`_ #3628 Added anchor for permissions (WouterJ)
- `6380113 <https://github.com/symfony/symfony-docs/commit/6380113ce6ce702d51783bf62c5080ad1e31571d>`_ #3667 Update index.rst (NAYZO)
- `97ef2f7 <https://github.com/symfony/symfony-docs/commit/97ef2f7dcfce4ca6c46a06def7dc8efe6d99e379>`_ #3566 Changes ACL permission setting hints (MicheleOnGit)
- `9f7d742 <https://github.com/symfony/symfony-docs/commit/9f7d7423434e0092135e614bd144613d4ca07f6c>`_ #3654 [Cookbook][Security] Fix VoterInterface signature (bicpi)
- `0a65b6f <https://github.com/symfony/symfony-docs/commit/0a65b6f54ef0240b607186c9ba843ea13b2954b3>`_ #3608 [Reference][Forms] add versionadded directive for multiple option of file type (xabbuh)
- `e34204e <https://github.com/symfony/symfony-docs/commit/e34204e5a3029013d5663b8b3ea821ff1c44159f>`_ #3605 Fixed a plural issue (benjaminpaap)
- `e7d5a45 <https://github.com/symfony/symfony-docs/commit/e7d5a459db50fbedbacbb60e59f0a98f7242db6a>`_ #3599 [CHANGELOG] fix reference to contributing docs (xabbuh)
- `3582bf1 <https://github.com/symfony/symfony-docs/commit/3582bf1cb6a8f2d57f0652834214d0f0d4af0ba2>`_ #3598 add changelog to hidden toctree (xabbuh)
- `58b7f96 <https://github.com/symfony/symfony-docs/commit/58b7f96781f3696fbfaa8adc2f4504b986405161>`_ #3596 [HTTP Cache] Validation model: Fix header name (bicpi)
- `6d1378e <https://github.com/symfony/symfony-docs/commit/6d1378e03e152851fc2d536fee77aa85a521d6af>`_ #3592 Added a tip about hardcoding URLs in functional tests (javiereguiluz)
- `04cf9f8 <https://github.com/symfony/symfony-docs/commit/04cf9f8699d322497b5979f1e38547da509f70f4>`_ #3595 Collection of fixes and improvements (bicpi)
- `2ed0943 <https://github.com/symfony/symfony-docs/commit/2ed0943572465a334d8c6d5b7c2d7c709275b515>`_ #3645 Adjusted the BC rules to be consistent (stof)
- `664a0be <https://github.com/symfony/symfony-docs/commit/664a0bef8b4904b62abf5ec1eb3d7a7afa04f43c>`_ #3633 Added missing PHP syntax coloration (DerekRoth)
- `1714a31 <https://github.com/symfony/symfony-docs/commit/1714a31344020bce18315b5977429bddab1db9a0>`_ #3585 Use consistent method chaining in BlogBundle sample application (ockcyp)
- `cb61f4f <https://github.com/symfony/symfony-docs/commit/cb61f4fcda438583f297591fee1ecd081f4e72a3>`_ #3581 Add missing hyphen in HTTP Fundamentals page (ockcyp)

February, 2014
--------------

New Documentation
~~~~~~~~~~~~~~~~~

- `9dcf467 <https://github.com/symfony/symfony-docs/commit/9dcf467b1cfb247f6acfbc892b55fd33cbe5e02b>`_ #3613 Javiereguiluz revamped quick tour (weaverryan)
- `89c6f1d <https://github.com/symfony/symfony-docs/commit/89c6f1d8437d2c950f3a641140579b437d5346ef>`_ #3439 [Review] Added detailed Backwards Compatibility Promise text (webmozart)
- `0029408 <https://github.com/symfony/symfony-docs/commit/0029408c86b8829c6a37b68b8be94a09f5a48eb1>`_ #3558 Created Documentation CHANGELOG (WouterJ)
- `f6dd678 <https://github.com/symfony/symfony-docs/commit/f6dd67829ff83b9e14486902b6a285dbeb4b3e6d>`_ #3548 Update forms.rst (atmosf3ar)
- `9676f2c <https://github.com/symfony/symfony-docs/commit/9676f2c61156e80503639762de0b339d1aeabb6d>`_ #3523 [Components][EventDispatcher] describe that the event name and the event dispatcher are passed to even... (xabbuh)
- `5c367b4 <https://github.com/symfony/symfony-docs/commit/5c367b4dfa5c3adc8993702b1ae8f686c74419c8>`_ #3517 Fixed OptionsResolver component docs (WouterJ)
- `527c8b6 <https://github.com/symfony/symfony-docs/commit/527c8b6d9042bc8719c5dbe2c1c68a57feeb6eb7>`_ #3496 Added a section about using named assets (vmattila)
- `8ccfe85 <https://github.com/symfony/symfony-docs/commit/8ccfe8559b18fc941768ab88fbaed7ff32a2aa9a>`_ #3491 Added doc for named encoders (tamirvs)
- `46377b2 <https://github.com/symfony/symfony-docs/commit/46377b29e8f72f5093b588ef1ee767d42bc559ad>`_ #3486 Documenting createAccessDeniedException() method (klaussilveira)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `5c4336a <https://github.com/symfony/symfony-docs/commit/5c4336a325ec98bc0eb7ef35baf86bcb9628e490>`_ #3570 Callback: [Validator, validate] expects validate to be static (nixilla)
- `5c367b4 <https://github.com/symfony/symfony-docs/commit/5c367b4dfa5c3adc8993702b1ae8f686c74419c8>`_ #3517 Fixed OptionsResolver component docs (WouterJ)
- `adcbb5d <https://github.com/symfony/symfony-docs/commit/adcbb5de6c3b8d6ba59e619315ef13fe45318494>`_ #3615 Fixes to cookbook/doctrine/registration_form.rst (Crushnaut)
- `a21fb26 <https://github.com/symfony/symfony-docs/commit/a21fb260978eaa27b1cb94fccb0bd0af71b6df7f>`_ #3559 Remove reference to copying parameters.yml from Git cookbook (pwaring)
- `de71a51 <https://github.com/symfony/symfony-docs/commit/de71a5143f6d30fff7e890cea250d047a63916d2>`_ #3551 [Cookbook][Dynamic Form Modification] Fix sample code (rybakit)
- `143db2f <https://github.com/symfony/symfony-docs/commit/143db2f75caa9ef6d7e1c230d0ac9d59c17fde5f>`_ #3550 Update introduction.rst (taavit)
- `384538b <https://github.com/symfony/symfony-docs/commit/384538bcb030c4ae6f8df06840ccd725fca28661>`_ #3549 Fixed createPropertyAccessorBuilder usage (antonbabenko)
- `642e776 <https://github.com/symfony/symfony-docs/commit/642e7768952225b06834acb47496a593b4b7d162>`_ #3544 Fix build errors (xabbuh)
- `d275302 <https://github.com/symfony/symfony-docs/commit/d27530241cf75de4846fe9040bc6ce2235b924f3>`_ #3541 Update generic_event.rst (Lumbendil)
- `819949c <https://github.com/symfony/symfony-docs/commit/819949cce72a4253ef9a4df6f7d260f83d08e5c6>`_ #3537 Add missing variable assignment (colinodell)
- `d7e8262 <https://github.com/symfony/symfony-docs/commit/d7e8262a350b7fa40f34a89b7d3796b06c660db2>`_ #3535 fix form type name. (yositani2002)
- `821af3b <https://github.com/symfony/symfony-docs/commit/821af3ba55c53246670a9bc62a3360ca064777d8>`_ #3493 Type fix in remove.rst (weaverryan)
- `003230f <https://github.com/symfony/symfony-docs/commit/003230fc6c8657c9b12976789618aff30a83fce6>`_ #3530 Update form_customization.rst (dczech)
- `a43f15a <https://github.com/symfony/symfony-docs/commit/a43f15ac376fc1bcac2c0010a1ad0d44319f984f>`_ #3519 [Book][Service Container] Fix syntax highlighting (iamdto)
- `86e02c6 <https://github.com/symfony/symfony-docs/commit/86e02c6f04b4cdfc43707e0bb73bc7d336f50cac>`_ #3514 Fixed some small typos in code example (RobinvdVleuten)
- `696313c <https://github.com/symfony/symfony-docs/commit/696313cf8eef0e72685f3e943d6d6d094f1157ed>`_ #3513 [Component-DI] Fixed typo (saro0h)
- `27dcebd <https://github.com/symfony/symfony-docs/commit/27dcebd1c53cca2d8d991fa4b5060288d8f17c57>`_ #3509 Fix typo: side.bar.twig => sidebar.twig (ifdattic)
- `0dc8c26 <https://github.com/symfony/symfony-docs/commit/0dc8c261384974d91b64391c36fa20478e5ebf78>`_ #3507 Fix a typo (missing `````) in ``:doc:`` link (ifdattic)
- `272197b <https://github.com/symfony/symfony-docs/commit/272197be5d22e637546801f95e6c3db3e2c07f2d>`_ #3504 fix include directive so that the contents are really included (xabbuh)
- `e385d28 <https://github.com/symfony/symfony-docs/commit/e385d28bee7c7418c8175d43befc4954a43a300c>`_ #3503 file extension correction xfliff to xliff (nixilla)
- `6d34aa6 <https://github.com/symfony/symfony-docs/commit/6d34aa6038b8317259d2e8fffd186ad24fef5bc5>`_ #3478 Update custom_password_authenticator.rst (piotras-s)
- `a171700 <https://github.com/symfony/symfony-docs/commit/a171700fb8d9695947bc1b16c6f61c183f296657>`_ #3477 Api key user provider should use "implements" instead of "extends" (skowi)
- `7fe0de3 <https://github.com/symfony/symfony-docs/commit/7fe0de330b2d72155b6b7ec87c59f5a7e7ee4881>`_ #3475 Fixed doc for framework.session.cookie_lifetime refrence. (tyomo4ka)
- `8155e4c <https://github.com/symfony/symfony-docs/commit/8155e4cab70e481962a4775274a4412a4465ecdc>`_ #3473 Update proxy_examples.rst (AZielinski)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `0928249 <https://github.com/symfony/symfony-docs/commit/0928249282cb29336aca665fbe9a8904ec71e994>`_ #3568 Update checkbox_compound.rst.inc (joshuaadickerson)
- `38def3b <https://github.com/symfony/symfony-docs/commit/38def3bd2cd03208b95dfebfbe76aaf994e049ac>`_ #3567 Update checkbox_compound.rst.inc (joshuaadickerson)
- `15d8ab8 <https://github.com/symfony/symfony-docs/commit/15d8ab892168efacb34c53b22b222ef669e90897>`_ #3553 Minimize horizontal scrolling in code blocks to improve readability (ifdattic)
- `5120863 <https://github.com/symfony/symfony-docs/commit/512086321061d3d2d9ae53975d430f7f3d90babf>`_ #3547 Update acl.rst (iqfoundry)
- `b7ac326 <https://github.com/symfony/symfony-docs/commit/b7ac326369845cba198f06514bbe1eaeb6002992>`_ #3557 Minimize horizontal scrolling in code block to improve readability (ifdattic)
- `d974c77 <https://github.com/symfony/symfony-docs/commit/d974c773c9e1a9122244fe2a1aedbe32ee232490>`_ #3556 Fix PSR error (ifdattic)
- `f4bb017 <https://github.com/symfony/symfony-docs/commit/f4bb017d99b225c2ab06490633cb9d30bc0e456c>`_ #3555 Wrap variables in {} for safer interpolation (ifdattic)
- `5f02bca <https://github.com/symfony/symfony-docs/commit/5f02bca0959e20d54c9977d0428bd9bb1324f009>`_ #3552 Fix typos (ifdattic)
- `6e32c47 <https://github.com/symfony/symfony-docs/commit/6e32c473b0bb04620ce3723d962c5650c56b1568>`_ #3546 Fix README: contributions should be based off 2.3 or higher (colinodell)
- `ffa8f76 <https://github.com/symfony/symfony-docs/commit/ffa8f76d3582fe4cc78cc788f6f9c7018ebca75e>`_ #3545 Example of getting entity managers directly from the container (colinodell)
- `6a2a55b <https://github.com/symfony/symfony-docs/commit/6a2a55b2ebf898f20081c4e627f21f700b4fee85>`_ #3579 Fix build errors (xabbuh)
- `dce2e23 <https://github.com/symfony/symfony-docs/commit/dce2e23b4407bb7c468ba2d970981a97b4801d34>`_ #3532 Added tip for Entity Listeners (slavafomin)
- `73adf8b <https://github.com/symfony/symfony-docs/commit/73adf8b6f3d9e55addd19363a3ce010429ce3f05>`_ #3528 Clarify service parameters usages (WouterJ)
- `7e75b64 <https://github.com/symfony/symfony-docs/commit/7e75b64a34659f48e07bb53b34264ed3fb827530>`_ #3533 Moving the new named algorithms into their own cookbook entry (weaverryan)
- `f634600 <https://github.com/symfony/symfony-docs/commit/f634600ce3dcb5fa4a18567faa67bc8e2a29dd29>`_ #3531 Remove horizontal scrolling in code block (ifdattic)
- `9ba4fa7 <https://github.com/symfony/symfony-docs/commit/9ba4fa7d35bfc02cab66e1f7f595a39c6ddf3e2a>`_ #3527 Changes to components domcrawler (ifdattic)
- `8973c81 <https://github.com/symfony/symfony-docs/commit/8973c812c57dca77037da16eb72f3c7c565ef73a>`_ #3526 Changes for Console component (ifdattic)
- `6848bed <https://github.com/symfony/symfony-docs/commit/6848bed188528fb3b11d4f491aa5e3af6440ddb7>`_ #3538 Rebasing #3518 (weaverryan)
- `c838df8 <https://github.com/symfony/symfony-docs/commit/c838df869dade91aa41a703da0485194daacf2c7>`_ #3511 [Component-DI] Removed useless else statement in code example (saro0h)
- `1af6742 <https://github.com/symfony/symfony-docs/commit/1af67425f9653b539f898888d30b42e8e69aa5aa>`_ #3510 add empty line (lazyants)
- `1131247 <https://github.com/symfony/symfony-docs/commit/11312477437e5367da466727acdc89c97b8ed73a>`_ #3508 Add 'in XML' for additional clarity (ifdattic)
- `a650b93 <https://github.com/symfony/symfony-docs/commit/a650b9364297aa5eaa5ffd3c018b3f0858d12238>`_ #3506 Nykopol overriden options (weaverryan)
- `ab10035 <https://github.com/symfony/symfony-docs/commit/ab1003501de3e81bc48226b32b53156e2e7a573a>`_ #3505 replace Akamaï with Akamai (xabbuh)
- `7f56c20 <https://github.com/symfony/symfony-docs/commit/7f56c201ea4cd9b1e7b7ed36ceb2046352a143f2>`_ #3501 [Security] Fix markup (tyx)
- `80a90ba <https://github.com/symfony/symfony-docs/commit/80a90ba8b5c2eceeb2d80bb1386fb2620b8e0c6e>`_ #3500 Minimize horizontal scrolling in code blocks (improve readability) (ifdattic)
- `e5bc4ea <https://github.com/symfony/symfony-docs/commit/e5bc4eafeab96b8b12070ce0435b8a77ee85c6c1>`_ #3498 Remove second empty data (xabbuh)
- `d084d87 <https://github.com/symfony/symfony-docs/commit/d084d876e3ab961c92f2753c73b0a73a75ee7a8b>`_ #3485 [Cookbook][Assetic] Fix "javascripts" tag name typo (bicpi)
- `3250aba <https://github.com/symfony/symfony-docs/commit/3250aba13ccf8662aa8e38cb624b3adeab0944bc>`_ #3481 Fix code block (minimise horizontal scrolling), typo in yaml (ifdattic)

January, 2014
-------------

New Documentation
~~~~~~~~~~~~~~~~~

- `d52f3f8 <https://github.com/symfony/symfony-docs/commit/d52f3f8a146356e9e114474e820f8ec6ac5f2374>`_ #3454 [Security] Add host option (ghostika)
- `11e079b <https://github.com/symfony/symfony-docs/commit/11e079b32e006fdd3e219dd24b3ed41e94cc38ce>`_ #3446 [WCM] Documented deprecation of the apache router. (jakzal)
- `0a0bf4c <https://github.com/symfony/symfony-docs/commit/0a0bf4c196c789edb337a6c27811f7e05098d387>`_ #3437 Add info about callback in options resolver (marekkalnik)
- `6db5f23 <https://github.com/symfony/symfony-docs/commit/6db5f233c71a4bf8de0fdf1bbc1c4674b7d3316c>`_ #3426 New Feature: Change the Default Command in the Console component (danielcsgomes)
- `6b3c424 <https://github.com/symfony/symfony-docs/commit/6b3c424034fec441a32d8305600c1c26936b8f1e>`_ #3428 Translation - Added info about JsonFileLoader added in 2.4 (singles)

Fixed Documentation
~~~~~~~~~~~~~~~~~~~

- `fb22fa0 <https://github.com/symfony/symfony-docs/commit/fb22fa094fe7cf14e6499836f701c87de4886149>`_ #3456 remove duplicate label (xabbuh)
- `a87fe18 <https://github.com/symfony/symfony-docs/commit/a87fe18083c34238f500b49d30f340efd784dea6>`_ #3470 Fixed typo (danielcsgomes)
- `c205bc6 <https://github.com/symfony/symfony-docs/commit/c205bc6798bac34741f2d4d91450aac75ab14b93>`_ #3468 enclose YAML string with double quotes to fix syntax highlighting (xabbuh)
- `89963cc <https://github.com/symfony/symfony-docs/commit/89963cc246263e7e7cdecd3cad1f019ff9cb28a5>`_ #3463 Fix typos in cookbook/testing/database (ifdattic)
- `e0a52ec <https://github.com/symfony/symfony-docs/commit/e0a52ecf0cbcf1b5aa029f323588880080f5c6f3>`_ #3460 remove confusing outdated note on interactive rebasing (xabbuh)
- `6831b13 <https://github.com/symfony/symfony-docs/commit/6831b1337f99c26d9f04eb82990cc3b3ac128de0>`_ #3455 [Contributing][Code] fix indentation so that the text is rendered properly (xabbuh)
- `ea5816f <https://github.com/symfony/symfony-docs/commit/ea5816f571309decd946bf30aa0b3b84fffacb9e>`_ #3433 [WIP][Reference][Form Types] Update "radio" form type (bicpi)
- `42c80d1 <https://github.com/symfony/symfony-docs/commit/42c80d12ac760f40834afef76fd42db83d4d4a33>`_ #3448 Overridden tweak (weaverryan)
- `bede4c3 <https://github.com/symfony/symfony-docs/commit/bede4c3a0e5d6be62d382c3ebb673b3059fbb566>`_ #3447 Fix error in namespace when use TokenInterface (joanteixi)
- `d9d7c58 <https://github.com/symfony/symfony-docs/commit/d9d7c58ca41ae370545ae25f13857780c089f970>`_ #3444 Fix issue #3442 (ifdattic)
- `a6ad607 <https://github.com/symfony/symfony-docs/commit/a6ad607fa9397ba680fdf49e8b74c4bd55143ea2>`_ #3441 [Expression]Change title 'Accessing Public Methods' (Pyrech)
- `9e2e64b <https://github.com/symfony/symfony-docs/commit/9e2e64b26a355416038b632b7eec89c7c14490cb>`_ #3427 Removed code references to Symfony Standard Distribution (danielcsgomes)
- `3c2c5fc <https://github.com/symfony/symfony-docs/commit/3c2c5fcb855c456c682fb6dcf3a4dce54f3686ee>`_ #3435 Update custom_password_authenticator.rst (boardyuk)
- `26b8146 <https://github.com/symfony/symfony-docs/commit/26b8146188a3f8bedf2e681d40509b418c8e7ec0>`_ #3415 [#3334] the data_class option was not introduced in 2.4 (xabbuh)
- `0b2a491 <https://github.com/symfony/symfony-docs/commit/0b2a49199752f60aa1bcc16d48f4c558160e852e>`_ #3414 add missing code-block directive (xabbuh)
- `4988118 <https://github.com/symfony/symfony-docs/commit/4988118e127dc51d73e2518982c0a0f4ca9206f1>`_ #3432 [Reference][Form Types] Add "max_length" option in form type (nykopol)
- `26a7b1b <https://github.com/symfony/symfony-docs/commit/26a7b1b80aa654c9293599743f9c0a38054eb4d3>`_ #3423 [Session Configuration] add clarifying notes on session save handler proxies (cordoval)
- `f2f5e9a <https://github.com/symfony/symfony-docs/commit/f2f5e9ac94db2eaaf89c151b80d202fc29d49b59>`_ #3421 [Contributing] Cleaning the "contributing patch" page a bit (lemoinem)

Minor Documentation Changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~

- `f285d93 <https://github.com/symfony/symfony-docs/commit/f285d930377d8cbaedccc3ad46853fa72ee6439d>`_ #3451 some language tweaks (AE, third-person perspective) (xabbuh)
- `b9bbe5d <https://github.com/symfony/symfony-docs/commit/b9bbe5d5f8cb427f1a52e839f23a0f286da1a010>`_ #3499 Fix YAML syntax highlight + remove trailing whitespace (ifdattic)
- `2b7e0f6 <https://github.com/symfony/symfony-docs/commit/2b7e0f6f2f9982e600918f447852a6f4c60966a1>`_ #3497 Fix highlighting (WouterJ)
- `2746067 <https://github.com/symfony/symfony-docs/commit/27460671c635a898912b931620adaa1cc1cd64f9>`_ #3472 Fixed `````versionadded````` inconsistencies in Symfony 2.5+ (danielcsgomes)
- `a535ae0 <https://github.com/symfony/symfony-docs/commit/a535ae0383a2a6715021681980877b0205dc3281>`_ #3471 Fixed `````versionadded````` inconsistencies in Symfony 2.3 (danielcsgomes)
- `f077a8e <https://github.com/symfony/symfony-docs/commit/f077a8e71c4973e7775db8c9fb548a0866d21131>`_ #3465 change wording in versionadded example to be consistent with what we use... (xabbuh)
- `f9f7548 <https://github.com/symfony/symfony-docs/commit/f9f7548c7a53e62564b30d7e945a9b52b3f358db>`_ #3462 Replace ... with etc (ifdattic)
- `65efcc4 <https://github.com/symfony/symfony-docs/commit/65efcc4f64365acf5895597bb32e9b611f9bbfcd>`_ #3445 [Reference][Form Types] Add missing (but existing) options to "form" type (bicpi)
- `1d1b91d <https://github.com/symfony/symfony-docs/commit/1d1b91d6cbd4479e85ff2fdbc2cbab4f7a9a778b>`_ #3431 [Config] add cautionary note on ini file loader limitation (cordoval)
- `f2eaf9b <https://github.com/symfony/symfony-docs/commit/f2eaf9bbc5d3a73c83ef6e4ce1830bd3e277dcc0>`_ #3419 doctrine file upload example uses dir -- caution added (cordoval)
- `72b53ad <https://github.com/symfony/symfony-docs/commit/72b53ad4312f74920568e39ebbddc2b3b8008797>`_ #3404 [#3276] Trying to further clarify the session storage directory details (weaverryan)
- `67b7bbd <https://github.com/symfony/symfony-docs/commit/67b7bbda858337555b7404a17e6ead20d2144eff>`_ #3413 [Cookbook][Bundles] improve explanation of code block for bundle removal (cordoval)
- `7c5a914 <https://github.com/symfony/symfony-docs/commit/7c5a9141d6dd716e692b27904190225be324f332>`_ #3369 Indicate that Group Sequence Providers can use YAML (karptonite)
- `1e0311e <https://github.com/symfony/symfony-docs/commit/1e0311ef0124fda8ad0cb07f73a3a52ce3303f2b>`_ #3416 add empty_data option where required option is used (xabbuh)
- `2be3f52 <https://github.com/symfony/symfony-docs/commit/2be3f52cf5178606e54826e0766f31ce110ee122>`_ #3422 [Cookbook][Custom Authentication Provider] add a note of warning for when forbidding anonymous users (cordoval)
- `e255de9 <https://github.com/symfony/symfony-docs/commit/e255de9bfec27f1f8e920f8e9efdfc8576b85229>`_ #3429 [Reference][Form Types] Document "with_minutes" time/datetime option (bicpi)
