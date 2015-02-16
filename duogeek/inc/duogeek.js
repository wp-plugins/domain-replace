;jQuery(function($) {

    $('.duo-kb h3.hndle').click(function() {
        $(this).next('.inside').slideToggle();
    });

    $('.duo_prod_panel input').iCheck({
        checkboxClass: 'icheckbox_square',
        radioClass: 'iradio_square',
        increaseArea: '20%' // optional
    });

    $('.duo_prod_panel select').selectize({
        sortField: 'text'
    });

});