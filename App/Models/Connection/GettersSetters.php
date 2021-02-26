<?php

namespace App\Models\Connection;

trait GettersSetters {

	/** @return int */
	public function GetIdConnection () {
		/** @var $this \App\Models\Connection */
		return $this->idConnection;
	}
	/**
	 * @param int $idConnection 
	 * @return \App\Models\Connection
	 */
	public function SetIdConnection ($idConnection) {
		/** @var $this \App\Models\Connection */
		$this->idConnection = $idConnection;
		return $this;
	}
	/** @return int */
	public function GetIdGeneralLog () {
		/** @var $this \App\Models\Connection */
		return $this->idGeneralLog;
	}
	/**
	 * @param int $idGeneralLog 
	 * @return \App\Models\Connection
	 */
	public function SetIdGeneralLog ($idGeneralLog) {
		/** @var $this \App\Models\Connection */
		$this->idGeneralLog = $idGeneralLog;
		return $this;
	}
	/** @return int */
	public function GetIdUser () {
		/** @var $this \App\Models\Connection */
		return $this->idUser;
	}
	/**
	 * @param int $idUser 
	 * @return \App\Models\Connection
	 */
	public function SetIdUser ($idUser) {
		/** @var $this \App\Models\Connection */
		$this->idUser = $idUser;
		return $this;
	}
	/** @return string */
	public function GetUser () {
		/** @var $this \App\Models\Connection */
		return $this->user;
	}
	/**
	 * @param string $user 
	 * @return \App\Models\Connection
	 */
	public function SetUser ($user) {
		/** @var $this \App\Models\Connection */
		$this->user = $user;
		return $this;
	}
	/** @return int */
	public function GetIdDatabase () {
		/** @var $this \App\Models\Connection */
		return $this->idDatabase;
	}
	/**
	 * @param int $idDatabase
	 * @return \App\Models\Connection
	 */
	public function SetIdDatabase ($idDatabase) {
		/** @var $this \App\Models\Connection */
		$this->idDatabase = $idDatabase;
		return $this;
	}
	/** @return string */
	public function GetDatabase () {
		/** @var $this \App\Models\Connection */
		return $this->database;
	}
	/**
	 * @param string $database 
	 * @return \App\Models\Connection
	 */
	public function SetDatabase ($database) {
		/** @var $this \App\Models\Connection */
		$this->database = $database;
		return $this;
	}
	/** @return int */
	public function GetIdThread () {
		/** @var $this \App\Models\Connection */
		return $this->idThread;
	}
	/**
	 * @param int $idThread 
	 * @return \App\Models\Connection
	 */
	public function SetIdThread ($idThread) {
		/** @var $this \App\Models\Connection */
		$this->idThread = $idThread;
		return $this;
	}
	/** @return \DateTime|NULL */
	public function GetConnected () {
		/** @var $this \App\Models\Connection */
		return $this->connected;
	}
	/**
	 * @param \DateTime|NULL $connected 
	 * @return \App\Models\Connection
	 */
	public function SetConnected ($connected) {
		/** @var $this \App\Models\Connection */
		$this->connected = $connected;
		return $this;
	}
	/** @return \DateTime|NULL */
	public function GetDisconnected () {
		/** @var $this \App\Models\Connection */
		return $this->disconnected;
	}
	/**
	 * @param \DateTime|NULL $connected 
	 * @return \App\Models\Connection
	 */
	public function SetDisconnected ($disconnected) {
		/** @var $this \App\Models\Connection */
		$this->disconnected = $disconnected;
		return $this;
	}
	/** @return int */
	public function GetRequestsCount () {
		/** @var $this \App\Models\Connection */
		return $this->requestsCount;
	}
	/**
	 * @param int $requestsCount 
	 * @return \App\Models\Connection
	 */
	public function SetRequestsCount ($requestsCount) {
		/** @var $this \App\Models\Connection */
		$this->requestsCount = $requestsCount;
		return $this;
	}
	/** @return int */
	public function GetQueriesCount () {
		/** @var $this \App\Models\Connection */
		return $this->queriesCount;
	}
	/**
	 * @param int $queriesCount 
	 * @return \App\Models\Connection
	 */
	public function SetQueriesCount ($queriesCount) {
		/** @var $this \App\Models\Connection */
		$this->queriesCount = $queriesCount;
		return $this;
	}
	/** @return int */
	public function GetMark () {
		/** @var $this \App\Models\Connection */
		return $this->mark;
	}
	/**
	 * @param int $mark 
	 * @return \App\Models\Connection
	 */
	public function SetMark ($mark) {
		/** @var $this \App\Models\Connection */
		$this->mark = $mark;
		return $this;
	}
	/** @return \App\Models\LogFile */
	public function GetGeneralLog () {
		/** @var $this \App\Models\Connection */
		return \App\Models\LogFile::GetById($this->idGeneralLog);
	}


	/** @return int */
	public function GetSelectsCount () {
		/** @var $this \App\Models\Connection */
		return $this->selectsCount;	
	}
	/** @return int */
	public function GetInsertsCount () {
		/** @var $this \App\Models\Connection */
		return $this->insertsCount;	
	}
	/** @return int */
	public function GetUpdatesCount () {
		/** @var $this \App\Models\Connection */
		return $this->updatesCount;	
	}
	/** @return int */
	public function GetDeletesCount () {
		/** @var $this \App\Models\Connection */
		return $this->deletesCount;	
	}
}