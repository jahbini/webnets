<?php
// vim:sw=3:sts=3:ft=php:    

class HotPage extends GraphPage {
   static $db = array(
   );
   static $has_one = array(
   );
}

class HotPage_Controller extends GraphPage_Controller {
   function init() {
      parent::init();
}

function SearchForm() { return ""; } /* no search form on visual page */
function existingQueryForm () { return ""; }
function TweetBox () { return ""; }

}
?>
