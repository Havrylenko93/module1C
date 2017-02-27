<?php
class ModelModuleImport1C extends Model {
   
    public function get_files_ids(){
        $query = $this->db->query("SELECT DISTINCT `fileID` FROM `filesIds`");
        return $query->rows;
    }

    // Добавляем id файла
    public function insert_file_id($fileID){
        $this->db->query("INSERT INTO `filesIds` SET `fileID`='".$fileID."'");
    }

    // Получение всех записей таблицы oc_optionID_to_optionID_1C (id`шники opencart и 1C)
    public function get_all_ids(){
        $query = $this->db->query("SELECT DISTINCT * FROM `".DB_PREFIX ."optionID_to_optionID_1C`");
        return $query->rows;
    }
 
    // Получить имена опций и id опций
    public function get_options_names(){
        $query = $this->db->query("SELECT DISTINCT * FROM `".DB_PREFIX ."option_description`");
        return $query->rows;
    }
    
    // Получить имена опций и id опций из ocfilter
    public function get_options_names_from_ocfilter(){
        $query = $this->db->query("SELECT DISTINCT * FROM `".DB_PREFIX ."ocfilter_option_description`");
        return $query->rows;
    }

    // 
    public function get_options_values_names_from_ocfilter(){
        $query = $this->db->query("SELECT DISTINCT * FROM `".DB_PREFIX ."ocfilter_option_value_description`");
        return $query->rows;
    }
    
    // Добавление записей в таблицу связанных id. Табл. oc_optionID_to_optionID_1C
    public function insert_related_ids($insert_values){
        $query = $this->db->query("INSERT INTO ".DB_PREFIX ."optionID_to_optionID_1C(oc_option_id, oc_filter_option_id, 1C_option_id) VALUES ".$insert_values);
    }

    // Добавление новой опции и её описания
    public function insert_option($option_name){
    	$this->db->query("INSERT INTO `" . DB_PREFIX . "option` SET type = 'select', sort_order = '1'");
	$option_id = $this->db->getLastId();
        $this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "',  name = '" . $option_name . "', `language_id`='1'");
        return $option_id;
    }
    
    // Добавление новой опции и её описания в oc_ocfilter_option
    public function insert_option_ocfilter($option_name, $translit_option_name){
    	$this->db->query("INSERT INTO `" . DB_PREFIX . "ocfilter_option` SET type = 'checkbox', keyword = '".$translit_option_name."'");
        $option_id = $this->db->getLastId();
        $this->db->query("INSERT INTO ".DB_PREFIX."ocfilter_option_description SET option_id = '" .$option_id . "',  name = '" . $option_name . "', `language_id`='1'");
        $this->db->query("INSERT INTO ".DB_PREFIX."ocfilter_option_to_store SET option_id = '" .$option_id . "', `store_id`='0'");
        $this->db->query("INSERT INTO ".DB_PREFIX."ocfilter_option_to_category SET option_id = '" .$option_id . "', `category_id`='1'");
        
        return $option_id;

    }
    
    
    
    
    
    
    //
    public function insert_option_value_ocfilter($translit_option_name, $option_id, $option_value_name){
    	$this->db->query("INSERT INTO `" . DB_PREFIX . "ocfilter_option_value` SET option_id = '".$option_id."', keyword = '".$translit_option_name."', sort_order='0'");
        $option_value_id = $this->db->getLastId();
        
        $this->db->query("INSERT INTO ".DB_PREFIX."ocfilter_option_value_description SET value_id = '" .$option_value_id."',  option_id = '" .$option_id. "', `language_id`='1', name = '".$option_value_name."'");

    }
    
    
    
    
    
    
    
    
    

    // Получение всех записей таблицы oc_optionvalueID_to_optionvalueID_1C 
    // (id`шники значений своств opencart и 1C)
    public function get_all_ids_options_values(){
        $query = $this->db->query("SELECT DISTINCT * FROM `".DB_PREFIX ."optionvalueID_to_optionvalueID_1C`");
        return $query->rows;
    }

    // Получить имена значений опций и id значений опций
    public function get_options_values_names(){
        $query = $this->db->query("SELECT DISTINCT * FROM `".DB_PREFIX ."option_value_description`");
        return $query->rows;
    }

    public function get_option_id_from_1C_id($oneCid){
        $query = $this->db->query("SELECT DISTINCT * FROM `".DB_PREFIX ."optionID_to_optionID_1C` WHERE `1C_option_id` = '".$oneCid."'");
        return $query->rows;
    }
    
    public function get_option_value_id_from_1C_id($oneCid){
        $query = $this->db->query("SELECT DISTINCT * FROM `".DB_PREFIX ."optionID_to_optionID_1C` WHERE `1C_option_id` = '".$oneCid."'");
        return $query->rows;
    }
    
    // Добавление нового значения опции и её описания
    public function insert_option_value($option_value_name, $option_id ){
    	$this->db->query("INSERT INTO `" . DB_PREFIX . "option_value` SET `option_id`='".$option_id."', sort_order = '1'");
	$option_value_id = $this->db->getLastId();
        $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "',  name = '" . $option_value_name . "', `option_id`='".$option_id."', `language_id`='1'");                                           
        return $option_value_id;
    } 

    // Добавление записей в таблицу связанных id. Табл. oc_optionID_to_optionID_1C
    public function insert_related_ids_options_values($insert_values){
        $query = $this->db->query("INSERT INTO ".DB_PREFIX ."optionvalueID_to_optionvalueID_1C(oc_optionvalue_id, ocfilter_option_value_id, 1C_optionvalue_id) VALUES ".$insert_values);
    }

    // Получение всех записей таблицы oc_attributeID_to_attributeID_1C (id`шники opencart и 1C)
    public function get_all_attributes_ids(){
        $sql = "SELECT DISTINCT * FROM `".DB_PREFIX ."attributeID_to_attributeID_1C`";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    // Получить имена атрибутов и id атрибутов
    public function get_attributes_names(){
        $sql = "SELECT DISTINCT * FROM `".DB_PREFIX ."attribute_description`";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    // Добавление нового атрибута и его описания
    public function insert_attribute($attribute_name){
    	$this->db->query("INSERT INTO `" . DB_PREFIX . "attribute` SET sort_order = '1'");
	$attribute_id = $this->db->getLastId();
        $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "',  name = '" . $attribute_name . "'");
        return $attribute_id;
    }

    // Добавление записей в таблицу связанных id. Табл. oc_attributeID_to_attributeID_1C
    public function insert_related_ids_attributes_values($insert_values){
        $sql = "INSERT INTO ".DB_PREFIX ."attributeID_to_attributeID_1C(oc_attribute_id, 1C_attribute_id) VALUES ".$insert_values;
        $this->db->query($sql);
    }

    // Получение всех записей таблицы oc_categoryID_to_categoryID_1C (id`шники opencart и 1C)
    public function get_all_categories_ids(){
        $sql = "SELECT DISTINCT * FROM `".DB_PREFIX ."categoryID_to_categoryID_1C`";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    // Получить имена категорий и id категорий
    public function get_categories_names(){
        $sql = "SELECT DISTINCT * FROM `".DB_PREFIX ."category_description`";
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    
    
    
    // Добавление новой категории и её описания
    public function insert_category($category_name, $parent_id = 0){
    	$this->db->query("INSERT INTO `" . DB_PREFIX . "category` SET sort_order = '0', parent_id='".$parent_id."', image='', status = '1', date_added = NOW(), date_modified = NOW()");
	$category_id = $this->db->getLastId();
        $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '0'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "',  name = '" .$category_name. "', language_id='1'");
        
        return $category_id;
    }
    
    // Добавление записей в таблицу связанных id. Табл. oc_categoryID_to_categoryID_1C
    public function insert_related_ids_categories_values($insert_values){
        $sql = "INSERT INTO ".DB_PREFIX ."categoryID_to_categoryID_1C(oc_category_id, 1C_category_id) VALUES ".$insert_values;
        $this->db->query($sql);
    }
    
    // Получение всех записей таблицы oc_productID_to_productID_1C (id`шники opencart и 1C)
    public function get_all_products_ids(){
        $sql = "SELECT DISTINCT * FROM `".DB_PREFIX ."productID_to_productID_1C`";
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    // Получить имена товаров и id товаров
    public function get_products_names(){
        $sql = "SELECT DISTINCT * FROM `".DB_PREFIX ."product_description`";
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    // Добавление нового товара
    public function insert_product($product_name, $quantity = 0, $sku, $image_product, $product_price, $attribute_country, $product_description){
    	$this->db->query("INSERT INTO `" . DB_PREFIX . "product` SET price='".$product_price."', image='".$image_product."', model='".$sku."', quantity='".$quantity."', sort_order = '1',  date_added = NOW(), date_modified = NOW(), status='1'");
	$product_id = $this->db->getLastId();
        //> добавляем данные в oc_product_attribute
        // 16 - id атрибута страна_производитель
        $this->db->query("INSERT INTO `" . DB_PREFIX . "product_attribute` SET `product_id`='".$product_id."', attribute_id='16', language_id='1', text='".$attribute_country."' ");
        //<//
        $this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_store` SET `product_id`='".$product_id."', store_id='0'");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "product_description` SET `product_id`='".$product_id."', language_id='1', name='".$product_name."', meta_title='".$product_name."', description='".$product_description."' ");
        return $product_id;
    }
    
    // Добавление записей в таблицу связанных id. Табл. oc_productID_to_productID_1C
    public function insert_related_ids_products_values($insert_values){
        $sql = "INSERT INTO ".DB_PREFIX."productID_to_productID_1C(oc_product_id, 1C_product_id) VALUES ".$insert_values;
        $this->db->query($sql);
    }

    public function get_attribute_id($attrName){
        $sql = "SELECT attribute_id FROM ".DB_PREFIX."attribute_description WHERE name='".$attrName."'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function insert_product_attribute($product_id, $attribute_id, $text=''){
        $sql = "INSERT INTO ".DB_PREFIX."product_attribute SET product_id='".$product_id."', attribute_id='".$attribute_id."', language_id='1', text='".$text."'";
        $query = $this->db->query($sql);
    }

    public function insert_product_image($insert_values){
        $sql = "INSERT INTO ".DB_PREFIX ."product_image(image, product_id, sort_order) VALUES ".$insert_values;
        $this->db->query($sql);
    }
    
    // Получить id опции опенкарт из id 1C
    public function get_OC_option_id($oneCoptionId){
        $sql = "SELECT DISTINCT * FROM `".DB_PREFIX ."optionID_to_optionID_1C` WHERE `1C_option_id`='{$oneCoptionId}'";
        $query = $this->db->query($sql);
        return $query->row;
    }
    
    public function insert_product_option($product_id, $option_id){
    	$this->db->query("INSERT INTO `" . DB_PREFIX . "product_option` SET product_id = '".$product_id."', option_id='".$option_id."'");
	$product_option_id = $this->db->getLastId();
        return $product_option_id;
    }
    
    public function get_OC_option_value_id($oneCoptionvalueId){
        $sql = "SELECT DISTINCT * FROM `".DB_PREFIX ."optionvalueID_to_optionvalueID_1C` WHERE `1C_optionvalue_id`='{$oneCoptionvalueId}'";
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    public function insert_product_option_value($product_optionID, $product_id, $OCoptionID, $OCoptionvalueID){
        $sql = "INSERT INTO ".DB_PREFIX."product_option_value SET product_option_id='".$product_optionID."', product_id='".$product_id."', option_id='".$OCoptionID."', option_value_id='".$OCoptionvalueID."'";
        $this->db->query($sql);
    }
    
    public function get_OC_id_category($oneCcategoryId){
        $sql = "SELECT DISTINCT * FROM `".DB_PREFIX ."categoryID_to_categoryID_1C` WHERE `1C_category_id`='{$oneCcategoryId}'";
        $query = $this->db->query($sql);
        return $query->rows;
    }
    public function get_OC_parent_id_category($cat_id){
        $sql = "SELECT DISTINCT * FROM `".DB_PREFIX ."category` WHERE `category_id`='{$cat_id}'";
        $query = $this->db->query($sql);
        return $query->rows;
    }
    public function insert_product_to_category($product_id, $category_id, $parent_id){
        $sql = "INSERT INTO ".DB_PREFIX."product_to_category SET product_id='".$product_id."', category_id='".$category_id."', main_category='".$parent_id."'";
        $this->db->query($sql);
    }
    public function get_oc_product_id($oneC_prod_id){
        $sql = "SELECT DISTINCT `oc_product_id` FROM `".DB_PREFIX ."productID_to_productID_1C` WHERE `1C_product_id`='".$oneC_prod_id."'";
        $query = $this->db->query($sql);
        return $query->row;
    }
    public function get_oc_option_id_for_filter($oneC_option_id){
        $sql = "SELECT DISTINCT `oc_option_id` FROM `".DB_PREFIX ."optionID_to_optionID_1C` WHERE `1C_option_id`='".$oneC_option_id."'";
        $query = $this->db->query($sql);
        return $query->row;
    }
    
    public function get_oc_option_value_id_for_filter($oneC_option_value_id){
        $sql = "SELECT DISTINCT `oc_optionvalue_id` FROM `".DB_PREFIX ."optionvalueID_to_optionvalueID_1C` WHERE `1C_optionvalue_id`='".$oneC_option_value_id."'";
        $query = $this->db->query($sql);
        return $query->row;
    }
    
    // заполняем tdo_option_value
    public function insert_to_tdo_option_value($oc_product_id, $parent_option_id, $child_option_id, $parent_option_value_id, $child_option_value_id, $balance){
        $sql = "INSERT INTO ".DB_PREFIX."tdo_option_value SET `product_id`='".$oc_product_id."', `parent_option_id`='".$parent_option_id."', `child_option_id`='".$child_option_id."', `parent_option_value_id`='".$parent_option_value_id."', `child_option_value_id`='".$child_option_value_id."'";
        $this->db->query($sql);
        // заполняем tdo_data
        $tdo_id = $this->db->getLastId();
        $sql2 = "INSERT INTO ".DB_PREFIX."tdo_data SET `tdo_id` = '".$tdo_id."', `product_id`='".$oc_product_id."', `quantity`='".$balance."', `subtract`='1', `price`='0', `special`='0'";
        $this->db->query($sql2);
        
    }
}


