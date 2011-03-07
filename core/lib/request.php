<?

function p($name, $val='')
{
  global $params;
  $elems = preg_split("/[\[\]]/", $name);
  $name = '';
  foreach($elems as $e)
  {
    if(!$e) continue;
    $name.= "['$e']";
  }
  if (eval("return isset(\$params$name);")) return eval("return \$params$name;");
  return $val;
}

function q($s, $default='')
{
  if (!array_key_exists($s,$_REQUEST)) return $default;
  return $_REQUEST[$s];
}