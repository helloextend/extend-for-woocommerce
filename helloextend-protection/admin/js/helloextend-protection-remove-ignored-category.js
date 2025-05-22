(function ( $ ) {
    $('.helloextend-category-remove').on('click', (e) => {
        const categoryId = e.target.parentElement.dataset.categoryId;
        const ajaxUrl = window.location.origin + window.ajaxurl;

        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data: {
                action: 'helloextend_remove_ignored_category',
                categoryId: categoryId
            },
            success: (data, message, xhr) => {
                $(`div[data-category-id="${categoryId}"]`).remove();
                if ($('.helloextend-category-button').length == 0) {
                    $('.helloextend-ignored-categories-container').prepend('None');
                }
            },
            failure: (e) => {
                console.error('failure', e);
            }
        })
    });
})(jQuery);