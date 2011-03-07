<?

// Non-recurive Quicksort for an array of Person objects
// adapted from http://www.algorithmist.com/index.php/Quicksort_non-recursive.php
function qsort( &$array, $prop_name )
{
 if(count($array)==0) return;
 $cur = 1;
 $stack[1]['l'] = 0;
 $stack[1]['r'] = count($array)-1;

 do
 {
  $l = $stack[$cur]['l'];
  $r = $stack[$cur]['r'];
  $cur--;

  do
  {
   $i = $l;
   $j = $r;
   $tmp = $array[(int)( ($l+$r)/2 )];

   // partion the array in two parts.
   // left from $tmp are with smaller values,
   // right from $tmp are with bigger ones
   do
   {
    while( $array[$i]->$prop_name < $tmp->$prop_name )
     $i++;

    while( $tmp->$prop_name < $array[$j]->$prop_name )
     $j--;

    // swap elements from the two sides
    if( $i <= $j)
    {
     $w = $array[$i];
     $array[$i] = $array[$j];
     $array[$j] = $w;

     $i++;
     $j--;
    }

   }while( $i <= $j );

 if( $i < $r )
   {
    $cur++;
    $stack[$cur]['l'] = $i;
    $stack[$cur]['r'] = $r;
   }
   $r = $j;

  }while( $l < $r );

 }while( $cur != 0 );


}
