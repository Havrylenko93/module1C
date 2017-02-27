<?php
class ControllerModuleImport1C extends Controller {
    public function index(){
        //> Общая информация для всех методов
        header('Content-Type: text/html; charset=utf-8');
        $this->load->model('module'.DIRECTORY_SEPARATOR.'Import1C');
        $dir    = $this->request->server['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'import1C'.DIRECTORY_SEPARATOR.'webdata';
        $files1 = scandir($dir);
        
        // Как бы не называлась папка в webdata - получаем путь
        foreach($files1 as $file){
            
            if(!is_dir($file)){
               $pwd = $this->request->server['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'import1C'.DIRECTORY_SEPARATOR.'webdata'.DIRECTORY_SEPARATOR.$file;
            }
            
        }
        //<//
        
        $this->move_image();
        $this->options($pwd);
        $this->options_values($pwd);
        $this->attributes($pwd);
        $this->categories($pwd);
        $this->product($pwd);
        $this->del_files($pwd);
         foreach($files1 as $file){
            
            if(!is_dir($file)){
               $dir = $this->request->server['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'import1C'.DIRECTORY_SEPARATOR.'webdata'.DIRECTORY_SEPARATOR.$file;
               @rmdir($dir); 
            }
            
        }
        
        
        
    }
    
    public function move_image(){
        echo "<b>move_image()</b> method is working<br/>";
        
        $path_image = $this->request->server['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'import1C'.DIRECTORY_SEPARATOR.'image1C'.DIRECTORY_SEPARATOR;
        $image_arr  = scandir($path_image);
        
        if(count($image_arr)>2){ // если есть файлы в данной папке
            
            foreach ($image_arr  as $image){
                if(!is_dir($image)){
                    $image_array[] = $image;
                }
            }
            
            foreach($image_array as $image_name){
                $old = $path_image.$image_name;
                $new = $this->request->server['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."image".DIRECTORY_SEPARATOR."from1C".DIRECTORY_SEPARATOR.$image_name;
                
                if(@rename($old, $new)){
                    echo "image was moved<br/>";
                }
                
            }
            
        }else{
            echo "there are no images<br/>";
        }
        echo "_________________________________________________________________<br/>";
    }
    
    public function options($pwd){
        echo "<b>options</b> method is working<br/>";
        //> работаем с папкой properties
        $pwd_properties  = scandir($pwd.DIRECTORY_SEPARATOR."properties");
        $dirs_properties = array(); // массив папок в папке properties
        
        foreach ($pwd_properties as $prop){
            
            if(!is_dir($prop)){
                $dirs_properties[] = $prop;
            }
            
        }
        
        $all_files_id = $this->model_module_Import1C->get_files_ids(); 
        
        $all_files_ids = array();
        
        foreach ($all_files_id as $file_id){
            $all_files_ids[] = $file_id['fileID'];
        }
        
        $allIds = $this->model_module_Import1C->get_all_ids(); // id из таблицы связей id`шников
        $oneCid = array();
        foreach ($allIds as $all){
            $oneCid[] = $all['1C_option_id'];
        }
        $alloptions_names  = array();
        $all_options_names = array();
        $alloptions_names = $this->model_module_Import1C->get_options_names();
        
        foreach ($alloptions_names as $all){
            $all_options_names[] = mb_strtoupper($all['name'], 'UTF-8');
        }
        
        $ocfilter_alloptions_names  = array();
        $ocfilter_all_options_names = array();
        $ocfilter_alloptions_names = $this->model_module_Import1C->get_options_names_from_ocfilter();
        
        foreach ($ocfilter_alloptions_names as $all){
            $ocfilter_all_options_names[] = mb_strtoupper($all['name'], 'UTF-8');
        }
        
        foreach($dirs_properties as $dir_propertie){ // работаем с конкретной папкой в properties
            //$path = $pwd."/properties/".$dir_propertie;
            $path     = $pwd."/properties/";
            chdir($path);
            $filelist = glob("import*.xml"); // получаем массив xml`ек import
            
            foreach ($filelist as $fileoffer){
                $fileID = explode('___',$fileoffer);
                $fileID = substr($fileID[1], 0, -4);
                
                $res['isname']='';
                $res['notname']='';
                
                if(empty($all_files_ids)||(!in_array($fileID, $all_files_ids))){ // если id нет в базе
                    // пишем id файла в базу
                   /* $this->model_module_Import1C->insert_file_id($fileID);*/
                    $xml = simplexml_load_file($fileoffer);
                    
                    foreach($xml->Классификатор->Свойства->children() as $option){
                        
                        if($option->ВидСвойства=='Опция'){ // если это опция, а не атрибут
                            
                            if(!in_array($option->Ид, $oneCid)||empty($oneCid)){ // отсеиваем те id, которые уже есть в базе
                                
                                    if(in_array($option->Наименование, $all_options_names)){                          
                                        $res['isname']['ids'][]   = (string)$option->Ид; // $res['ids'] - массив с айишниками свойтв
                                        $res['isname']['names'][] = (string)$option->Наименование; 
                                        
                                        foreach ($alloptions_names as $item){ 
                                            
                                            if(mb_strtoupper($item['name'], 'UTF-8')==$option->Наименование){
                                                $res['isname']['oc_option_id'][] = $item['option_id'];
                                            }
                                                
                                        }
                                            
                                    }else{ // если в базе нет данной опции
                                        $res['notname']['ids'][]          = $option->Ид;
                                        $res['notname']['names'][]        = $option->Наименование; 
                                        $res['notname']['oc_option_id'][] = $this->model_module_Import1C->insert_option($option->Наименование);
                                        
                                        //> Работаем с oc_ocfilter
                                        
                                        if(in_array($option->Наименование, $ocfilter_all_options_names)){ 
                                            
                                        }else{
                                            $res['notname']['ocfilter_option_id'][] = $this->model_module_Import1C->insert_option_ocfilter($option->Наименование, $this->translit($option->Наименование));
                                        }
                                    
                                        //<//
                                    }
                                    
                                    
                                    
                                    
                            }
                        
                    }
                    }
                    
                }else{
                    continue;
                }
                //> Опции, которые есть в opencart и есть в табл. опций;  добавляем в таблицу связей id`шников
                if(!empty($res['isname'])){
                    $myres     = array();
                    $query_str = '';
                    $myres[]   = $res['isname'];
                    $count=count($res['isname']['oc_option_id']);

                    foreach ($myres as $key=>$item){
                       for($i=0; $i<$count; $i++){
                            $query_str.= "('{$item['oc_option_id'][$i]}', '{$item['ocfilter_option_id'][$i]}', '{$item['ids'][$i]}'),  ";
                       }
                    }

                    $insert_values = substr($query_str, 0, strlen($query_str) - 2) . ';';

                    $this->model_module_Import1C->insert_related_ids($insert_values); 
                }
                //<//
        
                //> Опции, которых нет в opencart
                if(!empty($res['notname'])){
                    $myres      = array();
                    $query_str  = '';
                    $myres[]    = $res['notname'];
                    $count=count($res['notname']['oc_option_id']);

                    foreach ($myres as $key=>$item){

                       for($i=0; $i<$count; $i++){
                            $query_str.= "('{$item['oc_option_id'][$i]}', '{$item['ocfilter_option_id'][$i]}', '{$item['ids'][$i]}'), ";
                       }

                    }

                    $insert_values = substr($query_str, 0, strlen($query_str) - 2) . ';';

                    $this->model_module_Import1C->insert_related_ids($insert_values); 
                }
                //<//
            }
            
        }  
        
        /*
        echo "все имена опций_______________________________";
        echo "<pre>"; 
        var_dump($all_options_names);
        echo "</pre>_______________________________________________";
        echo "опции которые есть в базе_______________________________";
        echo "<pre>"; 
        var_dump($res['isname']['names']);
        echo "</pre>_______________________________________________";
        echo "опции которых нет в базе_______________________________";
        echo "<pre>"; 
        var_dump($res['notname']);
        echo "</pre>_______________________________________________";
        */
        //<
       echo "_________________________________________________________________<br/>";
    }
    
    public function options_values($pwd){
        echo "<b>optionsValues</b> method is working<br/>";
        
        //> работаем с папкой properties
        $pwd_properties = scandir($pwd.DIRECTORY_SEPARATOR."properties");
        $dirs_properties = array(); // массив папок в папке properties
        
        foreach ($pwd_properties as $prop){
            
            if(!is_dir($prop)){
                $dirs_properties[] = $prop;
            }
            
        }
        
        $all_files_id = $this->model_module_Import1C->get_files_ids(); 
        
        $all_files_ids = array();
        
        foreach ($all_files_id as $file_id){
            $all_files_ids[] = $file_id['fileID'];
        }
        
        $allIds = $this->model_module_Import1C->get_all_ids_options_values(); // id из таблицы связей id`шников
        $oneCid = array();
        foreach ($allIds as $all){
            $oneCid[] = $all['1C_optionvalue_id'];
        }
        $alloptions_names = array();
        $all_options_names = array();
        $alloptions_names = $this->model_module_Import1C->get_options_values_names();
        
        $ocfilter_alloptions_values_names  = array();
        $ocfilter_all_options_values_names = array();
        $ocfilter_alloptions_values_names = $this->model_module_Import1C->get_options_values_names_from_ocfilter();
        
        foreach ($ocfilter_alloptions_values_names as $all){
            $ocfilter_all_options_values_names[] = mb_strtoupper($all['name'], 'UTF-8');
        }
        
        foreach ($alloptions_names as $all){
            $all_options_names[] = mb_strtoupper($all['name'], 'UTF-8');
        }
        
        foreach($dirs_properties as $dir_propertie){ // работаем с конкретной папкой в properties
            //$path = $pwd."/properties/".$dir_propertie;
            $path     = $pwd.DIRECTORY_SEPARATOR."properties".DIRECTORY_SEPARATOR;
            chdir($path);
            $filelist = glob("import*.xml"); // получаем массив xml`ек offers
            
            foreach ($filelist as $fileoffer){
                $fileID = explode('___',$fileoffer);
                $fileID = substr($fileID[1], 0, -4);
                
                $res['isname']='';
                $res['notname']='';
                
                if(empty($all_files_ids)||(!in_array($fileID, $all_files_ids))){ // если id нет в базе
                    // пишем id файла в базу
                    /*$this->model_module_Import1C->insert_file_id($fileID);*/
                    $xml = simplexml_load_file($fileoffer);
                    $i   = 0;

                    foreach($xml->Классификатор->Свойства->children() as $IneedOptionId){
                        $try = '';
                        $try = (string)$IneedOptionId->ВидСвойства;
                        if($try=='Опция'){
                        
                        $res1 = $this->model_module_Import1C->get_option_id_from_1C_id($IneedOptionId->Ид);
                        $my_oc_option_id = '';
                        
                        foreach ($res1 as $oc_option_id){
                            $my_oc_option_id = $oc_option_id['oc_option_id'];
                        }
                        
                        
                        //> получаем oc_filter_option_id
                        $res2 = $this->model_module_Import1C->get_option_value_id_from_1C_id($IneedOptionId->Ид);
                        $my_oc_option_value_id = '';
                        
                        foreach ($res2 as $oc_option_value_id){
                            $my_oc_option_value_id = $oc_option_value_id['oc_filter_option_id'];
                        }
                        //<//
                        
                        foreach($xml->Классификатор->Свойства->Свойство[$i]->ВариантыЗначений->children() as $option){
                        if($option->ВидСвойства!='Атрибут'){ // если это опция, а не атрибут
                                if(!in_array($option->ИдЗначения, $oneCid)){ // отсеиваем те id, которые уже есть в базе

                                    if(in_array($option->Значение, $all_options_names)){
                                        $res['isname']['ids'][]   = (string)$option->ИдЗначения; // $res['ids'] - массив с айишниками свойтв
                                        $res['isname']['names'][] = (string)$option->Значение; 

                                            foreach ($alloptions_names as $item){

                                                if(mb_strtoupper($item['name'], 'UTF-8')==$option->Значение){
                                                   $res['isname']['oc_option_id'][] = $item['option_value_id']; 
                                                }

                                            }

                                    }else{ // если в базе нет данной опции
                                        $res['notname']['ids'][]          = $option->ИдЗначения;
                                        $res['notname']['names'][]        = $option->Значение; 
                                        $res['notname']['oc_option_id'][] = $this->model_module_Import1C->insert_option_value($option->Значение, $my_oc_option_id);
                                        
                                        //> Работаем с oc_ocfilter
                                        
                                        if(in_array($option->Наименование, $ocfilter_all_options_values_names)){ 
                                            
                                        }else{
                                            // нужен id опции из oc_ocfilter_option
                                            $res['notname']['ocfilter_option_values_id'][] = $this->model_module_Import1C->insert_option_value_ocfilter($this->translit($option->Значение), $my_oc_option_value_id, $option->Значение);
                                        }
                                    
                                        //<//

                                    }    
                                }
                                
                        }
                        
                    }
                        
                        $i++;
                        
                    }}
                }else{
                    continue;
                }
                //> Опции, которые есть в opencart и есть в табл. опций;  добавляем в таблицу связей id`шников
                if(!empty($res['isname'])){
                    $myres      = array();
                    $query_str  = '';
                    $myres[]    = $res['isname'];
                    $count      =count($res['isname']['oc_option_id']);

                    foreach ($myres as $key=>$item){

                       for($i=0; $i<$count; $i++){
                            $query_str.= "('{$item['oc_option_id'][$i]}', '{$item['ocfilter_option_values_id'][$i]}', '{$item['ids'][$i]}'), ";
                       }

                    }

                    $insert_values = substr($query_str, 0, strlen($query_str) - 2) . ';';

                    $this->model_module_Import1C->insert_related_ids_options_values($insert_values);
                }
                //<//

                //> Опции, которых нет в opencart
                if(!empty($res['notname'])){
                    $myres     = array();
                    $query_str = '';
                    $myres[]   = $res['notname'];
                    $count     =count($res['notname']['oc_option_id']);

                    foreach ($myres as $key=>$item){

                       for($i=0; $i<$count; $i++){
                            $query_str.= "('{$item['oc_option_id'][$i]}', '{$item['ocfilter_option_values_id'][$i]}', '{$item['ids'][$i]}'), ";
                       }

                    }

                    $insert_values = substr($query_str, 0, strlen($query_str) - 2) . ';';

                    $this->model_module_Import1C->insert_related_ids_options_values($insert_values);
                } 
            }
        }
        
        
        //<//
        /*
        echo "<br/>все имена значений опций(из базы)_______________________________";
        echo "<pre>"; 
        var_dump($all_options_names);
        echo "</pre>_______________________________________________";
        echo "значения опций которые есть в базе_______________________________";
        echo "<pre>"; 
        var_dump($res['isname']['names']);
        echo "</pre>_______________________________________________";
        echo "значения опций которых нет в базе_______________________________";
        echo "<pre>"; 
        var_dump($res['notname']);
        echo "</pre>_______________________________________________";
        */
        echo "_________________________________________________________________<br/>";
    }
    
    public function attributes($pwd){
        echo "<b>attributes</b> method is working<br/>";
        
        //> работаем с папкой properties
        $pwd_properties  = scandir($pwd."/properties");
        $dirs_properties = array(); // массив папок в папке properties
        
        foreach ($pwd_properties as $prop){
            
            if(!is_dir($prop)){
                $dirs_properties[] = $prop;
            }
            
        }
        
        $allIds = $this->model_module_Import1C->get_all_attributes_ids(); // id из таблицы связей id`шников
        
        $oneCid = array();
        
        foreach ($allIds as $all){
            $oneCid[] = $all['1C_attribute_id'];
        }
        
        $alloptions_names = $this->model_module_Import1C->get_attributes_names();
        $all_options_names = array();
        
        foreach ($alloptions_names as $all){
            $all_options_names[] = $all['name'];
        }
        
        foreach($dirs_properties as $dir_propertie){ // работаем с конкретной папкой в properties
            //$path = $pwd."/properties/".$dir_propertie;
            $path = $pwd.DIRECTORY_SEPARATOR."properties".DIRECTORY_SEPARATOR;
            chdir($path);
            $filelist = glob("import*.xml"); // получаем массив xml`ек import
            
            foreach ($filelist as $fileoffer){
                $fileID = explode('___',$fileoffer);
                $fileID = substr($fileID[1], 0, -4);
                $res['isname']='';
                $res['notname']='';
                
                $all_files_id  = $this->model_module_Import1C->get_files_ids(); 
        
                $all_files_ids = array();
        
                foreach ($all_files_id as $file_id){
                    $all_files_ids[] = $file_id['fileID'];
                }
                
                if(empty($all_files_ids)||(!in_array($fileID, $all_files_ids))){ // если id нет в базе
                    // пишем id файла в базу
                    $this->model_module_Import1C->insert_file_id($fileID);
                    $xml = simplexml_load_file($fileoffer);

                    foreach($xml->Классификатор->Свойства->children() as $option){

                        if($option->ВидСвойства=='Атрибут'){ // если это атрибут

                            if(!in_array($option->Ид, $oneCid)||empty($oneCid)){ // отсеиваем те id, которые уже есть в базе

                                    if(in_array($option->Наименование, $all_options_names)){                     

                                        $res['isname']['ids'][]   = (string)$option->Ид; // $res['ids'] - массив с айишниками свойтв
                                        $res['isname']['names'][] = (string)$option->Наименование; 

                                        foreach ($alloptions_names as $item){ 

                                                if($item['name'] == $option->Наименование){
                                                   $res['isname']['oc_attribute_id'][] = $item['attribute_id'];
                                                }

                                            }

                                    }else{ // если в базе нет данного атрибута
                                        $res['notname']['ids'][]             = $option->Ид;
                                        $res['notname']['names'][]           = $option->Наименование; 
                                        $res['notname']['oc_attribute_id'][] = $this->model_module_Import1C->insert_attribute($option->Наименование);
                                    }
                            }
     
                    }
                    }
                    // отпарсили => удаляем файл
                    /*$unlink_file = $path.$fileoffer;

                    if(unlink($unlink_file)){
                        echo "Файл в папке properties удален<br/>";
                    }*/

                }else{
                    continue;
                }
                //> Атрибуты, которые есть в opencart и есть в табл. атрибутов;  добавляем в таблицу связей id`шников
                if(!empty($res['isname'])){
                    $myres      = array();
                    $query_str  = '';
                    $myres[]    = $res['isname'];
                    $count      =count($res['isname']['oc_attribute_id']);

                    foreach ($myres as $key=>$item){

                       for($i=0; $i<$count; $i++){
                            $query_str.= "('{$item['oc_attribute_id'][$i]}', '{$item['ids'][$i]}'), ";
                       }

                    }

                    $insert_values = substr($query_str, 0, strlen($query_str) - 2) . ';';

                    $this->model_module_Import1C->insert_related_ids_attributes_values($insert_values);
                }
                //<//
                //> Атрибуты, которых нет в opencart
                if(!empty($res['notname'])){
                    $myres      = array();
                    $query_str  = '';
                    $myres[]    = $res['notname'];
                    $count      = count($res['notname']['oc_attribute_id']);

                    foreach ($myres as $key=>$item){

                       for($i=0; $i<$count; $i++){
                            $query_str.= "('{$item['oc_attribute_id'][$i]}', '{$item['ids'][$i]}'), ";
                       }

                    }

                    $insert_values = substr($query_str, 0, strlen($query_str) - 2) . ';';

                    $this->model_module_Import1C->insert_related_ids_attributes_values($insert_values);
                }
                //<//
                echo "_________________________________________________________________<br/>";
                
                
                
            }
        }  
        
        
    }
    
    public function categories($pwd){
        echo "<b>categories</b> method is working<br/>";
        
        //> работаем с папкой properties
        $pwd_properties  = scandir($pwd.DIRECTORY_SEPARATOR);
        $dirs_properties = array(); // массив папок в папке properties
        
        foreach ($pwd_properties as $prop){
            
            if(!is_dir($prop)){
                $dirs_properties[] = $prop;
            }
            
        }

        $allIds = $this->model_module_Import1C->get_all_categories_ids(); // id из таблицы связей id`шников
        
        $oneCid = array();
        foreach ($allIds as $all){
            $oneCid[] = $all['1C_category_id'];
        }
        
        $alloptions_names = $this->model_module_Import1C->get_categories_names();
        $all_options_names = array();
        foreach ($alloptions_names as $all){
            $all_options_names[] = $all['name'];
        }
        
        foreach($dirs_properties as $dir_propertie){ // работаем с конкретной папкой
            
            $path = $pwd.DIRECTORY_SEPARATOR;
            chdir($path);
            $filelist = glob("import*.xml"); // получаем массив xml`ек import
            
            foreach ($filelist as $fileoffer){
                $fileID = explode('___',$fileoffer);
                $fileID = substr($fileID[1], 0, -4);
                $res['isname']='';
                $res['notname']='';
              
                $all_files_id = $this->model_module_Import1C->get_files_ids(); 
        
                $all_files_ids = array();
        
                foreach ($all_files_id as $file_id){
                    $all_files_ids[] = $file_id['fileID'];
                }
                
                if(empty($all_files_ids)||(!in_array($fileID, $all_files_ids))){ // если id нет в базе
                    // пишем id файла в базу
                    $this->model_module_Import1C->insert_file_id($fileID);
                    
                    $xml = simplexml_load_file($fileoffer);
                    
                    foreach($xml->Классификатор->Группы->children() as $option){
                              
                            if(!in_array($option->Ид, $oneCid)||empty($oneCid)){ // отсеиваем те id, которые уже есть в базе
                                
                                    if(in_array($option->Наименование, $all_options_names)){                          
                                        $res['isname']['ids'][]   = (string)$option->Ид; // $res['ids'] - массив с айишниками свойтв
                                        $res['isname']['names'][] = (string)$option->Наименование; 
                                        $parentId                 = 0;
                                        
                                        foreach ($alloptions_names as $item){ 
                                            
                                                if($item['name']==$option->Наименование){
                                                   $res['isname']['oc_category_id'][] = $item['category_id'];
                                                   $parentId                          = $item['category_id'];
                                                }
                                                
                                        }
                                        
                                        //> Если уже есть данная категория - проверяем её на наличие дочерних и пишем дочерние
                                           foreach($option->Группы->children() as $subcategory){
                                               // проверяем наличие подкатегории с xml`ки в табл. oc_category_description, если нет - пишем
                                               
                                               if(!in_array($subcategory->Наименование, $all_options_names)){
                                                    $res['notname']['ids'][]            = $subcategory->Ид;
                                                    $res['notname']['names'][]          = $subcategory->Наименование; 
                                                    $res['notname']['oc_category_id'][] = $this->model_module_Import1C->insert_category($subcategory->Наименование, $parentId);     
                                               }
                                               
                                           }
                                        //<//
                                    }else{ 
                                        $parentId                  = 0;
                                        $res['notname']['ids'][]   = $option->Ид;
                                        $res['notname']['names'][] = $option->Наименование; 
                                        $parentId = $this->model_module_Import1C->insert_category($option->Наименование);
                                        $res['notname']['oc_category_id'][] = $parentId;
                                        //> Если нет такой категории - проверяем наличие дочерних
                                        
                                        if(isset($option->Группы)){
                                            
                                            foreach($option->Группы->children() as $subcategory){
                                               // проверяем наличие подкатегории с xml`ки в табл. oc_category_description, если нет - пишем
                                                
                                                if(!in_array($subcategory->Наименование, $all_options_names)){
                                                    $res['notname']['ids'][]            = $subcategory->Ид;
                                                    $res['notname']['names'][]          = $subcategory->Наименование; 
                                                    $res['notname']['oc_category_id'][] = $this->model_module_Import1C->insert_category($subcategory->Наименование, $parentId);     
                                                }
                                                
                                            }
                                            //> Дикий говнокод, по возможности сделать ф-ю с рекурсией
                                            if(isset($subcategory->Группы)){
                                            
                                                foreach($subcategory->Группы->children() as $subsubcategory){

                                                    if(!in_array($subsubcategory->Наименование, $all_options_names)){
                                                        $res['notname']['ids'][]            = $subsubcategory->Ид;
                                                        $res['notname']['names'][]          = $subsubcategory->Наименование; 
                                                        $res['notname']['oc_category_id'][] = $this->model_module_Import1C->insert_category($subsubcategory->Наименование, $parentId);     
                                                    }

                                                }
                                            
                                            }
                                            //<//
                                        }
                                        
                                        //<//
                                    }
                            }
                    }
                    // отпарсили => удаляем файл
                  /*  $unset_file = $path.$fileoffer;
                    
                    if(unlink($unset_file)){
                        echo "Файл с категориями удален<br/>";
                    }*/
                    
                }else{
                    continue; 
                }
               //> Заполняем таблицу связанных id
               if(!empty($res['isname'])){
                   $myres     = array();
                   $query_str = '';
                   $myres[]   = $res['isname'];
                   $count     = count($res['isname']['oc_category_id']);

                   foreach ($myres as $key=>$item){

                      for($i=0; $i<$count; $i++){
                           $query_str.= "('{$item['oc_category_id'][$i]}', '{$item['ids'][$i]}'), ";
                      }

                   }

                   $insert_values = substr($query_str, 0, strlen($query_str) - 2) . ';';

                   $this->model_module_Import1C->insert_related_ids_categories_values($insert_values);
               }

               if(!empty($res['notname'])){
                   $myres     = array();
                   $query_str ='';
                   $myres[]   = $res['notname'];
                   $count     =count($res['notname']['oc_category_id']);

                   foreach ($myres as $key=>$item){

                      for($i=0; $i<$count; $i++){
                           $query_str.= "('{$item['oc_category_id'][$i]}', '{$item['ids'][$i]}'), ";
                      }

                   }

                   $insert_values = substr($query_str, 0, strlen($query_str) - 2) . ';';

                   $this->model_module_Import1C->insert_related_ids_categories_values($insert_values);
               }
               //<//
            }
        }  
        
       
        echo "_________________________________________________________________<br/>";
    }
    
    public function product($pwd){
        echo "<b>product</b> method is working<br/>";
        
        //> работаем с папкой googs
        $pwd_properties  = scandir($pwd.DIRECTORY_SEPARATOR."goods");
        $dirs_properties = array(); // массив в папке goods
        
        foreach ($pwd_properties as $prop){
            
            if(!is_dir($prop)){
                $dirs_properties[] = $prop;
            }
            
        }
        
        $all_files_id  = $this->model_module_Import1C->get_files_ids(); 
        
        $all_files_ids = array();
        
        foreach ($all_files_id as $file_id){
            $all_files_ids[] = $file_id['fileID'];
        }
        
        $allIds = $this->model_module_Import1C->get_all_products_ids(); // id из таблицы связей id`шников
        $oneCid = array();
        foreach ($allIds as $all){
            $oneCid[] = $all['1C_product_id'];
        }
        
        $allproducts_names = $this->model_module_Import1C->get_products_names();
        
        foreach ($allproducts_names as $all){
            $all_products_names[] = $all['name'];
        }
        
        foreach($dirs_properties as $dir_propertie){
            //$path = $pwd."/properties/".$dir_propertie;
            $path     = $pwd.DIRECTORY_SEPARATOR."goods".DIRECTORY_SEPARATOR;
            chdir($path);
            $filelist = glob("import*.xml"); // получаем массив xml`ек import
            
            foreach ($filelist as $fileoffer){
                $fileID = explode('___',$fileoffer);
                $fileID = substr($fileID[1], 0, -4);
                
                if(empty($all_files_ids)||(!in_array($fileID, $all_files_ids))){ // если id нет в базе
                
                    // пишем id файла в базу
                    $this->model_module_Import1C->insert_file_id($fileID);
                    
                    $xml = simplexml_load_file($fileoffer);
                    
                    foreach($xml->Каталог->Товары->children() as $option){
                             
                             if(!in_array($option->Ид, $oneCid)||empty($oneCid)){
                                 
                                if(in_array($option->Наименование, $all_products_names)){
                                    $res['isname']['ids'][]   = (string)$option->Ид; // $res['ids'] - массив с айишниками
                                    $res['isname']['names'][] = (string)$option->Наименование; 
                                        $product_id=0;
                                        
                                        foreach ($allproducts_names as $item){
                                            
                                                if($item['name']==$option->Наименование){
                                                   $res['isname']['oc_product_id'][] = $item['product_id'];
                                                   $product_id                       =$item['product_id'];
                                                }
                                                
                                        }
                                        
                                }else{ // если в базе нет данного товара
                                        $product_id                = 0;
                                        $res['notname']['ids'][]   = $option->Ид;
                                        $res['notname']['names'][] = $option->Наименование;
                                        $oneC_category_id          = $option->Группы->Ид;    // Ид категории товара
                                        $oc_category_id            = $this->model_module_Import1C->get_OC_id_category($oneC_category_id);
                                        @$oc_parent_id              = $this->model_module_Import1C->get_OC_parent_id_category($oc_category_id[0]['oc_category_id']);
                                        @$oc_category_id            = $oc_category_id[0]['oc_category_id']; //
                                        @$oc_category_parent_id     = $oc_parent_id[0]['parent_id'];
                                        
                                        //> Получим количество товара
                                        $quantity =0;
                                        
                                        foreach($xml->ПакетПредложений->Предложения->children() as $offer){
                                            $myid = explode("#", $offer->Ид);
                                            
                                            if($option->Ид==$myid[0]){
                                                $quantity += $offer->Остатки->Остаток->Количество;
                                            }
                                            
                                        }
                                        //<//
                                        
                                        //> Получим цену за единицу
                                        $product_price = 0;
                                        
                                        foreach($xml->ПакетПредложений->Предложения->children() as $offer){
                                            $myid = explode("#", $offer->Ид);
                                            
                                            if($option->Ид==$myid[0]){
                                                
                                                if(isset($offer->Цены->Цена->ЦенаЗаЕдиницу)){
                                                    $product_price=$offer->Цены->Цена->ЦенаЗаЕдиницу;
                                                }
                                                
                                            }
                                            
                                        }
                                        //<//
                                        $pathArr           = explode("\\",$option->Картинка);
                                        $image_product     = array_pop($pathArr);
                                        $attribute_country = $option->Изготовитель->ОфициальноеНаименование;

                                        $product_id=$this->model_module_Import1C->insert_product($option->Наименование, $quantity, $option->Артикул, $image_product, $product_price, $attribute_country, $option->Описание);
                                        
                                        //> product to category
                                        $this->model_module_Import1C->insert_product_to_category($product_id, $oc_category_id, $oc_category_parent_id);
                                        //<//
                                        
                                        //> Картинки для oc_product_image
                                        
                                        foreach($option->Картинка as $pic){
                                            $picture        = explode("\\",$pic);
                                            $picture        = array_pop($picture);
                                            $pictureArray[] = $picture;
                                        }
                                        
                                        $query_str1 = '';
                                        
                                        foreach($pictureArray as $picArr){
                                           $query_str1.= "('{$picArr}', '{$product_id}', '1'), ";
                                        }
                                        
                                        $insert_values1 = substr($query_str1, 0, strlen($query_str1) - 2) . ';';
                                        
                                        $this->model_module_Import1C->insert_product_image($insert_values1);
                                        //<//
                                        $res['notname']['oc_product_id'][] = $product_id;
                                        
                                        foreach ($option->ЗначенияСвойств->children() as $optionvalue){
                                            
                                            if(strlen($optionvalue->Значение)>0){ // если есть значение
                                            $attribute_id = 0;
                                            $text         = '';
                                                // сейчас мы в товарах
                                                // <ЗначенияСвойства><Ид></Ид><ВидСвойства>Атрибут</ВидСвойства><Значение></Значение>  </ЗначенияСвойства>
                                            
                                                if($optionvalue->ВидСвойства=='Атрибут'){
                                                    $path     = $pwd."/properties/";
                                                    chdir($path);
                                                    $filelist = glob("import*.xml"); // получаем массив xml`ек import
                                                    
                                                    foreach ($filelist as $fileoffer){
                                                        $xml1 = simplexml_load_file($fileoffer);
                                                        
                                                        foreach($xml1->Классификатор->Свойства->children() as $attr){
                                                            
                                                            $x          = (string)$attr->ВидСвойства;
                                                            $typeValues = (string)$attr->ТипЗначений;
                                                            
                                                            if($x=="Атрибут"){
                                                                
                                                                if((string)$optionvalue->Ид==(string)$attr->Ид){
                                                                    
                                                                    if($typeValues=='Строка'){
                                                                        $text = $optionvalue->Значение;
                                                                    }elseif($typeValues=='Справочник'){
                                                                        
                                                                        foreach ($attr->ВариантыЗначений->children() as $handbook){
                                                                        
                                                                            if((string)$optionvalue->Значение==(string)$handbook->ИдЗначения){
                                                                                $text = $handbook->Значение;
                                                                            }
                                                                            
                                                                        }
                                                                    
                                                                    }else{
                                                                        if(isset($optionvalue->Значение)){
                                                                            $text = $optionvalue->Значение;
                                                                        }else{
                                                                            $text='';
                                                                        }
                                                                    }
                                                                    $attrName     = $attr->Наименование;
                                                                    $attribute_id = $this->model_module_Import1C->get_attribute_id($attrName);
                                                                    $attribute_id = $attribute_id['attribute_id'];
                                                                }

                                                            }
                                                        }
                                                    }
                                                    
                                                    if($attribute_id!=0){
                                                        $this->model_module_Import1C->insert_product_attribute($product_id, $attribute_id, $text);
                                                    }
                                                    
                                                }else{
                                                    // файл товаров. если ВидСвойства=='Опция'
                                                    //Получим option_id opencart из таблицы 
                                                    $OCoptionID       = $this->model_module_Import1C->get_OC_option_id($optionvalue->Ид);
                                                    
                                                    $OCoptionID       = $OCoptionID['oc_option_id'];
                                                    
                                                    $product_optionID = $this->model_module_Import1C->insert_product_option($product_id, $OCoptionID );
                                                    
                                                    $OCoptionvalueID  = $this->model_module_Import1C->get_OC_option_value_id($optionvalue->Значение);
                                                    
                                                    $OCoptionvalueID  = $OCoptionvalueID[0]['oc_optionvalue_id'];
                                                    
                                                    $this->model_module_Import1C->insert_product_option_value($product_optionID, $product_id, $OCoptionID, $OCoptionvalueID);
                                                }
                                            }
                                        }
                                    }
                             }
                         
                    }
                    // отпарсили => удаляем файл
                   /* $unset_file = $path.$fileoffer;
                    if(isset($unset_file)){
                        @unlink($unset_file); echo "Файл с товарами удален<br/>";
                    }*/
                    
                }else{
                    continue;
                }
                //> Заполняем таблицу связанных id
                if(!empty($res['isname'])){
                    $myres     = array();
                    $query_str = '';
                    $myres[]   = $res['isname'];
                    $count=count($res['isname']['oc_product_id']);

                    foreach ($myres as $key=>$item){

                       for($i=0; $i<$count; $i++){
                            $query_str.= "('{$item['oc_product_id'][$i]}', '{$item['ids'][$i]}'), ";
                       }

                    }

                    $insert_values = substr($query_str, 0, strlen($query_str) - 2) . ';';

                    $this->model_module_Import1C->insert_related_ids_products_values($insert_values);
                }
                if(!empty($res['notname'])){
                    $myres     = array();
                    $query_str = '';
                    $myres[]   = $res['notname'];
                    $count     =count($res['notname']['oc_product_id']);

                    foreach ($myres as $key=>$item){

                       for($i=0; $i<$count; $i++){
                            $query_str.= "('{$item['oc_product_id'][$i]}', '{$item['ids'][$i]}'), ";
                       }

                    }

                    $insert_values = substr($query_str, 0, strlen($query_str) - 2) . ';';

                    $this->model_module_Import1C->insert_related_ids_products_values($insert_values);
                }
                //<//
                if(!empty($res['notname'])){
                    $myexplode = array();
                    foreach($res['notname']['ids'] as $oneCid){
                        foreach ($xml->ПакетПредложений->Предложения->children() as $predlozheniye){
                            $myexplode = explode('#',$predlozheniye->Ид);
                            if($myexplode[0] == $oneCid){                       //$myexplode id товара из "предложение"
                                $oc_product_id = $this->model_module_Import1C->get_oc_product_id($myexplode[0]);
                                $oc_product_id = $oc_product_id['oc_product_id']; // готовый для записи product_id OC
                                
                                $i=0;
                                
                                foreach ($predlozheniye->ЗначенияСвойств->children() as $znach_svoystva){
                                    $oc_option_id[$i] = $this->model_module_Import1C->get_oc_option_id_for_filter($znach_svoystva->Ид);
                                    $oc_option_id['oc_option_id'][$i] = $oc_option_id[$i]['oc_option_id'];
                                    
                                    $oc_option_value_id[$i] = $this->model_module_Import1C->get_oc_option_value_id_for_filter($znach_svoystva->Значение);
                                    $oc_option_value_id['oc_optionvalue_id'][$i] = $oc_option_value_id[$i]['oc_optionvalue_id'];
                                    
                                   $i++;
                                }
                                
                                foreach ($predlozheniye->Остатки->Остаток->children() as $mybalance){
                                    $balance = $mybalance; // остаток
                                }
                                
                                if($oc_option_id[0]['oc_option_id']==2){
                                    
                                    $prom                            = $oc_option_id[0]['oc_option_id'];
                                    $oc_option_id[0]['oc_option_id'] = $oc_option_id[1]['oc_option_id'];
                                    $oc_option_id[1]['oc_option_id'] = $prom;
                                    $prom2 = $oc_option_value_id[0]['oc_optionvalue_id'];
                                    $oc_option_value_id[0]['oc_optionvalue_id'] = $oc_option_value_id[1]['oc_optionvalue_id'];
                                    $oc_option_value_id[1]['oc_optionvalue_id'] = $prom2;
                                }
                                echo "product_id: ".$oc_product_id."<br/>";
                                echo "balance: ".$balance."<br/>";
                                echo "parent_option_id: ".$oc_option_id[0]['oc_option_id']."<br/>";  
                                echo "child_option_id: ".$oc_option_id[1]['oc_option_id']."<br/>";
                                echo "parent_option_value_id".$oc_option_value_id[0]['oc_optionvalue_id']."<br/>";
                                echo "child_option_value_id".$oc_option_value_id[1]['oc_optionvalue_id']."<br/><br/><br/>";
                               // echo "Остаток: ";
                                //> Добавляем
                                $this->model_module_Import1C->insert_to_tdo_option_value($oc_product_id, $oc_option_id[0]['oc_option_id'], $oc_option_id[1]['oc_option_id'], $oc_option_value_id[0]['oc_optionvalue_id'], $oc_option_value_id[1]['oc_optionvalue_id'], $balance);
                                //<//
    
                   
                            }
                        }
                        
                    }
                }
                
                
                
            }// внутри файла
        }  
        
        
        echo "_________________________________________________________________<br/>";
    }
    
    public function del_files($directory){
        $dir = opendir($directory);
        while(($file = readdir($dir))){
            if ( is_file ($directory."/".$file)){
                unlink ($directory."/".$file);
            }
            else if (is_dir ($directory."/".$file) &&($file != ".") && ($file != "..")){
                $this->del_files ($directory."/".$file); 
            }
        }
        closedir ($dir);
    }
    
    public function translit($str) {
        $rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $lat = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
        return strtolower(str_replace($rus, $lat, $str));
    }
    
    
    public function create_xml_for_1C($mydata){
        $pwd = $this->request->server['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'import1C'.DIRECTORY_SEPARATOR.'ocOrderXML'.DIRECTORY_SEPARATOR;
        $file_path = $pwd.'order'.$mydata['order_id'].'.xml';
        
        if (!file_exists($file_path)) {
                fopen($file_path, "w");
            }
            
            $dateforxml = $mydata['date']."T".$mydata['time'];
            $content = '<?xml version="1.0" encoding="UTF-8"?>
<КоммерческаяИнформация xmlns="urn:1C.ru:commerceml_2" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ВерсияСхемы="2.09" ДатаФормирования="'.$dateforxml.'" Ид="1">
	<Документ>
	</Документ>
</КоммерческаяИнформация>';
        file_put_contents($file_path, $content);
        
        $xml = simplexml_load_file($file_path);
        
        $xml->Документ->addChild('Ид', ' '); 
        $xml->Документ->addChild('НомерВерсии', ' ');
        $xml->Документ->addChild('ПометкаУдаления', 'false');
        $xml->Документ->addChild('Номер', $mydata['order_id']);
        $xml->Документ->addChild('Номер1С', '');
        $xml->Документ->addChild('Дата', $mydata['date']);
        $xml->Документ->addChild('Дата1С', '');
        $xml->Документ->addChild('Время', $mydata['time']);
        $xml->Документ->addChild('ХозОперация', 'Заказ товара');
        $xml->Документ->addChild('Контрагенты', ' ');
        $xml->Документ->Контрагенты->addChild('Контрагент', '');
        $xml->Документ->Контрагенты->Контрагент->addChild('Ид', ' ');
        $xml->Документ->Контрагенты->Контрагент->addChild('НомерВерсии', ' ');
        $xml->Документ->Контрагенты->Контрагент->addChild('ПометкаУдаления', 'false');
        $xml->Документ->Контрагенты->Контрагент->addChild('Наименование', $mydata['name']);
        $xml->Документ->Контрагенты->Контрагент->addChild('ПолноеНаименование', $mydata['name']);
        $xml->Документ->Контрагенты->Контрагент->addChild('Роль', 'Покупатель');
        $xml->Документ->Контрагенты->Контрагент->addChild('ИНН', $mydata['inn']);
        $xml->Документ->Контрагенты->Контрагент->addChild('КПП', '');
        $xml->Документ->Контрагенты->Контрагент->addChild('КодПоОКПО', '');
        $xml->Документ->Контрагенты->Контрагент->addChild('Представители', '');
        $xml->Документ->Контрагенты->Контрагент->addChild('Адрес', ' ');
        $xml->Документ->Контрагенты->Контрагент->Адрес->addChild('Представление', $mydata['city']);
        $xml->Документ->Контрагенты->Контрагент->Адрес->addChild('АдресноеПоле', ' ');
        $xml->Документ->Контрагенты->Контрагент->Адрес->АдресноеПоле->addChild('Тип', 'Страна');
        $xml->Документ->Контрагенты->Контрагент->Адрес->АдресноеПоле->addChild('Значение', $mydata['city']);
        $xml->Документ->Контрагенты->Контрагент->addChild('АдресРегистрации', ' ');
        $xml->Документ->Контрагенты->Контрагент->АдресРегистрации->addChild('Представление', $mydata['city']);
        $xml->Документ->Контрагенты->Контрагент->АдресРегистрации->addChild('АдресноеПоле', ' ');
        $xml->Документ->Контрагенты->Контрагент->АдресРегистрации->АдресноеПоле->addChild('Тип', 'Страна');
        $xml->Документ->Контрагенты->Контрагент->АдресРегистрации->АдресноеПоле->addChild('Значение', $mydata['country']);
        $xml->Документ->Контрагенты->Контрагент->addChild('Контакты', ' ');
        $xml->Документ->Контрагенты->Контрагент->Контакты->addChild('Контакт', ' ');
        $xml->Документ->Контрагенты->Контрагент->Контакты->Контакт->addChild('Тип', 'Телефон рабочий');
        $xml->Документ->Контрагенты->Контрагент->Контакты->Контакт->addChild('Значение', $mydata['phone']);
        $xml->Документ->Контрагенты->Контрагент->Контакты->addChild('Контакт', ' ');
        $xml->Документ->Контрагенты->Контрагент->Контакты->Контакт[1]->addChild('Тип', 'Электронная почта');
        $xml->Документ->Контрагенты->Контрагент->Контакты->Контакт[1]->addChild('Значение', $mydata['email']);
        $xml->Документ->addChild('Склады', ' ');
        $xml->Документ->Склады->addChild('Склад', ' ');
        $xml->Документ->Склады->Склад->addChild('Ид', '');
        $xml->Документ->Склады->Склад->addChild('Наименование', 'Основной склад');
        $xml->Документ->addChild('Валюта', 'RUB');
        $xml->Документ->addChild('Курс', '1.0000');
        $xml->Документ->addChild('Сумма', $mydata['itogo']);
        $xml->Документ->addChild('Роль', 'Покупатель');
        $xml->Документ->addChild('Комментарий', '');
        $xml->Документ->addChild('Налоги', ' ');
        $xml->Документ->Налоги->addChild('Налог', ' ');
        $xml->Документ->Налоги->Налог->addChild('Наименование', 'НДС');
        $xml->Документ->Налоги->Налог->addChild('УчтеноВСумме', 'true');
        $summa_nds = (int)$mydata['itogo'];
        $summa_nds = $summa_nds*18/118;
        $summa_nds = round($summa_nds, 1);
        $xml->Документ->Налоги->Налог->addChild('Сумма', $summa_nds);
        $xml->Документ->addChild('ЗначенияРеквизитов', ' ');
        $xml->Документ->ЗначенияРеквизитов->addChild('ЗначениеРеквизита', ' ');
        $xml->Документ->ЗначенияРеквизитов->ЗначениеРеквизита->addChild('Наименование', 'ПометкаУдаления');
        $xml->Документ->ЗначенияРеквизитов->ЗначениеРеквизита->addChild('Значение', 'false');
        $xml->Документ->ЗначенияРеквизитов->addChild('ЗначениеРеквизита', ' ');
        $xml->Документ->ЗначенияРеквизитов->ЗначениеРеквизита[1]->addChild('Наименование', 'Проведен');
        $xml->Документ->ЗначенияРеквизитов->ЗначениеРеквизита[1]->addChild('Значение', 'true');
        $xml->Документ->ЗначенияРеквизитов->addChild('ЗначениеРеквизита', ' ');
        $xml->Документ->ЗначенияРеквизитов->ЗначениеРеквизита[2]->addChild('Наименование', 'Статуса заказа ИД');
        $xml->Документ->ЗначенияРеквизитов->ЗначениеРеквизита[2]->addChild('Значение', 'F');
        $xml->Документ->addChild('Товары', ' '); 
        $i = 0;
        
        foreach ($mydata['products'] as $product){
            
            foreach ($product as $prod){
                
               $xml->Документ->Товары->addChild('Товар', ' ');
               $xml->Документ->Товары->Товар[$i]->addChild('Ид', ' ');
               $xml->Документ->Товары->Товар[$i]->addChild('Наименование', $prod['name']);
               $xml->Документ->Товары->Товар[$i]->addChild('СтавкиНалогов', ' ');
               $xml->Документ->Товары->Товар[$i]->СтавкиНалогов->addChild('СтавкаНалога', ' ');
               $xml->Документ->Товары->Товар[$i]->СтавкиНалогов->СтавкаНалога->addChild('Наименование', 'НДС');
               $xml->Документ->Товары->Товар[$i]->СтавкиНалогов->СтавкаНалога->addChild('Ставка', '18');
               $xml->Документ->Товары->Товар[$i]->addChild('ЗначенияРеквизитов', ' ');
               $xml->Документ->Товары->Товар[$i]->ЗначенияРеквизитов->addChild('ЗначениеРеквизита', ' ');
               $xml->Документ->Товары->Товар[$i]->ЗначенияРеквизитов->ЗначениеРеквизита[0]->addChild('Наименование', 'ВидНоменклатуры');
               $xml->Документ->Товары->Товар[$i]->ЗначенияРеквизитов->ЗначениеРеквизита[0]->addChild('Значение', 'Товар упаковками');
               $xml->Документ->Товары->Товар[$i]->ЗначенияРеквизитов->addChild('ЗначениеРеквизита', ' ');
               $xml->Документ->Товары->Товар[$i]->ЗначенияРеквизитов->ЗначениеРеквизита[1]->addChild('Наименование', 'ТипНоменклатуры');
               $xml->Документ->Товары->Товар[$i]->ЗначенияРеквизитов->ЗначениеРеквизита[1]->addChild('Значение', 'Товар');
               $xml->Документ->Товары->Товар[$i]->addChild('Единица', ' ');
               $xml->Документ->Товары->Товар[$i]->Единица->addChild('Ид', '796');
               $xml->Документ->Товары->Товар[$i]->Единица->addChild('НаименованиеКраткое', 'шт');
               $xml->Документ->Товары->Товар[$i]->Единица->addChild('Код', '796');
               $xml->Документ->Товары->Товар[$i]->Единица->addChild('НаименованиеПолное', 'Штука');
               $xml->Документ->Товары->Товар[$i]->addChild('Коэффициент', '1');
               $xml->Документ->Товары->Товар[$i]->addChild('Количество', $prod['quantity']);
               $xml->Документ->Товары->Товар[$i]->addChild('Цена', $prod['price']);
               $xml->Документ->Товары->Товар[$i]->addChild('Сумма', $prod['total']);
               $xml->Документ->Товары->Товар[$i]->addChild('Налоги', ' ');
               $xml->Документ->Товары->Товар[$i]->Налоги->addChild('Налог', ' ');
               $xml->Документ->Товары->Товар[$i]->Налоги->Налог->addChild('Наименование', 'НДС');
               $xml->Документ->Товары->Товар[$i]->Налоги->Налог->addChild('УчтеноВСумме', 'true');
               $sum_nds = (int)$prod['total'];
               $sum_nds = $sum_nds*18/118;
               $sum_nds = round($sum_nds, 1);
               $xml->Документ->Товары->Товар[$i]->Налоги->Налог->addChild('Сумма', $sum_nds);
               $xml->Документ->Товары->Товар[$i]->Налоги->Налог->addChild('Ставка', '18');
               
               $i++; 
            }
        }
        
      /*  echo "<pre>";
        var_dump($mydata['products']);
        echo "</pre>";*/
        
        //> Форматирование
        $dom = new DOMDocument("1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        //<//
        file_put_contents($file_path, $dom->saveXML());

    }
    
    public function tdo(){
        echo "<b>tdo</b> method is working<br/>";
        
        //> работаем с папкой documents
        $pwd_properties  = scandir($pwd.DIRECTORY_SEPARATOR."documents");
        $dirs_properties = array(); // массив в папке documents
        
        foreach ($pwd_properties as $prop){
            
            if(!is_dir($prop)){
                $dirs_properties[] = $prop;
            }
            
        }
        
        $all_files_id  = $this->model_module_Import1C->get_files_ids(); 
        
        $all_files_ids = array();
        
        foreach ($all_files_id as $file_id){
            $all_files_ids[] = $file_id['fileID'];
        }
        
        $allIds = $this->model_module_Import1C->get_all_products_ids(); // id из таблицы связей id`шников
        
        foreach ($allIds as $all){
            $oneCid[] = $all['1C_product_id'];
        }
        /**************************************************************************/
        foreach($dirs_properties as $dir_propertie){
            //$path = $pwd."/properties/".$dir_propertie;
            $path     = $pwd.DIRECTORY_SEPARATOR."goods".DIRECTORY_SEPARATOR;
            chdir($path);
            $filelist = glob("import*.xml"); // получаем массив xml`ек import
            
            foreach ($filelist as $fileoffer){
                $fileID = explode('___',$fileoffer);
                $fileID = substr($fileID[1], 0, -4);
                
                if(empty($all_files_ids)||(!in_array($fileID, $all_files_ids))){ // если id нет в базе
                
                    // пишем id файла в базу
                    $this->model_module_Import1C->insert_file_id($fileID);
                    
                    $xml = simplexml_load_file($fileoffer);
                    
                    foreach($xml->Каталог->Товары->children() as $option){
                             
                         
                    }
                    
                }else{
                    continue;
                }
            }
        }
        /*****************************************************************************/
    }
    
}