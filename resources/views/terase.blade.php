@extends('layout')

@section('title', 'Calculație Terase - Style AI Agent')

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
</style>
@endsection

@section('content')
<div class="card">
    <h2>Lista de Prețuri Terase</h2>
    <p style="margin-bottom: 1rem; color: #666;">Prețuri pentru materiale și servicii terase. Agentul AI va folosi aceste prețuri pentru calculații automate.</p>
    <div class="success-count">{{ $rowCount }} produse în listă</div>
</div>

@if($data !== null && count($data) > 0)
    <div class="card">
        <table class="data-table" id="teraseTable">
            <thead>
                <tr>
                    <th>Produs</th>
                    <th>U.M.</th>
                    <th>Preț Achiziție (lei)</th>
                    <th>Preț Vânzare (lei)</th>
                    <th>Marjă (%)</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                <tr data-index="{{ $index }}">
                    <td>{{ $row['produs'] }}</td>
                    <td>{{ $row['um'] }}</td>
                    <td class="editable" data-field="pret_achizitie">{{ number_format((float)$row['pret_achizitie'], 2) }}</td>
                    <td class="editable" data-field="pret_vanzare">{{ number_format((float)$row['pret_vanzare'], 2) }}</td>
                    <td>{{ $row['marja'] }}%</td>
                    <td>
                        <button class="btn-edit" onclick="editRow({{ $index }})">Editează</button>
                        <button class="btn-save" onclick="saveRow({{ $index }})" style="display:none;">Salvează</button>
                        <button class="btn-cancel" onclick="cancelEdit({{ $index }})" style="display:none;">Anulează</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="info-message" style="margin-top: 1rem;">
            <strong>Notă:</strong> Modificările prețurilor sunt temporare și nu sunt salvate permanent. Pentru modificări permanente, contactați administratorul.
        </div>
    </div>

    <div class="card" style="margin-top: 2rem;">
        <h3>Formule de Calcul pentru Oferte Terase</h3>
        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 4px; margin-top: 1rem;">
            <h4 style="margin-top: 0; color: #2c3e50;">Cum calculează Agentul AI ofertele:</h4>

            <div style="margin: 1rem 0;">
                <strong>1. Calcul Materiale:</strong>
                <div style="margin-left: 1.5rem; color: #555;">
                    Costul = Cantitate × Preț Vânzare
                </div>
            </div>

            <div style="margin: 1rem 0;">
                <strong>2. Calcul MATERIALE+REGIE:</strong>
                <div style="margin-left: 1.5rem; color: #555;">
                    MATERIALE+REGIE = SUM(doar materiale fizice) × 1.4<br>
                    <em>(se aplică marja de 40% asupra sumei materialelor fizice)</em>
                </div>
            </div>

            <div style="margin: 1rem 0;">
                <strong>3. Calcul PROFIT:</strong>
                <div style="margin-left: 1.5rem; color: #555;">
                    PROFIT = (MATERIALE+REGIE + Manoperă + Transport) × 0.30<br>
                    <em>(30% din suma: MATERIALE+REGIE + manoperă + transport - rezultatul reprezintă diferența de 30%)</em>
                </div>
            </div>

            <div style="margin: 1rem 0;">
                <strong>4. Calcul Manoperă:</strong>
                <div style="margin-left: 1.5rem; color: #555;">
                    Cost Manoperă = Ore × 75 lei/oră<br>
                    <em>(dacă nu este specificat, se consideră 0 ore)</em>
                </div>
            </div>

            <div style="margin: 1rem 0;">
                <strong>5. Calcul Deplasare/Transport:</strong>
                <div style="margin-left: 1.5rem; color: #555;">
                    Cost Transport = Kilometri × 2 lei/KM<br>
                    <em>(dacă nu este specificat, se consideră 0 km)</em>
                </div>
            </div>

            <div style="margin: 1rem 0;">
                <strong>6. Total fără TVA:</strong>
                <div style="margin-left: 1.5rem; color: #555;">
                    Total fără TVA = MATERIALE+REGIE + Manoperă + Transport + PROFIT
                </div>
            </div>

            <div style="margin: 1rem 0;">
                <strong>7. Calcul TVA (21%):</strong>
                <div style="margin-left: 1.5rem; color: #555;">
                    TVA = Total fără TVA × 0.21
                </div>
            </div>

            <div style="margin: 1rem 0; padding: 1rem; background: #e8f5e9; border-left: 4px solid #4caf50;">
                <strong>8. Total Final (Total cu TVA):</strong>
                <div style="margin-left: 1.5rem; color: #2e7d32; font-size: 1.1em;">
                    <strong>Total cu TVA = Total fără TVA + TVA</strong>
                </div>
            </div>

            <div style="margin-top: 1.5rem; padding: 1rem; background: #fff3cd; border-left: 4px solid #ffc107;">
                <strong>Exemplu de calcul:</strong>
                <div style="margin-top: 0.5rem; color: #856404;">
                    <strong>Materiale fizice:</strong><br>
                    - FOLIE CRISTAL: 6 mp × 33.60 lei = 201.60 lei<br>
                    - FERMOAR: 1 buc × 49.00 lei = 49.00 lei<br>
                    - CARABINE: 5 buc × 2.10 lei = 10.50 lei<br>
                    <strong>Suma materiale: 261.10 lei</strong><br><br>
                    MATERIALE+REGIE: 261.10 × 1.4 = 365.54 lei<br>
                    Manoperă: 0 ore × 75 = 0.00 lei<br>
                    Transport: 0 km × 2 = 0.00 lei<br><br>
                    <strong>Bază de calcul pentru profit:</strong><br>
                    365.54 (MATERIALE+REGIE) + 0 (Manoperă) + 0 (Transport) = 365.54 lei<br>
                    PROFIT: 365.54 × 0.30 = 109.66 lei ✓<br><br>
                    <strong>Total fără TVA:</strong><br>
                    365.54 (MATERIALE+REGIE) + 0 (Manoperă) + 0 (Transport) + 109.66 (PROFIT) = 475.20 lei<br>
                    TVA 21%: 475.20 × 0.21 = 99.79 lei<br>
                    <strong style="color: #2e7d32;">Total cu TVA: 574.99 lei</strong>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
let originalData = {};

function editRow(index) {
    const row = document.querySelector(`tr[data-index="${index}"]`);
    const cells = row.querySelectorAll('.editable');

    // Save original data
    originalData[index] = {};
    cells.forEach(cell => {
        const field = cell.getAttribute('data-field');
        originalData[index][field] = cell.textContent.trim();

        // Replace with input
        const value = cell.textContent.trim().replace(',', '').replace(' ', '');
        const input = document.createElement('input');
        input.type = 'number';
        input.value = value;
        input.step = '0.01';
        input.min = '0';
        input.className = 'edit-input';
        cell.textContent = '';
        cell.appendChild(input);
    });

    // Toggle buttons
    row.querySelector('.btn-edit').style.display = 'none';
    row.querySelector('.btn-save').style.display = 'inline-block';
    row.querySelector('.btn-cancel').style.display = 'inline-block';
}

function cancelEdit(index) {
    const row = document.querySelector(`tr[data-index="${index}"]`);
    const cells = row.querySelectorAll('.editable');

    // Restore original data
    cells.forEach(cell => {
        const field = cell.getAttribute('data-field');
        cell.textContent = originalData[index][field];
    });

    // Toggle buttons
    row.querySelector('.btn-edit').style.display = 'inline-block';
    row.querySelector('.btn-save').style.display = 'none';
    row.querySelector('.btn-cancel').style.display = 'none';
}

function saveRow(index) {
    const row = document.querySelector(`tr[data-index="${index}"]`);
    const cells = row.querySelectorAll('.editable');

    // Collect data
    const data = { row_index: index };
    cells.forEach(cell => {
        const field = cell.getAttribute('data-field');
        const input = cell.querySelector('input');
        data[field] = parseFloat(input.value);
    });

    // Send AJAX request
    fetch('{{ url("/terase/update") }}', {
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
            // Update cells with new values
            cells.forEach(cell => {
                const field = cell.getAttribute('data-field');
                const input = cell.querySelector('input');
                let value = parseFloat(input.value).toFixed(2);
                cell.textContent = value;
            });

            // Toggle buttons
            row.querySelector('.btn-edit').style.display = 'inline-block';
            row.querySelector('.btn-save').style.display = 'none';
            row.querySelector('.btn-cancel').style.display = 'none';

            alert(result.message || 'Preț actualizat!');
        } else {
            alert('Eroare: ' + (result.error || 'Actualizare eșuată'));
        }
    })
    .catch(error => {
        alert('Eroare la salvare: ' + error);
    });
}
</script>

<style>
.btn-edit, .btn-save, .btn-cancel {
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
    background: #e74c3c;
    color: white;
}

.btn-cancel:hover {
    background: #c0392b;
}

.edit-input {
    width: 100%;
    padding: 0.4rem;
    border: 1px solid #3498db;
    border-radius: 4px;
    font-size: 0.9rem;
}

.data-table th:last-child,
.data-table td:last-child {
    width: 200px;
    text-align: center;
}
</style>
@endsection
