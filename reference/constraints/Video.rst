Video
=====

Validates that a file is a valid video that meets certain constraints (width, height,
aspect ratio, etc.). It extends the :doc:`File </reference/constraints/File>` constraint
and adds video-specific validation options.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Video`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\VideoValidator`
==========  ===================================================================

Basic Usage
-----------

This constraint is most commonly used on a property that stores a video file as a
:class:`Symfony\\Component\\HttpFoundation\\File\\File` object. For example, suppose
you're creating a video platform and want to validate uploaded video files::

    // src/Entity/VideoContent.php
    namespace App\Entity;

    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\Validator\Constraints as Assert;

    class VideoContent
    {
        #[Assert\Video(
            maxWidth: 1920,
            maxHeight: 1080,
            maxSize: '100M',
            mimeTypes: ['video/mp4', 'video/webm'],
        )]
        private File $videoFile;
    }

.. warning::

    This constraint requires the ``ffprobe`` binary to be installed and accessible
    on your system. The constraint uses ``ffprobe`` to extract video metadata.
    You can install it as part of the `FFmpeg package`_.

Options
-------

``allowedCodecs``
~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``['h264', 'hevc', 'h265', 'vp9', 'av1', 'mpeg4', 'mpeg2video']``

Defines which `video codecs`_ are allowed. If the video uses a codec not in this
list, validation will fail::

    // src/Entity/VideoContent.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class VideoContent
    {
        #[Assert\Video(
            allowedCodecs: ['h264', 'hevc'],
        )]
        private $videoFile;
    }

``allowedContainers``
~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``['mp4', 'mov', 'mkv', 'webm', 'avi']``

Defines which `video container formats`_ are allowed::

    // src/Entity/VideoContent.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class VideoContent
    {
        #[Assert\Video(
            allowedContainers: ['mp4', 'webm'],
        )]
        private $videoFile;
    }

``allowLandscape``
~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If set to ``false``, the video cannot be landscape oriented (i.e., the width
cannot be greater than the height).

``allowPortrait``
~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If set to ``false``, the video cannot be portrait oriented (i.e., the width
cannot be less than the height).

``allowSquare``
~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If set to ``false``, the video cannot be a square (i.e., the width and height
cannot be equal).

``maxHeight``
~~~~~~~~~~~~~

**type**: ``integer``

If set, the height of the video file must be less than or equal to this value
in pixels.

``maxPixels``
~~~~~~~~~~~~~

**type**: ``integer`` | ``float``

If set, the total number of pixels (``width * height``) of the video file must be less
than or equal to this value.

``maxRatio``
~~~~~~~~~~~~

**type**: ``integer`` | ``float``

If set, the aspect ratio (``width / height``) of the video file must be less
than or equal to this value. For example, a square video has a ratio of 1,
a 16:9 video has a ratio of 1.78, and a 4:3 video has a ratio of 1.33.

``maxWidth``
~~~~~~~~~~~~

**type**: ``integer``

If set, the width of the video file must be less than or equal to this value
in pixels.

``mimeTypes``
~~~~~~~~~~~~~

**type**: ``array`` or ``string`` **default**: ``video/*``

See the :ref:`File mimeTypes option <reference-constraints-file-mime-types>` for a
description of this option and a list of common video MIME types.

``minHeight``
~~~~~~~~~~~~~

**type**: ``integer``

If set, the height of the video file must be greater than or equal to this
value in pixels.

``minPixels``
~~~~~~~~~~~~~

**type**: ``integer`` | ``float``

If set, the total number of pixels (``width * height``) of the video file must be greater
than or equal to this value.

``minRatio``
~~~~~~~~~~~~

**type**: ``integer`` | ``float``

If set, the aspect ratio (``width / height``) of the video file must be greater
than or equal to this value.

``minWidth``
~~~~~~~~~~~~

**type**: ``integer``

If set, the width of the video file must be greater than or equal to this value
in pixels.

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

Messages
--------

``allowLandscapeMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video is landscape oriented ({{ width }}x{{ height }}px). Landscape oriented videos are not allowed.``

The message displayed if the video is landscape oriented and landscape videos
are not allowed.

``allowPortraitMessage``
~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video is portrait oriented ({{ width }}x{{ height }}px). Portrait oriented videos are not allowed.``

The message displayed if the video is portrait oriented and portrait videos are not allowed.

``allowSquareMessage``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video is square ({{ width }}x{{ height }}px). Square videos are not allowed.``

The message displayed if the video is square and square videos are not allowed.

``corruptedMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video file is corrupted.``

The message displayed if the video file is corrupted and cannot be read by ``ffprobe``.

``maxHeightMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video height is too big ({{ height }}px). Allowed maximum height is {{ max_height }}px.``

The message displayed if the height of the video exceeds ``maxHeight``.

``maxPixelsMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video has too many pixels ({{ pixels }} pixels). Maximum amount expected is {{ max_pixels }} pixels.``

The message displayed if the total number of pixels of the video exceeds ``maxPixels``.

``maxRatioMessage``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video ratio is too big ({{ ratio }}). Allowed maximum ratio is {{ max_ratio }}.``

The message displayed if the aspect ratio of the video exceeds ``maxRatio``.

``maxWidthMessage``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video width is too big ({{ width }}px). Allowed maximum width is {{ max_width }}px.``

The message displayed if the width of the video exceeds ``maxWidth``.

``mimeTypesMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This file is not a valid video.``

The message displayed if the media type of the video is not a valid media type
per the ``mimeTypes`` option.

``minHeightMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video height is too small ({{ height }}px). Minimum height expected is {{ min_height }}px.``

The message displayed if the height of the video is less than ``minHeight``.

``minPixelsMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video has too few pixels ({{ pixels }} pixels). Minimum amount expected is {{ min_pixels }} pixels.``

The message displayed if the total number of pixels of the video is less than ``minPixels``.

``minRatioMessage``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video ratio is too small ({{ ratio }}). Minimum ratio expected is {{ min_ratio }}.``

The message displayed if the aspect ratio of the video is less than ``minRatio``.

``minWidthMessage``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video width is too small ({{ width }}px). Minimum width expected is {{ min_width }}px.``

The message displayed if the width of the video is less than ``minWidth``.

``multipleVideoStreamsMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The video contains multiple streams. Only one stream is allowed.``

The message displayed if the video file contains multiple video streams.

``sizeNotDetectedMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The size of the video could not be detected.``

The message displayed if ``ffprobe`` cannot detect the dimensions of the video.

``unsupportedCodecMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``Unsupported video codec "{{ codec }}".``

The message displayed if the video uses a codec that is not in the ``allowedCodecs`` list.

``unsupportedContainerMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``Unsupported video container "{{ container }}".``

The message displayed if the video uses a container format that is not in the
``allowedContainers`` list.

.. _`FFmpeg package`: https://ffmpeg.org/
.. _`video codecs`: https://en.wikipedia.org/wiki/Comparison_of_video_codecs
.. _`video container formats`: https://en.wikipedia.org/wiki/Comparison_of_video_container_formats
