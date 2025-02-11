(function( $ ) {
    $(document).ready(($) => {
        let isIgnored = $('input[name="helloextend-ignore-value"]').val() == 1;

        if (isIgnored)
            $('input#helloextend-ignore-display').attr("checked", isIgnored);

        $('#helloextend-ignore-display').on('click', (e) => {
            $('input[name="helloextend-ignore-value"]').attr('value', e.currentTarget.checked ? 1 : 0);
        });
    });
})(jQuery);