<?
$__test_data_scope = '';
$__test_data = null;

function data_scope($scope=null)
{
  global $__test_data_scope;
  if (!$scope) $__test_data_scope='';
  $__test_data_scope = $scope;
}

function data($name=null)
{
  global $__test_data;
  if (!$name) return $__test_data;
  return $__test_data[$name];
}

function request($url, $request_params, $expected_code = 200)
{
  global $request_path;
  global $full_request_path;
  global $subdomain;
  global $params;
  global $event_table,$route_controlled_hooks;
  global $result_code;
  global $__test_data_scope;
  global $__test_data;
  global $use_ssl;
  global $flash;
  $flash = array();
  $use_ssl=false;
  if(!$request_params) $request_params = array();
  $event_table = array();
  $route_controlled_hooks = null;
  $result_code = 200;
  
  $_SERVER['REQUEST_URI'] = $url;
  $_SERVER['QUERY_STRING'] = $request_params;
  $_REQUEST = $request_params;
  
  $params = ($request_params) ? $request_params : array();
  $request_path = substr($url,1);
  ob_start();
  $data = array();
  try
  {
    event('kernel_start');
    require(dirname(__FILE__).'/../request.php');
    event('kernel_done');
  } catch(RedirectException $r)
  {
  }
  $o = ob_get_contents();
  ob_end_clean();
  $__test_data = eval("return \$data{$__test_data_scope};");
  ae($result_code, $expected_code, $url, array($flash, $__test_data));
  return $__test_data;
}

function get_url($path, $params=array(), $expected_code=200)
{
  $parts = parse_url($path);
  if (array_key_exists('query', $parts))
  {
    $pairs = split('&',$parts['query']);
    foreach($pairs as $pair)
    {
      list($k,$v) = split('=',$pair);
      $params[$k] = $v;
    }
  }
  return request($parts['path'], $params, $expected_code);
}

function get($url, $params=array(), $expected_code=200)
{
  $_POST = array();
  return get_url($url, $params, $expected_code);
}

function post($url, $params=array(), $expected_code=200)
{
  $_POST['x'] = 1;
  return get_url($url, $params, $expected_code);
}

$__assertion_count = 0;

function a($v, $msg='', $data = null)
{
  global $__assertion_count;
  $__assertion_count++;
  eprint('.');
  if (!$v) click_error("Assertion failed: $msg", $data);
}


function stringval($v)
{
  switch(gettype($v))
  {
    case 'object':
      return 'Object';
    case 'array':
      return 'Array';
  }
  return "$v";
}

function ae($x,$y,$msg='', $data = null)
{
  a($x==$y, $msg . " (".stringval($x)." <> ".stringval($y).")", $data);
}

function af($x,$msg='', $data = null)
{
  a($x==false, $msg . " (".stringval($x)." is true)", $data);
}

function ace($arr, $count, $msg='', $data = null)
{
  ae(count($arr),$count,$msg, $data);
}

function alte($x, $y, $msg='', $data = null)
{
  a($x<=$y, $msg . " (".stringval($x)." > ".stringval($y).")", $data);
}

function agte($x, $y, $msg='', $data=null)
{
  a($x>=$y, $msg . " (".stringval($x)." < ".stringval($y).")", $data);
}

function agt($x, $y, $msg='', $data=null)
{
  a($x>$y, $msg . " (".stringval($x)." <= ".stringval($y).")", $data);
}