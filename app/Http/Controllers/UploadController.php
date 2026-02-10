<?php

namespace App\Http\Controllers;

use App\Services\ExcelService;
use Illuminate\Http\Request;
use Exception;

class UploadController extends Controller
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
        $filePath = storage_path('app/uploads/data.xlsx');

        if (file_exists($filePath)) {
            try {
                $data = $this->excelService->readExcel($filePath);
                $rowCount = count($data);
            } catch (Exception $e) {
                return view('upload', [
                    'data' => null,
                    'rowCount' => 0,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('upload', [
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

            // Ensure uploads directory exists
            $uploadPath = storage_path('app/uploads');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Save file as data.xlsx (overwrite if exists)
            $filePath = $uploadPath . '/data.xlsx';
            $file->move($uploadPath, 'data.xlsx');

            // Validate the Excel structure
            $data = $this->excelService->readExcel($filePath);
            $rowCount = count($data);

            return redirect('/upload')->with('success', "Excel încărcat cu succes — {$rowCount} rânduri de date.");
        } catch (Exception $e) {
            return back()->withErrors(['upload' => $e->getMessage()]);
        }
    }

    /**
     * Delete the uploaded Excel file
     */
    public function delete()
    {
        $filePath = storage_path('app/uploads/data.xlsx');

        if (file_exists($filePath)) {
            unlink($filePath);
            return redirect('/upload')->with('success', 'Baza de date a fost ștearsă cu succes.');
        }

        return redirect('/upload')->with('error', 'Nu există nicio bază de date de șters.');
    }
}
