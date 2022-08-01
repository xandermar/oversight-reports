(function ($) {

    $('#edit-available-fields--wrapper').append(`
    <div><a href="#" id="oversight_reports-checkall"><strong>Select All Fields</strong></a> / 
    <a href="#" id="oversight_reports-uncheckall"><strong>De-Select All Fields</strong></a></div>
    `);
    $('#oversight_reports-checkall').click(function (event) {
        event.preventDefault();
        $(".available-fields").prop("checked", true);
    });
    $('#oversight_reports-uncheckall').click(function (event) {
        event.preventDefault();
        $(".available-fields").prop("checked", false);
    });

})(jQuery);

