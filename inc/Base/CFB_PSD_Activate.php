<?php
namespace CFB_PSD\Base;

class CFB_PSD_Activate
{
	// write sql to add user table
	public static function activate() {
		flush_rewrite_rules();
	}
}