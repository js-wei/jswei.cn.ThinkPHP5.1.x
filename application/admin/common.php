<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:52:31+08:00



/**
 * 获取文档的标题
 */
function get_article_title($id=0){
	if(!$id){
		return '';
	}
	$title = db('article')->field('title')->find($id);
	return $title['title'];
}
/**
 * 获取nickname
 */
function get_member_nickname($id=0){
	if(!$id){
		return '';
	}
	$member = db('member')->field('username,email,nickname')->find($id);
	if(!empty($member['nickname'])){
		return $member['nickname'];
	}else if(!empty($member['username'])){
		return $member['username'];
	}else if(!empty($member['email'])){
		return $member['email'];
	}else{
		return '匿名用户';
	}
}

//获取栏目类型
function getPosition($type){
	switch ($type) {
		case 1:
			$t='头部';
			break;
		case 2:
			$t='中部';
			break;
		case 3:
			$t='左侧';
			break;
		case 4:
			$t='右侧';
			break;
		case 5:
			$t='底部';
			break;
	}
	return $t;
}

/**
 * [get_column 获取栏目名]
 * @param  [type] $column_id [栏目ID]
 * @return [type]            [description]
 */
function get_column($column_id=0){
	if(!$column_id){
		return "不限制";
	}else{
		$column = M('column')->field('id,title')->find($column_id);
		return $column['title'];
	}
}
/**
 * 权限名
 */
function get_power($power='',$l=0){
	if(!$power){
		return '';
	}
	$_power = db('model')->field('id,title,name')->where('id','in',$power)->select();

	$html = '';
	if($l){
		$_power = array_slice($_power,0,$l);
	}

	foreach($_power as $k=>$v){
		$html .= "<label class='label label-success mr10'>{$v['name']}</label>";
	}
	return $html;
}

function get_group($id=''){
	if(!$id){
		return '';
	}
	$_group = db('group')->field('title')->find($id);
	return $_group['title'];
}
