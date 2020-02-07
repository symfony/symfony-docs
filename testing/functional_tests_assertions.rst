.. index::
   single: Tests; Assertions

Functional Test specific Assertions
===================================

.. versionadded:: 4.3

    The shortcut methods for assertions using ``WebTestCase`` were introduced
    in Symfony 4.3.

When doing functional tests, sometimes you need to make complex assertions in
order to check whether the ``Request``, the ``Response`` or the ``Crawler``
contain the expected information to make your test succeed.

The following example uses plain PHPUnit to assert that the response redirects
to a certain URL::

    $this->assertSame(301, $client->getResponse()->getStatusCode());
    $this->assertSame('https://example.com', $client->getResponse()->headers->get('Location'));

This is the same example using the assertions provided by Symfony::

    $this->assertResponseRedirects('https://example.com', 301);

.. note::

    These assertions only work if a request has been made with the ``Client``
    in a test case extending the ``WebTestCase`` class.

Assertions Reference
---------------------

.. versionadded:: 4.4

    Starting from Symfony 4.4, when using `symfony/panther`_ for end-to-end
    testing, you can use all the following assertions except the ones related to
    the :doc:`Crawler </components/dom_crawler>`.

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
- ``assertSelectorTextContains()`` (note: it only checks the first selector occurrence)
- ``assertSelectorTextSame()`` (note: it only checks the first selector occurrence)
- ``assertSelectorTextNotContains()`` (note: it only checks the first selector occurrence)
- ``assertPageTitleSame()``
- ``assertPageTitleContains()``
- ``assertInputValueSame()``
- ``assertInputValueNotSame()``

Mailer
~~~~~~

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

.. versionadded:: 4.4

    The mailer assert methods were introduced in Symfony 4.4.

.. _`symfony/panther`: https://github.com/symfony/panther
