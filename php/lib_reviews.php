<?php
$_LANG['no_privelage'] = '没有权限评论该商品';
$_LANG['save_comment_tips'] = '您的评价提交成功,感谢您的参与';
$_LANG['comment_question'] = '评论与咨询';
$_LANG['upload_photo_success'] = '会员头像设置成功';
$_LANG['back_user_center'] = '返回用户中心';
$_LANG['upload_photo1'] = '只能上传GIF,JPG,PNG,BMP';
$_LANG['no_title'] = '评价标题没有填写完整!';
$_LANG['no_impression'] = '请填写您对我们商品的印象!';

/**
 * 查询评论内容
 *
 * @access  public
 * @params  integer     $id
 * @params  integer     $type
 * @params  integer     $page
 * @return  array
 */
function assign_comment_1($id, $type, $page = 1,$comment_rank=0)
{
	 /* 取得评论列表 */
	$where  = " where 1 and c.comment_type = $type AND c.status = 1 AND c.parent_id = 0";
	if(!empty($id))
	{
		$where .=" and c.id_value='$id'";
	}

	
	//好评
	if($comment_rank==3)
	{
		$where .=" and c.comment_rank in(4,5)";
	}
	elseif($comment_rank==2)
	{
		$where .=" and c.comment_rank in(2,3)";
	}
	elseif($comment_rank==1)
	{
		$where .=" and c.comment_rank in(1,0)";
	}
	
	
	$count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment').
           "  as c $where");
    $size  = !empty($GLOBALS['_CFG']['comments_number']) ? $GLOBALS['_CFG']['comments_number'] : 5;

    $page_count = ($count > 0) ? intval(ceil($count / $size)) : 1;
   


    $sql = 'SELECT c.*,u.photo,u.user_rank,u.rank_points,g.goods_id,g.goods_thumb,g.goods_name,shop_price,count(re.comment_id) as replyCount FROM ' . $GLOBALS['ecs']->table('comment') . " as c ".
		   ' left join '.$GLOBALS['ecs']->table('users'). ' u on u.user_id=c.user_id '.
		   ' left join '.$GLOBALS['ecs']->table('goods'). ' g on g.goods_id=c.id_value '.
		   ' left join '.$GLOBALS['ecs']->table('comment'). ' re on re.parent_id=c.comment_id and re.status=1 '.
            " $where".
            " group by c.comment_id ORDER BY add_time desc";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page-1) * $size);


  

    $arr = array();
    $ids = '';
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $ids .= $ids ? ",$row[comment_id]" : $row['comment_id'];
        
		$arr[$row['comment_id']]['comment_id']       = $row['comment_id'];
		$arr[$row['comment_id']]['id']       = $row['comment_id'];
        $arr[$row['comment_id']]['email']    = $row['email'];
		$row['user_name'] = str_replace("&lt;","<",$row['user_name']);
		$row['user_name'] = str_replace("&gt;",">",$row['user_name']);
		
		
        $row['user_name'] = strip_tags($row['user_name']);
		
		if(!empty($row['user_name']))
		{
		 $arr[$row['comment_id']]['username'] = mb_substr($row['user_name'],0,1,EC_CHARSET).'***'.mb_substr($row['user_name'],strlen($row['user_name'])-1,strlen($row['user_name'])+1,EC_CHARSET);
		}

		
		if($row['user_id']>0)
		{
			if ($row['user_rank'] == 0)
			{
				$sql = 'SELECT rank_name,rank_id FROM ' . $GLOBALS['ecs']->table('user_rank') . " WHERE special_rank = '0' AND min_points <= " . intval($row['rank_points']) . ' AND max_points > ' . intval($row['rank_points']);
			}
			else
			{
				$sql = 'SELECT rank_name,rank_id FROM ' . $GLOBALS['ecs']->table('user_rank') . " WHERE rank_id = '$row[user_rank]'";
			}
			$rank_info = $GLOBALS['db']->getRow($sql);
			$arr[$row['comment_id']]['rank_name'] = $rank_info['rank_name'];
			$arr[$row['comment_id']]['user_rank'] = $rank_info['rank_id'];
		}
		
		if(!empty($row['photo']))
		{
		    $arr[$row['comment_id']]['photo'] = $row['photo'];
		}
		else
		{
			$arr[$row['comment_id']]['photo'] = "data/user_photos/default-photo.jpg";
		}
		
		$arr[$row['comment_id']]['photo1'] = $row['photo1'];
		$arr[$row['comment_id']]['photo2'] = $row['photo2'];
		$arr[$row['comment_id']]['photo3'] = $row['photo3'];
		$arr[$row['comment_id']]['photo4'] = $row['photo4'];
		$arr[$row['comment_id']]['photo5'] = $row['photo5'];
	    $arr[$row['comment_id']]['photo1_thumb'] = $row['photo1_thumb'];
		$arr[$row['comment_id']]['photo2_thumb'] = $row['photo2_thumb'];
		$arr[$row['comment_id']]['photo3_thumb'] = $row['photo3_thumb'];
		$arr[$row['comment_id']]['photo4_thumb'] = $row['photo4_thumb'];
		$arr[$row['comment_id']]['photo5_thumb'] = $row['photo5_thumb'];
		
        $arr[$row['comment_id']]['content']  = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));
        $arr[$row['comment_id']]['content']  = nl2br(str_replace('\n', '<br />', $arr[$row['comment_id']]['content']));
        $arr[$row['comment_id']]['rank']     = $row['comment_rank'];
        $arr[$row['comment_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
		
		$arr[$row['comment_id']]['impression']    = $row['impression'];
		$arr[$row['comment_id']]['support']       = $row['support'];
		$arr[$row['comment_id']]['unsupport']     = $row['unsupport'];
		
		
		$arr[$row['comment_id']]['url']      = reviews_build_uri('viewreviews', array('review_id' => $row['comment_id']));
		
    }
    /* 取得已有回复的评论 */
/* 取得已有回复的评论 */
    if ($ids)
    {
        $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') .
                " WHERE parent_id IN( $ids )";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetch_array($res))
        {
            $arr[$row['parent_id']]['re_content']  = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));
            $arr[$row['parent_id']]['re_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
            $arr[$row['parent_id']]['re_email']    = $row['email'];
            $arr[$row['parent_id']]['re_username'] = $row['user_name'];
        }
    }
	
    /* 分页样式 */
    //$pager['styleid'] = isset($GLOBALS['_CFG']['page_style'])? intval($GLOBALS['_CFG']['page_style']) : 0;
    $pager['page']         = $page;
    $pager['size']         = $size;
    $pager['record_count'] = $count;
    $pager['page_count']   = $page_count;
    $pager['page_first']   = "javascript:gotoPage(1,$id,$type)";
    $pager['page_prev']    = $page > 1 ? "javascript:gotoPage(" .($page-1). ",$id,$type,$comment_rank)" : 'javascript:;';
    $pager['page_next']    = $page < $page_count ? 'javascript:gotoPage(' .($page + 1) . ",$id,$type,$comment_rank)" : 'javascript:;';
    $pager['page_last']    = $page < $page_count ? 'javascript:gotoPage(' .$page_count. ",$id,$type,$comment_rank)"  : 'javascript:;';

    for($i = 1; $i <= $page_count; ++ $i) {
	   $pager['array'][$i]['item_url'] = 'javascript:gotoPage(' .$i. ",$id,$type,$comment_rank)";
	   
    }
	
	

    $cmt = array('comments' => $arr, 'pager' => $pager);

    return $cmt;
}

/**
 * 调用评论信息
 *
 * @access  public
 * @return  string
 */
function insert_comments_1($arr)
{
    $need_cache = $GLOBALS['smarty']->caching;
    $need_compile = $GLOBALS['smarty']->force_compile;

    $GLOBALS['smarty']->caching = false;
    $GLOBALS['smarty']->force_compile = true;

    /* 验证码相关设置 */
    if ((intval($GLOBALS['_CFG']['captcha']) & CAPTCHA_COMMENT) && gd_version() > 0)
    {
        $GLOBALS['smarty']->assign('enabled_captcha', 1);
        $GLOBALS['smarty']->assign('rand', mt_rand());
    }
    $GLOBALS['smarty']->assign('username',     stripslashes($_SESSION['user_name']));
    $GLOBALS['smarty']->assign('email',        $_SESSION['email']);
    $GLOBALS['smarty']->assign('comment_type', $arr['type']);
    $GLOBALS['smarty']->assign('id',           $arr['id']);
    $cmt = assign_comment_1($arr['id'],          $arr['type']);
    $GLOBALS['smarty']->assign('comments',     $cmt['comments']);
    $GLOBALS['smarty']->assign('pager',        $cmt['pager']);


    $goods_id = $arr['id'];
	
	$GLOBALS['smarty']->assign('by_allreviews',   reviews_build_uri('reviews_list', array('goods_id' => $goods_id)));//全部评价

    /* 取得评论列表 */
    $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment').
           " WHERE id_value = '$goods_id' AND comment_type = 0 AND status = 1 AND parent_id = 0");
    $count_hao = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment').
           " WHERE id_value = '$goods_id' AND comment_type = 0 AND status = 1 AND comment_rank = 5 AND parent_id = 0");
    $count_mid = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment').
           " WHERE id_value = '$goods_id' AND comment_type = 0 AND status = 1 AND comment_rank BETWEEN 2 AND 4 AND parent_id = 0");
    $count_cha = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment').
           " WHERE id_value = '$goods_id' AND comment_type = 0 AND status = 1 AND comment_rank = 1 AND parent_id = 0");
	
     $percent['hp'] = ($count_hao > 0) ? percent($count_hao,$count) : 0;
     $percent['zp'] = ($count_mid > 0) ? percent($count_mid,$count) : 0;
     $percent['cp'] = ($count_cha > 0) ? percent($count_cha,$count) : 0;
     $GLOBALS['smarty']->assign('percent',         $percent);
	 
	 $reviews_count['all'] = insert_reviews_total_count(array('goods_id'=>$goods_id,'comment_rank'=>0));
	 $reviews_count['hp'] = insert_reviews_total_count(array('goods_id'=>$goods_id,'comment_rank'=>3));
	 $reviews_count['zp'] = insert_reviews_total_count(array('goods_id'=>$goods_id,'comment_rank'=>2));
	 $reviews_count['cp'] = insert_reviews_total_count(array('goods_id'=>$goods_id,'comment_rank'=>1));
	 
	 
	 $GLOBALS['smarty']->assign('reviews_count',         $reviews_count);
	 
	/*获得前五位获得积分的用户*/
	$sql = 'SELECT user_name,user_id, points FROM ' .$GLOBALS['ecs']->table('comment').
           " WHERE id_value = '$goods_id' AND comment_type = 0 AND status = 1 AND parent_id = 0 and is_point=1 and points>0 and user_id>0 limit 5";
	
	
	$get_points_users = $GLOBALS['db']->getAll($sql);
	
	
	foreach($get_points_users as $key=>$row)
	{
		//$get_points_users[$key]['user_url'] = reviews_build_uri('userreviews', array('user_id' => $row['user_id']));
	}
	
	$GLOBALS['smarty']->assign('points_users', $get_points_users);  
	

    $val = $GLOBALS['smarty']->fetch('library/comments_list.lbi');

    $GLOBALS['smarty']->caching = $need_cache;
    $GLOBALS['smarty']->force_compile = $need_compile;

    return $val;
}

function get_comment_list($para=array(),$size, $page, $sort='comment_id', $order='DESC',$comment_rank=0)
{
    
	if($sort=='comment_id')
	{
	   $sort = 'add_time';	
	}
	
	$where  = ' where 1 and c.comment_type = 0 AND c.status = 1 AND c.parent_id = 0';
	if(!empty($para['goods_id']))
	{
		$where .=" and c.id_value=$para[goods_id]";
	}
	if(!empty($para['user_id']))
	{
		$where .=" and c.user_id=$para[user_id]";
	}
	
	//好评
	//好评
	if($comment_rank==3)
	{
		$where .=" and c.comment_rank in(4,5)";
	}
	elseif($comment_rank==2)
	{
		$where .=" and c.comment_rank in(2,3)";
	}
	elseif($comment_rank==1)
	{
		$where .=" and c.comment_rank in(1,0)";
	}
	
	if(!empty($GLOBALS['best_reviews']))
	{
		$where .=" and c.comment_rank(4,5)";
	}
	 /* 取得评论列表 */

	
	
    $sql = 'SELECT c.*,u.photo,u.user_id,u.user_rank,u.photo, u.rank_points,g.goods_id,g.goods_thumb,g.goods_name,shop_price,count(re.comment_id) as replyCount FROM ' . $GLOBALS['ecs']->table('comment') . " as c ".
		   ' left join '.$GLOBALS['ecs']->table('users'). ' u on u.user_id=c.user_id '.
		   ' left join '.$GLOBALS['ecs']->table('goods'). ' g on g.goods_id=c.id_value '.
		   ' left join '.$GLOBALS['ecs']->table('comment'). ' re on re.parent_id=c.comment_id and re.status = 1  '.
            " $where".
            " group by c.comment_id ORDER BY $sort $order";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page-1) * $size);

    $arr = array();
    $ids = '';
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $ids .= $ids ? ",$row[comment_id]" : $row['comment_id'];
        $arr[$row['comment_id']]['id']       = $row['comment_id'];
		$arr[$row['comment_id']]['comment_id']       = $row['comment_id'];
		$arr[$row['comment_id']]['replyCount']       = $row['replyCount'];
		$arr[$row['comment_id']]['support']       = $row['support'];
		$arr[$row['comment_id']]['unsupport']       = $row['unsupport'];
        $arr[$row['comment_id']]['email']    = $row['email'];
		
        if(!empty($row['user_name']))
		{
		 $arr[$row['comment_id']]['username'] = mb_substr($row['user_name'],0,1,EC_CHARSET).'***'.mb_substr($row['user_name'],strlen($row['user_name'])-1,strlen($row['user_name'])+1,EC_CHARSET);
		}
		
		
		$arr[$row['comment_id']]['user_id'] = $row['user_id'];
		$arr[$row['comment_id']]['user_url']      = reviews_build_uri('userreviews', array('user_id' => $row['user_id']));
		$arr[$row['comment_id']]['url']      = reviews_build_uri('viewreviews', array('review_id' => $row['comment_id']));
		
	
		$arr[$row['comment_id']]['photo1'] = $row['photo1'];
		$arr[$row['comment_id']]['photo2'] = $row['photo2'];
		$arr[$row['comment_id']]['photo3'] = $row['photo3'];
		$arr[$row['comment_id']]['photo4'] = $row['photo4'];
		$arr[$row['comment_id']]['photo5'] = $row['photo5'];
	    $arr[$row['comment_id']]['photo1_thumb'] = $row['photo1_thumb'];
		$arr[$row['comment_id']]['photo2_thumb'] = $row['photo2_thumb'];
		$arr[$row['comment_id']]['photo3_thumb'] = $row['photo3_thumb'];
		$arr[$row['comment_id']]['photo4_thumb'] = $row['photo4_thumb'];
		$arr[$row['comment_id']]['photo5_thumb'] = $row['photo5_thumb'];
	  
	    
	    $arr[$row['comment_id']]['goods_thumb']  = get_image_path($row['goods_id'], $row['goods_thumb'], true);
	    $arr[$row['comment_id']]['goods_name']   = $row['goods_name'];
            //update tangw 20140812
	    $arr[$row['comment_id']]['goods_url']    = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']); //build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
	    $arr[$row['comment_id']]['shop_price']   = price_format($row['shop_price']);
		
		if($row['user_id']>0)
		{
			if ($row['user_rank'] == 0)
			{
				$sql = 'SELECT rank_name,rank_id FROM ' . $GLOBALS['ecs']->table('user_rank') . " WHERE special_rank = '0' AND min_points <= " . intval($row['rank_points']) . ' AND max_points > ' . intval($row['rank_points']);
			}
			else
			{
				$sql = 'SELECT rank_name,rank_id FROM ' . $GLOBALS['ecs']->table('user_rank') . " WHERE rank_id = '$row[user_rank]'";
			}
			$rank_info = $GLOBALS['db']->getRow($sql);
			$arr[$row['comment_id']]['rank_name'] = $rank_info['rank_name'];
			$arr[$row['comment_id']]['user_rank'] = $rank_info['rank_id'];
		}
		
		if(!empty($row['photo']))
		{
		    $arr[$row['comment_id']]['photo'] = $row['photo'];
		}
		else
		{
			$arr[$row['comment_id']]['photo'] = "data/user_photos/default-photo.jpg";
		}
		
        $arr[$row['comment_id']]['content']  = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));
        $arr[$row['comment_id']]['content']  = nl2br(str_replace('\n', '<br />', $arr[$row['comment_id']]['content']));
		$arr[$row['comment_id']]['content'] = strip_tags($arr[$row['comment_id']]['content']);
        $arr[$row['comment_id']]['rank']     = $row['comment_rank'];
        $arr[$row['comment_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
    }
   /* 取得已有回复的评论 */
    if ($ids)
    {
        $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') .
                " WHERE parent_id IN( $ids )";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetch_array($res))
        {
            $arr[$row['parent_id']]['re_content']  = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));
            $arr[$row['parent_id']]['re_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
            $arr[$row['parent_id']]['re_email']    = $row['email'];
            $arr[$row['parent_id']]['re_username'] = $row['user_name'];
        }
    }
	
    /* ·ÖÒ³ÑùÊ½ */
    return $arr;
}


function get_reviews_count($para=array(),$comment_rank=0)
{
    $where  = ' where 1 and c.comment_type = 0 AND c.status = 1 AND c.parent_id = 0';
	if(!empty($para['goods_id']))
	{
		$where .=" and c.id_value=$para[goods_id]";
	}
	if(!empty($para['user_id']))
	{
		$where .=" and c.user_id=$para[user_id]";
	}
	
	//好评
	if($comment_rank==3)
	{
		$where .=" and c.comment_rank in(4,5)";
	}
	elseif($comment_rank==2)
	{
		$where .=" and c.comment_rank in(2,3)";
	}
	elseif($comment_rank==1)
	{
		$where .=" and c.comment_rank in(1,0)";
	}
	
	if(!empty($GLOBALS['best_reviews']))
	{
		$where .=" and c.comment_rank in(4,5)";
	}
	
    $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment')." as c $where");
    return $count;
}

function get_reviews_details($comment_id=0)
{
    
	 $sql = 'SELECT c.*,u.photo,u.user_rank,u.rank_points,g.goods_id,g.goods_thumb,g.goods_name,g.shop_price FROM ' . $GLOBALS['ecs']->table('comment') . " as c ".
		   ' left join '.$GLOBALS['ecs']->table('users'). ' u on u.user_id=c.user_id '.
		   ' left join '.$GLOBALS['ecs']->table('goods'). ' g on g.goods_id=c.id_value '.
            " WHERE  c.comment_type = 0 AND c.status = 1 AND c.parent_id = 0 and c.comment_id='".$comment_id."'";
			
	 $row = 	$GLOBALS['db']->getRow($sql);
	
	 $row['content']  = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));
     $row['content']  = nl2br(str_replace('\n', '<br />', $row['content']));
	 $row['content'] = strip_tags($row['content']);
	 $row['goods_thumb']  = get_image_path($row['goods_id'], $row['goods_thumb'], true);
	 $row['goods_name']   = $row['goods_name'];
	 //add tangw 2014-08-12
	 $row['goods_url']    = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
	 
	 //build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
	 $row['shop_price']   = price_format($row['shop_price']);
	 $row['rank']     = $row['comment_rank'];
	 $row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
	 
	 if($row['user_id']>0)
		{
			if ($row['user_rank'] == 0)
			{
				$sql = 'SELECT rank_name,rank_id FROM ' . $GLOBALS['ecs']->table('user_rank') . " WHERE special_rank = '0' AND min_points <= " . intval($row['rank_points']) . ' AND max_points > ' . intval($row['rank_points']);
			}
			else
			{
				$sql = 'SELECT rank_name,rank_id FROM ' . $GLOBALS['ecs']->table('user_rank') . " WHERE rank_id = '$row[user_rank]'";
			}
			$rank_info = $GLOBALS['db']->getRow($sql);
			$row['rank_name'] = $rank_info['rank_name'];
			$row['user_rank'] = $rank_info['rank_id'];
		}
		
		if(!empty($row['photo']))
		{
		    $row['photo'] = $row['photo'];
		}
		else
		{
			$row['photo'] = "themes/".$GLOBALS['_CFG']['template']."/images/reviews/rank_".$row['user_rank'].".gif";
		}

	 return $row;
}

function get_comment_reply_list($comment_id,$size, $page, $sort='comment_id', $order='DESC')
{
    
    $sql = 'SELECT c.*,u.photo,u.user_rank,u.rank_points FROM ' . $GLOBALS['ecs']->table('comment') . " as c ".
		   ' left join '.$GLOBALS['ecs']->table('users'). ' u on u.user_id=c.user_id '.
            " WHERE  c.comment_type = 0 AND c.status = 1 AND c.parent_id = '$comment_id'".
            " ORDER BY $sort $order";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page-1) * $size);

    $arr = array();
    $ids = '';
	$i=1;
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$i]['id']       = $row['comment_id'];
        $arr[$i]['email']    = $row['email'];
        $arr[$i]['username'] = $row['user_name'];
		$arr[$i]['user_id'] = $row['user_id'];
        $arr[$i]['content']  = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));
        $arr[$i]['content']  = nl2br(str_replace('\n', '<br />', $arr[$i]['content']));
		$arr[$i]['content'] = strip_tags($arr[$i]['content']);
        $arr[$i]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
		$i++;
    }
    /* 分页样式 */
    return $arr;
}

function get_reply_count($comment_id)
{
    $where  = "";
    $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment')."  as c WHERE comment_type = 0 AND status = 1 AND parent_id='$comment_id'");
    return $count;
}

function insert_reviews_total_count($arr)
{
  
    $goods_id = $arr['goods_id'];
	$comment_rank = $arr['comment_rank'];
	$user_id = $arr['user_id'];
	
	
	$where  = ' where 1 and c.comment_type = 0 AND c.status = 1 AND c.parent_id = 0';
	if(!empty($goods_id))
	{
		$where .=" and c.id_value=$goods_id";
	}
	if(!empty($user_id))
	{
		$where .=" and c.user_id='$user_id'";
	}
	
	//好评
	if($comment_rank==3)
	{
		$where .=" and c.comment_rank in(4,5)";
	}
	elseif($comment_rank==2)
	{
		$where .=" and c.comment_rank in(2,3)";
	}
	elseif($comment_rank==1)
	{
		$where .=" and c.comment_rank in(1,0)";
	}
	
	
	
	
	$db = $GLOBALS['db'];
	$ecs = $GLOBALS['ecs'];
	$goods_id = $arr['id'];
	
	$comment_total_count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment').
	   " as c $where");    
    return $comment_total_count;
}

function insert_reply_count($arr)
{
    $where  = "";
    $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment')." as c WHERE comment_type = 0 AND status = 1 AND parent_id = 0 and parent_id='$arr[comment_id]'");
    return $count;
}


/**
 * 取得当前位置和页面标题
 *
 * @access  public
 * @param   integer     $cat    分类编号（只有商品及分类、文章及分类用到）
 * @param   string      $str    商品名、文章标题或其他附加的内容（无链接）
 * @return  array
 */
function assign_reviews_ur_here($cat = 0, $goods_id=0, $str = '')
{
    /* 判断是否重写，取得文件名 */
    $cur_url = basename(PHP_SELF);
    if (intval($GLOBALS['_CFG']['rewrite']))
    {
        $filename = strpos($cur_url,'-') ? substr($cur_url, 0, strpos($cur_url,'-')) : substr($cur_url, 0, -4);
    }
    else
    {
        $filename = substr($cur_url, 0, -4);
    }

    /* 初始化“页面标题”和“当前位置” */
    $page_title = $GLOBALS['_CFG']['shop_title'] . ' - ' . '';
    $ur_here    = '<a href=".">' . $GLOBALS['_LANG']['home'] . '</a>';

    /* 根据文件名分别处理中间的部分 */
    if ($filename != 'index')
    {
        /* 处理有分类的 */
        if (in_array($filename, array('viewreviews','reviews_list')))
        {
           
			/* 商品评论　 */
            if ('viewreviews' == $filename || 'reviews_list' == $filename)
            {
                if ($cat > 0)
                {
                    $cat_arr = get_parent_cats($cat);
                    $key     = 'bid';
                    $type    = 'category';
                }
                else
                {
                    $cat_arr = array();
                }
            }
           
            /* 循环分类 */
            if (!empty($cat_arr))
            {
                krsort($cat_arr);
                foreach ($cat_arr AS $val)
                {
                    $page_title = htmlspecialchars($val['cat_name']) . '_' . $page_title;
                    $args       = array($key => $val['cat_id']);
                    $ur_here   .= ' <code>&gt;</code> <a href="' . build_uri($type, $args, $val['cat_name']) . '">' .
                                    htmlspecialchars($val['cat_name']) . '</a>';
                }
            }
        }
        /* 处理无分类的 */
        else
        {
			if ('allreviews' == $filename)
            {
                $page_title = $GLOBALS['_LANG']['allreviews'] . '_' . $page_title;
                $args       = array('wsid' => '0');
                $ur_here   .= ' <code>&gt;</code> <a href="' . reviews_build_uri('reviews') . '">' .
                                $GLOBALS['_LANG']['allreviews'] . '</a>';
            }
			if ('alltradereviews' == $filename)
            {
                $page_title = $GLOBALS['_LANG']['alltradereviews'] . '_' . $page_title;
                $args       = array('wsid' => '0');
                $ur_here   .= ' <code>&gt;</code> <a href="' . reviews_build_uri('alltradereviews') . '">' .
                                $GLOBALS['_LANG']['alltradereviews'] . '</a>';
            }
			if ('userreviews' == $filename)
            {
                $page_title = $GLOBALS['_LANG']['allreviews'] . '_' . $page_title;
                $args       = array('wsid' => '0');
                $ur_here   .= ' <code>&gt;</code> <a href="' . reviews_build_uri('reviews') . '">' .
                                $GLOBALS['_LANG']['allreviews'] . '</a>';
            }
        }
    }
	
	  
	
	if(!empty($goods_id))
	{
		
		  $sql = 'SELECT g.goods_id,g.goods_name FROM ' . $GLOBALS['ecs']->table('goods') . " AS g WHERE g.goods_id = '$goods_id' AND g.is_delete = 0 GROUP BY g.goods_id";
		  $goods_info = $GLOBALS['db']->getRow($sql);
		  $goods_info['goods_url']    = reviews_build_uri('reviews_list', array('goods_id' => $goods_info['goods_id']));
		  $page_title  = $goods_info['goods_name'] . '_' . $page_title;
          $ur_here    .= ' <code>&gt;</code> <a href="' . $goods_info['goods_url'] . '">' . $goods_info['goods_name'] . '...评论</a>';
	}

    /* 处理最后一部分 */
    if (!empty($str))
    {
        $page_title  = $str . '_' . $page_title;
        $ur_here    .= ' <code>&gt;</code> ' . $str;
    }

    /* 返回值 */
    return array('title' => $page_title, 'ur_here' => $ur_here);
}

function percent($p,$t){
 return sprintf('%.0f',$p/$t*100);
}

/**
 * 重写 URL 地址
 *
 * @access  public
 * @param   string  $app        执行程序
 * @param   array   $params     参数数组
 * @param   string  $append     附加字串
 * @param   integer $page       页数
 * @param   string  $keywords   搜索关键词字符串
 * @return  void
 */
function reviews_build_uri($app, $params=array(), $append = '', $page = 0, $keywords = '', $size = 0)
{
    static $rewrite = NULL;

    if ($rewrite === NULL)
    {
        $rewrite = intval($GLOBALS['_CFG']['rewrite']);
    }

    $args = array('cid'   => 0,
                  'gid'   => 0,
                  'bid'   => 0,
                  'acid'  => 0,
                  'aid'   => 0,
                  'sid'   => 0,
                  'gbid'  => 0,
                  'auid'  => 0,
                  'sort'  => '',
                  'order' => '',
                );

    extract(array_merge($args, $params));
$rewrite = 0;
    $uri = '';
    switch ($app)
    {
		/*wzsy增加代码*/
		case 'reviews':
                if ($rewrite)
                {
                    $uri = 'allreviews';
                    if (!empty($page))
                    {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '-' . $order;
                    }
                    if (!empty($keywords))
                    {
                        $uri .= '-' . $keywords;
                    }
                }
                else
                {
                    $uri = 'allreviews.php?act=all';
                    if (!empty($page))
                    {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '&amp;order=' . $order;
                    }
                }
            break;
	     
		 /*wzsy增加代码*/
		 case 'alltradereviews':
                if ($rewrite)
                {
                    $uri = 'alltradereviews';
                    if (!empty($page))
                    {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '-' . $order;
                    }
                    if (!empty($keywords))
                    {
                        $uri .= '-' . $keywords;
                    }
                }
                else
                {
                    $uri = 'alltradereviews.php?act=all';
                    if (!empty($page))
                    {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '&amp;order=' . $order;
                    }
                }
            break;
		
		 case 'reviews_list':
		        if(empty($goods_id))
				{
					return '';
				}
                if ($rewrite)
                {
                    $uri = 'reviews-list-' . $goods_id;
                    if (!empty($page))
                    {
                        $uri .= '-p' . $page;
                    }
					if (!empty($comment_rank))
                    {
                        $uri .= '-' . $comment_rank;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '-' . $order;
                    }
                }
                else
                {
                    $uri = 'reviews_list.php?goods_id='.$goods_id;
                    if (!empty($page))
                    {
                        $uri .= '&amp;page=' . $page;
                    }
					if (!empty($comment_rank))
                    {
                        $uri .= '&amp;comment_rank=' . $comment_rank;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '&amp;order=' . $order;
                    }
                }
            break;
		
		case 'userreviews':
		        if(empty($user_id))
				{
					return '';
				}
                if ($rewrite)
                {
                    $uri = 'userreviews-' . $user_id;
                    if (!empty($page))
                    {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '-' . $order;
                    }
                }
                else
                {
                    $uri = 'userreviews.php?user_id='.$user_id;
                    if (!empty($page))
                    {
                        $uri .= '&amp;page=' . $page;
                    }
					if (!empty($comment_rank))
                    {
                        $uri .= '&amp;comment_rank=' . $comment_rank;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '&amp;order=' . $order;
                    }
                }
            break;
		 case 'viewreviews':
		 
		        if (empty($review_id))
				{
					return false;
				}
                if ($rewrite)
                {
                    $uri = 'viewreviews-' . $review_id;
                    if (!empty($page))
                    {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '-' . $order;
                    }
                }
                else
                {
                    $uri = 'viewreviews.php?review_id='.$review_id;
                    if (!empty($page))
                    {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty($sort))
                    {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order))
                    {
                        $uri .= '&amp;order=' . $order;
                    }
                }
            break;
		
		case 'consultations':
		 
		        if (empty($goods_id))
				{
					return false;
				}
                if ($rewrite)
                {
                    $uri = 'allconsultations-' . $goods_id;
					if (!empty($type))
                    {
                        $uri .= '-' . $type;
                    }
                    if (!empty($page))
                    {
                        $uri .= '-p' . $page;
                    }
					if (!empty($keywords))
                    {
                        $uri .= '-s_' . $keywords;
                    }
					
                }
                else
                {
                    $uri = 'allconsultations.php?goods_id='.$goods_id;
                    if (!empty($page))
                    {
                        $uri .= '&amp;page=' . $page;
                    }
                }
            break;
		
		/*wzsy增加代码 end*/
		
        default:
            return false;
            break;
    }

    if ($rewrite)
    {
        if ($rewrite == 2 && !empty($append))
        {
            $uri .= '-' . urlencode(preg_replace('/[\.|\/|\?|&|\+|\\\|\'|"|,]+/', '', $append));
        }

        $uri .= '.html';
    }
    if (($rewrite == 2) && (strpos(strtolower(EC_CHARSET), 'utf') !== 0))
    {
        $uri = urlencode($uri);
    }
    return $uri;
}

function reviews_assign_pager($app, $cat, $record_count, $size, $sort, $order, $page = 1, $keywords = '', $brand = 0, $price_min = 0, $price_max = 0, $display_type = 'list', $filter_attr = '', $url_format = '', $sch_array = '') {
	$sch = array ('keywords' => $keywords, 'sort' => $sort, 'order' => $order, 'cat' => $cat, 'brand' => $brand, 'price_min' => $price_min, 'price_max' => $price_max, 'filter_attr' => $filter_attr, 'display' => $display_type );
	
	$page = intval ( $page );
	if ($page < 1) {
		$page = 1;
	}
	
	$page_count = $record_count > 0 ? intval ( ceil ( $record_count / $size ) ) : 1;
	
	$pager ['page'] = $page;
	$pager ['size'] = $size;
	$pager ['sort'] = $sort;
	$pager ['order'] = $order;
	$pager ['record_count'] = $record_count;
	$pager ['page_count'] = $page_count;
	$pager ['display'] = $display_type;
	
	switch ($app) {
	/*wzys修改过代码*/	
		case 'reviews':
            $uri_args = array('sort' => $sort, 'order' => $order);
            break;	
		case 'alltradereviews':
            $uri_args = array('sort' => $sort, 'order' => $order);
            break;		
		case 'viewreviews':
            $uri_args = array('review_id' => $cat);
			break;
		case 'userreviews':
            $uri_args = array('user_id' => $cat, 'sort' => $sort, 'order' => $order);	
            break;	
		case 'reviews_list':
            $uri_args = array('goods_id' => $cat, 'sort' => $sort, 'order' => $order);	
            break;	
		case 'consultations':
            $uri_args = array('goods_id' => $cat,'type' => $brand);	
            break;	
		/*wzys修改过代码 end*/	
	}
	/* 分页样式 */
	$pager ['styleid'] = isset ( $GLOBALS ['_CFG'] ['page_style'] ) ? intval ( $GLOBALS ['_CFG'] ['page_style'] ) : 0;
	
	$page_prev = ($page > 1) ? $page - 1 : 1;
	$page_next = ($page < $page_count) ? $page + 1 : $page_count;
	if ($pager ['styleid'] == 0) {
		if (! empty ( $url_format )) {
			$pager ['page_first'] = $url_format . 1;
			$pager ['page_prev'] = $url_format . $page_prev;
			$pager ['page_next'] = $url_format . $page_next;
			$pager ['page_last'] = $url_format . $page_count;
		}
		else {
			$pager ['page_first'] = reviews_build_uri ( $app, $uri_args, '', 1, $sch );
			$pager ['page_prev'] = reviews_build_uri ( $app, $uri_args, '', $page_prev );
			$pager ['page_next'] = reviews_build_uri ( $app, $uri_args, '', $page_next );
			$pager ['page_last'] = reviews_build_uri ( $app, $uri_args, '', $page_count );
		}
		$pager ['array'] = array ();
		
		for($i = 1; $i <= $page_count; $i ++) {
			$pager ['array'] [$i] = $i;
		}
	}
	else {
		$_pagenum = 10; // 显示的页码
		$_offset = 2; // 当前页偏移值
		$_from = $_to = 0; // 开始页, 结束页
		if ($_pagenum > $page_count) {
			$_from = 1;
			$_to = $page_count;
		}
		else {
			$_from = $page - $_offset;
			$_to = $_from + $_pagenum - 1;
			if ($_from < 1) {
				$_to = $page + 1 - $_from;
				$_from = 1;
				if ($_to - $_from < $_pagenum) {
					$_to = $_pagenum;
				}
			}
			elseif ($_to > $page_count) {
				$_from = $page_count - $_pagenum + 1;
				$_to = $page_count;
			}
		}
		if (! empty ( $url_format )) {
			$pager ['page_first'] = ($page - $_offset > 1 && $_pagenum < $page_count) ? $url_format . 1 : '';
			$pager ['page_prev'] = ($page > 1) ? $url_format . $page_prev : '';
			$pager ['page_next'] = ($page < $page_count) ? $url_format . $page_next : '';
			$pager ['page_last'] = ($_to < $page_count) ? $url_format . $page_count : '';
			$pager ['page_kbd'] = ($_pagenum < $page_count) ? true : false;
			$pager ['page_number'] = array ();
			for($i = $_from; $i <= $_to; ++ $i) {
				$pager ['page_number'] [$i] = $url_format . $i;
			}
		}
		else {
			$pager ['page_first'] = ($page - $_offset > 1 && $_pagenum < $page_count) ? reviews_build_uri ( $app, $uri_args, '', 1 ) : '';
			$pager ['page_prev'] = ($page > 1) ? reviews_build_uri ( $app, $uri_args, '', $page_prev ) : '';
			$pager ['page_next'] = ($page < $page_count) ? reviews_build_uri ( $app, $uri_args, '', $page_next ) : '';
			$pager ['page_last'] = ($_to < $page_count) ? reviews_build_uri ( $app, $uri_args, '', $page_count ) : '';
			$pager ['page_kbd'] = ($_pagenum < $page_count) ? true : false;
			$pager ['page_number'] = array ();
			for($i = $_from; $i <= $_to; ++ $i) {
				$pager ['page_number'] [$i] = reviews_build_uri ( $app, $uri_args, '', $i );
			}
		}
	}
	if (! empty ( $sch_array )) {
		$pager ['search'] = $sch_array;
	}
	else {
		$pager ['search'] ['category'] = $cat;
		foreach ( $sch as $key => $row ) {
			$pager ['search'] [$key] = $row;
		}
	}
	
	$GLOBALS ['smarty']->assign ( 'pager', $pager );
}

?>