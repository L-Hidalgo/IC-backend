<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExcelDataImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Validators\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportarExcelController extends Controller
{
    public function importarExcelPlanilla(Request $request)
    {
        try {
            $file = $request->file('archivoPlanilla');

            $spreadsheet = IOFactory::load($file->getPathname());

            $originalFileName = $file->getClientOriginalName();

            $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME);

            $newFileName = $fileNameWithoutExtension . '.xls';

            $tempFilePath = storage_path('app/temporary/' . $newFileName);

            $writer = IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save($tempFilePath);

            $convertedFile = new \Illuminate\Http\File($tempFilePath);

            Excel::import(new ExcelDataImport, $convertedFile);
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];

            foreach ($failures as $failure) {
                $errorMessages[] = "Fila {$failure->row()}: {$failure->errors()[0]}";
            }
            return $this->sendError($e->getMessage(), 406);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 406);
        }

        return $this->sendSuccess(["msn" => "Exitoso"]);
    }
}
