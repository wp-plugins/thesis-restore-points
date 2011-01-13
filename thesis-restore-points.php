<?php
/*
Plugin Name: Thesis Restore Points
Plugin URI: http://thesify.com/thesis-restore-points/
Description: Thesis Restore Points
Version: 1.0
Author: Thesify Team
Author URI: http://thesify.com
*/
define( 'TRP_DIR', WP_CONTENT_DIR . '/uploads/thesis-restore-points' );
define( 'TRP_URL', WP_CONTENT_URL . '/uploads/thesis-restore-points' );
abstract class ThesisRestorePoints
{
	static function init()
	{
		add_action( 'wp_ajax_trp', array( __CLASS__, 'ajax' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 10, 1 );
		register_post_type( 'thesis-restore-point', array(
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false, 
			'query_var' => false,
			'rewrite' => false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array( 'title', 'editor', 'author' )
		) );
				
	}

	static function admin_menu()
	{
		add_options_page( 'Thesis Restore Points Options', 'Thesis Restore Points', 'manage_options', 'thesis-restore-points', array( __CLASS__, 'plugin_options' ) );		
	}

	static function plugin_options()
	{
?>
	<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				var nonce = '<?php echo wp_create_nonce('thesis-restore-points') ?>';
				$('a.delete, a.restore, a.email').live( 'click', function(){
					var $this = $(this);
					var id = $this.closest("tr").attr('data-id');
					//alert(id);
					var method = $this.hasClass('delete') ? 'delete' : ($this.hasClass('restore') ? 'restore' : ($this.hasClass('email') ? 'email' : ('')));
					if( method == '') return;
					$.get(ajaxurl, {
						'action' : 'trp',
						'method' : method,
						'nonce': nonce,
						'r': Math.random(),
						'id' : id
					}, function(data) { 
							if(data == 1)
							{
								if(method == 'delete')
									$this.closest('tr').hide();
								if(method == 'email')
									alert('Email sent!');
								if(method == 'restore')
									alert('Restored');
							}
						 } );

					return false;
				});

				var create_backup = function(){
					var name = $("#name").val();
					$.get(ajaxurl, {
						'action' : 'trp',
						'method' : 'backup',
						'nonce': nonce,
						'name' : name
					}, function(data) { $(data).insertAfter('#current-backups tr:first'); } );

					return false;
				}
				$('a.new').click(create_backup);
				$('#name').keypress(function(e){if(e.which == 13 || e.which == 10) return create_backup(); });
			});
		})(jQuery);
	</script>
<div class="wrap">
<h2>Thesis Restore Points</h2>
<h3>Create Backup</h3>

	<input type="text" id="name" name="name" value="Backup Name" />
	<a class="button new">Go</a>

<h3>Current Backups</h3>
<table id="current-backups"class="widefat" cellspacing="0" style="width:700px">
<thead>
	<tr>
		<th>Date created</th>
		<th>Description</th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php
		$posts = get_posts( array( 'post_type' => 'thesis-restore-point', 'numberposts' => -1 ) );
		foreach ( $posts as $post ) {
			self::render_row( $post );
		}
?>
</tbody>
</table>
</div>
<?php
	}	
	
	static function ajax()
	{
		isset( $_GET['method'] ) && current_user_can( 'manage_options' ) ? $method = $_GET['method'] : die;
		if( ! wp_verify_nonce( $_GET['nonce'], 'thesis-restore-points' ) ) die;
		switch( $method )
		{
			case 'backup':
				self::init_backup();

				$uploads_dir = wp_upload_dir();
				$uploads_dir = $uploads_dir['path'];
				
				$custom_folder = THESIS_CUSTOM;
				
				$thesis_options = serialize( get_option( 'thesis_options' ) );
				$thesis_design_options = serialize( get_option( 'thesis_design_options' ) );
				
				$files = array();
				$files[] = TRP_DIR . '/thesis_options.dat';
				$files[] = TRP_DIR . '/thesis_design_options.dat';
				
				file_put_contents( $files[0], $thesis_options );
				file_put_contents( $files[1], $thesis_design_options );
				
				
				$backup_file = '/backup-' . md5( mt_rand() ) . '.zip';
				$archive = new PclZip( TRP_DIR . $backup_file );
				
				function exclude_cache( $p_event, &$p_header )
				{
					$s = array( '/', '\\' );
					if( strpos( $p_header['filename'], str_replace( '\\', '/', THESIS_CUSTOM . '/cache' ) ) === 0 )
						return 0;
					return 1;
				}
				
				$o = $archive->add( THESIS_CUSTOM, PCLZIP_OPT_REMOVE_PATH, THESIS_CUSTOM, PCLZIP_OPT_ADD_PATH, 'custom', PCLZIP_CB_PRE_ADD, 'exclude_cache' );
				$o = $archive->add( $files, PCLZIP_OPT_REMOVE_PATH, TRP_DIR );
				
				if ( $o == 0 ) {
					echo( "Error : ".$archive->errorInfo( true ) );
				} else {
					$title = isset( $_GET['name'] ) && strlen( trim( $_GET['name'] ) ) > 0 ? $_GET['name'] :  'Backup on ' . date( 'M D s' );
					$post = array( 'post_title' => $title, 'post_status' => 'publish', 'post_type' => 'thesis-restore-point' );
					$post_id = wp_insert_post( $post );
					update_post_meta( $post_id, 'url', TRP_URL . $backup_file );
					update_post_meta( $post_id, 'path', str_replace( '\\', '/', TRP_DIR ) . $backup_file );
				}
				@unlink( $files[0] );
				@unlink( $files[1] );

				die( self::render_row( get_post( $post_id ) ) );
				break;
				
			case 'restore':
			
			
				break;
			
			case 'email':
				if( !isset( $_GET['id'] ) ) die( '0' );
				$post_id = $_GET['id'];
				$post = get_post( $post_id );
				if( !$post ) die( '0' );
				
				
				$email = get_option( 'admin_email' );
				$subject = 'Thesis Custom Folder Backup on ' . current_time( 'mysql' );
				$message = 'Attached';
				$headers = '';
				$attachments = get_post_meta( $post_id, 'path' );
				
				wp_mail( $email, $subject, $message, $headers, $attachments );
				break;

			case 'delete':
				// unlink file
				if( !isset( $_GET['id'] ) ) die( '0' );
				$post_id = $_GET['id'];
				$post = get_post( $post_id );
				if( !$post ) die( '0' );

				$file = get_post_meta( $post_id, 'path', true );
				
				@unlink( $file );

				echo ( wp_delete_post( $post_id ) !== false ? 1 : 0 );
		}
		die;
	}
	
	static function init_backup()
	{
		require_once( ABSPATH . '/wp-admin/includes/class-pclzip.php' );
		if( !is_dir( TRP_DIR ) )
			wp_mkdir_p( TRP_DIR );	
		if( !file_exists( TRP_DIR . '/index.html' ) )
		{
			file_put_contents( TRP_DIR . '/index.html', '' );
		}
	}

	private function render_row( $post )
	{
		echo '<tr data-id='.$post->ID.'>';
		echo '<td>' . $post->post_date . '</td>';
		echo '<td>' . $post->post_title . '</td>';
		echo '<td>' . '<a href="' . get_post_meta( $post->ID, 'url', true ) . '">Download</a> | ';
		echo '<a class="restore" href="#">Restore</a> | ';
		echo '<a class="email" href="">Email me</a> | ';
		echo '<span class="delete"><a href="" class="submitdelete delete">Delete</a></span>';
		echo '</td>';
		echo '</tr>';
	}
	
}

ThesisRestorePoints::init();
