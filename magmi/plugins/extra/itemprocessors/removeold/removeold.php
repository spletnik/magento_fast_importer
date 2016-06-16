<?php
require_once('magmi_utils.php');

class RemoveOldItemProcessor extends Magmi_ItemProcessor {
  private $start_time;
  private $mode;
  private $import_id;
  private $imgsourcedir;

  public function getPluginInfo() {
    return array(
      "name" => "Remove products",
      "author" => "Obid",
      "version" => "2.1",
      "url" => "http://spletnisistemi.si",
    );
  }

  // Parametri
  public function getPluginParams($params) {
    $pp=array();
    foreach($params as $k=>$v)
	if(preg_match("/^ROLD:.*$/",$k))
	  $pp[$k]=$v;
    return $pp;
  }


  public function initialize($params) {
    $this->start_time = date('Y-m-d H:i:s', time());
    $this->mode = $this->getParam('ROLD:mode', 'disable');
    $this->import_id = $this->getParam('ROLD:import_id', false);
    $magdir=Magmi_Config::getInstance()->getMagentoDir();
    $this->imgsourcedir=realpath($magdir.'/media/catalog/product/');
  }

  public function afterImport() {
    if(!$this->import_id) {
      $this->log("Unknown import ID\n", 'error');
      return true;
    }

    // Tables
    if($this->mode == 'disable') {
      $this->log("Disabling old products.", 'startup');
      $this->disable_products();
    } else if($this->mode == 'remove') {
      $this->log("Removing old products.", 'startup');
      $this->remove_products();
    }

    $cce=$this->tablename("catalog_category_entity");
    $sql="UPDATE  $cce as cce
          LEFT JOIN 
               (SELECT s1.entity_id as cid, COALESCE( COUNT( s2.entity_id ) , 0 ) AS cnt
                       FROM $cce AS s1
                       LEFT JOIN $cce AS s2 ON s2.parent_id = s1.entity_id
               GROUP BY s1.entity_id) as sq ON sq.cid=cce.entity_id
               SET cce.children_count=sq.cnt";
    $this->update($sql);

    return true;
  }

  private function disable_products() {
    $g_table = $this->tablename('catalog_product_entity_media_gallery');
    $p_table = $this->tablename('catalog_product_entity');
    $i_table = $this->tablename('catalog_product_entity_int');

    $import = $this->getAttrInfo('import_id');
    $attribute_id = $import['attribute_id'];

    $status = $this->getAttrInfo('status');

    // Disable products
    $sql = 'SELECT p.entity_id FROM '.$p_table.' p LEFT JOIN '.$i_table.' i ON p.entity_id=i.entity_id WHERE i.attribute_id=? AND i.value=? AND updated_at < ?';
    $products = $this->selectAll($sql, array($attribute_id, $this->import_id, $this->start_time));
    $this->log("deleted - ".count($products), 'info');

    $sql = 'INSERT INTO '.$i_table.' (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, ?, 0, ?, 2) ON DUPLICATE KEY UPDATE value=2';      
    foreach($products as $product)
      $this->insert($sql, array($status['attribute_id'], $product['entity_id']));
  }

  private function remove_products() {
    $g_table = $this->tablename('catalog_product_entity_media_gallery');
    $p_table = $this->tablename('catalog_product_entity');
    $i_table = $this->tablename('catalog_product_entity_int');

    $import = $this->getAttrInfo('import_id');
    $attribute_id = $import['attribute_id'];

    // Remove images
    $sql = 'SELECT g.value FROM '.$p_table.' p LEFT JOIN '.$i_table.' i ON p.entity_id=i.entity_id RIGHT JOIN '.$g_table.' g ON g.entity_id=p.entity_id WHERE i.attribute_id=? AND i.value=? AND updated_at < ?';
    $images = $this->selectAll($sql, array($attribute_id, $this->import_id, $this->start_time));
    foreach($images as $image)
      unlink($this->imgsourcedir.'/'.$image['value']);

    // Remove products
    $sql = 'DELETE p FROM '.$p_table.' p LEFT JOIN '.$i_table.' i ON p.entity_id=i.entity_id WHERE i.attribute_id=? AND i.value=? AND updated_at < ?';
    $this->delete($sql, array($attribute_id, $this->import_id, $this->start_time));
  }
}
