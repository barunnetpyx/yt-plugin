<?php
function yt_video_channel_create () {
	global $wpdb;
	$table_name = $wpdb->prefix . "yt_video_channel";	
	//insert
	if(isset($_POST['insert'])){
		
		insertChannel($_POST);
	}
	?>
	<div class="wrap">
		<h2>Add New Channel</h2>
		<?php if (isset($message)): ?><div class="updated"><p><?php echo $message;?></p></div><?php endif;?>
		<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			
			<table class='form-table'>
			<tr>
				<th scope="row"><label for="id">Channel ID</label></th>
				<td><input type="text" name="id"/></td>
			</tr>
			<tr>
				<th scope="row"><label for="category">Category</label></th>
				<td>
					<select name="category" id="category">
						<option value="">-Select Category-</option>
						<?php foreach (getCategories() as $key => $value) { ?>
						<option value="<?php echo $value['id']; ?>"><?php echo $value['cat_name']; ?></option>
						<?php  } ?>
					</select>
				</td>
			</tr>
			</table>
			<input type='submit' name="insert" value='Save' class='button button-primary'>
		</form>
	</div>
<?php
	
}