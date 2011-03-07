<?

$fname = dirname(__FILE__)."/../../config.php";
if(file_exists($fname))
{
  require($fname);
}

if(!defined('CLI'))
{
  define('CLI',isset($_ENV['SSH_CLIENT']));
}

$start = microtime(true);

require('load_and_connect.php');

$__build=find_build();


date_default_timezone_set('UTC');

$run_mode = strtolower($__build['run_mode']);

if(isset($__projects))
{
  $__project = $__projects[$__build['project_id']];
} else {
  $recs = query_assoc("select * from projects where id = ?", $__build['project_id']);
  $__project = $recs[0];
}

if(!isset($__client))
{
  $recs = query_assoc("select * from users where id = ?", $__project['client_id']);
  $__client = $recs[0];
}

$domain = $__build['host'];


define('APP_DOMAIN', $domain);

if(!CLI)
{
  if($__build['http_auth_username'])
  {
    if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER']!=$__build['http_auth_username'] || $_SERVER['PHP_AUTH_PW']!=$__build['http_auth_password'])
    {
      header('WWW-Authenticate: Basic realm="$domain Authentication Required"');
      header('HTTP/1.0 401 Unauthorized');
      echo 'You are not authorized to view this area.';
      exit;
    }
  }
}

$modules = array();

require("constants.php");

foreach(glob(dirname(__FILE__)."/lib/*.php") as $lib)
{
  require_once($lib);
}

ensure_writable_folder(TEMP_FPATH);
ensure_writable_folder(CACHE_FPATH);
ensure_writable_folder(CODEGEN_CACHE_FPATH);
ensure_writable_folder(CODEGEN_CLASSES_CACHE_FPATH);


$queries=array();
add_global('queries');

$stash = (object)array();
add_global('stash');

$__core = array();
add_global('__core');

register_shutdown_function('click_save_session');

if(!session_id()) session_start();

if(!isset($_SESSION['__core'])) $_SESSION['__core'] = array();
if(!isset($_SESSION['__core']['current_session_id'])) $_SESSION['__core']['current_session_id'] = uniqid();
if(!isset($_SESSION['__core']['sessions'])) $_SESSION['__core']['sessions'] = array();
if(!isset($_SESSION['__core']['sessions'][$_SESSION['__core']['current_session_id']])) $_SESSION['__core']['sessions'][$_SESSION['__core']['current_session_id']] = array();
if(isset($_GET['__session_id'])) $_SESSION['__core']['current_session_id'] = $_GET['__session_id'];
if(!isset($_SESSION['__core']['sessions'][$_SESSION['__core']['current_session_id']])) $_SESSION['__core']['sessions'][$_SESSION['__core']['current_session_id']] = array();
$__core['session'] = $_SESSION['__core']['sessions'][$_SESSION['__core']['current_session_id']];
if(!isset($__core['session']['run_mode'])) $__core['session']['run_mode'] = $run_mode;

if(isset($_GET['__run_mode']))
{
  $__core['session']['run_mode'] = $_GET['__run_mode'];
}

$run_mode = $__core['session']['run_mode'];

$f = BUILD_FPATH."/modules.php";
if(file_exists($f)) require($f);
$f = BUILD_FPATH."/{$run_mode}_modules.php";
if(file_exists($f)) require($f);

$database_settings = array(
  'host'=>$__build['db_host'],
  'username'=>$__build['db_username'],
  'password'=>$__build['db_password'],
  'catalog'=>$__build['db_catalog']
);

foreach( glob(BUILD_FPATH."/config/*.php") as $path)
{
  require_once($path);
}


if(CLI)
{
  $database_settings['catalog'] .= '_test';
}

if(!defined('COMPANY_NAME')) define('COMPANY_NAME', $__client['company_name']);
if(!defined('APP_NAME')) define('APP_NAME', $__project['name']);

$dbh = db_connect($database_settings);
$app_dbh = $dbh;

if($run_mode == RUN_MODE_TEST)
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

if (q('__restart') || in($run_mode,RUN_MODE_DEVELOPMENT,RUN_MODE_TEST))
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

