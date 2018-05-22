<?php
   /**************************************************************************
 * @CLASS Calendar
 * @brief creates calendar controls, and calculations
 * @REQUIRES:
 *  -BaseClass.php
 *  -HTMLHelper.php
 *
 **************************************************************************/
class Calendar extends BaseClass{
   private $objCalendar;
   private $intMonth=1;
   private $intDay=1;
   private $intYear = 0;
   private $arrCalendarProperties;
   private $strResults;
   public static function Get(){
    //==== instantiate or retrieve singleton ====
    static $inst = NULL;
    if( $inst == NULL )
      $inst = new Calendar();
    return( $inst );
  }

  function __construct(){
    //Start on instantiation
    if($this->intYear === 0)
      $this->intYear = date('Y') ;
  }

  /**
  * interpret controls from the form
  * @return bool
  */
  function InterpretCalendarFunctions(){
    //clean our inputs
    $this->CleanInputs();
    if(array_key_exists('presentmonth',$this->arrPOST) && array_key_exists('monthaction',$this->arrPOST)){
     //we're changing the month
     if($this->arrPOST['monthaction'] == 'next'){
       if($this->arrPOST['presentmonth'] == 12){
         $this->intMonth = 1;
         $this->intYear = ($this->arrPOST['presentyear'] + 1);
       }
       else
         $this->intMonth = ($this->arrPOST['presentmonth'] + 1);
     }
     else if($this->arrPOST['monthaction'] == 'previous'){
       if($this->arrPOST['presentmonth'] == 1){
         $this->intMonth = 12;
         $this->intYear = ($this->arrPOST['presentyear'] - 1);
       }
       else
         $this->intMonth = ($this->arrPOST['presentmonth'] - 1);
     }
     else{
      //load our previous values
      if((int)$this->arrPOST['presentmonth'] > 0)
        $this->intMonth = ($this->arrPOST['presentmonth']);
      if((int)$this->arrPOST['presentyear'] > 0)
        $this->intYear = ($this->arrPOST['presentyear']);
     }
    }
    else{//make our defaults
      if($this->intMonth  === 0)
          $this->intMonth  = date('m') ;
      if($this->intDay  === 0)
          $this->intDay  = date('j') ;
    }
    //execute our calendar actions
    if(array_key_exists('calendaraction',$this->arrPOST)){
      $arrCalendarData = json_decode($this->arrPOST['actiondata'],TRUE);
      //clean up our data
      $arrCalendarData = $this->CleanActionData($arrCalendarData);
      //execute
      if($this->arrPOST['calendaraction'] == 'comparedates'){
        $this->CompareDates($arrCalendarData);
      }
      if($this->arrPOST['calendaraction'] == 'adddates'){
        $this->AddSelectedDates($arrCalendarData);
      }
      if($this->arrPOST['calendaraction'] == 'nowtillthen'){
        $this->GetTheTillNow($arrCalendarData);
      }
    }
  }

  /**
  * given an action data array, clean it up
  * @param $arrCalendarData
  * @retun array
  */
  function CleanActionData($arrCalendarData){
    $arrReturnArray = array();
    foreach($arrCalendarData as $varCalendarId=>$arrData){
      if(is_array($arrData)){
        $arrReturnArray[$varCalendarId] = array();
        foreach($arrData as $intKey=>$varDays){
          if(is_array($varDays)){
              $arrReturnArray[$varCalendarId][] = $intKey;
          }
        }
      }
    }
    return ($arrReturnArray);
  }

  /**
  * construct a calendar
  * @param $arrCalendarProperties
  * @return string ( HTML )
  */
  function MakeCalendar($arrCalendarProperties){
    //check for calendar ctions
    $this->InterpretCalendarFunctions();
    $this->arrCalendarProperties = $arrCalendarProperties;
    //make our HTML from the array
    $this->objCalendar = new HTMLHelper();
    $this->objCalendar->LoadBaseHTML('<div class="calendar"></div>');
    $this->MakeCalendarControls();
    $this->MakeCalendarGrid();
    $this->AddResultsForActions();
    $this->CreateCalendarActions();
    //our dimensions of a calendar
    return $this->objCalendar->CloseDocument();
  }

  /**
  * make the base calendar
  * @return bool
  */
  function MakeCalendarGrid(){
   //get our days
   $intMonthDays = cal_days_in_month(CAL_GREGORIAN, $this->intMonth, $this->intYear);
   $intDayCounter = 0;
   $arrMonthAttributes = array('colspan'=>'7');
   //make our parent calendar table
   $objCalendarParent = $this->objCalendar->AddChildNode($this->objCalendar->objHTML,'', 'table');
   //let's make our header
   $objHeaderRow = $this->objCalendar->AddChildNode($objCalendarParent,'', 'tr');
   //establish our base attributes
   for($intWeekDay=0;$intWeekDay<7;$intWeekDay++){
    $strDay = date('l', strtotime("Sunday +{$intWeekDay} days"));
    $this->objCalendar->AddChildNode($objHeaderRow,$strDay, 'th',array('class'=>'weekday'));
   }
   //get the starting date
   $intMonthStart = date('N', strtotime($this->intYear.'-'.$this->intMonth.'-1'));
    $intMonthStart++;
   //avoid empty rows
   if($intMonthStart == 8)
    $intMonthStart = 1;
   //make our days now
   for($intDay=1;$intDay<($intMonthDays + $intMonthStart);$intDay++){
    $arrDateAttributes = array('onclick'=>'SelectDay(this);');
    if($intDayCounter === 0){
      //make our new week
      $objWeekRow = $this->objCalendar->AddChildNode($objCalendarParent,'', 'tr');
      $arrDateAttributes['class'] = 'calendarday weekend';
    }
    else if($intDayCounter === 6)
      $arrDateAttributes['class'] = 'calendarday  weekend';
    else if($intDay == date('j'))
      $arrDateAttributes['class'] = 'calendarday  today';
    else
        $arrDateAttributes['class'] = 'calendarday  weekday';
    $intDayCounter++;
    //reset our week now
    if($intDayCounter == 7)
        $intDayCounter = 0;
    if($intDay < $intMonthStart){
        unset($arrDateAttributes['onclick']);
        $this->objCalendar->AddChildNode($objWeekRow,'&nbsp;', 'td',$arrDateAttributes);
        continue 1;
    }
    $arrDateAttributes['id'] = ($intDay - ($intMonthStart - 1)).'-'.$this->arrCalendarProperties['calendarid'];
    //make our day now
    $this->objCalendar->AddChildNode($objWeekRow,($intDay - ($intMonthStart - 1)), 'td',$arrDateAttributes);
   }
   return TRUE;
  }

  /**
  * make the calendar controls
  * @return bool
  */
  function MakeCalendarControls(){
    $arrFormAttributes = array('method'=>'post');
    $arrTitleAttributes = array('class'=>'calendarmonth');
    $objControlForm = $this->objCalendar->AddChildNode($this->objCalendar->objHTML,'', 'form',$arrFormAttributes);
   //make our month designation
    $strThisMonth = date('F', strtotime($this->intYear.'-'.$this->intMonth.'-1'));
    //previous month
    $this->objCalendar->AddChildNode($objControlForm,'', 'input',array('type'=>'button','onclick'=>'this.form.monthaction.value=\'previous\';this.form.submit();','value'=>'Last Month','class'=>'calendarnavbuttons left'));
    //month and year title
    $this->objCalendar->AddChildNode($objControlForm,$strThisMonth.', '.$this->intYear, 'b',$arrTitleAttributes);
    //next month button
    $this->objCalendar->AddChildNode($objControlForm,'', 'input',array('type'=>'button','onclick'=>'this.form.monthaction.value=\'next\';this.form.submit();','value'=>'Next Month','class'=>'calendarnavbuttons right'));
    //hidden present month
    $this->objCalendar->AddChildNode($objControlForm,'', 'input',array('type'=>'hidden','value'=>$this->intMonth,'name'=>'presentmonth'));
    //hidden present year
    $this->objCalendar->AddChildNode($objControlForm,'', 'input',array('type'=>'hidden','value'=>$this->intYear,'name'=>'presentyear'));
    //hidden month action
    $this->objCalendar->AddChildNode($objControlForm,'', 'input',array('type'=>'hidden','value'=>'none','name'=>'monthaction'));
    return TRUE;
  }

  /**
  * create some actions for our calendar
  * @return bool
  */
  function CreateCalendarActions(){
    $arrFormAttributes = array('method'=>'post');
    $objActionsForm = $this->objCalendar->AddChildNode($this->objCalendar->objHTML,'', 'form',$arrFormAttributes);
    //compare dates
    $this->objCalendar->AddChildNode($objActionsForm,'', 'input',array('type'=>'button','onclick'=>'ExecuteAction(\'comparedates\',this.form);','value'=>'Compare dates between selected options','class'=>'calendarnavbuttons left linebreak'));
    //add dates
    $this->objCalendar->AddChildNode($objActionsForm,'', 'input',array('type'=>'button','onclick'=>'ExecuteAction(\'adddates\',this.form);','value'=>'Add dates together','class'=>'calendarnavbuttons left linebreak'));
    //add now till then ( last date )
    $this->objCalendar->AddChildNode($objActionsForm,'', 'input',array('type'=>'button','onclick'=>'ExecuteAction(\'nowtillthen\',this.form);','value'=>'Add now till then ( earliest date if more than one is selected )','class'=>'calendarnavbuttons left linebreak'));
    //hidden calendar action field
    $this->objCalendar->AddChildNode($objActionsForm,'', 'input',array('type'=>'hidden','value'=>'','name'=>'calendaraction'));
    //hidden calendar action data field
    $this->objCalendar->AddChildNode($objActionsForm,'', 'input',array('type'=>'hidden','value'=>'','name'=>'actiondata'));
    //hidden present month
    $this->objCalendar->AddChildNode($objActionsForm,'', 'input',array('type'=>'hidden','value'=>$this->intMonth,'name'=>'presentmonth'));
    //hidden present year
    $this->objCalendar->AddChildNode($objActionsForm,'', 'input',array('type'=>'hidden','value'=>$this->intYear,'name'=>'presentyear'));
    //hidden month action
    $this->objCalendar->AddChildNode($objActionsForm,'', 'input',array('type'=>'hidden','value'=>'none','name'=>'monthaction'));
    return TRUE;
  }

  /**
  * report our results for actions
  * @return bool
  */
  function AddResultsForActions(){
    $arrResultsAttributes = array('class'=>'results linebreak');
    $this->objCalendar->AddChildNode($this->objCalendar->objHTML,$this->strResults, 'div',$arrResultsAttributes);
    return TRUE;
  }

  /**
  * make a date from our existing data
  * @param $intDay
  * @return string ( date )
  */
  function MakeDateFromMembers($intDay=1){
    $strDayName = $strDay = date('M j<\s\up>S</\s\up> Y', strtotime($this->intYear.'-'.$this->intMonth.'-'.$intDay));

    return $strDayName;
  }

  /**
  * calculate the days difference
  * @param $arrDateData
  * @return bool
  */
  function CompareDates($arrDateData){
    $intLastDay = 0;
    foreach($arrDateData as $intCalendarId=>$arrDates){
      if(sizeof($arrDates) < 2){
        $this->strResults .= 'Cannot compare less than two dates!';
        continue 1;
      }
      $this->strResults .= '<h3>Calendar ID ['.$intCalendarId.']</h3>';
      $this->strResults .= '<h4>There are ['.sizeof($arrDates).'] dates selected</h4>';
      foreach($arrDates as $intIndex=>$intDate){
        if($intIndex == 0){
          $intLastDay = $intDate;
          continue 1;
        }
        $this->strResults .= 'Day '.($intIndex).' '.$this->MakeDateFromMembers($intLastDay).'  is '.($intDate - $intLastDay).' days apart from day '.($intIndex + 1).' '.$this->MakeDateFromMembers($intDate).'.<br />';
        $intLastDay = $intDate;
      }
    }
    return TRUE;
  }

  /**
  * add the dates selected
  * @param $arrDateData
  * @return bool
  */
  function AddSelectedDates($arrDateData){
    $intLastDay = 0;
    $intTotalDays = 0;
    foreach($arrDateData as $intCalendarId=>$arrDates){
      if(sizeof($arrDates) < 2){
        $this->strResults .= 'Cannot add less than two dates!';
        continue 1;
      }
      $this->strResults .= '<h3>Calendar ID ['.$intCalendarId.']</h3>';
      $this->strResults .= '<h4>There are ['.sizeof($arrDates).'] dates selected</h4>';
      foreach($arrDates as $intIndex=>$intDate){
        if($intIndex == 0){
          $intLastDay = $intDate;
          $intTotalDays += $intDate;
          continue 1;
        }
        $this->strResults .= 'Day '.($intIndex).' '.$this->MakeDateFromMembers($intLastDay).'  plus day '.($intIndex + 1).' '.$this->MakeDateFromMembers($intDate).' is '.((int)$intDate + (int)$intLastDay).'.<br />';
        $intLastDay = $intDate;
        $intTotalDays += $intDate;
      }
       $this->strResults .= 'The aggregate value is '.$intTotalDays.'.';
    }
    return TRUE;
  }

  /**
  * get the date until or from now
  * @param $arrDateData
  * @return bool
  */
  function GetTheTillNow($arrDateData){
    $intLastDay = 0;
    foreach($arrDateData as $intCalendarId=>$arrDates){
      $this->strResults .= '<h3>Calendar ID ['.$intCalendarId.']</h3>';
      $this->strResults .= '<h4>There are ['.sizeof($arrDates).'] dates selected</h4>';
      $intDateSelected = strtotime($this->intYear.'-'.$this->intMonth.'-'.$arrDates[0]);
      if($intDateSelected > time()){
        $intDifference = ($intDateSelected - time());
        $strTill = 'There are ';
        $strTail = ' until '.$this->intYear.'-'.$this->intMonth.'-'.$arrDates[0];
      }
      else{
        $intDifference = (time() - $intDateSelected);
        $strTill = 'We are beyond that date by ';
        $strTail = '';
      }
      $intYears = floor($intDifference / 31536000);
      $intWeeks = floor($intDifference / 604800);
      $intDays = ($intDifference / 86400);
      $this->strResults .= $strTill.$intYears.' years '.$strTail.'<br />';
      $this->strResults .= $strTill.$intWeeks.' weeks '.$strTail.'<br />';
      $this->strResults .= $strTill.$intDays.' days '.$strTail.'<br />';
    }
    return TRUE;
  }


}//end class
?>