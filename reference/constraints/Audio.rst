Audio
=====

Validates that a file is a valid audio file that meets certain
constraints (duration, bitrate, sample rate, number of channels, etc.).
It extends the :doc:`File </reference/constraints/File>` constraint and
adds audio-specific validation options.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Audio`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\AudioValidator`
==========  ===================================================================

.. versionadded:: 8.2

    The Audio constraint was introduced in Symfony 8.2.

Basic Usage
-----------

This constraint is most commonly used on a property that stores an audio
file as a :class:`Symfony\\Component\\HttpFoundation\\File\\File`
object. For example, suppose you're creating a podcast platform and want
to validate uploaded episodes::

    // src/Entity/PodcastEpisode.php
    namespace App\Entity;

    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\Validator\Constraints as Assert;

    class PodcastEpisode
    {
        #[Assert\Audio(
            maxSize: '200M',
            maxDuration: 7200,
            minBitrate: 64000,
            allowedSampleRates: [44100, 48000],
            maxChannels: 2,
        )]
        private File $audioFile;
    }

The constraint reads the actual characteristics of the file with
``ffprobe``, so it doesn't rely on the file extension or on its media
type alone. When none of the audio-specific options is set, the
constraint only performs the checks of the ``File`` constraint and
doesn't run ``ffprobe``.

.. warning::

    The audio-specific options require the ``ffprobe`` binary to be
    installed and accessible on your system, as well as the
    :doc:`Process component </components/process>`. You can install
    ``ffprobe`` as part of the `FFmpeg package`_.

Options
-------

``allowedCodecs``
~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Defines which `audio codecs`_ are allowed, using the codec names
reported by ``ffprobe`` (e.g. ``mp3``, ``aac``, ``opus``, ``flac``,
``pcm_s16le``). The comparison is case-insensitive. If the audio uses a
codec not in this list, validation fails. By default, all codecs are
allowed::

    // src/Entity/PodcastEpisode.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class PodcastEpisode
    {
        #[Assert\Audio(
            allowedCodecs: ['mp3', 'aac'],
        )]
        private $audioFile;
    }

``allowedContainers``
~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Defines which `audio container formats`_ are allowed, using the format
names reported by ``ffprobe`` (e.g. ``mp3``, ``ogg``, ``wav``, ``flac``,
``mp4``). The comparison is case-insensitive. When ``ffprobe`` reports
several names for a format (e.g. ``mov,mp4,m4a`` for an M4A file), the
file is valid if any of them is allowed. By default, all containers are
allowed::

    // src/Entity/PodcastEpisode.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class PodcastEpisode
    {
        #[Assert\Audio(
            allowedContainers: ['mp3', 'ogg'],
        )]
        private $audioFile;
    }

``allowedSampleRates``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

If set, the sample rate of the audio file, in hertz, must be one of the
given integer values (e.g. ``[44100, 48000]``).

``maxBitrate``
~~~~~~~~~~~~~~

**type**: ``integer``

If set, the bitrate of the audio file must be less than or equal to this
value in bits per second. It must be greater than ``0``.

``maxChannels``
~~~~~~~~~~~~~~~

**type**: ``integer``

If set, the number of channels of the audio file must be less than or
equal to this value (e.g. ``1`` to only allow mono files). It must be
greater than ``0``.

``maxDuration``
~~~~~~~~~~~~~~~

**type**: ``integer`` | ``float``

If set, the duration of the audio file must be less than or equal to
this value in seconds. It must be greater than ``0``.

``mimeTypes``
~~~~~~~~~~~~~

**type**: ``array`` or ``string`` **default**: ``audio/*``

See the
:ref:`File mimeTypes option <reference-constraints-file-mime-types>` for
a description of this option.

``minBitrate``
~~~~~~~~~~~~~~

**type**: ``integer``

If set, the bitrate of the audio file must be greater than or equal to
this value in bits per second. It can't be negative.

``minChannels``
~~~~~~~~~~~~~~~

**type**: ``integer``

If set, the number of channels of the audio file must be greater than or
equal to this value (e.g. ``2`` to only allow stereo files). It can't be
negative.

``minDuration``
~~~~~~~~~~~~~~~

**type**: ``integer`` | ``float``

If set, the duration of the audio file must be greater than or equal to
this value in seconds. It can't be negative.

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

Messages
--------

``bitrateNotDetectedMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The bitrate of the audio could not be detected.``

The message displayed if ``ffprobe`` cannot detect the bitrate of the
audio and the ``minBitrate`` or ``maxBitrate`` option is set.

``channelsNotDetectedMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The number of audio channels could not be detected.``

The message displayed if ``ffprobe`` cannot detect the number of
channels of the audio and the ``minChannels`` or ``maxChannels`` option
is set.

``corruptedMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio file is corrupted.``

The message displayed if the audio file is corrupted and cannot be read
by ``ffprobe``.

``durationNotDetectedMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The duration of the audio could not be detected.``

The message displayed if ``ffprobe`` cannot detect the duration of the
audio and the ``minDuration`` or ``maxDuration`` option is set.

``maxBitrateMessage``
~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio bitrate is too high ({{ bitrate }} bps). Allowed maximum bitrate is {{ max_bitrate }} bps.``

The message displayed if the bitrate of the audio exceeds
``maxBitrate``.

``maxChannelsMessage``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio has too many channels ({{ channels }}). Maximum amount expected is {{ max_channels }}.``

The message displayed if the number of channels of the audio exceeds
``maxChannels``.

``maxDurationMessage``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio is too long ({{ duration }} seconds). Allowed maximum duration is {{ max_duration }} seconds.``

The message displayed if the duration of the audio exceeds
``maxDuration``.

``mimeTypesMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This file is not a valid audio file.``

The message displayed if the media type of the file is not a valid media
type per the ``mimeTypes`` option. When you change the ``mimeTypes``
option to a list without ``audio/*``, the default message becomes the
one of the ``File`` constraint.

``minBitrateMessage``
~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio bitrate is too low ({{ bitrate }} bps). Minimum bitrate expected is {{ min_bitrate }} bps.``

The message displayed if the bitrate of the audio is less than
``minBitrate``.

``minChannelsMessage``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio has too few channels ({{ channels }}). Minimum amount expected is {{ min_channels }}.``

The message displayed if the number of channels of the audio is less
than ``minChannels``.

``minDurationMessage``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio is too short ({{ duration }} seconds). Minimum duration expected is {{ min_duration }} seconds.``

The message displayed if the duration of the audio is less than
``minDuration``.

``multipleAudioStreamsMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio contains multiple streams. Only one stream is allowed.``

The message displayed if the file contains more than one audio stream.

``noAudioStreamMessage``
~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file does not contain any audio stream.``

The message displayed if the file doesn't contain any audio stream (e.g.
a video file without a soundtrack, when ``mimeTypes`` allows video
files).

``sampleRateNotDetectedMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The sample rate of the audio could not be detected.``

The message displayed if ``ffprobe`` cannot detect the sample rate of
the audio and the ``allowedSampleRates`` option is set.

``unsupportedCodecMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``Unsupported audio codec "{{ codec }}".``

The message displayed if the audio uses a codec that is not in the
``allowedCodecs`` list.

``unsupportedContainerMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``Unsupported audio container "{{ container }}".``

The message displayed if the audio uses a container format that is not
in the ``allowedContainers`` list.

``unsupportedSampleRateMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``Unsupported audio sample rate ({{ sample_rate }} Hz).``

The message displayed if the sample rate of the audio is not in the
``allowedSampleRates`` list.

.. _`FFmpeg package`: https://ffmpeg.org/
.. _`audio codecs`: https://en.wikipedia.org/wiki/Comparison_of_audio_coding_formats
.. _`audio container formats`: https://en.wikipedia.org/wiki/Audio_file_format
