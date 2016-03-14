$('form.landing-pages-form').submit(function(){

    var $inputs = $('input', this);

    var ok = true;
    for ( var i = 0; i < $inputs.length; i++ ) {
        var inp = $($inputs[i]);
        if ( inp.hasClass('validate-mandatory') ) {
            if ( inp.val().trim() == '' ) {
                // ERROR
                inp.parents('.form-group').addClass('has-error has-feedback');
                if ( ok ) inp.focus();
                ok = false;
            } else {
                // OK
                inp.parents('.form-group').removeClass('has-error has-feedback');
            }
        }
    }

    if ( ok ) {
        stats_conversion( $('input[name="_CONVERSION"]').val() );
    }

    return ok;
});
