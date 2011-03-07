<?

define('ONE_MINUTE', 60);
define('ONE_HOUR', 60*ONE_MINUTE);
define('ONE_DAY', 24*ONE_HOUR);
define('ONE_WEEK', 7*ONE_DAY);

function date_at_timezone($format, $locale, $timestamp=null){
   
    if(is_null($timestamp)) $timestamp = time();
   
    //Prepare to calculate the time zone offset
    $current = time();
   
    //Switch to new time zone locale
    $tz = date_default_timezone_get();
    date_default_timezone_set($locale);
   
    //Calculate the offset
    $offset = time() - $current;
   
    //Get the date in the new locale
    $output = date($format, $timestamp - $offset);
   
    //Restore the previous time zone
    date_default_timezone_set($tz);
   
    return $output;
   
}


function business_date_from($start_date, $business_days)
{
  for($i=0;$i<$business_days;$i++)
  {
    $start_date += (60*60*24);
    $start_date = advance_to_weekday($start_date);
  }
  return $start_date;
}

function advance_to_weekday($dt)
{
  $parts = getdate($dt);
  while(is_weekend_day($dt))
  {
    $dt += (60*60*24);
    $parts = getdate($dt);
  }
  return $dt;
}

function is_weekend_day($dt)
{
  $parts = getdate($dt);
  return $parts['wday']==0 || $parts['wday']==6;
}

function is_weekday($dt)
{
  return !is_weekend_day($dt);
}

function is_today($dt)
{
  $parts = getdate($dt);
  $today_parts = getdate();
  return $parts['year'] == $today_parts['year'] && $parts['yday'] == $today_parts['yday'];
}

function is_past($dt)
{
  $parts = getdate($dt);
  $today_parts = getdate();
  return $parts['year'] < $today_parts['year'] || ($parts['year'] == $today_parts['year'] && $parts['yday'] < $today_parts['yday']);
}

function is_future($dt)
{
  return !is_today($dt) && !is_past($dt);
}

function is_same_day($dt1, $dt2)
{
  $p1 = getdate($dt1);
  $p2 = getdate($dt2);
  return $p1['year']==$p2['year'] && $p1['yday']==$p2['yday'];
}

function click_date_format($timestamp, $include_time=false)
{
  if (!$timestamp) return null;
  $s = 'm-d-Y';
  if($include_time) $s .= ' h:i A';
  return date($s, $timestamp);
}

function click_time_format($timestamp, $include_date = false)
{
  if (!$timestamp) return null;
  $s = 'h:i A';
  if($include_date) $s = 'm-d-Y ' . $s;
  return date($s, $timestamp);
}

function beginning_of_week($dt=null)
{
  if(!$dt) $dt=time();
  $parts = getdate($dt);
  $dt = $dt - (($parts['wday']-1) * ONE_DAY);
  $parts = getdate($dt);
  return mktime(0,0,0,$parts['mon'], $parts['mday'], $parts['year']);
}

function end_of_week($dt=null)
{
  if(!$dt) $dt=time();
  $parts = getdate($dt);
  $d = $parts['wday'];
  if ($d==0) $d=7;
  $dt = $dt + ( (7-$d) * ONE_DAY);
  $parts = getdate($dt);
  return mktime(23,59,59,$parts['mon'], $parts['mday'], $parts['year']);
}

function beginning_of_month($dt=null)
{
  if(!$dt) $dt=time();
  $parts = getdate($dt);
  $parts['mday'] = 1;
  return mktime(0,0,0,$parts['mon'], $parts['mday'], $parts['year']);
}

function end_of_month($dt=null)
{
  if(!$dt) $dt=time();
  $parts = getdate($dt);
  $parts['mday'] = date('t', $dt);
  return mktime(23,59,59,$parts['mon'], $parts['mday'], $parts['year']);
}

function last_month($dt=null)
{
  if(!$dt) $dt=time();
  $dt = strtotime('last month', $dt);
  return $dt;
}

function next_month($dt=null)
{
  if(!$dt) $dt=time();
  $dt = strtotime('next month', $dt);
  return $dt;
}


function beginning_of_day($dt=null)
{
  if(!$dt) $dt=time();
  $parts = getdate($dt);
  return mktime(0,0,0,$parts['mon'], $parts['mday'], $parts['year']);
}

function end_of_day($dt=null)
{
  if(!$dt) $dt=time();
  $parts = getdate($dt);
  return mktime(23,59,59,$parts['mon'], $parts['mday'], $parts['year']);
}

function business_days_later($dt, $days)
{
  while($days>0)
  {
    $dt += ONE_DAY;
    while(is_weekend_day($dt))
    {
      $dt+=ONE_DAY;
    }
    $days--;
  }
  return $dt;
}

function timezone_offset_set($offset)
{
  $timezones = array(
    '-12'=>'Pacific/Kwajalein',
    '-11'=>'Pacific/Samoa',
    '-10'=>'Pacific/Honolulu',
    '-9'=>'America/Juneau',
    '-8'=>'America/Los_Angeles',
    '-7'=>'America/Denver',
    '-6'=>'America/Mexico_City',
    '-5'=>'America/New_York',
    '-4'=>'America/Caracas',
    '-3.5'=>'America/St_Johns',
    '-3'=>'America/Argentina/Buenos_Aires',
    '-2'=>'Atlantic/Azores',// no cities here so just picking an hour ahead
    '-1'=>'Atlantic/Azores',
    '0'=>'Europe/London',
    '1'=>'Europe/Paris',
    '2'=>'Europe/Helsinki',
    '3'=>'Europe/Moscow',
    '3.5'=>'Asia/Tehran',
    '4'=>'Asia/Baku',
    '4.5'=>'Asia/Kabul',
    '5'=>'Asia/Karachi',
    '5.5'=>'Asia/Calcutta',
    '6'=>'Asia/Colombo',
    '7'=>'Asia/Bangkok',
    '8'=>'Asia/Singapore',
    '9'=>'Asia/Tokyo',
    '9.5'=>'Australia/Darwin',
    '10'=>'Pacific/Guam',
    '11'=>'Asia/Magadan',
    '12'=>'Asia/Kamchatka'
  );
  date_default_timezone_set($timezones[$offset]);
}

