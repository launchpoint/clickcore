<?

function load($module_name, $module_version=1)
{
  global $modules;
  $modules[] = array($module_name, $module_version);
}

function gather_callbacks($path)
{
  $callbacks = array();
  foreach(array('callbacks', 'views') as $folder)
  {
    foreach( glob($path."/$folder/*.*") as $fname)
    {
      $info = pathinfo($fname);
      if (preg_match('/[\s\.]/', $info['filename']))
      {
        click_error("Illegal characters in $fname");
      }
      $callbacks[$info['filename']] = true;
    }
  }
  return array_keys($callbacks);
}

function call_event_func($event_args, &$event_data, $this_event_name, $this_module_fpath, $this_module_vpath, $callback_path)
{
  global $globals;
  foreach($globals as $var_name) eval("global $$var_name;");
  extract($event_args);
  
  require($callback_path);
    
  $ret = get_defined_vars();
  foreach($globals as $var_name) unset($ret[$var_name]);
  foreach($event_args as $k=>$v) unset($ret[$k]);
  unset($ret['globals']);
  return $ret;
}

function is_loaded($dependency_name)
{
  global $loaded_modules;
  foreach($loaded_modules as $loaded_module_name)
  {
    if ($loaded_module_name == $dependency_name) return true;
  }
  return false;
}

function all_loaded($arr)
{
  if (!is_array($arr)) $arr=array($arr);
  $all_found = true;
  foreach($arr as $dependency_name)
  {
    $all_found = $all_found && is_loaded($dependency_name);
  }
  return $all_found;
}

$globals = array();
function add_global($s, $default=null)
{
  global $globals;
  if (!is_array($s)) $s = array($s);
  foreach($s as $var)
  {
    global $$var;
    if (!isset($$var)) $$var = $default;
    $globals[]=$var;
  } 
}

function module_path($name)
{
  global $manifests;
  if (!array_key_exists($name, $manifests)) dprint("Manifest $name does not exist, did you reference a misspelled module?");
  return $manifests[$name]['path'];
}

function validate_module_folder_structure($modules)
{
  $user_folders = array();
  foreach(glob(LOCAL_MODULES_FPATH."/*") as $fpath)
  {
    $module_name = basename($fpath);
    $user_folders[$module_name] = $fpath;
  }

  $core_folders = array();
  foreach(glob(GLOBAL_MODULES_FPATH."/*") as $fpath)
  {
    $module_name = basename($fpath);
    $core_folders[$module_name] = $fpath;
  }
  
  $module_folders = array_merge($core_folders,$user_folders);

  foreach($modules as $module_name=>$module_version)
  {
    if (!array_key_exists($module_name, $module_folders)) click_error("$module_name not found. Did you spell it right?");
  }
  foreach($module_folders as $module_name=>$module_path)
  {
    if (!preg_match('/^[A-Za-z_][A-Za-z_\d]*$/', $module_name)) click_error("Module '$module_name' contains invalid characters in $module_path.");
    $module_path .= '/branches';
    if(!file_exists($module_path)) continue; // module does not have multiple versions
    $version = 1;
    if(isset($modules[$module_name])) $version = $modules[$module_name];
    $module_path .= "/".$version;
    if(!file_exists($module_path)) click_error("$module_name v{$modules[$module_name]} not found. Did you specify an existing version?");
    $module_folders[$module_name] = $module_path;
  }

  return $module_folders;
}

function select_module_versions($modules)
{
  $new_modules = array();
  foreach($modules as $module_info)
  {
    list($module_name, $module_versions) = $module_info;
    if(!is_array($module_versions)) $module_versions = array($module_versions);
    if(array_key_exists($module_name, $new_modules))
    {
      $new_modules[$module_name] = array_intersect($new_modules[$module_name], $module_versions);
      if(count($new_modules[$module_name])==0)
      {
        click_error("Versioning problem: no common version found for module $module_name.", $modules);
      }
    } else {
      $new_modules[$module_name] = $module_versions;
    }
  }
  $modules = $new_modules;
  foreach($modules as $module_name=>$module_versions)
  {
    $modules[$module_name] = max($module_versions);
  }
  return $modules;
}

function load_manifests()
{
  global $modules, $manifests, $loaded_modules, $manifest;
  $modules = select_module_versions($modules);
  $module_folders = validate_module_folder_structure($modules);

  $manifests = array();
  $loaded_modules = array();
  $requires = array();
  foreach($module_folders as $module_name=>$this_module_fpath)
  {
    if (array_key_exists($module_name, $manifests)) continue;
    
    $manifest_path = "$this_module_fpath/manifest.php"; 
  
    $manifest = array();
    if (file_exists($manifest_path))
    {
      include($manifest_path);
    }
    $manifest['path'] = $this_module_fpath;
    $arr = array('load_before', 'load_after', 'routes', 'requires');
    foreach($arr as $dep)
    {
      if (!array_key_exists($dep, $manifest)) $manifest[$dep] = array();
      if (!is_array($manifest[$dep])) $manifest[$dep] = array($manifest[$dep] );
    }
    $manifest['loaded']=false;
    if (!array_key_exists('priority_load', $manifest)) $manifest['priority_load'] = false;
    $manifest['enabled']=(isset($modules[$module_name])) || startswith($this_module_fpath, LOCAL_MODULES_FPATH);
    if (!array_key_exists('requires', $manifest)) $manifest['requires'] = array();
    foreach($manifest['routes'] as $event_name=>$handler_struct)
    {
      foreach($handler_struct as $handler_name=>$v)
      {
        if (!is_array($v))
        {
          $v = array($v);
          $handler_struct[$handler_name] = $v;
        }
        $manifest['routes'][$event_name] = $handler_struct;
      }
    }
    $manifests[$module_name] = $manifest;
  }

  // require all children of required manifests
  $start_over = true;
  while($start_over)
  {
    $start_over = false;
    foreach($manifests as $module_name=>$manifest)
    {
      if (!$manifest['enabled']) continue;
      foreach($manifest['requires'] as $required_module_name)
      {
        if (!isset($manifests[$required_module_name])) click_error("$module_name mentions $required_module_name, but it's not found. Did you spell it right?");
        $start_over |= !$manifests[$required_module_name]['enabled'];
        $manifests[$required_module_name]['enabled'] = true;
      }
      continue;
    }
  }

  // enable required manifests
  $requires = array_unique($requires);
  foreach($requires as $module_name)
  {
    $manifests[$module_name]['enabled'] = true;
  }
  
  // filter disabled manifests
  foreach($manifests as $module_name=>$manifest)
  {
    if ($manifest['enabled']==false) unset($manifests[$module_name]);
  }
  
  // filter disabled manifests
  foreach($manifests as $module_name=>$manifest)
  {
    foreach(array('load_before', 'load_after') as $condition)
    {
      for($i=0;$i<count($manifest[$condition]);$i++)
      {
        if(!isset($manifests[$manifest[$condition][$i]]))
        {
          unset($manifests[$module_name][$condition][$i]);
        }
      }
    }
  }
  
  // precalc load order
  foreach($manifests as $manifest_name=>$manifest)
  {
    foreach($manifest['load_before'] as $dependency_name)
    {
      $manifests[$dependency_name]['load_after'][] = $manifest_name;
      if ($manifest['priority_load']==true) $manifests[$dependency_name]['priority_load'] = true;
    }
    if ($manifest_name!='browser_detection' && $manifest_name!='debug') $manifest['load_after'][] = 'debug';
  }
  
  $next_pass = array();
  foreach($manifests as $module_name=>$manifest)
  {
    if (!$manifest['enabled']) continue;
    $mods = $manifest['load_after'];
    $mods[] = $module_name;
    if ($manifest['priority_load'])
    {
      $next_pass = array_merge($mods, $next_pass);
    } else {
      $next_pass = array_merge($next_pass, $mods);
    }
  }
  $next_pass = array_unique($next_pass);

  add_global( array(
    'domain', 'params', 'request_path', 'subdomain', 'rendered_page', 'start', 'end', 'manifests', 'flash', 'loaded_modules', 'run_mode', 'querystring', 'full_request_path', 'current_url', 'event_table'
  ));
  
  // calculate module load order
  while(count($next_pass)>0)
  {
    $old_count = count($loaded_modules);
    foreach($next_pass as $k=>$module_name)
    {
      $should_load=true;
      $this_module_fpath = $manifests[$module_name]['path'];
      $manifest = &$manifests[$module_name];
      $should_load = all_loaded($manifest['load_after']);
      if (!$should_load) continue;
      $loaded_modules[] = $module_name;
      unset($next_pass[$k]);
      break;
    }
  
    if (count($loaded_modules) == $old_count)
    {
      echo("Circular module dependencies detected:<br/>");
      var_dump($next_pass);
      $png = make_dependency_graph($manifests, $next_pass);
      $png_name = basename($png);
      echo "<a href='".TEMP_VPATH."/$png_name'><img src='".TEMP_VPATH."/$png_name' width=600/></a>";
      die;
    }
  }

  // sort $manifests
  $arr = array();
  foreach($loaded_modules as $module_name)
  {
    $arr[$module_name] = $manifests[$module_name];
  }
  $manifests = $arr;
  
}
