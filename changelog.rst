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
