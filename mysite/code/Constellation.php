<?php
class Constellation extends ModelAdmin {
   
  public static $managed_models = array(
      'TagGroup',
      'Tag'
   );
 
  static $url_segment = 'constellation'; // will be linked as /admin/products
  static $menu_title = 'Constellation';
 
}
?>
