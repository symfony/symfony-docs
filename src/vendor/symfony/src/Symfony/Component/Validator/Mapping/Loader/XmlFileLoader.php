<?php

namespace Symfony\Component\Validator\Mapping\Loader;

use Symfony\Component\Validator\Exception\MappingException;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class XmlFileLoader extends FileLoader
{
    /**
     * An array of SimpleXMLElement instances
     * @val array
     */
    protected $classes = null;

    /**
     * {@inheritDoc}
     */
    public function loadClassMetadata(ClassMetadata $metadata)
    {
        if (is_null($this->classes)) {
            $this->classes = array();
            $xml = $this->parseFile($this->file);

            foreach ($xml->class as $class) {
                $this->classes[(string)$class['name']] = $class;
            }
        }

        if (isset($this->classes[$metadata->getClassName()])) {
            $xml = $this->classes[$metadata->getClassName()];

            foreach ($this->parseConstraints($xml->constraint) as $constraint) {
                $metadata->addConstraint($constraint);
            }

            foreach ($xml->property as $property) {
                foreach ($this->parseConstraints($property->constraint) as $constraint) {
                    $metadata->addPropertyConstraint((string)$property['name'], $constraint);
                }
            }

            foreach ($xml->getter as $getter) {
                foreach ($this->parseConstraints($getter->constraint) as $constraint) {
                    $metadata->addGetterConstraint((string)$getter['property'], $constraint);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Parses a collection of "constraint" XML nodes
     *
     * @param  SimpleXMLElement $nodes   The XML nodes
     * @return array                     The Constraint instances
     */
    protected function parseConstraints(\SimpleXMLElement $nodes)
    {
        $constraints = array();

        foreach ($nodes as $node) {
            if (count($node) > 0) {
                if (count($node->value) > 0) {
                    $options = $this->parseValues($node->value);
                } else if (count($node->constraint) > 0) {
                    $options = $this->parseConstraints($node->constraint);
                } else if (count($node->option) > 0) {
                    $options = $this->parseOptions($node->option);
                } else {
                    $options = array();
                }
            } else if (strlen((string)$node) > 0) {
                $options = trim($node);
            } else {
                $options = null;
            }

            $constraints[] = $this->newConstraint($node['name'], $options);
        }

        return $constraints;
    }

    /**
     * Parses a collection of "value" XML nodes
     *
     * @param  SimpleXMLElement $nodes   The XML nodes
     * @return array                     The values
     */
    protected function parseValues(\SimpleXMLElement $nodes)
    {
        $values = array();

        foreach ($nodes as $node) {
            if (count($node) > 0) {
                if (count($node->value) > 0) {
                    $value = $this->parseValues($node->value);
                } else if (count($node->constraint) > 0) {
                    $value = $this->parseConstraints($node->constraint);
                } else {
                    $value = array();
                }
            } else {
                $value = trim($node);
            }

            if (isset($node['key'])) {
                $values[(string)$node['key']] = $value;
            } else {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * Parses a collection of "option" XML nodes
     *
     * @param  SimpleXMLElement $nodes   The XML nodes
     * @return array                     The options
     */
    protected function parseOptions(\SimpleXMLElement $nodes)
    {
        $options = array();

        foreach ($nodes as $node) {
            if (count($node) > 0) {
                if (count($node->value) > 0) {
                    $value = $this->parseValues($node->value);
                } else if (count($node->constraint) > 0) {
                    $value = $this->parseConstraints($node->constraint);
                } else {
                    $value = array();
                }
            } else {
                $value = trim($node);
            }

            $options[(string)$node['name']] = $value;
        }

        return $options;
    }

    /**
     * @param  string $file
     * @return SimpleXMLElement
     */
    protected function parseFile($file)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!$dom->load($file, LIBXML_COMPACT)) {
            throw new MappingException(implode("\n", $this->getXmlErrors()));
        }
        if (!$dom->schemaValidate(__DIR__.'/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd')) {
            throw new MappingException(implode("\n", $this->getXmlErrors()));
        }
        $dom->validateOnParse = true;
        $dom->normalizeDocument();
        libxml_use_internal_errors(false);

        return simplexml_import_dom($dom);
    }

    protected function getXmlErrors()
    {
        $errors = array();
        foreach (libxml_get_errors() as $error) {
            $errors[] = sprintf('[%s %s] %s (in %s - line %d, column %d)',
                LIBXML_ERR_WARNING == $error->level ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ? $error->file : 'n/a',
                $error->line,
                $error->column
            );
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        return $errors;
    }
}