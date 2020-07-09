<?php
/*
 * modalità di conversione xml to array.
 */

$xml = simplexml_load_file('Config/import.xml');

/*
 * Prima modalità.
 */
$json = json_encode($xml);
$array[$xml->getName()] = json_decode($json,TRUE);

/*
 * Seconda modalità.
 */
//$array = (array)$xml;

/*
 * Terza modalità.
 */
//function toArray($xml) {
//    $array = json_decode(json_encode($xml), TRUE);
//
//    foreach (array_slice($array, 0) as $key => $value) {
//        if (empty($value)) {
//            $array[$key] = NULL;
//            
//        } elseif (is_array($value)) {
//            $array[$key] = toArray($value);
//            
//        }
//    }
//
//    return $array;
//}
//
//$array = toArray($xml);

/*
 * Quarta modalità.
 */
//function toArray($xml) {
//    $array = json_decode(json_encode($xml), TRUE);
//
//    foreach (array_slice($array, 0) as $key => $value) {
//        if (is_array($value)) {
//            $array[$key] = toArray($value);
//        }
//    }
//
//    return $array;
//}
//
//$array = toArray($xml);

/*
 * Quinta modalità.
 */
//$array = array();
//
//SimpleXMLToArray($xml);
//
//function SimpleXMLToArray($element) {
//    global $array;
//    $array_temp = null;
//    
//    $element_name_suffix_ID = 1;
//    $element_name_suffix = null;
//    
//    if (array_key_exists($element_name, $array)) {
//        $element_name_suffix_ID++;
//        $element_name_suffix = '_' . $element_name_suffix_ID;
//    }
//    
//    
//    
//    
//    foreach ($children as $element) {
//        $element_name = $element->getName();
//        
//        
//        
////        $array[$element_name . $element_name_suffix] = array();
////        $array_temp = $array[$element_name . $element_name_suffix];
//        
//        foreach ($element->attributes() as $key => $value) {
//            $array_temp[$element_name . $element_name_suffix]['@attributes'][$key] = $value;
//        }
//        
//        $element_children = $element->children();
//        if ($element_children) {
//            
//            SimpleXMLToArray($element_children);
//        } else {
//            $array_temp[$element_name . $element_name_suffix] = $element->__toString();
//            $array[$element_name . $element_name_suffix] = $array_temp;
//        }
//        
//        
//    
//    }
//
//}
//
print_r($array);
var_dump($array);