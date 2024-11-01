<?php

function sparkle_dashboard_settings() {
	add_options_page(
        'Sparkle',
        'Sparkle',
        'manage_options',
        'sparkle',
        'sparkle_optimizer_page'
    );
}

add_action( 'admin_post_sparkle_form_response', 'sparkle_form_response' );

function sparkle_form_response() {
	$post_data = wp_unslash( $_POST );
	if ( isset( $post_data['sparkle_nonce'] ) && wp_verify_nonce( $post_data['sparkle_nonce'], 'sparkle_action' ) ) {
		$ewc_message = '';

		if(isset($post_data['sparkle_optimizer_revision'])){
			$revision = sanitize_text_field( $post_data['sparkle_optimizer_revision'] );
			sparkle_optimizer( $revision );
			$ewc_message = __( 'All revisions are deleted', 'sparkle' );
		}

		if(isset($post_data['sparkle_optimizer_draft'])){
			$draft = sanitize_text_field( $post_data['sparkle_optimizer_revision'] );
			sparkle_optimizer( $draft );
			$ewc_message = __( 'All drafts are deleted', 'sparkle');
		}

		if(isset($post_data['sparkle_optimizer_autodraft'])){
			$autodraft = sanitize_text_field( $post_data['sparkle_optimizer_autodraft'] );
			sparkle_optimizer( $autodraft );
			$ewc_message = __('All autodrafts are deleted', 'sparkle' );
		}


		if(isset($post_data['sparkle_optimizer_spam'])){
			$spam = sanitize_text_field( $post_data['sparkle_optimizer_spam'] );
			sparkle_optimizer($spam);
			$ewc_message = __( 'All spam comments are deleted', 'sparkle' );
		}

		if(isset($post_data['sparkle_optimizer_trash'])){
			$trash = sanitize_text_field( $post_data['sparkle_optimizer_trash'] );
			sparkle_optimizer( $trash );
			$ewc_message = __( 'All trash comments are deleted', 'sparkle' );
		}

		if ( isset($post_data['sparkle_optimizer_all'] ) ){
			$optimizer_all = sanitize_text_field( $post_data['sparkle_optimizer_all'] );
			sparkle_optimizer('revision');
			sparkle_optimizer('draft');
			sparkle_optimizer('autodraft');
			sparkle_optimizer('spam');
			sparkle_optimizer('trash');
			$ewc_message = __( 'All unnecessary data are deleted', 'sparkle');
		}
	} else {
		$ewc_message = __( 'nonce verification failed',  'sparkle');
	}

	wp_redirect( admin_url( 'options-general.php?page=sparkle' ) .'&ewc_message='.$ewc_message );
	// exit();
}

function sparkle_optimizer($type){
	global $wpdb;
	switch( $type ){
		case "revision":
			$ewc_sql = $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_type = %s ", 'revision' );
			$wpdb->query($ewc_sql);
			break;
		case "draft":
			$ewc_sql = $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_status = %s ", 'draft' );
			$wpdb->query($ewc_sql);
			break;
		case "autodraft":
			$ewc_sql = $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_status = %s ", 'auto-draft' );
			$wpdb->query($ewc_sql);
			break;
		case "spam":
			$ewc_sql = $wpdb->prepare( "DELETE FROM $wpdb->comments WHERE comment_approved = %s ", 'spam' );
			$wpdb->query($ewc_sql);
			break;
		case "trash":
			$ewc_sql = $wpdb->prepare( "DELETE FROM $wpdb->comments WHERE comment_approved = %s ", 'trash' );
			$wpdb->query($ewc_sql);
			break;
	}
}

function sparkle_optimizer_count($type){
	global $wpdb;
	switch($type){
		case "revision":
			$ewc_sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s", 'revision');
			$count = $wpdb->get_var($ewc_sql);
			break;
		case "draft":
			$ewc_sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = %s", 'draft');
			$count = $wpdb->get_var($ewc_sql);
			break;
		case "autodraft":
			$ewc_sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = %s", 'auto-draft');
			$count = $wpdb->get_var($ewc_sql);
			break;
		case "spam":
			$ewc_sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = %s", 'spam');
			$count = $wpdb->get_var($ewc_sql);
			break;
		case "trash":
			$ewc_sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = %s", 'trash');
			$count = $wpdb->get_var($ewc_sql);
			break;
	}
	return $count;
}

function sparkle_optimizer_page(){
?>
<style type="text/css"> .plugin-title{ font-size:20px !important; margin:0px 25px } </style>
<?php
	if( isset( $_GET['ewc_message'] ) ) {
		$message  = sanitize_text_field( wp_unslash( $_GET['ewc_message'] ) );
		echo '<div id="message" class="updated"><p><strong>' . esc_html($message) . '</strong></p></div>';
	}
?>

	<div class="wrap">
		<?php  if( current_user_can( 'manage_options' ) ) {	 ?>
		<h2> <?php esc_html_e( 'Sparkle: Make your old website live longer, and your new website run faster', 'sparkle'); ?> </h2>
			<table class="widefat" style="width:100%">
				<thead>
					<tr>
						<th> <?php esc_html_e( 'Type', 'sparkle'); ?>  </th>
						<th> <?php esc_html_e( 'Count', 'sparkle'); ?> </th>
						<th> <?php esc_html_e( 'Action', 'sparkle'); ?> </th>
					</tr>
				</thead>
				<tbody id="the-list">
					<tr class="alternate">
						<td class="column-name"> <?php esc_html_e( 'Revision', 'sparkle' ); ?> </td>
						<td class="column-name"><?php echo esc_html(sparkle_optimizer_count('revision')); ?></td>
						<td class="column-name">
							<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
								<input type="hidden" name="sparkle_optimizer_revision" value="revision" />
								<input type="hidden" name="action" value="sparkle_form_response">
								<?php wp_nonce_field( 'sparkle_action', 'sparkle_nonce' ); ?>
								<input type="submit" class="<?php if(sparkle_optimizer_count('revision')>0){echo esc_attr('button-primary');}else{echo esc_attr('button');} ?>" value="Delete" />
							</form>
						</td>
					</tr>
					<tr>
						<td class="column-name"> <?php esc_html_e( 'Draft', 'sparkle' ); ?> </td>
						<td class="column-name"><?php echo esc_html(sparkle_optimizer_count('draft')); ?></td>
						<td class="column-name">
							<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
								<input type="hidden" name="sparkle_optimizer_draft" value="draft" />
								<input type="hidden" name="action" value="sparkle_form_response">
								<?php wp_nonce_field( 'sparkle_action', 'sparkle_nonce' ); ?>
								<input type="submit" class="<?php if(sparkle_optimizer_count('draft')>0){echo esc_attr('button-primary');}else{echo esc_attr('button');} ?>" value="Delete" />
							</form>
						</td>
					</tr>
					<tr class="alternate">
						<td class="column-name"> <?php esc_html_e( 'Auto Draft', 'sparkle'); ?> </td>
						<td class="column-name"><?php echo esc_html(sparkle_optimizer_count('autodraft')); ?></td>
						<td class="column-name">
							<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
								<input type="hidden" name="sparkle_optimizer_autodraft" value="autodraft" />
								<input type="hidden" name="action" value="sparkle_form_response">
								<?php wp_nonce_field( 'sparkle_action', 'sparkle_nonce' ); ?>
								<input type="submit" class="<?php if(sparkle_optimizer_count('autodraft')>0){echo esc_attr('button-primary');}else{echo esc_attr('button');} ?>" value="Delete" />
							</form>
						</td>
					</tr>
					<tr class="alternate">
						<td class="column-name"> <?php esc_html_e( 'Spam Comments', 'sparkle'); ?> </td>
						<td class="column-name"><?php echo esc_html( sparkle_optimizer_count('spam') ); ?></td>
						<td class="column-name">
							<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
								<input type="hidden" name="sparkle_optimizer_spam" value="spam" />
								<input type="hidden" name="action" value="sparkle_form_response">
								<?php wp_nonce_field( 'sparkle_action', 'sparkle_nonce' ); ?>
								<input type="submit" class="<?php if(sparkle_optimizer_count('spam')>0){echo esc_attr('button-primary');}else{echo esc_attr('button');} ?>" value="Delete" />
							</form>
						</td>
					</tr>
					<tr>
						<td class="column-name"> <?php esc_html_e('Trash Comments','sparkle'); ?> </td>
						<td class="column-name"><?php echo esc_html( sparkle_optimizer_count('trash') ); ?></td>
						<td class="column-name">
							<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
								<input type="hidden" name="sparkle_optimizer_trash" value="trash" />
								<input type="hidden" name="action" value="sparkle_form_response">
								<input type="hidden" name="action" value="sparkle_form_response">
								<?php wp_nonce_field( 'sparkle_action', 'sparkle_nonce' ); ?>
								<input type="submit" class="<?php if(sparkle_optimizer_count('trash')>0){echo esc_attr('button-primary');}else{echo esc_attr('button');} ?>" value="Delete" />
							</form>
						</td>
					</tr>
				</tbody>
			</table>
			</p>

			<p>
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="sparkle_optimizer_all" value="all" />
					<input type="hidden" name="action" value="sparkle_form_response">
					<?php wp_nonce_field( 'sparkle_action', 'sparkle_nonce' ); ?>
					<input type="submit" class="button-primary" value="Delete All" />
				</form>
			</p>

			<table class="widefat" style="width:100%">
				<thead>
					<tr>
						<th>
							<strong>
								<?php esc_html_e('Warning: Once the data is being deleted those will not be possible to recover, please be sure 100% before deleting.','sparkle');?>
							</strong>
						</th>
					</tr>
				</thead>
			</table>
		<?php } else {
			esc_html_e(' "You are not authorized to perform this operation.', 'sparkle');
		} ?>
	</div>
<?php
}
add_action('admin_menu', 'sparkle_dashboard_settings');
