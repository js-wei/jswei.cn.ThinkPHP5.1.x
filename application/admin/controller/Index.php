<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:51:05+08:00



namespace app\admin\controller;

use think\Db;
use think\facade\App;

class Index extends Base {
	protected function  initialize(){
		parent::initialize();
	}

	public function index(){
		$os = $this->_sys();
		$this->_get_details();
		$this->assign('os',$os);
		return view();
	}

	protected function _get_details(){
        $totals = db('orderlist')->where('ordstatus','eq',0)->sum('ordfee');
	    $totals1 = db('orderlist')->where('ordstatus','eq',1)->sum('ordfee');
        $auth = db('authentication')->where('status','eq',0)->count();
        $sum = db('member')->count();
        $sum1 = db('member')->where('last_login_time','eq',0)->count();

        $order = db('orderlist')->count();
        $order1 = db('orderlist')->where('ordstatus','eq',1)->count();


	    $this->assign('totals',$totals?$totals:0);
        $this->assign('totals1',$totals1?$totals1:0);

        $this->assign('auth',$auth);

        $this->assign('sum',$sum);
        $this->assign('sum1',$sum1);

        $this->assign('order',$order);
        $this->assign('order1',$order1);
    }
	/**
     * @return array 获取当前系统信息
     */
    protected function _sys(){
        date_default_timezone_set("Etc/GMT-8");
        if (function_exists('gd_info')){
            $gdInfo = gd_info();
            $gd_support = true;
            $gdv_version = $gdInfo['GD Version'];
        }  else {
            $gd_support = false;
            $gdv_version  = '';
        }
        $sys=array(
                'os'=>PHP_OS,
                'os_all'=>php_uname('s'),
                //'server1'=>apache_get_version(),
                'server'=>php_sapi_name(),
                'think_ver'=>App::version(),
                'php'=> PHP_VERSION,
                'php_dir'=> DEFAULT_INCLUDE_PATH,
                'safe_mode'=>ini_get('safe_mode')?0:1,
                'gd'=>$gd_support,
                'gd_ver'=>$gdv_version,
                'mysql'=>$this->get_version(),
                'mysql_size'=>$this->get_mysql_db_size(),
                'file_size'=>ini_get("file_uploads") ? ini_get("upload_max_filesize") : "Disabled",
                'host'=>$_SERVER['SERVER_NAME'],
                'system_time' => date("Y-m-d",time()).'&nbsp;&nbsp;<span id="item-time">'.date('H:i:s',time()).'</span>',
                //'cpu_num'=>$_SERVER['PROCESSOR_IDENTIFIER'],
                'server'=>$_SERVER['SERVER_SOFTWARE'],
                //'user_group'=>$_SERVER['USERDOMAIN'],
                'server_lang'=>$_SERVER['HTTP_ACCEPT_LANGUAGE'],
                'server_point'=>$_SERVER['SERVER_PORT'],
                // 脚本运行占用最大内存
                'memory_limit' => get_cfg_var("memory_limit") ? get_cfg_var("memory_limit") : '-',
        );
        return $sys;
    }
     /**
     * [get_version 获取数据库版本]
     * @return [type] [description]
     */
    protected function get_version(){
        $version = Db::query("select version() as ver");
        return $version[0]['ver'];
    }
    /**
     * [_mysql_db_size mysql数据库大小]
     * @return [type] [description]
     */
    protected function get_mysql_db_size(){

        $sql = "SHOW TABLE STATUS FROM ".Config('database.database');
        $tblPrefix = Config('database.prefix');
        if($tblPrefix != null) {
            $sql .= " LIKE '{$tblPrefix}%'";
        }

        $row = Db::query($sql);

        $size = 0;
        foreach($row as $value) {
            $size += $value["Data_length"] + $value["Index_length"];
        }
        return round(($size/1048576),2).'M';
    }
}
