<?


function merge_bottom(&$target, $src, $name)
{
	if (is_array($src))
	{
		foreach($src as $k=>$v)
		{
			if (is_array($v))
			{
				merge_bottom($target[$k], $v, $name);
			} else {
				$target[$k][$name] = $v;
			}
		}
	} else {
		$target[$name] = $src;
	}
}

function gather($coll, $name)
{
  $arr=array();
  foreach($coll as $child_coll)
  {
    if (!array_key_exists($name, $child_coll)) continue;
    $arr = array_merge($arr, $child_coll[$name]);
  }
  return $arr;
}

function array_wrap(&$arr, $wrap_with)
{
  foreach($arr as $k=>$v)
  {
    $arr[$k] = $wrap_with . $v . $wrap_with;
  }
  return $arr;
}

function wrap_and_join(&$arr, $wrap_with, $join_with)
{
  array_wrap($arr, $wrap_with);
  return join($arr, $join_with);
}

function in()
{
  $haystack = func_get_args();
  $needle = array_shift($haystack);
  return array_search($needle, $haystack)!==FALSE;
}

function &object_lookup(&$objs)
{
  $obj_lookup=array();
  for($i=0;$i<count($objs);$i++)
  {
    $obj_lookup[$objs[$i]->id] = &$objs[$i];
  }
  return $obj_lookup;
}

function keys($arr)
{
  $ret = array();
  if (is_array($arr))
  {
    foreach($arr as $k=>$v)
    {
      $n = $k;
      if (is_numeric($k)) $n = $v;
      $ret[] = $n;
    }
  } else {
    $ret[] = $arr;
  }
  return $ret;
}

/*
  Return an array of values of the corresponding object property.
*/
function collect($objs, $prop, $assoc_name=null)
{
  $ret = array();
  foreach($objs as $o)
  {
    if (is_object($o))
    {
      if ($o->$prop && !isset($o->$assoc_name)) $ret[] = $o->$prop;
    }
    if (is_array($o))
    {
      if (array_key_exists($prop, $o)) $ret[] = $o[$prop];
    }
  }
  $ret = array_unique($ret);
  return $ret;
}

function merge ($arr,$ins)
{
  if(is_array($arr))
  {
    if(is_array($ins)) foreach($ins as $k=>$v)
    {
      if(isset($arr[$k])&&is_array($v)&&is_array($arr[$k]))
      {
        $arr[$k] = merge($arr[$k],$v);
      }
      else 
      {
        if (is_numeric($k))
        {
          if (array_search($v,$arr)===FALSE) $arr[] = $v;
        } else {
          $arr[$k] = $v;
        }
      }
    }
  }
  elseif(!is_array($arr)&&(strlen($arr)==0||$arr==0))
  {
    $arr=$ins;
  }
  return($arr);
} 

function array_md5($arr)
{
  $vals=array();
  foreach($arr as $k=>$v)
  {
    $vals[]=$k;
    if (is_array($v))
    {
      $v = array_md5($v);
    }
    if (is_object($v))
    {
      $v = spl_object_hash($v);
    }
    $vals[] = $v;
  }
  sort($vals);
  $s = join('|',$vals);
  $md5 = md5($s);
  return $md5;
}

function sort_by($field, &$arr, $sorting=SORT_ASC, $case_insensitive=true){
    if(is_array($arr) && (count($arr)>0) && ( ( is_array($arr[0]) && isset($arr[0][$field]) ) || ( is_object($arr[0]) && isset($arr[0]->$field) ) ) ){
        if($case_insensitive==true) $strcmp_fn = "strnatcasecmp";
        else $strcmp_fn = "strnatcmp";

        if($sorting==SORT_ASC){
            $fn = create_function('$a,$b', '
                if(is_object($a) && is_object($b)){
                    return '.$strcmp_fn.'($a->'.$field.', $b->'.$field.');
                }else if(is_array($a) && is_array($b)){
                    return '.$strcmp_fn.'($a["'.$field.'"], $b["'.$field.'"]);
                }else return 0;
            ');
        }else{
            $fn = create_function('$a,$b', '
                if(is_object($a) && is_object($b)){
                    return '.$strcmp_fn.'($b->'.$field.', $a->'.$field.');
                }else if(is_array($a) && is_array($b)){
                    return '.$strcmp_fn.'($b["'.$field.'"], $a["'.$field.'"]);
                }else return 0;
            ');
        }
        usort($arr, $fn);
        return true;
    }else{
        return false;
    }
}
