<?php

namespace Symfony\Component\DomCrawler;

use Symfony\Component\CssSelector\Parser as CssParser;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Crawler eases navigation of a list of \DOMNode objects.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class Crawler extends \SplObjectStorage
{
    protected $uri;
    protected $host;
    protected $path;

    /**
     * Constructor.
     *
     * @param mixed  $node A Node to use as the base for the crawling
     * @param string $uri  The base URI to use for absolute links or form actions
     */
    public function __construct($node = null, $uri = null)
    {
        $this->uri = $uri;
        list($this->host, $this->path) = $this->parseUri($this->uri);

        $this->add($node);
    }

    /**
     * Removes all the nodes.
     */
    public function clear()
    {
        $this->removeAll($this);
    }

    /**
     * Adds a node to the current list of nodes.
     *
     * This method uses the appropriate specialized add*() method based
     * on the type of the argument.
     *
     * @param null|\DOMNodeList|array|\DOMNode $node A node
     */
    public function add($node)
    {
        if ($node instanceof \DOMNodeList) {
            $this->addNodeList($node);
        } elseif (is_array($node)) {
            $this->addNodes($node);
        } elseif (is_string($node)) {
            $this->addContent($node);
        } elseif (is_object($node)) {
            $this->addNode($node);
        }
    }

    public function addContent($content, $type = null)
    {
        if (empty($type)) {
            $type = 'text/html';
        }

        // DOM only for HTML/XML content
        if (!preg_match('/(x|ht)ml/i', $type, $matches)) {
            return null;
        }

        $charset = 'ISO-8859-1';
        if (false !== $pos = strpos($type, 'charset=')) {
            $charset = substr($type, $pos + 8);
        }

        if ('x' === $matches[1]) {
            $this->addXmlContent($content, $charset);
        } else {
            $this->addHtmlContent($content, $charset);
        }
    }

    /**
     * Adds an HTML content to the list of nodes.
     *
     * @param string $content The HTML content
     * @param string $charset The charset
     */
    public function addHtmlContent($content, $charset = 'UTF-8')
    {
        $dom = new \DOMDocument('1.0', $charset);
        $dom->validateOnParse = true;

        @$dom->loadHTML($content);
        $this->addDocument($dom);
    }

    /**
     * Adds an XML content to the list of nodes.
     *
     * @param string $content The XML content
     * @param string $charset The charset
     */
    public function addXmlContent($content, $charset = 'UTF-8')
    {
        $dom = new \DOMDocument('1.0', $charset);
        $dom->validateOnParse = true;

        // remove the default namespace to make XPath expressions simpler
        @$dom->loadXML(str_replace('xmlns', 'ns', $content));
        $this->addDocument($dom);
    }

    /**
     * Adds a \DOMDocument to the list of nodes.
     *
     * @param \DOMDocument $dom A \DOMDocument instance
     */
    public function addDocument(\DOMDocument $dom)
    {
        if ($dom->documentElement) {
            $this->addNode($dom->documentElement);
        }
    }

    /**
     * Adds a \DOMNodeList to the list of nodes.
     *
     * @param \DOMNodeList $nodes A \DOMNodeList instance
     */
    public function addNodeList(\DOMNodeList $nodes)
    {
        foreach ($nodes as $node) {
            $this->addNode($node);
        }
    }

    /**
     * Adds an array of \DOMNode instances to the list of nodes.
     *
     * @param array $nodes An array of \DOMNode instances
     */
    public function addNodes(array $nodes)
    {
        foreach ($nodes as $node) {
            $this->add($node);
        }
    }

    /**
     * Adds a \DOMNode instance to the list of nodes.
     *
     * @param \DOMNode $node A \DOMNode instance
     */
    public function addNode(\DOMNode $node)
    {
        if ($node instanceof \DOMDocument) {
            $this->attach($node->documentElement);
        } else {
            $this->attach($node);
        }
    }

    /**
     * Returns a node given its position in the node list.
     *
     * @param integer $position The position
     *
     * @return A new instance of the Crawler with the selected node, or an empty Crawler if it does not exist.
     */
    public function eq($position)
    {
        foreach ($this as $i => $node) {
            if ($i == $position) {
                return new static($node, $this->uri);
            }
        }

        return new static(null, $this->uri);
    }

    /**
     * Calls an anonymous function on each node of the list.
     *
     * The anonymous function receives the position and the node as arguments.
     *
     * Example:
     *
     *     $crawler->filter('h1')->each(function ($i, $node)
     *     {
     *       return $node->nodeValue;
     *     });
     *
     * @param \Closure $closure An anonymous function
     *
     * @return array An array of values returned by the anonymous function
     */
    public function each(\Closure $closure)
    {
        $data = array();
        foreach ($this as $i => $node) {
            $data[] = $closure($node, $i);
        }

        return $data;
    }

    /**
     * Reduces the list of nodes by calling an anonymous function.
     *
     * To remove a node from the list, the anonymous function must return false.
     *
     * @param \Closure $closure An anonymous function
     *
     * @return Crawler A Crawler instance with the selected nodes.
     */
    public function reduce(\Closure $closure)
    {
        $nodes = array();
        foreach ($this as $i => $node) {
            if (false !== $closure($node, $i)) {
                $nodes[] = $node;
            }
        }

        return new static($nodes, $this->uri);
    }

    /**
     * Returns the first node of the current selection
     *
     * @return Crawler A Crawler instance with the first selected node
     */
    public function first()
    {
        return $this->eq(0);
    }

    /**
     * Returns the last node of the current selection
     *
     * @return Crawler A Crawler instance with the last selected node
     */
    public function last()
    {
        return $this->eq(count($this) - 1);
    }

    /**
     * Returns the siblings nodes of the current selection
     *
     * @return Crawler A Crawler instance with the sibling nodes
     *
     * @throws \InvalidArgumentException When current node is empty
     */
    public function siblings()
    {
        if (!count($this)) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        return new static($this->sibling($this->getNode(0)->parentNode->firstChild), $this->uri);
    }

    /**
     * Returns the next siblings nodes of the current selection
     *
     * @return Crawler A Crawler instance with the next sibling nodes
     *
     * @throws \InvalidArgumentException When current node is empty
     */
    public function nextAll()
    {
        if (!count($this)) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        return new static($this->sibling($this->getNode(0)), $this->uri);
    }

    /**
     * Returns the previous sibling nodes of the current selection
     *
     * @return Crawler A Crawler instance with the previous sibling nodes
     */
    public function previousAll()
    {
        if (!count($this)) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        return new static($this->sibling($this->getNode(0), 'previousSibling'), $this->uri);
    }

    /**
     * Returns the parents nodes of the current selection
     *
     * @return Crawler A Crawler instance with the parents nodes of the current selection
     *
     * @throws \InvalidArgumentException When current node is empty
     */
    public function parents()
    {
        if (!count($this)) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);
        $nodes = array();

        while ($node = $node->parentNode) {
            if (1 === $node->nodeType && '_root' !== $node->nodeName) {
                $nodes[] = $node;
            }
        }

        return new static($nodes, $this->uri);
    }

    /**
     * Returns the children nodes of the current selection
     *
     * @return Crawler A Crawler instance with the children nodes
     *
     * @throws \InvalidArgumentException When current node is empty
     */
    public function children()
    {
        if (!count($this)) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        return new static($this->sibling($this->getNode(0)->firstChild), $this->uri);
    }

    /**
     * Returns the attribute value of the first node of the list.
     *
     * @param string $attribute The attribute name
     *
     * @return string The attribute value
     *
     * @throws \InvalidArgumentException When current node is empty
     */
    public function attr($attribute)
    {
        if (!count($this)) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        return $this->getNode(0)->getAttribute($attribute);
    }

    /**
     * Returns the node value of the first node of the list.
     *
     * @return string The node value
     *
     * @throws \InvalidArgumentException When current node is empty
     */
    public function text()
    {
        if (!count($this)) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        return $this->getNode(0)->nodeValue;
    }

    /**
     * Extracts information from the list of nodes.
     *
     * You can extract attributes or/and the node value (_text).
     *
     * Example:
     *
     * $crawler->filter('h1 a')->extract(array('_text', 'href'));
     *
     * @param array $attributes An array of attributes
     *
     * @return array An array of extracted values
     */
    public function extract($attributes)
    {
        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }

        $data = array();
        foreach ($this as $node) {
            $elements = array();
            foreach ($attributes as $attribute) {
                if ('_text' === $attribute) {
                    $elements[] = $node->nodeValue;
                } else {
                    $elements[] = $node->getAttribute($attribute);
                }
            }

            $data[] = count($attributes) > 1 ? $elements : $elements[0];
        }

        return $data;
    }

    /**
     * Filters the list of nodes with an XPath expression.
     *
     * @param string $xpath An XPath expression
     *
     * @return Crawler A new instance of Crawler with the filtered list of nodes
     */
    public function filterXPath($xpath)
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $root = $document->appendChild($document->createElement('_root'));
        foreach ($this as $node) {
            $root->appendChild($document->importNode($node, true));
        }

        $domxpath = new \DOMXPath($document);

        return new static($domxpath->query($xpath), $this->uri);
    }

    /**
     * Filters the list of nodes with a CSS selector.
     *
     * This method only works if you have installed the CssSelector Symfony Component.
     *
     * @param string $selector A CSS selector
     *
     * @return Crawler A new instance of Crawler with the filtered list of nodes
     *
     * @throws \RuntimeException if the CssSelector Component is not available
     */
    public function filter($selector)
    {
        if (!class_exists('Symfony\\Component\\CssSelector\\Parser')) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Unable to filter with a CSS selector as the Symfony CssSelector is not installed (you can use filterXPath instead).');
            // @codeCoverageIgnoreEnd
        }

        return $this->filterXPath(CssParser::cssToXpath($selector));
    }

    /**
     * Selects links by name or alt value for clickable images.
     *
     * @param  string $value The link text
     *
     * @return Crawler A new instance of Crawler with the filtered list of nodes
     */
    public function selectLink($value)
    {
        $xpath  = sprintf('//a[contains(concat(\' \', normalize-space(string(.)), \' \'), %s)] ', static::xpathLiteral(' '.$value.' ')).
                            sprintf('| //a/img[contains(concat(\' \', normalize-space(string(@alt)), \' \'), %s)]/ancestor::a', static::xpathLiteral(' '.$value.' '));

        return $this->filterXPath($xpath);
    }

    /**
     * Selects a button by name or alt value for images.
     *
     * @param  string $value The button text
     *
     * @return Crawler A new instance of Crawler with the filtered list of nodes
     */
    public function selectButton($value)
    {
        $xpath = sprintf('//input[((@type="submit" or @type="button") and contains(concat(\' \', normalize-space(string(@value)), \' \'), %s)) ', static::xpathLiteral(' '.$value.' ')).
                         sprintf('or (@type="image" and contains(concat(\' \', normalize-space(string(@alt)), \' \'), %s)) or @id="%s" or @name="%s"] ', static::xpathLiteral(' '.$value.' '), $value, $value).
                         sprintf('| //button[contains(concat(\' \', normalize-space(string(.)), \' \'), %s) or @id="%s" or @name="%s"]', static::xpathLiteral(' '.$value.' '), $value, $value);

        return $this->filterXPath($xpath);
    }

    /**
     * Returns a Link object for the first node in the list.
     *
     * @param  string $method The method for the link (get by default)
     *
     * @return Link   A Link instance
     *
     * @throws \InvalidArgumentException If the current node list is empty
     */
    public function link($method = 'get')
    {
        if (!count($this)) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);

        return new Link($node, $method, $this->host, $this->path);
    }

    /**
     * Returns an array of Link objects for the nodes in the list.
     *
     * @return array An array of Link instances
     */
    public function links()
    {
        $links = array();
        foreach ($this as $node) {
            $links[] = new Link($node, 'get', $this->host, $this->path);
        }

        return $links;
    }

    /**
     * Returns a Form object for the first node in the list.
     *
     * @param  array  $arguments An array of values for the form fields
     * @param  string $method    The method for the form
     *
     * @return Form   A Form instance
     *
     * @throws \InvalidArgumentException If the current node list is empty
     */
    public function form(array $values = null, $method = null)
    {
        if (!count($this)) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $form = new Form($this->getNode(0), $method, $this->host, $this->path);

        if (null !== $values) {
            $form->setValues($values);
        }

        return $form;
    }

    protected function getNode($position)
    {
        foreach ($this as $i => $node) {
            if ($i == $position) {
                return $node;
            }
        // @codeCoverageIgnoreStart
        }

        return null;
        // @codeCoverageIgnoreEnd
    }

    protected function parseUri($uri)
    {
        if ('http' !== substr($uri, 0, 4)) {
            return array(null, '/');
        }

        $path = parse_url($uri, PHP_URL_PATH);

        return array(preg_replace('#^(.*?//[^/]+)\/.*$#', '$1', $uri), $path);
    }

    protected function sibling($node, $siblingDir = 'nextSibling')
    {
        $nodes = array();

        do {
            if ($node !== $this->getNode(0) && $node->nodeType === 1) {
                $nodes[] = $node;
            }
        } while($node = $node->$siblingDir);

        return $nodes;
    }

    static public function xpathLiteral($s)
    {
        if (false === strpos($s, "'")) {
            return sprintf("'%s'", $s);
        }

        if (false === strpos($s, '"')) {
            return sprintf('"%s"', $s);
        }

        $string = $s;
        $parts = array();
        while (true) {
            if (false !== $pos = strpos($string, "'")) {
                $parts[] = sprintf("'%s'", substr($string, 0, $pos));
                $parts[] = "\"'\"";
                $string = substr($string, $pos + 1);
            } else {
                $parts[] = "'$string'";
                break;
            }
        }

        return sprintf("concat(%s)", implode($parts, ', '));
    }
}
