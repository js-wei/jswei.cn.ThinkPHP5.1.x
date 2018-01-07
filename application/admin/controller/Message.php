<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:51:19+08:00

namespace app\admin\controller;

class Message extends Base{
    protected function  initialize(){
        parent::initialize();
    }

    /**
     * 首页
     * @param int $aid
     * @return \think\response\View
     */
    public function index(){
        $where=[];
        $search = $this->_search();
        $where = array_merge($where,$search);

        $list = db('Message')->where($where)->order('date desc')->paginate(15,false,[
            'query'=>[
                's_keywords'=>input('s_keywords'),
                "s_date"=>input('s_date'),
                "s_status"=>input('s_status')
            ]
        ]);
        // 查询状态为1的用户数据 并且每页显示10条数据
        $count = db('Message')->count('*');
        $this->assign('count',$count);
        $this->assign('list',$list);
        return view();
    }

    /**
     * 添加
     * @param int $id
     * @return \think\response\View
     */
    public function add($id=0){
        $model = [
            'name'=>'添加消息'
        ];
        if($id){
            $vo = db('product')->field('dates',true)->find($id);
            //p($vo);die;
            $this->assign('info',$vo);
        }
        $this->assign('model',$model);
        return view();
    }

    public function add_handler($id=0){
        $param = request()->param();
        $param['date']=time();
        $param['type']=1;
        $member = db('member')->where('status','eq',0)->select();
        foreach ($member as $k => $v){
            $param['mid']=$v['id'];
            if(!db('message')->insert($param)){
                return ['status'=>0,'msg'=>'添加失败请重试'];
            }
        }
        return ['status'=>1,'msg'=>'添加成功','redirect'=>Url('index')];
    }

    /**
     * 搜索
     * @param array $param
     * @return array
     */
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
            's_status'=>!empty($param['s_status'])?$param['s_status']:-1
        ]);
        return $where;
    }
    /**
     * [status 状态操作]
     * @param  [type] $id [修改id]
     * @param  [type] $type  [操作类型]
     * @return [type]     [description]
     */
    public function status($id,$type){
        $type = ($type=="delete-all")?"delete":$type;
        $_result = $this->_status($id,'message',$type);
        return $_result;
    }
}
