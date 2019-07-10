.. index::
   single: Tests; Assertions

Functional Test specific Assertions
===================================

When doing functional tests, sometimes you need to make complex assertions in
order to check whether the ``Request``, the ``Response`` or the ``Crawler``
contain the expected information to make your test succeed.

Here is an example with plain PHPUnit::

    $this->assertGreaterThan(
        0,
        $crawler->filter('html:contains("Hello World")')->count()
    );

Now here is the example with the assertions specific to Symfony::

    $this->assertSelectorTextContains('html', 'Hello World');

.. note::

    These assertions only work if a request has been made with the ``Client``
    in a test case extending the ``WebTestCase`` class.

Assertions Reference
---------------------

Response
~~~~~~~~

- ``assertResponseIsSuccessful()``
- ``assertResponseStatusCodeSame()``
- ``assertResponseRedirects()``
- ``assertResponseHasHeader()``
- ``assertResponseNotHasHeader()``
- ``assertResponseHeaderSame()``
- ``assertResponseHeaderNotSame()``
- ``assertResponseHasCookie()``
- ``assertResponseNotHasCookie()``
- ``assertResponseCookieValueSame()``

Request
~~~~~~~

- ``assertRequestAttributeValueSame()``
- ``assertRouteSame()``

Browser
~~~~~~~

- ``assertBrowserHasCookie()``
- ``assertBrowserNotHasCookie()``
- ``assertBrowserCookieValueSame()``

Crawler
~~~~~~~

- ``assertSelectorExists()``
- ``assertSelectorNotExists()``
- ``assertSelectorTextContains()``
- ``assertSelectorTextSame()``
- ``assertSelectorTextNotContains()``
- ``assertPageTitleSame()``
- ``assertPageTitleContains()``
- ``assertInputValueSame()``
- ``assertInputValueNotSame()``

.. versionadded:: 4.4

    Starting from Symfony 4.4, when using `symfony/panther`_ for end-to-end
    testing, you can use all the above assertions except the ones related to
    the :doc:`Crawler </components/dom_crawler>`.

.. _`symfony/panther`: https://github.com/symfony/panther
