<?

function startof($s, $chop)
{
  if (!is_numeric($chop)) $chop = strlen($chop);
  return substr($s, 0, strlen($s)-$chop);
}


function endof($s,$n)
{
  return substr($s, -$n, $n);
}
function spacify($camel, $glue = ' ') {
    return preg_replace( '/([a-z0-9])([A-Z])/', "$1$glue$2", $camel );
}


   
function truncate ($str, $length=30, $trailing='...')
{
/*
** $str -String to truncate
** $length - length to truncate
** $trailing - the trailing character, default: "..."
*/
      // take off chars for the trailing
      $length-=mb_strlen($trailing);
      if (mb_strlen($str)> $length)
      {
         // string exceeded length, truncate and add trailing dots
         return mb_substr($str,0,$length).$trailing;
      }
      else
      {
         // string was already short enough, return the string
         $res = $str;
      }
 
      return $res;
}


function endswith()
{
  $args = func_get_args();
  $s = array_shift($args);
  foreach($args as $sub)
  {
    if (substr($s, strlen($sub)*-1) == $sub)
    {
      return true;
    }
  }
  return false;
}

function startswith($s,$sub)
{
  return substr($s,0, strlen($sub))==$sub;
}