<?php
namespace app\admin\model;
use think\Model;

class Comments extends Model{
	//自定义初始化
    protected function initialize(){
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }
	
	public function infor(){
        return $this->hasOne('member','id')->field('date',true);
    }
	public function article(){
        return $this->hasOne('article','id','aid')->field('id,title');
    }
}
