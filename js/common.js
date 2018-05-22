/**
* collect data about our calendar
* @param objThis
* @return true
*/
var arrCalendarObjects = [];
function SelectDay(objThis){
  if(typeof objThis.id == 'undefined')
    return true;
  var arrCalendarParts = objThis.id.split('-');
  if(typeof arrCalendarObjects[arrCalendarParts[1]] == 'undefined'){
      arrCalendarObjects[arrCalendarParts[1]] = [];
  }
  if(typeof arrCalendarObjects[arrCalendarParts[1]][arrCalendarParts[0]] != 'undefined'){
    //remove it
    RemoveCalendarEntry(arrCalendarParts[1],arrCalendarParts[0]);
  }
  else{
    //make it now
    arrCalendarObjects[arrCalendarParts[1]][arrCalendarParts[0]] = [];
    arrCalendarObjects[arrCalendarParts[1]][arrCalendarParts[0]]['class'] = objThis.className;
    arrCalendarObjects[arrCalendarParts[1]][arrCalendarParts[0]]['id'] = objThis.id;
    objThis.className = 'calendarday selectedday';
  }
  return true;
}

/**
* remove an entry from a calendar
* @param varCalendar
* @param intDay
* @return true
*/
function RemoveCalendarEntry(varCalendar,intDay){
  var arrNewCalendars = [];
  for(i in arrCalendarObjects){
    arrNewCalendars[i] = [];
    if(i == varCalendar){
      for(n in arrCalendarObjects[i]){
        if(n != intDay){
          arrNewCalendars[i][n] = arrCalendarObjects[i][n];
        }
        else{
          var objElement = document.getElementById(arrCalendarObjects[i][n]['id']);
          objElement.className = 'calendarday '+arrCalendarObjects[i][n]['class'];
        }
      }
    }
  }
  arrCalendarObjects = arrNewCalendars;
  return true;
}

/**
* given an action perform actions
* @param strAction
* @return bool
*/
function ExecuteAction(strAction,objThisForm){
  objThisForm.calendaraction.value = strAction;
  objThisForm.actiondata.value = JSON.stringify(arrCalendarObjects);
  objThisForm.submit();
  return true;
}