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
	 *    "headingName": "ID Connection",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $idConnection;
	
	/**
	 * @datagrid Column({
	 *    "dbColumnName": "mark",
	 *    "headingName": "Marked",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $mark;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "id_thread",
	 *    "headingName": "ID Thread",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $idThread;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "connected",
	 *    "headingName": "Connected",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var \DateTime
	 */
	protected $connected;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "disconnected",
	 *    "headingName": "Disconnected",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var \DateTime
	 */
	protected $disconnected;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "requests_count",
	 *    "headingName": "Requests Count",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $requestsCount;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "queries_count",
	 *    "headingName": "Queries Count",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $queriesCount;
	
	/**
	 * @datagrid Column({
	 *    "dbColumnName": "user",
	 *    "headingName": "User",
	 *    "sort": true,
	 *    "filter": false
	 * })
	 * @var string
	 */
	protected $user;
	
	/**
	 * @datagrid Column({
	 *    "dbColumnName": "database",
	 *    "headingName": "Database",
	 *    "sort": true,
	 *    "filter": false
	 * })
	 * @var string|NULL
	 */
	protected $database;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "id_user",
	 *    "headingName": "ID User",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $idUser;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "id_database",
	 *    "headingName": "ID Database",
	 *    "sort": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $idDatabase;


	/**
	 * @datagrid Column({
	 *    "dbColumnName": "selects_count",
	 *    "headingName": "SELECTs",
	 *    "sort": false,
	 *    "filter": false
	 * })
	 * @var int
	 */
	protected $selectsCount;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "inserts_count",
	 *    "headingName": "INSERTs",
	 *    "sort": false,
	 *    "filter": false
	 * })
	 * @var int
	 */
	protected $insertsCount;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "updates_count",
	 *    "headingName": "UPDATEs",
	 *    "sort": false,
	 *    "filter": false
	 * })
	 * @var int
	 */
	protected $updatesCount;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "deletes_count",
	 *    "headingName": "DELETEs",
	 *    "sort": false,
	 *    "filter": false
	 * })
	 * @var int
	 */
	protected $deletesCount;
}