$(document).ready(function() {

    $('#s-product-frontend-url-edit-link').after('<div class="copychpu__generator"><a href="#" id="copychpu__genchpu" class="copychpu__link">Сгенерировать ЧПУ</a></div>');
    $('#copychpu__genchpu').on('click', function(){
        var name_item = $('.s-product-name-input').val();
        if($('#copychpu__settings').attr('data-watranslit') == 1){
            ru2en.watranslit(name_item);
        }else{
            translit = ru2en.translit(name_item);
            $('#s-product-frontend-url-input').val(translit);
            $('#s-product-frontend-url').html(translit);
        }

        return false;
    });


    var ru2en = {
        ru_str : 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя(),.; "+/*?!@%&#$^«»',
        en_str : ['a','b','v','g','d','e','jo','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f',
            'h','c','ch','sh','shh','','i','','je','ju','ja',
            'a','b','v','g','d','e','jo','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f',
            'h','c','ch','sh','shh','','i','','je','ju','ja',
            '', '','', '', '','-','','','','','','','','','','','','','',''],
        translit : function(org_str) {
            var tmp_str = "";
            for(var i = 0, l = org_str.length; i < l; i++) {
                var s = org_str.charAt(i), n = this.ru_str.indexOf(s);
                if(n >= 0) { tmp_str += this.en_str[n]; }
                else { tmp_str += s; }
            }
            return tmp_str.toLowerCase();
        },
        watranslit : function(org_str){
            $.ajax({
                'url': '?action=transliterate',
                'dataType': 'html',
                'data': 'str='+org_str
            }).done(function (response) {
                if ((response = $.parseJSON(response)) && (response.status == "ok")) {
                    var tra = response.data.toLowerCase()
                    $('#s-product-frontend-url-input').val(tra);
                    $('#s-product-frontend-url').html(tra);
                    return true;
                }
            });
        }
    };


});