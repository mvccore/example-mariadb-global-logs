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
	 *    "order": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $idConnection;
	
	/**
	 * @datagrid Column({
	 *    "dbColumnName": "mark",
	 *    "humanName": "Marked",
	 *    "order": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $mark;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "id_thread",
	 *    "humanName": "ID Thread",
	 *    "order": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $idThread;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "connected",
	 *    "humanName": "Connected",
	 *    "order": true,
	 *    "filter": true
	 * })
	 * @var \DateTime
	 */
	protected $connected;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "disconnected",
	 *    "humanName": "Disconnected",
	 *    "order": true,
	 *    "filter": true
	 * })
	 * @var \DateTime
	 */
	protected $disconnected;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "requests_count",
	 *    "humanName": "Requests Count",
	 *    "order": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $requestsCount;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "queries_count",
	 *    "humanName": "Queries Count",
	 *    "order": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $queriesCount;
	
	/**
	 * @datagrid Column({
	 *    "dbColumnName": "user",
	 *    "humanName": "User",
	 *    "order": true,
	 *    "filter": false
	 * })
	 * @var string
	 */
	protected $user;
	
	/**
	 * @datagrid Column({
	 *    "dbColumnName": "database",
	 *    "humanName": "Database",
	 *    "order": true,
	 *    "filter": false
	 * })
	 * @var string
	 */
	protected $database;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "id_user",
	 *    "humanName": "ID User",
	 *    "order": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $idUser;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "id_database",
	 *    "humanName": "ID Database",
	 *    "order": true,
	 *    "filter": true
	 * })
	 * @var int
	 */
	protected $idDatabase;


	/**
	 * @datagrid Column({
	 *    "dbColumnName": "selects_count",
	 *    "humanName": "SELECTs",
	 *    "order": false,
	 *    "filter": false
	 * })
	 * @var int
	 */
	protected $selectsCount;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "inserts_count",
	 *    "humanName": "INSERTs",
	 *    "order": false,
	 *    "filter": false
	 * })
	 * @var int
	 */
	protected $insertsCount;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "updates_count",
	 *    "humanName": "UPDATEs",
	 *    "order": false,
	 *    "filter": false
	 * })
	 * @var int
	 */
	protected $updatesCount;

	/**
	 * @datagrid Column({
	 *    "dbColumnName": "deletes_count",
	 *    "humanName": "DELETEs",
	 *    "order": false,
	 *    "filter": false
	 * })
	 * @var int
	 */
	protected $deletesCount;
}