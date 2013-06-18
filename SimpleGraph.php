<?php
$width = 600;
$height = 600;

$font_height = ImageFontHeight(5);
$font_width = ImageFontWidth(5);

$im = ImageCreate ($width, $height);
$white = ImageColorAllocate ($im, 255, 255, 255);
$blue = ImageColorAllocate ($im, 0, 0, 255);
$black = ImageColorAllocate ($im, 0, 0, 0);
$gray = ImageColorAllocate ($im, 192, 192, 192);

// Initialize the canvas

ImageFill ($im, 1, 1, $white);
ImageRectangle ($im, 0, 0, $width-1, $height-1, $black);

// Draw the axes

ImageLine ($im, 40, 550, $width-40, 550, $black);
ImageLine ($im, 50, 40, 50, $height-40, $black);

// Draw the Y scale - Count of attendees

for ($i = 50; $i <= 250; $i += 50)
{
  $x = 10;
  $y = $height-50 - ($i * 2);
  ImageString ($im, 5, $x, $y-($font_height/2), $i, $black);
  ImageLine ($im, 40, $y, $width-40, $y, $gray);
}

// Draw the X scale - Time (in days) - Start is 1-Jan-2004

$start = mktime (0, 0, 0, 1, 1, 2004);

for ($i = 2; $i <= 15; $i++)
{
  $t = mktime (0, 0, 0, $i, 1, 2004);
  $d = intval (0.1 + (($t - $start) / (60 * 60 * 24)));

  $date = date ('M', $t);

  $x = $d + 50;

  //  echo "$date: $t, $d, $x<br>\n";

  ImageLine ($im, $x, 40, $x, $height - 40, $gray);
  ImageStringUp ($im, 5, $x - ($font_height/2), $height-10, $date, $black);
}

Header ('Content-type: image/png');
ImagePNG ($im);
ImageDestroy ($im);
?>