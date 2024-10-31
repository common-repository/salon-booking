<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');


class Customer_Data extends Salon_Data {

	const TABLE_NAME = 'salon_customer';

	function __construct() {
		parent::__construct();
	}


	public function insertTable ($table_data){
		$customer_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%s,%d,%d,%s,%s,%s,%s');
		if ($customer_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $customer_cd;
	}

	public function updateTable ($table_data){

		$set_string = 	' ID = %d , '.
						' branch_cd = %d , '.
						' remark =  %s , '.
						' memo =  %s , '.
						' user_login =  %s , '.
						' rank_patern_cd =  %d , '.
						' update_time = %s ';

		$set_data_temp = array(
						$table_data['ID'],
						$table_data['branch_cd'],
						$table_data['remark'],
						$table_data['memo'],
						$table_data['user_login'],
						$table_data['rank_patern_cd'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['customer_cd']);
		$where_string = ' customer_cd = %d ';

		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}

	public function updateColumn($table_data){

		$set_string = 	$table_data['column_name'].' , '.
								' update_time = %s ';

		$set_data_temp = array($table_data['value'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['customer_cd']);
		$where_string = ' customer_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false ) {
			$this->_dbAccessAbnormalEnd();
		}


	}

	public function deleteTable ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(Salon_Reservation_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['customer_cd']);
		$where_string = ' customer_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}


	public function getInitDatas($page_params ,$branch_cd ) {

		return $this->get_customer_datas($page_params, $branch_cd);
	}

	public function getCustomerDataByCustomercd($customer_cd = "") {
		global $wpdb;
		$join = '';
		$where ='';
		$method = 'LEFT';
		if (!empty($customer_cd)) {
			$where = $wpdb->prepare(' WHERE cu.customer_cd = %d ',$customer_cd);
		}
		else {
			$join = ' AND cu.delete_flg <> '.Salon_Reservation_Status::DELETED;
			//他支店の情報はみられないようにする
			if ($this->isSalonAdmin() == false) {
				$method = 'INNER ';

			}
		}
		$sql = 'SELECT us.ID,us.user_login,um.* ,us.user_email,'.
				'        cu.customer_cd,cu.branch_cd,cu.remark,cu.memo,cu.notes,cu.delete_flg ,cu.rank_patern_cd'.
				' FROM '.$wpdb->users.' us  '.
				' INNER JOIN '.$wpdb->usermeta.' um  '.
				'       ON    us.ID = um.user_id '.
				$method.' JOIN '.$wpdb->prefix.'salon_customer cu  '.
				'       ON    us.user_login = cu.user_login '.
				$join.
				$where.
				' ORDER BY customer_cd desc,ID';

		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result;
	}

	public function get_customer_cnt($page_params) {
		global $wpdb;
		$method = 'LEFT';
		if ($this->isSalonAdmin() == false) {
			$method = 'INNER ';
		}
		$filter = "";
		if (empty($page_params) == false ) {
			$filter = $this->edit_filter_string($page_params['filter']);
		}
		$sql = 'SELECT count(*) as cnt '
			.' FROM '.$wpdb->users.' us  '
			.' INNER JOIN '
			.' (SELECT user_id FROM '.$wpdb->usermeta
			.'   WHERE meta_key = "'.$wpdb->prefix.'capabilities"'
			.'     AND meta_value LIKE "%subscriber%"'
				.$filter
			.' ) um  '
			.' ON    us.ID = um.user_id '
			.$method.' JOIN '.$wpdb->prefix.'salon_customer cu  '
			.' ON    us.user_login = cu.user_login '
			.'   AND cu.delete_flg <> '.Salon_Reservation_Status::DELETED
			;
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return ($result[0]['cnt']);

	}

 	private function edit_filter_string($filter) {
 		global $wpdb;
 		$result = "";
		if ($filter <>0 && empty($filter) == false){
			$result = 'AND user_id in (';
			$result .= 'SELECT user_id ';
			$result .= ' FROM '.$wpdb->usermeta;
			$result .= ' WHERE ';
			$result .= ' (meta_key ="first_name" and meta_value like %s) ';
 			$result .= ' OR (meta_key ="last_name" and meta_value like %s) ';
			$result .= ")";
			$result = $wpdb->prepare($result
					,'%'.$filter.'%'
					,'%'.$filter.'%');
		}
 		return $result;
 	}
	public function get_customer_datas($page_params,$branch_cd) {
		global $wpdb;
		$method = 'LEFT';
		if ($this->isSalonAdmin() == false) {
			$method = 'INNER ';
		}
		$limit = "";
		if (0 < $page_params['displayLength'] ) {
			//膨大な値が入った場合を考慮して
			if (Salon_Regist_Customer::MAX_GET_CNT <  $page_params['displayLength']) {
				$page_params['displayLength']=Salon_Regist_Customer::MAX_GET_CNT;
			}
			$limit = ' LIMIT '.$page_params['displayStart'].','.$page_params['displayLength'];
		}
		$filter = $this->edit_filter_string($page_params['filter']);
		$sql = 'SELECT us.ID,us.user_login,us.user_email,'
				.'        cu.customer_cd,cu.branch_cd,cu.remark ,cu.rank_patern_cd'
				.' FROM '.$wpdb->users.' us  '
				.' INNER JOIN '
				.' (SELECT user_id FROM '.$wpdb->usermeta
				.'   WHERE meta_key = "'.$wpdb->prefix.'capabilities"'
				.'     AND meta_value LIKE "%subscriber%"'
				.$filter
				.' ) um  '
				.' ON    us.ID = um.user_id '
				.$method.' JOIN '.$wpdb->prefix.'salon_customer cu  '
				.' ON    us.user_login = cu.user_login '
				.'   AND cu.delete_flg <> '.Salon_Reservation_Status::DELETED
				.' ORDER BY  customer_cd desc,ID '
				.$limit
				;
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		//
		if (count($result ) == 0) {
			return null;
		}

		$in_string = ' user_id in (';
		$comma = '';
		foreach ($result as $k1 => $d1) {
			$in_string .= $comma . $d1['ID'];
			$comma = ',';
		}
		$in_string .= ' )';
		$sql ='SELECT um.user_id,um.meta_key,um.meta_value '
			.' FROM '
			. ' ( select meta_key,meta_value,user_id from '.$wpdb->usermeta
			. '  where '.$in_string. ' ) um '
			.' WHERE '
			.'    um.meta_key = "first_name"'
			.' OR um.meta_key = "last_name"'
			.' OR um.meta_key = "zip"'
			.' OR um.meta_key = "address"'
			.' OR um.meta_key = "tel"'
			.' OR um.meta_key = "mobile"'
			.' OR um.meta_key = "'.$wpdb->prefix.'capabilities"'
			.' ORDER BY um.user_id ';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result_metas = $wpdb->get_results($sql,ARRAY_A);
		}
		//reformat usermeta by user_id
		$key_users_array = array();
		foreach($result_metas as $k1 => $d1) {
			$key_users_array[$d1['user_id']][$d1['meta_key']] = $d1['meta_value'];
			unset($result_metas[$k1]);
		}
		$result_after = array();
		$index = 0;

		//cat users & usermeta
		foreach($result as $k1 => $d1) {
			$d2 = $key_users_array[$d1['ID']];
			unset($key_users_array[$d1['ID']]);
			$role = unserialize($d2[$wpdb->prefix.'capabilities']) ;

			//顧客は「購読者」のみ
			if ( array_key_exists('subscriber',$role) ){
				//管理者以外は他店舗をみられない
				if ($this->isSalonAdmin()
						||	$branch_cd == $d1['branch_cd']) {
					$result_after[$index]['ID'] = $d1['ID'];
					$result_after[$index]['customer_cd'] = $d1['customer_cd'];
					$result_after[$index]['user_login'] = $d1['user_login'];
					$result_after[$index]['mail'] = $d1['user_email'];
					$result_after[$index]['branch_cd'] = $d1['branch_cd'];
					$result_after[$k1]['branch_name'] = "";
					if (empty($d1['branch_cd'] ) ) {
						$result_after[$k1]['branch_name'] = __('registerd salon ',SL_DOMAIN).__('not registered',SL_DOMAIN);
					}
					$result_after[$index]['remark'] = $d1['remark'];
// 					$result_after[$index]['memo'] = $d1['memo'];
// 					$result_after[$index]['notes'] = $d1['notes'];
					$result_after[$index]['rank_patern_cd'] = $d1['rank_patern_cd'];
					//usermeta
					$result_after[$index]['first_name'] = $d2['first_name'];
					$result_after[$index]['last_name'] = $d2['last_name'];
					$result_after[$index]['zip'] = @$d2['zip'];
					$result_after[$index]['address'] = @$d2['address'];
					$result_after[$index]['tel'] = @$d2['tel'];
					$result_after[$index]['mobile'] = @$d2['mobile'];
					$index++;
				}
			}
			unset($result[$k1]);
		}
		return $result_after;
	}



}