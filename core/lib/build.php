<?

function find_build()
{
  global $__click;
  
  foreach($__click['sites'] as $pat=>$info)
  {
    $pat = "/{$pat}/";
    $pat = preg_replace("/\*/", ".*", $pat);
    if(preg_match($pat, $_SERVER['HTTP_HOST'])==0) continue;
    return $info;
  }
  die("Build not found or inactive.");
}