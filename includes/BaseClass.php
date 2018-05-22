<?php
    /**************************************************************************
 * @CLASS BaseClass
 * @brief common functionality for use within all classes
 * @REQUIRES:
 *  -none
 *
 **************************************************************************/
class BaseClass{
  var $arrPOST;
  var $arrGET;


   public static function Get(){
    //==== instantiate or retrieve singleton ====
    static $inst = NULL;
    if( $inst == NULL )
      $inst = new BaseClass();
    return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }

  /**
  * clean all variables prior to using them
  * @return bool
  */
  function CleanInputs(){
    $this->arrGET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
    $this->arrPOST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    return TRUE;
  }

  /**
  * Get the timezones available, or convert an existing timezone to an offset
  * @param $intTimeZoneIdentifier (int) list identifier
  * @return string/int
  */
   public static function GetTimeZoneData($intTimeZoneIdentifier=0,$boolDateString=FALSE){
    $arrTimeZones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    if((int)$intTimeZoneIdentifier === 0)
        return $this->GetTimeZoneList($arrTimeZones);
    //load UTC for comparison
    $intUTCTime = new DateTime('now', new DateTimeZone('UTC'));
    foreach($arrTimeZones as $ka=>$va){
      //load the object by timezone country/city designation
      //it matches our chosen TZ
      if((int)$intTimeZoneIdentifier > 0 && $intTimeZoneIdentifier == $ka){
        if($boolDateString)
            return $va;
        $objZoneTime = new DateTimeZone($va);
        //give back the offset in seconds
        return $objZoneTime->getOffset($intUTCTime);
      }
    }
  }

  /**
  * given an array of timezones, narrow down the list to something reasonable
  * @param $arrTimeZones
  * @return array
  */
  public static function GetTimeZoneList($arrTimeZones){
    $arrTimeZoneList = array();
    $arrUsedTimeZones = array();
    $arrCountryZones = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY);
    //GET UNIQUE VALUES ONLY
    foreach ($arrCountryZones as $strTimeZoneName) {
        $objDateTimeZone = new DateTimeZone($strTimeZoneName);
        $objDateTime = new DateTime("now", $objDateTimeZone);
        $intOffset = $objDateTime->getOffset();
     if(!in_array($intOffset,$arrUsedTimeZones)){
       $arrUsedTimeZones[] = $intOffset;
    $strMeridian = $objDateTime->format('H') > 12 ? ' ('. $objDateTime->format('g:i a'). ')' : '';
    $arrTimeZoneList[array_search($strTimeZoneName,$arrTimeZones)] = trim($strTimeZoneName).' - '.$objDateTime->format('H:i').$strMeridian;
     }
    }
    //order them sanely
    ksort($arrTimeZoneList);
    //give it back
    return $arrTimeZoneList;
  }

}
?>