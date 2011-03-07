<?


function command_url($command_name, $args=array(), $namespace=null)
{
  global $full_request_path;
  $args['cmd'] = $command_name;
  if($namespace) $args = array($namespace=>$args);
  return build_url($full_request_path, 0, array($args), false);
}


function build_url($path, $arg_count, $args, $is_ssl_required=false)
{
  global $app_prefix, $use_ssl;
  $qs = array();
  if (count($args)>0 && is_array($args[count($args)-1])) $qs = array_pop($args);
  if (count($args) > $arg_count) click_error("Wrong number of arguments", array($path, $arg_count, $args));
  $parts = explode('?', $path);
  $extra_args = array();
  if(count($parts)>1)
  {
    $path = $parts[0];
    parse_str($parts[1], $extra_args);
    $qs = array_merge($extra_args, $qs);
    foreach($qs as $k=>$v)
    {
      if($v===null) unset($qs[$k]);
    }
  }
  if(count($_SESSION['__core']['sessions'])>1)
  {
    $qs['__session_id'] = $_SESSION['__core']['current_session_id'];
  }
  if(isset($_GET['__session_id']))
  {
    $qs['__session_id'] = $_GET['__session_id'];
  }
  $path = "/$path";
  if (ROOT_VPATH)
  {
    $path = ROOT_VPATH.$path;
  }
  
  for($i=0;$i<count($args);$i++)
  {
    $val = $args[$i];
    if (is_object($val))
    {
      preg_match("/:([^\/]+)/", $path, $matches);
      $k = $matches[1];
      $val = $val->$k;
    }
    $path = preg_replace("/:[^\/]+/", u($val), $path, 1);
  }
  
  if (count($args)>0)
  {
    $last_arg = $args[$i-1];
    if ($last_arg != null)
    {
      while(true)
      {
        $val = $last_arg;
        preg_match("/:([^\/]+)/", $path, $matches);
        if (count($matches)==0) break;
        $k = $matches[1];
        if(is_object($val))
        {
          $val = $val->$k;
        }
        $path = preg_replace("/:[^\/]+/", u($val), $path, 1);
      }
    }
  }
  if (count($qs)>0)
  {
    $path .= "?".http_build_query($qs);
  }
  $protocol = ($is_ssl_required && $use_ssl) ? 'https' : 'http';
  return $protocol . "://".$_SERVER['HTTP_HOST'].$path;
}
      
      
$__routes = array();

function map($event_name, $path, $routed_event_name = null, $url_generator_name=null, $is_ssl_required = false)
{
  global $code, $__click, $__routes;
  $parts = explode("/", $path);
  $keys=array();
  foreach($parts as &$part)
  {
    if (startswith($part, ':'))
    {
      $url_part = '?';
      $key = substr($part, 1);
      $keys[] = $key;
      $part = "(?P<$key>[^\/]+?)";
    } elseif ($part=='*') {
      $part = "(.*?)";
    } else {
      $part = preg_quote($part);
    }
  }
  if($url_generator_name) 
  {
    $__routes[$url_generator_name] = array(
      'event_name'=>$event_name,
      'path'=>$path,
      'routed_event_name'=>$routed_event_name,
      'is_ssl_required'=>$is_ssl_required,
      'keys'=>$keys,
    );
  }
  $pattern = join("\\/",$parts);
  $pattern = $__click['app_routing_prefix'] . $pattern;
  $pattern = "/^$pattern\$/";
  map_raw($event_name, $pattern, $routed_event_name);
  if ($url_generator_name)
  {
    $url_name = $url_generator_name."_url";
    $arg_count = count($keys);
    $is_ssl_required = $is_ssl_required ? 'true' : 'false';
    $func = <<<FUNC
      function $url_name()
      {
        \$args = func_get_args();
        return build_url('$path', $arg_count, \$args, $is_ssl_required);
      }
FUNC;
    $code[] = $func;
  }
  
  
}

function map_raw($event_name, $regex, $routed_event_name=null)
{
  global $manifest;
  if (!$routed_event_name) $routed_event_name=$event_name;
  $manifest['routes'][$event_name][$routed_event_name][] = $regex;
}

function home_url()
{
  return build_url('', 0, array(), false);
}

function route_matches()
{
  global $request_path;
  
  $pats = array();
  foreach(func_get_args() as $route)
  {
    $pats[] = route_to_regex($route);
  }
  foreach($pats as $pat)
  {
    $matches = array();
    if (preg_match($pat, $request_path, $matches) > 0) return $matches;
  }
  return array();
}


function route_to_regex($path)
{
  global $code, $__click;
  $parts = explode("/", $path);
  $keys=array();
  foreach($parts as &$part)
  {
    if (startswith($part, ':'))
    {
      $url_part = '?';
      $key = substr($part, 1);
      $keys[] = $key;
      $part = "(?P<$key>[^\/]+?)";
    } elseif ($part=='*') {
      $part = "(.*?)";
    } else {
      $part = preg_quote($part);
    }
  }
  $pattern = join("\\/",$parts);
  $pattern = $__click['app_routing_prefix'] . $pattern;
  $pattern = "/^$pattern\$/";
  return $pattern;
}
