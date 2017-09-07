jQuery(function($) {
    $("#mobi_menu_btn").click(function(){
        $("#block-chum-main-menu").slideToggle();
    });
    $("#block-ongletsderecherche .onglet").click(function(){
        $(this).next('ul').slideToggle('');
    });
});
