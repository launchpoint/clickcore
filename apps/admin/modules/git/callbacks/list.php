<?

chdir(ROOT_FPATH);

exec("find . -name '.git'", $dirs);

$status = array();
foreach($dirs as $gpath)
{
  $fpath = dirname($gpath);
  chdir(ROOT_FPATH."/{$fpath}");
  exec("git status", $output);
  $status[$fpath] = $output;
  $output='';
}

