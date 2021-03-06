/**
 * 公共js对象
 * @type type
 */
var ykp = {
	version: Math.random(),
	//version: version,
	/**
	 * 私有构造函数   
	 */    
//	 lchyhfx.52qingqiyun.com  正式   项目域名
//hyhwap.52qingqiyun.com  测试
	 
	 //https://api.lchyh.com  正式
	 //https://hyhapi.52qingqiyun.com     测试
	api_url: 'https://api.lchyh.com',
	//http://article.longcaixiaoyuan.com   https://articletest.longcaixiaoyuan.com
	base_url:"https://articletest.longcaixiaoyuan.com",
	__construct: function() {
		var it = this;
		//it.loadCss(["../js/jslib/jquery-weui-build/dist/css/jquery-weui.css", "../css/style.css"]);
		//it.loadJs(["../js/jslib/jquery-weui-build/dist/js/jquery-weui.js", "../js/jslib/template.js"]);
		//it.setCookie("clientTime", (new Date()).getTime(), 1);
	},
	/**
	 * 采用模板引擎渲染页面
	 * @param {type} option
	 * @returns {undefined}
	 */
	rend: function(option) {
		var it = this;
		var defaultOption = {
			//模板地址，支持jquery选择器、文件地址
			from: "",
			//渲染目的地址，支持jquery选择器，为空时与模板地址一致，此时模板地址不能为文件地址
			target: "",
			//通过ajax获取数据
			dataOption: {
				url: "",
				data: {},
			},
			//通过data获取数据，优先级高于dataOption
			data: {},
			//页面渲染完成后执行回调函数
			callback: function(res) {},
			//异常执行回调函数
			error: function(err) {},
		};
		var opt = $.extend({}, defaultOption, option);
		if(!opt.target) {
			opt.target = "#" + opt.from;
		}
		if(template && typeof template === 'function') {
			/*template.config({
			 openTag: '{',
			 closeTag: '}',
			 escape: true,
			 cache: false,
			 compress: false
			 });*/
			var doCallback = function(html, data, opt) {
				$(opt.target).html(html);
				if(opt.callback && typeof opt.callback === 'function') {
					opt.callback.call(this, html, data, opt);
				}
			};
			if(opt.dataOption && typeof opt.dataOption === 'object' && opt.dataOption.url) {
				opt.dataOption.success = function(res) {
					if(res && res.data) {
						var html = template(opt.from, res.data);
						doCallback(html, opt.data, opt);
					}
				};
				it.doAjax(opt.dataOption);
			}
			if(opt.data && typeof opt.data === 'object') {
				var html = template(opt.from, opt.data);
				doCallback(html, opt.data, opt);
			}
		}
	},
	/**
	 * json转换成字符串
	 * @param {type} json
	 */
	toString: function(json) {
		var it = this;
		var type = $.type(json);
		var res = json;
		if(!json || type === 'string') {
			res = json;
		} else if(type === 'array') {
			res = JSON.stringify(json);
		} else if(type === 'object') {
			res = JSON.stringify(json);
		} else if(type === 'date') {
			res = it.formatDate(json);
		} else {
			res = json;
		}
		return res;
	},
	/**
	 *
	 * @param {type} num
	 * @param {type} preNum
	 * @returns {Number}向下取整获取百分比
	 */
	fixNumber: function(num, preNum) {
		if(num <= 0) {
			return preNum;
		}
		var num = Math.ceil(num / preNum) * preNum;
		return num;
	},
	/**
	 * 转换为日期对象
	 */
	toDate: function(str) {
		if(typeof str === 'number') {
			return new Date(str);
		} else if(typeof str === 'string') {
			var date = new Date(str.replace(/-/g, "/"));
			return date;
		} else {
			return str;
		}
	},
	/**
	 * @param {type} date
	 * @returns {String}日期格式化
	 */
	toDateString: function(date) {
		var it = this;
		var hdate = it.formatDate(it.toDate(date), 'M月d日');
		var btDate = Number(it.formatDate(it.toDate(date), 'yyyyMMdd')) - Number(it.formatDate(new Date(), 'yyyyMMdd'));
		if(btDate === 0) {
			hdate = '今天 ' + hdate;
		} else if(btDate === 1) {
			hdate = '明天 ' + hdate;
		} else if(btDate === 2) {
			hdate = '后天 ' + hdate;
		} else if(btDate === -1) {
			hdate = '昨天 ' + hdate;
		} else if(btDate === -2) {
			hdate = '前天 ' + hdate;
		} else {
			hdate = it.formatDate(it.toDate(date), 'EE M月d日');
		}
		return hdate;
	},
	/**
	 * 倒计时
	 */
	onTimeout: function(dom, msTime, formtStr, callback) {
		var it = this;
		if(!msTime) {
			msTime = 133;
		}
		if(!formtStr) {
			formtStr = "hh:mm:ss.S";
		}
		if($(dom).length <= 0) {
			return;
		}
		it.doAjax({
			url: it.WEB_PATH + "/mobile/mobile/getsysconfig/",
			data: {
				clientTime: (new Date()).getTime()
			},
			method: "post",
			success: function(res) {
				// console.log(res);
				if(res && res.data && res.data.fixedTime) {
					var fixedTime = res.data.fixedTime;
					var timeOut = setInterval(function() {
						var isGoOn = false;
						$(dom).each(function(i, v) {
							var q_end_time = $(this).attr("endtime");
							var timeStr = it.timeLeft(q_end_time * 1000, fixedTime, formtStr);
							if(timeStr <= 0 && callback && typeof callback === 'function') {
								callback.call(it, i, v, timeStr);
							} else {
								isGoOn = true;
								$(this).html(timeStr);
							}
						});
						if($(dom).length > 0 && isGoOn) {
							//继续执行
						} else {
							clearInterval(timeOut);
						}
					}, msTime);
					return timeOut;
				}
			}
		});
	},
	/**
	 * 根据毫秒计算年月日时分秒毫秒
	 * @param {type} time1
	 * @returns {String|Number}
	 */
	timeLeft: function(time1, fixedTime, formtStr) {
		var it = this;
		if(!formtStr) {
			formtStr = "hh:mm:ss.S";
		}
		if(Number(time1) <= 0) {
			return -1;
		}
		var time = Number(time1) - (new Date()).getTime() + fixedTime;
		if(time > 0) {
			time = time - 1000 * 60 * 60 * 8;
			var dateTime = new Date(time);
			var d = Math.floor(time / (24 * 60 * 60 * 1000));
			var timeStr = it.formatDate(dateTime, formtStr);
			if(d > 0) {
				timeStr = d + "天" + timeStr;
			}
			return timeStr;
		} else {
			return 0;
		}
	},
	/**
	 * 转换为对象
	 */
	toJson: function(str) {
		var it = this;
		var json = {};
		if(!str) {
			return json;
		} else if($.type(str) === 'string') {
			try {
				var ss = str.replace(/&quot;/g, '"');
				json = $.parseJSON(ss);
			} catch(err) {
				it.alert("toJsonErr:" + it.toString(err));
			} finally {
				return json;
			}
		} else {
			return str;
		}
	},
	doAjax: function(option) {
		var it = this;
		var ajax;
		var url = option.url || "";
		//      var url = "/base/proxy";
		var right_code = 200;
		//          it.showBlock();
		if($('.dataTables_processing').length == 0 && !option.loading) {
			var div = document.createElement("div");
			var msg = option.msg || '';
			var html = '<div class="dataTables_processing" id="onProcess" style="z-index:5001">' + '<div  class="note-success" style="padding: 14px 15px;border-radius: 10px;display: flex;align-items: center;">' + '<img  style="top:-1px;display: inline-block; position:relative" src="/resource/front/images/forward/load.png" class="rotate"/>' + '<span style="font-size:14px;">' + msg + '</div>' + '</div>'
			div.innerHTML = html; //html
			//          div.setAttribute('id','mark');
			var Fun = function() {};
			Fun = function() {
				$('body').append(div);
			};
		}

		var break_debug = option.breakDebug || false;

		var defaultOptions = {
			beforeSend: Fun,
			url: "",
			showBlock: true,
			timeout: 600000,
			dataType: 'json',
			method: "post",
			async: true,
			success: function(res) {
				var doSuccess = function() {
					if(option.success && $.isFunction(option.success)) {
						option.success.call(this, res);
					} else {
						it.showMessage(res.msg, res); //没有设置返回成功回调函数时，默认显示请求回来的数据
					}
					if(res.code == right_code && option.showBlock === true) {
						//$('.pop_block').remove();
						it.hideBlock();
					}
				};
				//始终加上错误码判断
				if(res && res.code) {
					doSuccess();

				} else {
					doSuccess();
				}
			},
			error: function(err) {
				$('#onProcess').parent().remove();
				var doError = function() {
					if(option.error && $.isFunction(option.error)) {
						option.error.call(this, err);
					}
				};
				it.showOk({
					text: "未请求到数据，请刷新页面或重新登陆",
					callback: function() {
						//location.reload();
					}
				});
				return;
				//始终弹出错误信息
				var msg = err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : err.responseText;
				it.showMessage(msg, '请求发生错误，err:' + it.toString(err) + '<br/>url:' + url + '<br/>option:' + it.toString(option), [{
						button: '确定',
						callback: function() {
							doError();
							$('.pop_block').remove();
						}
					},
					{
						button: '取消'
					}
				]);
			},
			complete: function(jqXHR, textStatus) {
				if(option.complete && $.isFunction(option.complete)) {
					option.complete.call(this, jqXHR, textStatus);
				}
				//始终判断是否有block
				if(option.showBlock === true) {
					it.hideBlock();
				}
				$('#onProcess').parent().remove();
				if(!option.async) {
					ajax = jqXHR.responseJSON;
					return ajax;
				}
			}
		};
		var ajaxOption = $.extend({}, defaultOptions, option);
		//      ajaxOption.data.login_token =
		//因为要加上统一提示处理，这些设置是不能被覆盖的
		//      option.data.request_url = it.api_url + ajaxOption.url;
		//option.data.method = option.method;
		ajaxOption.url = ajaxOption.baseUrl ? it.base_url + ajaxOption.url :  it.api_url + ajaxOption.url;
		//ajaxOption.method = defaultOptions.method;
		ajaxOption.data = option.data || defaultOptions.data || {};
		ajaxOption.success = defaultOptions.success;
		
		ajaxOption.data['token'] = $.md5(ajaxOption.url.slice(-10))+$.md5('qqy').slice(-7);
		ajaxOption.error = defaultOptions.error;
		ajaxOption.complete = defaultOptions.complete;
		var tajax = $.ajax(ajaxOption);
		return ajax || tajax;
	},
	showMessage: function(msg, detail, buttons) {
		var it = this;
		var html = [msg, '<span class="ajax_show_detail" style="position: absolute;left: 0;top: 0;padding: 10px;">&nbsp;&nbsp;&nbsp;</span>',
			'<div class="ajax_detail" style="text-align:left;">', it.toString(detail), '</div>'
		];
		it.alert(html.join(""), buttons);
		$(".ajax_detail").hide();
		$(".ajax_show_detail").on({
			click: function() {
				$(".ajax_detail").toggle();
			}
		});
	},
	/**
	 * 移动端分页列表
	 * @param {type} obj
	 * @returns {undefined}
	 */
	list: function(obj) {
		var it = this;
		var isok = false;
		var postData = $.extend({}, {
			page_size: 200,
			page_no: 1,
		}, obj.postData);
		var option = $.extend({}, obj);
		console.log(option);
		it.doAjax({
			url: option.url,
			method: "get",
			data: postData,
			showBlock: true,
			success: function(res) {
				var total = 200;
				var page_no = res.page_no
				if(option.pageBar && option.pageBar.id) { //分页组件
					it.loadJs('/resource/front/js/jqPaginator/jqPaginator.min.js');
					it.loadCss('/resource/front/js/jqPaginator/jqPaginator.min.css');
					$(option.pageBar.id).jqPaginator({
						totalPages: total, //总页数
						//totalCounts: res.totalRecord , //设置分页的总条目数
						visiblePages: option.pageBar.showNum || 5, //显示条目数
						currentPage: page_no, //当前页
						first: '<li class="first"><a href="javascript:void(0);">首页<\/a><\/li>',
						prev: '<li class="prev"><a href="javascript:void(0);"><i class="arrow arrow2"><\/i><\/a><\/li>',
						next: '<li class="next"><a href="javascript:void(0);"><i class="arrow arrow3"><\/i><\/a><\/li>',
						last: '<li class="last"><a href="javascript:void(0);">末页<\/a><\/li>',
						page: '<li class="page"><a href="javascript:void(0);">{{page}}<\/a><\/li>',
						onPageChange: function(n) {
							if(n != postData.pageNo) {
								option.data.pageNo = n;
								it.list(option);
							}
						}
					});
				}
				return;
				if(option.loadList && typeof option.loadList === 'function') {
					option.loadList.call(this, res, postData);
					if(total > page) {
						isok = true;
						$(window).off("scroll");
						$(window).scroll(function() {
							if((200 + $(window).scrollTop()) >= ($(document).height() - $(window).height()) && isok) {
								isok = false;
								option.postData.page = postData.page + 1;
								it.list(option);
							}
						});
					} else {
						$(window).off("scroll");
						//console.log("已经没有数据了");
					}
				} else {
					var html = [];
					for(var i in res.rows) {
						var item = res.rows[i];
					}
					$(option.id).append(html.join(""));
				}
			},
			complete: function() {
				it.hideBlock();
			}
		});
	},
	nopage: function(key, msg, ico, foot) {
		var it = this;
		var obj = {
			key: key,
			msg: msg,
			ico: ico,
			foot: foot
		}
		location.href = it.WEB_PATH + "/mobile/mobile/nopage/?" + $.param(obj);
	},
	/**
	 *
	 * @param {type} msg
	 * @returns {undefined}弹出层
	 */
	onMask: function(msg) {
		var it = this;
		var body_width = it.getWindowSize('width');
		var maskHeight = it.getWindowSize('height');
		$('.mask').remove();
		var html = [
			'<div class="mask" style="height:', maskHeight,
			'px;background-color:rgba(0,0,0,0.6);position:fixed;width:',
			body_width, 'px;z-index:50;">',
			msg,
			'</div>'
		]
		$("body").prepend(html.join(""));
	},
	onShare: function() {
		var it = this;
		$(".mywin_share").off("click").on({
			click: function() {
				it.onMask('<img src="' + it.G_TEMPLATES_STYLE + '/images/mobile/sharego.png" style="position: fixed;right:18px;top: 5px;width: 262px!important; height: 218px!important; z-index: 55;"/>');
				$('.mask').off('click').on({
					click: function() {
						$('.mask').remove();
					}
				});
			}
		});
	},
	/**
	 *去除字符串中的html标签，并截取
	 */
	delHtml: function(html, len, str) {
		var title = html.replace(/<[^>]+>/g, ""); //去掉所有的html标记
		if(title.length > len) {
			title = title.substring(0, len);
			if(str) {
				title = title + str;
			}
		}
		return title;
	},
	/**
	 * 动态加载js,为后续支持统一压缩合并铺垫
	 * @param {type} name
	 * @returns {undefined}
	 */
	loadJs: function(name, callback) {
		var it = this;
		var res = false;
		var doLoadJs = function(itemname) {
			var domid = itemname.replace(new RegExp(":|#|\\.|\\/|\\/", 'gm'), "9");
			if($("#" + domid, "head").length == 0) {
				$("<script>").attr({
					id: domid,
					type: "text/javascript",
					src: itemname + "?v=" + it.version
				}).appendTo("head");
				res = domid;
			}
		};
		if(typeof name === "object") {
			for(var i in name) {
				var itemname = name[i];
				doLoadJs(itemname);
			}
		} else {
			var itemname = name;
			doLoadJs(itemname);
		}
		if(callback && typeof callback === 'function') {
			callback.call(this, res, name);
		}
	},
	/**
	 * 动态加载css，为后续支持统一压缩合并铺垫
	 * @param {type} name
	 * @returns {undefined}
	 */
	loadCss: function(name, callback) {
		var it = this;
		var res = false;
		var doLoadCss = function(itemname) {
			var domid = itemname.replace(new RegExp(":|#|\\.|\\/|\\/", 'gm'), "7");
			if($("#" + domid, "head").length == 0) {
				$("<link>").attr({
					id: domid,
					rel: "stylesheet",
					type: "text/css",
					href: itemname + "?v=" + it.version
				}).appendTo("head");
				res = domid;
			}
		};
		if(typeof name === "object") {
			for(var i in name) {
				var itemname = name[i];
				doLoadCss(itemname);
			}
		} else {
			var itemname = name;
			doLoadCss(itemname);
		}
		if(callback && typeof callback === 'function') {
			callback.call(this, res, name);
		}
	},
	getUrlParam: function(url) {
		if(typeof url === 'undefined') {
			url = location.href;
		}
		if(url.indexOf("?") === -1) {
			return {};
		}
		url = decodeURIComponent(url);
		var paraString = url.substring(url.lastIndexOf("?") + 1, url.length).split("&"),
			paraObj = {};
		var i, j;
		for(i = 0; j = paraString[i]; i++) {
			var key = j.substring(0, j.indexOf("=")).toLowerCase();
			var value = j.substring(j.indexOf("=") + 1, j.length);
			paraObj[key] = value;
		}
		return paraObj;
	},
	showBlock: function(msg) {
		$('.global_loader_contain').remove();
		var html = [
			'<div class="global_loader_contain">',
			'<div class="global_loader"><div class="cycle_loader">',
			'<div></div>',
			'<div></div>',
			'<div></div>',
			'<div></div>',
			'<div></div>',
			'<div></div>',
			'<div></div>',
			'<div></div>',
			'</div>',
			'</div>'
		];
		$('body').prepend(html.join(""));
	},
	hideBlock: function(callback) {
		var it = this;
		setTimeout(function() {
			$('.global_loader_contain').remove();
		}, 0); //暂时为2秒
		if(callback && typeof callback === 'function') {
			callback.call(this, it);
		}
	},
	alert: function(msg, buttons) {
		var it = this;
		if(msg && typeof msg === 'object') {
			msg = it.toString(msg);
		}
		var body_width = it.getWindowSize('width');
		var maskHeight = it.getWindowSize('height');
		$('.pop_block').remove();
		var html = [
			'<div class="pop_block" style="position:absolute;width:', body_width, 'px">',
			'<div class="mask"  style="height:', maskHeight,
			'px;background-color:#000;opacity:0.2;position:fixed;width:',
			body_width, 'px;z-index:60;">',
			'</div>',
			'<div class="pop" style="background-color:#FFF;opacity:1;bottom:0;padding:10px 0 0;width:',
			body_width, 'px;text-align:center;z-index:65;position:fixed;">',
			'<div class="msg sp_left" style="padding: 20px 15px;word-wrap:break-word;">',
			msg || '空',
			'</div>',
			'<div class="tools">',
			'</div>',
			'</div>',
			'</div>'
		];
		$('body').prepend(html.join(""));
		if(!buttons || buttons.length === 0) {
			buttons = [{
				button: '确定'
			}];
		}
		for(var i in buttons) {
			var item = buttons[i];
			var buttonHtml = ['<div class="sp_left" style="border-bottom:solid 4px #999999;line-height:45px;" id="tools_button',
				i, '" index = ', i, '>', item.button, '</div>'
			];
			$(buttonHtml.join("")).appendTo('.tools').off('click').on({
				click: function(e) {
					var j = $(this).attr('index');
					if(buttons[j].callback && typeof buttons[j].callback === 'function') {
						buttons[j].callback.call(this, e);
					} else {
						$('.pop_block').remove();
					}
				}
			});
		}
		$('.tools div:first').css({
			'border-top': 'solid 1px #eaeaea'
		});
		$('.tools div:last').css({
			'border-bottom': 'none'
		});
	},
	/**
	 * 提示框 （建议20汉字以内体验较好)
	 * @returns {undefined}
	 */
	prompt: function(str) {
		var str = str ? str : 'API数据错误';
		//var id = 'global_' + parseInt(Math.random() * 100, 10);
		var id = 'global_prompt';
		var html = [
			'<div id="' + id + '" class="global_shade">',
			'<div style="width:260px;height: 45px;position: fixed;left:50%;top:50%;margin-left:-130px;">',
			'<p style="color:#fff;width:100%;height:100%;line-height: 45px;margin: 0 auto;text-align:center;font-size:14px;box-shadow: 0 2px 3px #9d9d9d;background:#484848;border-radius:5px;">',
			str,
			'</p>',
			'</div>',
			'</div>,'
		];
		$('#' + id).remove();
		$(html.join('')).fadeIn().appendTo('body');
		setTimeout(function() {
			$('#' + id).fadeOut(600);
		}, 1000);
	},
	/**
	 * OK提示框 （建议20汉字以内体验较好)
	 * @returns {undefined}
	 */
	showOk: function(options) {
		var defaultOptions = {
			id: 'global_showok',
			text: "操作成功!",
			time: 600,
			callback: function() {
				//console.log(this);
			}
		};
		var obj = $.extend({}, defaultOptions, options);
		var html = [
			'<div id="', obj.id, '" class="global_shade" >',
			'<div  style="width:260px;height: 45px;position: fixed;left:50%;top:50%;margin-left:-130px;">',
			'<p style="color:#fff;width:100%;height:100%;line-height: 45px;margin: 0 auto;text-align:center;font-size:14px;box-shadow: 0 2px 3px #9d9d9d;background:#484848;border-radius:5px;">',
			obj.text,
			'</p>',
			'</div>',
			'</div>',
		];
		$('#' + obj.id).remove();
		var $dom = $(html.join("")).fadeIn().appendTo('body');
		setTimeout(function() {
			$('#' + obj.id).fadeOut(obj.time, function() {
				$dom.remove();
				obj.callback.call(this);
			});
		}, 1000);
	},
	/**
	 * 获取url路径中的参数值
	 * @param {type} param
	 * @param {type} url
	 * @returns {unresolved}
	 */
	getUrlRequestParam: function(key, url) {
		var it = this;
		var paraObj = it.getUrlParam(url);
		var returnValue = paraObj[key.toLowerCase()];
		if(typeof(returnValue) === 'undefined' || returnValue === 'undefined') {
			return null;
		} else {
			if(returnValue.charAt(returnValue.length - 1) === '#') {
				returnValue = returnValue.substr(0, returnValue.length - 1);
			}
			return decodeURIComponent(returnValue);
		}
	},
	getUrlParam: function(name) {
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
		var r = window.location.search.substr(1).match(reg); //匹配目标参数
		if(r != null)
			return unescape(r[2]);
		return null; //返回参数值
	},
	setUrlParam: function(obj, url) {
		if(typeof url === 'undefined') {
			url = location.href;
		}
		url = decodeURIComponent(url);
		var paraObj = {};
		if(url.indexOf("?") !== -1) {
			var paraString = url.substring(url.indexOf("?") + 1, url.length).split("&");
			var i, j;
			for(i = 0; j = paraString[i]; i++) {
				paraObj[j.substring(0, j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=") + 1, j.length);
			}
		}
		$.extend(paraObj, obj);
		var newUrl = url;
		if(url.indexOf("?") !== -1) {
			newUrl = url.substring(0, url.indexOf("?"));
		}
		if(newUrl.indexOf('?') === -1) {
			newUrl = newUrl + '?';
		}
		if(newUrl.charAt(newUrl.length - 1) !== '?') {
			newUrl = newUrl + '&';
		}
		var paramUrl = decodeURIComponent($.param(paraObj));
		return newUrl + paramUrl;
	},
	formatDate: function(date, fmt) {
		var o = {
			"M+": date.getMonth() + 1, //月份
			"d+": date.getDate(), //日
			"h+": date.getHours() % 12 === 0 ? 12 : date.getHours() % 12, //小时
			"H+": date.getHours(), //小时
			"m+": date.getMinutes(), //分
			"s+": date.getSeconds(), //秒
			"q+": Math.floor((date.getMonth() + 3) / 3), //季度
			"S+": date.getMilliseconds() //毫秒
		};
		var week = {
			"0": "日",
			"1": "一",
			"2": "二",
			"3": "三",
			"4": "四",
			"5": "五",
			"6": "六"
		};
		if(/(y+)/.test(fmt)) {
			fmt = fmt.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
		}
		if(/(E+)/.test(fmt)) {
			fmt = fmt.replace(RegExp.$1, ((RegExp.$1.length > 1) ? (RegExp.$1.length > 2 ? "星期" : "周") : "") + week[date.getDay() + ""]);
		}
		for(var k in o) {
			if(new RegExp("(" + k + ")").test(fmt)) {
				fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
			}
		}
		return fmt;
	},
	getFormData: function(form) {
		var it = this;
		var arr = [];
		$.each($('input,select,textarea', $(form)), function(i, v) {
			var key = $(this).attr('name') || $(this).attr('id');
			var value = $(this).val();
			var type = $(this).attr('validate');
			var msg = $(this).attr('msg');
			var dom = $(this);
			var error = $(this).attr('error') || function() {
				$(dom).addClass('border_red');
				$(dom).focus();
			};
			var success = $(this).attr('success') || function() {
				$(dom).removeClass('border_red');
			};
			var item = {
				key: key,
				value: value,
				type: type,
				msg: msg,
				success: success,
				error: error
			};
			arr.push(item);
		});
		return it.validate(arr);
	},
	validate: function(arr) {
		var it = this;
		var obj = {};
		for(var i in arr) {
			var item = arr[i];
			var is_ok = true;
			if(item.type === 'username') {
				if(!/^[\w-]{4,30}$/.test(item.value)) {
					is_ok = false;
				}
			} else if(item.type === 'password') {
				if(!/^[\w\@-]{6,30}$/.test(item.value)) {
					is_ok = false;
				}
			} else if(item.type === 'code16') {
				if(!/^#?([a-f0-9]{6}|[a-f0-9]{3})$/.test(item.value)) {
					is_ok = false;
				}
			} else if(item.type === 'email') {
				if(!/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/.test(item.value)) {
					is_ok = false;
				}
			} else if(item.type === 'phone') {
				if(!/(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/.test(item.value)) {
					is_ok = false;
				}
			} else if(item.type === 'idcard') {
				if(!/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(item.value)) {
					is_ok = false;
				}
			} else if(item.type === 'url') {
				if(!/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/.test(item.value)) {
					is_ok = false;
				}
			} else if(item.type === 'ip') {
				if(!/((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)/.test(item.value)) {
					is_ok = false;
				}
			} else if(item.type === 'html') {
				if(!/^<([a-z]+)([^<]+)*(?:>(.*)<\/\1>|\s+\/>)$/.test(item.value)) {
					is_ok = false;
				}
			} else if(item.type === 'chinese') {
				if(!/^[\u2E80-\u9FFF]+$/.test(item.value)) {
					is_ok = false;
				}
			} else if($.type(item.type) === 'regexp') {
				if(!item.type.test(item.value)) {
					is_ok = false;
				}
			} else if(item.type === 'string') {
				if(!/^\w+$/.test(item.value)) {
					is_ok = false;
				}
			} else if(item.type === 'int') {
				if(!/^\d+$/.test(item.value)) {
					is_ok = false;
				}
			} else if(item.type === 'required') {
				if(!item.value) {
					is_ok = false;
				}
			} else if(item.type) {
				//字符串类型的正则表达式
				var str = item.type;
				str = str.replace(/\/\//g, "\/");
				var reg = eval(str); //转成正则
				if(!reg.test(item.value)) {
					is_ok = false;
				}
			} else {

			}
			if(!is_ok) {
				if(item.msg) {
					//it.alert(item.msg);
					it.prompt(item.msg);
				}
				if(item.error && typeof item.error === 'function') {
					item.error.call(this, item.value);
				}
				return false;
			}
			if(item.success && typeof item.success === 'function') {
				item.success.call(this, item.value);
			}
			obj[item.key] = item.value;
		}
		return obj;
	},
	getMax: function(arr) {
		var sortNumber = function(a, b) {
			return b - a;
		};
		var sortArr = new Array();
		if($.type(arr) !== 'array') {
			for(var i in arguments) {
				sortArr.push(arguments[i]);
			}
		} else {
			sortArr = arr;
		}
		var sortList = sortArr.sort(sortNumber);
		var res = sortList[0];
		return res;
	},
	getMin: function(arr) {
		var sortNumber = function(a, b) {
			return a - b;
		};
		var sortArr = new Array();
		if($.type(arr) !== 'array') {
			for(var i in arguments) {
				sortArr.push(arguments[i]);
			}
		} else {
			sortArr = arr;
		}
		var sortList = sortArr.sort(sortNumber);
		var res = sortList[0];
		return res;
	},
	getWindowSize: function(key) {
		var it = this;
		var dpi = window.devicePixelRatio;
		var width = $(window).width();
		var height = $(window).height();
		var obj = {
			width: width,
			height: height,
			clientWidth: document.body.clientWidth,
			clientHeight: document.body.clientHeight,
			offsetWidth: document.body.offsetWidth,
			offsetHeight: document.body.offsetHeight,
			scrollWidth: document.body.scrollWidth,
			scrollHeight: document.body.scrollHeight,
			scrollTop: document.body.scrollTop,
			scrollLeft: document.body.scrollLeft,
			screenTop: window.screenTop,
			screenLeft: window.screenLeft,
			screenWidth: window.screen.width,
			screenHeight: window.screen.height,
			availWidth: window.screen.availWidth,
			availHeight: window.screen.availHeight
		};
		if(key) {
			return obj[key];
		}
		var arr = [
			"网页可见区域宽 ：" + document.body.clientWidth,
			"网页可见区域高：" + document.body.clientHeight,
			"网页可见区域高：" + document.body.offsetHeight + " (包括边线的宽)",
			"网页正文全文宽：" + document.body.scrollWidth,
			"网页正文全文高：" + document.body.scrollHeight,
			"网页被卷去的高：" + document.body.scrollTop,
			"网页被卷去的左：" + document.body.scrollLeft,
			"网页正文部分上：" + window.screenTop,
			"网页正文部分左：" + window.screenLeft,
			"屏幕分辨率的高：" + window.screen.height,
			"屏幕分辨率的宽：" + window.screen.width,
			"屏幕可用工作区高度：" + window.screen.availHeight,
			"屏幕可用工作区宽度：" + window.screen.availWidth
		];
		var str = arr.join("<br/>");
		//console.log(str);
		it.alert(obj);
		return obj;
	},
	//消息提示
	showMsg: function(msg, time) {
		msg = msg || '';
		time = time || 3000;
		var html = [
			'<div class="mask" style="width:100%;height:100%;background: rgba(0,0,0,0.0);position:fixed;">',
			'<div style="width:150px;min-height:80px;background:#fff;position:fixed;margin-top:-40px;margin-left:-75px;',
			'left:50%;top:50%;line-height:80px;text-align:center;border-radius:5px;">',
			msg,
			'</div>',
			'</div>'
		];
		if($(".mask").length > 0) {
			$(".mask").remove();
		}
		$('body').prepend(html.join(""));
		setTimeout(function() {
			$(".mask").remove();
		}, time);
	},
	getNowTime: function(time, statu) {
		if(!time || time == 0) {
			return "";
		}
		var time = new Date(time * 1000),
			year = time.getFullYear(),
			month = this.fixZero(time.getMonth() + 1, 2),
			day = this.fixZero(time.getDate(), 2),
			hour = this.fixZero(time.getHours(), 2),
			minute = this.fixZero(time.getMinutes(), 2),
			second = this.fixZero(time.getSeconds(), 2);

		//		time = year + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + second;
		if(statu) {
			time = year + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + second;
		} else {
			time = year + "-" + month + "-" + day;
		}
		return time;
	},
	
	/**
	 * 补零操作
	 * @param num 需要补零的字符
	 * @param length 需要补零的位数 如 7 补为07，length=2,007length则为3
	 */
	fixZero: function(num, length) {
		var str = "" + num,
			len = str.length,
			s = "";
		for(var i = length; i-- > len;) {
			s += "0";
		}
		return s + str;
	},
	
	//格式化统一金额,保留小数位
	dataFloat: function(value, doint) {
		value = value + '';
		if(value.match(/^[\d.]+$/) == null || Number(value) == 0) {
			return '';
		} else {
			var keep = doint ? doint : 0;
			value = parseFloat(value).toFixed(keep);
			if(Number(value) == 0) {
				return '';
			} else {
				return value;
			}
		}
	},
	/**
	 *输入文本框中添加快捷清除功能
	 * @param {object} options
	 * @returns object
	 * @notice: 如果输入框不需要此事件,则加上class="no-clear"属性即可
	 */
	showInputClear: function(options) {
		var settings = $.extend({
			'exclude': '.no-clear'
		}, options);
		return settings.each(function() {
			$(this).not(settings.exclude)
				.unbind("clear-focus")
				.bind("clear-focus", (
					function() {
						if($(this).data("clear-button"))
							return;
						var img = '<img class="icon_del" src="/resources/front/images/search_del_nor.png" style="width:13px;float:right;"/>';
						var x = $("<a class='clear-text' style='cursor:pointer;display:block;'>" + img + "</i></a>");
						$(x).data("text-box", this);
						$(x).mouseover(function() {
							$(this).addClass("over");
						}).mouseleave(function() {
							$(this).removeClass("over");
						});
						$(this).data("clear-button", x);
						// var left = $(this).position().left + $(this).width() - $(x).width();
						//var top = (($(this).position().top + $(this).height()) - $(x).height()) / 2;
						$(x).css({
							"position": "absolute",
							"left": left,
							"top": top,
							'z-index': '10'
						});
						var left = $(this).offset().left + $(this).width() - $(x).width();
						var top = $(this).offset().top + $(this).height() / 2 - $(x).height();
						$(x).css({
							"position": "fixed",
							"left": left,
							"top": top,
							'z-index': '10'
						});
						$(this).after(x);
					}))
				.unbind("clear-blur").bind("clear-blur", (
					function(e) {
						var x = $(this).data("clear-button");
						if(x) {
							if($(x).hasClass("over")) {
								$(x).removeClass("over");
								$(x).hide().remove();
								$(this).val("");
								$(this).removeData("clear-button");
								var txt = this;
								e.stopPropagation();
								e.stopImmediatePropagation();
								setTimeout($.proxy(function() {
									$(this).trigger("focus");
								}, txt), 50);
								return false;
							}
						}
						if(x && !$(x).hasClass("over")) {
							$(this).removeData("clear-button");
							$(x).remove();
						}
					}));
			//添加私有默认聚集事件
			$(this).on("focus", function() {
				$(this).trigger("clear-focus");
			}).on("blur", function() {
				$(this).trigger("clear-blur");
			});
		});
	},
	getRule:function(){
		var that = this;
		var search = window.location.search;
		search = search.replace(/\?/, "").split("=")[1];
		$('.rule').append(`<div class="rule-list">
					<img src="/resource/front/images/forward/icon-time.png"/> <p>传播区域：<span class="tj_address"></span></p>
				</div>
				<div class="rule-list">
					<img src="/resource/front/images/forward/icon_local.png"/> <p>有效浏览：1/2页  有效时长<span class="look_time_yhd"></span>秒<span class="look_money_yhd"></span>LCB</p>
				</div>
				<div class="rule-list">
					<img src="/resource/front/images/forward/icon_click.png"/> <p>有效点击：点击下方广告模块获取相应龙采币</p>
				</div>
				<div class="rule-list1">
					<img src="/resource/front/images/forward/icon-more.png"/> <p>获取更多：此页面分享给好友，好友进行阅读点击达到上述标准后，作为宝宝的你就会获得更多龙采币哟，快去分享哟</p>
				</div>`);
		$('.ruleBtn').click(function(){
			$('.rule').show();
			$('.content').append('<div class="mui-backdrop" style="opacity:1"></div>');
			$('.mui-backdrop').click(function(){
				$('.rule').hide();
				$(this).remove();
			})
			ykp.doAjax({
				url: "/api/banner/ad_rules",
				method: "post",
				data: {
					ad_id: search.split(",")[1]
				},
				loading: false,
				success: function(res) {
					var data = res.data;
					that.rule = res.data;
					$('.rule-list').find('span').each(function() {
						for(var i in data) {
							if(i == $(this).attr('class')) {
								$(this).text(data[i]);
							}
						}
					})
					$('.look_money_yhd').text(res.data.look_money)
					$('.look_time_yhd').text(res.data.look_time)
					$('.tj_address').attr('type', res.data.promote_type)
					if(res.data.promote_type == 1){
						$('.tj_address').addClass('tj_address1')
					}
					$('.tj_address').click(function() {
						console.log($('.tj_address').attr('type'))
						if($('.tj_address').attr('type') == 1) {
							window.location.href = 'map?lng=' + that.rule.lat + "&lat=" + that.rule.lng + "&km=" + that.rule.km;
						}
	
					})
	
				}
			})
		})
		
	},
	browser: {
		versions: function() {
			var u = navigator.userAgent,
				app = navigator.appVersion;
			return {
				trident: u.indexOf('Trident') > -1, //IE内核
				presto: u.indexOf('Presto') > -1, //opera内核
				webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
				gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
				mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
				ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
				android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或者uc浏览器
				iPhone: u.indexOf('iPhone') > -1, //是否为iPhone或者QQHD浏览器
				iPad: u.indexOf('iPad') > -1, //是否iPad
				webApp: u.indexOf('Safari') == -1, //是否web应该程序，没有头部与底部
				weixin: u.indexOf('MicroMessenger') > -1, //是否微信 （2015-01-22新增）
				qq: u.match(/\sQQ/i) == " qq" //是否QQ
			};
		}(),
		language: (navigator.browserLanguage || navigator.language).toLowerCase()
	},
	min: function(arr) {
		return arr.sort(function(a, b) {
			return a - b;
		})[0];
	},
	setCookie: function(name, value, day) {
		var Days = day ? day : 30; //默认30天
		var exp = new Date();
		exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
		document.cookie = name + "=" + escape(value) + ";path=/;expires=" + exp.toGMTString();
	},
	getCookie: function(name) {
		var arrstr = document.cookie.split("; ");
		for(var i = 0; i < arrstr.length; i++) {
			var temp = arrstr[i].split("=");
			if(temp[0] == name)
				return unescape(temp[1]);
		}
	},
	delCookie: function(name) {
		var it = this;
		var date = new Date();
		date.setTime(date.getTime() - 10000);
		var cval = it.getCookie(name);
		if(cval != null)
			document.cookie = name + "=" + cval + "; expires=" + date.toGMTString();
	},
	setLocalStorage: function(name, value) {
		localStorage.setItem(name, value);
	},
	setLocalStorage_mul: function(option) {
		var option = option;
		for(var i in option) {
			localStorage.setItem(i, option[i]);
		}
	},
	getLocalStorage: function(name) {
		return localStorage.getItem(name);
	},
	delLocalStorage: function(name) {
		localStorage.removeItem(name);
	},
	clearLocalStorage: function() {
		localStorage.clear();
	},
	setSessionStorage: function(option) {
		var option = option;
		for(var i in option) {
			sessionStorage.setItem(i, option[i]);
		}
	},

	getSessionStorage: function(name, value) {
		return sessionStorage.getItem(name);
	},
	getFormCode: function(params) {
		var obj = {};
		var count = 0;
		var num = 0; 
		$(params.el).find('input,select,textarea').each(function() {
			if($(this).attr('field')){
				num++;
			}
		})
		$(params.el).find('input,select,textarea').each(function() {
			
			if($(this).attr('field') && $(this).attr('required')) {
				if(!$(this).val()) {
					mui.toast($(this).attr('errmsg') ? $(this).attr('errmsg') : "请输入" + $(this).prev().text());
					return false;
				} else {
					count++;
					obj[$(this).attr('field')] = $(this).val();
				}
			} else if($(this).attr('field')) {
				count++;
				obj[$(this).attr('field')] = $(this).val();
			}
		})
		return count != num ? false : obj;
	},
	clearObj: function() {

	},
	/**
	 * 显示无数据时的状态
	 * @returns {undefined}
	 */
	showNoContens: function(option) {
		var it = this;
		var defaultOptions = {
			msg: '暂时还没有数据',
			img_path: '',
			try_url: it.WEB_PATH + '/mobile/mobile/get_trybuy',
			try_again: '', //试试手式填充内容
			buttons: [{
				button: "返回首页",
				callback: function() {
					location.href = it.WEB_PATH + "/mobile/mobile/";
				}
			}, {
				button: "刷新",
				callback: function() {
					location.href = location.href;
				}
			}]
		};
		var finaOptions = $.extend({}, defaultOptions, option);
		var btn_html = [];
		for(var i in finaOptions.buttons) {
			var item = finaOptions.buttons[i];
			btn_html.push('<a class="weui_btn weui_btn_orange" style="margin-top:0;padding:0;" href="javascript:;" index="', i, '">', item.button, '</a>');
		}
		var img_alt = '<div style="width:200px;height: 200px;border-radius: 50%;background:#E1E5E6;margin:0 auto;" ></div>';
		if(finaOptions.img_path) {
			img_alt = '<img src="' + finaOptions.img_path + '" style="width:200px;border-radius:50%;display:block;margin:0 auto;opacity:0.8;" />';
		}
		var html = [
			'<div style="padding-top:10px;background:#eaebeb;">',
			img_alt,
			'<div style="font-size:15px;text-align:center;line-height:normal;margin-top:10px;">', finaOptions.msg, '</div>',
			'<div style="margin:0 auto;padding:10px 0;width:60%;overflow:hidden;">',
			btn_html.join(""),
			'</div>',
			'</div>'
		];
		var dom = $(html.join(""));
		if(finaOptions.buttons.length > 1) {
			$('a[index=0]', dom).css({
				'float': 'left',
				'width': '45%'
			});
			$('a[index=1]', dom).css({
				'float': 'right',
				'width': '45%'
			});
			var jtem = finaOptions.buttons[1];
			$(".weui_btn[index=" + 1 + "]", dom).off("click").on({
				click: function() {
					if(jtem.callback && typeof jtem.callback === 'function') {
						jtem.callback.call(this, finaOptions, dom, 1, jtem);
					}
				}
			});
		}
		if(finaOptions.buttons.length > 0) {
			var item = finaOptions.buttons[0];
			$(".weui_btn[index=" + 0 + "]", dom).off("click").on({
				click: function() {
					if(item.callback && typeof item.callback === 'function') {
						item.callback.call(this, finaOptions, dom, 0, item);
					}
				}
			});
		}
		//追加试试手气
		if(finaOptions.try_url && finaOptions.try_again) {
			it.doAjax({
				url: finaOptions.try_url,
				method: 'post',
				success: function(res) {
					//console.log(res);
					var data = res.data;
					var goos_html = [];
					for(var i in data) {
						var item = data[i];
						goos_html.push(
							'<a class="weui_grid" style="width:50%;" href="', WEB_PATH, '/mobile/mobile/shopinfo/?id=', item['id'], '">',
							'<div style="text-align:center;"><img src="', G_UPLOAD_PATH, '/', item['thumb'], '" style="width:120px;height:120px;"></div>',
							'<p class="span1" style="font-size:14px;padding-top:13px;height:43px;">', item['title'], '</p>',
							'</a>'
						);
					}
					var try_html = [
						'<p class="span2 bg_white span3" style="margin-top:10px;padding:10px 15px;font-size:12px;">试试手气</p>',
						'<div class="weui_grids bg_white">',
						goos_html.join(''),
						'</div>'
					];
					$(finaOptions.try_again).html(try_html.join(''));
				}
			});
		}
		return dom;
	},
	loading: function() {
		if(!document.querySelector('.dataTables_processing')) {
			var div = document.createElement("div");
			var msg = '';
			var html = '<div class="dataTables_processing" id="onProcess" style="z-index:5001">' + '<div  class="note-success" style="padding: 14px 15px;border-radius: 10px;display: flex;align-items: center;">' + '<img  style="top:-1px;display: inline-block; position:relative" src="/resource/front/images/forward/load.png" class="rotate"/>' + '<span style="font-size:14px;">' + msg + '</div>' + '</div>'
			div.innerHTML = html; //html
			document.getElementsByTagName('body')[0].append(div);
		}
	}
};
/**
 * 定义JS类
 * @param {type} name
 * @param {type} parentClass
 * @param {type} currentClass
 * @returns {base_L8.module.exports.defineClass.defineClass}
 */
var defineClass = function(name, parentClass, currentClass) {
	var defObj = $.extend({}, parentClass, currentClass);
	if(defObj[name] && typeof defObj[name] === 'function') {
		$(document).ready(function() {
			//增加执行私有构造函数
			if(defObj['__construct'] && typeof defObj['__construct'] === 'function') {
				defObj['__construct'].call(defObj, $.extend({}, defObj, this));
			}
			defObj[name].call(defObj, $.extend({}, defObj, this));
		});
	}
	return defObj;
};