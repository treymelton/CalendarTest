<?php
    /**************************************************************************
 * @CLASS HTMLHelper
 * @brief Handle HTML form creation and assisted tasks
 * @REQUIRES:
 *  -BaseClass.php
 *
 **************************************************************************/
class HTMLHelper extends BaseClass{
  var $objHTML = null;
  var $objDocument = null;
  var $intHTML = 1;//0 for xml 1 for HTML

   public static function Get(){
    //==== instantiate or retrieve singleton ====
    static $inst = NULL;
    if( $inst == NULL )
      $inst = new HTMLHelper();
    return( $inst );
  }

  function __construct(){
    // construct here
    if($this->objHTML == null)
      $this->objHTML = new SimpleXMLElement('<html></html>');
  }
 /**
 * given an HTML tag group, load the HTML for a base document
 * @param $strBaseHTML
 * @return bool
 */
 //make a table
 function LoadBaseHTML($strBaseHTML){
    $this->objHTML = new SimpleXMLElement($strBaseHTML);
   return TRUE;
 }

 //add an element
 function FindElementByName($objParentNode, $strElementName){
   $objChildNode = null;
   if($strElementName !=""){
     foreach( $objParentNode->children() as $objChild ){
       //lets get recursive with it.
       if($objChild->children()->count() > 0){
          if($objChildNode = $this->FindElementByName($objChild, $strElementName)){
            return $objChildNode;
          }
       }
       if($objChild["name"] == $strElementName){
         return $objChild;
       }
     }
   }
   return FALSE;
 }

 //do what it says
 function FindElementByTagName($objParentElement,$strTagName,$intIndex = 0){
    $objChildNode = null;
   if($intIndex > 0)
      $objParentElement = $objParentElement[$intIndex];
   if($strTagName !=""){
     foreach( $objParentElement->children() as $objChild ){
       //lets get recursive with it.
       if(count($objChild->children()) > 0){
          if($objChildNode = $this->FindElementByTagName($objChild, $strTagName)){
            return $objChildNode;
          }
       }
       if($objChild->getName() == $strTagName){
         return $objChild;
       }
     }
   }
   return FALSE;
 }

 //we need to update an elements content
 function UpdateElementContent($objElement,$strNewContent){
   $objElement[0] =  ($objElement[0].$strNewContent);
   return $objElement;
 }

 //we need to update an elements content
 function CalculateElementContent($objElement,$intNewValue = 0,$strOperator = '+'){
   if($strOperator == '+')
      $objElement[0] =  ($objElement[0] + $intNewValue);
   if($strOperator == '*')
      $objElement[0] =  ($objElement[0] * $intNewValue);
   if($strOperator == '/')
      $objElement[0] =  ($objElement[0] / $intNewValue);
   if($strOperator == '-')
      $objElement[0] =  ($objElement[0] - $intNewValue);
   if($strOperator == '%')
      $objElement[0] =  ($objElement[0] % $intNewValue);
   return $objElement;
 }

 //we need to replace an elements content
 function ReplaceElementContent($objElement,$strNewContent){
   $objElement[0] =  $strNewContent;
   return $objElement;
 }

 //upate an element
 function UpdateElementAttribute($objParentNode, $arrAttributes){
   $objTempObjectAttributes = array();
   //unset everything;
   if(is_object($objParentNode)){
     foreach($objParentNode->attributes() as $ka=>$va){
       $objTempObjectAttributes[$ka] = $va;
        if(array_key_exists($ka,$arrAttributes))
            $objParentNode[$ka]= '';
     }
   }
   //reset them now
   foreach($arrAttributes as $kb=>$vb){
     if(trim($kb) == '' )
      continue 1;
      if(!array_key_exists($kb,$objTempObjectAttributes))
          $objParentNode->addAttribute($kb,$vb);
      else
         $objParentNode->attributes()->$kb=$vb;
   }
   return $objParentNode;
 }

 //lets set the row table cells
 function SetBlankSpacerRow($objParent,$intCellCount,$arrAttributes = array()){
   //lets make the row
   $objTableRow = $this->AddChildNode($objParent, '','tr',array());
   for($i=0;$i<$intCellCount;$i++){
      $this->AddChildNode($objTableRow, '','td',$arrAttributes);
   }
 }

 //add a child to the XML/HTML
 function AddChildNode($objParentNode,$strContent='', $strChildName = '',$arrAttributes = array(),$objNewChild = null){
   if(!is_object($objParentNode))
      return FALSE;
   if($objNewChild == null)
      $objNewChildElement = $objParentNode->addChild($strChildName);
   else
      $objNewChildElement = $objParentNode->addChild($objNewChild['name']);
   //add the content
   $objNewChildElement = $this->UpdateElementContent($objNewChildElement,$strContent);
   //now we'll add the attributes
   if(is_array($arrAttributes) && sizeof($arrAttributes) > 0)
      $this->UpdateElementAttribute($objNewChildElement, $arrAttributes);
      return  $objNewChildElement;
 }

 //wrap up the HTML/XML
 function CloseDocument(){
    $objHTML = dom_import_simplexml($this->objHTML);
    $objDocument = new DOMDocument('1.0', 'utf-8');
    $objImportElement = $objDocument->importNode($objHTML,true);
    $objDocument->appendChild($objImportElement);
    return html_entity_decode($objDocument->saveHTML());
 }

}//end class
?>