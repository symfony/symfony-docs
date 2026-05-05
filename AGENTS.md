# Symfony Docs

Official Symfony documentation repository. All content is reStructuredText (`.rst`).

## Branch Rules

Maintained branches change over time. Fetch the current list from the
`maintained_versions` key at https://symfony.com/releases.json before
choosing a target branch.

- **Bug fixes**: target the oldest maintained branch that contains the bug
- **New features**: target the branch that introduced the feature — or the next still-maintained branch if the original is no longer maintained
- **Other non-feature changes** (typos, rewording, restructuring): target the oldest maintained branch

**Exception:** @frontend.rst and @frontend/ docs cover tools that are
versioned independently from Symfony (AssetMapper, Webpack Encore, etc.),
so all changes — including new features — target the oldest maintained branch.

## RST Formatting

### Heading Levels

```
Level 1  =====
Level 2  -----
Level 3  ~~~~~
Level 4  .....
Level 5  """""
```

Underline characters must span the full heading text length.

The only exception is @reference/configuration/ files that use `~~~~~` (level 2), `.....` (level 3) and `"""""` (level 4)

### General Formatting

- **Line length**: break at 80 characters (exceptions: tables, URLs, orphan lines)
- **Code blocks**: use `::` shorthand for PHP instead of `.. code-block:: php`, unless `::` would be alone on its line
- **Inline code**: use double backticks (``some_inline_code``)
- **Links**: no inline hyperlinks — place link targets at the bottom of the file
- **Bold/italic**: don't span across multiple lines
- **Lists**: must start at the beginning of a line (no indentation before the bullet)

### Directives

- Indent all directive content by **4 spaces**
- Leave a **blank line** after the `::` marker before the content
- Leave a **blank line** before and after every directive
- Continuation lines of multi-line list items align with the text start (not the bullet)
- Use these admonitions: `note`, `tip`, `warning`, `caution`, `seealso`
- Use `versionadded` and `deprecated` directives:
  ```rst
  .. versionadded:: 7.2

      The ``Something`` feature was introduced in Symfony 7.2.

  .. deprecated:: 7.2

      The ``OldThing`` was deprecated in Symfony 7.2.
  ```

### Internal Links

```rst
:doc:`/absolute/path/to/page`       (auto title)
:doc:`Custom Title </path/to/page>` (custom title)
:ref:`Link Text <target-label>`     (cross-reference to labeled section)
```

Always use absolute paths (starting with `/`), never relative.

### External Links

```rst
`External link`_                          (link title)
.. _`External link`: https://example.com/ (link target)
```

Do not use inline syntax for external link. Add target at the end of the file.

### API Links

```rst
:class:`Symfony\\Component\\Routing\\Matcher\\ApacheUrlMatcher`
:method:`Symfony\\Component\\HttpKernel\\Bundle\\Bundle::build`
:phpclass:`SimpleXMLElement`
:phpmethod:`DateTime::createFromFormat`
:phpfunction:`iterator_to_array`
```

## Configuration Blocks

Show all supported formats using `.. configuration-block::`. Format order:

| Context                | Order                          |
|------------------------|--------------------------------|
| Configuration/services | YAML, XML, PHP                 |
| Routing                | Attributes, YAML, XML, PHP     |
| Validation             | Attributes, YAML, XML, PHP     |
| Doctrine Mapping       | Attributes, YAML, XML, PHP     |
| Translation            | XML, YAML, PHP                 |

Use `.. tabs::` for non-configuration tabbed content (e.g. showing Webpack Encore vs AssetMapper).

Some docs show examples for both Symfony and non-Symfony PHP apps.
Use `.. code-block:: php-symfony` and `.. code-block:: php-standalone`.

## Code Examples

- Follow Symfony coding standards and best practices
- Use realistic examples — avoid `foo`, `bar`, `demo`
- Use `Acme` as vendor name; `example.com`, `example.org`, `example.net` for domains
- Break lines at the 85th character in code blocks
- Use **4-space indentation** (even for YAML examples)
- Start with namespace declaration and relevant `use` statements when useful
- Begin code blocks with a comment showing the filename (no blank line after unless next line is also a comment)
- Prefix every bash line with `$`
- Folding comments by language: `// ...` (PHP), `# ...` (YAML/bash), `{# ... #}` (Twig), `<!-- ... -->` (XML/HTML), `; ...` (INI), `...` (text)
- When folding part of a line (e.g. a variable value), use `...` without comment markers
- In YAML, put spaces after `{` and before `}` (e.g. `{ _controller: ... }`), but not in Twig (e.g. `{'hello': 'value'}`)

## English Language

- Use **American English**
- **Title case** for section titles (capitalize first word + all words except closed-class words)
- No Oxford commas
- Use **you** instead of **we** (no first person)
- Use **gender-neutral pronouns** (they/their/them)
- **Contractions** are allowed (`you'd`, `it's`, etc.)
- Avoid passive voice
- Avoid belittling words: *obviously, simply, just, easy/easily, clearly, basically, of course, trivial, merely, logically, quick/quickly*
- Keep language **basic and accessible** for a worldwide non-native English audience (use simple words, avoid idioms)

## Images and Diagrams

- All images must have **alt descriptions** (concise, start with capital, end with period)
- Don't start alt text with "A screenshot of" or "Diagram of"
- Describe complex diagrams in surrounding text, not in the alt description

## Files and Directories

- Trailing slash for directories (`bin/` not `bin`)
- Leading dot for file extensions (`.xml` not `xml`)
- Use `your-project/` as top-level directory name in hierarchy examples

## Build Locally

```bash
cd _build/ && composer install && php build.php
php -S localhost:8000 -t output/
```
