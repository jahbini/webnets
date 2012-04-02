<?php 
// define the back end to enter subdomains we serve
class MyCatalogAdmin extends ModelAdmin {

	public static $managed_models = array(
		'SubDomain'
		,'Mentor'
	);
	public static $url_segment = 'SubDomain';
	public static $menu_title = 'eGroups';
}
