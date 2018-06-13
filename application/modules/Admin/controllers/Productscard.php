<?php

/*
 * 功能：后台中心－卡密管理
 * Author:资料空白
 * Date:20180509
 */

class ProductscardController extends AdminBasicController
{
	private $m_products_card;
	private $m_products;
    public function init()
    {
        parent::init();
		$this->m_products_card = $this->load('products_card');
		$this->m_products = $this->load('products');
    }

    public function indexAction()
    {
        if ($this->AdminUser==FALSE AND empty($this->AdminUser)) {
            $this->redirect("/admin/login");
            return FALSE;
        }

		$data = array();
		$this->getView()->assign($data);
    }

	//ajax
	public function ajaxAction()
	{
        if ($this->AdminUser==FALSE AND empty($this->AdminUser)) {
            $data = array('code' => 1000, 'msg' => '请登录');
			Helper::response($data);
        }
		
		$where = array();
		
		$page = $this->get('page');
		$page = is_numeric($page) ? $page : 1;
		
		$limit = $this->get('limit');
		$limit = is_numeric($limit) ? $limit : 10;
		
		$total=$this->m_products_card->Where($where)->Total();
		
        if ($total > 0) {
            if ($page > 0 && $page < (ceil($total / $limit) + 1)) {
                $pagenum = ($page - 1) * $limit;
            } else {
                $pagenum = 0;
            }
			
            $limits = "{$pagenum},{$limit}";
			$sql ="SELECT p1.*,p2.name FROM `t_products_card` as p1 left join `t_products` as p2 on p1.pid=p2.id Order by p1.id desc LIMIT {$limits}";
			$items=$this->m_products_card->Query($sql);
			
            if (empty($items)) {
                $data = array('code'=>0,'count'=>0,'data'=>array(),'msg'=>'无数据');
            } else {
                $data = array('code'=>0,'count'=>$total,'data'=>$items,'msg'=>'有数据');
            }
        } else {
            $data = array('code'=>0,'count'=>0,'data'=>array(),'msg'=>'无数据');
        }
		Helper::response($data);
	}
	
    public function addAction()
    {
        if ($this->AdminUser==FALSE AND empty($this->AdminUser)) {
            $this->redirect("/admin/login");
            return FALSE;
        }
		$data = array();
		
		$products=$this->m_products->Where(array('auto'=>1))->Order(array('id'=>'DESC'))->Select();
		$data['products'] = $products;
		
		$this->getView()->assign($data);
    }
	public function addajaxAction()
	{
		$method = $this->getPost('method',false);
		$pid = $this->getPost('pid',false);
		$card = $this->getPost('card',false);
		$csrf_token = $this->getPost('csrf_token', false);
		
		$data = array();
		
        if ($this->AdminUser==FALSE AND empty($this->AdminUser)) {
            $data = array('code' => 1000, 'msg' => '请登录');
			Helper::response($data);
        }
		
		if($method AND $pid AND $card AND $csrf_token){
			if ($this->VerifyCsrfToken($csrf_token)) {
				$m=array(
					'pid'=>$pid,
					'card'=>$card,
					'oid'=>0,
					'addtime'=>time(),
				);
				if($method == 'add'){
					$u = $this->m_products_card->Insert($m);
					if($u){
						//更新缓存 
						//$this->m_products_type->getConfig(1);
						$data = array('code' => 1, 'msg' => '新增成功');
					}else{
						$data = array('code' => 1003, 'msg' => '新增失败');
					}
				}else{
					$data = array('code' => 1002, 'msg' => '未知方法');
				}
			} else {
                $data = array('code' => 1001, 'msg' => '页面超时，请刷新页面后重试!');
            }
		}else{
			$data = array('code' => 1000, 'msg' => '丢失参数');
		}
		Helper::response($data);
	}
}