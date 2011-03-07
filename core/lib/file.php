<?

function ensure_writable_folder($path)
{
  $path = normalize_path($path);
  if (!file_exists($path))
  {
    if (!mkdir($path, 0775, true)) click_error("Failed to mkdir on $path");
    chmod($path,0775);
    if (!file_exists($path)) click_error("Failed to verify $path");
  }
}



function is_newer($src,$dst)
{
  if (!file_exists($dst)) return true;
  if(!file_exists($src)) return false;
  $ss = stat($src);
  $ds = stat($dst);
  $st = max($ss['mtime'], $ss['ctime']);
  $dt = max($ds['mtime'], $ds['ctime']);
  return $st>$dt;
}


function ftov($fpath)
{
  $path = substr($fpath, strlen(ROOT_FPATH));
  if (ROOT_VPATH)
  {
    $path = ROOT_VPATH . $path;
  }
  return $path;
}

function vpath($path)
{
  if (ROOT_VPATH)
  {
    $path = ROOT_VPATH . $path;
  }
  return $path;
}

function folderize($fname)
{
  return strtolower(preg_replace("/[^A-Za-z0-9]/", '_', $fname));
}