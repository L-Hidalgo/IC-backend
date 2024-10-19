<?php
namespace App\Http\Controllers;

use App\Imports\ExcelDataImport;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Maatwebsite\Excel\Facades\Excel;

class ImportarExcelController extends Controller
{
    public function importarExcelPlanilla(Request $request)
    {
        // Validación del archivo
        $request->validate([
            'archivoPlanilla' => 'required|file|mimes:xlsx,xls', // Ajusta el tamaño máximo según lo necesites
        ]);

        try {
            // Obtener el archivo del request
            $file = $request->file('archivoPlanilla');

            // Comprobar que el archivo no es nulo
            if (!$file) {
                throw new \Exception('No se subió ningún archivo.');
            }

            // Cargar el archivo en PhpSpreadsheet
            $spreadsheet = IOFactory::load($file->getPathname());

            $sheetNames = $spreadsheet->getSheetNames();
            $sheetIndex = array_search('NUEVA PLANILLA', $sheetNames);

            // Comprobar si la hoja existe
            if ($sheetIndex === false) {
                throw new \Exception('No se encontró el sheet "NUEVA PLANILLA" en el archivo.');
            }

            $sheet = $spreadsheet->getSheet($sheetIndex);

            $newSpreadsheet = new Spreadsheet();
            $newSpreadsheet->addExternalSheet($sheet);

            $newFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.xls';
            $tempFilePath = storage_path('app/temporary/' . $newFileName);

            // Guardar el nuevo archivo
            $writer = IOFactory::createWriter($newSpreadsheet, 'Xls');
            $writer->save($tempFilePath);

            $convertedFile = new File($tempFilePath);

            // Importar los datos
            Excel::import(new ExcelDataImport, $convertedFile);

            // Eliminar el archivo temporal
            Storage::delete('temporary/' . $newFileName);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            return $this->sendError("Error de PhpSpreadsheet: " . $e->getMessage(), 406);
        } catch (\Exception $e) {
            return $this->sendError("Error general: " . $e->getMessage(), 406);
        }

        return $this->sendSuccess(["msn" => "Exitoso"]);
    }
}


