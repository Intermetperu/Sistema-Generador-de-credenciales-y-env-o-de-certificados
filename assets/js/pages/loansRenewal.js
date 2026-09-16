let table;
document.addEventListener('DOMContentLoaded', function () {
    table = new DataTable('#loansTable', {
        responsive: true,
        ajax: {
            url: base_url + "loans/renewals/list",
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'original_loan_id' },
            { data: 'new_loan_id' },
            { data: 'reason' },
            { data: 'created_at' }
        ],
        language,
        order: [0, 'asc']
    });
});
