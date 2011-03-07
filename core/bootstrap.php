<?

$start = microtime(true);

$fname = dirname(__FILE__)."/../config.php";
require($fname);

foreach(glob(dirname(__FILE__)."/lib/*.php") as $lib)
{
  require_once($lib);
}

define('CLI',isset($_ENV['SSH_CLIENT']));

$__click = array();
add_global('__click');


$fname = dirname(__FILE__)."/../sites.php";
require($fname);
$__click['build'] = find_build();

if(!CLI)
{
  if($__click['build']['http_auth']['username'])
  {
    if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER']!=$__click['build']['http_auth']['username'] || $_SERVER['PHP_AUTH_PW']!=$__click['build']['http_auth']['password'])
    {
      header('WWW-Authenticate: Basic realm="Authentication Required"');
      header('HTTP/1.0 401 Unauthorized');
      echo 'You are not authorized to view this area.';
      exit;
    }
  }
}

if(isset($__click['build']['database']['host']) && $__click['build']['database']['host']=='') $__click['build']['database']=array();
if($__click['build']['database'])
{
  $__click['dbh'] = db_connect($__click['build']['database']);
  $__click['root_dbh'] = $__click['dbh'];
  $__click['queries']=array();
}

require('constants.php');

date_default_timezone_set('UTC');

$__click['run_mode'] = strtolower($__click['build']['run_mode']);


$__click['domain'] = $_SERVER['HTTP_HOST'];

$modules = array();

ensure_writable_folder(TEMP_FPATH);
ensure_writable_folder(CACHE_FPATH);
ensure_writable_folder(CODEGEN_CACHE_FPATH);
ensure_writable_folder(CODEGEN_CLASSES_CACHE_FPATH);


$__click['stash'] = (object)array();

register_shutdown_function('click_save_session');

if(!session_id()) session_start();

if(!isset($_SESSION['__core'])) $_SESSION['__core'] = array();
if(!isset($_SESSION['__core']['current_session_id'])) $_SESSION['__core']['current_session_id'] = uniqid();
if(!isset($_SESSION['__core']['sessions'])) $_SESSION['__core']['sessions'] = array();
if(!isset($_SESSION['__core']['sessions'][$_SESSION['__core']['current_session_id']])) $_SESSION['__core']['sessions'][$_SESSION['__core']['current_session_id']] = array();
if(isset($_GET['__session_id'])) $_SESSION['__core']['current_session_id'] = $_GET['__session_id'];
if(!isset($_SESSION['__core']['sessions'][$_SESSION['__core']['current_session_id']])) $_SESSION['__core']['sessions'][$_SESSION['__core']['current_session_id']] = array();
$__click['session'] = $_SESSION['__core']['sessions'][$_SESSION['__core']['current_session_id']];
if(!isset($__click['session']['run_mode'])) $__click['session']['run_mode'] = $__click['run_mode'];

if(isset($_GET['__run_mode']))
{
  $__click['session']['run_mode'] = $_GET['__run_mode'];
}

$__click['run_mode'] = $__click['session']['run_mode'];

$f = BUILD_FPATH."/modules.php";
if(file_exists($f)) require($f);
$f = BUILD_FPATH."/{$__click['run_mode']}_modules.php";
if(file_exists($f)) require($f);

foreach( glob(BUILD_FPATH."/config/*.php") as $path)
{
  require_once($path);
}


if($__click['run_mode'] == RUN_MODE_TEST)
{
  if(!endswith($database_settings['catalog'], "_test")) click_error("In test mode, but database is not a test database");
  $recs = query_assoc("show tables;");
  foreach($recs as $rec)
  {
    $table_name = $rec["Tables_in_{$database_settings['catalog']}"];
    query("drop table if exists `$table_name`");
    query("drop view if exists `$table_name`");
  }
}


if (!file_exists(KERNEL_CACHE_FPATH."/stub.php")) $_GET['__restart'] = true;

if (q('__restart') || in($__click['run_mode'],RUN_MODE_DEVELOPMENT,RUN_MODE_TEST))
{
  lock('bootstrap');
  if(q('__restart')==2) clear_all_cache();
  require_once("codegen_kernel_stub.php");
  unlock('bootstrap');
} else {
  $do_codegen=false;
  require(KERNEL_CACHE_FPATH."/stub.php");
}

try
{
    event('kernel_start');
    event('kernel_run');
    event('kernel_done');
} catch(RedirectException $r)
{
}
$end = microtime(true);

