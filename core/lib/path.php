<?

function normalize_path($path)
{
  $parts = explode('/', $path);
  $new_path = array();
  foreach($parts as $part)
  {
    $skip = false;
    if ($part == "..")
    {
      array_pop($new_path);
      continue;
    }
    $new_path[] = $part;
  }
  $new_path = join('/',$new_path);
  return $new_path;
}
