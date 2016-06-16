<?php
ini_set('memory_limit', '512M'); // FIX
chdir(dirname(__FILE__));

require_once("../../../../inc/magmi_defs.php");
require_once('../../../../inc/PHPExcel/IOFactory.php');
require_once('../../../../inc/PHPExcel/Cell.php');
require_once('../../../../inc/PHPExcel/Worksheet.php');
require_once('../../../inc/magmi_datasource.php');

/*require_once('/var/www/magento-marec1/magmi/inc/magmi_utils.php');
require_once('/var/www/magento-marec1/magmi/inc/PHPExcel/IOFactory.php');
require_once('/var/www/magento-marec1/magmi/plugins/inc/magmi_datasource.php');*/


class Magmi_XLSDataSource extends Magmi_Datasource {
	protected $objWorksheet;
	protected $rows = 0;
	protected $cols = array();
	protected $row_index = 2; // da shranimo pozicijo, kje smo ostali pri branju vrstic
	protected $highestColumnIndex = 0;

	public function initialize($params) {
	}

	public function getAbsPath($path) {
		return abspath($path,$this->getScanDir());
	}

	public function getScanDir($resolve=true) {
		$scandir=$this->getParam("XLS:basedir","media/import");
		if(!isabspath($scandir)) {
			$scandir=abspath($scandir,Magmi_Config::getInstance()->getMagentoDir(),$resolve);
		}
		return $scandir;	
	}

	public function getXLSList() {
		$scandir=$this->getScanDir();
		$files=glob("$scandir/*.xls");
		return $files;
	}

	public function getPluginParams($params) {
		$pp=array();
		foreach($params as $k=>$v)
		{
			if(preg_match("/^XLS:.*$/",$k))
			{
				$pp[$k]=$v;
			}
		}
		return $pp;
	}

	public function getPluginInfo() {
		return array("name"=>"XLS Datasource",
					 "author"=>"Spletni sistemi",
					 "version"=>"0.9");
	}

	public function getRecordsCount() {
		return $this->rows;
	}

	public function getAttributeList()
	{
	}

	// import	

	protected function curlData($url) {
		$ch = curl_init(); 
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	} 
	public function beforeImport()
	{
		
		if($this->getParam("XLS:importmode","local")=="remote") {
			$url=$this->getParam("XLS:remoteurl","");
			$creds="";
			$authmode="";
			if($this->getParam("XLS:remoteauth",false)==true)
			{
				$user=$this->getParam("XLS:remoteuser");
				$pass=$this->getParam("XLS:remotepass");
				$authmode=$this->getParam("XLS:authmode");
				$creds="$user:$pass";
			}
			$outname=$this->getRemoteFile($url,$creds,$authmode);
			$this->setParam("XLS:filename", $outname);
		}
		$fileType = 'Excel5';
		$filename = $this->getParam('XLS:filename');

        // Read the file
        $objReader = PHPExcel_IOFactory::createReader($fileType);
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($filename);
		$this->objWorksheet = $objPHPExcel->getActiveSheet();

		$this->iterator = $this->objWorksheet->getRowIterator();
		$this->rows = $this->objWorksheet->getHighestRow() - 1;

		$highestColumn = $this->objWorksheet->getHighestColumn();
		$this->highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		for ($col = 0; $col <= $this->highestColumnIndex; ++$col) {
			$val = $this->objWorksheet->getCellByColumnAndRow($col, 1)->getValue();
			array_push($this->cols, $val);
		}
	}
	private function fetch_row($trim=false) {

		$row = array();
		for ($col = 0; $col <= $this->highestColumnIndex; ++$col) {
			$val = $this->objWorksheet->getCellByColumnAndRow($col, $this->row_index)->getValue();
			if (empty($val) && $col == 0) {
				break;
			}else{
				array_push($row, $trim?trim($val):$val);
			}
		}
		$this->row_index++;
		if (empty($row))
			return false;
		$record=array_combine($this->cols,$row);
		return $record;
	}
	
	
	public function afterImport() {
	}
	
	public function startImport() {

	}

	public function getColumnNames($prescan=false) {
		return $this->cols;
	}
	
	public function endImport() {
	}

	
	public function getNextRecord() {
		return $this->fetch_row();
	}
  public function getRemoteFile($url,$creds=null,$authmode=null)
  {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	$this->log("Fetching XLS: $url","startup");
			//output filename (current dir+remote filename)
	$xlsdldir=dirname(__FILE__)."/downloads";
	if(!file_exists($xlsdldir))
	{
		@mkdir($xlsdldir);
		@chmod($xlsdldir, Magmi_Config::getInstance()->getDirMask());
	}
	
		$outname=$xlsdldir."/".md5(basename($url));
  //open file for writing
		if(file_exists($outname))
		{
			unlink($outname);
		}
		$fp = fopen($outname, "w");
		if($fp==false)
		{
			throw new Exception("Cannot write file:$outname");
		}

	if(substr($url,0,4)=="http")
	{
		$lookup=1;
                
  	  $lookup_opts= array(CURLOPT_RETURNTRANSFER=>true,
							     CURLOPT_HEADER=>true,
							     CURLOPT_NOBODY=>true,
							     CURLOPT_FOLLOWLOCATION=>true,
							     CURLOPT_FILETIME=>true,
							     CURLOPT_CUSTOMREQUEST=>"HEAD");
							  
    	$dl_opts=array(CURLOPT_FILE=>$fp,
		                         CURLOPT_CUSTOMREQUEST=>"GET",
	  						     CURLOPT_HEADER=>false,
							     CURLOPT_NOBODY=>false,
							     CURLOPT_FOLLOWLOCATION=>true,
							     CURLOPT_UNRESTRICTED_AUTH=>true,
							     CURLOPT_HTTPHEADER=> array('Expect:'));
	
	}
	else
	{
		if(substr($url,0,3)=="ftp")
		{
			$lookup=0;
			$dl_opts=array(CURLOPT_FILE=>$fp);
		}
	}
	
	if($creds!="")
	{
	if($lookup!=0)
	{
		if(substr($url,0,4)=="http")
		{
	  	 $lookup_opts[CURLOPT_HTTPAUTH]=CURLAUTH_ANY;
	  	 $lookup_opts[CURLOPT_UNRESTRICTED_AUTH]=true;
		}
	   $lookup_opts[CURLOPT_USERPWD]="$creds";
	}
	if(substr($url,0,4)=="http")
	{
		$dl_opts[CURLOPT_HTTPAUTH]=CURLAUTH_ANY;
	  	$dl_opts[CURLOPT_UNRESTRICTED_AUTH]=true;
	}
	$dl_opts[CURLOPT_USERPWD]="$creds";
	}
	
	if($lookup)
	{	
		//lookup , using HEAD request
		$ok=curl_setopt_array($ch,$lookup_opts);
		$this->log("Headers", "startup");
		$res=curl_exec($ch);
		if($res!==false)
		{
		$this->log("Done", "startup");
			$lm=curl_getinfo($ch);
			if(curl_getinfo($ch,CURLINFO_HTTP_CODE)!=200)
			{
				$resp = explode("\n\r\n", $res);
				$this->log("http header:<pre>".$resp[0]."</pre>","error");
				throw new Exception("Cannot fetch $url");
				
			}
		}
		else
		{
			$this->log("Err: ".curl_error($ch), "startup");
			throw Exception("Cannot fetch $url");
		}

	}
	
	$res=array("should_dl"=>true,"reason"=>"");

	if($res["should_dl"])
	{
	    //clear url options
		$ok=curl_setopt_array($ch, array());
		
		//Download the file , force expect to nothing to avoid buffer save problem
	    curl_setopt_array($ch,$dl_opts);
		$this->log("Prenos", "startup");
		curl_exec($ch);
		if(curl_error($ch)!="")
		{
			$this->log(curl_error($ch),"error");
			throw new Exception("Cannot fetch $url");
		}
		else
		{
			$lm=curl_getinfo($ch);
			
			$this->log("XLS Fetched in ".$lm['total_time']. "secs","startup");
		}
		curl_close($ch);
		fclose($fp);
		
	}
	else
	{
	    curl_close($ch);
	    //bad file or bad hour, no download this time
		$this->log("No dowload , ".$res["reason"],"info");
	}
    //return the xls filename
	return $outname;
}

	function fetch($url, $cookies, $post=array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'User-Agent: Links (2.7; Linux 3.2.29 x86_64; GNU C 4.7.1; text)',
                'Connection: keep-alive',
                'Accept: text/html,application/xhtml+xls,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-us,en;q=0.5',
                'Accept-Encoding: gzip, deflate'
        ));
        curl_setopt($ch, CURLOPT_REFERER, 'http://reseller.apcom.eu/');

        if($post) {
                $d = '';
                foreach($post as $k=>$v)
                        $d .= urlencode($k).'='.urlencode($v).'&';
                $d = rtrim($d, '&');
                curl_setopt($ch, CURLOPT_POST, count($d));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
        }

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
	}

}
