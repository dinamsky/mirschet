<?php

require_once(__DIR__ . '/../../vendor/autoload.php');

class shopSeoredirectPluginErrorExportExcelController extends waController
{
	public function execute()
	{
		PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip);
		$excel = new PHPExcel();
		
		$worksheet = $excel->getActiveSheet();
		
		$position_row = 1;
		$this->writeHeader($worksheet, $position_row);
		$this->writeErrors($worksheet, $position_row);
		$this->readFile($excel);
	}
	
	protected function writeHeader(PHPExcel_Worksheet $worksheet, &$position_row)
	{
		foreach ($this->getColumns() as $position_column => $column)
		{
			$worksheet->getColumnDimensionByColumn($position_column)->setAutoSize(true);
			$worksheet->setCellValueByColumnAndRow($position_column, $position_row, $column['title']);
		}
		
		$position_row++;
	}
	
	protected function writeErrors(PHPExcel_Worksheet $worksheet, &$position_row)
	{
		$error_storage = new shopSeoredirectErrorStorage();
		$columns = $this->getColumns();
		
		foreach ($error_storage->getAllIterable() as $i => $error)
		{
			foreach ($columns as $position_column => $column)
			{
				$cell = $worksheet->getCellByColumnAndRow($position_column, $position_row);
				$cell->setValue($error[$column['code']]);
				unset($cell);
			}
			
			$position_row++;
		}
	}
	
	protected function readFile(PHPExcel $excel)
	{
		$date = date('Ymd');
		$filename = "404-error-{$date}.xlsx";
		
		$writer = PHPExcel_IOFactory::createWriter($excel, "Excel2007");
		
		header('Content-type: application/vnd.ms-excel');
		header("Content-Disposition: attachment; filename=\"{$filename}\"");

		try
		{
			$writer->save('php://output');
		}
		catch (PHPExcel_Writer_Exception $e)
		{
			$temp_file_path = sys_get_temp_dir() . "/" . rand(0, getrandmax()) . rand(0, getrandmax()) . ".tmp";
			$writer->save($temp_file_path);
			readfile($temp_file_path);
			unlink($temp_file_path);
		}
	}
	
	protected function getColumns()
	{
		$excel_config = new shopSeoredirectExcelConfig();
		
		return $excel_config->getErrorColumns();
	}
}
