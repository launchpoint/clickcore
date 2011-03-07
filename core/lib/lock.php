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


$__locks = array();

register_shutdown_function('unlock_all');

function lock($name, $should_block=true)
{
  global $__locks;
  $has_lock=false;
  while(!$has_lock)
  {
    $lock_name = md5(ROOT_FPATH)."_$name";
    $res = query_assoc("select get_lock('$lock_name',1) lck");
    $has_lock = $res[0]['lck'];
    if(!$should_block) break;
  }
  if($has_lock) $__locks[$lock_name] = true;
  return $has_lock;
}

function unlock($name)
{
  $lock_name = md5(ROOT_FPATH)."_$name";
  query_assoc("select release_lock('$lock_name')");
}

function unlock_all()
{
  global $__locks;
  foreach($__locks as $lock_name=>$v)
  {
    unlock($lock_name);
  }
}