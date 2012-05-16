<?php

namespace Symfony\Component\Templating\Helper;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * SlotsHelper manages template slots.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class SlotsHelper extends Helper
{
    protected $slots = array();
    protected $openSlots = array();

    /**
     * Starts a new slot.
     *
     * This method starts an output buffer that will be
     * closed when the stop() method is called.
     *
     * @param string $name  The slot name
     *
     * @throws \InvalidArgumentException if a slot with the same name is already started
     */
    public function start($name)
    {
        if (in_array($name, $this->openSlots)) {
            throw new \InvalidArgumentException(sprintf('A slot named "%s" is already started.', $name));
        }

        $this->openSlots[] = $name;
        $this->slots[$name] = '';

        ob_start();
        ob_implicit_flush(0);
    }

    /**
     * Stops a slot.
     *
     * @throws \LogicException if no slot has been started
     */
    public function stop()
    {
        $content = ob_get_clean();

        if (!$this->openSlots) {
            throw new \LogicException('No slot started.');
        }

        $name = array_pop($this->openSlots);

        $this->slots[$name] = $content;
    }

    /**
     * Returns true if the slot exists.
     *
     * @param string $name The slot name
     */
    public function has($name)
    {
        return isset($this->slots[$name]);
    }

    /**
     * Gets the slot value.
     *
     * @param string $name    The slot name
     * @param string $default The default slot content
     *
     * @return string The slot content
     */
    public function get($name, $default = false)
    {
        return isset($this->slots[$name]) ? $this->slots[$name] : $default;
    }

    /**
     * Sets a slot value.
     *
     * @param string $name    The slot name
     * @param string $content The slot content
     */
    public function set($name, $content)
    {
        $this->slots[$name] = $content;
    }

    /**
     * Outputs a slot.
     *
     * @param string $name    The slot name
     * @param string $default The default slot content
     *
     * @return Boolean true if the slot is defined or if a default content has been provided, false otherwise
     */
    public function output($name, $default = false)
    {
        if (!isset($this->slots[$name])) {
            if (false !== $default) {
                echo $default;

                return true;
            }

            return false;
        }

        echo $this->slots[$name];

        return true;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'slots';
    }
}
