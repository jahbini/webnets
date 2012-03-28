<?php
class PenNames extends ModelAdmin {
   
  public static $managed_models = array(
      'PenName'
   );
 
  static $url_segment = 'pennames'; // will be linked as /admin/products
  static $menu_title = 'Pen Names';
 
}
?>
