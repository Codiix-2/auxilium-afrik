<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('BVProtectLoggerDB_V648')) :
class BVProtectLoggerDB_V648 {
	private $tablename;
	private $bv_tablename;

	const MAXROWCOUNT = 100000;

	function __construct($tablename) {
		$this->tablename = $tablename;
		$this->bv_tablename = BVProtect_V648::$db->getBVTable($tablename);
	}

	public function log($data) {
		if (is_array($data)) {
			if (BVProtect_V648::$db->rowsCount($this->bv_tablename) > BVProtectLoggerDB_V648::MAXROWCOUNT) {
				BVProtect_V648::$db->deleteRowsFromtable($this->tablename, 1);
			}

			BVProtect_V648::$db->replaceIntoBVTable($this->tablename, $data);
		}
	}
}
endif;