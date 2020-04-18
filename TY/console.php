<?php
include 'header.php';
include 'menu.php';
?>
<h1>
<?php 
include 'page-title.php';?>
</h1>
<?php
$img_handle=Helper::options()->plugin('GitStatic')->ImgHandle;
if($img_handle=="on")
{
  $handle_url="?do=off";
  $bth="关闭图片地址优化";
}else{
  $handle_url="?do=on";
  $bth="开启图片地址优化";
}
echo "<a href='";
$options->index("action/GitImg");
echo "{$handle_url}'>{$bth}</a>";
?>

<?php
$isapex?:include 'copyright.php';
include 'common-js.php';
?>