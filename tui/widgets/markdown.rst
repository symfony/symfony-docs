MarkdownWidget
==============

``MarkdownWidget`` renders Markdown text with terminal styling. It
uses ``league/commonmark`` for parsing and ``tempest/highlight`` for
syntax highlighting of fenced code blocks.

When to Use
-----------

Use ``MarkdownWidget`` when you need to display rich formatted text:
AI assistant responses, documentation previews, changelogs, README
files, etc. For plain unformatted text, use :doc:`/tui/widgets/text` instead.

Basic Usage
-----------

.. code-block:: php

    use Symfony\Component\Tui\Widget\MarkdownWidget;

    $md = new MarkdownWidget('# Hello

    This is **bold** and *italic* text.');
    $tui->add($md);

Update the content at any time::

    $md->setText($newMarkdown);

Supported Markdown Features
---------------------------

``MarkdownWidget`` supports the full CommonMark specification plus
GitHub Flavored Markdown extensions:

* **Headings** (``# H1`` through ``###### H6``), rendered with a
  ``#`` prefix and heading styling.
* **Bold** (``**text**``) and **italic** (``*text*``).
* **Strikethrough** (``~~text~~``).
* **Inline code** (``code``), highlighted with code styling.
* **Fenced code blocks** with syntax highlighting for most languages.
* **Indented code blocks**.
* **Block quotes** (``> text``), rendered with a vertical border.
* **Ordered and unordered lists**.
* **Links** (``[text](url)``), the URL is displayed in parentheses.
* **Tables**, rendered with Unicode box-drawing characters.
* **Horizontal rules** (``---``).

Syntax Highlighting
-------------------

Fenced code blocks with a language identifier are syntax-highlighted
automatically::

    $md = new MarkdownWidget('
    ```php
    $greeting = "Hello, world!";
    echo $greeting;
    ```
    ');

The default highlighter uses a dark terminal theme. Pass a custom
``Highlighter`` instance to the constructor for a different theme::

    use Tempest\Highlight\Highlighter;

    $md = new MarkdownWidget($text, highlighter: new Highlighter($myTheme));

Styleable Elements
------------------

``MarkdownWidget`` exposes several styleable elements for fine-grained
control over the appearance:

* ``heading``: heading text.
* ``bold``: bold inline text.
* ``italic``: italic inline text.
* ``strikethrough``: strikethrough text.
* ``code``: inline code.
* ``code-block-border``: top/bottom border of code blocks.
* ``quote``: block quote text.
* ``quote-border``: block quote vertical border.
* ``list-bullet``: list bullet or number.
* ``link``: link text.
* ``link-url``: link URL.
* ``hr``: horizontal rule.

Events
------

``MarkdownWidget`` does not emit any events. It is a pure display
widget.
