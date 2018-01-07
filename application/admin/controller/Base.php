<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:50:47+08:00



namespace app\admin\controller;
use think\Controller;
use think\Session;

class Base extends Controller{

	protected function initialize(){
		header('Content-type:text/html;charset=utf-8;');
		set_time_limit(0);

		//常用变量
		$this->action = request()->action();
		$this->controller = request()->controller();
		$this->module = request()->module();
 		//判断是否登录
		$this->check_priv();
		//获取功能导航
		$nav = $this->auth_list();

        //获取网站配置
        $this->site = db('Config')->order('id desc')->find();
        $controller = db('model')->field('id,title,name')->where('title','eq',$this->controller)->find();
        $controller = db('model')->field('id,title,name')->where('fid','eq',$controller['id'])->select();
        foreach ($controller as $k => $v){
            if($v['title']==$this->action){
                $temp=[
                  'name'=>  $v['name']
                ];
                $this->assign('model',$temp);
            }
        }

		$ctr = db('model')->where(['title'=>$this->controller])->find();
        $act = db('model')->where(['title'=>$this->action,'fid'=>$ctr['id']])->find();
        $this->current=$act['name'];

		//$nav = array_merge($nav,$col);
        $this->_msg();
		//输出导航
		$this->assign('site',$this->site);
		$this->assign('nav',$nav);
		$this->assign('action',strtolower($this->action));
		$this->assign('controller',strtolower($this->controller));
		$this->assign('module',strtolower($this->module));
	}

	protected  function _msg(){
	    $_count = db('authentication')->where('status','eq',0)->count();
        $_msg = db('authentication')->where('status','eq',0)->limit(4)->select();
        $this->assign('_msg_count',$_count);
        $this->assign('_msg_list',$_msg);
    }

	/**
	 * [column_list 获取栏目数据]
	 * @return [type] [description]
	 */
	protected function column_list(){
		$column = db("column")->where('status=0 and type!=6')->order('sort asc,id asc')->select();
		foreach ($column as $k => $v) {
			$column[$k]['col']=1;
		}
		$column= \service\Category::unlimitedForLevel($column);
		return $column;
	}
	/**
	 * [auth_list 获取功能栏目]
	 * @return [type] [description]
	 */
	protected function auth_list(){
        $gid  = session('_gid');
        $this->assign('gid',$gid);
        $nav=[];

        if($gid!=-1 && !empty($gid)){
			$_group = db('group')->field('id,title,power')->find($gid);
            $model = db('model')
				->where('fid','eq',0)
				->where('status','eq',0)
            	->where('id','in',$_group['power'])
            	->order('sort asc')
            	->select();
        }else{
            $model = db('model')->where('fid=0 and "show"=0 and status=0')->order('sort asc')->select();
        }

		foreach ($model as  $v) {
			$map['fid']=$v['id'];
			$map['status']=0;
            $map['show']=0;
			$child=db('model')->where($map)->order('sort asc,id asc')->select();
			foreach ($child as $k1 => $v1) {
				$child[$k1]['col']=0;
			}
			$v['child']=$child;
			$v['col']=0;
			$nav[]=$v;
		}
		return $nav;
	}
	/**
	 * [check_priv 判断登录状态]
	 * @return [type] [description]
	 */
	protected function check_priv() {
		if(!session('?_logined')){
			$this->redirect('user/login');
		}else{
            $user = session('_logined');
	        $user['username']=ucfirst($user['username']);
	        $this->assign('user',$user);
        }
	}
	/**
	 * [_status 修改状态]
	 * @param  [type] $id          [id]
	 * @param  string $model       [模型]
	 * @param  string $t           [类型]
	 * @param  string $redirect    [跳转地址]
	 * @param  string $img         [删除图片]
	 * @return [type]              [description]
	 */
    protected function _status($id,$model='',$t='',$img='image',$redirect=''){
        $id = $id?$id:input('k');
        $where['id']=array('in',$id);
        $redirect = $redirect?$redirect:Url('index');   //跳转地址
        $m = db($model);

        switch($t){
            case 'enable':            //启用
                $result = $m->where($where)->update(array('status'=>0));
                if(!$result){
                    return ['status'=>0,'msg'=>'启用失败'];
                }else{
                   return ['status'=>1,'msg'=>'启用成功','redirect'=>$redirect];
                }
                break;
            case 'forbidden':        //禁用
                $result = $m->where($where)->update(array('status'=>1));
                if(!$result){
                   return ['status'=>0,'msg'=>'禁用失败'];
                }
                return ['status'=>1,'msg'=>'禁用成功','redirect'=>$redirect];
                break;
            case 'delete':           //删除
                $upload = new Uploadify();
                $flag =true;
                if(!empty($img)){
                    $flag =  $upload->delmgByWhere1($m,$where,$img) && $upload->delArticleImage($m,$where,'content')
                    		&& $upload->delImageAtlas($m, $where);
                }
                if($flag){
                    $result = $m->where($where)->delete();
                    if(!$result){
                        return ['status'=>0,'msg'=>'删除失败'];
                    }
                    return ['status'=>1,'msg'=>'删除成功','redirect'=>$redirect];
                }else{
                    return ['status'=>0,'msg'=>'删除失败'];
                }
                break;
        }
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
            'think_ver'=>THINK_VERSION,
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
            'memory_limit' => get_cfg_var("memory_limit") ? get_cfg_var("memory_limit") : '-',
        );
        return $sys;
    }
    /**
     * [get_version 获取数据库版本]
     * @return [type] [description]
     */
    protected function get_version(){
        $version = db::query("select version() as ver");
        return $version[0]['ver'];
    }
    /**
     * [_mysql_db_size mysql数据库大小]
     * @return [type] [description]
     */
    protected function get_mysql_db_size(){
        $sql = "SHOW TABLE STATUS FROM ".config('database');
        $tblPrefix = config('prefix');
        if($tblPrefix != null) {
            $sql .= " LIKE '{$tblPrefix}%'";
        }

        $row = db::query($sql);
        $size = 0;
        foreach($row as $value) {
            $size += $value["data_length"] + $value["index_length"];
        }
        return round(($size/1048576),2).'M';
     }
	/**
     * [makeAttr 重置文章属性]
     * @param  [array] $resetAttr 重置的属性
     * @return [array] 返回重置的属性
     */
    protected function makeAttr($resetAttr){
    	$attr=array('com'=>0,'new'=>0,'head'=>0,'top'=>0,'img'=>0,'hot'=>0);
    	foreach ($resetAttr as $k => $v) {
    		$attr[$k]=1;
    	}
    	return $attr;
    }

    /**
     * 导出Excel
     * @param $expTitle
     * @param $expCellName
     * @param $expTableData
     * @param $header
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    protected function exportExcel($expTitle,$expCellName,$expTableData,$header=''){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = date('_YmdHis_').'对账单';//or $xlsTitle 文件名称可根据自己情况设定

        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        //vendor("PHPExcel.PHPExcel");

        $objPHPExcel = new \PHPExcel();

        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $header);
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }

        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        return '';
    }

    /**
     * 获取短路由
     * @param string $source
     * @param string $url_long
     */
    public function get_short_url($source='',$url_long=''){
        $curl = new \Curl\Curl();
        $curl->post('http://api.t.sina.com.cn/short_url/shorten.json', array(
            'source' => $source,
            'url_long' =>$url_long
        ));
        if ($curl->error) {
            echo $curl->error_code;
        }
        else {
            echo $curl->response;
        }
    }

	 /**
     * 删除图片
     * @param string $url   图片地址
     * @return \think\response\Json
     */
	public function delete_image($url=''){
        if(strpos($url,'.')!==true){
            $url = "." . $url;
        }
	    if(!$url){
	        return json(['status'=>0,'msg'=>'图片地址不能为空']);
        }
        if(!is_file($url)){
            return json(['status'=>0,'msg'=>'不是一个文件地址或地址不正确']);
        }
        if(!unlink($url)){
            return json(['status'=>0,'msg'=>'删除失败,请重试']);
        }
        return json(['status'=>1,'msg'=>'删除成功']);
    }

    protected function _search($param=[]){
        $where=[];
        if(empty($param)){
            $param = request()->param();
        }
        if(!empty($param['s_keywords'])){
            $where['title']=['like',"%".$param['s_keywords']."%"];
        }
        if(!empty($param['s_status'])){
            $where['status']=$param['s_status']>-1?$param['s_status']:'';
        }
        if(!empty($param['s_date'])){
            $date = explode('-',$param['s_date']);
            $date[1] = "$date[1] 24:00";
            $where['date']=['between',[strtotime($date[0]),strtotime($date[1])]];
        }

        $this->assign('search',[
            's_keywords'=>!empty($param['s_keywords'])?$param['s_keywords']:'',
            's_date'=>!empty($param['s_date'])?$param['s_date']:'',
            's_status'=>!empty($param['s_status'])?$param['s_status']:''
        ]);
        return $where;
    }
}
