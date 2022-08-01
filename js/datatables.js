jQuery(document).ready(function ($) {
    $('#oversight-reports').DataTable({
        order: [[0, 'desc']],
        initComplete: function () {
            this.api()
                .columns([2, 3])
                .every(function () {
                    var column = this;
                    var select = $('<select><option value=""></option></select>')
                        .appendTo($(column.footer()).empty())
                        .on('change', function () {
                            var val = $.fn.dataTable.util.escapeRegex($(this).val());

                            column.search(val ? '^' + val + '$' : '', true, false).draw();
                        });

                    column
                        .data()
                        .unique()
                        .sort()
                        .each(function (d, j) {
                            select.append('<option value="' + d + '">' + d + '</option>');
                        });
                });
        },
    });
    $('#recommendations-results-table').DataTable({
        order: [[0, 'asc']],
    });
});