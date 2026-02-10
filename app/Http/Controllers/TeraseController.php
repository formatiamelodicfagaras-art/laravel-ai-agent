<?php

namespace App\Http\Controllers;

use App\Services\ExcelService;
use Illuminate\Http\Request;
use Exception;

class TeraseController extends Controller
{
    private ExcelService $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    /**
     * Show terase prices page
     */
    public function index()
    {
        $data = $this->excelService->getTerasePrices();
        $rowCount = count($data);

        return view('terase', [
            'data' => $data,
            'rowCount' => $rowCount
        ]);
    }


    /**
     * Update a specific price (temporary update, not persisted)
     */
    public function updateRow(Request $request)
    {
        $request->validate([
            'row_index' => 'required|integer|min:0',
            'pret_achizitie' => 'required|numeric|min:0',
            'pret_vanzare' => 'required|numeric|min:0',
        ], [
            'row_index.required' => 'Indexul rândului este obligatoriu.',
            'pret_achizitie.required' => 'Prețul de achiziție este obligatoriu.',
            'pret_achizitie.numeric' => 'Prețul de achiziție trebuie să fie un număr.',
            'pret_vanzare.required' => 'Prețul de vânzare este obligatoriu.',
            'pret_vanzare.numeric' => 'Prețul de vânzare trebuie să fie un număr.',
        ]);

        try {
            $updatedRow = $this->excelService->updateTerasePrice($request->row_index, [
                'pret_achizitie' => $request->pret_achizitie,
                'pret_vanzare' => $request->pret_vanzare,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Preț actualizat temporar! (Modificările nu sunt permanente)',
                'data' => $updatedRow
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
