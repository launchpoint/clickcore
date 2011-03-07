<?

class cLock
{
	var $lockname;
	var $timeout;
	var $locked;

	function cLock($name, $timeout = 0)
	{
		$this->lockname = $name;
		$this->timeout = $timeout;
		$this->locked = -1;
	}

	function lock()
	{
		$rs = query("SELECT GET_LOCK('".$this->lockname."', ".$this->timeout.")");
		$this->locked = result($rs, 0);
		mysqli_free_result($rs);
	}

	function release()
	{
		$rs = qdb("SELECT RELEASE_LOCK('".$this->lockname."')");
		$this->locked = !result($rs, 0);
		mysqli_free_result($rs);

	}

	function isFree()
	{
		$rs = qdb("SELECT IS_FREE_LOCK('".$this->lockname."')");
		$lock = (bool)result($rs, 0);
		mysqli_free_result($rs);

		return $lock;
	}
}


$__click['locks'] = array();

register_shutdown_function('unlock_all');

function lock($lock_name, $should_block=true)
{
  global $__click;
  $has_lock=false;
  $lock_key = md5(ROOT_FPATH)."_{$lock_name}";
  while(!$has_lock)
  {
    if($__click['build']['database'])
    {
      $res = query_assoc("select get_lock('$lock_key',1) lck");
      $has_lock = $res[0]['lck'];
    } else {
      $flag = LOCK_EX;
      if(!$should_block) $flag |= LOCK_NB;
      $fp = fopen(TEMP_FPATH."/{$lock_key}", "w");      
      $has_lock = flock($fp, $flag);
    }
    if(!$should_block) break;
  }
  if($has_lock)
  {
    if($__click['build']['database'])
    {
      $__click['locks'][$lock_name] = true;
    } else {
      $__click['locks'][$lock_name] = $fp;
    }
  }
  return $has_lock;
}

function unlock($lock_name)
{
  global $__click;
  dprint($__click['locks'],false);
  $lock_key = md5(ROOT_FPATH)."_{$lock_name}";
  if($__click['build']['database'])
  {
    query_assoc("select release_lock('$lock_key')");
  } else {
    fclose($__click['locks'][$lock_name]);
    unset($__click['locks'][$lock_name]);
  }
}

function unlock_all()
{
  global $__click;
  foreach($__click['locks'] as $lock_name=>$info)
  {
    unlock($lock_name);
  }
}