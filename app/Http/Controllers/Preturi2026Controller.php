<?php

namespace App\Http\Controllers;

use App\Services\ExcelService;
use Illuminate\Http\Request;
use Exception;

class Preturi2026Controller extends Controller
{
    private ExcelService $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    /**
     * Show upload page with preview if file exists
     */
    public function index()
    {
        $data = null;
        $rowCount = 0;
        $filePath = storage_path('app/uploads/preturi_2026.xlsx');

        if (file_exists($filePath)) {
            try {
                $data = $this->excelService->readPriceList($filePath);
                $rowCount = count($data);
            } catch (Exception $e) {
                return view('preturi2026', [
                    'data' => null,
                    'rowCount' => 0,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('preturi2026', [
            'data' => $data,
            'rowCount' => $rowCount
        ]);
    }

    /**
     * Process Excel upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'excel_file.required' => 'Vă rugăm să selectați un fișier Excel.',
            'excel_file.file' => 'Fișierul încărcat nu este valid.',
            'excel_file.mimes' => 'Fișierul trebuie să fie de tip .xlsx sau .xls.',
            'excel_file.max' => 'Fișierul nu trebuie să depășească 10MB.',
        ]);

        try {
            $file = $request->file('excel_file');

            $uploadPath = storage_path('app/uploads');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $filePath = $uploadPath . '/preturi_2026.xlsx';
            $file->move($uploadPath, 'preturi_2026.xlsx');

            $data = $this->excelService->readPriceList($filePath);
            $rowCount = count($data);

            return redirect('/preturi-2026')->with('success', "Listă prețuri 2026 încărcată cu succes — {$rowCount} produse.");
        } catch (Exception $e) {
            return back()->withErrors(['upload' => $e->getMessage()]);
        }
    }

    /**
     * Update a price list row via AJAX
     */
    public function updateRow(Request $request)
    {
        try {
            $filePath = storage_path('app/uploads/preturi_2026.xlsx');
            $rowIndex = $request->input('row_index');

            $newData = [
                'denumire' => $request->input('denumire'),
                'um' => $request->input('um'),
                'pret_cu_tva' => $request->input('pret_cu_tva'),
            ];

            $this->excelService->updatePriceRow($filePath, $rowIndex, $newData);

            return response()->json(['success' => true, 'message' => 'Preț actualizat cu succes!']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete entire price list file
     */
    public function deleteAll()
    {
        $filePath = storage_path('app/uploads/preturi_2026.xlsx');

        if (file_exists($filePath)) {
            unlink($filePath);
            return redirect('/preturi-2026')->with('success', 'Lista de prețuri a fost ștearsă cu succes.');
        }

        return redirect('/preturi-2026')->with('error', 'Nu există nicio listă de prețuri de șters.');
    }

    /**
     * Delete a price list row via AJAX
     */
    public function deleteRow(Request $request)
    {
        try {
            $filePath = storage_path('app/uploads/preturi_2026.xlsx');
            $rowIndex = $request->input('row_index');

            $this->excelService->deletePriceRow($filePath, $rowIndex);

            return response()->json(['success' => true, 'message' => 'Produs șters cu succes!']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
