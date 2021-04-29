<?php

namespace App\Models\Connection;

trait GettersSetters {

	/** @return int */
	public function GetIdConnection () {
		/** @var \App\Models\Connection $this */
		return $this->idConnection;
	}
	/**
	 * @param int $idConnection 
	 * @return \App\Models\Connection
	 */
	public function SetIdConnection ($idConnection) {
		/** @var \App\Models\Connection $this */
		$this->idConnection = $idConnection;
		return $this;
	}
	/** @return int */
	public function GetIdGeneralLog () {
		/** @var \App\Models\Connection $this */
		return $this->idGeneralLog;
	}
	/**
	 * @param int $idGeneralLog 
	 * @return \App\Models\Connection
	 */
	public function SetIdGeneralLog ($idGeneralLog) {
		/** @var \App\Models\Connection $this */
		$this->idGeneralLog = $idGeneralLog;
		return $this;
	}
	/** @return int */
	public function GetIdUser () {
		/** @var \App\Models\Connection $this */
		return $this->idUser;
	}
	/**
	 * @param int $idUser 
	 * @return \App\Models\Connection
	 */
	public function SetIdUser ($idUser) {
		/** @var \App\Models\Connection $this */
		$this->idUser = $idUser;
		return $this;
	}
	/** @return string */
	public function GetUser () {
		/** @var \App\Models\Connection $this */
		return $this->user;
	}
	/**
	 * @param string $user 
	 * @return \App\Models\Connection
	 */
	public function SetUser ($user) {
		/** @var \App\Models\Connection $this */
		$this->user = $user;
		return $this;
	}
	/** @return int */
	public function GetIdDatabase () {
		/** @var \App\Models\Connection $this */
		return $this->idDatabase;
	}
	/**
	 * @param int $idDatabase
	 * @return \App\Models\Connection
	 */
	public function SetIdDatabase ($idDatabase) {
		/** @var \App\Models\Connection $this */
		$this->idDatabase = $idDatabase;
		return $this;
	}
	/** @return string */
	public function GetDatabase () {
		/** @var \App\Models\Connection $this */
		return $this->database;
	}
	/**
	 * @param string $database 
	 * @return \App\Models\Connection
	 */
	public function SetDatabase ($database) {
		/** @var \App\Models\Connection $this */
		$this->database = $database;
		return $this;
	}
	/** @return int */
	public function GetIdThread () {
		/** @var \App\Models\Connection $this */
		return $this->idThread;
	}
	/**
	 * @param int $idThread 
	 * @return \App\Models\Connection
	 */
	public function SetIdThread ($idThread) {
		/** @var \App\Models\Connection $this */
		$this->idThread = $idThread;
		return $this;
	}
	/** @return \DateTime|NULL */
	public function GetConnected () {
		/** @var \App\Models\Connection $this */
		return $this->connected;
	}
	/**
	 * @param \DateTime|NULL $connected 
	 * @return \App\Models\Connection
	 */
	public function SetConnected ($connected) {
		/** @var \App\Models\Connection $this */
		$this->connected = $connected;
		return $this;
	}
	/** @return \DateTime|NULL */
	public function GetDisconnected () {
		/** @var \App\Models\Connection $this */
		return $this->disconnected;
	}
	/**
	 * @param \DateTime|NULL $connected 
	 * @return \App\Models\Connection
	 */
	public function SetDisconnected ($disconnected) {
		/** @var \App\Models\Connection $this */
		$this->disconnected = $disconnected;
		return $this;
	}
	/** @return int */
	public function GetRequestsCount () {
		/** @var \App\Models\Connection $this */
		return $this->requestsCount;
	}
	/**
	 * @param int $requestsCount 
	 * @return \App\Models\Connection
	 */
	public function SetRequestsCount ($requestsCount) {
		/** @var \App\Models\Connection $this */
		$this->requestsCount = $requestsCount;
		return $this;
	}
	/** @return int */
	public function GetQueriesCount () {
		/** @var \App\Models\Connection $this */
		return $this->queriesCount;
	}
	/**
	 * @param int $queriesCount 
	 * @return \App\Models\Connection
	 */
	public function SetQueriesCount ($queriesCount) {
		/** @var \App\Models\Connection $this */
		$this->queriesCount = $queriesCount;
		return $this;
	}
	/** @return int */
	public function GetMark () {
		/** @var \App\Models\Connection $this */
		return $this->mark;
	}
	/**
	 * @param int $mark 
	 * @return \App\Models\Connection
	 */
	public function SetMark ($mark) {
		/** @var \App\Models\Connection $this */
		$this->mark = $mark;
		return $this;
	}
	/** @return \App\Models\LogFile */
	public function GetGeneralLog () {
		/** @var \App\Models\Connection $this */
		return \App\Models\LogFile::GetById($this->idGeneralLog);
	}


	/** @return int */
	public function GetSelectsCount () {
		/** @var \App\Models\Connection $this */
		return $this->selectsCount;	
	}
	/** @return int */
	public function GetInsertsCount () {
		/** @var \App\Models\Connection $this */
		return $this->insertsCount;	
	}
	/** @return int */
	public function GetUpdatesCount () {
		/** @var \App\Models\Connection $this */
		return $this->updatesCount;	
	}
	/** @return int */
	public function GetDeletesCount () {
		/** @var \App\Models\Connection $this */
		return $this->deletesCount;	
	}
}