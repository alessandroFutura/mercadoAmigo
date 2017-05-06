$(document).ready(function() {
    setConfig();
});

function setConfig() {
    //$('.treeview').removeClass('active');
    $('#menuAccount').addClass('active');

    $(".select2").select2();

    var table = $('#table_checking_account').DataTable({
        "language": {"url": URI_PUBLIC + "admin/js/DataTable-Portuguese-Brasil.json"},
        "columnDefs": [{
            "targets": [0, 6, 7],
            "searchable": false,
            "orderable": false
        }],
        "order": [[1, 'asc'], [2, 'asc']]
    });
}