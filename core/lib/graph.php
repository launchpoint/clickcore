<?

function make_dependency_graph($manifests,$highlights, $dst_fpath = TEMP_FPATH)
{
  $highlights = array();

  $s = "digraph G {\n";
  foreach($manifests as $module_name=>$manifest)
  {
    if ($module_name=='debug') continue;
    $s .= "$module_name\n";
    
    foreach($manifest['requires'] as $dependency_name)
    {
      if ($dependency_name=='debug') continue;
      $a = $dependency_name;
      $b = $module_name;
      if (array_search($a, $highlights)!==FALSE) $s .= "$a [color=red];\n";
      if (array_search($b, $highlights)!==FALSE) $s .= "$b [color=red];\n";
      $s .= "$b -> $a;\n";
    }
  }
  $s .= "}";
  $dot = $dst_fpath."/modules.dot";
  file_put_contents($dot,$s);
  $md5 = md5($s);
  $png = $dst_fpath."/modules.$md5.png";
  system("dot \"$dot\" -Tpng > \"$png\"");
  return $png;
}