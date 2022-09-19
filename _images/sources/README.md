How to Create Symfony Images
============================

Creating Diagrams
-----------------

* Use [Dia][1] as the diagramming application;
* Use [PT Sans Narrow][2] as the only font in all diagrams (if possible, use
  only the "normal" weight for all contents);
* Use 36pt as the base font size;
* Use 0.10 cm width for lines and shape borders;
* Use the following color palette:
  * Text, lines and shape borders: black (#000000)
  * Shape backgrounds:
    * Grays: dark (#4d4d4d), medium (#b3b3b3), light (#f2f2f2)
    * Blue: #b2d4eb
    * Red: #ecbec0
    * Green: #b2dec7
    * Orange: #fddfbb

In case of doubt, check the existing diagrams or ask to the
[Symfony Documentation Team][3].

### Saving and Exporting the Diagram

* Save the original diagram in `*.dia` format in `_images/sources/<folder-name>`;
* Export the diagram to SVG format and save it in `_images/<folder-name>`.

Important: choose "Cairo Scalable Vector Graphics (.svg)" format instead of
plain " Scalable Vector Graphics (.svg)" because the former is the only format
that transforms text into vector shapes (resulting file is larger in size, but
it's truly portable because text is displayed the same even if you don't have
some fonts installed).

### Including the Diagram in the Symfony Docs

Use the following snippet to embed the diagram in the docs:

```
.. raw:: html

    <object data="../_images/<folder-name>/<diagram-file-name>.svg" type="image/svg+xml"></object>
```

### Reasoning

* Dia was chosen because it's one of the few applications which are free, open
  source and compatible with Linux, macOS and Windows.
* Font, colors and line widths were chosen to be similar to the diagrams used
  in the best tech books.

### Troubleshooting

* On some macOS systems, Dia cannot be executed as a regular application and
  you must run the following console command instead:
  `export DISPLAY=:0 && /Applications/Dia.app/Contents/Resources/bin/dia`

Creating Console Screenshots
----------------------------

* Use [Asciinema][4] to record the console session locally:

  ```
  $ asciinema rec -c bash recording.cast
  ```
* Use `$ ` as the prompt in recordings. E.g. if you're using Bash, add the
  following lines to your ``.bashrc``:

  ```
  if [ "$ASCIINEMA_REC" = "1" ]; then
    PS1="\e[37m$ \e[0m"
  fi
  ```
* Save the generated asciicast in `_images/sources/<folder-name>`.

### Rendering the Recording

Rendering the recording can be a difficult task. The [documentation team][3]
is always ready to help you with this task (e.g. you can open a PR with
only the asciicast file).

* Use [agg][5] to generated a GIF file from the recording;
* Install the [JetBrains Mono][6] font;
* Use the ``_images/sources/ascii-render.sh`` file to call agg:

  ```
  AGG_PATH=/path/to/agg ./_images/sources/ascii-render.sh recording.cast --cols 45 --rows 20
  ```

  This utility configures a predefined theme;
* Always configure `--cols`` (width) and ``--rows`` (height), try to use as
  low as possible numbers. Do not exceed 70 columns;
* Save the generated GIF file in `_images/<folder-name>`.

[1]: http://dia-installer.de/
[2]: https://fonts.google.com/specimen/PT+Sans+Narrow
[3]: https://symfony.com/doc/current/contributing/code/core_team.html
[4]: https://github.com/asciinema/asciinema
[5]: https://github.com/asciinema/agg
[6]: https://www.jetbrains.com/lp/mono/
