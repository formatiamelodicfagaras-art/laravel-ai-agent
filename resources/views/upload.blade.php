@extends('layout')

@section('title', 'Baza de Date Contabilitate - Style AI Agent')

@section('styles')
<style>
    .upload-section {
        margin-bottom: 2rem;
    }

    .upload-form {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .file-input {
        flex: 1;
        padding: 0.75rem;
        border: 2px dashed #ddd;
        border-radius: 4px;
        cursor: pointer;
        min-width: 200px;
    }

    .btn-upload {
        padding: 0.75rem 2rem;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-upload:hover {
        background: #2980b9;
    }

    .btn-delete {
        padding: 0.75rem 2rem;
        background: #e74c3c;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-delete:hover {
        background: #c0392b;
    }

    .info-message {
        background: #fff3cd;
        color: #856404;
        padding: 1rem;
        border-radius: 4px;
        border: 1px solid #ffeaa7;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .data-table th,
    .data-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #ddd;
        font-size: 0.9rem;
    }

    .data-table th {
        background: #3498db;
        color: white;
        font-weight: 600;
        position: sticky;
        top: 0;
    }

    .data-table tr:hover {
        background: #f8f9fa;
    }

    .success-count {
        color: #27ae60;
        font-weight: 600;
        margin-top: 1rem;
    }

    .filters-section {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .filter-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #555;
    }

    .filter-input {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-width: 200px;
    }

    .filter-input:focus {
        outline: none;
        border-color: #3498db;
    }

    .btn-clear-filter {
        padding: 0.5rem 1rem;
        background: #95a5a6;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        align-self: flex-end;
    }

    .btn-clear-filter:hover {
        background: #7f8c8d;
    }

    .table-container {
        max-height: 500px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .filter-count {
        color: #3498db;
        font-weight: 600;
    }

    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
</style>
@endsection

@section('content')
<div class="card upload-section">
    <div class="header-actions">
        <h2>Baza de Date Contabilitate</h2>
        @if($data !== null && count($data) > 0)
        <form action="{{ url('/upload/delete') }}" method="POST" onsubmit="return confirm('Sigur doriți să ștergeți toată baza de date? Această acțiune nu poate fi anulată!');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-delete">Sterge Baza de Date</button>
        </form>
        @endif
    </div>
    <form action="{{ url('/upload') }}" method="POST" enctype="multipart/form-data" class="upload-form" style="margin-top: 1rem;">
        @csrf
        <input type="file" name="excel_file" accept=".xlsx,.xls" required class="file-input">
        <button type="submit" class="btn-upload">Incarca</button>
    </form>
</div>

@if($data === null && $rowCount === 0)
    <div class="info-message">
        Nu exista fisier Excel incarcat. Uploadati un fisier pentru a-l vizualiza.
    </div>
@elseif($data !== null && count($data) > 0)
    <div class="card">
        <h3>Preview Date Excel</h3>
        <div class="success-count">{{ $rowCount }} randuri de date incarcate</div>

        <!-- Filtre -->
        <div class="filters-section" style="margin-top: 1rem;">
            <div class="filter-group">
                <label for="filterClient">Filtru Client:</label>
                <input type="text" id="filterClient" class="filter-input" placeholder="Cauta client...">
            </div>
            <div class="filter-group">
                <label for="filterArticol">Filtru Articol:</label>
                <input type="text" id="filterArticol" class="filter-input" placeholder="Cauta articol...">
            </div>
            <button type="button" class="btn-clear-filter" onclick="clearFilters()">Sterge Filtre</button>
            <span class="filter-count" id="filterCount"></span>
        </div>

        <div class="table-container">
            <table class="data-table" id="dataTable">
                <thead>
                    <tr>
                        <th>Vanzator</th>
                        <th>DataDoc</th>
                        <th>Client</th>
                        <th>Articol</th>
                        <th>UM</th>
                        <th>Cantitate</th>
                        <th>Pret fara TVA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $row)
                    <tr>
                        <td>{{ $row['vanzator'] }}</td>
                        <td>{{ $row['data_doc'] }}</td>
                        <td class="cell-client">{{ $row['client'] }}</td>
                        <td class="cell-articol">{{ $row['articol'] }}</td>
                        <td>{{ $row['um'] }}</td>
                        <td>{{ $row['cantitate'] }}</td>
                        <td>{{ number_format((float)$row['pret_fara_tva'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
const filterClient = document.getElementById('filterClient');
const filterArticol = document.getElementById('filterArticol');
const dataTable = document.getElementById('dataTable');
const filterCount = document.getElementById('filterCount');

function applyFilters() {
    if (!dataTable) return;

    const clientValue = filterClient.value.toLowerCase().trim();
    const articolValue = filterArticol.value.toLowerCase().trim();
    const rows = dataTable.querySelectorAll('tbody tr');

    let visibleCount = 0;
    let totalCount = rows.length;

    rows.forEach(row => {
        const clientCell = row.querySelector('.cell-client');
        const articolCell = row.querySelector('.cell-articol');

        const clientText = clientCell ? clientCell.textContent.toLowerCase() : '';
        const articolText = articolCell ? articolCell.textContent.toLowerCase() : '';

        const matchClient = clientValue === '' || clientText.includes(clientValue);
        const matchArticol = articolValue === '' || articolText.includes(articolValue);

        if (matchClient && matchArticol) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    if (clientValue || articolValue) {
        filterCount.textContent = `Afisate: ${visibleCount} din ${totalCount}`;
    } else {
        filterCount.textContent = '';
    }
}

function clearFilters() {
    if (filterClient) filterClient.value = '';
    if (filterArticol) filterArticol.value = '';
    applyFilters();
}

if (filterClient) filterClient.addEventListener('input', applyFilters);
if (filterArticol) filterArticol.addEventListener('input', applyFilters);
</script>
@endsection
