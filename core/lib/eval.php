<?

function eval_php($__path__, $__data__=array())
{
  global $run_mode;
  extract($__data__);
  ob_start();
 // if ($run_mode==RUN_MODE_DEVELOPMENT) echo "\n<!-- PHP EVAL $__path__ -->\n";
  require($__path__);
//  if ($run_mode==RUN_MODE_DEVELOPMENT) echo "\n<!-- END PHP EVAL $__path__ -->\n";
  $s = ob_get_contents();
  ob_end_clean();
  return $s;
}
