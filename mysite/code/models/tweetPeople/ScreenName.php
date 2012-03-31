<?php
/* since the screen_name can change, a single user can have many names over
 * the course of the account's life time.  And, rarely, another user
 * could pick up on a name.  Hence the screen_name is now a many_many
 * splintered thing.
 */
class ScreenNames extends ModelAdmin {
   
  public static $managed_models = array(
      'ScreenName'
   );
 
  static $url_segment = 'ScreenName'; // will be linked as /admin/products
  static $menu_title = 'edit screen names';
 
}


class ScreenName extends DataObject {
	static $db = array ('screen_name' => 'Varchar');
	static $indexes = array ('screen_name' => 'unique (screen_name)');
	static $belongs_many_many =array ('TweetUsers' => 'TweetUser');

}
?>
