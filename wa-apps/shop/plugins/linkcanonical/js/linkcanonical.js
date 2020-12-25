jQuery(function ($) {
    // setTimeout(function () {
    //     $('.plugin-settings').show();
    // }, 500);
	var LinkcanonicalOverlay = {
		el: $('.linkcanonical-overlay'),
		hide: function () {
			this.el.hide()
		},
		show: function () {
			this.el.show()
		}
	};

    $("#storefronts").change(function () {
	    LinkcanonicalOverlay.show();
	    var url = '?plugin=linkcanonical&module=settings&action=default&storefront=' + $(this).val();
	    $.ajax({
            url: url
        }).done(function(data) {
            $("#wa-plugins-content").html(data);
	        LinkcanonicalOverlay.hide();
        })
    });

	var Form = {
		el: $(".plugin-form"),
		init: function () {
			var _this = this;
			this.el.on('submit', function (event) {
				event.preventDefault();
				_this.send();
			});
		},
		clearStatus: function () {
			$('.ajax-status_type_success').hide();
			$('.ajax-status_type_loading').hide();
			$('.ajax-status_type_error').hide();
		},
		loading: function () {
			$('.ajax-status_type_loading').show();
		},
		success: function () {
			$('.ajax-status_type_success').show();
		},
		error: function () {
			$('.ajax-status_type_error').show();
		},
		send: function () {
			var _this = this;
			this.clearStatus();
			this.loading();

			$.post(
				this.el.attr('action'),
				this.el.serialize()
			).done(function() {
				_this.clearStatus();
				_this.success();
			}).fail(function() {
				_this.error();
			});

			setTimeout(function () {
				_this.clearStatus();
			}, 5000);

			return false;
		}
	};

	Form.init();
});