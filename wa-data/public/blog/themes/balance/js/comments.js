//Scripts for Blog App
( function($) {

    var $form_wrapper = $("#b-comments-form"),
        $form = $form_wrapper.find("form"),
        $captcha = $(".wa-captcha"),
        $provider = $("#user-auth-provider"),
        $error_wrapper = $form.find(".errormsg").closest(".u-cen-txt"),
        current_provider = $provider.find(".selected").attr('data-provider');

    var comments_initialize = function() {
        bindEvents();
    };

    var bindEvents = function() {
		if( 'ontouchstart' in window ){ var click = 'touchstart'; }
		else { var click = 'click'; }

        $('.comments__btn-showform').on(click, function(){
			unsetReplyID();
            showForm();
            return false;
        });

        $(document).on("click",".rev-form__close-btn_js", function(){
            unsetReplyID();
			closeForm();
            return false;
        });

        $(document).on("click", ".rate-it__star_js", function() {
            setRating( $(this) );
            return false;
        });

        $(document).on("click", "#user-auth-provider a", function () {
            onProviderClick( $(this) );
            return false;
        });

        $(document).on("click", ".comment__btn", function() {
            setReplyID( $(this) );
            return false;
        });

        $(document).on("click", ".feedback-form__resetreplyto", function() {
            unsetReplyID();
            return false;
        });

        $form.submit( function(e) {
			e.preventDefault();
			console.log(123);
			submitForm();
            return false;
        });

    };

    var submitForm = function() {
        clearErrors();
        addComment();
    };

    var unsetReplyID = function() {
        var $form = $("#b-comments-form"),
            $input =  $form.find("input[name=\"parent\"]");

        $form.find(".rev-form__f-row_rating").removeClass("is-hidden");
		$input.val("0");
		if(!$(".comments-sec__form").hasClass("is-static"))
			$(".comments-sec__form").addClass("is-hidden").append($form);
    };

    var setReplyID = function( $link ) {
        var $comment = $link.closest(".comment"),
            parent_id = $comment.data("id"),
            $form = $("#b-comments-form"),
            $input =  $form.find("input[name=\"parent\"]");

        $form.find(".rev-form__f-row_rating").addClass("is-hidden");
		// Set Form data
        $input.val(parent_id);
        // Show form and notification
		//showForm();
        $link.after($form);
        // Animate
		$('html, body').animate({
			scrollTop: ($($form).offset().top)-50
		},300);
    };

    var onProviderClick = function( $link ) {
        if (!$link.hasClass('selected')) {
            $link.siblings(".selected")
                .removeClass("selected");

            $link.addClass('selected');

            var provider_name = $link.attr('data-provider');

            if (provider_name == 'guest') {
                $('.provider-fields').hide();
                $('.provider-fields[data-provider=guest]').show();
            }

            if (provider_name == current_provider) {
                $(".provider-fields").hide();
                $(".provider-fields[data-provider='+provider_name+']").show();

            }

            // Set input
            $form.find('input[name=auth_provider]').val(provider);

            window.open( $(this).attr('href'), "oauth");
        }
    };

    var clearCommentForm = function() {
		unsetReplyID();
		clearErrors();
		refreshCaptcha();
		$form.find("input, textarea").val("");
		$form.find(".rate-it__wrapper rate-it__star_js:last-child").click();
    };

    var addComment = function() {
		var $button = $form.find(".rev-form__btn");
		$error_wrapper.addClass("is-hidden");
		$button.attr("disabled","disabled").find(".is-status").addClass("is-hidden");
		$button.find(".rev-form__btn-loading").removeClass("is-hidden");
		console.log("sended");
        $.post( $form.attr('action')+'?json=1', $form.serialize(), function (r) {
			console.log(r);
            if (r.status === "fail") {
                showErrors($form, r.errors);
                refreshCaptcha();
				$button.find(".rev-form__btn-text").removeClass("is-hidden");
            } else {
				if(r.data.parent != "0" || r.data.parent > 0) {
					var tmp = $('<div></div>').html(r.data.template);
					$('#b-comments-list .comment[data-id="'+r.data.parent+'"]').append(tmp);
				} else {
					var tmp = $('<div></div>').html(r.data.template);
					if($('#b-comments-list').length)
						$('#b-comments-list').prepend(tmp);
					else
						location.href = $form.data('redirect-url');
				}
				clearCommentForm();
				$("#b-comments-count-str").text(r.data.count_str);
				$form_wrapper.closest('.item-info-feedback-form').hide();
				$('html, body').animate({
					scrollTop: ($('#b-comments-list .comment[data-id="'+r.data.comment_id+'"]').offset().top)-50
				},700);
				if(r.data.parent != "0" || r.data.parent > 0)
					$button.find(".rev-form__btn-text__reply").removeClass("is-hidden");
				else
					$button.find(".rev-form__btn-text").removeClass("is-hidden");
            }
			$button.removeAttr("disabled").find(".rev-form__btn-loading").addClass("is-hidden");
        },
        'json');
    };

    var clearErrors = function() {
		// remove error messages
		$form.find("div.errormsg").each(function(){
			$(this).remove();
		});
		// remove red borders
		$form.find(".error").each(function(){
			$(this).removeClass("error");
		});
    };

    var showErrors = function($form, errors) {
		console.log(errors);
		$(errors).each(function($name){
			var error = this;
			for(name in error) {
				if(name == "captcha") {
					$form.find(".rev-form__captcha-input").after("<div class='errormsg'>"+error[name]+"</div>");
					$form.find(".rev-form__captcha-input").find("input").addClass("error");
				} else {
					$form.find("#comment-"+name).addClass("error").after("<div class='errormsg'>"+error[name]+"</div>");
				}
			}
		});
    };

    var showForm = function() {
        $('.comments-sec__form').removeClass("is-hidden");
		// scrollTo form
		$('html, body').animate({
			scrollTop: ($('.comments-sec__form').offset().top - 50)
		},700);
    };

    var closeForm = function() {
        var formWrapper = $form_wrapper.closest('.comments-sec__form');
		if(formWrapper.length) {
			formWrapper.addClass("is-hidden");
		}
    };

    var setRating = function( $link ) {
        var $wrapper = $link.closest(".rate-it__wrapper"),
            $input = $wrapper.find("input[name=\"rate\"]"),
            rate_count = $link.data("rate-count"),
            $links = $wrapper.find(".rate-it__star_js"),
            full_rate_class = "star-colour";

		$links.removeClass("animate");
        if (rate_count && rate_count > 0) {
            // SET RATING
                // Clear old styles
                $links.find(".full").removeClass(full_rate_class);

                for ( var i = 0; i < rate_count; i++ ) {
                    $($links[i]).addClass("animate").find(".full").addClass(full_rate_class);
                }

            // SET FIELD VALUE
            $input.val(rate_count);
        }
    };

    var refreshCaptcha = function() {
        $(".rev-form__captcha-img img").trigger("click");
    };

    $(document).ready( function() {
        comments_initialize();
    });

})(jQuery);