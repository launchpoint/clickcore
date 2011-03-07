<?

function uuid()
{
  return md5(uniqid(mt_rand(), true));
}