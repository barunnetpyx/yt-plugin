<?php
/*
Plugin Name: YouTube Plugin
Description:
Version: 1
Author: Barun
Author URI: http://effectivez.com
*/

//menu items
global $wpdb;
$table_name = $wpdb->prefix . "yt_video_channel";
define('YT_TABLE', $table_name);
define('YT_TABLE_LOG', $wpdb->prefix . "yt_video_channel_log");
define('YT_API_KEY', "AIzaSyA0Rl-m97nZBkANhSNIivrbI505w9-ZkOY");

function yt_install() {
	$sql = "CREATE TABLE `". YT_TABLE ."` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` VARCHAR(145) NULL,
	  `channelId` VARCHAR(45) NULL,
	  `category` INT NULL,
	  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `status` TINYINT NULL DEFAULT 1,
	  PRIMARY KEY (`id`),
	  UNIQUE INDEX `channelId_UNIQUE` (`channelId` ASC));";
	$sql1 = "CREATE TABLE `". YT_TABLE_LOG ."` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `channelID` VARCHAR(60) NULL,
  `updatedOn` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`));";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	dbDelta( $sql1 );
}
register_activation_hook( __FILE__, 'yt_install' );
function yt_video_modifymenu() {
	
	//this is the main item for the menu
	add_menu_page('YouTube Channels', //page title
	'Channels', //menu title
	'manage_options', //capabilities
	'yt_video_channel', //menu slug
	'yt_video_channel_list' //function
	);
	
	//this is a submenu
	add_submenu_page('yt_video_channel_list', //parent slug
	'Add Channel', //page title
	'Add Channel', //menu title
	'manage_options', //capability
	'yt_video_channel_create', //menu slug
	'yt_video_channel_create'); //function
	
	//this submenu is HIDDEN, however, we need to add it anyways
	add_submenu_page(null, //parent slug
	'Update Channel', //page title
	'Update', //menu title
	'manage_options', //capability
	'yt_video_channel_update', //menu slug
	'yt_video_channel_update'); //function
}
add_action('admin_menu','yt_video_modifymenu');
function getCategories(){
	$cat = array();
	$category_ids = get_all_category_ids();
	foreach($category_ids as $cat_id) {
	 	$cat_name = get_cat_name($cat_id);
	 	$cat[] = array('cat_name' =>  $cat_name, 'id' => $cat_id );
	}
	return $cat;
}
function insertChannel($post){
	if(!empty($post['category'])){
		$post['category'] = $post['category'];
	}else{
		$post['category'] = 1;
	}
	$checkVideoFromChannel = checkChannelDetials(trim($post['id']));
	if( count($checkVideoFromChannel) > 0){
		$channel['channelId'] = $checkVideoFromChannel[0]->id;
		$channel['name'] = $checkVideoFromChannel[0]->snippet->title;
		$channel['category'] = $post['category'];
		global $wpdb;
		$wpdb->insert( YT_TABLE , $channel);
	}
	
}
function getAllChannel(){
	global $wpdb;
	$table_name = $wpdb->prefix . "yt_video_channel";
	$rows = $wpdb->get_results("SELECT * FROM " . YT_TABLE . " limit 0, 5");
	return $rows;
}
function getAllChannelList(){
	global $wpdb;
	$table_name = $wpdb->prefix . "yt_video_channel";
	$rows = $wpdb->get_results("SELECT * FROM " . YT_TABLE );
	return $rows;
}
function checkChannelDetials($channelID){
	$channelData = "https://www.googleapis.com/youtube/v3/channels?id=". $channelID ."&part=snippet&key=". YT_API_KEY;
	$channelDataJson = json_decode( file_get_contents( $channelData ) );
	return $channelDataJson->items;
}
function checkVideoFromChannel( $channelID ){
	$channelData = "https://www.googleapis.com/youtube/v3/search?key=". YT_API_KEY ."&channelId=". $channelID ."&safeSearch=moderate&part=snippet&maxResults=20&order=date";
	$channelDataJson = json_decode( @file_get_contents( $channelData ) );
	return $channelDataJson->items;
}
function checkVideoFromChannelCron( $channelID ){
	$channelData = "https://www.googleapis.com/youtube/v3/search?key=". YT_API_KEY ."&channelId=". $channelID ."&safeSearch=moderate&part=snippet&maxResults=3&order=date";
	$channelDataJson = json_decode( @file_get_contents( $channelData ) );
	return $channelDataJson->items;
}
function getVideoDetials($youtube_id =''){
	if (!empty($youtube_id)) {
		$videoData = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id=". $youtube_id ."&key=". YT_API_KEY ;
		$videoDataJson = json_decode( file_get_contents( $videoData ) );
		return $videoDataJson->items;
	}
}
function check_duplicate_youtube($youtube_id){
	global $wpdb;
	$myrows = $wpdb->get_results( "SELECT * FROM `video_postmeta` WHERE `meta_value` = '". trim($youtube_id)."'" );
	return count($myrows);
	//return 0;
}
function addChannelVideos($getchannel = "UCRI89qcX7PDR_Or8CspgFyg", $category ="12"){
	$channel['id'] = $getchannel;
	$channel['cat'] = array($category);

	$channelData = checkVideoFromChannel( $channel['id'] );
	addVideoInDB($channelData, $channel['cat'], $channel['id']);
	
	
}
function addChannelVideosCron($getchannel = "UCRI89qcX7PDR_Or8CspgFyg", $category ="12"){
	$channel['id'] = $getchannel;
	$channel['cat'] = array($category);

	$channelData = checkVideoFromChannelCron( $channel['id'] );
	addVideoInDB($channelData, $channel['cat'], $channel['id']);
	
	
}
function logChannel($channelId){
	$channel['channelId'] = $channelId;
	global $wpdb;
	$wpdb->insert( YT_TABLE_LOG , $channel);

}
function channel_proccess(){
	if(!empty($_REQUEST['channel'])){
		print_r($_REQUEST);
		addChannelVideos(trim($_REQUEST['channel']), trim($_REQUEST['category']));
	}
	die;
}
function channel_proccessCron(){
	if(!empty($_REQUEST['channel'])){
		$allChannel =getAllChannel();
		foreach ($allChannel as $value) {
			addChannelVideosCron(trim($value->channelId), trim($value->category));
		}
	}
	die();
}
function channel_proccessCronReal(){
	$allChannel =getAllChannelList();
	foreach ($allChannel as $value) {
		addChannelVideosCron(trim($value->channelId), trim($value->category));
	}
	die();
}
function addVideoInDB($channelData, $category, $channelId){
	if(!empty($channelData)){
		foreach ($channelData as $video) {
			
			if(isset($video->id->videoId) && !empty($video->id->videoId)){
				$videoId = $video->id->videoId;
				if( check_duplicate_youtube($videoId) == 0 ){
					
					$videoDetails = getVideoDetials($videoId);
					$pubDate = explode("T", $videoDetails[0]->snippet->publishedAt);	
					$my_post['post_title'] = $videoDetails[0]->snippet->title;
					$my_post['post_content'] = "http://www.youtube.com/watch?v=".$videoDetails[0]->id ."\n \n". htmlspecialchars( $videoDetails[0]->snippet->description ) ;
					$my_post['post_status'] = 'publish';
					$my_post['post_date'] = $pubDate[0];
					$my_post['post_date_gmt'] = $pubDate[0];
					$my_post['post_author'] = '1';
					$my_post['post_category'] = $category;
					$postId = wp_insert_post( $my_post );
					set_post_format($postId, 'video' ); 
					add_post_meta($postId, 'youtube_id', $videoDetails[0]->id, true);
					add_post_meta($postId, 'youtube_publisher', $videoDetails[0]->snippet->channelTitle, true);
					add_post_meta($postId, 'custom_post_thumb', 'https://i1.ytimg.com/vi/'. $videoDetails[0]->id .'/mqdefault.jpg', true);
					add_post_meta($postId, '_webdados_fb_open_graph_specific_image', 'https://i1.ytimg.com/vi/'. $videoDetails[0]->id .'/mqdefault.jpg', true);
					add_post_meta($postId, '_aioseop_description', substr(htmlspecialchars( $videoDetails[0]->snippet->description ), 0, 150), true);
					$keywords = str_replace(" ", ", ", $videoDetails[0]->snippet->title);
					add_post_meta($postId, '_aioseop_keywords', $keywords, true);
					logChannel($channelId);
				}
				
			}
			
		}
		
	}
}
function deleteDuplicateChannelVideo(){
	global $wpdb;
	$sql = "select distinct(p2.post_id) from video_postmeta p1, video_postmeta p2 where p1.meta_id < p2.meta_id and p1.meta_value = p2.meta_value and p2.meta_key = 'youtube_id' limit 0,5";
	$myrows = $wpdb->get_results($sql);
	foreach ($myrows as $post) {
		wp_delete_post($post->post_id); 
	}
	die("deleted" . count($myrows));
}
add_action('wp_ajax_yt_channelProcess', 'channel_proccess');
add_action('wp_ajax_yt_channelVideoDeleteProcess', 'deleteDuplicateChannelVideo');
add_action('wp_ajax_nopriv_yt_channelProcess', 'channel_proccessCron');

//wp_schedule_event(time(), "hourly", "channel_proccessCron");

define('ROOTDIR', plugin_dir_path(__FILE__));
require_once(ROOTDIR . 'yt_video_channel_list.php');
require_once(ROOTDIR . 'yt_video_channel_create.php');
require_once(ROOTDIR . 'yt_video_channel_update.php');
