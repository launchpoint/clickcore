<?

function dprint($s,$shouldExit=true)
{
  if(!CLI) echo "<pre>";
  ob_start();
  var_dump($s);
  $out = ob_get_contents();
  ob_end_clean();
  if(CLI)
  {
    eprint($out);
  } else {
    echo htmlentities($out);
  }
  if(!CLI) echo "</pre>";
  if ($shouldExit) click_error('Development stop');
}

function eprint($s)
{
  if(CLI)
  {
    fwrite(STDERR,$s);
  } else {
    echo $s;
  }
}

function click_error($err, $data=null)
{
  if ($data)
  {
    if(CLI)
    {
      $err = $err."\n".s_var_export($data)."\n";
    } else {
      $err = $err."<br/><pre>".htmlentities(s_var_export($data))."</pre>";
    }
  }
  trigger_error($err, E_USER_ERROR);
}

function s_var_export($v)
{
  ob_start();
  var_export($v);
  $s = ob_get_contents();
  ob_end_clean();
  return $s;
}