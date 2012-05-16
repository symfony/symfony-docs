<?php

namespace Symfony\Component\DomCrawler;

use Symfony\Component\DomCrawler\FormField;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Form represents an HTML form.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class Form implements \ArrayAccess
{
    protected $document;
    protected $button;
    protected $node;
    protected $fields;
    protected $method;
    protected $host;
    protected $path;

    /**
     * Constructor.
     *
     * @param \DOMNode $node   A \DOMNode instance
     * @param string   $method The method to use for the link (if null, it defaults to the method defined by the form)
     * @param string   $host   The base URI to use for absolute links (like http://localhost)
     * @param string   $path   The base path for relative links (/ by default)
     *
     * @throws \LogicException if the node is not a button inside a form tag
     */
    public function __construct(\DOMNode $node, $method = null, $host = null, $path = '/')
    {
        $this->button = $node;
        if ('button' == $node->nodeName || ('input' == $node->nodeName && in_array($node->getAttribute('type'), array('submit', 'button', 'image')))) {
            do {
                // use the ancestor form element
                if (null === $node = $node->parentNode) {
                    throw new \LogicException('The selected node does not have a form ancestor.');
                }
            } while ('form' != $node->nodeName);
        } else {
            throw new \LogicException(sprintf('Unable to submit on a "%s" tag.', $node->nodeName));
        }
        $this->node = $node;
        $this->method = $method;
        $this->host = $host;
        $this->path = empty($path) ? '/' : $path;

        $this->initialize();
    }

    /**
     * Gets the form node associated with this form.
     *
     * @return \DOMNode A \DOMNode instance
     */
    public function getFormNode()
    {
        return $this->node;
    }

    /**
     * Sets the value of the fields.
     *
     * @param array $values An array of field values
     */
    public function setValues(array $values)
    {
        foreach ($values as $name => $value) {
            $this[$name] = $value;
        }

        return $this;
    }

    /**
     * Gets the field values.
     *
     * The returned array does not include file fields (@see getFiles).
     *
     * @return array An array of field values.
     */
    public function getValues()
    {
        $values = array();
        foreach ($this->fields as $name => $field) {
            if (!$field instanceof Field\FileFormField && $field->hasValue()) {
                $values[$name] = $field->getValue();
            }
        }

        return $values;
    }

    /**
     * Gets the file field values.
     *
     * @return array An array of file field values.
     */
    public function getFiles()
    {
        if (!in_array($this->getMethod(), array('post', 'put', 'delete'))) {
            return array();
        }

        $files = array();
        foreach ($this->fields as $name => $field) {
            if ($field instanceof Field\FileFormField) {
                $files[$name] = $field->getValue();
            }
        }

        return $files;
    }

    /**
     * Gets the field values as PHP.
     *
     * This method converts fields with th array notation
     * (like foo[bar] to arrays) like PHP does.
     *
     * @return array An array of field values.
     */
    public function getPhpValues()
    {
        $qs = http_build_query($this->getValues());
        parse_str($qs, $values);

        return $values;
    }

    /**
     * Gets the file field values as PHP.
     *
     * This method converts fields with th array notation
     * (like foo[bar] to arrays) like PHP does.
     *
     * @return array An array of field values.
     */
    public function getPhpFiles()
    {
        $qs = http_build_query($this->getFiles());
        parse_str($qs, $values);

        return $values;
    }

    /**
     * Gets the URI of the form.
     *
     * The returned URI is not the same as the form "action" attribute.
     * This method merges the value if the method is GET to mimics
     * browser behavior.
     *
     * @param Boolean $absolute Whether to return an absolute URI or not (this only works if a base URI has been provided)
     *
     * @return string The URI
     */
    public function getUri($absolute = true)
    {
        $uri = $this->node->getAttribute('action');
        $urlHaveScheme = 'http' === substr($uri, 0, 4);

        if (!in_array($this->getMethod(), array('post', 'put', 'delete')) && $queryString = http_build_query($this->getValues(), null, '&')) {
            $sep = false === strpos($uri, '?') ? '?' : '&';
            $uri .= $sep.$queryString;
        }

        $path = $this->path;
        if ('?' !== substr($uri, 0, 1) && '/' !== substr($path, -1)) {
            $path = substr($path, 0, strrpos($path, '/') + 1);
        }

        if ($uri && '/' !== $uri[0] && !$urlHaveScheme) {
            $uri = $path.$uri;
        }

        if ($absolute && null !== $this->host && !$urlHaveScheme) {
            return $this->host.$uri;
        }

        return $uri;
    }

    /**
     * Gets the form method.
     *
     * If no method is defined in the form, GET is returned.
     *
     * @return string The method
     */
    public function getMethod()
    {
        if (null !== $this->method) {
            return $this->method;
        }

        return $this->node->getAttribute('method') ? strtolower($this->node->getAttribute('method')) : 'get';
    }

    /**
     * Returns true if the named field exists.
     *
     * @param string $name The field name
     *
     * @return Boolean true if the field exists, false otherwise
     */
    public function hasField($name)
    {
        return isset($this->fields[$name]);
    }

    /**
     * Gets a named field.
     *
     * @param string $name The field name
     *
     * @return FormField The field instance
     *
     * @throws \InvalidArgumentException When field is not present in this form
     */
    public function getField($name)
    {
        if (!$this->hasField($name)) {
            throw new \InvalidArgumentException(sprintf('The form has no "%s" field', $name));
        }

        return $this->fields[$name];
    }

    /**
     * Sets a named field.
     *
     * @param string $name The field name
     *
     * @return FormField The field instance
     */
    public function setField(Field\FormField $field)
    {
        $this->fields[$field->getName()] = $field;
    }

    /**
     * Gets all fields.
     *
     * @return array An array of fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    protected function initialize()
    {
        $this->fields = array();

        $document = new \DOMDocument('1.0', 'UTF-8');
        $node = $document->importNode($this->node, true);
        $button = $document->importNode($this->button, true);
        $root = $document->appendChild($document->createElement('_root'));
        $root->appendChild($node);
        $root->appendChild($button);
        $xpath = new \DOMXPath($document);

        foreach ($xpath->query('descendant::input | descendant::textarea | descendant::select', $root) as $node) {
            if ($node->hasAttribute('disabled') || !$node->hasAttribute('name')) {
                continue;
            }

            $nodeName = $node->nodeName;

            if ($node === $button) {
                $this->setField(new Field\InputFormField($node));
            } elseif ('select' == $nodeName || 'input' == $nodeName && 'checkbox' == $node->getAttribute('type')) {
                $this->setField(new Field\ChoiceFormField($node));
            } elseif ('input' == $nodeName && 'radio' == $node->getAttribute('type')) {
                if ($this->hasField($node->getAttribute('name'))) {
                    $this->getField($node->getAttribute('name'))->addChoice($node);
                } else {
                    $this->setField(new Field\ChoiceFormField($node));
                }
            } elseif ('input' == $nodeName && 'file' == $node->getAttribute('type')) {
                $this->setField(new Field\FileFormField($node));
            } elseif ('input' == $nodeName && !in_array($node->getAttribute('type'), array('submit', 'button', 'image'))) {
                $this->setField(new Field\InputFormField($node));
            } elseif ('textarea' == $nodeName) {
                $this->setField(new Field\TextareaFormField($node));
            }
        }
    }

    /**
     * Returns true if the named field exists.
     *
     * @param string $name The field name
     *
     * @return Boolean true if the field exists, false otherwise
     */
    public function offsetExists($name)
    {
        return $this->hasField($name);
    }

    /**
     * Gets the value of a field.
     *
     * @param string $name The field name
     *
     * @return FormField The associated Field instance
     *
     * @throws \InvalidArgumentException if the field does not exist
     */
    public function offsetGet($name)
    {
        if (!$this->hasField($name)) {
            throw new \InvalidArgumentException(sprintf('The form field "%s" does not exist', $name));
        }

        return $this->fields[$name];
    }

    /**
     * Sets the value of a field.
     *
     * @param string       $name  The field name
     * @param string|array $value The value of the field
     *
     * @throws \InvalidArgumentException if the field does not exist
     */
    public function offsetSet($name, $value)
    {
        if (!$this->hasField($name)) {
            throw new \InvalidArgumentException(sprintf('The form field "%s" does not exist', $name));
        }

        $this->fields[$name]->setValue($value);
    }

    /**
     * Unimplemented.
     *
     * @param string $name The field name
     */
    public function offsetUnset($name)
    {
        throw new \LogicException('The Form fields cannot be removed.');
    }
}
