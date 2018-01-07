<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:52:19+08:00



namespace app\admin\controller;

use think\Env;

class Uploadify extends Base{

	protected function initialize(){
		parent::initialize();
	}

    /**
     * webUploader 上传方法
     * @param string $file      接收名称
     * @param string $folder    保存目录名
     * @return \think\response\Json
     */
    public function webUploader($file='file',$folder='image'){
        $file = request()->file($file);
        $path = DIRECTORY_SEPARATOR .'uploads'. DIRECTORY_SEPARATOR . $folder;
        $config=[
            'size'=>1024*1024*20,
            'ext'=>'jpg,png,gif'
        ];
        $info = $file->validate($config)->move(Env::get('root_path') . 'public'.$path);
        $w = input('w')?input('w/d'):$this->site['w'];
        $h = input('h')?input('h/d'):$this->site['h'];
        $o = input('o')?input('o'):0;
        if($info){
            $fullPath =  $path.DIRECTORY_SEPARATOR.$info->getSaveName();
            $_path = $this->thumb($fullPath,$w,$h,$o);
            return json([
                "jsonrpc" => "2.0",
                'result'=>[
                    'code'=>200,
                    'file'=>$fullPath,
                    'thumb'=>$_path,
                    'id'=>'id'
                ]
            ]);
        }else{
            return json([
                "jsonrpc" => "2.0",
                'error'=>[
                    'code'=>102,
                    'message'=>"Failed to open output stream.",
                    'id'=>'id'
                ]
            ]);
        }
    }

	/**
	 * [uploadimg 上传单个图片]
	 * @return [type] [description]
	 */
	public function uploadimg($file='image'){
	    $file = request()->file($file);
        $path = Env::get('root_path')  .'public'.DIRECTORY_SEPARATOR .'uploads'. DIRECTORY_SEPARATOR .'uploadify'. DIRECTORY_SEPARATOR . 'image';
	    $info = $file->validate(config('UPLOADE_IMAGE'))->move($path);
		$w = input('w')?input('w/d'):$this->site['w'];
		$h = input('h')?input('h/d'):$this->site['h'];
	    if($info){
	        $_path =  DIRECTORY_SEPARATOR . 'uploads'. DIRECTORY_SEPARATOR .'uploadify'. DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR . $info->getSaveName();
            $this->thumb($_path,$w,$h);
	        return $_path;
	    }else{
	        return $file->getError();
	    }
	}

    /**
     * 缩略图
     * @param string $path          地址
     * @param int $w                宽
     * @param int $h                宽
     * @param bool $o               覆盖
     * @param string $thumb_path    保存地址
     * @return string               路径
     */
	public function thumb($path='',$w=16,$h=16,$o=false,$thumb_path=''){
        $path = Env::get('root_path')  . 'public'.$path;
        $pathinfo = pathinfo($path);
        $image = \think\Image::open($path);
        if($o){
            $path = $pathinfo['dirname'].DIRECTORY_SEPARATOR . $pathinfo['basename'];
        }else{
            $path = $thumb_path?$thumb_path:$pathinfo['dirname']. DIRECTORY_SEPARATOR . 'm_'. $pathinfo['basename'];
        }
		if(!is_file($path)){
            $image->thumb($w, $h,\think\Image::THUMB_CENTER)->save($path);
        }
        return str_replace(Env::get('root_path')  . 'public','',$path);
	}

    /**
     * 水印
     * @param string $path      地址
     * @param int $t            类型:1文字,2图片
     * @param string $param     水印对象
     */
	public function water($path='',$t=1,$param=''){
        $path = Env::get('root_path') .'public'.$path;
        $image = \think\Image::open($path);

        switch ($t){
            case 1:
                $font = Env::get('root_path')  . 'public'.DIRECTORY_SEPARATOR.'data/HYQingKongTiJ.ttf';
                $image->text($param,$font,20,'#000000')->save($path);
                break;
            case 2:
                $logo_path = Env::get('root_path')  . 'public'.$param;
                $temp_path = pathinfo($logo_path);

                $logo_path1 = $temp_path['dirname'].DIRECTORY_SEPARATOR."m_{$temp_path['basename']}";
                if(is_file($logo_path)){
                    $logo_path = $logo_path1;
                }
                $image->water($logo_path,\think\Image::WATER_SOUTHEAST,50)->save($path);
                break;
        }
    }

	/**
	 * [uploadfile 上传单个文件]
	 * @return [type] [description]
	 */
	public function uploadfile($file='file'){
	    $file = request()->file($file);
	    $path = Env::get('root_path')  . 'public'. DS .'uploads'. DS . 'file';
	    $info = $file->validate(config('UPLOADE_FILE'))->move($path);
	    if($info){
	        $fullPath =  $path.DIRECTORY_SEPARATOR.$info->getSaveName().'|'.$info->getinfo()['name'];
	        return $fullPath;
	    }else{
	        return $file->getError();
	    }
	}

	/**
	 * [uploads 上传多个文件]
	 * @param  string $file [接收字段]
	 * @return [type]       [description]
	 */
	public function uploads($file='image'){
	    // 获取表单上传文件
	    $files = request()->file($file);
	    $_result=[];
	    $path = Env::get('root_path')  . 'public' . DS . 'uploads'. DS . 'file';
	    foreach($files as $file){
	        $info = $file->validate(config('UPLOADE_FILE'))->move($path);
	        if($info){
	            $_result['ext']=$info->getExtension();
	            $_result['file_name']=$info->getFilename();
	            $_result['full_path']=$path. DS .$info->getFilename();
	        }else{
	            return $file->getError();
	        }
	    }
	}

    /**
     * KindEditor编辑器上传文件
     * @param int $size                 logo 尺寸
     * @return \think\response\Json
     */
	public function KindEditorUpload($size=64){

		$file = request()->file('imgFile');
		//上传路径
		if(input('dir')=='image'){
			//图片保存地址
			$path = Env::get('root_path')  . 'public' . DS . 'uploads'. DS . 'KindEditor'. DS .'image';
		}else{
			//文件保存地址
			$path = Env::get('root_path')  . 'public' . DS . 'uploads'. DS . 'KindEditor'. DS .'file';
		}

		//字体地址
		$font = Env::get('root_path')  . 'public' . DS .'static'.DS .'fonts'. DS .'HYQingKongTiJ.ttf';
		//Logo
        $logo_path = Env::get('root_path') .'public'.$this->site['logo'];
        $temp_path = pathinfo($logo_path);
        $logo = $temp_path['dirname'].DIRECTORY_SEPARATOR."m{$size}_{$temp_path['basename']}";

		$info = $file->validate(config('UPLOADE_KINDEDITOR'))->move($path);
        if($info){
        	$fullPath =  DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR."KindEditor".DIRECTORY_SEPARATOR.input('dir').DIRECTORY_SEPARATOR.$info->getSaveName();
        	$path = $path.DIRECTORY_SEPARATOR.$info->getSaveName();
        	switch (input('water')) {
	    		case '0':	//网址水印
	    			$image = \think\Image::open($path);
					// 给原图左上角添加水印并保存water_image.png
					$image->text($this->site['url'],$font,15)->save($path);
					return json(['error'=>0,'url'=>$fullPath]);
	    			break;
	    		case '1':	//图片水印
	    			if(!is_file($logo)){
						return json(['error'=>1,'message'=>'对不起不存在的网站LOGO,请在网站配置中上传','url'=>'']);
	    			}
	    			$image = \think\Image::open($path);
					// 给原图左上角添加透明度为50的水印并保存alpha_image.png
					$image->water($logo,\think\Image::WATER_SOUTHEAST,50)->save($path);
					return json(['error'=>0,'url'=>$fullPath]);
	    			break;
	    		case '2':	//自定义文字
	    			$image = \think\Image::open($path);
					// 给原图左上角添加透明度为50的水印并保存alpha_image.png
					$image->text(input('fonts'),$font,15)->save($path);
					return json(['error'=>0,'url'=>$fullPath]);
	    			break;
	    		case '-1':	//无水印
	    		default:
	    		 	return json(['error'=>0,'url'=>$fullPath]);
	    			break;
	    	}
        }else{
            return json(['error'=>1,'message'=>$file->getError(),'url'=>'']);
        }
	}

	/**
     * 删除文件
     * @param $model
     * @param $where
     * @param $file
     */
    public function delmgByWhere($model,$where,$file){
        $m = $model->where($where)->find();
        $src = $m[$file];
        if(empty($src)){
            return array('status'=>0,'msg'=>'图片地址不能为空');
        }
        if(strpos($src,'.')!==true){
            $src = ".".$src;
        }

        $ii = explode('/', $src);
        $ii[count($ii)-1]="m_".$ii[count($ii)-1];
        $ii1 = implode('/', $ii);

        if(file_exists($src)){
            if(!unlink($src) || !unlink($ii1)){
                return array('status'=>0,'msg'=>'图片删除失败，请重试！');
            }
        }
        if(file_exists($ii1)){
            if(!unlink($ii1)){
                return array('status'=>0,'msg'=>'缩略图删除失败，请重试！');
            }
        }
        return true;
    }
	/**
	 * 删除文件
	 * @param $model
	 * @param $where
	 * @param $file
	 */
	public function delmgByWhere1($model,$where,$file){
		$m = $model->where($where)->find();
		$src = $m[$file];
		$flag = true ;
		if(empty($src)){
			return $flag;
		}

		if(strpos($src,'.')!==true){
			$src = ".".$src;
		}

		$ii = explode('/', $src);

		$ii[count($ii)-1]="m_".$ii[count($ii)-1];
		$ii1 = implode('/', $ii);

		if(file_exists($src)){
			if(!unlink("$src")){
				$flag = false;
			}
		}
		if(file_exists($ii1)){
			if(!unlink("$ii1")){
				$flag = false;
			}
		}
		return $flag;
	}


    /**
     * 删文章除图片
     * @param $model
     * @param $where
     * @param $field
     * @return bool
     */
    public function delArticleImage($model,$where,$field){
        $flag =true;
        $a = $model->where($where)->find();

        if(empty($a) || empty($a[$field])){
            return $flag;
        }
		$content = htmlspecialchars_decode($a[$field]);
        $images = get_images($content);
        foreach ($images[1]  as $k=> $v){
        	if(strpos($v,'Uploads') !== false){
        		$v =".".$v;
				$ii = explode('/', $v);
				$ii[count($ii)-1]="m_".$ii[count($ii)-1];
				$ii1 = implode('/', $ii);
				if(file_exists($v)){
					if(!unlink("$v")){
						$flag = false;
						break;
					}
				}
				if(file_exists($ii1)){
					if(!unlink("$ii1")){
						$flag = false;
						break;
					}
				}
        	}
        }
        return $flag;
    }
	/**
	 * @author 魏巍
	 * @description 删除图集
	 * @model 模型
	 * @where 查询条件
	 * @field 删除字段
	 */
	public function delImageAtlas($model,$where,$field='atlas'){
		$flag =true;
        $a = $model->where($where)->find();

        if(empty($a) || empty($a[$field])){
            return $flag;
        }
		$images = explode(',', $a[$field]);
		foreach ($images  as $k=> $v){
        	if(strpos($v,'Uploads') !== false){
        		$v =".".$v;
				$ii = explode('/', $v);
				$ii[count($ii)-1]="m_".$ii[count($ii)-1];
				$ii1 = implode('/', $ii);
				if(file_exists($v)){
					if(!unlink("$v")){
						$flag = false;
						 break;
					}
				}
				if(file_exists($ii1)){
					if(!unlink($ii1)){
						$flag = false;
						break;
					}
				}
        	}
        }
        return $flag;
	}
	/**
	 * [delmg 删除图片]
	 * @param  integer $src [删除地址]
	 * @return [type]       [description]
	 */
	public function delmg($src=0){
		if(empty($src)){
			return ['status'=>0,'msg'=>'图片地址不能为空'];
		}
		if(strpos($src,'.')!==true){
			$src = ".".$src;
		}
        $ii = explode('/', $src);
        $ii[count($ii)-1]="m_".$ii[count($ii)-1];
        $ii1 = implode('/', $ii);

		if(is_file($src)){
            if(!unlink($src)){
                return ['status'=>0,'msg'=>'删除失败请重试'];
            }
        }
		if(is_file($ii1)){
            if(!unlink($ii1)){
                return ['status'=>0,'msg'=>'删除失败请重试'];
            }
        }

		return ['status'=>1,'msg'=>'删除成功！'];
	}
}
