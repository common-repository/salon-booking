<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Checkconfig_Edit extends Salon_Page {

	private $table_data = null;


	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}


	public function check_request() {
		$msg = null;
		Salon_Page::serverCheck(array(),$msg);

	}


	public function show_page() {
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($this->table_data).' }';
	}


}