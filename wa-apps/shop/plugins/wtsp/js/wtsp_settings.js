$(document).on( "click","a.wtsp-action-add", function() {
    var id = $(this).data("id");
    $('<div id="wtsp-dialog-plugin"></div>').waDialog({
    'height': '600px',
    'width': '80%',
    'onSubmit': function (d) {
        d.trigger('close');
        return false;
    },
    onLoad: function (d) {
         $("#wtsp-dialog-plugin .dialog-content-indent").load('?plugin=wtsp&module=settingsEdit&id='+id);
         return false;
    },
    'buttons': '<div><input type="submit" id="n-send-button" value="Сохранить" class="button green"> или <a href="javascript:void(0)" class="inline-link cancel"><b><i>'+$_("Close")+'</i></b></a>',
    onSubmit: function (d) {
        $this = $(this);
        $(".errors",$this).html("");
        $.post('?plugin=wtsp&module=settingsSave', $(this).serialize(), function (result) {
            if(result.status == "ok"){
                $.plugins.dispatch("wtsp", true);
                d.trigger('close'); // закрыть диалог
            } else {
                $(".errors",$this).html(result.errors.data);
            }

        }, 'json');
        return false;
    }
    });
});
$(document).on( "click","a.wtsp-templates-delete", function() {
    var isDelete = confirm("Удалить данный шаблон?");
    var id = $(this).data("id");
    if(isDelete){
        $.post('?plugin=wtsp&module=settingsDelete&id='+id, function (result) {
            $("#wtsp-dialog-plugin").trigger('close');
            $.plugins.dispatch("wtsp", true);
        }, 'json');
    }
});
var $f = $('.wtsp');

$f.find('.menu-v>li>a').click(function (e) {
    e.preventDefault();
    var $li = $(this).closest('li'),
        tab = $li.data('tab');

    $li.addClass('selected').siblings().removeClass('selected');

    $f.find('[data-content]').hide().filter('[data-content="'+tab+'"]').show();
});

$f.submit(function(){
    $('#plugins-settings-form-status').html('<i class="icon16 loading"></i>');
});
