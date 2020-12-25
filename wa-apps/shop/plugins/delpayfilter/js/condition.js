$.delpayfilter_conditions = {
    urls: {
        cat: '?plugin=delpayfilter&action=handler&data=getCategoryJson',
        set: '?plugin=delpayfilter&action=handler&data=getSetJson',
        type: '?plugin=delpayfilter&action=handler&data=getTypeJson',
        product: '?plugin=delpayfilter&module=dialog&action=getProducts',
        feature: '?plugin=delpayfilter&action=handler&data=getFeatureJson',
        featureValues: '?plugin=delpayfilter&action=handler&data=getFeatureValuesJson',
        services: '?plugin=delpayfilter&action=handler&data=getServicesJson',
        servicesVariants: '?plugin=delpayfilter&action=handler&data=getServicesVariantsJson',
        ucat: '?plugin=delpayfilter&action=handler&data=getUserCategoryJson',
        user: '?plugin=delpayfilter&module=dialog&action=getUsers',
        autocomplete_product: '?plugin=delpayfilter&action=productAutocomplete',
        autocomplete_contact: '?plugin=delpayfilter&action=contactAutocomplete',
        shipping: '?plugin=delpayfilter&action=handler&data=getShippingJson',
        payment: '?plugin=delpayfilter&action=handler&data=getPaymentJson',
        country: '?plugin=delpayfilter&action=handler&data=getCountryJson',
        region: '?plugin=delpayfilter&action=handler&data=getRegionJson',
        storefrontDomains: '?plugin=delpayfilter&action=handler&data=getStorefrontDomainsJson',
        storefrontRoutes: '?plugin=delpayfilter&action=handler&data=getStorefrontRoutesJson',
        orderStatus: '?plugin=delpayfilter&action=handler&data=getOrderStatusJson',
        user_data: '?plugin=delpayfilter&action=handler&data=getUserDataJson',
        stocks: '?plugin=delpayfilter&action=handler&data=getStocksJson',
        tags: '?plugin=delpayfilter&action=handler&data=getTagsJson'
    },
    optsEqNe: {
        type: 'Select',
        width: '100px',
        name: 'op',
        placeholder: $__('select'),
        values: {
            eq: $__("equal"),
            neq: $__("not equal")
        }
    },
    optsText: {
        type: 'Select',
        width: '130px',
        name: 'op',
        values: {
            eq: $__("equal"),
            neq: $__("not equal"),
            cont: $__("contains"),
            notcont: $__("not contains"),
            begins: $__("begins with")
        }
    },
    optsNum: function (name) {
        return {
            type: 'Select',
            width: '60px',
            placeholder: $__('select'),
            name: name ? name : 'op',
            values: {
                gt: '>',
                gte: '>=',
                lt: '<',
                lte: '<=',
                eq_num: '=',
                neq_num: '<>'
            }
        };
    },
    optsAll: {
        type: 'Select',
        width: '130px',
        name: 'op',
        placeholder: $__('select'),
        values: {
            eq: $__("equal"),
            neq: $__("not equal"),
            cont: $__("contains"),
            notcont: $__("not contains"),
            begins: $__("begins with"),
            gt: '>',
            gte: '>=',
            lt: '<',
            lte: '<=',
            eq_num: '=',
            neq_num: '<>'
        }
    },
    optsSumSku: {
        type: 'Select',
        width: '150px',
        class: 's-sum-sku',
        name: 'sum_type',
        values: {
            not_sum: $__("each sku is separate"),
            sum: $__("summarize skus")
        }
    },
    optsStocks: {
        type: 'Select',
        width: '150px',
        class: 'stock-options',
        name: 'stock_type',
        values: {
            virtreal: $__("virtual and real"),
            virt: $__("virtual"),
            real: $__("real")
        }
    },
    resetField: '<a href="#/reset/selection/" class="js-action s-reset-button" style="display: none" title="' + $__('reset selection') + '"><i class="icon16 no"></i></a>',
    chosenParams: {disable_search_threshold: 12, no_results_text: $__("No result text"), search_contains: true},
    init: function (options) {
        this.optCats = {type: 'SelectFromUrl', source: this.urls['cat'], placeholder: $__('Select category'), id: 'cat', name: 'value'};
        this.currency = options.currency || '';
        this.types = {
            cat: [$__('Category'), this.optsEqNe, this.optCats],
            cat_all: [$__('Category and subcategories'), this.optsEqNe, this.optCats],
            set: [$__('Product set'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['set'], placeholder: $__('Select product set'), id: 'set', name: 'value'}],
            type: [$__('Product type'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['type'], placeholder: $__('Select product type'), id: 'type', name: 'value'}],
            product: [$__('Product'), this.optsEqNe, {type: 'PopupFromUrl', source: this.urls['product'], link: $__("select product"), id: 'product', name: 'value'}],
            feature: [
                $__('Product feature'),
                {type: 'SelectFromUrl', source: this.urls['feature'], placeholder: $__('Select product feature'), id: 'feature', name: 'field', class: 'feature-select', width: '350px'},
                this.optsAll,
                {type: 'SelectFromUrl', source: this.urls['featureValues'], placeholder: $__('Select feature value'), id: 'featureValue', name: 'value', class: 'feature-value', hidden: 1, width: '350px'},
                {type: 'Input', name: 'value', class: 'feature-value-input', hidden: 1, width: '150px'}
            ],
            services: [
                $__('Service'),
                this.optsEqNe,
                {type: 'SelectFromUrl', source: this.urls['services'], placeholder: $__('Select service'), id: 'services', name: 'field', class: 'feature-select s-services', width: '350px'},
                {type: 'SelectFromUrl', source: this.urls['servicesVariants'], placeholder: $__('Select service variant'), id: 'servicesVariants', name: 'value', class: 'feature-value', hidden: 1, width: '350px'}
            ],
            product_stock: [
                $__('Product stock count'),
                this.optsSumSku, 
                $__('on '),
                {type: 'SelectFromUrl', source: this.urls['stocks'], placeholder: $__('all stocks'), id: 'stocks', class: 'stocks-select', name: 'field'},
                this.optsStocks, 
                this.optsNum(), 
                {type: 'Input', name: 'value', width: '150px', placeholder: '∞'}
            ],
            product_stock_storefront: [
                $__('Product stock count'),
                $__('on '),
                {type: 'SelectFromUrl', source: this.urls['stocks'], placeholder: $__('all stocks'), id: 'stocks', class: 'stocks-select', name: 'field'},
                this.optsStocks,
                this.optsNum(),
                $__('product quantity in the order'),
            ],
            num: [$__('Total quantity of all products'), this.optsNum(), {type: 'Input', name: 'value', width: '150px'}],
            num_prod: [$__('Quantity of product'), {type: 'PopupFromUrl', source: this.urls['product'], link: $__("any product"), id: 'product', name: 'field'}, this.optsNum(), {type: 'Input', name: 'value', width: '90px'}],
            num_cat: [$__('Quantity of product'), {type: 'PopupFromUrl', source: this.urls['product'], link: $__("any product"), id: 'product', name: 'field'}, $__('from category'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['cat'], placeholder: $__('select category'), id: 'cat', name: 'ext'}, this.optsNum('op2'), {type: 'Input', name: 'value', width: '90px'}],
            num_cat_all: [$__('Quantity of product'), {type: 'PopupFromUrl', source: this.urls['product'], link: $__("any product"), id: 'product', name: 'field'}, $__('from category and subcategories of'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['cat'], placeholder: $__('select category'), id: 'cat', name: 'ext'}, this.optsNum('op2'), {type: 'Input', name: 'value', width: '90px'}],
            num_set: [$__('Quantity of product'), {type: 'PopupFromUrl', source: this.urls['product'], link: $__("any product"), id: 'product', name: 'field'}, $__('from set'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['set'], placeholder: $__('Select product set'), id: 'set', name: 'ext'}, this.optsNum('op2'), {type: 'Input', name: 'value', width: '90px'}],
            num_type: [$__('Quantity of product'), {type: 'PopupFromUrl', source: this.urls['product'], link: $__("any product"), id: 'product', name: 'field'}, $__('from type'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['type'], placeholder: $__('Select product type'), id: 'type', name: 'ext'}, this.optsNum('op2'), {type: 'Input', name: 'value', width: '90px'}],
            num_all_cat: [$__('Quantity of all products from category'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['cat'], placeholder: $__('select category'), id: 'cat', name: 'field'}, this.optsNum('op2'), {type: 'Input', name: 'value', width: '90px'}],
            num_all_cat_all: [$__('Quantity of all products from category and subcategories'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['cat'], placeholder: $__('select category'), id: 'cat', name: 'field'}, this.optsNum('op2'), {type: 'Input', name: 'value', width: '90px'}],
            num_all_set: [$__('Quantity of all products from set'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['set'], placeholder: $__('Select product set'), id: 'set', name: 'field'}, this.optsNum('op2'), {type: 'Input', name: 'value', width: '90px'}],
            num_all_type: [$__('Quantity of all products from type'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['type'], placeholder: $__('Select product type'), id: 'type', name: 'field'}, this.optsNum('op2'), {type: 'Input', name: 'value', width: '90px'}],
            num_all_tags: [$__('Quantity of all products with tags'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['tags'], placeholder: $__('Select tags'), id: 'cat', name: 'field'}, this.optsNum('op2'), {type: 'Input', name: 'value', width: '90px'}],
            num_feat: [
                $__('Quantity of products with features'),
                {type: 'SelectFromUrl', source: this.urls['feature'], placeholder: $__('Select product feature'), id: 'feature', name: 'field', class: 'feature-select', width: '350px'},
                this.optsAll,
                {type: 'SelectFromUrl', source: this.urls['featureValues'], placeholder: $__('Select feature value'), id: 'featureValue', name: 'ext', class: 'feature-value', hidden: 1, width: '350px'},
                {type: 'Input', name: 'ext', class: 'feature-value-input', hidden: 1, width: '90px'},
                this.optsNum('op2'),
                {type: 'Input', name: 'value', width: '90px'}
            ],
            num_items: [$__('Quantity of unique items'), this.optsNum(), {type: 'Input', name: 'value', width: '90px'}],
            total: [$__('Order price with discount'), this.optsNum(), {type: 'Input', name: 'value', width: '150px'}, this.currency],
            total_with_shipping: [$__('Order price with discount + Shipping price'), this.optsNum(), {type: 'Input', name: 'value', width: '150px'}, this.currency],
            total_without_discount: [$__('Order price without discount'), this.optsNum(), {type: 'Input', name: 'value', width: '150px'}, this.currency],
            total_without_discount_shipping: [$__('Order price without discount + Shipping price'), this.optsNum(), {type: 'Input', name: 'value', width: '150px'}, this.currency],
            discount: [$__('Total discount'), this.optsNum(), {type: 'Input', name: 'value', width: '150px'}, this.currency],
            sum: [$__('Total price of all products'), this.optsNum(), {type: 'Input', name: 'value', width: '150px'}, this.currency],
            sum_cat: [$__('Total price of products'), $__('from category'), {type: 'SelectFromUrl', source: this.urls['cat'], placeholder: $__('select category'), id: 'cat', name: 'field'}, this.optsNum(), {type: 'Input', name: 'value', width: '90px'}, this.currency],
            sum_cat_all: [$__('Total price of products'), $__('from category and subcategories of'), {type: 'SelectFromUrl', source: this.urls['cat'], placeholder: $__('select category'), id: 'cat', name: 'field'}, this.optsNum(), {type: 'Input', name: 'value', width: '90px'}, this.currency],
            sum_feat: [
                $__('Total price of all products with features'),
                {type: 'SelectFromUrl', source: this.urls['feature'], placeholder: $__('Select product feature'), id: 'feature', name: 'field', class: 'feature-select', width: '350px'},
                this.optsAll,
                {type: 'SelectFromUrl', source: this.urls['featureValues'], placeholder: $__('Select feature value'), id: 'featureValue', name: 'ext', class: 'feature-value', hidden: 1, width: '350px'},
                {type: 'Input', name: 'ext', class: 'feature-value-input', hidden: 1, width: '90px'},
                this.optsNum('op2'),
                {type: 'Input', name: 'value', width: '90px'},
                this.currency
            ],
            'total_feat': [
                $__('Total sum of features values'),
                {type: 'SelectFromUrl', source: this.urls['feature'], placeholder: $__('Select product feature'), id: 'feature', name: 'field', class: 'feature-select extrem-feature-select', width: '350px'},
                this.optsNum(),
                {type: 'Input', name: 'value', class: 'feature-value-input', hidden: 1, width: '150px'}
            ],
            product_tags: [
                $__('Product has tag'),
                this.optsEqNe,
                {type: 'SelectFromUrl', source: this.urls['tags'], placeholder: $__('Select tags'), id: 'tags', name: 'value', width: '450px'}
            ],
            shipping: [$__('Shipping'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['shipping'], placeholder: $__('Select shipping'), id: 'shipping', name: 'value'}],
            payment: [$__('Payment'), $__('equals'), {type: 'SelectFromUrl', source: this.urls['payment'], placeholder: $__('Select payment'), id: 'payment', name: 'value'}],
            prod_price: [$__('Price of any product'), this.optsNum(), {type: 'Input', name: 'value', width: '150px'}, this.currency],
            prod_each_price: [$__('Price of each product'), this.optsNum(), {type: 'Input', name: 'value', width: '150px'}, this.currency],
            ucat: [$__('User category'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['ucat'], placeholder: $__('Select user category'), id: 'ucat', name: 'value'}],
            user: [$__('Contact'), this.optsEqNe, {type: 'PopupFromUrl', source: this.urls['user'], link: $__("select contact"), id: 'user', name: 'value'}],
            user_date: [$__('Contact create datetime'), this.optsNum(), {type: 'Date', width: '120px', name: 'value', callback: 'dateCallback'}],
            user_data: [$__('User data'), {type: 'SelectFromUrl', source: this.urls['user_data'], placeholder: $__('Select data'), id: 'user_data', name: 'field'}, this.optsAll, {type: 'Input', name: 'value', width: '150px'}],
            user_country: [
                $__('Contact country and region'),
                this.optsEqNe,
                {type: 'SelectFromUrl', source: this.urls['country'], placeholder: $__('Select country'), id: 'country', name: 'field', class: 'dynamic-select', width: '350px'},
                {type: 'SelectFromUrl', source: this.urls['region'], id: 'region', placeholder: $__('Select region'), name: 'value', class: 'dynamic-value-template', hidden: 1, width: '350px'},
                this.resetField
            ],
            user_city: [$__('User city'), this.optsText, {type: 'Input', name: 'value', width: '450px'}],
            user_auth: [$__('Contact'), this.optsEqNe, $__('authorized')],
            all_orders: [$__('Total sum of all orders'), this.optsNum(), {type: 'Input', name: 'value', width: '150px'}, this.currency],
            order_int: [$__('Sum of orders for period from'), {type: 'Date', width: '120px', name: 'field', callback: 'dateCallback'}, $__("to"), {type: 'Date', width: '120px', name: 'ext', callback: 'dateCallback'}, this.optsNum(), {type: 'Input', name: 'value', width: '90px'}, this.currency],
            count_orders: [$__('Quantity of all orders'), this.optsNum(), {type: 'Input', name: 'value', width: '150px'}],
            order_count_int: [$__('Quantity of orders for period from'), {type: 'Date', width: '120px', name: 'field', callback: 'dateCallback'}, $__("to"), {type: 'Date', width: '120px', name: 'ext', callback: 'dateCallback'}, this.optsNum(), {type: 'Input', name: 'value', width: '90px'}],
            order_prod: [$__('Orders have product'), {type: 'PopupFromUrl', source: this.urls['product'], link: $__("select product"), id: 'product', name: 'value'}],
            order_prod_int: [$__('Orders have product'), {type: 'PopupFromUrl', source: this.urls['product'], link: $__("any product"), id: 'product', name: 'value'}, $__('for period from'), {type: 'Date', width: '120px', name: 'field', callback: 'dateCallback'}, $__("to"), {type: 'Date', width: '120px', name: 'ext', callback: 'dateCallback'}],
            order_prod_cat: [$__('Orders have product'), {type: 'PopupFromUrl', source: this.urls['product'], link: $__("any product"), id: 'product', name: 'field'}, $__("from category"), this.optCats],
            order_prod_cat_all: [$__('Orders have product'), {type: 'PopupFromUrl', source: this.urls['product'], link: $__("any product"), id: 'product', name: 'field'}, $__("from category and subcategories of"), this.optCats],
            order_prod_cat_int: [$__('Orders have product'), {type: 'PopupFromUrl', source: this.urls['product'], link: $__("any product"), id: 'product', name: 'field'}, $__("from category"), this.optCats, $__('for period from'), {type: 'Date', width: '120px', name: 'ext1', callback: 'dateCallback'}, $__("to"), {type: 'Date', width: '120px', name: 'ext2', callback: 'dateCallback'}],
            order_prod_cat_all_int: [$__('Orders have product'), {type: 'PopupFromUrl', source: this.urls['product'], link: $__("any product"), id: 'product', name: 'field'}, $__("from category and subcategories of"), this.optCats, $__('for period from'), {type: 'Date', width: '120px', name: 'ext1', callback: 'dateCallback'}, $__("to"), {type: 'Date', width: '120px', name: 'ext2', callback: 'dateCallback'}],
            order_status: [$__('Status of any order'), this.optsEqNe, {type: 'SelectFromUrl', source: this.urls['orderStatus'], placeholder: $__('Select status'), id: 'orderStatus', name: 'value'}],
            date: [$__('Date'), this.optsNum(), {type: 'Date', width: '120px', name: 'value', callback: 'dateCallback'}],
            week: [$__('Day of week'), {type: 'Select', width: '110px', name: 'value', values: {1: $__("Monday"), 2: $__("Tuesday"), 3: $__("Wednesday"), 4: $__("Thursday"), 5: $__("Friday"), 6: $__("Saturday"), 7: $__("Sunday")}}],
            time: [$__('Time'), this.optsNum(), {type: 'Time'}],
            cookie: ['$__COOKIE["', {type: 'Input', name: 'field', width: '150px'}, '"]', this.optsAll, {type: 'Input', name: 'value', width: '150px'}],
            session: ['$__SESSION["', {type: 'Input', name: 'field', width: '150px'}, '"]', '["', {type: 'Input', name: 'field2', width: '150px'}, '"]',this.optsAll, {type: 'Input', name: 'value', width: '150px'}],
            get: ['$__GET["', {type: 'Input', name: 'field', width: '150px'}, '"]', this.optsAll, {type: 'Input', name: 'value', width: '150px'}],
            post: ['$__POST["', {type: 'Input', name: 'field', width: '150px'}, '"]', this.optsAll, {type: 'Input', name: 'value', width: '150px'}],
            not_isset_post: [($__('Not isset') + ' $__POST["'), {type: 'Input', name: 'field', width: '150px'}, '"]'],
            not_isset_get: [($__('Not isset') + ' $__GET["'), {type: 'Input', name: 'field', width: '150px'}, '"]'],
            storefront: [
                $__('Storefront'),
                this.optsEqNe,
                {type: 'SelectFromUrl', placeholder: $__('Select domain'), width: '350px', source: this.urls['storefrontDomains'], id: 'storefrontDoman', class: 'storefront-domain', name: 'field'},
                {type: 'SelectFromUrl', placeholder: $__('Select route'), hidden: 1, class: 'storefront-route', source: this.urls['storefrontRoutes'], id: 'storefrontRoutes', name: 'value', width: '350px'},
                this.resetField
            ],
            shipping_price: [$__('Shipping price'), this.optsNum(), {type: 'Input', name: 'value', width: '150px'}, this.currency],
            coupon: [$__('Coupon'), this.optsEqNe, {type: 'Input', name: 'value', width: '150px'}],
            isset_coupon: [this.optsEqNe, $__('Isset active coupon')]
        };

        this.initCallbacks();

        if (options.conditions !== '' || options.target !== '') {
            $.post("?plugin=delpayfilter&module=filter&action=decodeJSON", {conditions: options.conditions !== '' ? JSON.stringify(options.conditions) : '', target: options.target !== '' ? JSON.stringify(options.target) : ''}, function (response) {
                if (response.status == 'ok' && response.data) {
                    if (response.data.conditions !== undefined) {
                        $("#delpayfilter-save-form .s-conditions .condition-block").replaceWith(response.data.conditions);
                        $("#delpayfilter-save-form .s-conditions > .value > .condition-block select").chosen($.delpayfilter_conditions.chosenParams).each(function () {
                            var that = $(this);
                            if (that.hasClass("extrem-feature-select")) {
                                that.find(".selectable:not(.dimension)").hide();
                                that.trigger("chosen:updated");
                            }
                            that.addClass("inited");
                        });
                    }
                    if (response.data.target !== undefined && $.trim(response.data.target) !== '') {
                        $("#delpayfilter-save-form .targets").html(response.data.target);
                        $("#delpayfilter-save-form .targets select").not(".hidden").each(function () {
                            var select = $(this);
                            if (select.hasClass("target-chosen")) {
                                select.addClass("inited");
                                $.delpayfilter.initTargetChosen(select);
                            } else {
                                select.chosen($.delpayfilter_conditions.chosenParams).addClass("inited");
                            }
                            if (select.hasClass("hide-after-init")) {
                                select.next(".chosen-container").hide();
                            }
                        });
                    }
                    $.delpayfilter_conditions.afterLoadInit();
                    $("#fixed-save-panel input[type='submit']").removeAttr("disabled");
                } else {
                    $("#fixed-save-panel input[type='submit']").removeAttr("disabled");
                }
                $("#delpayfilter-save-form .is-loading").remove();
            });
        } else {
            $("#delpayfilter-save-form .is-loading").remove();
            $("#fixed-save-panel input[type='submit']").removeAttr("disabled");
        }
    },
    afterLoadInit: function () {
        // Скрываем ненужные характеристики, опции
        $(".feature-select, .dynamic-select, .stocks-select").each(function () {
            $(this).change();
        });
        
        // Инициализируем поля с датами
        $(".init-datepicker").each(function () {
            $.delpayfilter_conditions.initDatepicker($(this));
        });
    },
    // Автозаполнение 
    initAutocomplete: function (elem, type) {
        elem.autocomplete({
            source: this.urls['autocomplete_' + type],
            minLength: 3,
            delay: 300,
            autoFocus: true,
            select: function (event, ui) {
                if (ui.item && $(".condition a.has-dialog").length) {
                    $.delpayfilter_conditions.dialogSelectProduct(elem, ui.item.label, ui.item.id);
                }
                elem.val('');
                return false;
            }
        });
    },
    isTypeExists: function (type) {
        return typeof this.types[type] !== 'undefined';
    },
    // Выбор товара во всплывающем окне
    dialogSelectProduct: function (elem, name, value) {
        $(".condition a.has-dialog").html(name).closest("div").find("input").val(value);
        elem.closest(".dialog").trigger("close");
    },
    // Добавление поля
    addField: function (block, type, source) {
        source = source || 'condition';
        if (typeof this.types[type] !== 'undefined') {
            var callbacks = [];
            var field = $("<div class='condition inprocess'></div>");
            if (type == 'shipping' && source == 'target') {
                this.types[type][1] = $__('equals');
            }
            for (var i in this.types[type]) {
                var param = this.types[type][i];
                if (typeof param === 'string') {
                    field.append("<span class='condition-text'>" + param + "</span>");
                } else {
                    var methodName = "get" + param.type + "Code";
                    if (this[methodName] === undefined) {
                        console.log("Method %s not exists", methodName);
                        continue;
                    }
                    field.append(this[methodName](param));
                    typeof param.callback !== 'undefined' && callbacks.push(param.callback);
                }
            }
            field.append(this.getInputCode({name: 'type', input_type: 'hidden', value: type}));
            field.append(this.getRemoveButtonCode());
            block.append(field);

            block.find(".inprocess select").chosen(this.chosenParams).each(function () {
                // Подгружаем значения select
                var select = $(this);
                if (select.data('source')) {
                    select.before("<i class='icon16 loading'></i>");
                    $.post(select.data('source'), function (response) {
                        select.append(response.data);
                        select.prev(".loading").remove();
                        select.trigger("chosen:updated").trigger("options_loaded");

                        // Скрываем поле при необходимости
                        var selectParam = select.data('param');
                        if (typeof selectParam !== 'undefined' && selectParam.hidden !== undefined) {
                            select.next(".chosen-container").hide();
                        }
                    }, "json");
                } else {
                    var selectParam = select.data('param');
                    if (typeof selectParam !== 'undefined' && selectParam.hidden !== undefined) {
                        select.next(".chosen-container").hide();
                    }
                }
                select.trigger("chosen:updated");
            });
            block.find("select.extrem-feature-select").each(function () {
                var select = $(this);
                select.find(".selectable:not(.dimension)").hide();
                select.trigger("chosen:updated");
            });

            if (callbacks) {
                for (var i in callbacks) {
                    try {
                        if (typeof this[callbacks[i]] === 'function') {
                            this[callbacks[i]].call(this);
                        }
                    } catch (e) {
                        console.log('Callback error: ' + e.message, e);
                    }
                }
            }

            block.find(".inprocess").removeClass("inprocess");
            this.updateConditionOperatorBlock(block.closest(".condition-block"));
        }
    },
    // Обновление оператора для условий
    updateConditionOperatorBlock: function (block) {
        if ($("> .conditions > .condition", block).length > 1) {
            if (block.children(".cond-op").length) {
                block.find(".cond-op").show();
            } else {
                block.prepend("<div class='cond-op margin-block'></div>");
                block.children(".cond-op").append(this.getConditionOperatorCode());
                $("> .cond-op select", block).chosen(this.chosenParams);
            }
            block.addClass("show-cond-op");
        } else {
            block.children(".cond-op").hide();
            block.removeClass("show-cond-op");
        }
    },
    // Добавление группы условий
    addGroup: function (block) {
        var group = $("<div class='condition'><div class='condition-block'><div class='conditions'></div>" + this.getRemoveBlockButtonCode() + "</div></div>");
        group.find(".condition-block").append(this.getAddButtonCode());
        block.parents(".condition-block").length % 2 === 1 && group.find(".condition-block").addClass("even");
        block.append(group);
        this.updateConditionOperatorBlock(block.closest(".condition-block"));
    },
    getConditionOperatorCode: function () {
        return this.getSelectCode({name: 'cond-op', values: {and: $__("All conditions return true"), or: $__("Any of condition returns true")}, width: '350px'});
    },
    getAddButtonCode: function () {
        return '<a href="#/show/condition/" class="js-action" title="' + $__("Add condition") + '"><i class="icon16 add"></i> ' + $__("Add condition") + '</a>';
    },
    getRemoveButtonCode: function () {
        return '<span class="condition-text s-delete"><a href="#/delete/condition/" class="js-action block half-padded" title="' + $__("Delete") + '"><i class="icon16 delete"></i></a></span>';
    },
    getRemoveBlockButtonCode: function () {
        return '<a href="#/delete/conditionBlock/" style="position: absolute; top: -5px; right: -5px;" class="js-action" title="' + $__("delete") + '"><i class="icon16 delete"></i></a>';
    },
    getSelectCode: function (param) {
        var select = $("<select" + (param.class !== undefined ? " class='" + param.class + "'" : "") + " name='" + param.name + "'></select>");
        if (param.width !== undefined) {
            select.width(param.width);
        }
        if (param.placeholder !== undefined) {
            select.attr("data-placeholder", param.placeholder).prepend("<option value=''></option>");
        }
        $.each(param.values, function (i, v) {
            select.append("<option class='op-" + i + "' value='" + i + "'>" + v + "</option>");
        });
        return select;
    },
    getInputCode: function (param) {
        var params = {
            name: param.name,
            class: (param.hidden !== undefined ? 'hidden ' : '') + (param.class !== undefined ? param.class : ''),
            type: param.input_type ? param.input_type : 'text',
            value: param.value !== undefined ? param.value : '',
            placeholder: param.placeholder !== undefined ? param.placeholder : ''
        };
        if (param.width !== undefined) {
            params['width'] = param.width;
            params['style'] = "min-width: " + param.width + ";width:" + param.width;
        }
        if (param.maxlength !== undefined) {
            params['maxlength'] = param.maxlength;
        }
        var input = $("<input />", params);
        return input;
    },
    getSelectFromUrlCode: function (param) {
        if ($("#" + param.id + "List").length) {
            var select = $("#" + param.id + "List").clone();
            select.removeData("source").removeAttr("id").data('param', param).attr('name', param.name).show().find("option").removeAttr('style');
            if (param.class !== undefined) {
                select.toggleClass().addClass(param.class);
            }
        } else {
            var select = $("<select" + (param.class !== undefined ? " class='" + param.class + "'" : "") + " name='" + param.name + "'></select>");
            select.css('width', param.width !== undefined ? param.width : '400px');
            if (param.placeholder !== undefined) {
                select.attr("data-placeholder", param.placeholder).prepend("<option value=''></option>");
            }
            select.attr("id", param.id + "List").data('source', param.source).data('param', param);
        }
        return select;
    },
    getPopupFromUrlCode: function (param) {
        var div = $("<div />", {class: 'condition-text'});
        $("<a href='#/open/conditionDialog/' class='js-action' data-id='" + param.id + "' data-source='" + param.source + "' title='" + param.link + "'>" + param.link + "</a>").appendTo(div);
        div.append(this.getInputCode({name: param.name, input_type: 'hidden'}));
        return div;
    },
    getDateCode: function (param) {
        param['class'] = 'f-datepicker not-inited';
        var input = this.getInputCode(param);
        return input;
    },
    getTimeCode: function (param) {
        var html = "<div class='inline-block align-center condition-text'>" + this.getInputCode({type: 'text', name: 'hour', width: '35px', maxlength: 2}).prop('outerHTML') + "<br>" + $__("HH") + "</div>";
        html += "<div class='inline-block align-center condition-text'>" + this.getInputCode({type: 'text', name: 'minute', width: '35px', maxlength: 2}).prop('outerHTML') + "<br>" + $__("MM") + "</div>";
        return html;
    },
    dateCallback: function () {
        $.each($(".f-datepicker.not-inited"), function () {
            $.delpayfilter_conditions.initDatepicker($(this));
            $(this).removeClass("not-inited");
        });
    },
    // Инициализация всплывающего календаря
    initDatepicker: function (field, outside) {
        var params = {};
        if ($.delpayfilter.locale == 'ru_RU') {
            params["months"] = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
            params["days_abbr"] = ["Вск", "Пон", "Вт", "Ср", "Чтв", "Пят", "Суб"];
            params["show_select_today"] = "Сегодня";
            params["lang_clear_date"] = "Очистить";
        }
        params["inside"] = typeof outside !== 'undefined' ? false : true;
        params["show_clear_date"] = true;
        field.Zebra_DatePicker(params);
    },
    initCallbacks: function () {
        // Выбор характеристик товара
        $(document).off("change", ".feature-select").on("change", ".feature-select", function () {
            var that = $(this);
            var selected = that.find(":selected");
            var block = that.closest(".condition");
            var featureValuesSelect = block.find(".feature-value");
            var featureValues = featureValuesSelect.next(".chosen-container");
            var operatorSelect = block.find("select[name='op']");
            var input = block.find(".feature-value-input");
            // Если поле имеет выпадающие значения характеристик
            if (selected.hasClass("selectable")) {
                if (featureValues.length) {
                    var chosen = featureValuesSelect.data('chosen');
                    // Сбрасываем результат
                    if (typeof chosen !== 'undefined' && !featureValuesSelect.hasClass("inited")) {
                        chosen.results_reset();
                    }

                    var chosenOp = operatorSelect.data('chosen');
                    operatorSelect.find("option[value='cont'], option[value='notcont'], option[value='begins'], option[value='eq_num'], option[value='neq_num']").hide();
                    operatorSelect.trigger("chosen:updated");
                    // Сбрасываем результат
                    if (typeof chosenOp !== 'undefined' && !featureValuesSelect.hasClass("inited") && !block.find(".s-services").length) {
                        chosenOp.results_reset();
                    }

                    featureValuesSelect.find("option").hide().siblings(".feature-" + that.val()).show();
                    featureValuesSelect.trigger("chosen:updated").removeClass("inited");
                    featureValues.show();
                    featureValuesSelect.removeClass("hidden");
                } else {
                    featureValues.hide();
                    featureValuesSelect.addClass("hidden");
                    operatorSelect.find("option").show();
                    operatorSelect.trigger("chosen:updated");
                }
                // Данное решение подходит для "Суммарного значения хар-к", чтобы отображать поле для ввода
                if (!that.hasClass('extrem-feature-select')) {
                    input.addClass("hidden");
                } else {
                    input.removeClass("hidden");
                }
            } else {
                featureValues.hide();
                featureValuesSelect.addClass("hidden");
                operatorSelect.find("option").show();
                operatorSelect.trigger("chosen:updated");
                if (!that.hasClass("inited")) {
                    input.val('');
                }
                that.removeClass("inited");
                input.removeClass("hidden");
            }
            if (selected.data("base-unit") !== undefined) {
                input.next(".f-temp").remove();
                input.addClass("has-base-unit").after("<span class='condition-text f-temp'>" + selected.data("base-unit") + "</span>");
            } else {
                input.removeClass("has-base-unit").next(".f-temp").remove();
            }
        });

        // Выбор витрин
        $(document).off("change", ".storefront-domain").on("change", ".storefront-domain", function () {
            var that = $(this);
            var block = that.closest(".condition");
            var routeSelect = block.find(".storefront-route");
            var routeValues = routeSelect.next(".chosen-container");
            var resetButton = block.find(".s-reset-button");
            // Если поле имеет выпадающие значения характеристик
            if (routeValues.length) {
                var chosen = routeSelect.data('chosen');
                // Сбрасываем результат
                if (typeof chosen !== 'undefined' && !routeSelect.hasClass("inited")) {
                    chosen.results_reset();
                }
                routeSelect.find("option").hide().siblings(".domain-" + that.val()).show();
                routeSelect.trigger("chosen:updated");
                routeValues.show();
                resetButton.show().parent().show();
            } else {
                routeValues.hide();
                resetButton.hide().parent().hide();
            }
        });

        /* Выбор стран и регионов */
        $(document).off("change", ".dynamic-select").on("change", ".dynamic-select", function () {
            var that = $(this);
            var selected = that.find(":selected");
            var block = that.closest(".condition");
            var resetButton = block.find(".s-reset-button");

            block.find(".dynamic-value, .dynamic-value-template").removeClass('is-active').addClass('hidden').next(".chosen-container").hide();

            var operatorSelect = block.find("select[name='op']");
            var dynamicValuesSelect = block.find(".dynamic-value-" + selected.val());
            /* Если значений у данного фильтра нет, но на странице их уже добавили, тогда копируем их */
            if (block.find(".dynamic-value-" + selected.val()).length) {
                block.find(".dynamic-value-" + selected.val()).removeClass("dynamic-value-template hidden").next().show();
                var dynamicValuesSelect = block.find(".dynamic-value-" + selected.val());
            }
            resetButton.show().parent().show();

            operatorSelect.find("option[value='cont'], option[value='notcont'], option[value='begins'], option[value='eq_num'], option[value='neq_num']").hide();
            operatorSelect.trigger("chosen:updated");
            that.removeClass("inited");
            if (dynamicValuesSelect.length) {
                var dynamicValues = dynamicValuesSelect.next(".chosen-container");
                dynamicValuesSelect.removeClass("inited hidden").addClass('is-active');
                dynamicValues.show();
            } else {
                var dynamicValuesClone = block.find(".dynamic-value-template").clone();
                block.find(".dynamic-value-template").before(dynamicValuesClone);
                /* Подгружаем значения select */
                dynamicValuesClone.before("<i class='icon16 loading'></i>");
                dynamicValuesClone.removeClass("dynamic-value-template hidden").addClass("dynamic-value dynamic-value-" + selected.val()).show().chosen($.delpayfilter_conditions.chosenParams);

                var source = block.find(".dynamic-value-template").data('param') !== undefined ? block.find(".dynamic-value-template").data('param').source : $.delpayfilter_conditions.urls[that.attr('data-value-url')];
                if (source) {
                    $.post(source, {dynamic_id: selected.val()}, function (response) {
                        dynamicValuesClone.html(response.data);
                        dynamicValuesClone.prev(".loading").remove();
                        dynamicValuesClone.trigger("chosen:updated");

                        /* Скрываем поле при необходимости */
                        var selectParam = dynamicValuesClone.data('param');
                        if (typeof selectParam !== 'undefined' && selectParam.hidden !== undefined) {
                            dynamicValuesClone.next(".chosen-container").hide();
                        } else {
                            dynamicValuesClone.addClass("is-active").removeClass("hidden");
                        }
                    }, "json");
                } else {
                    dynamicValuesClone.prev(".loading").remove();
                }
            }
        });
        
        /* Скрываем ненужные опции у складов */
        $(document).off("change", ".stocks-select").on("change", ".stocks-select", function () {
            var selected = $(this).find(":selected");
            if (selected.hasClass("show-stock-options")) {
                selected.closest(".condition").find('.stock-options').next().show();
            } else {
                selected.closest(".condition").find('.stock-options').next().hide();
            }
        });

        // Изменение типа условий (OR, AND)
        $(document).off('change', '.cond-op select').on('change', '.cond-op select', function () {
            var that = $(this);
            if (that.val() == 'or') {
                that.closest(".condition-block").addClass('s-or');
            } else {
                that.closest(".condition-block").removeClass('s-or');
            }
        });

        $(document).off('options_loaded', '.extrem-feature-select').on('options_loaded', '.extrem-feature-select', function () {
            var that = $(this);
            that.find(".selectable:not(.dimension)").hide();
            that.trigger("chosen:updated");
        });
    },
    // Собираем все условия в JSON объект
    getJsonConditions: function () {
        var group = $(".s-conditions .condition-block").first();
        var result = this.getConditionGroupData(group);
        var jsonResult = result ? JSON.stringify(result) : '';
        return jsonResult;
    },
    getConditionGroupData: function (group) {
        var groupOpObj = group.find("> .cond-op select");
        var data = {group_op: groupOpObj.length ? groupOpObj.val() : 'and', conditions: []};
        var conditions = group.children(".conditions").children(".condition");
        // Если нет условий
        if (!conditions.length) {
            return null;
        }
        conditions.each(function () {
            var condition = $(this);
            var obj;
            if (condition.children(".condition-block").length) {
                obj = $.delpayfilter_conditions.getConditionGroupData(condition.children(".condition-block"));
            } else {
                obj = $.delpayfilter_conditions.getConditionData(condition);
            }
            if (obj) {
                data.conditions.push(obj);
            }
        });
        return data;
    },
    getConditionData: function (condition) {
        var values = {};
        condition.find("input, select").not(".hidden").each(function () {
            var elem = $(this);
            if (elem.attr("name") === undefined) {
                return true;
            }
            values[elem.attr("name")] = elem.val();
        });
        return values;
    },
    // Собираем все цели в JSON объект
    getJsonTarget: function () {
        var result = [];
        $(".target-row").each(function () {
            var target = $(this);
            var value = target.find(".target-chosen").val();
            if ($.trim(value) !== '') {
                var data = {target: value};
                var targetBlock = target.find(".target-block");
                if (targetBlock.length && !targetBlock.hasClass("hidden")) {
                    data['condition'] = $.delpayfilter_conditions.getConditionData(targetBlock);
                }
                result.push(data);
            }
        });
        var jsonResult = result.length ? JSON.stringify(result) : '';
        return jsonResult;
    }
};
