jQuery(function($) {
    $("#mobi_menu_btn").click(function(){
        $("#block-chum-main-menu").slideToggle();
    });
    $(".tab-category").click(function(){
        $(".tab-category").removeClass('tab_on');
        $('.list-category').slideUp();
        $(this).addClass('tab_on');
        var _rel = $(this).attr('rel');
        $('.list-category[rel='+_rel+']').slideToggle();
    });
});
