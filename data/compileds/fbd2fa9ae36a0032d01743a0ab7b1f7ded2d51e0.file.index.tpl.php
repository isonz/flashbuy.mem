<?php /* Smarty version Smarty-3.0.7, created on 2014-09-04 04:08:07
         compiled from "E:\Site\flashbuy.ptp.cn\template\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:188665407e5a70a4250-87186708%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fbd2fa9ae36a0032d01743a0ab7b1f7ded2d51e0' => 
    array (
      0 => 'E:\\Site\\flashbuy.ptp.cn\\template\\index.tpl',
      1 => 1408094924,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '188665407e5a70a4250-87186708',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php $_template = new Smarty_Internal_Template("header.tpl", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
<div style="position:absolute; top:47px; left:5px; bottom:10px; right:5px; width:auto; z-index:5;">
<div class="showInfo">
	<div class="notice_name"<?php if ($_smarty_tpl->getVariable('to_user')->value['username']!=''){?> style="display:block"<?php }?>>客服 <em><?php echo $_smarty_tpl->getVariable('to_user')->value['nickname'];?>
</em> 为您服务！</div>
	<div class="notice"<?php if ($_smarty_tpl->getVariable('to_user')->value['username']==''){?> style="display:block"<?php }?>>所有的客服都不在线，请留言，我们会尽快回复您。</div>
    <div id="show"></div>
</div>

<div class="emotiondiv"><span class="emotion">表情</span></div>
<div class="comment">
	<div class="com_form">
    	<textarea class="input" id="saytext" name="saytext"></textarea>
        <p style="text-align:right; display:block;"><input type="button" class="sub_btn" value="提交"></p>
    </div>
</div>

</div>
<script type="text/javascript">var O=<?php echo $_smarty_tpl->getVariable('organize')->value;?>
;var U='<?php echo $_smarty_tpl->getVariable('u')->value;?>
';var R='<?php echo $_smarty_tpl->getVariable('r')->value;?>
';var toUser='<?php echo $_smarty_tpl->getVariable('to_user')->value['username'];?>
';</script>
<?php $_template = new Smarty_Internal_Template("footer.tpl", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php unset($_template);?>
