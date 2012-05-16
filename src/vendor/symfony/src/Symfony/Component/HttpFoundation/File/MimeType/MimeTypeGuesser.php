<?php

namespace Symfony\Component\HttpFoundation\File\MimeType;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A singleton mime type guesser.
 *
 * By default, all mime type guessers provided by the framework are installed
 * (if available on the current OS/PHP setup). You can register custom
 * guessers by calling the register() method on the singleton instance.
 *
 * <code>
 * $guesser = MimeTypeGuesser::getInstance();
 * $guesser->register(new MyCustomMimeTypeGuesser());
 * </code>
 *
 * The last registered guesser is preferred over previously registered ones.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class MimeTypeGuesser implements MimeTypeGuesserInterface
{
    /**
     * The singleton instance
     * @var MimeTypeGuesser
     */
    static private $instance = null;

    /**
     * All registered MimeTypeGuesserInterface instances
     * @var array
     */
    protected $guessers = array();

    /**
     * Returns the singleton instance
     *
     * @return MimeTypeGuesser
     */
    static public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Registers all natively provided mime type guessers
     */
    private function __construct()
    {
        $this->register(new FileBinaryMimeTypeGuesser());

        if (ContentTypeMimeTypeGuesser::isSupported()) {
            $this->register(new ContentTypeMimeTypeGuesser());
        }

        if (FileinfoMimeTypeGuesser::isSupported()) {
            $this->register(new FileinfoMimeTypeGuesser());
        }
    }

    /**
     * Registers a new mime type guesser
     *
     * When guessing, this guesser is preferred over previously registered ones.
     *
     * @param MimeTypeGuesserInterface $guesser
     */
    public function register(MimeTypeGuesserInterface $guesser)
    {
        array_unshift($this->guessers, $guesser);
    }

    /**
     * Tries to guess the mime type of the given file
     *
     * The file is passed to each registered mime type guesser in reverse order
     * of their registration (last registered is queried first). Once a guesser
     * returns a value that is not NULL, this method terminates and returns the
     * value.
     *
     * @param  string $path   The path to the file
     * @return string         The mime type or NULL, if none could be guessed
     * @throws FileException  If the file does not exist
     */
    public function guess($path)
    {
        if (!is_file($path)) {
            throw new FileNotFoundException($path);
        }

        if (!is_readable($path)) {
            throw new AccessDeniedException($path);
        }

        $mimeType = null;

        foreach ($this->guessers as $guesser) {
            $mimeType = $guesser->guess($path);

            if (!is_null($mimeType)) {
                break;
            }
        }

        return $mimeType;
    }
}