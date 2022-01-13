		(function($){

			$(document).ready(function(){

				var $body = $('body');

				$('.icon-picker').qlIconPicker({
					'save': 'class'
				});
				$('.save-code').qlIconPicker({
					'save': 'code'
				});
				$('.icon-large').qlIconPicker({
					'size': 'large',
					'mode': 'inline'
				});
				$('.icon-dontclose').qlIconPicker({
					'mode': 'inline',
					'closeOnPick': false
				});

				$body.on('iconselected.queryloop', function(e, icon){
					console.log('Icon selected: ' + icon);
				});
				$body.on('iconpickershow.queryloop', function(e, mode){
					console.log('Icon picker shown, mode: ' + mode);
				});
				$body.on('iconpickerclose.queryloop', function(e, mode){
					console.log('Icon picker closed, mode: ' + mode);
				});

			});

		})(jQuery);


