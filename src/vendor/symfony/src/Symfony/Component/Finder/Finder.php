<?php

namespace Symfony\Component\Finder;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Finder allows to build rules to find files and directories.
 *
 * It is a thin wrapper around several specialized iterator classes.
 *
 * All rules may be invoked several times.
 *
 * All methods return the current Finder object to allow easy chaining:
 *
 * $finder = new Finder();
 * $finder = $finder->files()->name('*.php')->in(__DIR__);
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class Finder implements \IteratorAggregate
{
    protected $mode        = 0;
    protected $names       = array();
    protected $notNames    = array();
    protected $exclude     = array();
    protected $filters     = array();
    protected $depths      = array();
    protected $sizes       = array();
    protected $followLinks = false;
    protected $sort        = false;
    protected $ignoreVCS   = true;
    protected $dirs        = array();
    protected $dates       = array();

    /**
     * Restricts the matching to directories only.
     *
     * @return Finder The current Finder instance
     */
    public function directories()
    {
        $this->mode = Iterator\FileTypeFilterIterator::ONLY_DIRECTORIES;

        return $this;
    }

    /**
     * Restricts the matching to files only.
     *
     * @return Finder The current Finder instance
     */
    public function files()
    {
        $this->mode = Iterator\FileTypeFilterIterator::ONLY_FILES;

        return $this;
    }

    /**
     * Adds tests for the directory depth.
     *
     * Usage:
     *
     *   $finder->depth('> 1') // the Finder will start matching at level 1.
     *   $finder->depth('< 3') // the Finder will descend at most 3 levels of directories below the starting point.
     *
     * @param  int $level The depth level expression
     *
     * @return Finder The current Finder instance
     *
     * @see Symfony\Component\Finder\Iterator\DepthRangeFilterIterator
     * @see Symfony\Component\Finder\Comparator\NumberComparator
     */
    public function depth($level)
    {
        $this->depths[] = new Comparator\NumberComparator($level);

        return $this;
    }

    /**
     * Adds tests for file dates (last modified).
     *
     * The date must be something that strtotime() is able to parse:
     *
     *   $finder->date('since yesterday');
     *   $finder->date('until 2 days ago');
     *   $finder->date('> now - 2 hours');
     *   $finder->date('>= 2005-10-15');
     *
     * @param  string $date A date rage string
     *
     * @return Finder The current Finder instance
     *
     * @see strtotime
     * @see Symfony\Component\Finder\Iterator\DateRangeFilterIterator
     * @see Symfony\Component\Finder\Comparator\DateComparator
     */
    public function date($date)
    {
        $this->dates[] = new Comparator\DateComparator($date);

        return $this;
    }

    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     * $finder->name('*.php')
     * $finder->name('/\.php$/') // same as above
     * $finder->name('test.php')
     *
     * @param  string $pattern A pattern (a regexp, a glob, or a string)
     *
     * @return Finder The current Finder instance
     *
     * @see Symfony\Component\Finder\Iterator\FilenameFilterIterator
     */
    public function name($pattern)
    {
        $this->names[] = $pattern;

        return $this;
    }

    /**
     * Adds rules that files must not match.
     *
     * @param  string $pattern A pattern (a regexp, a glob, or a string)
     *
     * @return Finder The current Finder instance
     *
     * @see Symfony\Component\Finder\Iterator\FilenameFilterIterator
     */
    public function notName($pattern)
    {
        $this->notNames[] = $pattern;

        return $this;
    }

    /**
     * Adds tests for file sizes.
     *
     * $finder->size('> 10K');
     * $finder->size('<= 1Ki');
     * $finder->size(4);
     *
     * @param string $size A size range string
     *
     * @return Finder The current Finder instance
     *
     * @see Symfony\Component\Finder\Iterator\SizeRangeFilterIterator
     * @see Symfony\Component\Finder\Comparator\NumberComparator
     */
    public function size($size)
    {
        $this->sizes[] = new Comparator\NumberComparator($size);

        return $this;
    }

    /**
     * Excludes directories.
     *
     * @param  string $dir A directory to exclude
     *
     * @return Finder The current Finder instance
     *
     * @see Symfony\Component\Finder\Iterator\ExcludeDirectoryFilterIterator
     */
    public function exclude($dir)
    {
        $this->exclude[] = $dir;

        return $this;
    }

    /**
     * Forces the finder to ignore version control directories.
     *
     * @return Finder The current Finder instance
     *
     * @see Symfony\Component\Finder\Iterator\IgnoreVcsFilterIterator
     */
    public function ignoreVCS($ignoreVCS)
    {
        $this->ignoreVCS = (Boolean) $ignoreVCS;

        return $this;
    }

    /**
     * Sorts files and directories by an anonymous function.
     *
     * The anonymous function receives two \SplFileInfo instances to compare.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @param  Closure $closure An anonymous function
     *
     * @return Finder The current Finder instance
     *
     * @see Symfony\Component\Finder\Iterator\SortableIterator
     */
    public function sort(\Closure $closure)
    {
        $this->sort = $closure;

        return $this;
    }

    /**
     * Sorts files and directories by name.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return Finder The current Finder instance
     *
     * @see Symfony\Component\Finder\Iterator\SortableIterator
     */
    public function sortByName()
    {
        $this->sort = Iterator\SortableIterator::SORT_BY_NAME;

        return $this;
    }

    /**
     * Sorts files and directories by type (directories before files), then by name.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return Finder The current Finder instance
     *
     * @see Symfony\Component\Finder\Iterator\SortableIterator
     */
    public function sortByType()
    {
        $this->sort = Iterator\SortableIterator::SORT_BY_TYPE;

        return $this;
    }

    /**
     * Filters the iterator with an anonymous function.
     *
     * The anonymous function receives a \SplFileInfo and must return false
     * to remove files.
     *
     * @param  Closure $closure An anonymous function
     *
     * @return Finder The current Finder instance
     *
     * @see Symfony\Component\Finder\Iterator\CustomFilterIterator
     */
    public function filter(\Closure $closure)
    {
        $this->filters[] = $closure;

        return $this;
    }

    /**
     * Forces the following of symlinks.
     *
     * @return Finder The current Finder instance
     */
    public function followLinks()
    {
        $this->followLinks = true;

        return $this;
    }

    /**
     * Searches files and directories which match defined rules.
     *
     * @param  string|array $dirs A directory path or an array of directories
     *
     * @return Finder The current Finder instance
     *
     * @throws \InvalidArgumentException if one of the directory does not exist
     */
    public function in($dirs)
    {
        if (!is_array($dirs)) {
            $dirs = array($dirs);
        }

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                throw new \InvalidArgumentException(sprintf('The "%s" directory does not exist.', $dir));
            }
        }

        $this->dirs = array_merge($this->dirs, $dirs);

        return $this;
    }

    /**
     * Returns an Iterator for the current Finder configuration.
     *
     * This method implements the IteratorAggregate interface.
     *
     * @return \Iterator An iterator
     *
     * @throws \LogicException if the in() method has not been called
     */
    public function getIterator()
    {
        if (0 === count($this->dirs)) {
            throw new \LogicException('You must call the in() method before iterating over a Finder.');
        }

        if (1 === count($this->dirs)) {
            return $this->searchInDirectory($this->dirs[0]);
        }

        $iterator = new \AppendIterator();
        foreach ($this->dirs as $dir) {
            $iterator->append($this->searchInDirectory($dir));
        }

        return $iterator;
    }

    protected function searchInDirectory($dir)
    {
        $flags = \FilesystemIterator::SKIP_DOTS;

        if ($this->followLinks) {
            $flags |= \FilesystemIterator::FOLLOW_SYMLINKS;
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, $flags), \RecursiveIteratorIterator::SELF_FIRST);

        if ($this->depths) {
            $iterator = new Iterator\DepthRangeFilterIterator($iterator, $this->depths);
        }

        if ($this->mode) {
            $iterator = new Iterator\FileTypeFilterIterator($iterator, $this->mode);
        }

        if ($this->exclude) {
            $iterator = new Iterator\ExcludeDirectoryFilterIterator($iterator, $this->exclude);
        }

        if ($this->ignoreVCS) {
            $iterator = new Iterator\IgnoreVcsFilterIterator($iterator);
        }

        if ($this->names || $this->notNames) {
            $iterator = new Iterator\FilenameFilterIterator($iterator, $this->names, $this->notNames);
        }

        if ($this->sizes) {
            $iterator = new Iterator\SizeRangeFilterIterator($iterator, $this->sizes);
        }

        if ($this->dates) {
            $iterator = new Iterator\DateRangeFilterIterator($iterator, $this->dates);
        }

        if ($this->filters) {
            $iterator = new Iterator\CustomFilterIterator($iterator, $this->filters);
        }

        if ($this->sort) {
            $iterator = new Iterator\SortableIterator($iterator, $this->sort);
        }

        return $iterator;
    }
}
