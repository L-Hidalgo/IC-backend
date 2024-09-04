<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExcelDataImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Validators\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class ImportarExcelController extends Controller
{
    public function importarExcelPlanilla(Request $request)
{
    try {
        $file = $request->file('archivoPlanilla');
        $extension = strtolower($file->getClientOriginalExtension());

        // Si el archivo es .xls, convertirlo a .xlsx
        if ($extension === 'xlsx') {
            $spreadsheet = IOFactory::load($file->getPathname());
            $newFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.xlsx';
            $tempFilePath = storage_path('app/temporary/' . $newFileName);
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFilePath);
            $filePath = $tempFilePath;
        } else {
            $filePath = $file->getPathname();
        }

        // Procesar el archivo en formato adecuado
        Excel::import(new ExcelDataImport, $filePath);

        // Eliminar el archivo temporal si se creó
        if (isset($tempFilePath) && file_exists($tempFilePath)) {
            unlink($tempFilePath);
        }

    } catch (ValidationException $e) {
        $failures = $e->failures();
        $errorMessages = [];

        foreach ($failures as $failure) {
            $errorMessages[] = "Fila {$failure->row()}: {$failure->errors()[0]}";
        }
        return $this->sendError("Error de validación: " . implode(', ', $errorMessages), 406);
    } catch (\Exception $e) {
        return $this->sendError("Error al procesar el archivo: " . $e->getMessage(), 406);
    }

    return $this->sendSuccess(["msn" => "Exitoso"]);
}
}
