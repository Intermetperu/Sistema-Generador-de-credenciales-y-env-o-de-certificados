let table;
document.addEventListener('DOMContentLoaded', function () {
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const pdfExport = document.getElementById('pdfExport');
    const excelExport = document.getElementById('excelExport');

    table = $('#paymentsTable').DataTable({
        responsive: true,
        ajax: {
            url: base_url + 'payments/list',
            data: function (d) {
                d.start_date = startInput.value;
                d.end_date = endInput.value;
            }
        },
        columns: [
            {
                data: 'encrypted_id',
                orderable: false,
                searchable: false,
                render: function (data) {
                    return `
                        <button type=""button onclick="abrirVentanaPDF('${base_url}payments/print/${data}')" class="btn btn-sm btn-danger">
                            <i class="fas fa-print"></i>
                        </button>`;
                }
            },
            { data: 'payment_date' },
            { data: 'amount' },
            {
                data: 'currency',
                render: function (data) {
                    return `<span class="badge bg-secondary">${data}</span>`;
                }
            },
            { data: 'name_payment_method' },
            {
                data: 'loan_code',
                render: function (data) {
                    return `<span class="badge bg-dark">#${data}</span>`;
                }
            },
            { data: 'note' },
            { data: 'user' },
            {
                data: 'total_paid',
                render: function (data) {
                    return `<span class="badge bg-success">${data}</span>`;
                }
            },
            { data: 'total_late_fee' },
            { data: 'detail_count' },
        ],
        language
    });

    function updateExportLinks(start, end) {
        // Si hay fechas, agrega al link; si no, genera todos
        let query = '';
        if (start && end) {
            query = `?start=${start}&end=${end}`;
        }
        pdfExport.href = `${base_url}payments/export/pdf${query}`;
        excelExport.href = `${base_url}payments/export/excel${query}`;
    }

    document.getElementById('filterBtn').addEventListener('click', function () {
        const start = startInput.value;
        const end = endInput.value;

        // No validamos nada, permitimos todos si no hay fechas
        updateExportLinks(start, end);
        table.ajax.reload();
    });

    // Inicializar exportación sin filtro (para generar todo al inicio)
    updateExportLinks('', '');
});
