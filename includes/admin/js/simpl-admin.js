var $ = jQuery;
$(document).ready(function () {
    var closestDiv = $('span.simpl-accordian-mapping').closest('div')
    closestDiv.addClass('accordian-heading')
    closestDiv.prev('h2').addClass('accordian-heading')

    $(document).on('click', '.accordian-heading', function () {
        var currentClickedItem = $(this).prop("tagName").toLowerCase()
        if (currentClickedItem == 'div' && $(this).attr('id') == 'checkout_endpoint_options-description' || currentClickedItem == 'h2' && $(this).next().closest('div').attr('id') == 'checkout_endpoint_options-description') {
            $('#wc_settings_tab_simpl_api_key-description').toggle()
            currentClickedItem == 'div' ? $(this).find('p').toggleClass('clicked') : $(this).next().find('p').toggleClass('clicked')
        } else if (currentClickedItem == 'div') {
            $(this).next().closest('.form-table').toggle()
            $(this).find('p').toggleClass('clicked')
        } else if (currentClickedItem == 'h2') {
            $(this).next().find('p').toggleClass('clicked')
            $(this).next().next().closest('.form-table').toggle()
        }
    })

    $('.status-enabled-simpl').each(function () {
        $(this).closest('div').trigger('click');
    });
})