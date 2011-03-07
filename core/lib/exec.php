<?

function click_exec($cmd, $expected_retval=0, &$out='')
{
  exec($cmd . " 2>&1", $out, $ret);
  if ($ret != $expected_retval)
  {
    click_error("Command failed: $cmd", array($ret, $out));
  }
  return $ret;
  
}