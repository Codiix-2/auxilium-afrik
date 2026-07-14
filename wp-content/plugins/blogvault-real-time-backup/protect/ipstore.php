<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('BVProtectIpstore_V648')) :
require_once dirname( __FILE__ ) . '/request.php';
require_once dirname( __FILE__ ) . '/ipstore/fs.php';
require_once dirname( __FILE__ ) . '/ipstore/db.php';

class BVProtectIpstore_V648 {
	private $storage;
	private $storage_type;

	const STORAGE_TYPE_FS = 0;
	const STORAGE_TYPE_DB = 1;

	function __construct($storage_type = BVProtectIpstore_V648::STORAGE_TYPE_DB) {
		$this->storage_type = $storage_type;
		if ($this->storage_type == BVProtectIpstore_V648::STORAGE_TYPE_FS) {
			$this->storage = new BVProtectIpstoreFS_V648();
		} else {
			$this->storage = new BVProtectIpstoreDB_V648();
		}
	}

	public static function uninstall() {
		BVProtectIpstoreDB_V648::uninstall();
	}

	public function isLPIPBlacklisted($ip) {
		if ($this->storage_type == BVProtectIpstore_V648::STORAGE_TYPE_DB) {
			return $this->storage->isLPIPBlacklisted($ip);
		}
	}

	public function isLPIPWhitelisted($ip) {
		if ($this->storage_type == BVProtectIpstore_V648::STORAGE_TYPE_DB) {
			return $this->storage->isLPIPWhitelisted($ip);
		}
	}

	public function getTypeIfBlacklistedIP($ip) {
		return $this->storage->getTypeIfBlacklistedIP($ip);
	}

	public function isFWIPBlacklisted($ip) {
		return $this->storage->isFWIPBlacklisted($ip);
	}

	public function isFWIPWhitelisted($ip) {
		return $this->storage->isFWIPWhitelisted($ip);
	}
}
endif;