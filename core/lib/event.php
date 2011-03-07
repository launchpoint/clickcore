<?

$loaded_modules = array();

function validate_event_data($f, &$data)
{
  global $run_mode;
  
  if ($run_mode == RUN_MODE_PRODUCTION) return;
  if ($data==null)
  {
    $data = array();
    return;
  }
  if (!is_array($data)) 
  {
    click_error("$f must return a key/value array.");
  }
  foreach($data as $k=>$v)
  {
    if (is_numeric($k))
    {
      click_error("Expected key/value array, but got garbled array in $f.");
    }
  }
}


$event_table = array();
$recalc = array();
$route_controlled_hooks = null;

function calculate_event_table($event_name)
{
  global $event_table, $loaded_modules, $manifests, $request_path, $params, $recalc, $route_controlled_hooks, $validate_request_params;
  
  if (array_key_exists($event_name, $recalc)) unset($event_table[$event_name]);
  if (array_key_exists($event_name, $event_table)) return;
  
  $event_table[$event_name] = array();

  if (!$route_controlled_hooks)
  {
    // calc all route-controlled hooks - these hooks will not be called by open events
    $route_controlled_hooks = array();
    foreach($manifests as $module_name=>$manifest)
    {
      $routes = $manifest['routes'];
      foreach($routes as $mapped_event_name=>$route_struct)
      {
        foreach($route_struct as $hook_name=>$patterns)
        {
          $f = "${module_name}_${hook_name}"; 
          $route_controlled_hooks[$f] = true;
        }
      }
    }
  }
  
  foreach($manifests as $module_name=>$manifest)
  {
    // calc route-based hooks - if your event is route-controlled, the normal callback will not be used.
    $routes = $manifest['routes'];
    $try_normal_event = true;
    if (array_key_exists($event_name, $routes))
    {
      $hooks = $routes[$event_name];
      foreach($hooks as $hook_name=>$route_struct)
      {
        $f = "${module_name}_${hook_name}"; // route-based hooks override the normal hook
        foreach($route_struct as $route)
        {
          $matches = array();
          $args = array();
          if (preg_match($route, $request_path, $matches) > 0)
          {
            $try_normal_event = false;
            array_shift($matches);
            foreach($matches as $k=>$v)
            {
              if (is_numeric($k)) continue;
              if($validate_request_params && array_key_exists($k, $_REQUEST)) dprint("$k exists both as a GET/POST param and a route param for $route");
              $params[$k] = urldecode($v);
              $args[] = $v;
            }
            if (function_exists($f))
            {
              if ($manifest['loaded'])
              {
                 $event_table[$event_name][] = $f;
              } else {
                $recalc[$event_name] = true;
              }
            }
            break;
          }
        }
      }
    }
    
    // calc PHP hooks - if it is on the trigger end of a route-based hook, skip it
    if($try_normal_event)
    {
      $f = "${module_name}_${event_name}";
      if (array_key_exists($f, $route_controlled_hooks)) continue;
      if (function_exists($f))
      {
        if ($manifest['loaded'])
        {
           $event_table[$event_name][] = $f;
        } else {
          $recalc[$event_name] = true;
        }
      }
    }
  }
}

function event($event_name, $event_args=array(), $capture=false)
{
  global $event_table;
  
  calculate_event_table($event_name);
  
  $event_data=array();
  if (!$event_args) $event_args=array();
  
  foreach($event_table[$event_name] as $f)
  {
    // if (strpos($event_name, 'event_')!==0) event("event_triggered", $f);  // rename to event_before_hook
    if ($capture) ob_start();
    $data = call_user_func_array($f,array($event_args, &$event_data));
    if ($capture)
    {
      $s = ob_get_contents();
      ob_end_clean();
      $data['__output'] = $s;
    }
    if (count($data)>0) $event_data[$f] = $data;
  }

  return $event_data;
}

function responds_to($event_name)
{
  global $event_table;
  calculate_event_table($event_name);
  return count($event_table[$event_name])>0;
}

function event_capture($event_name, $event_args=array())
{
  $data = event($event_name, $event_args, true);
  $s = join('', collect($data, '__output'));
  return array('data'=>$data,'output'=>$s);
}

function render($name, $args=array())
{
  event('render_'.$name, $args);
}

function event_callback($event_args, &$event_data,
  $this_module_name,
  $this_event_name,
  $this_module_fpath,
  $this_module_vpath,
  $this_module_cache_fpath,
  $this_module_cache_vpath
)
{
  global $globals, $event_injections;
  foreach($globals as $var_name) eval("global $$var_name;");
  $__locals__ = get_defined_vars();

  extract($event_args, EXTR_REFS);
  
  foreach($event_injections[$this_module_name][$this_event_name] as $__path__)
  {
    if(!file_exists($__path__)) { echo $__path__; var_dump(glob(dirname($__path__).'/*.*'));die; }
    require($__path__);
  }
    
  $__ret__ = get_defined_vars();
  foreach($__locals__ as $k=>$v) unset($__ret__[$k]);
  unset($__ret__['__locals__']);
  unset($__ret__['__path__']);
  return $__ret__;
}

function delegate($event_name)
{
  global $event_table;
  
  $event_name = "delegate_$event_name";
  
  calculate_event_table($event_name);
  
  if(count($event_table[$event_name]) == 0) return array();
  
  $event_args = func_get_args();
  array_shift($event_args);
  $event_data=array();
  
  $f = $event_table[$event_name][count($event_table[$event_name])-1];
  $data = call_user_func_array($f,array($event_args, &$event_data));
  return $data;
}