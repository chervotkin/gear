<?php
define('DRUPAL_ROOT', getcwd().'/..');

if ( !isset( $_SERVER['REMOTE_ADDR'] ) ) {
  $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
require_once 'phpQuery/phpQuery.php';

drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);



$max_iter = 1;
$iter = 1;

$files = scandir('.');

	$bike = array();


foreach ($files as $file) {
  if (strpos($file, 'html') !== FALSE){
	$bike = array();
	$tbike = array();
	$html = file_get_contents($file);
	$doc = phpQuery::newDocumentHTML($html, 'utf-8');
	$details = $doc->find('div.detail-table');
	$bike['title'] = $doc->find('h1')->text();
	//print ("Parsing ".$bike['title']."\n");
	$bike['image'] = $doc->find('a.MagicZoomPlus')->attr('href');
	$price = $doc->find('form div.price-box span.price')->text();
	$price = str_replace('$', '', $price);
	$price = str_replace('.00', '', $price);
	$price = str_replace(',', '', $price);
	$price = floatval($price);
	$bike['field_moto_price'] = $price;
	$i = 0;
	foreach ($details->find('div.table-grid span') as $val){
		if ($i % 2 == 0) {
			$k = pq($val)->html();
			$k = str_replace(':', '', $k);
		}
		else {
			$v = pq($val)->text();
			if ($k == 'Make' || $k == 'Model'){
				$tbike[$k] = $v;
			}
			else {
				$tbike[$k] = floatval($v);
			}

		}
		$i++;
	}
  if (isset($tbike['Make']))			{$bike['make'] = $tbike['Make'];}
  if (isset($tbike['Model']))			{$bike['field_moto_model'] = $tbike['Model'];}
  if (isset($tbike['Production Year'])) {$bike['field_moto_productyear'] = $tbike['Production Year'];}
  if (isset($tbike['Engine Type'])) 	{$bike['field_moto_trokes'] = $tbike['Engine Type'];}
  if (isset($tbike['Bore (millimeters)'])) 	{$bike['field_moto_bore'] = $tbike['Bore (millimeters)'];}
  if (isset($tbike['Fuel Capacity (litres)'])) 	{$bike['field_moto_fueltank'] = $tbike['Fuel Capacity (litres)'];}
  if (isset($tbike['Stroke (millimeters)'])) 	{$bike['field_moto_stroke'] = $tbike['Stroke (millimeters)'];}
  if (isset($tbike['Seat Height (millimeters)'])) 	{$bike['field_moto_seat'] = $tbike['Seat Height (millimeters)'];}
  if (isset($tbike['Wheelbase (millimeters)'])) 	{$bike['field_moto_wheelbase'] = $tbike['Wheelbase (millimeters)'];}
  if (isset($tbike['Wet Weight (kilograms)'])) 	{$bike['field_moto_wetweight'] = $tbike['Wet Weight (kilograms)'];}
  if (isset($tbike['Weight (pounds)'])) 	{$bike['field_moto_weight'] = intval($tbike['Weight (pounds)'] * 0.4536);}
  
  if (isset($bike['field_moto_bore']) and isset($bike['field_moto_stroke'])) {
  	$bike['field_moto_volume'] = intval(pi() * $bike['field_moto_bore'] * $bike['field_moto_bore'] * $bike['field_moto_stroke'] / 4000);
  }

  //print_r ($tbike);
  print_r($bike);
  if ((!isset($bike['image'])))  {
  	print $bike['title']."Weight not set! \n";
  	print_r($tbike);
  }

  // --- Create node ------------

      $node = new stdClass(); // Create a new node object
      $node->type = "motorcycle"; // Or page, or whatever content type you like
      node_object_prepare($node); // Set some default values
        
      $node->title    = $bike['title'];
      $node->language = LANGUAGE_NONE; // Or e.g. 'en' if locale is enabled
      
      $node->uid = 1; // UID of the author of the node; or use $node->name
      
//      $node->body[$node->language][0]['value']   = $translated;

      $src = $bike['image'];
      $filename = pathinfo($src, PATHINFO_BASENAME);
      $img_dir = drupal_realpath(DRUPAL_ROOT."/sites/default/files/bikes/");
      $dst = drupal_realpath($img_dir.$filename);
      copy($src, $dst);
      
      
      foreach($bike as $k => $v){
        if (strpos($k, 'field') === 0){
          $node->{$k}[$node->language][0]['value'] = $v;
        }
      }

      $node->body[$node->language][0]['value'] = '<img src="/sites/default/files/bikes/'.$filename.'" alt="'.$title.'" />';
      $node->body[$node->language][0]['summary'] = '<img src="/sites/default/files/bikes/'.$filename.'" alt="'.$title.'" />';
      $node->body[$node->language][0]['format'] = 'full_html';

      if($node = node_submit($node)) { // Prepare node for saving
          node_save($node);
          echo "  Node with nid " . $node->nid . " saved!\n";
//          sleep(5);
      }


  // ----------------------------
  if ($iter++ >= $max_iter) break;
  }
}
//print_r($bike);
?>

<?/* --- Set of Parameters ---
    [title] => 2012 Zero X 
    [image] => http://gear.dirtrider.com/media/catalog/product/cache/15/image/9df78eab33525d08d6e5fb8d27136e95/2/0/2012_Zero_X_Dirt.jpg
    [Make] => zero
    [Model] => X
    [Production Year] => 2012
    [Bore (inches)] => 1.87
    [Bore (millimeters)] => 47.5
    [Engine Type] => 2
    [Fuel Capacity (gallons)] => 1.3
    [Fuel Capacity (litres)] => 4.9
    [Stroke (inches)] => 1.89
    [Stroke (millimeters)] => 47.8
    [Seat Height (inches)] => 34.3
    [Seat Height (millimeters)] => 870
    [Wheelbase (inches)] => 55.5
    [Wheelbase (millimeters)] => 1410
    [Weight (pounds)] => 215
    [Wet Weight (pounds)] => 215
    [Wet Weight (kilograms)] => 98
*/?>