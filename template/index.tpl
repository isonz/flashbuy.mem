<{include file="header.tpl"}>
<div style="position:absolute; top:47px; left:5px; bottom:10px; right:5px; width:auto; z-index:5;">
<div class="showInfo">
	<div class="notice_name"<{if $to_user.username neq ''}> style="display:block"<{/if}>>客服 <em><{$to_user.nickname}></em> 为您服务！</div>
	<div class="notice"<{if $to_user.username eq ''}> style="display:block"<{/if}>>所有的客服都不在线，请留言，我们会尽快回复您。</div>
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
<script type="text/javascript">var O=<{$organize}>;var U='<{$u}>';var R='<{$r}>';var toUser='<{$to_user.username}>';</script>
<{include file="footer.tpl"}>
