Audio
=====

Validates that a file is a valid audio file that meets certain constraints
(duration, bitrate, sample rate, number of channels, etc.). It extends the
:doc:`File </reference/constraints/File>` constraint, so you can also use all
its options (e.g. ``maxSize``).

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Audio`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\AudioValidator`
==========  ===================================================================

.. versionadded:: 8.2

    The ``Audio`` constraint was introduced in Symfony 8.2.

Basic Usage
-----------

This constraint is most commonly used on a property that stores an audio file
as a :class:`Symfony\\Component\\HttpFoundation\\File\\File` object. For
example, suppose you're creating a podcast platform and want to validate
uploaded episodes:

.. configuration-block::

    .. code-block:: php-attributes

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

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\PodcastEpisode:
            properties:
                audioFile:
                    - Audio:
                        maxSize: 200M
                        maxDuration: 7200
                        minBitrate: 64000
                        allowedSampleRates: [44100, 48000]
                        maxChannels: 2

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\PodcastEpisode">
                <property name="audioFile">
                    <constraint name="Audio">
                        <option name="maxSize">200M</option>
                        <option name="maxDuration">7200</option>
                        <option name="minBitrate">64000</option>
                        <option name="allowedSampleRates">
                            <value>44100</value>
                            <value>48000</value>
                        </option>
                        <option name="maxChannels">2</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/PodcastEpisode.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class PodcastEpisode
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('audioFile', new Assert\Audio(
                    maxSize: '200M',
                    maxDuration: 7200,
                    minBitrate: 64000,
                    allowedSampleRates: [44100, 48000],
                    maxChannels: 2,
                ));
            }
        }

The constraint reads the actual characteristics of the file with ``ffprobe``, so
it doesn't rely only on the file extension or its media type. When you don't set
any of the audio-specific options, the constraint only performs the checks of
the ``File`` constraint and doesn't run ``ffprobe``.

.. warning::

    The audio-specific options require the ``ffprobe`` binary, which is part of
    the `FFmpeg package`_, and the
    :doc:`Process component </components/process>`.

Options
-------

``allowedCodecs``
~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Defines which `audio codecs`_ are allowed, using the codec names reported by
``ffprobe`` (e.g. ``mp3``, ``aac``, ``opus``, ``flac``, ``pcm_s16le``). The
comparison is case-insensitive. An empty list allows all codecs. This example
only allows MP3 and AAC audio::

    // src/Entity/PodcastEpisode.php
    namespace App\Entity;

    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\Validator\Constraints as Assert;

    class PodcastEpisode
    {
        #[Assert\Audio(
            allowedCodecs: ['mp3', 'aac'],
        )]
        private File $audioFile;
    }

``allowedContainers``
~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Defines which `audio container formats`_ are allowed, using the format names
reported by ``ffprobe`` (e.g. ``mp3``, ``ogg``, ``wav``, ``flac``, ``mp4``). The
comparison is case-insensitive. An empty list allows all containers.

``ffprobe`` can report several names for a format (e.g.
``mov,mp4,m4a,3gp,3g2,mj2`` for an M4A file). In that case, the file is valid if
any of those names is allowed. This example only allows MP3 and Ogg files::

    // src/Entity/PodcastEpisode.php
    namespace App\Entity;

    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\Validator\Constraints as Assert;

    class PodcastEpisode
    {
        #[Assert\Audio(
            allowedContainers: ['mp3', 'ogg'],
        )]
        private File $audioFile;
    }

``allowedSampleRates``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

If set, the sample rate of the audio file must be one of these values in hertz
(e.g. ``[44100, 48000]``).

.. include:: /reference/constraints/_groups-option.rst.inc

``maxBitrate``
~~~~~~~~~~~~~~

**type**: ``integer``

If set, the bitrate of the audio file must be less than or equal to this value
in bits per second. It must be greater than ``0``.

``maxChannels``
~~~~~~~~~~~~~~~

**type**: ``integer``

If set, the number of channels of the audio file must be less than or equal to
this value (e.g. ``1`` to only allow mono files). It must be greater than ``0``.

``maxDuration``
~~~~~~~~~~~~~~~

**type**: ``integer`` | ``float``

If set, the duration of the audio file must be less than or equal to this value
in seconds. It must be greater than ``0``.

``mimeTypes``
~~~~~~~~~~~~~

**type**: ``array`` or ``string`` **default**: ``audio/*``

See the :ref:`File mimeTypes option <reference-constraints-file-mime-types>` for
a description of this option.

``minBitrate``
~~~~~~~~~~~~~~

**type**: ``integer``

If set, the bitrate of the audio file must be greater than or equal to this
value in bits per second. It can't be negative.

``minChannels``
~~~~~~~~~~~~~~~

**type**: ``integer``

If set, the number of channels of the audio file must be greater than or equal
to this value (e.g. ``2`` to reject mono files). It can't be negative.

``minDuration``
~~~~~~~~~~~~~~~

**type**: ``integer`` | ``float``

If set, the duration of the audio file must be greater than or equal to this
value in seconds. It can't be negative.

.. include:: /reference/constraints/_payload-option.rst.inc

Messages
--------

``bitrateNotDetectedMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The bitrate of the audio could not be detected.``

The message displayed if ``ffprobe`` can't detect the bitrate of the audio and
the `minBitrate`_ or `maxBitrate`_ option is set.

``channelsNotDetectedMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The number of audio channels could not be detected.``

The message displayed if ``ffprobe`` can't detect the number of channels of the
audio and the `minChannels`_ or `maxChannels`_ option is set.

``corruptedMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio file is corrupted.``

The message displayed if the audio file is corrupted and ``ffprobe`` can't read
it.

``durationNotDetectedMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The duration of the audio could not be detected.``

The message displayed if ``ffprobe`` can't detect the duration of the audio and
the `minDuration`_ or `maxDuration`_ option is set.

``maxBitrateMessage``
~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio bitrate is too high ({{ bitrate }} bps). Allowed maximum bitrate is {{ max_bitrate }} bps.``

The message displayed if the bitrate of the audio exceeds `maxBitrate`_.

You can use the following parameters in this message:

=======================  ======================================================
Parameter                Description
=======================  ======================================================
``{{ bitrate }}``        The current bitrate of the audio in bits per second
``{{ max_bitrate }}``    The maximum allowed bitrate in bits per second
=======================  ======================================================

``maxChannelsMessage``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio has too many channels ({{ channels }}). Maximum amount expected is {{ max_channels }}.``

The message displayed if the number of channels of the audio exceeds
`maxChannels`_.

You can use the following parameters in this message:

=======================  ======================================================
Parameter                Description
=======================  ======================================================
``{{ channels }}``       The current number of channels of the audio
``{{ max_channels }}``   The maximum allowed number of channels
=======================  ======================================================

``maxDurationMessage``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio is too long ({{ duration }} seconds). Allowed maximum duration is {{ max_duration }} seconds.``

The message displayed if the duration of the audio exceeds `maxDuration`_.

You can use the following parameters in this message:

=======================  ======================================================
Parameter                Description
=======================  ======================================================
``{{ duration }}``       The current duration of the audio in seconds
``{{ max_duration }}``   The maximum allowed duration in seconds
=======================  ======================================================

``mimeTypesMessage``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This file is not a valid audio file.``

The message displayed if the media type of the file is not a valid media type
per the `mimeTypes`_ option. If you set that option to a list without
``audio/*``, the default message becomes the one of the ``File`` constraint.

``minBitrateMessage``
~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio bitrate is too low ({{ bitrate }} bps). Minimum bitrate expected is {{ min_bitrate }} bps.``

The message displayed if the bitrate of the audio is less than `minBitrate`_.

You can use the following parameters in this message:

=======================  ======================================================
Parameter                Description
=======================  ======================================================
``{{ bitrate }}``        The current bitrate of the audio in bits per second
``{{ min_bitrate }}``    The minimum allowed bitrate in bits per second
=======================  ======================================================

``minChannelsMessage``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio has too few channels ({{ channels }}). Minimum amount expected is {{ min_channels }}.``

The message displayed if the number of channels of the audio is less than
`minChannels`_.

You can use the following parameters in this message:

=======================  ======================================================
Parameter                Description
=======================  ======================================================
``{{ channels }}``       The current number of channels of the audio
``{{ min_channels }}``   The minimum allowed number of channels
=======================  ======================================================

``minDurationMessage``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio is too short ({{ duration }} seconds). Minimum duration expected is {{ min_duration }} seconds.``

The message displayed if the duration of the audio is less than `minDuration`_.

You can use the following parameters in this message:

=======================  ======================================================
Parameter                Description
=======================  ======================================================
``{{ duration }}``       The current duration of the audio in seconds
``{{ min_duration }}``   The minimum allowed duration in seconds
=======================  ======================================================

``multipleAudioStreamsMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The audio contains multiple streams. Only one stream is allowed.``

The message displayed if the file contains more than one audio stream.

``noAudioStreamMessage``
~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The file does not contain any audio stream.``

The message displayed if the file doesn't contain any audio stream (e.g. a
video file without a soundtrack, when `mimeTypes`_ allows video files).

``sampleRateNotDetectedMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The sample rate of the audio could not be detected.``

The message displayed if ``ffprobe`` can't detect the sample rate of the audio
and the `allowedSampleRates`_ option is set.

``unsupportedCodecMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``Unsupported audio codec "{{ codec }}".``

The message displayed if the audio uses a codec that is not in the
`allowedCodecs`_ list.

You can use the following parameters in this message:

=================  ============================================================
Parameter          Description
=================  ============================================================
``{{ codec }}``    The codec of the audio
=================  ============================================================

``unsupportedContainerMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``Unsupported audio container "{{ container }}".``

The message displayed if the audio uses a container format that is not in the
`allowedContainers`_ list.

You can use the following parameters in this message:

=====================  ========================================================
Parameter              Description
=====================  ========================================================
``{{ container }}``    The container format of the audio
=====================  ========================================================

``unsupportedSampleRateMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``Unsupported audio sample rate ({{ sample_rate }} Hz).``

The message displayed if the sample rate of the audio is not in the
`allowedSampleRates`_ list.

You can use the following parameters in this message:

=======================  ======================================================
Parameter                Description
=======================  ======================================================
``{{ sample_rate }}``    The sample rate of the audio in hertz
=======================  ======================================================

.. _`FFmpeg package`: https://ffmpeg.org/
.. _`audio codecs`: https://en.wikipedia.org/wiki/Comparison_of_audio_coding_formats
.. _`audio container formats`: https://en.wikipedia.org/wiki/Audio_file_format
