<?php
require_once('magmi_utils.php');
class Magmi_XMLDataSource extends Magmi_Datasource
{
	protected $_xml;
	protected $children = array();
	protected $index = 0;
	protected $size = 0;
	
	public function initialize($params)
	{
		$this->_xml = null;
	}
	
	public function getAbsPath($path)
	{
		return abspath($path,$this->getScanDir());
	}
	
	public function getScanDir($resolve=true)
	{
		$scandir=$this->getParam("XML:basedir","var/import");
		if(!isabspath($scandir))
		{
			$scandir=abspath($scandir,Magmi_Config::getInstance()->getMagentoDir(),$resolve);
		}
		return $scandir;	
	}
	
	public function getXMLList()
	{
		$scandir=$this->getScanDir();
		$files=glob("$scandir/*.xml");
		return $files;
	}
	
	public function getPluginParams($params)
	{
		$pp=array();
		foreach($params as $k=>$v)
		{
			if(preg_match("/^XML:.*$/",$k))
			{
				$pp[$k]=$v;
			}
		}
		return $pp;
	}
	
	public function getPluginInfo()
	{
		return array("name"=>"XML Datasource",
					 "author"=>"Matija",
					 "version"=>"2.0");
	}
	
	public function getRecordsCount()
	{
		return $this->size = sizeof($this->_xml->children());
	}
	
	public function getAttributeList()
	{
	}

// import	
	public function beforeImport()
	{
		$file = '';
		if($this->getParam("XML:importmode","local")=="remote") 
			$file = $this->getParam("XML:remoteurl","");
		else
			$file = $this->getParam("XML:filename", "");
		if(!($this->_xml = simplexml_load_file($file))) 
			$this->log("Napaka pri odpiranju xml-ja: ".$this->getParam('XML:filename'), "error");

	}
	
	
	public function afterImport() {
	}
	
	public function startImport() {
		$children = $this->_xml->children();
		while(sizeof($children) && $children[0]->getName() !== $this->getParam('XML:Product'))
			$children = $children[0]->children();
		if(!(sizeof($children) && $children[0]->getName() === $this->getParam('XML:Product'))) {
			die("XML parsing error");
		}
		$this->children = $children;
	}

	protected function toarr($n) {
	  $map = array();
	  $this->getXmlMapNode($n, array(), $map);
	  return $map;
	}

	private function getXmlMapNode($xml, $path, &$map) {
	  // attributes
	  foreach($xml->attributes() as $attr => $v) {
	    $p = "#$attr";
	    if($path)
	      $p = '@'.join('.@', $path).'.'.$p;
	    $map[$p] = (string)trim($v);
	  }


	  if(sizeof($xml->children())) {
	    foreach($xml->children() as $child) {
	      // recursion
	      array_push($path, $child->getName());
	      $this->getXmlMapNode($child, $path, $map);
	      array_pop($path);
	    }
	  } else {
	    $p = join('.@', $path);
	    $map['@'.$p] = (string)trim($xml);
	  }
	  return $map;
	}


	public function getColumnNames($prescan=false)
	{
		$c = $this->children[0];
		$keys = array_keys($this->toarr($c));
		return $keys;
	}
	
	public function endImport() {
	}

	
	public function getNextRecord() {
		if($this->index == $this->size)
			return false;
		return $this->toarr($this->children[$this->index++]);
	}
}
