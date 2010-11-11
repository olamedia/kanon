/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
(function($) {
	var settings = {
		html: '<div id="dialogbox" style="display:none;">\
<div class="loading"></div>\
<div class="body"></div>\
</div>'
	};	
	var methods = {
		clickHandler: function(){
			alert('click!!');
		},
		init: function(options){
			return this.each(function(){
				if (options) { 
					$.extend(settings, options);
				}
				var data = $(this).data('dialogbox');
				var dialog = $(settings.html);
				// If the plugin hasn't been initialized yet
				if (!data) {
					$(this).data('dialogbox', {
						target : $(this),
						dialog : dialog
					});
				}
				$(this).bind('click', methods.clickHandler);
			});
		},
		loading: function(){
			$('.dialogbox .body').empty();
			$('.dialogbox .loading').append('Загрузка...');
			$('.dialogbox').fadeIn('normal');
		},
		show: function(data){
			$('.dialogbox .content').append(data);
			$('.dialogbox .loading').remove();
			$('.dialogbox .body').children().fadeIn('normal');
			$('.dialogbox').css('left', $(window).width()/2 - ($('.dialogbox .content').width()/2));
			$('.dialogbox .close').click($.facebox.close)
			$('.elastic').elastic();
		}
	};
	$.fn.dialogbox = function(method) {
		if (methods[method]){
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		}else if(typeof method === 'object' || !method) {
			return methods.init.apply( this, arguments );
		}
		$.error('Method ' +  method + ' does not exist on jQuery.dialogbox');
		return null;
	}
})(jQuery);