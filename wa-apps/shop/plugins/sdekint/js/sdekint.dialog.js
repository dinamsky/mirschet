$.sdekint = $.sdekint || {};
$.extend($.sdekint, {
    Dialog: function (options) {
        this.$wrapper = $(options.html);
        this.$block = false;
        this.is_full_screen = this.$wrapper.hasClass("is-full-screen");
        if (this.is_full_screen) {
            this.$block = this.$wrapper.find(".sdekint-dialog-block");
        }

        // VARS
        this.position = (options.position || false);

        // DYNAMIC VARS
        this.is_closed = false;

        //
        this.userPosition = (options.setPosition || false);

        // HELPERS
        this.onBgClick = (options.onBgClick || false);
        this.onOpen = (options.onOpen || function () {
        });
        this.onClose = (options.onClose || function () {
        });
        this.onRefresh = (options.onRefresh || false);
        this.onResize = (options.onResize || false);
        this.canBeClosed = options.canBeClosed || function () {
            return true;
        };

        this.initClass = function () {
            this.$wrapper.data('sdekintDialog', this);
            this.show();
            this.bindEvents();
        };

        this.bindEvents = function () {
            var
                $document = $(document),
                $block = (this.$block) ? this.$block : this.$wrapper;

            var
                close = function () {
                    if (!this.is_closed && this.canBeClosed()) {
                        this.close();
                    } else if (!this.canBeClosed()) {
                        return;
                    }
                    $document.off("click", close);
                    $document.off("wa_before_load", close);
                }.bind(this),

                onResize = function () {
                    var is_exist = $.contains(document, this.$wrapper[0]);
                    if (is_exist) {
                        this.resize();
                    } else {
                        $(window).off("resize", onResize);
                    }
                }.bind(this);

            // Delay binding close events so that dialog does not close immediately
            // from the same click that opened it.
            setTimeout(function () {
                $document.on("click", close).on("wa_before_load", close);
                this.$wrapper.on("close", close);

                // Click on background, default nothing
                if (this.is_full_screen) {
                    this.$wrapper.on("click", ".sdekint-dialog-background", function (event) {
                        if (!this.onBgClick) {
                            event.stopPropagation();
                        } else {
                            this.onBgClick(event);
                        }
                    }.bind(this));
                }

                $block.on("click", function (event) {
                    event.stopPropagation();
                });

                $document.on("keyup", function (event) {
                    var escape_code = 27;
                    if (event.keyCode === escape_code) {
                        this.close();
                    }
                }.bind(this));

                $block.on("click", ".js-close-dialog", function () {
                    close();
                });


                if (this.is_full_screen) {
                    $(window).on("resize", onResize);
                }
            }.bind(this), 0);
        };

        this.show = function () {
            $("body").append(this.$wrapper);
            this.setPosition();
            this.onOpen(this.$wrapper, this);
        };

        this.setPosition = function (blocksize) {
            var $window = $(window),
                window_w = $window.width(),
                window_h = (this.is_full_screen) ? $window.height() : $(document).height(),
                $block = (this.$block) ? this.$block : this.$wrapper,
                wrapper_w = blocksize && blocksize.width ? blocksize.width : $block.outerWidth(),
                wrapper_h = blocksize && blocksize.height ? blocksize.height : $block.outerHeight(),
                pad = 10;

            var getDefaultPosition = function (area) {
                // var scrollTop = $(window).scrollTop();

                return {
                    left: Math.ceil((parseInt(('' + window_w).trim()) - parseInt(('' + area.width).trim())) / 2),
                    top: Math.ceil((parseInt(('' + window_h).trim()) - parseInt(('' + area.height).trim())) / 2) // + scrollTop
                };
            };

            var css;

            if (this.position) {
                css = this.position;
            } else {
                var getPosition = (this.userPosition) ? this.userPosition : getDefaultPosition;
                css = getPosition({
                    width: wrapper_w,
                    height: wrapper_h
                });
            }

            if (css.left > 0) {
                if (css.left + wrapper_w > window_w) {
                    css.left = window_w - wrapper_w - pad;
                }
            }

            if (css.top > 0) {
                if (css.top + wrapper_h > window_h) {
                    css.top = window_h - wrapper_h - pad;
                }
            } else {
                css.top = pad;

                if (this.is_full_screen) {
                    var $content = $block.find(".sdekint-dialog-content");

                    $content.hide();

                    var block_h = $block.outerHeight(),
                        content_h = window_h - block_h - pad * 2;

                    $content
                        .height(content_h)
                        .addClass("is-long-content")
                        .show();

                }
            }

            if (blocksize) {
                $.extend(css, {
                    width: blocksize.width + 'px',
                    height: blocksize.height + 'px'
                });
            } else {
                $.extend(css, {width: $block.css('width'), height: $block.css('height')});
            }

            $block.css(css);

            return this;
        };

        this.close = function () {
            if (!this.canBeClosed()) {
                return;
            }
            this.is_closed = true;
            this.$wrapper.remove();
            this.onClose(this.$wrapper, this);
        };

        this.refresh = function () {
            if (this.onRefresh) {
                //
                this.onRefresh();
                //
                this.close();
            }
        };

        this.resize = function (blocksize) {
            var animate_class = "is-animated",
                do_animate = true;

            if (do_animate) {
                this.$block.addClass(animate_class);
            }

            this.setPosition(blocksize);

            if (this.onResize) {
                this.onResize(this.$wrapper, this);
            }
        };

        // INIT
        this.initClass();

    }
});