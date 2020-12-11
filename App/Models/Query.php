<?php

namespace App\Models;

class Query extends \App\Models\Base {
	/** @var int */
	protected $idQuery;
	/** @var int */
	protected $idConnection;
	/** @var int */
	protected $idQueryType;
	/** @var string */
	protected $queryTypeName;
	/** @var int */
	protected $requestNumber;
	/** @var \DateTime */
	protected $executed;
	/** @var int */
	protected $sourceLineBegin;
	/** @var int */
	protected $sourceLineEnd;
	/** @var string|NULL */
	protected $queryText;
	/** @var int */
	protected $mark;

	/** @var string|NULL */
	private $_formattedQuery;
	/** @var string|NULL */
	private $_compressedQuery;
	
	/** @return int */
	public function GetIdQuery(){
		return $this->idQuery;
	}
	/**
	 * @param int $idQuery 
	 * @return \App\Models\Query
	 */
	public function SetIdQuery($idQuery){
		$this->idQuery = $idQuery;
		return $this;
	}
	/** @return int */
	public function GetIdConnection(){
		return $this->idConnection;
	}
	/**
	 * @param int $idConnection 
	 * @return \App\Models\Query
	 */
	public function SetIdConnection($idConnection){
		$this->idConnection = $idConnection;
		return $this;
	}
	/** @return int */
	public function GetIdQueryTypeName(){
		return $this->idQueryTypeName;
	}
	/**
	 * @param int $idQueryTypeName 
	 * @return \App\Models\Query
	 */
	public function SetIdQueryTypeName($idQueryTypeName){
		$this->idQueryTypeName = $idQueryTypeName;
		return $this;
	}
	/** @return string */
	public function GetQueryTypeName(){
		return $this->queryTypeName;
	}
	/** @return int */
	public function GetRequestNumber(){
		return $this->requestNumber;
	}
	/**
	 * @param int $requestNumber 
	 * @return \App\Models\Query
	 */
	public function SetRequestNumber($requestNumber){
		$this->requestNumber = $requestNumber;
		return $this;
	}
	/** @return \DateTime */
	public function GetExecuted(){
		return $this->executed;
	}
	/**
	 * @param \DateTime $executed 
	 * @return \App\Models\Query
	 */
	public function SetExecuted($executed){
		$this->executed = $executed;
		return $this;
	}
	/** @return int */
	public function GetSourceLineBegin(){
		return $this->sourceLineBegin;
	}
	/**
	 * @param int $sourceLineBegin 
	 * @return \App\Models\Query
	 */
	public function SetSourceLineBegin($sourceLineBegin){
		$this->sourceLineBegin = $sourceLineBegin;
		return $this;
	}
	/** @return int */
	public function GetSourceLineEnd(){
		return $this->sourceLineEnd;
	}
	/**
	 * @param int $sourceLineEnd 
	 * @return \App\Models\Query
	 */
	public function SetSourceLineEnd($sourceLineEnd){
		$this->sourceLineEnd = $sourceLineEnd;
		return $this;
	}
	/** @return string */
	public function GetQueryText(){
		return $this->queryText;
	}
	/**
	 * @param string $queryText 
	 * @return \App\Models\Query
	 */
	public function SetQueryText($queryText){
		$this->queryText = $queryText;
		return $this;
	}
	/** @return int */
	public function GetMark(){
		return $this->mark;
	}
	/**
	 * @param int $mark 
	 * @return \App\Models\Query
	 */
	public function SetMark($mark){
		$this->mark = $mark;
		return $this;
	}

	public function GetQueryTextFormatted () {
		if ($this->_formattedQuery === NULL && $this->queryText !== NULL)
			$this->_formattedQuery = \SqlFormatter::format($this->queryText, TRUE);
		return $this->_formattedQuery;
	}
	public function GetQueryTextCompressed () {
		if ($this->_compressedQuery === NULL && $this->queryText !== NULL)
			$this->_compressedQuery = \SqlFormatter::compress($this->queryText);
		return $this->_compressedQuery;
	}

	
}