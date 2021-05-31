/**
 * 公共js对象
 * @type type
 */
var util = {
	version: Math.random(),
	//version: version,
	/**
	 * 私有构造函数   
	 */
	api_url: 'http://info.isxuan.com/',
	__construct: function() {
		var it = this;
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
	getDateDiff: function(dateTimeStamp) {
		dateTimeStamp = parseInt(new Date(dateTimeStamp)/1000);
		var minute = 1000 * 60;
		var hour = minute * 60;
		var day = hour * 24;
		var halfamonth = day * 15;
		var month = day * 30;
		var now = new Date().getTime();
		var diffValue = now - dateTimeStamp;
		if(diffValue < 0) {
			return;
		}
		var result = ""
		var monthC = diffValue / month;
		var weekC = diffValue / (7 * day);
		var dayC = diffValue / day;
		var hourC = diffValue / hour;
		var minC = diffValue / minute;
		if(monthC > 11){
			result = util.getNowTime(dateTimeStamp);
		}else if(monthC >= 1) {
			result = "" + parseInt(monthC) + "月前";
		} else if(weekC >= 1) {
			result = "" + parseInt(weekC) + "周前";
		} else if(dayC >= 1) {
			result = "" + parseInt(dayC) + "天前";
		} else if(hourC >= 1) {
			result = "" + parseInt(hourC) + "小时前";
		} else if(minC >= 1) {
			result = "" + parseInt(minC) + "分钟前";
		} else
			result = "刚刚";
		return result;
	},

	doAjax: function(option) {
		var it = this;
		var ajax;
		var url = option.url || "";
		//      var url = "/base/proxy";
		var right_code = 200;
		if($('.dataTables_processing').length == 0 && !option.loading) {
			var div = document.createElement("div");
			var msg = option.msg || '';
			var html = '<div class="dataTables_processing" id="onProcess" style="z-index:5001">' + '<div  class="note-success" style="padding: 14px 15px;border-radius: 10px;display: flex;align-items: center;">' + '<img  style="top:-1px;display: inline-block; position:relative" src="/resource/front/img/load.png" class="rotate"/>' + '<span style="font-size:14px;">' + msg + '</div>' + '</div>'
			div.innerHTML = html; //html
			//          div.setAttribute('id','mark');
			var Fun = function() {};
			Fun = function() {
				$('body').append(div);
			};
		}

		var break_debug = option.breakDebug || false;

		var defaultOptions = {
			url: "",
			//			beforeSend: Fun,
			showBlock: true,
			timeout: 600000,
			dataType: 'json',
			method: option.method || 'post',
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
				var doError = function() {
					if(option.error && $.isFunction(option.error)) {
						option.error.call(this, err);
					}
				};
				return;
				//始终弹出错误信息
				var msg = err.responseJSON && err.responseJSON.msg ? err.responseJSON.msg : err.responseText;
			},
			complete: function(jqXHR, textStatus) {
				if(option.complete && $.isFunction(option.complete)) {
					option.complete.call(this, jqXHR, textStatus);
				}
				//始终判断是否有block
				if(option.showBlock === true) {
					it.hideBlock();
				}
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
		ajaxOption.url = it.api_url + ajaxOption.url;
		ajaxOption.type = defaultOptions.method;
		ajaxOption.data = option.data || defaultOptions.data || {};
		ajaxOption.success = defaultOptions.success;
		if(util.getLocalStorage('token')){
			ajaxOption.data['token'] = util.getLocalStorage('token') == 'undefined' ? "" : util.getLocalStorage('token');
		}
		
		ajaxOption.error = defaultOptions.error;
		ajaxOption.complete = defaultOptions.complete;
		var tajax = $.ajax(ajaxOption);
		return ajax || tajax;
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
	GetRequest: function() {
		var url = location.search; //获取url中"?"符后的字串  
		var theRequest = new Object();
		if(url.indexOf("?") != -1) {
			var str = url.substr(1);
			strs = str.split("&");
			for(var i = 0; i < strs.length; i++) {
				theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
			}
		}
		return theRequest;
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
	getTime: function(time, statu, end) {
		var timeArr = time.split("-");
		if(statu) {
			if(end) {
				return parseInt(Date.parse(new Date(time) / 1000)) - 1;
			} else {
				return parseInt(Date.parse(new Date(time) / 1000));
			}
		} else {
			time = time.split('-').reverse().join('-');
			return parseInt(new Date(time) / 1000);
		}

	},

	/**
	 * 
	 * @param {Object} time 时间戳
	 * @param {Object} statu  true 年月日时分秒
	 */
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
	confirm: function(option) {
		if(!option.mask) {
			var mask = mui.createMask(function() {
				if(!option.clickMask) {
					return false;
				} else {
					$('.mui-backdrop').click(function() {
						$('.alert-pop').remove();
					})
					return true;
				}
			});
			mask.show();
		}
		var html = `<div class="mui-popup mui-popup-in alert-pop" style="display: block;" id="alert-pop">
				<div class="mui-popup-inner">
					<div class="mui-popup-title">${option.title}</div>
				</div>
				<div class="mui-popup-buttons">
					<span class="mui-popup-button alert-ok">${option.okBtn || '确认'}</span>
					<span class="mui-popup-button mui-popup-button-bold alert-cancle">${option.cancleBtn || '取消'}</span>
				</div>
			</div>`;
		$('body').append(html);

		$(".alert-ok").click(function() {
			if(typeof option.success == 'function') {
				$('.alert-pop').remove();
				option.success();
			}
			if(!option.mask) {
				$('.mui-backdrop').remove();
			}
		})

		$(".alert-cancle").click(function() {
			if(typeof option.cancle == 'function') {
				$('.alert-pop').remove();
				option.cancle();
			}
			if(!option.mask) {
				$('.mui-backdrop').remove();
			}
		})

	},
	getFormCode: function(params) {
		var obj = {};
		var count = 0;
		var num = 0;
		var verifyArr = {
			password: [/^[\S]{6,12}$/, "密码必须6到12位，且不能出现空格"], //密码
			tel: [
				/^1(3|4|5|7|8|9)\d{9}$/,
				"不是完整的11位手机号或者正确的手机号前七位"
			], //手机号
			gtNum: [/^\d+$/, "请填写非负数"], //非负数
			pInt: [/^[0-9]*[1-9][0-9]*$/, "请填写正整数"], //正整数
			stre: [/^[A-Za-z]+$/, "请填写英文"], //英文
			streB: [/^[A-Z]+$/, "请填写大写英文"], //大写英文
			streS: [/^[a-z]+$/, "请填写小写英文"], //小写英文
			email: [/^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/, "请填写正确的邮箱"], //邮箱
			url: [/[a-zA-z]+:\/\/[^\s]* /, "请填写正确的url地址"], //url
			china: [/[^\x00-\xff]/, "请填写中文"], //中文
			qq: [/^\d{5,10}$/, "请填写正确的qq"], //qq
			idCard: [/\d{15}|\d{18}/, "请填写正确的身份证"], //身份证
			// idCard: [/\d+\.\d+\.\d+\.\d+ /, "请填写正确的ip"], //ip地址
			mzero: [/^[1-9]\d*(\.\d+)?$/, "请输入大于零的数"], //大于零包含小数
			yzCode: [/^\d{6}$/, "请输入6位数字验证码"],

		};
		$(params.el).find('input,select,textarea').each(function() {
			if($(this).attr('field')) {
				num++;
			}
		})
		$(params.el).find('input,select,textarea').each(function() {
			if($(this).attr('field') && $(this).attr('required')) {
				if(!$(this).val()) {
					mui.toast($(this).attr('errmsg') ? $(this).attr('errmsg') : "请输入" + $(this).prev().text());
					return false;
				} else {
					if($(this).attr('data-type') && verifyArr[$(this).attr('data-type')][0]) {
						if(verifyArr[$(this).attr('data-type')][0] && !verifyArr[$(this).attr('data-type')][0].test($(this).val())) {
							mui.toast($(this).attr('errmsg') ? $(this).attr('errmsg') : "请输入" + $(this).prev().text());
							return false;
						} else {
							count++;
							obj[$(this).attr('field')] = $(this).val();
						}
					} else {
						count++;
						obj[$(this).attr('field')] = $(this).val();
					}

				}
			} else if($(this).attr('field')) {
				count++;
				obj[$(this).attr('field')] = $(this).val();
			}
		})
		return count != num ? false : obj;
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
	loadThree: function(option) {
		var it = this;
		it.loadCss("/resource/front/css/mui.picker.min.css");
		it.loadJs("/resource/front/js/mui.picker.min.js");
		if(option.layer && typeof option.layer != 'number') {
			console.error('option.layer is digital type');
			return false;
		}
		var cityPicker = new mui.PopPicker({
			layer: option.layer, //联动级别     1 2 3  级
		});
		if(option.defaultArea) {
			if(typeof $.isArray(option.defaultArea)) { //设置默认值
				//设置默认地址
				cityPicker.pickers[0].setSelectedValue(option.defaultArea[0], '0', function() {
					if(option.layer == 1)
						return false;
					cityPicker.pickers[1].setSelectedValue(option.defaultArea[1], '200', function() {
						if(option.layer == 2)
							return false;
						cityPicker.pickers[2].setSelectedValue(option.defaultArea[2], '300', function() {

						})
					})
				})

				var provinceid = it.getObj(option.city, option.defaultArea[0]);
				var city = it.getObj(provinceid.children, option.defaultArea[1]);
				var area = it.getObj(city.children, option.defaultArea[2]);
				//   option.showEl = "显示的节点"   option.defaultStr  默认连接字符串
				$(option.showEl).text(provinceid.text + option.defaultStr + city.text + option.defaultStr + area.text).attr("city", provinceid.value + "." + city.value + '.' + area.value);
			} else {
				console.error("option.defaultArea is array");
			}
		}

		cityPicker.setData(option.city);
		var showCityPickerButton = document.getElementById(option.tapEl);
		$('body').append('<div id="userResult" class="ui-alert"></div>');
		var cityResult3 = $('#userResult');
		showCityPickerButton.addEventListener('tap', function(event) {
			cityPicker.show(function(items) {
				if(typeof option.success == 'function') {
					option.success(items);
					return false;
				}
				var str = "";
				var strVal = "";
				if(option.layer <= 1) {
					str += items[0].text;
					strVal += items[0].value;
				}
				if(option.layer <= 2) {
					str += items[1].text;
					strVal += items[1].value;
				}
				if(option.layer <= 3) {
					str += items[2].text;
					strVal += items[2].value;
				}
				$(option.showEl).text(str).attr("city", strVal);

			});
		}, false);

	},

	scrollHearder: function(option) {
		var obj = {
			top: 0,
			scrollTop: 0,
			time: null
		}
		$(option.el || '.mui-bar-nav').css('transition', '.3s ease-in-out');
		$(window).scroll(function() {
			var top = $(option.el || '.mui-bar-nav').css('top');
			if(window.scrollY > obj.scrollTop) {
				if(obj.top != '-44px') {
					$(option.el || '.mui-bar-nav').css('top', '-44px');
					obj.top = '-44px';
				}
			} else {
				if(obj.top == '-44px') {
					$(option.el || '.mui-bar-nav').css('top', '0px');
					obj.top = '0px';
				}
			}
			obj.scrollTop = window.scrollY;
		})
	},

	getObj: function(Obj, id) {
		var res = "";
		for(var i in Obj) {
			if(Obj[i].value == id) {
				res = Obj[i];
				return res;
			}
		}
		return res;
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