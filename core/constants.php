<?


define('CORE_FPATH', normalize_path(dirname(__FILE__)));

define('ROOT_FPATH', normalize_path(CORE_FPATH.'/..'));
define('GLOBAL_MODULES_FPATH', ROOT_FPATH."/modules");
if(!isset($app_prefix)) $app_prefix = '';
if ($app_prefix)
{
  if(!endswith(ROOT_FPATH, $app_prefix)) die(ROOT_FPATH . " must end with app_prefix $app_prefix");
  define('HOME_FPATH', normalize_path(substr(ROOT_FPATH, 0, -strlen($app_prefix)-1)));
} else {
  define('HOME_FPATH', ROOT_FPATH);
}
$root_vpath = substr(ROOT_FPATH, strlen(HOME_FPATH));
$root_vpath = "/".trim($root_vpath,'/');
$root_vpath = trim($root_vpath, '/');
define('ROOT_VPATH', $root_vpath);

define('APPS_VPATH', ROOT_VPATH."/apps");
define('APPS_FPATH', ROOT_FPATH."/apps");

$keypath = strtolower($__click['build']['path']);
define('BUILD_FPATH', strtolower(normalize_path(ROOT_FPATH."/apps/$keypath")));
define('BUILD_VPATH', strtolower(normalize_path(ROOT_VPATH."/apps/$keypath")));

define('LOCAL_MODULES_FPATH', BUILD_FPATH."/modules");

define('DATA_FPATH', BUILD_FPATH."/data");
define('DATA_VPATH', "/".trim(BUILD_VPATH, '/')."/data");

define('TEMP_FPATH', ROOT_FPATH."/tmp/$keypath");
define('TEMP_VPATH', ROOT_VPATH."/tmp/$keypath");

define('CACHE_FPATH', ROOT_FPATH."/cache/$keypath");
define('CACHE_VPATH', trim(ROOT_VPATH, '/')."/cache/$keypath");

define('KERNEL_CACHE_FPATH', CACHE_FPATH.'/kernel');
define('KERNEL_CACHE_VPATH', CACHE_VPATH.'/kernel');
define('CODEGEN_CACHE_FPATH', KERNEL_CACHE_FPATH."/codegen");
define('CODEGEN_CLASSES_CACHE_FPATH', CODEGEN_CACHE_FPATH."/classes");

define('RUN_MODE_PRODUCTION', 'production');
define('RUN_MODE_DEVELOPMENT', 'development');
define('RUN_MODE_STAGING', 'staging');
define('RUN_MODE_TEST', 'test');

if(!isset($validate_request_params)) $__click['validate_request_params'] = true;
if(!isset($use_ssl)) $__click['use_ssl'] = true;

$__click['app_routing_prefix'] = '';
if (ROOT_VPATH)
{
  $__click['app_routing_prefix'] = preg_quote(ROOT_VPATH, '/') . "(?:$|\\/)";
}

