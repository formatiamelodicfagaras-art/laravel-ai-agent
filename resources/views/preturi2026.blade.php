@extends('layout')

@section('title', 'Lista Prețuri 2026 - Style AI Agent')

@section('styles')
<style>
    .upload-section {
        margin-bottom: 2rem;
    }

    .upload-form {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .file-input {
        flex: 1;
        padding: 0.75rem;
        border: 2px dashed #ddd;
        border-radius: 4px;
        cursor: pointer;
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
    }

    .data-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }

    .data-table tr:hover {
        background: #f8f9fa;
    }

    .success-count {
        color: #27ae60;
        font-weight: 600;
        margin-top: 1rem;
    }

    .btn-edit, .btn-save, .btn-cancel, .btn-row-delete {
        padding: 0.4rem 0.8rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.85rem;
        margin-right: 0.3rem;
    }

    .btn-edit {
        background: #3498db;
        color: white;
    }

    .btn-edit:hover {
        background: #2980b9;
    }

    .btn-save {
        background: #27ae60;
        color: white;
    }

    .btn-save:hover {
        background: #229954;
    }

    .btn-cancel {
        background: #95a5a6;
        color: white;
    }

    .btn-cancel:hover {
        background: #7f8c8d;
    }

    .btn-row-delete {
        background: #e74c3c;
        color: white;
    }

    .btn-row-delete:hover {
        background: #c0392b;
    }

    .edit-input {
        width: 100%;
        padding: 0.4rem;
        border: 1px solid #3498db;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .data-table th:first-child,
    .data-table td:first-child {
        width: 50px;
        text-align: center;
    }

    .data-table th:last-child,
    .data-table td:last-child {
        width: 220px;
        text-align: center;
    }

    .filter-section {
        display: flex;
        gap: 1rem;
        align-items: center;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }

    .filter-input {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.9rem;
        width: 250px;
    }

    .btn-delete-all {
        padding: 0.75rem 2rem;
        background: #e74c3c;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-delete-all:hover {
        background: #c0392b;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endsection

@section('content')
<div class="card upload-section">
    <div class="card-header">
        <h2>Listă Prețuri 2026</h2>
        @if($data !== null && count($data) > 0)
        <form action="{{ url('/preturi-2026/delete-all') }}" method="POST"
              onsubmit="return confirm('Sigur doriți să ștergeți toată lista de prețuri? Această acțiune nu poate fi anulată!');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-delete-all">Șterge Lista Prețuri</button>
        </form>
        @endif
    </div>
    <p style="margin-bottom: 1rem; color: #666;">Încarcă fișierul Excel cu lista de prețuri actualizată pentru 2026. Agentul AI va putea face calcule automate pe baza acestor date.</p>
    <form action="{{ url('/preturi-2026') }}" method="POST" enctype="multipart/form-data" class="upload-form">
        @csrf
        <input type="file" name="excel_file" accept=".xlsx,.xls" required class="file-input">
        <button type="submit" class="btn-upload">Încarcă</button>
    </form>
</div>

@if($data === null && $rowCount === 0)
    <div class="info-message">
        Nu există listă de prețuri încărcată. Uploadați un fișier pentru a-l vizualiza.
    </div>
@elseif($data !== null && count($data) > 0)
    <div class="card">
        <h3>Preview Listă Prețuri 2026</h3>
        <div class="success-count">{{ $rowCount }} produse în listă</div>

        <div class="filter-section">
            <label>Filtru:</label>
            <input type="text" id="filterInput" class="filter-input" placeholder="Caută produs..." oninput="filterTable()">
        </div>

        <table class="data-table" id="priceTable">
            <thead>
                <tr>
                    <th>Nr.</th>
                    <th>Denumire produs sau serviciu</th>
                    <th>U.M.</th>
                    <th>Preț cu TVA (lei)</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                <tr data-index="{{ $index }}">
                    <td>{{ $index + 1 }}</td>
                    <td class="editable" data-field="denumire">{{ $row['denumire'] }}</td>
                    <td class="editable" data-field="um">{{ $row['um'] }}</td>
                    <td class="editable" data-field="pret_cu_tva">{{ is_numeric($row['pret_cu_tva']) ? number_format((float)$row['pret_cu_tva'], 2) : $row['pret_cu_tva'] }} lei</td>
                    <td>
                        <button class="btn-edit" onclick="editRow({{ $index }})">Editează</button>
                        <button class="btn-save" onclick="saveRow({{ $index }})" style="display:none;">Salvează</button>
                        <button class="btn-cancel" onclick="cancelEdit({{ $index }})" style="display:none;">Anulează</button>
                        <button class="btn-row-delete" onclick="deleteRow({{ $index }})">Șterge</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection

@section('scripts')
<script>
let originalData = {};

function filterTable() {
    const filter = document.getElementById('filterInput').value.toLowerCase();
    const rows = document.querySelectorAll('#priceTable tbody tr');

    rows.forEach(row => {
        const denumire = row.querySelector('[data-field="denumire"]').textContent.toLowerCase();
        row.style.display = denumire.includes(filter) ? '' : 'none';
    });
}

function editRow(index) {
    const row = document.querySelector(`tr[data-index="${index}"]`);
    const cells = row.querySelectorAll('.editable');

    originalData[index] = {};
    cells.forEach(cell => {
        const field = cell.getAttribute('data-field');
        originalData[index][field] = cell.textContent.trim();

        const value = cell.textContent.trim();
        const input = document.createElement('input');
        input.type = field === 'pret_cu_tva' ? 'number' : 'text';
        input.value = field === 'pret_cu_tva' ? value.replace(/[^0-9.]/g, '') : value;
        if (field === 'pret_cu_tva') {
            input.step = '0.01';
            input.min = '0';
        }
        input.className = 'edit-input';
        cell.textContent = '';
        cell.appendChild(input);
    });

    row.querySelector('.btn-edit').style.display = 'none';
    row.querySelector('.btn-row-delete').style.display = 'none';
    row.querySelector('.btn-save').style.display = 'inline-block';
    row.querySelector('.btn-cancel').style.display = 'inline-block';
}

function cancelEdit(index) {
    const row = document.querySelector(`tr[data-index="${index}"]`);
    const cells = row.querySelectorAll('.editable');

    cells.forEach(cell => {
        const field = cell.getAttribute('data-field');
        cell.textContent = originalData[index][field];
    });

    row.querySelector('.btn-edit').style.display = 'inline-block';
    row.querySelector('.btn-row-delete').style.display = 'inline-block';
    row.querySelector('.btn-save').style.display = 'none';
    row.querySelector('.btn-cancel').style.display = 'none';
}

function saveRow(index) {
    const row = document.querySelector(`tr[data-index="${index}"]`);
    const cells = row.querySelectorAll('.editable');

    const data = { row_index: index };
    cells.forEach(cell => {
        const field = cell.getAttribute('data-field');
        const input = cell.querySelector('input');
        data[field] = field === 'pret_cu_tva' ? parseFloat(input.value) : input.value;
    });

    fetch('{{ url("/preturi-2026/update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            cells.forEach(cell => {
                const field = cell.getAttribute('data-field');
                const input = cell.querySelector('input');
                if (field === 'pret_cu_tva') {
                    cell.textContent = parseFloat(input.value).toFixed(2) + ' lei';
                } else {
                    cell.textContent = input.value;
                }
            });

            row.querySelector('.btn-edit').style.display = 'inline-block';
            row.querySelector('.btn-row-delete').style.display = 'inline-block';
            row.querySelector('.btn-save').style.display = 'none';
            row.querySelector('.btn-cancel').style.display = 'none';

            alert(result.message);
        } else {
            alert('Eroare: ' + (result.error || 'Actualizare eșuată'));
        }
    })
    .catch(error => {
        alert('Eroare la salvare: ' + error);
    });
}

function deleteRow(index) {
    if (!confirm('Sigur doriți să ștergeți acest produs?')) {
        return;
    }

    const row = document.querySelector(`tr[data-index="${index}"]`);

    fetch('{{ url("/preturi-2026/delete") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ row_index: index })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            row.remove();
            // Reload to update indexes
            location.reload();
        } else {
            alert('Eroare: ' + (result.error || 'Ștergere eșuată'));
        }
    })
    .catch(error => {
        alert('Eroare la ștergere: ' + error);
    });
}
</script>
@endsection
