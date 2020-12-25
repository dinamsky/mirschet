// Product Class
var Product = ( function($) {

    Product = function(options) {
        var that = this;

        // DOM
        that.$form = options["$form"];
        that.$price = that.$form.find(".s-product-price");
        that.$comparePrice = that.$form.find(".s-product-oldprice");
        that.$button = that.$form.find(".pd-cart__add-cart");
        that.$quantity = that.$form.find(".qty__field");
		that.$product = that.$form.closest(".item-pg__product");

        // VARS
        that.is_dialog = ( options["is_dialog"] || false );
        that.volume = 1;
        that.affiliate_rate = options["affiliate_rate"];
        that.saving = options["saving"];
        that.saving_percent_min = options["saving_percent_min"];
        that.saving_currency_min = options["saving_currency_min"];
        that.saving_rounding = options["saving_rounding"];
        that.currency = options["currency"];
        that.services = options["services"];
        that.features = options["features"];

        // DYNAMIC VARS
        that.price = parseFloat( that.$price.data("price") );
        that.compare_price = parseFloat( that.$comparePrice.data("compare-price") );

        // INIT
        that.initProduct();
    };

	Product.prototype.initProduct = function() {
        var that = this;

        //
        that.bindEvents();
        that.initGallery();

        $selected = that.$form.find(".pd-options-list__body .active, .pd-options-list__body .active");
		if($selected.length) {
			$selected.trigger('click');
		} else {
			initFirstSku();
		}

        function initFirstSku() {
            var $skuFeature = that.$form.find(".sku-feature:first"),
                is_buttons_view_type = $skuFeature.length;

            // for sku buttons type
            if (is_buttons_view_type) {
                initFirstButton( $skuFeature );

            // for sku radio type
            } else {
                var $radio = getRadioInput();
                if ($radio) {
                    $radio.click();
                }
            }

            function getRadioInput() {
                var $radios = that.$form.find(".skus input[type=radio]"),
                    result = false;

                $.each($radios, function() {
                    var $radio = $(this),
                        is_enabled = !( $radio.attr("disabled") && ($radio.attr("disabled") == "disabled") ),
                        is_checked = ( $radio.attr("checked") && ($radio.attr("checked") == "checked") );

                    if ( is_enabled && (!result || is_checked) ) {
                        result = $radio;
                    }
                });

                return result;
            }

            function initFirstButton( $skuFeature ) {
                var $wrapper = that.$form.find(".pd-options-list"),
					$groups = $wrapper.find(".pd-options-list__item"),
					groups = getGroupsData( $groups ),
					availableSku = getAvailableSku( groups );

				if (availableSku) {
					$.each(availableSku.$links, function() {
						$(this).click();
					});
				}

				function getGroupsData( $groups ) {
					var result = [];

					$.each($groups, function() {
						var $group = $(this),
							$links = $group.find("li"),
							linkArray = [];

						$.each($links, function() {
							var $link = $(this),
								id = $link.data("sku-id");

							linkArray.push({
								id: id,
								$link: $link
							});
						});

						result.push(linkArray);
					});

					return result;
				}

				function getAvailableSku( groups ) {
					function selectionIsGood(prefix) {
						var skuData = getSkuData( prefix ),
							sku = checkSku( skuData.id ),
							result = false;

						if (sku) {
							result = {
								sku: sku,
								$links: skuData.$links
							}
						}
						return result;
					}

					function getFirstWorking(groups, prefix) {
						if (!groups.length) {
							return selectionIsGood(prefix);
						}

						prefix = prefix || [];

						var group = groups[0],
							other_groups = groups.slice(1);

						for (var i = 0; i < group.length; i++) {
							var new_prefix = prefix.slice();
							new_prefix.push(group[i]);
							var result = getFirstWorking(other_groups, new_prefix);
							if (result) {
								return result;
							}
						}

						return null;
					}

					return getFirstWorking(groups);

					function getSkuData( sku_array ) {
						var id = [],
							$links = [];
						$.each(sku_array, function(index, item) {
							id.push(item.id);
							$links.push(item.$link);
						});

						return {
							id: id.join(""),
							$links: $links
						};
					}
				}

				function checkSku( skus_id ) {
					var result = false;

					if (that.features.hasOwnProperty(skus_id)) {
						var sku = that.features[skus_id];
						if (sku.available) {
							result = sku;
						}
					}

					return result;
				}
            }
        }

		initImageGallery();
		function initImageGallery() {
			var itemPageThumbs = $(".slideshow-thumbs__list");

			$('.pd-image__thumbs-slider').on('click', '.pd-thumb', function(){
				
				var $this = $(this),
				imageContainer = $('#product-core-image'),
				videoContainer = $('#video-container'),
				size = imageContainer.find('img').attr('src').replace(/^.*\/[^\/]+\.(.*)\.[^\.]*$/, '$1'),
				src = $this.find('img').attr('src').replace(/^(.*\/[^\/]+\.)(.*)(\.[^\.]*)$/, '$1' + size + '$3');
				
				if(!$this.hasClass('is-current')) {//Prevent multiple load of the image

					imageContainer.addClass('blurred');
					$("#switching-image").show();
					$this.addClass('is-current').siblings().removeClass('is-current');

					if($this.hasClass('is-image')) {
						imageContainer.show();
						videoContainer.hide();
						$('<img>').attr('src', src).load(function () {
							$("#product-core-image").find('img').attr('src', src);
							imageContainer.removeClass('blurred');
							$("#switching-image").hide();
						}).each(function() {
							//ensure image load is fired. Fixes opera loading bug
							if (this.complete) { $(this).trigger("load"); }
						});
					} else {
						imageContainer.removeClass('blurred');
						$("#switching-image").hide();
						imageContainer.hide();
						videoContainer.show();
					}
				}
			});

			var p_img_touch_currentX;
			var p_img_touch_lastX = 0;
			var p_img_touch_lastT;
			$('#product-core-image').on('touchmove',function(e){
				clearTimeout(p_img_touch_lastT);
				p_img_touch_currentX = e.originalEvent.touches[0].clientX;
				if(p_img_touch_lastX == 0) {
					p_img_touch_lastX = p_img_touch_currentX;
				}
				p_img_touch_lastT = setTimeout(function() {
					if(p_img_touch_currentX < p_img_touch_lastX) {
						if($(".pd-image__thumbs-slider .is-current").next(".pd-thumb").length) {
							$(".pd-image__thumbs-slider .is-current").next(".pd-thumb").click();
						}
					} else if(p_img_touch_currentX > p_img_touch_lastX){
						if($(".pd-image__thumbs-slider .is-current").prev(".pd-thumb").length) {
							$(".pd-image__thumbs-slider .is-current").prev(".pd-thumb").click();
						}
					}
					p_img_touch_lastX = 0;
				}, 150);
			});
		}
		
    };

    //
    Product.prototype.initGallery = function() {
		if($("#ip-gallery").length) {
			$('#product-core-image').on('click', function() {
				var photoIndex = 0;
				var $this = $(this);

				photoIndex = $(".pd-image__thumbs-slider .pd-thumb.is-image.is-current").index();

				$.magnificPopup.open({
					items: {
						src: '#ip-gallery', // can be a HTML string, jQuery object, or CSS selector
						type: 'inline'
					},
					closeBtnInside: true,
					removalDelay: 300,
					mainClass: 'mfp-zoom-in',
					callbacks: {
						beforeOpen: function() {},
						open: function() {
							//Добавляем кнопку Х для закрытия попопа
							$('<a></a>', {
								text: 'Close',
								href: '#'
							}).addClass('b-popup__close ds-hide')
								.prependTo('.mfp-container')
								.on('click', function() {
									$.magnificPopup.close();
								});
							$('.mfp-content').addClass('b-popup-container b-popup-container_gallery');

							var galleryThumbs = new Swiper('.gallery-thumbs', {
								spaceBetween: 10,
								slidesPerView: 4,
								watchSlidesVisibility: true,
								watchSlidesProgress: true
							});
							var galleryTop = new Swiper('.gallery-top', {
								spaceBetween: 10,
								navigation: {
									prevEl: '.pd-image-gallery__left',
									nextEl: '.pd-image-gallery__right'
								},
								thumbs: {
									swiper: galleryThumbs
								},
								keyboard: {
									enabled: true,
									onlyInViewport: false,
								},
								on: {
									init: function () {
										this.slideTo(photoIndex,100);
									},
								},
							});
						},
						close: function() {}
					}
				});
			});
		}
    };

    //
    Product.prototype.bindEvents = function() {
        var that = this;

        // services
        that.$form.find(".s-product-services input[type=checkbox]").on("click", function () {
            that.onServiceClick( $(this) );
        });
        that.$form.find(".s-product-services .service-variants").on("change", function () {
            that.updatePrice();
        });

        that.$form.find('.pd-options-list__body .pd-color, .pd-options-list__body .btn-option').on("click", function () {
            that.onSelectClick( $(this) );
            return false;
        });

        that.$form.find(".skus input[type=radio]").on("click", function () {
            that.onSkusClick( $(this) );
        });

        that.$form.find(".sku-feature").change( function () {
            that.onSkusChange( $(this) );
        });

        that.$form.on("submit", function () {
            that.onFormSubmit( $(this) );
            return false;
        });

        that.$quantity.parent().find(".qty__btn_incr").on("click", function(e) {
			e.preventDefault();
            that.increaseVolume( true );
            return false;
        });

        that.$quantity.parent().find(".qty__btn_decr").on("click", function(e) {
            e.preventDefault();
			that.increaseVolume( false );
            return false;
        });

        that.$quantity.on("change", function() {
            that.prepareChangeVolume( $(this) );
            return false;
        });

    };

    //
    Product.prototype.onFormSubmit = function( $form ) {
        var that = this,
			$button = that.$button,
            href = $form.attr('action') + '?items=1&html=1',
            dataArray = $form.serialize();

		if($("#added-to-cart").length) { // big popup
			$("#added-to-cart .js-item__title").html($form.find('.js-product_title').html());
			$("#added-to-cart .b-popup-cart__price-new").html($form.find('.s-product-price').html());
			if(!$form.find('.s-product-oldprice').hasClass('is-hidden'))
				$("#added-to-cart .b-popup-cart__price-old").html($form.find('.s-product-oldprice').html()).show();
			else
				$("#added-to-cart .b-popup-cart__price-old").html("").hide();
			if($form.closest(".item-pg__product").find('#product-image').length)
				$("#added-to-cart .b-popup-cart__image img").attr("src",$form.closest(".item-pg__product").find('#product-image').attr('src'));
			else
				$("#added-to-cart .b-popup-cart__image img").attr("src",$form.data('image'));
			if(parseInt($form.find(".qty__inner input").val()) > 1)
				$("#added-to-cart .js-item__quantity").show().find("span").html($form.find(".qty__inner input").val());
			else
				$("#added-to-cart .js-item__quantity").hide();
		}
		$button.addClass("is-adding-cart");
        $.post(href, dataArray, function (response) {
			$button.removeClass("is-adding-cart");
            if (response.status == 'ok') {
                if (that.is_dialog) {
                    $.magnificPopup.close();
				}
				if($("#cart-content").length) {
					$(".cart-check").addClass("is-loading");
					var cart_total = $(".total__amount");
					$("#cart-content").load(location.href, function () {
						if(typeof(jQuery.fn.stylizeInput) === "function") {
							$('#cart-content input[type=checkbox],#cart-content input[type=radio]').stylizeInput();
						}
						cart_total.html(response.data.total);
					});
					return false;
				}
                // Update cart counters
				updateHeaderCart(response.data);
				// construct popup
				if($(".b-popup_cart-1").length) { // big popup
					$.magnificPopup.open({
						items: {
							src: "#added-to-cart"
						},
						type: "inline",
						removalDelay: 300,
						mainClass: "mfp-zoom-in",
						callbacks: {
							open: function() {
								$("<a></a>", {text: "Close",href: "#"}).addClass("b-popup__close ds-hide").prependTo(".mfp-container").on("click", function() {
									$.magnificPopup.close();
								});
								$(".mfp-content").addClass("b-popup-container b-popup-container_default");
							},
							close: function() {
								$(".b-popup__close").remove();
							}
						}
					});
				} else if($(".b-popup_cart-2").length) { // fixed top popup
					if(typeof (_balance_added_to_cart_timer) == "number")
						clearInterval(_balance_added_to_cart_timer);

					$(".b-popup_cart-2").addClass("is-shown");
					var countNum = parseInt($(".b-popup-cart__notif .countdown").data('ttl'));
					if(countNum <= 0)
						countNum = 5;

					$(".b-popup-cart__notif .countdown").html(countNum);

					_balance_added_to_cart_timer = setInterval(function() {
						if (countNum === 0) {
							$(".b-popup_cart-2").removeClass("is-shown");
							clearInterval(_balance_added_to_cart_timer);
						} else {
							countNum--;
							$(".b-popup-cart__notif .countdown").html(countNum);
						}
					}, 1000);
					$(".b-popup-cart__close-btn").on("click", function() {
						$(".b-popup_cart-2").removeClass("is-shown");
						clearInterval(_balance_added_to_cart_timer);
					});
				}
                if (response.data.error)
                    alert(response.data.error);

            } else if (response.status == 'fail') {
                alert(response.errors);
            }

        }, "json");
    };

    //
    Product.prototype.onSkusChange = function() {
        var that = this;

        // DOM
        var $form = that.$form,
            $comparePrice = that.$comparePrice,
            $product = that.$product,
            $button = that.$button,
            $quantity = that.$quantity;

        var key = getKey(),
            sku = that.features[key];

        if (sku) {
            that.updateSkuServices(sku.id);
            if ($(".s-product-sku-"+sku.id).length) {
                $(".s-product-sku-"+sku.id).siblings().hide();
                $(".s-product-sku-"+sku.id).show();
				$(".s-product-sku").removeClass("is-hidden");
            } else {
				$(".s-product-sku").addClass("is-hidden");
			}
            if (sku.image_id) {
                that.changeImage(sku.image_id);
            }
            if (sku.available) {
                $button.removeAttr('disabled');
				$quantity.removeAttr('disabled').closest(".qty").removeClass("qty_disabled");
            } else {
                $product.find(".s-product-stock, .sku-no-stock").hide();
                $product.find(".sku-no-stock").show();
                $button.attr('disabled', 'disabled');
				$quantity.attr('disabled', 'disabled').closest(".qty").addClass("qty_disabled");
            }

            //
            sku["compare_price"] = ( sku["compare_price"] ) ? sku["compare_price"] : 0 ;
            //
            that.updatePrice(sku["price"], sku["compare_price"]);

        } else {
			$(".s-product-sku").addClass("is-hidden");
            //
            $product.find(".s-product-stock, .sku-no-stock").hide();
            //
            $product.find(".sku-no-stock").show();
            //
            $button.attr('disabled', 'disabled');
            //
            $quantity.attr('disabled', 'disabled').closest(".qty").addClass("qty_disabled");
            //
            that.$comparePrice.hide();
            //
            that.$price.empty();
        }
		// tippy "jump" fix
		window.scrollTo(window.scrollX, window.scrollY - 1);
		window.scrollTo(window.scrollX, window.scrollY + 1);

        function getKey() {
            var result = "";

            $form.find(".sku-feature").each( function () {
                var $input = $(this);

                result += $input.data("feature-id") + ':' + $input.val() + ';';
            });

            return result;
        }
    };

    //
    Product.prototype.onSkusClick = function( $link ) {
        var that = this,
            sku_id = $link.val(),
            price = $link.data("price"),
            compare_price = $link.data("compare-price"),
            sku = $link.data("sku"),
            $quantity = that.$quantity,
            image_id = $link.data('image-id');

        // DOM
        var $button = that.$button;

        if (image_id) {
            that.changeImage(image_id);
        }
        if (sku) {
            $(".s-product-sku").removeClass("is-hidden").find("span").html(" "+sku);
        } else {
			$(".s-product-sku").addClass("is-hidden");
		}

        if ($link.data('disabled')) {
            $button.attr('disabled', 'disabled');
			$quantity.attr('disabled', 'disabled').closest(".qty").addClass("qty_disabled");
        } else {
            $button.removeAttr('disabled');
			$quantity.removeAttr('disabled').closest(".qty").removeClass("qty_disabled");
        }

        //
        that.updateSkuServices(sku_id);
        //
        that.updatePrice(price, compare_price);
    };

    //
    Product.prototype.onSelectClick = function( $link ) {
        var data = $link.data("value");
        $link.closest('.pd-options-list__body').find('.sku-feature').val(data).change();
		$link.addClass("active").closest(".btn-options__i, .pd-colors__i").siblings().find(".btn-option, .pd-color").removeClass("active");
    };

    //
    Product.prototype.onServiceClick = function( $input ) {
        var that = this,
            $select = that.$form.find("select[name=\"service_variant[" + $input.val() + "]\"]");

        if ($select.length) {
            if ( $input.is(":checked") ) {
                $select.removeAttr("disabled");

            } else {
                $select.attr("disabled", "disabled");
            }
        }

        //
        that.updatePrice();
    };

	

    // Preparing to change volume
    Product.prototype.prepareChangeVolume = function( $input ) {
        var that = this,
            new_volume = parseFloat( $input.val() );

        // AntiWord at Field
        if (new_volume) {
            $input.val( new_volume );
            that.changeVolume( new_volume );

        } else {
            $input.val( that.volume );
        }
    };

    // Change Volume
    Product.prototype.changeVolume = function( type ) {
        var that = this,
            $quantity = that.$quantity,
            current_val = parseInt( $quantity.val() ),
            input_max_data = parseInt( $quantity.data("max-quantity")),
            max_val = ( isNaN(input_max_data) || input_max_data === 0 ) ? Infinity : input_max_data,
            new_val;

        if ( type > 0 && type !== that.volume ) {
            if (current_val <= 0) {
                if ( that.volume > 1 ) {
                    new_val = 1;
                }

            } else if (current_val >= max_val) {
                new_val = max_val;

            } else {
                new_val = current_val;
            }
        }

        // Set product data
        if (new_val) {
            that.volume = new_val;

            // Set new value
            $quantity.val(new_val);

            // Update Price
            that.updatePrice();
        }
    };

    Product.prototype.increaseVolume = function( type ) {
        var that = this,
            new_val;

        // If click "+" button
        if ( type ) {
            new_val = that.volume + 1;

        } else {
            new_val = that.volume - 1;
        }

        that.$quantity
            .val(new_val)
            .trigger("change");

    };

    // Replace price to site format
    Product.prototype.currencyFormat = function (number, no_html) {
        // Format a number with grouped thousands
        //
        // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +	 bugfix by: Michael White (http://crestidg.com)

        var that = this;
        var i, j, kw, kd, km;
        var decimals = that.currency.frac_digits;
        var dec_point = that.currency.decimal_point;
        var thousands_sep = that.currency.thousands_sep;

        // input sanitation & defaults
        if( isNaN(decimals = Math.abs(decimals)) ){
            decimals = 2;
        }
        if( dec_point == undefined ){
            dec_point = ",";
        }
        if( thousands_sep == undefined ){
            thousands_sep = ".";
        }

        i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

        if( (j = i.length) > 3 ){
            j = j % 3;
        } else{
            j = 0;
        }

        km = (j ? i.substr(0, j) + thousands_sep : "");
        kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
        //kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).slice(2) : "");
        kd = (decimals && (number - i) ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");

        number = km + kw + kd;
        var s = no_html ? that.currency.sign : that.currency.sign_html;
        if (!that.currency.sign_position) {
            return s + that.currency.sign_delim + number;
        } else {
            return number + that.currency.sign_delim + s;
        }
    };

    //
    Product.prototype.serviceVariantHtml= function (id, name, price) {
        var that = this;
        return $('<option data-price="' + price + '" value="' + id + '"></option>').text(name + ' (+' + that.currencyFormat(price, 1) + ')');
    };

    //
    Product.prototype.updateSkuServices = function(sku_id) {
        var that = this,
            $form = that.$form,
            $product = that.$product,
            $skuStock = $product.find(".sku-" + sku_id + "-stock"),
            sku_count = $skuStock.data("sku-count");

        if ( !(sku_count && sku_count > 0) ) {
            sku_count = null;
        }

        // Hide others
        $product.find(".s-product-stock, .sku-no-stock").hide();

        // Show
        $skuStock.show();

        for (var service_id in that.services[sku_id]) {

            var v = that.services[sku_id][service_id];

            if (v === false) {
                $form.find(".service-" + service_id).hide().find('input,select').attr('disabled', 'disabled').removeAttr('checked');

            } else {
                $form
                    .find(".service-" + service_id)
                        .show()
                        .find('input')
                            .removeAttr('disabled');

                if (typeof (v) == 'string') {
                    $form.find(".service-" + service_id + ' .service-price').html( that.currencyFormat(v) );
                    $form.find(".service-" + service_id + ' input').data('price', v);

                } else {

                    var select = $form.find(".service-" + service_id + ' .service-variants');
                    var selected_variant_id = select.val();

                    for (var variant_id in v) {
                        var obj = select.find('option[value=' + variant_id + ']');

                        if (v[variant_id] === false) {
                            obj.hide();

                            if (obj.attr('value') == selected_variant_id) {
                                selected_variant_id = false;
                            }

                        } else {

                            if (!selected_variant_id) {
                                selected_variant_id = variant_id;
                            }

                            obj.replaceWith(that.serviceVariantHtml(variant_id, v[variant_id][0], v[variant_id][1]));
                        }
                    }

                    $form.find(".service-" + service_id + ' .service-variants').val(selected_variant_id);
                }
            }
        }
    };

    // Update Price
    Product.prototype.updatePrice = function(price, compare_price) {
        var that = this;

        var hidden_class = "is-hidden";

        // DOM
        var $form = that.$form,
            $price = that.$price,
            $compare = that.$comparePrice;

        // VARS
        var services_price = getServicePrice(),
            price_sum,
            compare_sum;

        //
        if (price) {
            that.price = price;
            $price.data("price", price);
        } else {
            price = that.price;
        }

        //
        if (compare_price >= 0) {
            that.compare_price = compare_price;
            $compare.data("price", compare_price);
        } else {
            compare_price = that.compare_price;
        }

        //
        price_sum = (price + services_price) * 1;
        compare_sum = (compare_price + services_price) * 1;

        // Render Price
        $price.html( that.currencyFormat(price_sum) );
        $compare.html( that.currencyFormat(compare_sum) );

        // Render Compare
        if (compare_price > 0) {
			var show_saving = false,
			saving_variants_visible = 0;
            $compare.removeClass(hidden_class);
			if(that.saving == "full" || that.saving == "percent") {
				var saving_percent = 0;
				saving_percent = ((compare_price - price)*100)/compare_price;
				if(that.saving_rounding == 'floor')
					saving_percent = Math.floor(saving_percent);
				else if(that.saving_rounding == 'ceil')
					saving_percent = Math.ceil(saving_percent);
				else if(that.saving_rounding == 'round')
					saving_percent = Math.round(saving_percent);
				if(saving_percent >= that.saving_percent_min) {
					saving_variants_visible++;
					$form.find('.s-saving-percent').html(saving_percent+'%').removeClass(hidden_class);
				} else {
					$form.find('.s-saving-percent').addClass(hidden_class);
				}
			}
			if(that.saving == "full" || that.saving == "currency") {
				var saving_currency = 0;
				saving_currency = (compare_price - price);
				if(saving_currency >= that.saving_currency_min) {
					saving_variants_visible++;
					$form.find('.s-saving-currency').html(that.currencyFormat(saving_currency, false)).removeClass(hidden_class);
				} else {
					$form.find('.s-saving-currency').addClass(hidden_class);
				}
			}
			if(saving_variants_visible>0)
				$form.find('.s-product-saving').removeClass(hidden_class);
			else
				$form.find('.s-product-saving').addClass(hidden_class);
			if(saving_variants_visible == 1) {
				$form.find('.s-saving-percent + span').addClass(hidden_class);
			} else {
				$form.find('.s-saving-percent + span').removeClass(hidden_class);
			}
        } else {
            $compare.addClass(hidden_class);
			if(that.saving)
				$form.find('.s-product-saving').addClass(hidden_class);
        }

		// affiliate bonus
		if(that.affiliate_rate > 0) {
			var bonus = 0,
			volume = parseInt($form.find("input[name=\"quantity\"]").val());
			if(volume > 0)
				$(".s-product-bonuspoints").text("+"+Math.ceil((price_sum*volume) / that.affiliate_rate));
			else
				$(".s-product-bonuspoints").text("+"+Math.ceil(price_sum / that.affiliate_rate));
		}

        //
        function getServicePrice() {
            // DOM
            var $checkedServices = $form.find(".s-product-services input:checked");

            // DYNAMIC VARS
            var services_price = 0;

            $checkedServices.each( function () {
                var $service = $(this),
                    service_value = $service.val(),
                    service_price = 0;

                var $serviceVariants = $form.find(".service-" + service_value).next().find(".service-variants");

                if ($serviceVariants.length) {
                    service_price = parseFloat( $serviceVariants.find(":selected").data("price") );
                } else {
                    service_price = parseFloat( $service.data("price") );
                }

                services_price += service_price;
            });

            return services_price;
        }
    };

    Product.prototype.changeImage = function( image_id ) {
        if (image_id) {
            var $imageLink = $("#product-image-" + image_id);
            if ($imageLink.length) {
                $imageLink.click();
            }
        }
    };

    return Product;

})(jQuery);

