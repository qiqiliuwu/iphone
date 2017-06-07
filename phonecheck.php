<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';
$smarty->assign('action', $action);
assign_template();
$position = assign_ur_here(0, "真伪查询");
$smarty->assign('page_title',       $position['title']);    // 页面标题
$smarty->assign('ur_here',          $position['ur_here']);  // 当前位置
if ($action == 'default')
{
	$smarty->display('phonecheck.dwt');
}
if($action=='search')
{
	$smarty->assign('enabled_mes_captcha', (intval($_CFG['captcha']) & CAPTCHA_MESSAGE));
	$smarty->assign('rand',      mt_rand());
	include_once(ROOT_PATH . 'includes/lib_transaction.php');
	include_once(ROOT_PATH . 'includes/lib_payment.php');
	include_once(ROOT_PATH . 'includes/lib_order.php');
	include_once(ROOT_PATH . 'includes/lib_clips.php');
	
	$code  = isset($_REQUEST['code']) ? trim($_REQUEST['code'])  : '';
	if(empty($code))
	{
		show_message("请输入您的手机IMEI号!");
		exit;
	}
	$where = " where 1 and code='$code'";
	$code_info = $db->getRow("SELECT * FROM " .$ecs->table('code_list'). " as o $where");
	$smarty->assign('code_info',  $code_info);
	$smarty->display('phonecheck.dwt');
}

?>
