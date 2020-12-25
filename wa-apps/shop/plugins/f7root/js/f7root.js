/**
 * Created by onehalf on 06.04.16.
 */
(function () {
    "use strict";
    var formfeature = $('.f7rootfeature');
    var formfind = $('.f7find');
    var selectorfeature = formfeature.find('.nfid');
    var selectorfeaturevaluespan = formfeature.find('.nfvid');
    var searchtext = $('.f7root-searchtext');
    var inputsearchtext = searchtext.find('.f7root-searchtext-input');

    var selectoritems = formfind.find('.items');
    var root_href = decodeURI(window.location.href);
    // заполнение  списков  если показывается список товаров по характеристике
    var index = root_href.indexOf('f7root-feature');
    if (index != -1) {
        var ss = root_href.substring(index);
        var index2 = ss.indexOf('&');
        var hash = ss.substring(14, index2);
        formfeature.attr("id", 'f7root-feature' + hash + '-');
        var feature = hash.split('-');
        selectorfeature.load('?plugin=f7root&action=features&feature=' + feature[0]);
        selectorfeaturevaluespan.load('?plugin=f7root&action=featurevalues&feature=' + feature[0] + '&featurevalue=' + feature[1]);
    } else {
        selectorfeature.load('?plugin=f7root&action=features');
    }
    // заполнение списка при обновлении страницы
    var index3 = root_href.indexOf('f7root-find');
    if (index3 != -1) {
        var ss1 = root_href.substring(index3);
        var index4 = ss1.indexOf('&');
        var hash2 = ss1.substring(12, index4);
        var selitem = formfind.find(".items option[value=" + hash2 + "]");
        selitem.prop('selected', true);
        formfind.attr("id", 'f7root-find-' + hash2 + '-');
    }
    //заполнение поля ввода если ищется текст
    var index5 = root_href.indexOf('f7root-searchtext');
    if (index5 != -1) {
        var ss5 = root_href.substring(index5);
        var index6 = ss5.indexOf('&');
        var hash5 = ss5.substring(17, index6);
        var hasharray = hash5.split('-');
        inputsearchtext.val(hasharray[0]);


        searchtext.attr("id", 'f7root-searchtext' + hasharray[0] + '-' + hasharray[1] + '-');
    }

    // выбор характеристики
    selectorfeature.change(function () {
        var new_feature = selectorfeature.val();
        selectorfeaturevaluespan.load('?plugin=f7root&action=featurevalues&feature=' + new_feature);
        formfeature.attr("id", 'f7root-feature' + new_feature);
    });
    //выбор значения
    selectorfeaturevaluespan.change(function () {
        var new_feature = selectorfeature.val();
        var selectorfeaturevalue = formfeature.find('.sfvid');
        var new_featurevalue = selectorfeaturevalue.val();
        //смотри не находится ли пользователь на категории. если да то находи ее id
        var root_href_cat = window.location.href;
        var index_category = root_href_cat.indexOf('category_id');
        var index_feature = root_href.indexOf('f7root-feature');
        var category_id = 0;
        if (index_category != -1) {
            var ss2 = root_href_cat.substring(index_category);
            var index_end = ss2.indexOf('&');
            if (index_end != -1) {
                category_id = ss2.substring(12, index_end);
            } else {
                category_id = ss2.substring(12);
            }
        } else {

            if (index_feature != -1) {
                var ss3 = root_href_cat.substring(index_feature);
                var index_end2 = ss3.indexOf('&');
                var indexes = '';
                if (index_end2 != -1) {
                    indexes = ss3.substring(14, index_end2);
                } else {
                    indexes = ss3.substring(14);
                }
                var index_id = indexes.split('-');
                if (index_id.length > 2) {
                    category_id = index_id[2];
                }
            }
        }

        if (new_featurevalue != 'null') {
            formfeature.attr("id", 'f7root-feature' + new_feature + '-' + new_featurevalue + '-' + category_id + '-');
            $.products.dispatch('#/products/hash=f7root-feature' + new_feature + '-' + new_featurevalue + '-' + category_id);
        } else {
            return false;
        }
    });
    // выбор пустого поля
    selectoritems.change(function () {
        var selectoritems = formfind.find('.items');
        var new_itemsvalue = selectoritems.val();
        if (new_itemsvalue != null) {
            formfind.attr("id", 'f7root-find-' + new_itemsvalue + '-');
            $.products.dispatch('#/products/hash=f7root-find-' + new_itemsvalue);
        } else {
            return false;
        }
    });
    inputsearchtext.keyup(function () {
        var new_text = inputsearchtext.val();
        //смотри не находится ли пользователь на категории. если да то находи ее id
        var root_href_cat = window.location.href;
        var index_category = root_href_cat.indexOf('category_id');
        var index_searchtext = root_href.indexOf('f7root-searchtext');
        console.log("url "+root_href_cat);
        var category_id = 0;
        if (index_category != -1) {
            console.log("index_category"+index_category);
            var ss2 = root_href_cat.substring(index_category);
            var index_end = ss2.indexOf('&');
            if (index_end != -1) {
                category_id = ss2.substring(12, index_end);
            } else {
                category_id = ss2.substring(12);
            }
        } else {
            console.log("index_searchtext"+index_searchtext);
            if (index_searchtext != -1) {
                var ss3 = root_href_cat.substring(index_searchtext);
                var index_end2 = ss3.indexOf('&');
                var indexes = '';
                console.log("index_end2"+index_end2);
                if (index_end2 != -1) {
                    indexes = ss3.substring(17, index_end2);
                } else {
                    indexes = ss3.substring(17);
                }
                var index_id = indexes.split('-');
                if (index_id.length > 1) {
                    category_id = index_id[1];
                }
            }
        }
        searchtext.attr("id", 'f7root-searchtext' + new_text + '-' + category_id + '-');
        $.products.dispatch('#/products/hash=f7root-searchtext' + new_text + '-' + category_id);
    });
})();
