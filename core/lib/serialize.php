<?

// Fixes some PHP bugs
// see http://davidwalsh.name/php-serialize-unserialize-issues

function __serialize($data)
{
  return base64_encode(serialize($data));
}

function __unserialize($data)
{
  return unserialize(base64_decode($data));
}

