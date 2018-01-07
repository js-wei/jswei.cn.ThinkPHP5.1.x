<?php
# @Author: 魏巍
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  jswei30@gmail.com
# @Filename: Article.php
# @Last modified by:   魏巍
# @Last modified time: 2017-11-20T12:43:25+08:00




namespace app\common\taglib;

use think\template\TagLib;

class Article extends TagLib
{
    /**
     * 定义标签列表
     */
    protected $tags   =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'list' => array('attr' => 'cid,model,field,limit,order,where,empty,date,attr','close' =>1,'alias'=>'l'),
        'article' => array('attr' => 'id,field,where','close' =>1,'alias'=>'art'),
        'nav' => array('attr' => 'position,where,order','close' =>1,'alias'=>'nav'),
        'location'=>array('attr' => 'model,field,current,where','close' =>1),
        'banner'=>array('attr' => 'model,field,limit,where,date,order,','close' =>1,'alias'=>'banner'),
        'myad'=>array('attr' => 'model,field,limit,where,date','close' =>1,'alias'=>'ad'),
        'link'=>array('attr' => 'model,field,limit,where,position','close' =>1),
        'channel'=>array('attr' => 'model,id,field,limit,where,position','close' =>1,'alias'=>'c'),
        'channels'=>array('attr' => 'model,fid,field,limit,where,order,position','close' =>1,'alias'=>'c1'),
        'random'=>array('attr' => 'model,fid,field,limit,where,order','close' =>1,'alias'=>'r'),
        'file'=>array('attr' => 'model,field,limit,where,date,order,empty','close' =>1),
        'site'=>array('attr' => 'model,field,limit,where,empty','close' =>0),
        'carousel'=>['attr' => 'model,field,btn,limit,where,empty,date','close' =>0,'alias'=>'banner1']
    ];

    /**
     * 获取文章标签
     * @param $attr
     * @param $content
     * @return string
     */
    public function tagList($attr, $content)
    {
        $model=!empty($attr['model'])?$attr['model']:'Article';
        $field=!empty($attr['field'])?$attr['field']:'"dates",true';
        $limit=!empty($attr['limit'])?$attr['limit']:'';
        $order=!empty($attr['order'])?$attr['order']:'';
        $where= $this->adjunct($attr);
        $id=!empty($attr['cid'])?$attr['cid']:input('cid/d');
        if ($id) {
            $id = $this->_get_child($id);
        }
        if (!empty($id)) {
            if (!empty($where)) {
                if (strpos($where, 'column_id')===false) {
                    $where.=" and column_id in ({$id})";
                }
            } else {
                $where.= " column_id in ({$id})";
            }
        }

        $str='<?php ';
        $str .= '$_list_news=db("'.$model.'")->field('.$field.')->where("'.$where.'")->limit('.$limit.')->order("'.$order.'")->select();';//查询语句
        $str .='$_column=db("Column")->find("'.$id.'");';
        $str .='$column=$_column; $key=0;';
        $str .= 'if($_list_news){foreach ($_list_news as $_list_value):';
        //$str .= 'extract($_list_value);';
        $str .= '$list=$_list_value;++$key;';
        $str .= '?>';
        $str .= $content;
        $str .='<?php endforeach; ?>';
        $str .='<?php }else{';
        $str .= 'echo "'.$attr['empty'].'";} ?>';
        return $str;
    }

    /**
     *获取单个文章
     * @param $attr
     * @param $content
     * @return string
     */
    public function tagArticle($attr, $content)
    {
        $model=!empty($attr['model'])?$attr['model']:'Article';
        $field=!empty($attr['field'])?$attr['field']:'*';
        $key=!empty($attr['key'])?$attr['key']:'id';
        $id=!empty($attr['id'])?$attr['id']:input('id/d');
        $where= $this->adjunct($attr);

        if (!empty($where)) {
            $str='<?php ';
            $str .= '$_result_content=db("'.$model.'")->field("'.$field.'")->where("'.$where.'")->find();';//查询语句
            $str .= '$_column=db("Column")->find($_result_content["column_id"]);';
            $str .= '$pre=db("'.$model.'")->where("'.$key.' < '.$id.' and column_id=".$_result_content["column_id"])->order("'.$key.' desc")->limit(1)->find();'; //上一条
            $str .= '$nxt=db("'.$model.'")->where("'.$key.' > '.$id.' and column_id=".$_result_content["column_id"])->order("'.$key.' asc")->limit(1)->find();'; //下一条
            $str .= '$article=$_result_content;';
            $str .= '$column=$_column;';
            $str .= '?>';
            $str .= $content;
            return $str;
        } else {
            $str='<?php ';
            $str .= '$_result_content=M("'.$model.'")->field("'.$field.'")->where("'.$where.'")->find("'.$id.'");';
            $str .= '$_column=db("Column")->find($_result_content["column_id"]);';
            $str .= '$pre=db("'.$model.'")->where("'.$key.' < '.$id.' and column_id=".$_result_content["column_id"])->order("'.$key.' desc")->limit(1)->find();'; //上一条
            $str .= '$nxt=db("'.$model.'")->where("'.$key.' > '.$id.' and column_id=".$_result_content["column_id"])->order("'.$key.' asc")->limit(1)->find();'; //下一条
            $str .= '$article=$_result_content;';
            $str .= '$column=$_column;';
            $str .= '?>';
            $str .= $content;
            return $str;
        }
    }

    /**
     * 获取导航
     * @param $attr
     * @param $content
     * @return string
     */
    public function tagNav($attr, $content)
    {
        $model=!empty($attr['model'])?$attr['model']:'Column';
        $field=!empty($attr['field'])?$attr['field']:'*';
        $limit=!empty($attr['limit'])?$attr['limit']:'';
        $order=!empty($attr['order'])?$attr['order']:'';
        $position=$attr['position'];
        $where= $this->adjunct($attr);
        if (!empty($position)) {
            if (empty($where)) {
                $where .=' position = '.$position;
            } else {
                $where .=' and position = '.$position;
            }
        }
        $str='<?php ';
        $str .= '$_list_result=db("'.$model.'")->field("'.$field.'")->where("'.$where.'")->limit('.$limit.')->order("'.$order.'")->select();';
        $str .= '$nav=\service\Category::unlimitedForLevel($_list_result);';
        $str .= '?>';
        $str .= $content;
        return $str;
    }

    /**
     * 获取面包屑
     * @param $attr
     * @param $content
     * @return string
     */
    public function tagLocation($attr, $content)
    {
        $model=!empty($attr['model'])?$attr['model']:'Column';
        $field=!empty($attr['field'])?$attr['field']:'*';
        $where= $this->adjunct($attr);
        $id = input('id/d');
        if ($id) {
            $_article=db('Article')->find($id);
            $current=!empty($attr["current"])?$attr["current"]:$_article['column_id'];
        } else {
            $current=!empty($attr["current"])?$attr["current"]:input('cid/d');
        }

        $str='<?php ';
        $str .= '$_current_column=db("'.$model.'")->field("'.$field.'")->find("'.$current.'");
              $_result_content=db("'.$model.'")->field("'.$field.'")->where("'.$where.'")->select();
              $location=\service\Category::getParents($_result_content,$_current_column);
              $location[]=$_current_column;
              $temp=array(array("id" =>0,"title" => "首页","uri"=>"/"));
              $location=array_merge($temp,$location);
              $length=count($location)-1;
            ';
        $str .= '?>';
        $str .= $content;
        return $str;
    }
    /***
     *滚屏图片表
    CREATE TABLE IF NOT EXISTS `think_carousel` (
        `id` int(11)  NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
        `title` char(80) NOT NULL DEFAULT '' COMMENT '滚屏图片中文名称',
        `name` char(20) NOT NULL DEFAULT '' COMMENT '滚屏图片英文名称',
        `description` char(250) NOT NULL DEFAULT '' COMMENT '滚屏图片简单介绍',
        `image` char(250) NOT NULL DEFAULT '' COMMENT '滚屏图片图片',
        `date` int(11) NOT NULL DEFAULT 0 COMMENT '添加时间',
        `effective` int(11) NOT NULL DEFAULT 0 COMMENT '滚屏图片有效时间,在有效时间内会显示',
        `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序：越小越靠前',
        `status` int(1) NOT NULL DEFAULT 0 COMMENT '状态：0正常，1禁用',
        `dates` int(11) NOT NULL DEFAULT 0 COMMENT '时间戳',
        PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT '轮播图表'
     ***/
    /**
     * 获取轮播
     * @param $attr
     * @param $content
     * @return string
     */
    public function tagBanner($attr, $content)
    {
        $model=!empty($attr['model'])?$attr['model']:'carousel';
        $field=!empty($attr['field'])?$attr['field']:'*';
        $where= $this->adjunct($attr);
        $limit=!empty($attr['limit'])?$attr['limit']:'';
        $order=!empty($attr['order'])?$attr['order']:'';
        $str='<?php ';
        $str .= '$_list_banner2=db("'.$model.'")->field("'.$field.'")->where("'.$where.'")->limit('.$limit.')->order("'.$order.'")->select();';
        $str .= '$_i=0; foreach ($_list_banner2 as $_list_value):';
        //$str .= 'extract($_list_value);';
        $str .= '$banner=$_list_value;';
        $str .= '$key=$_i;$_i++;';
        $str .= '?>';
        $str .= $content;
        $str .='<?php endforeach ?>';
        return $str;
    }
    /***
        *Ad广告表
        CREATE TABLE IF NOT EXISTS `think_Ad` (
        `id` int(8) NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
        `title` char(80) NOT NULL DEFAULT '' COMMENT '广告中文名称',
        `name` char(20) NOT NULL DEFAULT '' COMMENT '广告英文名称',
        `description` char(250) NOT NULL DEFAULT '' COMMENT '广告简单介绍',
        `image` char(250) NOT NULL DEFAULT '' COMMENT '广告图片',
        `effective` int(11) NOT NULL DEFAULT 0 COMMENT '广告有效时间,在有效时间内会显示',
        `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序：越小越靠前',
        `status` int(1) NOT NULL DEFAULT 0 COMMENT '状态：0正常，1禁用',
        `date` int(11) NOT NULL DEFAULT 0 COMMENT '添加时间',
        `dates` int(11) NOT NULL DEFAULT 0 COMMENT '时间戳',
        PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT '广告表';
     ***/
    public function tagMyad($attr, $content)
    {
        $model=!empty($attr['model'])?$attr['model']:'Ad';
        $field=!empty($attr['field'])?$attr['field']:'*';
        $where= $this->adjunct($attr);
        $limit=!empty($attr['limit'])?$attr['limit']:'';
        $order=!empty($attr['order'])?$attr['order']:'';
        /*
        $date=!empty($attr['date'])?$attr['date']:'';
        $temp = '';
        if(!empty($date)){
            $date=explode(' ', $date);
            if(count($date)>1){
                if(count($date)==2){
                    $where.=' and date between '.strtotime($date[0]).' and '.strtotime($date[1]);
                }else{
                    foreach ($date as $v) {
                        $temp.= strtotime($v).',';
                    }
                    $temp=substr($temp, 1, -1);
                    $where.=' effective IN ('.$temp.')';
                }
            }else{
               if(strstr($date,'eq')){
                   $where.=' and effective = '.strtotime($date[0]);
               }
               if(strstr($date,'lt')){
                    $where.=' and effective < '.strtotime($date[0]);
               }
               if(strstr($date,'gt')){
                    $where.=' and effective > '.strtotime($date[0]);
               }
                if(strstr($date,'elt')){
                    $where.=' and effective <= '.strtotime($date[0]);
                }
                if(strstr($date,'egt')){
                    $where.=' and effective >= '.strtotime($date[0]);
                }
            }
        }
         */

        $str='<?php
          $_result_content=db("'.$model.'")->field("'.$field.'")->where("'.$where.'")->order("'.$order.'")->limit("'.$limit.'")->select();
          $myad=$_result_content;
          ?>';
        $str .= $content;
        return $str;
    }
    /***
     *Flink友情链接
    CREATE TABLE IF NOT EXISTS `think_Flink` (
    `id` int(11)  NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
    `title` char(80) NOT NULL DEFAULT '' COMMENT '友情链接中文名称',
    `name` char(20) NOT NULL DEFAULT '' COMMENT '友情链接英文名称',
    `description` char(250) NOT NULL DEFAULT '' COMMENT '友情链接简单介绍',
    `ico` char(250) NOT NULL DEFAULT '' COMMENT '友情链接图标',
    `url` char(250) NOT NULL DEFAULT '' COMMENT '友情链接链接指向,链接到的地址',
    `position` int(1) NOT NULL DEFAULT 0 COMMENT '友情链接位置：0不限,1首页，2内页',
    `date` int(11) NOT NULL DEFAULT 0 COMMENT '添加时间',
    `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序：越小越靠前',
    `status` int(1) NOT NULL DEFAULT 0 COMMENT '状态：0正常，1禁用',
    'dates` int(11) NOT NULL DEFAULT 0 COMMENT '时间戳',
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT '友情链接';
     ***/
    /**
     * 友情链接
     * @param $attr
     * @param $content
     * @return string
     */
    public function tagLink($attr, $content)
    {
        $model=!empty($attr['model'])?$attr['model']:'Flink';
        $field=!empty($attr['field'])?$attr['field']:'*';
        $position=!empty($attr['position'])?$attr['position']:0;
        $where= $this->adjunct($attr);
        $limit=!empty($attr['limit'])?$attr['limit']:'';
        $order=!empty($attr['order'])?$attr['order']:'';

        if (!empty($position)) {
            $where.=' and position='.$position;
        }
        $str='<?php
          $_result_content=db("'.$model.'")->field("'.$field.'")->where("'.$where.'")->order("'.$order.'")->limit("'.$limit.'")->select();
          $flink=$_result_content;
      ?>';
        $str .= $content;
        return $str;
    }
    /***
     *Column栏目表
    CREATE TABLE IF NOT EXISTS `think_Column` (
    `id` int(8)  NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
    `title` char(80) NOT NULL DEFAULT '' COMMENT '栏目中文名称',
    `name` char(20) NOT NULL DEFAULT '' COMMENT '栏目英文名称',
    `description` char(250) NOT NULL DEFAULT '' COMMENT '栏目简单介绍',
    `banner` char(250) NOT NULL DEFAULT '' COMMENT '栏目Banner',
    `iamge` char(250) NOT NULL DEFAULT '' COMMENT '栏目图片',
    `ico` char(250) NOT NULL DEFAULT '' COMMENT '栏目图标',
    `position` int(1) NOT NULL DEFAULT 0 COMMENT '栏目位置：1头部，2中部，3左侧，4右侧，5底部',
    `date` int(11) NOT NULL DEFAULT 0 COMMENT '添加时间',
    `effective` int(11) NOT NULL DEFAULT 0 COMMENT '栏目有效时间,在有效时间内会显示',
    `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序：越小越靠前',
    `status` int(1) NOT NULL DEFAULT 0 COMMENT '状态：0正常，1禁用',
    `dates` int(11) NOT NULL DEFAULT 0 COMMENT '时间戳',
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT '栏目表';
     ***/
    /**
     * 获取单个栏目
     * @param $attr
     * @param $content
     * @return string
     */
    public function tagChannel($attr, $content)
    {
        $model=!empty($attr['model'])?$attr['model']:'Column';
        $field=!empty($attr['field'])?$attr['field']:'*';
        $position=!empty($attr['position'])?$attr['position']:0;
        $id=!empty($attr['id'])?$attr['id']:0;
        $where = $this->adjunct($attr);
        if (!empty($position)) {
            $where.=' and position='.$position;
        }

        $str='<?php
          $channel=db("'.$model.'")->field("'.$field.'")->where("'.$where.'")->find("'.$id.'");
       ?>';
        $str .= $content;
        return $str;
    }

    /**
     * 获取栏目
     * @param $attr
     * @param $content
     * @return string
     */
    public function tagChannels($attr, $content)
    {
        $model=!empty($attr['model'])?$attr['model']:'Column';
        $position=!empty($attr['position'])?$attr['position']:'';
        $field=!empty($attr['field'])?$attr['field']:'*';
        $fid=!empty($attr['fid'])?$attr['fid']:input('fid/d');
        $where= $this->adjunct($attr);
        $limit=!empty($attr['limit'])?$attr['limit']:'';
        $order=!empty($attr['order'])?$attr['order']:'';
        if (!empty($position)) {
            $where.=' and position = '.$position;
        }
        $str='<?php
      	  $parent=db("'.$model.'")->field("'.$field.'")->find('.$fid.');
          $_result_channellist=db("'.$model.'")->field("'.$field.'")->where("'.$where.'")->order("'.$order.'")->limit("'.$limit.'")->select();
          $channels=\service\Category::unlimitedForLevel($_result_channellist);
      ?>';

        $str .= $content;
        return $str;
    }

    /**
     * 轮播图
     * @param $attr
     * @param $content
     * @return string
     */
    public function tagCarousel($attr, $content)
    {
        $model  = !empty($attr['model'])?$attr['model']:'Carousel';
        $position   =  !empty($attr['position'])?$attr['position']:'';
        $field  =   !empty($attr['field'])?$attr['field']:'*';
        $fid = !empty($attr['fid'])?$attr['fid']:input('fid/d');
        $where= $this->adjunct($attr);
        $limit=!empty($attr['limit'])?$attr['limit']:'';
        $order=!empty($attr['order'])?$attr['order']:'';
        $btn_show = !empty($attr['btn'])?$attr['btn']:0;
        $prev = !empty($attr['prev'])?$attr['prev']:'Previous';
        $next = !empty($attr['next'])?$attr['next']:'Next';
        $cycle_show = !empty($attr['cycle'])?$attr['cycle']:0;
        if (!empty($position)) {
            $where.=' and position = '.$position;
        }
        if ($fid) {
            $where.=' and fid = '.$fid;
        }
        $_banner = db($model)->field($field)->where($where)->limit($limit)->order($order)->select();

        $c = '<ol class="carousel-indicators">';
        $item = '';
        foreach ($_banner as $k=>$v) {
            if ($k==0) {
                $c.= '<li data-target="#myCarousel" data-slide-to="'.$k.'" class="active"></li>';
                $item .=' <div class="item active">
                      <img class="first-slide" src="'.$v['image'].'" alt="First slide">
                      <div class="container">
                        <div class="carousel-caption">
                          <h1>'.$v['title'].'</h1>
                          <p>'.$v['description'].'</p>
                          <p><a class="btn btn-lg btn-primary" href="'.$v['url'].'" role="button">'.$v['title'].'</a></p>
                        </div>
                      </div>
                    </div>';
            } else {
                $c.= '<li data-target="#myCarousel" data-slide-to="'.$k.'"></li>';
                $item .=' <div class="item">
                      <img class="first-slide" src="'.$v['image'].'" alt="First slide">
                      <div class="container">
                        <div class="carousel-caption">
                          <h1>'.$v['title'].'</h1>
                          <p>'.$v['description'].'</p>
                          <p><a class="btn btn-lg btn-primary" href="'.$v['url'].'" role="button">'.$v['title'].'</a></p>
                        </div>
                      </div>
                    </div>';
            }
        }
        $c .='</ol>';
        $cycle = $cycle_show?$c:'';
        //按钮
        $btn = $btn_show?'<a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
            <span class="sr-only">'.$prev.'</span>
          </a>
          <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
            <span class="sr-only">'.$next.'</span>
          </a>':'';
        $html = <<<EOT
         <script>document.write("<script src=\"https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js\"><\/script>")</script>
        <script>document.write("<script src=\"https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js\"><\/script>")</script>
        <style>.navbar-wrapper{position:absolute;top:0;right:0;left:0;z-index:20}.navbar-wrapper > .container{padding-right:0;padding-left:0}.navbar-wrapper .navbar{padding-right:15px;padding-left:15px}.navbar-wrapper .navbar .container{width:auto}.carousel{height:auto;margin-bottom:60px}.carousel-caption{z-index:10}.carousel .item{height:500px;background-color:#777}.carousel-inner > .item > img{position:absolute;top:0;left:0;min-width:100%;height:500px}.carousel-indicators li{margin-right:5px}.carousel-indicators .active{margin-right:5px}.marketing .col-lg-4{margin-bottom:20px;text-align:center}.marketing h2{font-weight:normal}.marketing .col-lg-4 p{margin-right:10px;margin-left:10px}.featurette-divider{margin:80px 0}.featurette-heading{font-weight:300;line-height:1;letter-spacing:-1px}@media (min-width: 768px){.navbar-wrapper{margin-top:20px}.navbar-wrapper .container{padding-right:15px;padding-left:15px}.navbar-wrapper .navbar{padding-right:0;padding-left:0}.navbar-wrapper .navbar{border-radius:4px}.carousel-caption p{margin-bottom:20px;font-size:21px;line-height:1.4}.featurette-heading{font-size:50px}}@media (min-width: 992px){.featurette-heading{margin-top:120px}}</style>
        <div id="myCarousel" class="carousel slide" data-ride="carousel">
    $cycle
    <div class="carousel-inner" role="listbox">
       $item
    </div>
    {$btn}
</div><!-- /.carousel -->
EOT;

        $str = '<?php';
        $str .= ' echo \''.$html.'\';';
        $str .= '?>';
        $str .= $content;
        return $str;
    }

    /***
     *Article文章表
     CREATE TABLE IF NOT EXISTS `think_Article` (
        `id` int(8)  NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
        `column_id` int(1) NOT NULL DEFAULT 0 COMMENT '所属栏目',
        `title` char(80) NOT NULL DEFAULT '' COMMENT '文章中文名称',
        `name` char(20) NOT NULL DEFAULT '' COMMENT '文章英文名称',
        `description` char(250) NOT NULL DEFAULT '' COMMENT '栏目简单介绍',
        `iamge` char(250) NOT NULL DEFAULT '' COMMENT '文章图片',
        `com` int(1) NOT NULL DEFAULT 0 COMMENT '推荐，0否，1是',
        `hot` int(1) NOT NULL DEFAULT 0 COMMENT '最热，0否，1是',
        `new` int(1) NOT NULL DEFAULT 0 COMMENT '最新，0否，1是',
        `head` int(1) NOT NULL DEFAULT 0 COMMENT '头条，0否，1是',
        `top` int(1) NOT NULL DEFAULT 0 COMMENT '置顶，0否，1是',
        `date` int(11) NOT NULL DEFAULT 0 COMMENT '添加时间',
        `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序：越小越靠前',
        `status` int(1) NOT NULL DEFAULT 0 COMMENT '状态：0正常，1禁用',
        `dates` int(11) NOT NULL DEFAULT 0 COMMENT '时间戳',
        PRIMARY KEY (`id`)
     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT "文章表";
     ***/
    /**
     * 获取随机文章
     * @param $attr
     * @param $content
     * @return string
     */
    public function tagRandom($attr, $content)
    {
        $model=!empty($attr['model'])?$attr['model']:'Article';
        $fid=!empty($attr['fid'])?$attr['fid']:0;
        $limit=!empty($attr['limit'])?$attr['limit']:'';
        $order=!empty($attr['order'])?$attr['order']:'id';
        $where= $this->adjunct($attr);

        if (!empty($fid)) {
            $where.=' and fid = '.$fid;
        }
        $DB_PREFIX = config('prefix');
        /*$sql='SELECT *  FROM `'.$DB_PREFIX.$model.'` AS t1 JOIN
              (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM `'.$DB_PREFIX.$model.'`)-(SELECT MIN(id) FROM `'.$DB_PREFIX.$model.'`))+(SELECT MIN(id) FROM `'.$DB_PREFIX.$model.'`)) AS id) AS t2
              WHERE t1.id >= t2.id ORDER BY t1.'.$order.' LIMIT '.$limit;*/
        $sql='SELECT * FROM `'.$DB_PREFIX.$model.'`
          WHERE id >= (SELECT floor(RAND() * (SELECT MAX(id) FROM `'.$DB_PREFIX.$model.'`))) and '.$where.'
          ORDER BY '.$order.' LIMIT '.$limit;

        $str='<?php
          $_result_randomrecom=db("'.$model.'")->query("'.$sql.'");
          $randomrecom=$_result_randomrecom;
        ?>';
        $str .= $content;
        return $str;
    }
    /*
     --
     -- 表的结构 `think_file`
     --
     CREATE TABLE IF NOT EXISTS `think_file` (
      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键自动增长',
      `title` varchar(50) NOT NULL COMMENT '文件名称',
      `ico` varchar(150) NOT NULL COMMENT '文件图标',
      `name` varchar(50) NOT NULL COMMENT '文件名称',
      `rename` varchar(50) NOT NULL COMMENT '文件上传名',
      `type` varchar(20) NOT NULL COMMENT '文件类型',
      `description` varchar(250) NOT NULL COMMENT '文件说明',
      `size` int(11) NOT NULL COMMENT '文件大小(B)',
      `path` varchar(150) NOT NULL COMMENT '文件路径',
      `sort` int(11) NOT NULL COMMENT '排序:愈小愈靠前',
      `status` tinyint(1) NOT NULL COMMENT '文件状态:0正常,1禁用',
      `date` int(11) NOT NULL COMMENT '文件上传时间',
      `dates` int(11) NOT NULL COMMENT '时间戳',
      PRIMARY KEY (`id`)
     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='文件表' AUTO_INCREMENT=1 ;
     **/
    /**
     * 获取文件
     * @param $attr
     * @param $content
     * @return string
     */
    public function tagFile($attr, $content)
    {
        $model=!empty($attr['model'])?$attr['model']:'File';
        $field=!empty($attr['field'])?$attr['field']:'*';
        $limit=!empty($attr['limit'])?$attr['limit']:'';
        $order=!empty($attr['order'])?$attr['order']:'id';
        $empty=!empty($attr['empty'])?$attr['empty']:'没有数据';

        $where= $this->adjunct($attr);  //条件
        $_result_content=db($model)->field($field)->where($where)->order($order)->limit($limit)->select();
        $str=!empty($_result_content)?'<?php $file=db("'.$model.'")->field("'.$field.'")->where("'.$where.'")->order("'.$order.'")->limit("'.$limit.'")->select(); ?>':'<?php echo "'.$empty.'"; ?>';
        $str .= $content;
        return $str;
    }
    /*
     --
     -- 表的结构 `think_confing`
     --
     CREATE TABLE IF NOT EXISTS `think_confing` (
       `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键，自增长',
       `title` varchar(150) NOT NULL COMMENT '网站名',
       `keywords` varchar(250) NOT NULL COMMENT '网站关键词',
       `description` varchar(500) NOT NULL COMMENT '网站说明',
       `conact` varchar(1000) NOT NULL COMMENT '联系方式',
       `flink` text NOT NULL COMMENT '友情链接',
       `sum` int(10) NOT NULL COMMENT '幻灯个数',
       `copyright` varchar(10000) NOT NULL COMMENT '版权信息',
       `shard` text NOT NULL COMMENT '分享代码',
       `code` text NOT NULL COMMENT '统计代码，多个使用'':''分割',
       `date` int(10) NOT NULL COMMENT '修改日期',
       `status` tinyint(1) NOT NULL DEFAULT '0',
       PRIMARY KEY (`id`)
     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COMMENT='网站配置' AUTO_INCREMENT=2;
     --
     -- 转存表中的数据 `think_confing`
     --
     INSERT INTO `think_confing` (`id`, `title`, `keywords`, `description`, `conact`, `flink`, `sum`, `copyright`, `shard`, `code`, `date`, `status`) VALUES
     (1, '51Game', '51Game', '51Game', '测试', 'htmlspecialchars', 3, '测试', '测试', '测试', 1419500501, 0);

    */
    /**
     * 获取网站配置
     * @param $attr
     * @param $content
     * @return string
     */
    public function tagSite($attr, $content)
    {
        $model=!empty($attr['model'])?$attr['model']:'config';
        $field=!empty($attr['field'])?$attr['field']:'*';
        $empty=!empty($attr['empty'])?$attr['empty']:'没有数据';

        $where= $this->adjunct($attr);  //条件
        $_result_content=db($model)->field($field)->where($where)->find();
        $str=!empty($_result_content)?'<?php $site=db("'.$model.'")->field("'.$field.'")->where("'.$where.'")->find(); ?>':'<?php echo "'.$empty.'"; ?>';
        $str .= $content;
        return $str;
    }

    /**
     * 组合条件
     * @param $attr
     * @return string
     */
    private function adjunct($attr)
    {
        $date=!empty($attr['date'])?$attr['date']:'';
        $where = !empty($attr['where'])?$attr['where']:'';
        $where= $this->condition($where);
        $file = strstr($date, '/e')?'effective':'date';
        if (empty($where)) {
            $where.='status = 0';
        } else {
            if (strpos($where, 'status') === false) {
                $where.=' and status = 0';
            }
        }

        if (!empty($date)) {
            $date1 = $date;
            $date=explode(' ', $date);
            $temp = '';
            if (count($date)>1) {
                if (count($date)==2) {  //两个区间查询
                    $where.=' and date between '.strtotime($date[0]).' and '.strtotime($date[1]);
                } else {
                    foreach ($date as $v) { //两个以上存在查询
                        $temp.= strtotime($v).',';
                    }
                    $temp=substr($temp, 1, -1);   //去掉最后一个字符
                    $where.=' and '.$file.' IN ('.$temp.')';
                }
            } else {  //一个精确查询
                //$where.=' and date = '.strtotime($date[0]);
                if (strstr($date1, 'eq')) {
                    $where.=' and '.$file.' = '.strtotime($date[0]);
                }
                if (strstr($date1, 'lt')) {
                    $where.=' and '.$file.' < '.strtotime($date[0]);
                }
                if (strstr($date1, 'gt')) {
                    $where.=' and '.$file.' > '.strtotime($date[0]);
                }
                if (strstr($date1, 'elt')) {
                    $where.=' and '.$file.' <= '.strtotime($date[0]);
                }
                if (strstr($date1, 'egt')) {
                    $where.=' and '.$file.' >= '.strtotime($date[0]);
                }
            }
        }
        $attr1=!empty($attr['attr'])?$this->makeAttr($attr['attr']):'';
        if (!empty($where) || !empty($attr1)) {
            if (!empty($where) && !empty($attr1)) {
                $where .= ' and '.$attr1;
            } else {
                $where .= $attr1;
            }
        }
        return $where;
    }

    /**
     * 重置查询符号
     * @param $str
     * @return mixed
     */
    private function condition($str)
    {
        if (strstr($str, 'neq') || strstr($str, 'eq')) {
            if (strstr($str, 'neq')) {
                $str=str_replace('neq', '!=', $str);
            } else {
                $str=str_replace('eq', '=', $str);
            }
        }
        if (strstr($str, 'elt') || strstr($str, 'lgt') || strstr($str, 'lt')) {
            if (strstr($str, 'elt')) {
                $str=str_replace('elt', '<=', $str);
            } elseif (strstr($str, 'lgt')) {
                $str=str_replace('lgt', '<>', $str);
            } else {
                $str=str_replace('lt', '<', $str);
            }
        }
        if (strstr($str, 'gt') || strstr($str, 'egt')) {
            if (strstr($str, 'egt')) {
                $str=str_replace('egt', '>=', $str);
            } else {
                $str=str_replace('gt', '>', $str);
            }
        }
        return $str;
    }

    /**
     * 获取子栏目ID
     * @param $id
     * @return string
     */
    protected function _get_child($id)
    {
        $curr=db('column')->field('dates', true)->find($id);
        $column=db('column')->field('dates', true)->where('status', 'eq', 0)->select();
        $data = \service\Category::getChildrenById($column, $curr['id']);
        $retVal = (!empty($data)) ? implode(',', $data).','.$id : $id;
        return $retVal;
    }

    /**
     * 获取父栏目ID
     * @param $id
     * @return string
     */
    protected function _get_parent($id)
    {
        $curr=db('column')->find($id);
        $column=db('column')->where(array('status = 0'))->select();
        $data = \service\Category::getParentsById($column, $curr['fid']);
        rsort($data);
        $retVal = $this->_get_child($data[0]);
        return $retVal;
    }
    /**
     * 组成属性条件
     * @param $attr
     * @return string
     */
    protected function makeAttr($attr)
    {
        switch ($attr) {
            case 'com':   #推荐
                return ' com = 1';
                break;
            case 'new':   #最新
                return' new = 1';
                break;
            case 'hot':   #最热
                return ' hot = 1';
                break;
            case 'head':  #头条
                return ' head = 1';
                break;
            case 'top':   #置顶
                return ' top = 1';
                break;
            case 'img':   #图文
                return ' img = 1';
                break;
            default:
                return '';
                break;
        }
    }

    /**
     * 拆解
     * @param $param
     * @param string $op
     * @return string
     */
    protected function _split_parama($param, $op="column_id")
    {
        $temp = explode('and', $param);
        $res="column_id in ";
        $t ='';
        $reid='';
        foreach ($temp as $k => $v) {
            if (strpos($v, $op)!==false) {
                $temp1=explode('=', $v);
                if (count($temp1)>=2) {
                    $res.=$temp1[1];
                }
            } else {
                $t.=$v;
            }
        }
        $where= $t." ".$res.$reid;
        return $where;
    }
}
