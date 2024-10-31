<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');


class Checkconfig_Data extends Salon_Data {


	const TABLE_NAME = 'salon_reservation';

	function __construct() {
		parent::__construct();
	}

	public function getTableData() {
		$return = array();
		global $wpdb;
		$sql = 			'SELECT '.
						' branch_cd'
						.',name'
						.',open_time'
						.',close_time'
						.',time_step'
						.',duplicate_cnt'
						.',delete_flg'
						.',DATE_FORMAT(insert_time,"%Y%m%d%H%i") as insert_time'
						.',DATE_FORMAT(update_time,"%Y%m%d%H%i") as update_time'
						.' FROM '.$wpdb->prefix.'salon_branch'
						.'';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		$return['BRANCH'] = $result;

		$sql = 			'SELECT '.
						' staff_cd'
						.',branch_cd'
						.',in_items'
						.',delete_flg'
						.',DATE_FORMAT(insert_time,"%Y%m%d%H%i") as insert_time'
						.',DATE_FORMAT(update_time,"%Y%m%d%H%i") as update_time'
						.' FROM '.$wpdb->prefix.'salon_staff'
						.'';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		$return['STAFF'] = $result;

		$sql = 			'SELECT '.
						' item_cd'
						.',branch_cd'
						.',delete_flg'
						.',DATE_FORMAT(insert_time,"%Y%m%d%H%i") as insert_time'
						.',DATE_FORMAT(update_time,"%Y%m%d%H%i") as update_time'
						.' FROM '.$wpdb->prefix.'salon_item'
						.'';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		$return['MENU'] = $result;


		return $return;
	}

	public function getCollationData() {
		global $wpdb;
		$sql = 'show full columns from '.$wpdb->prefix.'salon_staff'
			.' where field = "user_login"';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		$return['collation']['staff'] = $result[0]['Collation'];

		$sql = 'show full columns from '.$wpdb->prefix.'salon_customer'
			.' where field = "user_login"';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		$return['collation']['customer'] = $result[0]['Collation'];

		$sql = 'show full columns from '.$wpdb->users
			.' where field = "user_login"';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		$return['collation']['users'] = $result[0]['Collation'];

		return $return;
	}
	public function getConfigShowData() {
		$result = $this->getConfigData();
		return $result;
	}

	public function updateCollation() {
		$collations = $this->getCollationData();
		global $wpdb;
		if ($collations['collation']['users'] !=
				$collations['collation']['customer'] ) {
			$sql =$wpdb->prepare("ALTER TABLE ".$wpdb->prefix."salon_customer "
					."MODIFY COLUMN user_login varchar(60) collate %s "
					,$collations['collation']['users']);
			if ($wpdb->query($sql) === false ) {
				$this->_dbAccessAbnormalEnd();
			}
		}
		if ($collations['collation']['users'] !=
				$collations['collation']['staff'] ) {
			$sql =$wpdb->prepare("ALTER TABLE ".$wpdb->prefix."salon_staff "
					."MODIFY COLUMN user_login varchar(60) collate %s "
					,$collations['collation']['users']);
			if ($wpdb->query($sql) === false ) {
				$this->_dbAccessAbnormalEnd();
			}
		}
	}

// 	public function doDebug() {
// 			$sql ='insert into wp_salon_customer '
// 				. '(ID,user_login,branch_cd,insert_time,update_time) '
// 				. 'values (%d,%s,%d,%s,%s)';
// 			echo ($sql);
// 		for ($i=1; $i < 700 ; $i++ ) {
// 			$user_id = wp_create_user("user_".$i,"user_".$i,"mail@user_".$i);
// 			update_user_meta( $user_id, 'zip',"1112222");
// 			update_user_meta( $user_id, 'address',"address".$i);
// 			update_user_meta( $user_id, 'tel',"1234");
// 			update_user_meta( $user_id, 'mobile',"22234");
// 			update_user_meta( $user_id, 'first_name',"first_".$i);
// 			update_user_meta( $user_id, 'last_name',"last_".$i);
// 			update_user_meta( $user_id, 'wp_capabilities','a:1:{s:10:"subscriber";s:1:"1";}');
// 			global $wpdb;

// 			$current_time = date_i18n('Y-m-d H:i:s');
// 			$exec= $wpdb->prepare($sql,$user_id,"user_".$i,1,$current_time,$current_time);
// 			$res = $wpdb->query($exec);
// 			if ($res===false) {
// 				break;
// 			}
// 		}
// 	}

}