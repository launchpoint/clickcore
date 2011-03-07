<?

$use_ssl = false;
define('CLI',false);

/* DATABASE CONNECTION */

$database_settings = array(
  'host'=>'localhost',
  'username'=>'root',
  'password'=>'Dw22c-ps',
  'catalog'=>'jobmatch'
);





$__client = array(
  'folder_name'=>'ryankohler',
  'company_name'=>'JobMatch LLC',
);

$__projects = array(
  47=>array(
    'folder_name'=>'jobmatch_career_sites',
    'is_active'=>true,
    'name'=>'Carrer Site',
  ),
  83=>array(
    'folder_name'=>'jobmatch_domain_admin',
    'name'=>'Admin Site',
    'is_active'=>true,
  ),
);

/* SINGLE-BUILD, SINGLE-HOST CONFIGURATION */

/*
function find_build()
{
  return array(
    'project_id'=>1,
    'run_mode'=>'development',
    'db_host'=>$database_settings['host'],
    'db_username'=>$database_settings['username'],
    'db_password'=>$database_settings['password'],
    'db_catalog'=>$database_settings['catalog'],
    'http_auth_username'=>'',
    'http_auth_password'=>'',
    'core_version'=>1,
    'name'=>'dev',
    'folder_name'=>'dev',
    'default_host_id'=>null,
  );
}

*/

/* MULTI-BUILD, MULTI-HOST CONFIGURATION */

function find_build()
{
  $recs = query_assoc("select pb.*, bh.host from builds pb join build_hosts bh on pb.id = bh.build_id where bh.host = ?", $_SERVER['HTTP_HOST']);
  if(count($recs)==0)
  {
    $matches = array();
    if(preg_match('/(?:^|.+\.)b(\d+)\.painlessprogramming\.com$/', $_SERVER['HTTP_HOST'], $matches))
    {
      $build_id = $matches[1];
      $recs = query_assoc("select pb.* from builds pb join active_projects p on pb.project_id = p.id where pb.id = ?", $build_id);
    }
  }
  if(count($recs)==0)
  {
    die("Sorry, but {$_SERVER['HTTP_HOST']} is either inactive or not a valid project. Did you mistype the URL?");
  }
  
  return $recs[0];
}
