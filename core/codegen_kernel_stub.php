<?


load_manifests();
$code = array();
$event_injections = array();

ensure_writable_folder(KERNEL_CACHE_FPATH);
foreach($manifests as $module_name=>&$manifest)
{
  $this_module_fpath = $manifest['path'];
  $this_module_vpath = ftov($this_module_fpath);

  $code[] = "\${$module_name}_module_vpath = '$this_module_vpath';";
  $code[] = "\${$module_name}_module_fpath = '$this_module_fpath';";
  
  $pfx = strtoupper($module_name) ."_";
  $code[] = "define('{$pfx}FPATH', \${$module_name}_module_fpath);";
  $code[] = "define('{$pfx}VPATH', \${$module_name}_module_vpath);";
  
  $cachefpath = CACHE_FPATH."/{$module_name}";
  $cachevpath = CACHE_VPATH."/{$module_name}";

  ensure_writable_folder($cachefpath);

  $code[] = "define('{$pfx}CACHE_FPATH', '$cachefpath');"; 
  $code[] = "define('{$pfx}CACHE_VPATH', '$cachevpath');";
  ensure_writable_folder($cachefpath);
  
  $v = "{$module_name}_settings";
  if(isset($$v)) $code[] = "add_global('$v');";
  
  $code[] = "\$this_module_name = '$module_name';";
  $code[] = "\$this_module_vpath = '$this_module_vpath';";
  $code[] = "\$this_module_fpath = '$this_module_fpath';";
  if (file_exists("$this_module_fpath/constants.php")) 
  {
    $code[] = "require_once('$this_module_fpath/constants.php');";
  }
  if (file_exists("$this_module_fpath/routes.php"))
  {
    require_once("$this_module_fpath/routes.php");
  }
  if (file_exists("$this_module_fpath/lib"))
  {
    foreach(glob("$this_module_fpath/lib/*.php") as $lib_path)
    {
      $code[] = "require_once('$lib_path');";
    }
  }
  if (file_exists("$this_module_fpath/codegen.php"))
  {
    $code[] = <<<CODE
    if (\$do_codegen)
    {
      \$codegen = array();
      \$codegen_fpath = KERNEL_CACHE_FPATH."/{$module_name}.php";
      require_once('$this_module_fpath/codegen.php');
      if(\$codegen!==null)
      {
        \$codegen = join("\\n",\$codegen);
        file_put_contents(\$codegen_fpath, "<?\n".\$codegen);
      }
    }
    require(KERNEL_CACHE_FPATH."/{$module_name}.php");
CODE;
  }
  
  $callbacks = gather_callbacks($this_module_fpath);
  foreach($callbacks as $callback)
  {
    $event_injections[$module_name][$callback] = array();
    $path = $this_module_fpath."/callbacks/$callback.php";
    if (file_exists($path))
    {
      $event_injections[$module_name][$callback][] = $path;
    }
    $path = $this_module_fpath."/views/$callback.php";
    if (file_exists($path))
    {
      $event_injections[$module_name][$callback][] = $path;
    }
    $php = <<<END_CALLBACK
function {$module_name}_{$callback}(\$event_args, &\$event_data)
{
  return event_callback(\$event_args, \$event_data, '$module_name', '$callback', '$this_module_fpath', '$this_module_vpath', '$cachefpath', '$cachevpath');
}
END_CALLBACK;
    $code[] = $php;
  }
  if (file_exists("$this_module_fpath/bootstrap.php"))
  {
    $code[] = "require_once('$this_module_fpath/bootstrap.php');";
  }
  $code[] = "\$manifests['$module_name']['loaded']=true;";
}

$header[] = '$manifests = '.s_var_export($manifests).';';
$header[] = '$loaded_modules = '.s_var_export($loaded_modules).';';
$header[] = '$globals = '.s_var_export($globals).';';
$header[] = '$event_injections = '.s_var_export($event_injections).';';
$header = join($header, "\n");
$code = join($code, "\n");
$final_codegen = $header.$code;

$do_codegen=true;
$stub = KERNEL_CACHE_FPATH."/stub.php";
file_put_contents($stub, "<?\n".$final_codegen);
require($stub);
