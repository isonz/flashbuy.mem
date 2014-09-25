document.write('<iframe src="/user" width="0" height="0" style="display:none" frameborder="0"></iframe>');
setTimeout("autocas()",1000);
function autocas(){
	$.ajax({
        type: 'get',
        data: {},
        url: '/user',
        error: function (XMLHttpRequest, textStatus, errorThrown) {location.href="http://sso.ptp.cn/login?service="+encodeURIComponent("http://"+window.location.host+"/user?type=reurl&url=")+encodeURIComponent(window.location.href);},
        success: function (data) {
        	$("#userinfo").html(data);
        }
   });
}

//---------倒计时
var timer1;
function showtime(){
    var t = $("#desctime").text();
    t = t - 1;
    if(t < 1){
    	startBuyBtn();
    	clearInterval(timer1);
    	$("#desctime").text('');
    }else{
    	$("#desctime").text(t);
    }
}
function getTime(){
	$.getJSON('/etc?type=gettime',function(data){
		var status = data.error;
		var msg = data.msg;
		if(0==status){
			if(msg > 0){
				$("#desctime").html(msg);
				timer1 = setInterval("showtime()",1000);
			}else{
				startBuyBtn();
			}
		}else{
			$("#qiangBtn").text('抢购结束');
		}
	});
}
function startBuyBtn(){
	var id = location.href.split('/')
	id = id[id.length-1];
	$("#qiangBtn").attr('href','/buy?id='+id);
	$("#qiangBtn").text('立即抢购');
}
//-------------- end 

$(function(){
	getTime();

});


function checkInfoComplate(){
	$.getJSON('/user?type=checkinfo',function(data){
		var msg = data.msg;
		if(2 == data.error) msg = '<a href="http://mall.ptp.cn/user/info" target="_blank">'+msg+'</a>';
		if(3 == data.error) msg = '<a href="http://mall.ptp.cn/user/address" target="_blank">'+msg+'</a>'
		$("#checkstatus").html(msg);
	});
}