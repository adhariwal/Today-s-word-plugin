<?php
	/*
		Plugin Name: Today’s Word
		Plugin URI: http://Test.com
		Description: custom WordPress plugin named - Today’s Word.
		Author: Abhijeet Dhariwal
		Version: 1.0
		Author URI: 
	*/
	
	
	/**
		* Activate the plugin.
	*/
	function TodaysWord_activate() { 
		
		
		
	}
	register_activation_hook( __FILE__, 'TodaysWord_activate' );
	
	
	/**
		* Deactivation hook.
	*/
	function TodaysWord_deactivate() {
		
	}
	register_deactivation_hook( __FILE__, 'TodaysWord_deactivate' );
	
	
	function jquery_script() {
		wp_enqueue_script( 'jquery' );
	}
	add_action( 'wp_enqueue_scripts', 'jquery_script' );
	// Creating the widget 
	
	class TodaysWord_widget extends WP_Widget {
		
		function __construct() {
			
			parent::__construct(
           	'TodaysWord_widget', 
			
			// Widget name will appear in UI
			__("Today's word","TodaysWord"),
			);
			
			add_action( 'widgets_init', function() {
				register_widget( 'TodaysWord_widget' );
			});
			
		}
		
		public $args = array(
        'before_title'  => '<h4 class="widgettitle">',
        'after_title'   => '</h4>',
        'before_widget' => '<div class="widget-wrap">',
        'after_widget'  => '</div></div>'
		);
		
		public function widget( $args, $instance ) {
			
			echo $args['before_widget'];
			
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
			}
			
		?>
<div class="<?php echo $args['widget_id']; ?>">
    <img src="<?php echo plugin_dir_url( __FILE__ ); ?>images/loader.gif" id="loader_<?php echo $args['widget_id']; ?>">
    <div id="Dictionary_<?php echo $args['widget_id']; ?>" style="display:none;">
        <?php 
					if($instance['override']){
						foreach($instance['override'] as $key => $value){
							$post = get_post( $value); 
						?>
        <p> <?php echo __($post->post_title); ?> ..... <?php echo  __($post->post_content); ?></p>
        <?php
						} 
					} ?>
    </div>
    <input type="text" class="search-field" id="Search_<?php echo $args['widget_id']; ?>"
        name="<?php echo $args['widget_id']; ?>">
    <script>
    jQuery(document).ready(function() {
        jQuery('#loader_<?php echo $args['widget_id']; ?>').hide();
        jQuery('#Dictionary_<?php echo $args['widget_id']; ?>').show();
        jQuery('#Search_<?php echo $args['widget_id']; ?>').on('keypress', function(e) {
            if (e.which == 13) {
                jQuery('#loader_<?php echo $args['widget_id']; ?>').show();
                jQuery('#Dictionary_<?php echo $args['widget_id']; ?>').hide();
                e.preventDefault();
                var Value = jQuery("#Search_<?php echo $args['widget_id']; ?>").val();

                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: 'post',
                    data: {
                        action: 'my_ajax_handler',
                        SearchData: Value
                    },
                    success: function(response) {

                        var Html = "";
                        if (response.data.length != 0) {
                            jQuery.each(response.data, function(key, dataValue) {
                                Html += "<p>" + dataValue['post_title'] + "...." +
                                    dataValue[
                                        'post_content'] + "</p>";
                            });
                        } else {
                            Html = "<?php echo __('Word was not found.'); ?>";
                        }


                        jQuery('#Dictionary_<?php echo $args['widget_id']; ?>').html(Html);
                        jQuery('#loader_<?php echo $args['widget_id']; ?>').hide();
                        jQuery('#Dictionary_<?php echo $args['widget_id']; ?>').show();
                    }
                });
            }
        });
    });
    </script>
</div>
<?php
			
			echo $args['after_widget'];
			
		}
		
		public function form( $instance ) {
			
			
			if ( isset( $instance[ 'title' ] ) ) {
				$title = $instance[ 'title' ];
				$override = $instance[ 'override' ];;
			}
			else {
				$title = __( "Today's words" ,"TodaysWord");
				$override = "";
			}
			// Widget admin form
			if( $instance) {
				$select = $instance['override']; // Added 
				} else {
				$select =array();
			}
		?>

<p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ,"TodaysWord"); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
        name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'override' ); ?>"><?php _e( 'Word Override:',"TodaysWord" ); ?></label>
    <select multiple="multiple" name="<?php echo $this->get_field_name('override'); ?>[]"
        id="<?php echo $this->get_field_id('override'); ?>" class="widefat" size="15" style="margin-bottom:15px;">
        <?php
					$args = array('posts_per_page' => 200, 'post_status' => 'publish',"post_type"=>"word" );
					
					// The Query
					query_posts( $args );
					
					// The Loop
					while ( have_posts() ) : the_post();
					$optionSelected = "";
					if(in_array(get_the_ID(), $select) == 1){
						$optionSelected = ' selected="selected"';
					}
				?>
        <option value="<?php echo get_the_ID();?>" class="hot-topic" <?php   echo $optionSelected;?>
            style="margin-bottom:3px;">
            <?php echo get_the_title();?>
        </option>
        <?php
					endwhile;
					
					// Reset Query
					wp_reset_query();
				?>
    </select>
</p>
<?php 
			
		}
		
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			$instance['override'] = ( ! empty( $new_instance['override'] ) ) ?  $new_instance['override']  : '';
			return $instance;
		}
		
	}
	$TodaysWord_widget = new TodaysWord_widget();
	
	// Register and load the widget
	function TodaysWord_load_widget() {
		register_widget( 'TodaysWord_widget' );
	}
	add_action( 'widgets_init', 'TodaysWord_load_widget' );
	
	
	add_action( 'wp_ajax_nopriv_my_ajax_handler', 'my_ajax_handler' );
	add_action( 'wp_ajax_my_ajax_handler', 'my_ajax_handler' );
	
	function my_ajax_handler() {
		//SearchData
		
		$args = array("post_type" => "word", "s" => $_POST['SearchData']);
		$query = get_posts( $args );
		
		wp_send_json_success( $query);
	}
	
	add_action('admin_menu', 'add_importer_submenu');
	
	//admin_menu callback function
	
	function add_importer_submenu(){
		
		add_submenu_page(
		'edit.php?post_type=word', //$parent_slug
		__('Import menu item',"TodaysWord"),  //$page_title
		__('Import menu item',"TodaysWord"),        //$menu_title
		'manage_options',           //$capability
		'Import_word_item',//$menu_slug
		'Import_page' //$function
		);
		
	}
	
	//add_submenu_page callback function
	
	function Import_page() {
		
		
	?>

<h2><?php _e('Import Word',"TodaysWord"); ?></h2>

<?php
		
		if(isset($_POST['FileData'])){
			$fileType = $_FILES['import']['type'];
			if ($fileType != "application/json") {
				echo '<div class="notice notice-error"><p>'.__('File extension is not json',"TodaysWord").'</p></div>';
				}else{
				
				$fileData=file_get_contents($_FILES["import"]["tmp_name"]);
				$fileDataArray = json_decode($fileData);
				if(isset($fileDataArray->words)){
					$InsertPostCount = 0;
					$UpdatePostCount = 0;
					foreach($fileDataArray->words as $key=>$value){
						$checkPost = post_exists( wp_strip_all_tags( $value->word ),'','','');
						if($checkPost == 0){
							// Create post object
							$my_post = array(
							'post_title'    => wp_strip_all_tags( $value->word ),
							'post_content'  =>  $value->definition ,
							'post_status'   => 'publish',
							'post_author'   => 1,
							'post_type'     => 'word',
							);
							
							// Insert the post into the database
							wp_insert_post( $my_post );
							$InsertPostCount ++;
							}else{
							$my_post = array(
							'ID'           => $checkPost,
							'post_title'   => wp_strip_all_tags( $value->word ),
							'post_content' => $value->definition ,
							);
							
							// Update the post into the database
							wp_update_post( $my_post );
							$UpdatePostCount++;
							
						}
					}
					
					$mgs = __("Total ","TodaysWord") .count($fileDataArray->words)  .__(" records added words are ","TodaysWord"). $InsertPostCount .__(" and updated words are ","TodaysWord").$UpdatePostCount;
					
					
					
					echo '<div class="notice notice-success is-dismissible"><p>'.$mgs.'</p></div>';
					}else{
					echo '<div class="notice notice-error"><p>'.__('Json formate is not match !!!',"TodaysWord").'</p></div>';
				}
				
			}
		}
	?>

<div class="narrow">
    <p><?php echo __("Choose a JOSN file to upload, then click Upload file and import.","TodaysWord"); ?></p>
    <p> <a download href="<?php echo plugin_dir_url( __FILE__ ); ?>/demo.json">Download Json</a></p>
    <form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form" action="">
        <p>
            <label for="upload"><?php _e('Choose a file from your computer:',"TodaysWord"); ?></label>
            <?php _e('(Maximum size: 8 GB)'); ?>
            <input type="file" accept="application/JSON" id="upload" name="import" size="25">
            <input type="hidden" name="FileData" value="the_form_response">
            <input type="hidden" name="max_file_size" value="8388608000">
        </p>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                value="<?php _e('Upload file and import',"TodaysWord"); ?>" disabled=""></p>
    </form>
</div>
<?php }	?>