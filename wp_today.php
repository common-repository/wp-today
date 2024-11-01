<?php
/*
Plugin Name:WP-Today
Plugin URI: http://liucheng.name/1256/
Description: A wordpress plugin to display the posts of today on history. 显示wordpress博客当年今天的文章。
Author: 柳城
Version: 1.2.1
Author URI: http://liucheng.name/

*/


/**
*Loading language file...
*@
*/
function load_wp_today_language() {
		
		//Loading language file...
		$currentLocale = get_locale();
		if(!empty($currentLocale)) {
			$moFile = dirname(__FILE__) . "/wp_today-" . $currentLocale . ".mo";
			if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('wp_today_language', $moFile);
		}
}

/** load the language file **/
add_filter('init','load_wp_today_language');

function wp_today_admininit()
{
	 // Add a page to the options section of the website
   if (current_user_can('manage_options')) 				
 		add_options_page("WP Today","WP Today", 8, __FILE__, 'wp_today_optionpage');
}

function wp_today_topbarmessage($msg)
{
	 echo '<div class="updated fade" id="message"><p>' . $msg . '</p></div>';
}

function wp_today_optionpage()
{
	$get_options = get_option(wp_today_option);
	if(!empty($wp_today_option)){ 
		list($title,$limit,$order,$post,$feed) = explode("|",$wp_today_option); 
	}else{
		$title = "<h2>Today on history:</h2>";
		$limit = 5;
		$order = "latest";
		$post = 1;
		$feed = 1;
	}
      /* Perform any action */
		if(isset($_POST['action'])) {
			if ($_POST['action']=='save'){
				$title = $_POST[title];
				if(!$title){ $title = "<h2>Today on history:</h2>"; }
				$limit = $_POST[limit];
				if(!$limit){ $limit = 5; }
				$order = $_POST[order];
				$post = $_POST[post];
				$feed = $_POST[feed];
				$options = implode('|',array($title,$limit,$order,$post,$feed));
		        update_option(wp_today_option,$options); 
                wp_today_topbarmessage(__('Congratulate, Update options success','wp_today_language'));
			}
		}
		
		/* Definition */
       echo '<div class="wrap"></div>';
	   echo '<h2>WP Today</h2>';

		/* Introduction */ 
		echo '<p> A wordpress plugin to display the posts of today on history. 显示wordpress博客当年今天的文章。</p>';
		?>
		<h3>Options</h3>
		<table>
		<form name="wp_today" method="post" action="">
		<input type="hidden" name="action" value="save" />
		<tr><td><label for="title"><b>Custom Title: </b></label></td><td><input type="text" name="title" value="<?php echo $title;?>" /></td></tr>
		<tr><td><label for="limit"><b>Number of posts to show: </b></label></td><td><input type="text" name="limit" value="<?php echo $limit;?>" /></td></tr>
		<tr><td><label for="order"><b>Order: </b></label></td><td><select name="order"><option value="oldest" <?php if($order == 'oldest'){ echo 'selected'; }?> >oldest<option value="latest" <?php if($order == 'latest'){ echo 'selected'; }?> >latest</select></td></tr>
		<tr><td><label for="post"><b>Auto-display list on post: </b></label></td><td><input type="checkbox" name="post" value="1" <?php if($post == '1'){ echo 'checked';} ?> /></td></tr>
		<tr><td><label for="feed"><b>Auto-display list on feed: </b></label></td><td><input type="checkbox" name="feed" value="1" <?php if($feed == '1'){ echo 'checked'; }?> /></td></tr>
		<tr><td><input type=submit value="Save" /></td></tr>
		</table>
		</form>
		<p>use custom function <code>wp_today()</code> display on your theme. explame: <code>&lt;? if(function_exist(wp_today)){ print wp_today();} ?&gt;</code></p>
		<?php
}

function wp_today_auto($content){
	$wp_today_option = get_option(wp_today_option);
	if(!empty($wp_today_option)){ 
		list($title,$limit,$order,$post,$feed) = explode("|",$wp_today_option); 
	}else{
		$title = "<h2>Today on history:</h2>";
		$limit = 5;
		$order = "latest";
		$post = 1;
		$feed = 1;
	}
	if( $post && is_single() ){
		$content = $content.wp_today();
	}
	if( $feed && is_feed() ){
		$content = $content.wp_today();
	}
	return $content;
}

function wp_today(){
	$wp_today_option = get_option(wp_today_option);
	if(!empty($wp_today_option)){ 
		list($title,$limit,$order,$post,$feed) = explode("|",$wp_today_option); 
	}else{
		$title = "<h2>Today on history:</h2>";
		$limit = 5;
		$order = "latest";
		$post = 1;
		$feed = 1;
	}

	global $wpdb;
	$post_year = get_the_time('Y');
	$post_month = get_the_time('m');
	$post_day = get_the_time('j');
	if($order == "latest"){ $order = "DESC";} else { $order = '';}

	$sql = "select ID, year(post_date_gmt) as h_year, post_title, comment_count FROM 
			$wpdb->posts WHERE post_password = '' AND post_type = 'post' AND post_status = 'publish'
			AND year(post_date_gmt)!='$post_year' AND month(post_date_gmt)='$post_month' AND day(post_date_gmt)='$post_day'
			order by post_date_gmt $order limit $limit";
	$histtory_post = $wpdb->get_results($sql);
	if( $histtory_post ){
		foreach( $histtory_post as $post ){
			$h_year = $post->h_year;
			$h_post_title = $post->post_title;
			$h_permalink = get_permalink( $post->ID );
			$h_comments = $post->comment_count;
			$h_post .= "<li>$h_year:&nbsp;&nbsp;<a href='".$h_permalink."' title='Permanent Link to ".$h_post_title."'>$h_post_title($h_comments)</a></li>";
		}
	}

	if ( $h_post ){
		$result = "".$title."<ol>".$h_post."</ol>";
	}

	return $result;
}

add_action('admin_menu','wp_today_admininit');
add_filter('the_content', 'wp_today_auto',9999);
add_filter('init','wp_today');
?>