<?

function __autoload($klass)
{
  require_once(CODEGEN_CLASSES_CACHE_FPATH."/{$klass}.php");
}