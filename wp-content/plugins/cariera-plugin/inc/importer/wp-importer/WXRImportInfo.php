<?php

// https://github.com/proteusthemes/WordPress-Importer/blob/master/src/WXRImportInfo.php

namespace Cariera_Core\Importer\WP_Importer;

class WXRImportInfo {
	public $home;
	public $siteurl;
	public $title;
	public $users         = [];
	public $post_count    = 0;
	public $media_count   = 0;
	public $comment_count = 0;
	public $term_count    = 0;
	public $generator     = '';
	public $version;
}
