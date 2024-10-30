<?php
namespace CFB_PSD\Base;

class CFB_PSD_Deactivate
{
	public static function deactivate() {
		flush_rewrite_rules();
	}
}