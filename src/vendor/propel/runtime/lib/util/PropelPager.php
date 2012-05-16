<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 *  PropelPager
 *
 *  Example Usage:
 *
 *  require_once 'propel/util/PropelPager.php';
 *  require_once 'PEACH/Propel/Poem/poemPeer.php';
 *
 *  $c = new Criteria();
 *  $c->addDescendingOrderByColumn(poemPeer::SID);
 *
 *  // with join
 *  $pager = new PropelPager($c, 'poemPeer', 'doSelectJoinPoemUsers', 1, 50);
 *
 *  // without Join
 *
 *  $pager = new PropelPager($c, 'poemPeer', 'doSelect', 1, 50);
 *
 * Some template:
 *
 * <p>
 * Total Pages: <?=$pager->getTotalPages()?>  Total Records: <?=$pager->getTotalRecordCount()?>
 * </p>
 * <table>
 * <tr>
 * <td>
 * <?if ($link = $pager->getFirstPage):?>
 * <a href="somescript?page=<?=$link?>"><?=$link?></a>|
 * <?endif?>
 * </td>
 * <td>
 * <?if ($link = $pager->getPrev()):?>
 * <a href="somescript?page=<?=$link?>">Previous</a>|
 * <?endif?>
 * </td>
 * <td>
 * <?foreach ($pager->getPrevLinks() as $link):?>
 * <a href="somescript?page=<?=$link?>"><?=$link?></a>|
 * <?endforeach?>
 * </td>
 * <td><?=$pager->getPage()?></td>
 * <td>
 * <?foreach ($pager->getNextLinks() as $link):?>
 * | <a href="somescript?page=<?=$link?>"><?=$link?></a>
 * <?endforeach?>
 * </td>
 * <td>
 * <?if ($link = $pager->getNext()):?>
 * <a href="somescript?page=<?=$link?>">Last</a>|
 * <?endif?>
 * </td>
 * <td>
 * <?if ($link = $pager->getLastPage()):?>
 * <a href="somescript?page=<?=$link?>"><?=$link?></a>|
 * <?endif?>
 * </td>
 * </tr>
 * </table>
 * <table id="latestPoems">
 * <tr>
 * <th>Title</th>
 * <th>Auteur</th>
 * <th>Date</th>
 * <th>comments</th>
 * </tr>
 * <?foreach ($pager->getResult() as $poem):?>
 * <tr>
 * <td><?=$poem->getTitle()?></td>
 * <td><?=$poem->getPoemUsers()->getUname()?></td>
 * <td><?=$poem->getTime()?></td>
 * <td><?=$poem->getComments()?></td>
 * </tr>
 * <?endforeach?>
 * </table>
 *
 *
 * @author     Rob Halff <info@rhalff.com>
 * @author	   Niklas Närhinen <niklas@narhinen.net>
 * @version    $Revision: 1612 $
 * @copyright  Copyright (c) 2004 Rob Halff: LGPL - See LICENCE
 * @package    propel.runtime.util
 */
class PropelPager implements Countable, Iterator
{

	private $recordCount;
	private $pages;
	private $peerClass;
	private $peerSelectMethod;
	private $peerCountMethod;
	private $criteria;
	private $countCriteria;
	private $page;
	private $rs = null;
	
	//Iterator vars
	private $currentKey = 0;

	/** @var        int Start row (offset) */
	protected $start = 0;

	/** @var        int Max rows to return (0 means all) */
	protected $max = 0;

	/**
	 * Create a new Propel Pager.
	 * @param      Criteria $c
	 * @param      string $peerClass The name of the static Peer class.
	 * @param      string $peerSelectMethod The name of the static method for selecting content from the Peer class.
	 * @param      int $page The current page (1-based).
	 * @param      int $rowsPerPage The number of rows that should be displayed per page.
	 */
	public function __construct($c = null, $peerClass = null, $peerSelectMethod = null, $page = 1, $rowsPerPage = 25)
	{
		if (!isset($c)) {
			$c = new Criteria();
		}
		$this->setCriteria($c);
		$this->setPeerClass($peerClass);
		$this->setPeerSelectMethod($peerSelectMethod);
		$this->guessPeerCountMethod();
		$this->setPage($page);
		$this->setRowsPerPage($rowsPerPage);
	}

	/**
	 * Set the criteria for this pager.
	 * @param      Criteria $c
	 * @return     void
	 */
	public function setCriteria(Criteria $c)
	{
		$this->criteria = $c;
	}

	/**
	 * Return the Criteria object for this pager.
	 * @return     Criteria
	 */
	public function getCriteria()
	{
		return $this->criteria;
	}

	/**
	 * Set the Peer Classname
	 *
	 * @param      string $class
	 * @return     void
	 */
	public function setPeerClass($class)
	{
		$this->peerClass = $class;
	}

	/**
	 * Return the Peer Classname.
	 * @return     string
	 */
	public function getPeerClass()
	{
		return $this->peerClass;
	}

	/**
	 * Set the Peer select method.
	 * This exists for legacy support, please use setPeerSelectMethod().
	 * @param      string $method The name of the static method to call on the Peer class.
	 * @return     void
	 * @see        setPeerSelectMethod()
	 * @deprecated
	 */
	public function setPeerMethod($method)
	{
		$this->setPeerSelectMethod($method);
	}

	/**
	 * Return the Peer select method.
	 * This exists for legacy support, please use getPeerSelectMethod().
	 * @return     string
	 * @see        getPeerSelectMethod()
	 * @deprecated
	 */
	public function getPeerMethod()
	{
		return $this->getPeerSelectMethod();
	}

	/**
	 * Set the Peer select method.
	 *
	 * @param      string $method The name of the static method to call on the Peer class.
	 * @return     void
	 */
	public function setPeerSelectMethod($method)
	{
		$this->peerSelectMethod = $method;
	}

	/**
	 * Return the Peer select method.
	 * @return     string
	 */
	public function getPeerSelectMethod()
	{
		return $this->peerSelectMethod;
	}

	/**
	 * Sets the Count method.
	 * This is set based on the Peer method, for example if Peer method is doSelectJoin*() then the
	 * count method will be doCountJoin*().
	 * @param      string $method The name of the static method to call on the Peer class.
	 */
	public function setPeerCountMethod($method)
	{
		$this->peerCountMethod = $method;
	}

	/**
	 * Return the Peer count method.
	 */
	public function getPeerCountMethod()
	{
		return $this->peerCountMethod;
	}

	/**
	 * Guesses the Peer count method based on the select method.
	 */
	private function guessPeerCountMethod()
	{
		$selectMethod = $this->getPeerSelectMethod();
		if ($selectMethod == 'doSelect') {
			$countMethod = 'doCount';
		} elseif ( ($pos = stripos($selectMethod, 'doSelectJoin')) === 0) {
			$countMethod = 'doCount' . substr($selectMethod, strlen('doSelect'));
		} else {
			// we will fall back to doCount() if we don't understand the join
			// method; however, it probably won't be accurate.  Maybe triggering an error would
			// be appropriate ...
			$countMethod = 'doCount';
		}
		$this->setPeerCountMethod($countMethod);
	}

	/**
	 * Get the paged resultset
	 *
	 * @return     mixed $rs
	 */
	public function getResult()
	{
		if (!isset($this->rs)) {
			$this->doRs();
		}

		return $this->rs;
	}

	/**
	 * Get the paged resultset
	 *
	 * Main method which creates a paged result set based on the criteria
	 * and the requested peer select method.
	 *
	 */
	private function doRs()
	{
		$this->criteria->setOffset($this->start);
		$this->criteria->setLimit($this->max);
		$this->rs = call_user_func(array($this->getPeerClass(), $this->getPeerSelectMethod()), $this->criteria);
	}

	/**
	 * Get the first page
	 *
	 * For now I can only think of returning 1 always.
	 * It should probably return 0 if there are no pages
	 *
	 * @return     int 1
	 */
	public function getFirstPage()
	{
		return '1';
	}

	/**
	 * Convenience method to indicate whether current page is the first page.
	 *
	 * @return     boolean
	 */
	public function atFirstPage()
	{
		return $this->getPage() == $this->getFirstPage();
	}

	/**
	 * Get last page
	 *
	 * @return     int $lastPage
	 */
	public function getLastPage()
	{
		$totalPages = $this->getTotalPages();
		if ($totalPages == 0) {
			return 1;
		} else {
			return $totalPages;
		}
	}

	/**
	 * Convenience method to indicate whether current page is the last page.
	 *
	 * @return     boolean
	 */
	public function atLastPage()
	{
		return $this->getPage() == $this->getLastPage();
	}

	/**
	 * get total pages
	 *
	 * @return     int $this->pages
	 */
	public function getTotalPages() {
		if (!isset($this->pages)) {
			$recordCount = $this->getTotalRecordCount();
			if ($this->max > 0) {
					$this->pages = ceil($recordCount/$this->max);
			} else {
					$this->pages = 0;
			}
		}
		return $this->pages;
	}

	/**
	 * get an array of previous id's
	 *
	 * @param      int $range
	 * @return     array $links
	 */
	public function getPrevLinks($range = 5)
	{
		$total = $this->getTotalPages();
		$start = $this->getPage() - 1;
		$end = $this->getPage() - $range;
		$first =  $this->getFirstPage();
		$links = array();
		for ($i=$start; $i>$end; $i--) {
			if ($i < $first) {
					break;
			}
			$links[] = $i;
		}

		return array_reverse($links);
	}

	/**
	 * get an array of next id's
	 *
	 * @param      int $range
	 * @return     array $links
	 */
	public function getNextLinks($range = 5)
	{
		$total = $this->getTotalPages();
		$start = $this->getPage() + 1;
		$end = $this->getPage() + $range;
		$last =  $this->getLastPage();
		$links = array();
		for ($i=$start; $i<$end; $i++) {
			if ($i > $last) {
					break;
			}
			$links[] = $i;
		}

		return $links;
	}

	/**
	 * Returns whether last page is complete
	 *
	 * @return     bool Last page complete or not
	 */
	public function isLastPageComplete()
	{
		return !($this->getTotalRecordCount() % $this->max);
	}

	/**
	 * get previous id
	 *
	 * @return     mixed $prev
	 */
	public function getPrev() {
		if ($this->getPage() != $this->getFirstPage()) {
				$prev = $this->getPage() - 1;
		} else {
				$prev = false;
		}
		return $prev;
	}

	/**
	 * get next id
	 *
	 * @return     mixed $next
	 */
	public function getNext() {
		if ($this->getPage() != $this->getLastPage()) {
				$next = $this->getPage() + 1;
		} else {
				$next = false;
		}
		return $next;
	}

	/**
	 * Set the current page number (First page is 1).
	 * @param      int $page
	 * @return     void
	 */
	public function setPage($page)
	{
		$this->page = $page;
		// (re-)calculate start rec
		$this->calculateStart();
	}

	/**
	 * Get current page.
	 * @return     int
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 * Set the number of rows per page.
	 * @param      int $r
	 */
	public function setRowsPerPage($r)
	{
		$this->max = $r;
		// (re-)calculate start rec
		$this->calculateStart();
	}

	/**
	 * Get number of rows per page.
	 * @return     int
	 */
	public function getRowsPerPage()
	{
		return $this->max;
	}

	/**
	 * Calculate startrow / max rows based on current page and rows-per-page.
	 * @return     void
	 */
	private function calculateStart()
	{
		$this->start = ( ($this->page - 1) * $this->max );
	}

	/**
	 * Gets the total number of (un-LIMITed) records.
	 *
	 * This method will perform a query that executes un-LIMITed query.
	 *
	 * @return     int Total number of records - disregarding page, maxrows, etc.
	 */
	public function getTotalRecordCount()
	{

				if (!isset($this->rs)) {
					$this->doRs();
				}

				if (empty($this->recordCount)) {
						$this->countCriteria = clone $this->criteria;
						$this->countCriteria->setLimit(0);
						$this->countCriteria->setOffset(0);

						$this->recordCount = call_user_func(
								        array(
								                $this->getPeerClass(),
												$this->getPeerCountMethod()
								             ),
								        $this->countCriteria
								        );

				}

				return $this->recordCount;

	}

	/**
	 * Sets the start row or offset.
	 * @param      int $v
	 */
	public function setStart($v)
	{
		$this->start = $v;
	}

	/**
	 * Sets max rows (limit).
	 * @param      int $v
	 * @return     void
	 */
	public function setMax($v)
	{
		$this->max = $v;
	}
	
	/**
	 * Returns the count of the current page's records
	 * @return 	int
	 */
	public function count()
	{
		return count($this->getResult());
	}
	
	/**
	 * Returns the current element of the iterator
	 * @return mixed
	 */
	public function current()
	{
		if (!isset($this->rs)) {
			$this->doRs();
		}
		return $this->rs[$this->currentKey];
	}
	
	/**
	 * Returns the current key of the iterator
	 * @return int
	 */
	public function key()
	{
		return $this->currentKey;
	}
	
	/**
	 * Advances the iterator to the next element
	 * @return void
	 */
	public function next()
	{
		$this->currentKey++;
	}
	
	/**
	 * Resets the iterator to the first element
	 * @return void
	 */
	public function rewind()
	{
		$this->currentKey = 0;
	}
	
	/**
	 * Checks if the current key exists in the container
	 * @return boolean
	 */
	public function valid()
	{
		if (!isset($this->rs)) {
			$this->doRs();
		}
		return in_array($this->currentKey, array_keys($this->rs));
	}

}
