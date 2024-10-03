<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExcelDataImport;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Storage;

class ImportarExcelController extends Controller
{
    public function importarExcelPlanilla(Request $request)
    {
        try {
            $file = $request->file('archivoPlanilla');

            $spreadsheet = IOFactory::load($file->getPathname());

            $sheetNames = $spreadsheet->getSheetNames();
            $sheetIndex = array_search('NUEVA PLANILLA', $sheetNames);

            if ($sheetIndex === false) {
                throw new \Exception('No se encontró el sheet "NUEVA PLANILLA" en el archivo.');
            }

            $sheet = $spreadsheet->getSheet($sheetIndex);

            $newSpreadsheet = new Spreadsheet();
            $newSpreadsheet->addExternalSheet($sheet);

            $newFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.xls';

            $tempFilePath = storage_path('app/temporary/' . $newFileName);

            $writer = IOFactory::createWriter($newSpreadsheet, 'Xls');
            $writer->save($tempFilePath);

            $convertedFile = new \Illuminate\Http\File($tempFilePath);

            Excel::import(new ExcelDataImport, $convertedFile);

            Storage::delete('temporary/' . $newFileName);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            return $this->sendError("Error de PhpSpreadsheet: " . $e->getMessage(), 406);
        } catch (\Exception $e) {
            return $this->sendError("Error general: " . $e->getMessage(), 406);
        }

        return $this->sendSuccess(["msn" => "Exitoso"]);
    }
}
