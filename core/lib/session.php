<?


function click_save_session()
{
  global $__click;
  if(!isset($_SESSION['__core']['current_session_id'])) return;
  if(!$__click['session']) return;
  $_SESSION['__core']['sessions'][$_SESSION['__core']['current_session_id']] = $__click['session'];
}

function click_new_session()
{
  global $__click;
  
  $uid = uniqid();
  $_SESSION['__core']['current_session_id'] = $uid;
  $_SESSION['__core']['sessions'][$uid] = array();
  $__click['session'] = array();
}

function click_destroy_session()
{
  global $__click;
  
  unset($_SESSION['__core']['sessions'][$_SESSION['__core']['current_session_id']]);
  unset($_SESSION['__core']['current_session_id']);
  if(count($_SESSION['__core']['sessions'])>0)
  {
    $ids = array_keys($_SESSION['__core']['sessions']);
    $_SESSION['__core']['current_session_id'] = $ids[0];
  }
  $__click['session'] = null;
}