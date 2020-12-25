var Cart = ( function($) {

	Cart = function(options) {
		var that = this;

		// DOM
		that.$wrapper = ( options["$wrapper"] || false );
		that.$products = that.$wrapper.find(".cart-item");
		that.$services = that.$products.find(".cart-item__options");
		that.$cartAffiliateHint = $(".bonus-container");
		that.$cartWrapper = $(".cart-check");
		that.$cartDiscount = $("#sub-total__discount");
		that.$cartDiscountWrapper = $("#cart-discount-wrapper");
		that.$cartTotal = $(".s-cart-total");

		// VARS
		that.error_class = "error";

		// INIT
		this.bindEvents();
		this.initRelated();
	};

	// Events
	Cart.prototype.bindEvents = function() {
		var that = this,
			$wrapper = that.$wrapper,
			$product = that.$products,
			$productServices = that.$services;

		$product.on("click", ".qty__btn_incr", function() {
			var $currentProduct = $(this).closest(".cart-item");
			that.changeProductQuantity("positive", $currentProduct);
			return false;
		});

		$product.on("click", ".qty__btn_decr", function() {
			var $currentProduct = $(this).closest(".cart-item");
			that.changeProductQuantity("negative", $currentProduct);
			return false;
		});

		$product.on("change", ".qty__field", function() {
			that.onChangeProductQuantity( $(this) );
			return false;
		});

		$product.on("click", ".cart-item__clear", function() {
			that.deleteProduct( $(this).closest(".cart-item") );
			return false;
		});

		$productServices.on("change", "select", function() {
			that.onServiceChange( $(this) );
			return false;
		});

		$productServices.on("change", "input[type=\"checkbox\"]", function() {
			that.onServiceCheck( $(this) );
			return false;
		});

		$wrapper.on("change", "#use_affiliate", function () {
			that.changeAffiliate( $(this) );
			return false;
		});

		$wrapper.on("click", ".s-cart-submit", function () {
			that.submitForm( $(this).closest("form") );
		});

		$wrapper.on("click", ".pd-equal-item__to-cart", function() {
			that.onAddToCart( $(this) );
			return false;
		});
	};

	Cart.prototype.initRelated = function() {
		$(function() {
			if ($(".items-slider__body").length) {
				// Creating instances of all item sliders with one ititialization script
				//Otherwise we have to creating tens of such init scripts
				//This universal init which suits most of item sliders

				$(".items-slider__body_cart-cross").each(function(key, item) {
					//Uniqui name
					var sliderIdName = "sliderItems" + key;

					//Setting id to slider element
					this.id = sliderIdName;

					//Getting name with hash
					var sliderId = "#" + sliderIdName;

					//Setting unique id to parent block
					$(this)
						.closest(".items-slider")
						.attr("id", "items-" + sliderIdName);

					//Getting id of parent block
					var sliderParentElId =
						"#" +
						$(this)
							.closest(".items-slider")
							.attr("id");

					var sliderIdName = new Swiper(sliderId, {
						slidesPerView: "auto",
						// spaceBetween: 30,
						slidesPerGroup: 5,
						watchOverflow: true,
						navigation: {
							prevEl: sliderParentElId + " .items-slider__nav-left",
							nextEl: sliderParentElId + " .items-slider__nav-right"
						},
						breakpoints: {
							1281: {
								spaceBetween: 30
							},
							768: {
								slidesPerView: 3,
								spaceBetween: 20,
								slidesPerGroup: 3
							},
							320: {
								// slidesPerView: 2,
								spaceBetween: 10,
								slidesPerGroup: 2
							}
						}
					});
				});

				$(".items-slider").each(function() {
					var $this = $(this);

					if ($this.find(".items-slider__arrow .swiper-button-disabled").length == 2) {
						$this.find(".items-slider__arrow").hide();
					}
				});
			}
		});
	};

	Cart.prototype.onAddToCart = function( $link ) {
		var that = this,
		b = $link,
		$cartWrapper = that.$cartWrapper,
		f = b.closest(".pd-equal-item");
		f.addClass('is-loading');
		if (f.data('url')) {
			$.get(f.data('url'), function(html) {
				$('#extend-purchase .b-popup-it-opt__content').html(html);
                $('#extend-purchase .b-popup-it-opt__title').html(f.data("title"));
				if(typeof(jQuery.fn.stylizeInput) === "function") {
					$('#extend-purchase input[type=checkbox],#extend-purchase input[type=radio]').stylizeInput();
				}
				$.magnificPopup.open({
					items: {
						src: "#extend-purchase"
					},
					type: "inline",
					removalDelay: 0,
					mainClass: "mfp-zoom-in",
					callbacks: {
						open: function() {
							$("<a></a>", {text: "Close",href: "#"}).addClass("b-popup__close ds-hide").prependTo(".mfp-container").on("click", function() {
								$.magnificPopup.close();
							});
						},
						close: function() {
							$(".b-popup__close").remove();
						}
					}
				});
				f.removeClass('is-loading');
				$('.cart-popup__close').click(function(){
					$.magnificPopup.close();
					return false;
				 });
			});
			return false;
		}
		$.post(b.data('url'), {html: 1, product_id: b.data('product_id')}, function (response) {
			if (response.status == 'ok') {
				$cartWrapper.addClass("is-loading");
				var cart_total = $(".s-cart-total");
				$("#cart-content").load(location.href, function () {
					if(typeof(jQuery.fn.stylizeInput) === "function") {
						$('#cart-content input[type=checkbox],#cart-content input[type=radio]').stylizeInput();
					}
					cart_total.html(response.data.total);
				});
			}
			f.removeClass('is-loading');
		}, 'json');
    };

	Cart.prototype.onServiceCheck = function($input) {
		var that = this,
			$product = $input.closest(".cart-item"),
			$cartWrapper = that.$cartWrapper,
			$deferred = $.Deferred(),
			product_id = $product.data("id"),
			is_checked = $input.is(":checked"),
			input_val = $input.val(),
			$field = $('[name="service_variant[' + product_id + '][' + input_val + ']"]'),
			$service = $input.closest(".cart-item__options-i"),
			request_data = {};

		// Toggle service <select>
		if ($field.length) {
			if (is_checked) {
				$field.removeAttr('disabled');
			} else {
				$field.attr('disabled', 'disabled');
			}
		}

		if (is_checked) {
			request_data = {
				html: 1,
				parent_id: product_id,
				service_id: input_val
			};

			// If variants exits, adding to request_data
			if ($field.length) {
				request_data["service_variant_id"] = $field.val();
			}

			$cartWrapper.addClass("is-loading");
			$.post('add/', request_data, function(response) {
				$deferred.resolve(response);
			}, "json");

			$deferred.done( function(response) {
				// Set ID
				$service.data("id", response.data.id);

				// Set Product Total
				var $productTotal = $product.find(".cat-item__price-amount__total");
				$productTotal.html(response.data.item_total);

				// Update Cart Total
				that.updateCart($product, response.data);
			});

		} else {

			request_data = {
				html: 1,
				id: $service.data('id')
			};

			$cartWrapper.addClass("is-loading");
			$.post('delete/', request_data, function (response) {
				$deferred.resolve(response);
			}, "json");

			$deferred.done( function(response) {
				// Set ID
				$service.data('id', null);

				// Set Product Total
				var $productTotal = $product.find(".cat-item__price-amount__total");
				$productTotal.html(response.data.item_total);

				// Update Cart Total
				that.updateCart($product, response.data);
			});
		}

	};

	Cart.prototype.submitForm = function($form) {
		$form.append("<input type=\"hidden\" name=\"checkout\" value=\"1\">");
		$form.submit();
	};

	Cart.prototype.changeAffiliate = function($link) {
		var that = this,
			$form = $link.closest('form'),
			$cartWrapper = that.$cartWrapper,
			is_checked = $link.is(":checked");

		// Adding Affiliate Field
		if(!is_checked) {
			$form.append("<input type=\"hidden\" name=\"use_affiliate\" value=\"0\">");
		} else {
			$form.append("<input type=\"hidden\" name=\"use_affiliate\" value=\"1\">");
		}

		// Submit
		$cartWrapper.addClass("is-loading");
		$form.submit();
	};

	Cart.prototype.onServiceChange = function($select) {
		var that = this,
			$product = $select.closest(".cart-item"),
			$cartWrapper = that.$cartWrapper,
			$deferred = $.Deferred(),
			$service = $select.closest(".cart-item__options-i"),
			request_data = {
				html: 1,
				id: $service.data("id"),
				service_variant_id: $select.val()
			};

		$cartWrapper.addClass("is-loading");
		$.post("save/", request_data, function (response) {
			$deferred.resolve(response);
		}, "json");

		$deferred.done( function(response) {

			// Render Product Total
			$product.find('.cat-item__price-amount__total').html(response.data.item_total);

			// Render Cart Total
			that.updateCart($product, response.data);
		});
	};

	Cart.prototype.updateCart = function($product, data ) {
		var that = this,
			$cartTotal = that.$cartTotal,
			$cartWrapper = that.$cartWrapper,
			$cartDiscountWrapper = that.$cartDiscountWrapper,
			$cartAffiliateBonus = that.$cartAffiliateHint,
			$cartDiscount = that.$cartDiscount,
			loading_class = "is-loading",
			text = data["total"],
			count = data["count"];

		// Render Total
		$cartTotal.html(text);
		$cartWrapper.removeClass(loading_class);

		// Update Cart at Header
		if (text && count >= 0) {
			updateHeaderCart(data);
		}

		// Render Discount
		if (data.discount) {
			//$cartDiscountWrapper.show();
			$cartDiscount.html('&minus; ' + data.discount);
		} else {
			//$cartDiscountWrapper.hide();
		}

		// Render Affiliate Bonus
		if (data.affiliate_discount) {
			$cartWrapper
				.find(".cart-affiliate_currency")
				.html(data.affiliate_discount);
		}
		if (data.add_affiliate_bonus) {
			var add_affiliate_bonus_text = data.add_affiliate_bonus.match(/<strong.*?>(.*?)<\/strong>/);
			$cartAffiliateBonus
				.removeClass("is-hidden")
				.find(".bonus-container__text")
				.html(data.add_affiliate_bonus);
			$cartAffiliateBonus
				.find(".points-box strong")
				.html(add_affiliate_bonus_text[1].replace(/[^+\,\.\d]/g, ''));
		} else {
			$cartAffiliateBonus
				.addClass("is-hidden");
		}
	};

	Cart.prototype.changeProductQuantity = function(type, $product) {
		var that = this,
			$quantityInput = $product.find(".qty__field"),
			current_val = parseInt( $quantityInput.val() ),
			is_disabled = ( $quantityInput.attr("disabled") === "disabled"),
			disable_time = 800,
			new_val;

		if (type === "positive") {
			new_val = current_val + 1;
		} else if (type === "negative") {
			new_val = current_val - 1;
		}

		// Set new value
		if (!is_disabled) {

			if ( new_val > 0 ) {
				$quantityInput.attr("disabled","disabled");

				$quantityInput.val(new_val);

				// Recalculate Price
				$quantityInput.change();

				setTimeout( function() {
					$quantityInput.attr("disabled", false)
				}, disable_time);

				// If volume is zero => remove item from basket
			} else {

				this.deleteProduct($product );
			}
		}
	};

	Cart.prototype.onChangeProductQuantity = function( $input ) {
		var that = this,
			$product = $input.closest(".cart-item"),
			$cartWrapper = that.$cartWrapper,
			$deferred = $.Deferred(),
			$sum_wrapper = $product.find(".cat-item__price-amount__total"),
			product_quantity = parseInt( $input.val() ),
			request_data;

		// Check for STRING Data at Quantity Field
		if ( isNaN( product_quantity ) ) {
			product_quantity = 1;
		}
		$input.val( product_quantity );

		// Data for Request
		request_data  = {
			html: 1,
			id: $product.data('id'),
			quantity: product_quantity
		};

		// If Quantity 1 or more
		if (product_quantity > 0) {
			$cartWrapper.addClass("is-loading");
			$.post("save/", request_data, function (response) {
				$deferred.resolve(response);
			}, "json");

			$deferred.done( function(response) {
				$sum_wrapper.html( response.data.item_total );

				if (response.data.q) {
					$input.val( response.data.q );
				}

				if (response.data.error) {
					$input.addClass(that.error_class);

					// at Future make it better ( renderErrors(errors) )
					alert(response.data.error);

				} else {

					$input.removeClass(that.error_class);

				}

				that.updateCart($product, response.data);
			});

		// Delete Product
		} else if (product_quantity == 0) {
			that.deleteProduct($product );
		}
	};

	Cart.prototype.deleteProduct = function($product ) {
		var that = this,
			$cartWrapper = that.$cartWrapper,
			$deferred = $.Deferred(),
			request_data = {
				html: 1,
				id: $product.data('id')
			};

		$cartWrapper.addClass("is-loading");
		$product.addClass("is-loading");
		$.post("delete/", request_data, function (response) {
			$deferred.resolve(response);
		}, "json");

		$deferred.done( function(response) {
			if (response.data.count == 0) {
				location.reload();
			} else {
				$product.closest("li").remove();
				that.updateCart($product, response.data);
			}
		});

	};

	// Initialize
	return Cart;

})(jQuery);