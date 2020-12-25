(function($) {
    // cancel link
    $(document).on( "click",".wtsp-content a.cancel", function() {
        $('.wtsp-content').toggle();
        return false;
    });
    $(document).on( "click","a.wtsp-link", function() {
        $('.wtsp-content').toggle();
        return false;
    });
    $(document).on( "click","a.wtsp-viber-link", function() {
        $('.wtsp-viber-content').toggle();
        return false;
    });
    $(document).on( "click","a.wtsp-telegram-link", function() {
        $('.wtsp-telegram-content').toggle();
        return false;
    });
    $(document).on( "click","a.wtsp-skype-link", function() {
        $('.wtsp-skype-content').toggle();
        return false;
    });
    /*whatsapp start*/
    $(document).on( "submit","#wtsp-whatsapp-form", function() {
        var form = $(this);
        var phone = $("input[name=phone]",this).attr('value');
        phone = phone.replace(/[^0-9]/g, '');

        var content = $(".wtsp-content textarea[name=text]").val();
        var sender = $(".wtsp-content select[name=sender]").val();

        if(sender == "web"){
            url = "https://web.whatsapp.com/send?phone="+phone+"&text=" + encodeURI(content);
        } else {
            url = "whatsapp://send?phone="+phone+"&text=" + encodeURI(content);
        }

        window.open(url,'_blank');
        return false;
    });
    /*whatsapp start*/
    /*viber start*/
    $(document).on( "submit","#wtsp-viber-form", function() {
        var form = $(this);
        var phone = $("input[name=phone]",this).attr('value');
        phone = phone.replace(/[^0-9]/g, '');

        var content = $("textarea[name=text]",this).val();
        var sender = $("select[name=sender]",this).val();
        var senactions = $("select[name=senactions]",this).val();

        if(sender == "web"){
            url = "viber://chat?number=+"+phone;
        } else {
            url = "viber://add?number="+phone;
        }

        if(senactions == "mob"){
            url = "viber://forward?text=" + encodeURI(content);
        }


        window.open(url,'_blank');
        return false;
    });
    /*viber start*/
    /*telegram start*/
    $(document).on( "submit","#wtsp-telegram-form", function() {
        var form = $(this);
        var phone = $("input[name=phone]",this).attr('value');
        //phone = phone.replace(/[^0-9]/g, '');

        var content = $("textarea[name=text]",this).val();

        var senactions = $("select[name=senactions]",this).val();


        url = "tg://resolve?domain=" + phone ;
        if(senactions == "mob"){
            url = "tg://msg?text=" + encodeURI(content) ;
        }


        window.open(url);
        return false;
    });
    /*telegram start*/
    /*telegram start*/
    $(document).on( "submit","#wtsp-skype-form", function() {
        var form = $(this);
        var phone = $("input[name=phone]",this).attr('value');
        //phone = phone.replace(/[^0-9]/g, '');

        var content = $("textarea[name=text]",this).val();

        var senactions = $("select[name=senactions]",this).val();


        url = "skype:" + phone + "?add";
        if(senactions == "mob"){
    
            url = "https://web.skype.com/share?url="+ encodeURI(content);
        }


        window.open(url);
        return false;
    });
    /*telegram start*/
    /*template*/
    $(document).on( "click","a#wtsp-message-template", function() {
        var order_id = $(this).data("order_id");
        $('<div id="wtsp-dialog-plugin"></div>').waDialog({
        buttons: '<input class="button blue" type="submit" value="Применить шаблон"> или <a href="javascript:void(0)" class="inline-link cancel"><b><i>отмена</i></b><a class="wtdropright" href="?action=plugins#/wtsp">Редактировать шаблоны</a>',
        'onSubmit': function (d) {
            $.post('?plugin=wtsp&module=mytemplateResult', $(this).serialize(), function (result) {
                //d.trigger('close');
                if(result.status == "ok"){
                    $("#wtsp-content textarea[name=text]").html(result.data);
                    d.trigger('close'); // закрыть диалог
                } else {
                    alert(result.errors.data);
                }
            }, 'json');
            return false;
        },
        onLoad: function (d) {
             $("#wtsp-dialog-plugin .dialog-content-indent").load('?plugin=wtsp&module=mytemplate&order_id='+order_id);
             return false;
        },
        });
    });
    $(document).on( "click","a#wtsp-message-save", function() {
        var form = $(this).parents( "form" );

        $.post('?module=workflow&action=perform', form.serialize(), function (result) {
            if ("order" in $) { $.order.reload(); }
        }, 'json');
    });
})(jQuery);
