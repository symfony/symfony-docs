# Reference Docs

Lookup-style reference for Symfony options, types and constraints. These rules
complement the root `AGENTS.md`.

## General Rules

- Reference pages are for looking things up, not for learning. Keep
  explanations shorter than in the main guides and never repeat what a guide
  already explains; link to it instead (e.g. delegate "validation groups" to
  `/validation/groups`).
- Be exhaustive where the guides are selective: document *all* options and
  arguments, including the ones guides skip, and show more short examples.
- Sort entries alphabetically (case-insensitive) within each section: options,
  tags, attributes, twig functions/filters. Shared `.rst.inc` includes are
  interleaved in their alphabetical position, not appended at the end.
- Document every option starting with its type/default line (one space before
  `**default**`, values in double backticks); use `**required**` when there is
  no default:

  ```rst
  **type**: ``string`` **default**: ``UTF-8``
  ```

- There are no toctrees in `reference/`. Register every new page in the right
  `map.rst.inc` file (`reference/map.rst.inc`, `constraints/map.rst.inc` or
  `forms/types/map.rst.inc`), inside the right group, alphabetically.
- Reuse the shared snippets before writing anything: `constraints/_*.rst.inc`
  and `forms/types/options/*.rst.inc`. A leading `_` in the filename means the
  snippet is a fragment without a heading. Before editing a snippet, check all
  the pages that include it: `grep -rn "<snippet>" reference/`.
- Name new anchors `reference-<section>-<option>` (e.g.
  `reference-framework-secret`). The main guides link heavily into
  `reference/`; don't rename or remove anchors and headings without updating
  incoming links (and `_build/redirection_map` for moved pages).
- To reference another option of the same page, use the implicit target syntax
  (`` the `max`_ option ``), which points to the option heading.

## Constraint Pages (`constraints/`)

- File name is the constraint class name in PascalCase (`Length.rst`).
- Structure: title (bare class name) → 1-3 sentence description → header
  simple table with `Applies to`, `Class` and `Validator` rows (omit
  `Validator` for constraints without one) → `Basic Usage` (configuration
  block with `php-attributes`, `yaml`, `xml`, `php`) → `Options`.
- Option headings are level 3 and wrapped in double backticks
  (`` ``maxMessage`` ``). Message options that support placeholders end with a
  `Parameter` / `Description` table.
- The `groups`, `payload` and `normalizer` options exist as shared includes
  (`_groups-option.rst.inc`, etc.); include them, don't rewrite them.

## Form Type Pages (`forms/types/`)

- File name is the lowercase type name without the `Type` suffix
  (`money.rst` → `MoneyType`). Title is `XxxType Field`.
- Structure: title → short description → header grid table (`Rendered as`,
  `Default invalid message`, `Legacy invalid message`, `Parent type`,
  `Class`) → `.. include:: /reference/forms/types/options/_debug_form.rst.inc`
  → sections in this order: `Basic Usage` (optional), `Field Options`,
  `Overridden Options`, `Inherited Options`, `Field Variables`.
- An option shared by two or more types must live in
  `forms/types/options/<option>.rst.inc` and be included from each page. An
  option used by a single type is written inline under `Field Options`.
- To make an included option linkable, place an anchor on its own line right
  before the `.. include::` line.
- Parent and child types: always list the type's own and overridden options and
  the options inherited from any parent other than `FormType`. A new option on
  a parent type must be added to all its child pages (run
  `debug:form <type> --format=json` to see what each type resolves).
- From `FormType`, new pages list this common set: `attr`,
  `attr_translation_parameters`, `data`, `disabled`, `empty_data`,
  `error_bubbling`, `error_mapping`, `help`, `help_attr`, `help_html`,
  `help_translation_parameters`, `label`, `label_attr`, `label_format`,
  `label_html`, `label_translation_parameters`, `mapped`, `required`, `row_attr`
  and `translation_domain`, plus `invalid_message_parameters` wherever
  `invalid_message` is listed. Some existing pages only list part of this set.
  The `form`, `button`, `submit` and `reset` pages don't follow this rule.
  Exclusions: `hidden` has no `help*`, `label*`, `translation_domain` or
  `required` (it only renders the widget), `repeated` has no `label*` (it only
  renders its children) and `file` and `password` have no `data` (`file` inputs
  can't be prefilled and `password` inputs render empty by default).

## Configuration Pages (`configuration/`)

- Title is `<Thing> Configuration Reference (<BundleName>)`. Every page starts
  with the standard intro: the `config:dump-reference` / `debug:config`
  terminal block and the XML namespace/XSD note.
- Option order follows the existing file: `framework.rst` follows the config
  tree (add new options where they belong logically); `twig.rst` is
  alphabetical.
- `doctrine.rst` and `monolog.rst` intentionally delegate to the external
  bundle docs; don't expand them.
