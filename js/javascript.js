$(function(){

	/*banner start*/
	var bannerImg = $(".bannerimg li");
	var bannerScroll = $(".bannerscroll li");
	var index=0;
	var adTimer = null;

	bannerScroll.mouseover(function(){
		index = bannerScroll.index(this);
		showImg(index);
	}).eq(0).mouseover();

	$(".bannerimg").hover(function(){
		if (adTimer) {
			clearInterval(adTimer);
		}
	},function(){
		adTimer = setInterval(function(){
			showImg(index);
			index++;
			if (index==bannerScroll.length) {index=0;}
		},5000);
	}).trigger("mouseout");

	function showImg(index){
		bannerScroll.eq(index).css("background","#d72326").siblings().css("background","#fff");
		bannerImg.eq(index).stop(true,true).fadeIn(500).siblings().fadeOut(500);
	}

	/* 商品选项点击 */
	var value = Number($("#productValue").text());
	$(".check span").click(function(){
		$(this).addClass("current").siblings().removeClass("current");
		var value1 = value, len = $(".check span.current").length, num = Number($("#num").val());
		for (var i = 0; i < len; i++) {
			value1 += Number($(".check span.current")[i].title)
		}
		value1 = value1 * num;
		$("#productValue").html(value1);
	});

	/* 增减购买数量 */
	$(".content-top-r span.minus").click(function(){
		var value1 = value, num = Number($("#num").val()), len = $(".check span.current").length;
		for (var i = 0; i < len; i++) {
			value1 += Number($(".check span.current")[i].title)
		}
		if (num > 1) {
			num--;
			value1 = value1 * num;
			$("#num").val(num);
			$("#productValue").html(value1);
		}
	});
	$(".content-top-r span.add").click(function(){
		var value1 = value, num = Number($("#num").val()), len = $(".check span.current").length;
		for (var i = 0; i < len; i++) {
			value1 += Number($(".check span.current")[i].title)
		}
		num++;
		value1 = value1 * num;
		$("#num").val(num);
		$("#productValue").html(value1);
	});
	$("#num").blur(function(){
		var value1 = value, num = Number($("#num").val()), len = $(".check span.current").length;
		for (var i = 0; i < len; i++) {
			value1 += Number($(".check span.current")[i].title)
		}
		value1 = value1 * num;
		$("#productValue").html(value1);
	});

	/* 详情页切换 */
	$(".tab-nav li").click(function(){
		$(this).addClass("current").siblings().removeClass("current");
		$(".tab-content>div").eq(this.id).show().siblings().hide();
	});

})