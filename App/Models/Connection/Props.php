<?php

namespace App\Models\Connection;

trait Props {
	
	/**
	 * @var int
	 */
	protected $idGeneralLog;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "id_connection",
	 *    "humanName": "ID Connection",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $idConnection;
	
	/**
	 * @datagrid Column({
	 *    "dbColumnName": "mark",
	 *    "humanName": "Marked",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $mark;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "id_thread",
	 *    "humanName": "ID Thread",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $idThread;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "connected",
	 *    "humanName": "Connected",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var \DateTime
	 */
	protected $connected;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "disconnected",
	 *    "humanName": "Disconnected",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var \DateTime
	 */
	protected $disconnected;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "requests_count",
	 *    "humanName": "Requests Count",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $requestsCount;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "queries_count",
	 *    "humanName": "Queries Count",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $queriesCount;
	
	/**
	 * @datagrid Column({
	 *    "dbColumnName": "user",
	 *    "humanName": "User",
	 *    "sort": true,
	 *    "filter": false
	 * })
	 * @var string
	 */
	protected $user;
	
	/**
	 * @datagrid Column({
	 *    "dbColumnName": "database",
	 *    "humanName": "Database",
	 *    "sort": true,
	 *    "filter": false
	 * })
	 * @var string
	 */
	protected $database;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "id_user",
	 *    "humanName": "ID User",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $idUser;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "id_database",
	 *    "humanName": "ID Database",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $idDatabase;


	/**
	 * @datagrid Column({
	 *    "dbColumnName": "selects_count",
	 *    "humanName": "SELECTs",
	 *    "sort": false,
	 *    "filter": false
	 * })
	 * @var int
	 */
	protected $selectsCount;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "inserts_count",
	 *    "humanName": "INSERTs",
	 *    "sort": false,
	 *    "filter": false
	 * })
	 * @var int
	 */
	protected $insertsCount;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "updates_count",
	 *    "humanName": "UPDATEs",
	 *    "sort": false,
	 *    "filter": false
	 * })
	 * @var int
	 */
	protected $updatesCount;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "deletes_count",
	 *    "humanName": "DELETEs",
	 *    "sort": false,
	 *    "filter": false
	 * })
	 * @var int
	 */
	protected $deletesCount;
}