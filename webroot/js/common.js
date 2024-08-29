/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
 

$(document).ready(function()
{
	// 一定時間経過後、メッセージを閉じる
	setTimeout(function() {
		$('#flashMessage').fadeOut("slow");
	}, 1500);
	
	
	setInterval('_addSec()', 1000);
	
	$.ajax({
		url: URL_LOGS_ADD,
		type: "POST",
		data: {
			log_type	: 'view', 
			log_content	: '', 
			controller	: _controller,
			action		: _action,
			params		: _params
		},
		dataType: "text",
		success : function(response){
			//通信成功時の処理
			//alert(response);
		},
		error: function(){
			//通信失敗時の処理
			//alert('通信失敗');
		}
	});
	
	if(G_LANG=='en')
		$("[data-localize]").localize(G_WEBROOT + "locales/app", { language: "en" });
});

$(window).on('beforeunload', function(event)
{
	$.ajax({
		url: URL_LOGS_ADD,
		type: "POST",
		data: {
			log_type	: 'move', 
			log_content	: '', 
			controller	: _controller,
			action		: _action,
			params		: _params,
			sec			: _sec
		},
		dataType: "text",
		success : function(response){
			//通信成功時の処理
			//alert(response);
		},
		error: function(){
			//通信失敗時の処理
			//alert('通信失敗');
		}
	});
	
	return;
});

function _addSec()
{
	_sec++;
	
	var $target = $('input[name="study_sec"]');
	
	if($target);
	{
		var sec = parseInt($target.val());
		sec++;
		$target.val(sec);
	}
}


function CommonUtility() {}

// リッチテキストエディタの設定
CommonUtility.prototype.setRichTextEditor = function (selector, upload_image_maxsize, base_url)
{
	// 旧パラメータ（use_upload_image）の対応
	if((upload_image_maxsize===true)||(upload_image_maxsize===false))
		upload_image_maxsize = (1024 * 1024 * 2);
	
	$(selector).summernote({
		lang: "ja-JP",
//		fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New']
		maximumImageFileSize: upload_image_maxsize,
		callbacks: {
			onImageUpload: function(files)
			{
				var data = new FormData();
				var image_key= $('input[name="data[_Token][key]"]').val();
				
				data.append('file', files[0]);
				data.append('data[_Token][key]', image_key);
				
				$.ajax({
					data: data,
					type: 'POST',
					url: base_url + 'admin/contents/upload_image',
					cache: false,
					contentType: false,
					processData: false,
					success: function(url) {
						if(url)
						{
							$(selector).summernote('insertImage', JSON.parse(url)[0], 'image');
						}
						else
						{
							alert('画像のアップロードに失敗しました');
						}
					},
					error: function(url) {
						alert('通信中にエラーが発生しました');
					}
				});
			},
			onImageUploadError: function(e)
			{
				alert('指定されたファイルはアップロードできません');
			}
		}
	});
}

// 進捗率チャートの設定
CommonUtility.prototype.createProgressChart = function (labels, access_data, progress_data, height)
{
	var chartData	= null;
	
	chartData = {
		labels: labels,
		datasets: [{
			type: 'line',
			label: ACCESS_COUNT_LABEL,
			borderColor: window.chartColors.blue,
			borderWidth: 2,
			fill: false,
			data: access_data,
				yAxisID: "y-axis-1",
		}, {
			type: 'bar',
			label: UPDATE_COUNT_LABEL,
			backgroundColor: window.chartColors.green,
			data: progress_data,
				yAxisID: "y-axis-2",
		}]
	};
	
	if(!document.getElementById('chart'))
		return;
	
	var ctx = document.getElementById('chart').getContext('2d');
	
	ctx.canvas.height = height + '.px';
	
	window.myMixedChart = new Chart(ctx, {
		type: 'bar',
		data: chartData,
		options: {
			responsive: true,
			title: {
//					display: true,
//					text: '1日のログイン回数と学習で獲得したスターの推移'
			},
			tooltips: {
				mode: 'index',
				intersect: true
			},
			scales: {
		        yAxes: [{
		            id: "y-axis-1",
		            type: "linear", 
		            position: "left",
		            ticks: {
						min: 0,
//						max: 100,
//		                stepSize: 100
		            },
		        }, {
		            id: "y-axis-2",
		            type: "linear", 
		            position: "right",
		            ticks: {
						min:0,
//						max: 10,
//		                stepSize: 2
		            },
		            gridLines: {
		                drawOnChartArea: false, 
		            },
		        }],
		    }
		}
	});
}

CommonUtility.prototype.setLocalStorage = function(key, data)
{
	try
	{
		localStorage[key] = JSON.stringify(data);
	}
	catch (e)
	{
		console.log(e);
	}
}

CommonUtility.prototype.getLocalStorage = function(key)
{
	var result = new Array();
	try
	{
		if(localStorage[key])
			result = JSON.parse(localStorage[key])

		return result;
	}
	catch (e)
	{
		console.log(e);
		return result;
	}
}


var CommonUtil = new CommonUtility();

