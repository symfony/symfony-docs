# Symfony Docs

Official Symfony documentation repository. All content is reStructuredText (`.rst`).

## General Guidelines

- Explain things clearly but as concisely as possible
- Low-level explanations are fine; only skip the very deep internals that readers can see in the code
- Don't detail method signatures unless the method is critical
- Don't list every command argument/option — focus on the critical ones
- Don't document errors/exceptions whose in-code message already makes the cause obvious

### What to Document

- Don't write cookbooks or tutorials. The docs are reference material; education belongs to SymfonyCasts
- Integrate new content into the existing article about that topic. Don't create new pages, and don't drop a new section in the middle of another section
- Don't add to `components/*`. Those docs are being merged into the main articles, where features are explained as used inside a Symfony application
- Third-party integration packages (mailer, notifier and webhook bridges, etc.) are documented in the README of their own repository. The docs only list them
- Don't document what developers discover by themselves: self-explanatory error messages, which exception class is thrown, IDE-discoverable constants and methods, exhaustive lists of options, methods or status codes. Link to the code namespace instead of listing
- Don't repeat what another page or section already explains. Link to it with `:doc:`, `:ref:` or a short `seealso`
- Open an article or section with why and when to use the feature, then a small realistic example, then explain each behavior next to the code that shows it. Avoid intros that restate the name ("the deep cloner deep clones things")
- When several ways exist, show only the best one in the main example and mention the others in a secondary section or a note. Prefer pure PHP over expressions and other string-based config
- Put details in code and console comments rather than in the prose around them, so readers don't miss them
- Never mention a Symfony version in normal prose. Only the `versionadded` and `deprecated` directives may name a version
- If possible, werify every behavioral claim in the Symfony source code before writing it: which transports, bridges or adapters support a feature, what is thrown and whether a wrapper (failover, chain) catches it, defaults and option names. Check all bridges, not only the ones a PR mentions. Run the commands you document. When discussing behavior in a review, link the permalink to the code

## Branch Rules

Maintained branches change over time. Fetch the current list from the
`maintained_versions` key at https://symfony.com/releases.json before
choosing a target branch.

- **Bug fixes**: target the oldest maintained branch that contains the bug
- **New features**: target the branch that introduced the feature — or the next still-maintained branch if the original is no longer maintained
- **Other non-feature changes** (typos, rewording, restructuring): target the oldest maintained branch
- Target the oldest maintained branch that **already contains the affected content**, not the oldest maintained branch overall
- A deprecation is documented in the branch that deprecates it. In the next major, remove every usage of the deprecated feature instead of rewording the `deprecated` note
- A bug fixed in version N keeps a `warning` in older branches saying it is fixed in N. The N branch drops the warning entirely
- Version-independent tools (Symfony CLI, AssetMapper, Encore) never mention version numbers. Assume readers use the latest version

**Exceptions:** the following docs cover projects versioned independently
from Symfony (AssetMapper, Webpack Encore, etc.). All changes to these docs
— including new features — target the oldest maintained branch:

- @frontend.rst and @frontend/
- @setup/symfony_cli.rst

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
- **Code blocks**: use the `::` shorthand for PHP. Never fall back to `.. code-block:: php` to avoid a standalone `::`; add a short introductory phrase ending in `::` instead (e.g. `This is how you use it in a controller::`) or reorganize the text
- **Shell commands**: use `.. code-block:: terminal`, one `$` per command, output on the following lines.
- **Twig**: use `.. code-block:: twig` when the block has no HTML tags, `html+twig` otherwise
- **Inline code**: use double backticks (``some_inline_code``)
- **Links**: no inline hyperlinks — place link targets at the bottom of the file
- **php.net links**: use the short form, without `www.`, `/en/` or `.php` (e.g. `https://php.net/manual/function.array-map`)
- **Bold/italic**: don't span across multiple lines
- **Lists**: must start at the beginning of a line (no indentation before the bullet)

### Directives

- Indent all directive content by **4 spaces**
- Leave a **blank line** after the `::` marker before the content
- Leave a **blank line** before and after every directive
- Continuation lines of multi-line list items align with the text start (not the bullet)
- Use these admonitions: `note`, `tip`, `warning`, `danger`, `seealso` (`caution` is forbidden)

### Admonitions

- Keep notes and tips short. A note that compares two features is two lines. If it grows into a paragraph, turn it into a section
- A note must not mention concepts the reader hasn't met yet on that page. Place it after the section that introduces them
- Don't add a note when the code example and its comments already say it, or when a table or list on the same page already carries the information
- Don't break a short section with an admonition; plain prose reads better there

### Version Directives

```rst
.. versionadded:: X.Y

    The ``Something`` feature was introduced in Symfony X.Y.

.. deprecated:: X.Y

    The ``OldThing`` was deprecated in Symfony X.Y.
```

- The body contains only that one sentence. These directives are deleted automatically at each major release, so any explanation goes in a `note` or `tip` next to it (a `versionadded` may sit inside that note)
- Placement: at the top of a long section, at the bottom of a short one
- A feature that behaves like a bug fix may be phrased as "X now also works with Y"
- Each branch keeps only directives of its own major (e.g. `7.0`+ on 7.x). Nested directives inside tips and notes count too
- Never write a version that doesn't exist on the branch; the linter only checks the major
- Versions of other packages (Encore, Monolog, contracts) are allowed and whitelisted in `.doctor-rst.yaml`. Adding to the whitelist is a deliberate exception

### Internal Links

```rst
:doc:`/absolute/path/to/page`       (auto title)
:doc:`Custom Title </path/to/page>` (custom title)
:ref:`Link Text <target-label>`     (cross-reference to labeled section)
```

Always use absolute paths (starting with `/`), never relative.

Define link targets with an explicit label before the heading, followed by a blank line:

```rst
.. _target-label:

Section Title
-------------
```

Don't rely on auto-generated title anchors; they break when the title changes.

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
| Code examples          | PHP Symfony, PHP Standalone    |

Always show all formats together in one block, and update all of them when changing one.

Use `.. tabs::` for non-configuration tabbed content (e.g. showing Webpack Encore vs AssetMapper).

Some docs show examples for both Symfony and non-Symfony PHP apps.
Use `.. code-block:: php-symfony` and `.. code-block:: php-standalone`.

## Code Examples

- Follow Symfony coding standards and best practices
- Use realistic examples — avoid `foo`, `bar`, `demo`
- Use `Acme` as vendor name; `example.com`, `example.org`, `example.net` for domains
- Variable names must say what the value is (`$articleSlug`, not `$article` for a slug)
- Never use PHP named arguments; argument names can change. Explain parameters in prose or with plain variables
- CI checks code blocks with [code-block-checker](https://github.com/symfony-tools/code-block-checker): PHP, YAML, XML and Twig blocks must parse; classes in `use` statements must exist in a real Symfony app (except `App\` and `Acme\` namespaces); config blocks whose filename starts with `config/packages/` are loaded into that app
- Use only the placeholders the checker understands, and use them to keep examples concise:
  - `...` as an argument, array element, statement or block body: `foo(...)`, `[...]`, `...;`, `{ ... }`
  - a single-line comment as a value: `$id = /* the message id */;`
  - `...` as a YAML value
  - a method or property snippet without its wrapping class
- Anything else must be real PHP: no type hints in call arguments (`new Foo(array $bar)`), no empty right-hand sides (`$x = ;`), no multi-line comment placeholders
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
- Article and section titles matter for search. Name them after the task, not the tool ("End-to-End Testing", not "Panther")
- No Oxford commas
- Use **you** instead of **we** (no first person)
- Use **gender-neutral pronouns** (they/their/them)
- **Contractions** are allowed (`you'd`, `it's`, etc.)
- Avoid passive voice
- Avoid belittling words: *obviously, simply, just, easy/easily, clearly, basically, of course, trivial, merely, logically, quick/quickly*
- Avoid casual and marketing phrasing: *whatever*, *Great question!*, performance claims and scores
- Reuse the vocabulary the docs already use for a concept (e.g. "anonymous users", "route parameter"). Grep before introducing a new term
- Keep language **basic and accessible** for a worldwide non-native English audience (use simple words, avoid idioms)

## Images and Diagrams

- Avoid images. They age fast and add maintenance. Add one only when text can't explain it
- All images must have **alt descriptions** (concise, start with capital, end with period)
- Don't start alt text with "A screenshot of" or "Diagram of"
- Describe complex diagrams in surrounding text, not in the alt description

## Files and Directories

- Trailing slash for directories (`bin/` not `bin`)
- Leading dot for file extensions (`.xml` not `xml`)
- Use `your-project/` as top-level directory name in hierarchy examples

## Moving or Removing Pages

- Add one line per moved or deleted page at the **end** of `_build/redirection_map`, in the form `/old/path /new/path` or `/old/path /new/path#anchor`. Update in place any existing line whose target disappeared
- An anchor used as a redirect target must precede a heading, not a list
- Keep every label that other pages reference. Grep the tree for `:doc:` and `:ref:` pointing at the old path and fix them
- Run the full build; it fails on unresolved targets

## Reference Section

The @reference/ directory has its own rules in @reference/AGENTS.md. Read it before touching anything there.

## Build and Lint Locally

```bash
cd _build/ && composer install && php build.php --disable-cache
php -S localhost:8000 -t output/
```

`_build/output/` is gitignored; never read, diff or commit it.

If Docker is available, lint with the same tool CI uses. Read the image tag from `.github/workflows/ci.yaml`:

```bash
docker run --rm -v "$PWD":/github/workspace -w /github/workspace oskarstark/doctor-rst:<tag> --short
```
