<?

function clear_all_cache()
{
  $fpath = CACHE_FPATH."/*";
  clear_cache($fpath);
  ensure_writable_folder(CACHE_FPATH);
  ensure_writable_folder(CODEGEN_CACHE_FPATH);
  ensure_writable_folder(CODEGEN_CLASSES_CACHE_FPATH);
}

function clear_cache($fpath)
{
  if(!startswith($fpath, CACHE_FPATH)) click_error("Not a cache path.");
  $cmd = "rm -rf $fpath";
  click_exec($cmd);
  ensure_writable_folder($fpath);
}
