<?php
namespace Application\View\Model;

use Zend\View\Model\ViewModel;
use DateTime;
use Zend\Http\Response\Stream;
use Zend\Http\Headers;

class CsvModel extends ViewModel
{
	/**
	 * return file name
	 * @var string
	 */
	protected $fileName;
	
	/**
	 * 
	 * @var string
	 */
	protected $enclosedBy = '"';
	
	/**
	 * 
	 * @var string
	 */
	protected $delmitedBy = ',';
	
	/**
	 * column headers
	 * @var array
	 */
	protected $columnHeaders = [];
	
	/**
	 * data to format
	 * @var array
	 */
	protected $data = [];
	
	
	/**
	 * @param string $fileName
	 */
	public function setFileName($fileName)
	{
		$this->fileName = $fileName;
		return $this;
	}
	
	public function getFileName()
	{
		return $this->fileName;
	}
	
	/**
	 * 
	 * @param string $enclosedBy
	 */
	public function setEnclosedBy($enclosedBy = ',')
	{
		$this->enclosedBy = $enclosedBy;
		return $this;
	}
	
	public function getEnclosedBy()
	{
		return $this->enclosedBy;
	}
	
	/**
	 * 
	 * @param string $delimitedBy
	 */
	public function setDelimitedBy($delimitedBy = '"')
	{
		$this->delmitedBy = $delimitedBy;
		return $this;
	}
	
	public function getDelimitedBy()
	{
		$this->delmitedBy;
	}
	
	/**
	 * 
	 * @param array $headers
	 */
	public function setColumnHeaders(array $headers = [])
	{
		$this->columnHeaders = $headers;
		return $this;
	}
	
	public function getColumnHeaders()
	{
		return $this->columnHeaders;
	}
	
	/**
	 * 
	 * @param array $data
	 */
	public function setData(array $data = [])
	{
		$this->data = $data;
		return $this;
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	/**
	 * output data to CSV
	 */
	public function write()
	{
		if (count($this->data) > 0)
		{
			$stream = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
			
			if(count($this->columnHeaders) > 0){
				fputcsv($stream, $this->columnHeaders, $this->delmitedBy, $this->enclosedBy);
			}
			
			foreach($this->data as $csvRow)
			{
				foreach($csvRow as $key => $item)
				{
					if($item instanceof DateTime){
						$csvRow[$key] = $item->format('Y-m-d H:i:s');
					}
				}
				fputcsv($stream, $csvRow, $this->delmitedBy, $this->enclosedBy);
			}
			
			return $this->download($stream);
		}
	}
	
	private function download($stream)
	{
		if (strpos($this->fileName, '.csv') === false)
		{
			$this->fileName = $this->fileName . '.csv';
		}
		
		rewind($stream);
		
		$headers = new Headers();
		$headers->addHeaders([
				'Content-Type' => 'text/csv',
				'Content-Disposition' => 'attachment; filename="'.$this->fileName.'"',
				'Content-Description' => 'File Transfer',
				'Cache-control' => 'must-revalidate, post-check=0, pre-check=0',
				'Pragma' => 'public'
		]);
		
		$response = new Stream();
		$response->setHeaders($headers)
			->setStatusCode(200)
			->setStream($stream);
		
		return $response;
	}
	
}