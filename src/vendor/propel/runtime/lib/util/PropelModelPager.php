<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Implements a pager based on a ModelCriteria
 * The code from this class heavily borrows from symfony's sfPager class
 * 
 * @author		 Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author		 François Zaninotto
 * @version		 $Revision: 1665 $
 * @package		 propel.runtime.query
 */
class PropelModelPager implements IteratorAggregate, Countable
{
	protected
		$query					 = null,
		$page						 = 1,
		$maxPerPage			 = 10,
		$lastPage				 = 1,
		$nbResults			 = 0,
		$objects				 = null,
		$parameters			 = array(),
		$currentMaxLink	 = 1,
		$parameterHolder = null,
		$maxRecordLimit = false,
		$results         = null,
		$resultsCounter  = 0;

	public function __construct(Criteria $query, $maxPerPage = 10)
	{
		$this->setQuery($query);
		$this->setMaxPerPage($maxPerPage);
	}
	
	public function setQuery(Criteria $query)
	{
		$this->query = $query;
	}
	
	public function getQuery()
	{
		return $this->query;
	}

	public function init()
	{
		$hasMaxRecordLimit = ($this->getMaxRecordLimit() !== false);
		$maxRecordLimit = $this->getMaxRecordLimit();

		$qForCount = clone $this->getQuery();
		$count = $qForCount
			->offset(0)
			->limit(0)
			->count();

		$this->setNbResults($hasMaxRecordLimit ? min($count, $maxRecordLimit) : $count);

		$q = $this->getQuery()
			->offset(0)
			->limit(0);

		if (($this->getPage() == 0 || $this->getMaxPerPage() == 0)) {
			$this->setLastPage(0);
		} else {
			$this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

			$offset = ($this->getPage() - 1) * $this->getMaxPerPage();
			$q->offset($offset);

			if ($hasMaxRecordLimit) {
				$maxRecordLimit = $maxRecordLimit - $offset;
				if ($maxRecordLimit > $this->getMaxPerPage()) {
					$q->limit($this->getMaxPerPage());
				} else {
					$q->limit($maxRecordLimit);
				}
			} else {
				$q->limit($this->getMaxPerPage());
			}
		}
	}

	/**
	 * Get the collection of results in the page
	 *
	 * @return PropelObjectCollection A collection of results
	 */
	public function getResults()
	{
		if (null === $this->results) {
			$this->results = $this->getQuery()
				->setFormatter(ModelCriteria::FORMAT_OBJECT)
				->find();
		}
		return $this->results;
	}

	public function getCurrentMaxLink()
	{
		return $this->currentMaxLink;
	}

	public function getMaxRecordLimit()
	{
		return $this->maxRecordLimit;
	}

	public function setMaxRecordLimit($limit)
	{
		$this->maxRecordLimit = $limit;
	}

	public function getLinks($nb_links = 5)
	{
		$links = array();
		$tmp	 = $this->page - floor($nb_links / 2);
		$check = $this->lastPage - $nb_links + 1;
		$limit = ($check > 0) ? $check : 1;
		$begin = ($tmp > 0) ? (($tmp > $limit) ? $limit : $tmp) : 1;

		$i = (int) $begin;
		while (($i < $begin + $nb_links) && ($i <= $this->lastPage)) {
			$links[] = $i++;
		}

		$this->currentMaxLink = count($links) ? $links[count($links) - 1] : 1;

		return $links;
	}
	
	/**
	 * Test whether the number of results exceeds the max number of results per page
	 * 
	 * @return     boolean true if the pager displays only a subset of the results 
	 */
	public function haveToPaginate()
	{
		return (($this->getMaxPerPage() != 0) && ($this->getNbResults() > $this->getMaxPerPage()));
	}

	/**
	 * Get the index of the first element in the page
	 * Returns 1 on the first page, $maxPerPage +1 on the second page, etc
	 * 
	 * @return     int
	 */
	public function getFirstIndex()
	{
		if ($this->page == 0) {
			return 1;
		} else {
			return ($this->page - 1) * $this->maxPerPage + 1;
		}
	}

	/**
	 * Get the index of the last element in the page
	 * Always less than or eaqual to $maxPerPage
	 * 
	 * @return     int
	 */
	public function getLastIndex()
	{
		if ($this->page == 0) {
			return $this->nbResults;
		} else {
			if (($this->page * $this->maxPerPage) >= $this->nbResults) {
				return $this->nbResults;
			} else {
				return ($this->page * $this->maxPerPage);
			}
		}
	}

	/**
	 * Get the total number of results of the query
	 * This can be greater than $maxPerPage
	 * 
	 * @return     int
	 */
	public function getNbResults()
	{
		return $this->nbResults;
	}

	/**
	 * Set the total number of results of the query
	 * 
	 * @param     int $nb
	 */
	protected function setNbResults($nb)
	{
		$this->nbResults = $nb;
	}

	/**
	 * Check whether the current page is the first page
	 * 
	 * @return     boolean true if the current page is the first page
	 */
	public function isFirstPage()
	{
		return $this->getPage() == $this->getFirstPage();
	}

	/**
	 * Get the number of the first page
	 * 
	 * @return     int Always 1
	 */
	public function getFirstPage()
	{
		return 1;
	}

	/**
	 * Check whether the current page is the last page
	 * 
	 * @return     boolean true if the current page is the last page
	 */
	public function isLastPage()
	{
		return $this->getPage() == $this->getLastPage();
	}

	/**
	 * Get the number of the last page
	 * 
	 * @return     int
	 */
	public function getLastPage()
	{
		return $this->lastPage;
	}

	/**
	 * Set the number of the first page
	 * 
	 * @param     int $page
	 */
	protected function setLastPage($page)
	{
		$this->lastPage = $page;
		if ($this->getPage() > $page) {
			$this->setPage($page);
		}
	}

	/**
	 * Get the number of the current page
	 * 
	 * @return     int
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 * Set the number of the current page
	 * 
	 * @param     int $page
	 */
	public function setPage($page)
	{
		$this->page = intval($page);
		if ($this->page <= 0) {
			// set first page, which depends on a maximum set
			$this->page = $this->getMaxPerPage() ? 1 : 0;
		}
	}
	
	/**
	 * Get the number of the next page
	 * 
	 * @return     int
	 */
	public function getNextPage()
	{
		return min($this->getPage() + 1, $this->getLastPage());
	}

	/**
	 * Get the number of the previous page
	 * 
	 * @return     int
	 */
	public function getPreviousPage()
	{
		return max($this->getPage() - 1, $this->getFirstPage());
	}

	/**
	 * Get the maximum number results per page
	 * 
	 * @return     int
	 */
	public function getMaxPerPage()
	{
		return $this->maxPerPage;
	}

	/**
	 * Set the maximum number results per page
	 * 
	 * @param     int $max
	 */
	public function setMaxPerPage($max)
	{
		if ($max > 0) {
			$this->maxPerPage = $max;
			if ($this->page == 0) {
				$this->page = 1;
			}
		} else if ($max == 0) {
			$this->maxPerPage = 0;
			$this->page = 0;
		} else {
			$this->maxPerPage = 1;
			if ($this->page == 0) {
				$this->page = 1;
			}
		}
	}
	
	public function getIterator()
	{
		return $this->getResults()->getIterator();
	}

	/**
	 * Returns the total number of results.
	 *
	 * @see        Countable
	 * @return     int
	 */
	public function count()
	{
		return $this->getNbResults();
	}

}
