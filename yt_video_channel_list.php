<?php
function yt_video_channel_list () {
?>
<div class="wrap">
<h2>Channels <a class="add-new-h2" href="<?php echo admin_url('admin.php?page=yt_video_channel_create'); ?>">Add New</a></h2>
<h2><button class="button button-primary yt-deleteduplicate">Delete Duplicates</button></h2>

<?php 
$channels = getAllChannelList();
if (count($channels) > 0) {
?>
<table class="wp-list-table widefat fixed">
	<thead>
		<tr>
			<th width="40">Sl No</th>
			<th>Channel Name</th>
			<th>Channel ID</th>
			<th>Category</th>
			<th width="70">Created</th>
			<th width="60">Status</th>
			<th>Process</th>
		</tr>
	</thead>
	<tbody>
		<?php $i=1; foreach($channels as $channel) { ?>
		<tr>
			<td><?php echo $i; ?></td>
			<td><?php echo $channel->name; ?></td>
			<td><?php echo $channel->channelId; ?></td>
			<td><?php echo get_cat_name($channel->category); ?></td>
			<td><?php echo date("Y-m-d", strtotime($channel->created_on)); ?></td>
			<td><?php echo $channel->status; ?></td>
			<td><button class="button button-primary yt-process" id="<?php echo $channel->channelId; ?>" data-cat="<?php echo $channel->category; ?>" >Process</button></td>
		</tr>
		<?php $i++; } ?>
	</tbody>
</table>
<script type="text/javascript">
	jQuery(".yt-process").click(function(){
		var cat =  jQuery(this).attr('data-cat');
		if(this.id){
			jQuery.ajax({
			  type: "POST",
			  url: "admin-ajax.php",
			  data: { channel: this.id, action: "yt_channelProcess", category: cat }
			})
		}
	})
	jQuery(".yt-deleteduplicate").click(function(){
		var myVar = setInterval(function(){deleteCron()}, 20000);		
	})
	function deleteCron(){
		jQuery.ajax({
		  type: "POST",
		  url: "admin-ajax.php",
		  data: { action: "yt_channelVideoDeleteProcess"}
		})
	}
</script>

<?php } else { ?>
<h2>No channels yet <a class="add-new-h2" href="<?php echo admin_url('admin.php?page=yt_video_channel_create'); ?>">Add New</a></h2>
<?php }?>
</div>
<?php
}