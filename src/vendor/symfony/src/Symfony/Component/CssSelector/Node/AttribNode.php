<?php

namespace Symfony\Component\CssSelector\Node;

use Symfony\Component\CssSelector\XPathExpr;
use Symfony\Component\CssSelector\SyntaxError;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * AttribNode represents a "selector[namespace|attrib operator value]" node.
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class AttribNode implements NodeInterface
{
    protected $selector;
    protected $namespace;
    protected $attrib;
    protected $operator;
    protected $value;

    public function __construct($selector, $namespace, $attrib, $operator, $value)
    {
        $this->selector = $selector;
        $this->namespace = $namespace;
        $this->attrib = $attrib;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function __toString()
    {
        if ($this->operator == 'exists') {
            return sprintf('%s[%s[%s]]', __CLASS__, $this->selector, $this->formatAttrib());
        } else {
            return sprintf('%s[%s[%s %s %s]]', __CLASS__, $this->selector, $this->formatAttrib(), $this->operator, $this->value);
        }
    }

    /**
     * @throws SyntaxError When unknown operator is found
     */
    public function toXpath()
    {
        $path = $this->selector->toXpath();
        $attrib = $this->xpathAttrib();
        $value = $this->value;
        if ($this->operator == 'exists') {
            $path->addCondition($attrib);
        } elseif ($this->operator == '=') {
            $path->addCondition(sprintf('%s = %s', $attrib, XPathExpr::xpathLiteral($value)));
        } elseif ($this->operator == '!=') {
            // FIXME: this seems like a weird hack...
            if ($value) {
                $path->addCondition(sprintf('not(%s) or %s != %s', $attrib, $attrib, XPathExpr::xpathLiteral($value)));
            } else {
                $path->addCondition(sprintf('%s != %s', $attrib, XPathExpr::xpathLiteral($value)));
            }
            // path.addCondition('%s != %s' % (attrib, xpathLiteral(value)))
        } elseif ($this->operator == '~=') {
            $path->addCondition(sprintf("contains(concat(' ', normalize-space(%s), ' '), %s)", $attrib, XPathExpr::xpathLiteral(' '.$value.' ')));
        } elseif ($this->operator == '|=') {
            // Weird, but true...
            $path->addCondition(sprintf('%s = %s or starts-with(%s, %s)', $attrib, XPathExpr::xpathLiteral($value), $attrib, XPathExpr::xpathLiteral($value.'-')));
        } elseif ($this->operator == '^=') {
            $path->addCondition(sprintf('starts-with(%s, %s)', $attrib, XPathExpr::xpathLiteral($value)));
        } elseif ($this->operator == '$=') {
            // Oddly there is a starts-with in XPath 1.0, but not ends-with
            $path->addCondition(sprintf('substring(%s, string-length(%s)-%s) = %s', $attrib, $attrib, strlen($value) - 1, XPathExpr::xpathLiteral($value)));
        } elseif ($this->operator == '*=') {
            // FIXME: case sensitive?
            $path->addCondition(sprintf('contains(%s, %s)', $attrib, XPathExpr::xpathLiteral($value)));
        } else {
            throw new SyntaxError(sprintf('Unknown operator: %s', $this->operator));
        }

        return $path;
    }

    protected function xpathAttrib()
    {
        // FIXME: if attrib is *?
        if ($this->namespace == '*') {
            return '@'.$this->attrib;
        }

        return sprintf('@%s:%s', $this->namespace, $this->attrib);
    }

    protected function formatAttrib()
    {
        if ($this->namespace == '*') {
            return $this->attrib;
        }

        return sprintf('%s|%s', $this->namespace, $this->attrib);
    }
}
