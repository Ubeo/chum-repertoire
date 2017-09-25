jQuery(function($) {
    $("#repertoire-form").submit(function() {
        var _ok = false;

        var _checked_choices = $('.check-choices:checked');
        if (_checked_choices.length > 0) {
            _ok = true;
        }

        if($('#mot-clef').val() != '') {
            _ok = true;
        }

        if(!_ok) {
            alert("Veuillez choisir un mot-clé et/ou une catégorie pour effectuer une recherche.");
            return false;
        }
    });

    $("#form-submit").click(function() {
        $("#repertoire-form").submit();
    });
});
