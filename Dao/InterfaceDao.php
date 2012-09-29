<?php

namespace Smally\Dao;

interface InterfaceDao {

	public function getById($id);
	public function fetch(\Smally\Criteria $criteria);
	public function fetchAll(\Smally\Criteria $criteria);

	public function store($vo);
	public function delete($id);

	public function lastInsertId();

}