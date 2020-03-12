.. index::
   single: Tests; Assertions

Functional Test specific Assertions
===================================

When doing functional tests, sometimes you need to make complex assertions in
order to check whether the ``Request``, the ``Response`` or the ``Crawler``
contain the expected information to make your test succeed.

The following example uses plain PHPUnit to assert that the response redirects
to a certain URL::

    $this->assertSame(301, $client->getResponse()->getStatusCode());
    $this->assertSame('https://example.com', $client->getResponse()->headers->get('Location'));

This is the same example using the assertions provided by Symfony::

    $this->assertResponseRedirects('https://example.com', 301);

Assertions Reference
---------------------

Response
~~~~~~~~

.. note::

    The following assertions only work if a request has been made with the
    ``Client`` in a test case extending the ``WebTestCase`` class.

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

.. note::

    The following assertions only work if a request has been made with the
    ``Client`` in a test case extending the ``WebTestCase`` class.

- ``assertRequestAttributeValueSame()``
- ``assertRouteSame()``

Browser
~~~~~~~

.. note::

    The following assertions only work if a request has been made with the
    ``Client`` in a test case extending the ``WebTestCase`` class.

- ``assertBrowserHasCookie()``
- ``assertBrowserNotHasCookie()``
- ``assertBrowserCookieValueSame()``

Crawler
~~~~~~~

.. note::

    The following assertions only work if a request has been made with the
    ``Client`` in a test case extending the ``WebTestCase`` class. In addition,
    they are not available when using `symfony/panther`_ for end-to-end testing.

- ``assertSelectorExists()``
- ``assertSelectorNotExists()``
- ``assertSelectorTextContains()`` (note: it only checks the first selector occurrence)
- ``assertSelectorTextSame()`` (note: it only checks the first selector occurrence)
- ``assertSelectorTextNotContains()`` (note: it only checks the first selector occurrence)
- ``assertPageTitleSame()``
- ``assertPageTitleContains()``
- ``assertInputValueSame()``
- ``assertInputValueNotSame()``

Mailer
~~~~~~

.. versionadded:: 5.1

    Starting from Symfony 5.1, the following assertions no longer require to make
    a request with the ``Client`` in a test case extending the ``WebTestCase`` class.

- ``assertEmailCount()``
- ``assertQueuedEmailCount()``
- ``assertEmailIsQueued()``
- ``assertEmailIsNotQueued()``
- ``assertEmailAttachementCount()``
- ``assertEmailTextBodyContains()``
- ``assertEmailTextBodyNotContains()``
- ``assertEmailHtmlBodyContains()``
- ``assertEmailHtmlBodyNotContains()``
- ``assertEmailHasHeader()``
- ``assertEmailNotHasHeader()``
- ``assertEmailHeaderSame()``
- ``assertEmailHeaderNotSame()``
- ``assertEmailAddressContains()``

.. _`symfony/panther`: https://github.com/symfony/panther
