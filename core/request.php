<?

if (get_magic_quotes_gpc())
{
  $_POST = array_map('stripslashes_deep', $_POST);
  $_GET = array_map('stripslashes_deep', $_GET);
  $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
  $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

$path = substr($_SERVER['REQUEST_URI'], strlen('/'));
$parts = split('\?', $path);
$full_request_path = trim($_SERVER['REQUEST_URI'],"/");
$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
$current_url = "{$protocol}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$request_path = trim($parts[0],"/");
$params = $_REQUEST;

$host = $_SERVER['HTTP_HOST'];
$parts = split('\.', $host);

$domain = join('.', array_slice($parts, -2, 2));

$subdomain = join('.', array_slice($parts, 0, count($parts)-2));

$querystring = $_SERVER['QUERY_STRING'];

$__core = array_merge($__core, array(
  'domain'=>$domain,
  'subdomain'=>$subdomain,
  'host'=>$host,
  'querystring'=>$querystring,
  'request_path'=>$request_path,
  'params'=>$params,
  'current_url'=>$current_url,
  'protocol'=>$protocol,
));


if (strpos($subdomain, '_')) trigger_error("Subdomains with _ are not supported. They break sessions in IE7/8, possibly others.", E_USER_ERROR);

$fields = array('name', 'type', 'tmp_name', 'error', 'size');
foreach($_FILES as $k=>$v) {
  foreach($fields as $field) {
    if (count($v['name'])==0) break;
    merge_bottom($params[$k], $v[$field], $field);
  }
}

event('begin_request');
event('before_render');
ob_start();
$data = event('render');
$rendered_page = ob_get_contents();
ob_end_flush();
event('after_render');

