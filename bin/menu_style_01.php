<?php

echo '<ul class="nav navbar-nav navbar-left">';
foreach ($list as $name1 => $data1) {
	$name1 = ucwords(strtolower($name1));
  if (is_array($data1)) {
    echo '<li class="dropdown">';
    echo '<a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$name1.'</a>';
    echo '<ul class="dropdown-menu">';
    foreach ($data1 as $name2 => $data2) {
	$name2 = ucwords(strtolower($name2));
      echo '<li><a href="/'.$data2.'">'.$name2.'</a></li>';
      $i++;
    }
  }
  else {
    echo '<li><a href="/'.$data1.'">'.$name1.'</a></li>';
  }
  if (end($list) != $data1 && is_array($data1)) echo '</ul>';
}
echo '</ul>';
echo '</ul>';

?>
