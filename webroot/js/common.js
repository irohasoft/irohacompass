/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
 

function CommonUtility() {}

// リッチテキストエディタの設定
CommonUtility.prototype.setRichTextEditor = function (selector, use_upload_image, base_url)
{
	$(selector).summernote({
		lang: "ja-JP",
		maximumImageFileSize: (1024 * 500),
		callbacks: {
			onImageUpload: function(files)
			{
				var data = new FormData();
				data.append("file", files[0]);
				
				$.ajax({
					data: data,
					type: 'POST',
					url: base_url + 'admin/contents/upload_image',
					cache: false,
					contentType: false,
					processData: false,
					success: function(url) {
						$(selector).summernote('insertImage', JSON.parse(url)[0], 'image');
					}
				});
			},
			onImageUploadError: function(e)
			{
				alert('画像のアップロードに失敗しました');
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
			label: 'アクセス回数',
			borderColor: window.chartColors.blue,
			borderWidth: 2,
			fill: false,
			data: access_data,
				yAxisID: "y-axis-1",
		}, {
			type: 'bar',
			label: '進捗更新回数',
			backgroundColor: window.chartColors.green,
			data: progress_data,
				yAxisID: "y-axis-2",
		}]
	};
	
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
//		                stepSize: 10
		            },
		        }, {
		            id: "y-axis-2",
		            type: "linear", 
		            position: "right",
		            ticks: {
						min:0,
						max: 10,
		                stepSize: 1
		            },
		            gridLines: {
		                drawOnChartArea: false, 
		            },
		        }],
		    }
		}
	});
}

var CommonUtil = new CommonUtility();

