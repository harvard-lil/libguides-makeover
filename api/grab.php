<?php

class Grab extends Controller {

    function guide() {
      $f3=$this->framework;
      $db = $f3->get('DB');
      $db_user = $f3->get('DB_USER');
      $db_password = $f3->get('DB_PASSWORD');
      $db_host = $f3->get('DB_HOST');
      
      require(dirname(__FILE__).'/simple_html_dom.php');
      
      $guide = $_REQUEST['guide'];
      $content = array();
      $content['sections'] = array();
        
      $html = file_get_html("http://libguides.law.harvard.edu/process_d.php?mode=boxesapi&pid=$guide");
      
      // find all link
      foreach($html->find('optgroup') as $tab) { 
        $tab_contents = array();
        $tab_name = trim($tab->label);
        $tab_name = preg_replace( '/&nbsp;/', ' ', $tab_name );
        $tab_contents['name'] = trim($tab_name);
        $tab_html = str_get_html($tab);
        $boxes = array();
        foreach($tab_html->find('option') as $option) {
          $name = addslashes($option->plaintext);
          $id = $option->value;
          $section['name'] = $name;
          $section['id'] = $id;
          $section_html = file_get_html("http://api.libguides.com/api_box.php?iid=529&bid=$id&context=object");
          foreach($section_html->find('div.outerbox') as $div){
             $section_contents = $div->innertext;
             $section_contents = preg_replace( '/\s+/', ' ', $section_contents );
             $section['contents'] = $section_contents;
          }
          array_push($boxes, $section);
        }
        $tab_contents['boxes'] = $boxes;
        array_push($content['sections'], $tab_contents);
      }
      
      /*$callno_text = $f3->get('PARAMS.callno');*/
      
      
      //$callback = $_GET['callback'];
      //header('Content-type: application/json');
      //echo $callback . '(' . json_encode($content) . ')';
      //echo json_encode($content);
      $file_path = "guide$guide.json";
      file_put_contents($file_path, json_encode($content));

    }
    
    function guideBuilt() {
      $f3=$this->framework;
      
      $guide = $_REQUEST['guide'];
      $content = array();
        
      $html = file_get_contents("http://libguides.law.harvard.edu/process_d.php?mode=boxesapi&pid=$guide");
      $data['dom'] = array();
      
      $dom = new DOMDocument();
      @$dom->loadHTML($html);
      $x = new DOMXPath($dom); 
      
      foreach($x->query("//optgroup") as $tab) {
        $tab_contents = array();
        $tab_contents['name'] = $tab->getAttribute("label");
        echo $tab_contents['name'];
        foreach($tab->childNodes as $box){
          $id = $box->getAttribute("value");
          $name = $box->nodeValue;
          $htmlTab = file_get_contents("http://api.libguides.com/api_box.php?iid=529&bid=$id&context=object");
          echo $htmlTab;
        }
      }
      
      //$callback = $_GET['callback'];
      header('Content-type: application/json');
      //echo $callback . '(' . json_encode($content) . ')';
      //echo json_encode($content);

    }

}
?>
