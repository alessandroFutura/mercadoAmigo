$(document).ready(function(){
    setConfigOfficePayments();
})

function setConfigOfficePayments() {

    var table = $('#table_receivable').DataTable( {
        "language": { "url": URI_PUBLIC + "admin/js/DataTable-Portuguese-Brasil.json" },
        "columnDefs": [ {
            "targets": [ 6 ],
            "searchable": false,
            "orderable": false
        },{
            "targets": [ 6 ],
            "width": "30px"
        }],
        "order": [[1, 'desc' ]]
    });

}