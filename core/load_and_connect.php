<?

foreach(glob(dirname(__FILE__)."/lib/*.php") as $lib)
{
  require_once($lib);
}


global $dbh;
$dbh = db_connect($database_settings);
$root_dbh = $dbh;
