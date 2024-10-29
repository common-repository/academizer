<?php
add_action('init', 'academizer_register_reference');

function academizer_register_reference() {
 
	$labels = array(
		'name'                  => _x( 'References', 'Reference General Name', 'academizer' ),
		'singular_name'         => _x( 'Reference', 'Reference Singular Name', 'academizer' ),
		'menu_name'             => __( 'Academizer References', 'academizer' ),
		'name_admin_bar'        => __( 'Reference', 'academizer' ),
		'add_new_item'          => __( 'Add New Reference', 'academizer' ),
		'edit_item'             => __( 'Edit Reference', 'academizer' ),
		'featured_image'        => __( 'Reference Thumbnail', 'academizer' ),
	);
	$args = array(
	    'labels' 				=> $labels ,
	    'public' 				=> true,
	    'publicly_queryable' 	=> true,
	    'show_ui' 				=> true, 
	    'query_var' 			=> true,
	    'rewrite' 				=> array('slug'=>'references'),
	    'hierarchical' 			=> false,
	    'menu_position' 		=> null,
	    'capability_type' 		=> 'post',
	    'supports' 				=> array('title', 'editor','page-attributes','thumbnail'),
	    'has_archive'			=> false,
	    'menu_icon' 			=> 'dashicons-format-aside'
	); 
	register_post_type('academizer_reference', $args);
}

add_action( 'load-edit-tags.php', 'academizer_edit_tags' );
add_action( 'load-term.php', 'academizer_edit_tags' );

function academizer_edit_tags() {
	if( 'academizer_reference' !== get_current_screen()->post_type ) {
		return;
	}
	add_action("academizer_reftype_pre_add_form", 'academizer_scripts_new_academizer_reftype');
	add_action("academizer_reftype_pre_edit_form", 'academizer_scripts_edit_academizer_reftype');
}

function academizer_scripts_new_academizer_reftype() {
	wp_enqueue_script('academizer_bibtex', ACADEMIZER_PLUGIN_PATH. 'js/bibtexParse.js');
	wp_enqueue_script('academizer_clientReftype', ACADEMIZER_PLUGIN_PATH. 'js/clientReftype.js');
	wp_enqueue_style('academizer_settings', ACADEMIZER_PLUGIN_PATH. 'css/academizer-settings.css');
	wp_enqueue_style('academizer_dropdown', ACADEMIZER_PLUGIN_PATH. 'css/dropdown.css');
	wp_enqueue_style('academizer_tabmenu', ACADEMIZER_PLUGIN_PATH. 'css/tabmenu.css');
	wp_localize_script('academizer_clientReftype', 'academizer', array (
	    'pluginPath'    =>  ACADEMIZER_PLUGIN_PATH,
        'ajax_url'      =>	admin_url( 'admin-ajax.php' )));
}

function academizer_scripts_edit_academizer_reftype() {
	wp_enqueue_script('academizer_bibtex', ACADEMIZER_PLUGIN_PATH. 'js/bibtexParse.js');
	wp_enqueue_script('academizer_clientReftype', ACADEMIZER_PLUGIN_PATH. 'js/clientReftype.js');
	wp_enqueue_style('academizer_tabmenu', ACADEMIZER_PLUGIN_PATH. 'css/tabmenu.css');
	wp_localize_script('academizer_clientReftype', 'academizer', array (
        'pluginPath'    =>  ACADEMIZER_PLUGIN_PATH,
        'ajax_url'      =>	admin_url( 'admin-ajax.php' )));
}

add_action( 'init', 'academizer_create_reference_taxonomies',0); 
function academizer_create_reference_taxonomies(){
    register_taxonomy(
        'academizer_reftype', 'academizer_reference',
        array(
            'hierarchical'=> true, 
            'label' => 'Reference Types',
            'singular_label' => 'Reference Type',
			'show_ui' => true,
            'show_admin_column' => true,
			'meta_box_cb' => false,
            'rewrite' => true
        )
    );    
}

add_filter( 'manage_edit-academizer_reftype_columns', 'academizer_reftype_columns');
function academizer_reftype_columns($columns) {
	$columns = array(
		'name' => __('Name'),
		'entry_type' => __('Bibtex Entry Type'),
		'slug' => __('Slug'),
		'posts' => __('Posts')
		);
	return $columns;
}

add_filter('manage_academizer_reftype_custom_column', 'academizer_reftype_entry_type_column_content', 10, 3);
function academizer_reftype_entry_type_column_content($content, $column_name, $term_id){
	switch ($column_name) {
		case 'entry_type':
			$content .= get_term_meta($term_id, 'academizer_entry_type', true);
			break;
		
		default:
			break;
	}
	
	return $content;
}

add_filter('manage_edit-academizer_reference_columns', 'academizer_add_new_reference_columns');
function academizer_add_new_reference_columns( $columns ){
	    
	$columns['title']	= __('Reference Title',"academizer");
	$columns['menu_order'] = __('Order',"academizer");
    unset($columns['jss_post_thumb']);

    return $columns;
}

add_filter('manage_edit-academizer_reference_sortable_columns', 'academizer_register_reference_sortable_columns');
function academizer_register_reference_sortable_columns(){
	return array(
		'title' => "title",
		"date"  => "date",
		'menu_order' => 'menu_order',
		'taxonomy-academizer_reftype' => 'taxonomy-academizer_reftype'
	);
}

add_filter( 'default_hidden_meta_boxes', 'academizer_hidden_meta_boxes', 10, 2 );
function academizer_hidden_meta_boxes( $hidden, $screen ) {
    return array( 'commentstatusdiv'); // get these from the css class
}

add_action('restrict_manage_posts', 'academizer_restrict_reference_type');
function academizer_restrict_reference_type() {
	global $typenow;
	$post_type = 'academizer_reference';
	$taxonomy = 'academizer_reftype';
	if ($typenow == $post_type) {
		$selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
		$info_taxonomy = get_taxonomy($taxonomy);
		wp_dropdown_categories(array(
			'show_option_all' => __("Show All {$info_taxonomy->label}"),
			'taxonomy' => $taxonomy,
			'name' => $taxonomy,
			'orderby' => 'name',
			'selected' => $selected,
			'show_count' => true,
			'hide_empty' => true,
		));
	};
}

add_action( 'academizer_reftype_add_form_fields', 'academizer_reftype_add_form_fields', 10, 2);
function academizer_reftype_add_form_fields( $taxonomy) {
	?>
	<div class="form-field form-required">
		<label for="entry_type"><?php _e('Entry Type', 'academizer'); ?></label>
		<select required aria-required="true" name="entry_type" id="entry_type">
			<option value="article">Article</option>
			<option value="book">Book</option>
            <option value="booklet">Booklet</option>
            <option value="inbook">InBook</option>
            <option value="incollection">InCollection</option>
			<option value="inproceedings">InProceedings</option>
            <option value="manual">Manual</option>
            <option value="mastersthesis">MastersThesis</option>
            <option value="misc">Misc</option>
            <option value="phdthesis">PhdThesis</option>
            <option value="proceedings">Proceedings</option>
            <option value="techreport">TechReport</option>
            <option value="unpublished">Unpublished</option>
		</select>
		<p class="description">The Bibtex keyword used to differentiate entries.</p>
	</div>
    <div class="form-field form-required">
        <label for="format"><?php _e('Short citation format', 'academizer'); ?></label>
        <textarea id="format-short" name="format-short" class="ref-format-short" rows="3" width="100%"></textarea>
        <p class="description">The format to use to render only the name of the publication (required for some output styles).</p>
    </div>
    <div class="form-field form-required">
        <label for="format"><?php _e('Full citation format (required)', 'academizer'); ?></label>
        <textarea id="format-full" name="format-full" class="ref-format" rows="3" width="100%"></textarea>
        <p class="description">The format to use to render references of this type.</p>
    </div>
	<div>
		<h2>Preview</h2>
		<?php academizer_print_reference_preview_area($taxonomy);
		academizer_print_reference_predefined_accordion();?>
	</div><?php
}

function academizer_print_reference_preview_area($taxonomy, $entry_type='inproceedings', $format=NULL) {
	?><div class="tabs">
		  <input id="tab-1" type="radio" name="radio-set" class="tab-selector-1" <?php checked('inproceedings',$entry_type)?>data-index="1"/>
		  <label for="tab-1" class="tab-label-1">Conference</label>
		  <input id="tab-2" type="radio" name="radio-set" class="tab-selector-2" <?php checked('article',$entry_type)?>data-index="2"/>
		  <label for="tab-2" class="tab-label-2">Journal</label>
		  <div class="content">
			<div class="content-1">
				<input name="bibtex-1" type="hidden"/>
				<p id="preview-1"><?php if(empty($format)) echo 'Please enter a new format.'; ?></p>
			</div>
			<div class="content-2">
				<input name="bibtex-2" type="hidden"/>
				<p id="preview-2"><?php if(empty($format)) echo 'Please enter a new format.'; ?></p>
			</div>
		  </div>
    </div>
	<?php
}

function academizer_print_reference_predefined_accordion() {
	?><div><ul class="cd-accordion-menu animated">
		<li class="has-children">
			<input type="checkbox" name="group-1" id="group-1" checked>
			<label for="group-1">Predefined Formats</label>
			<ul>
			  <li class="has-children">
				<input type="checkbox" name="sub-group-1" id="sub-group-1">
				<label for="sub-group-1">ACM</label>
				<ul>
				  <li><a id="acm-proc" href="#0">Conference</a></li>
				  <li><a id="acm-journal" href="#0">Journal</a></li>
				</ul>
			  </li>
			  <li class="has-children">
				<input type="checkbox" name="sub-group-2" id="sub-group-2">
				<label for="sub-group-2">APA</label>
				<ul>
				  <li><a id="apa-proc" href="#0">Conference</a></li>
				  <li><a id="apa-journal" href="#0">Journal</a></li>
				</ul>
			  </li>
			  <li class="has-children">
                    <input type="checkbox" name="sub-group-3" id="sub-group-3">
                    <label for="sub-group-3">Harvard</label>
                    <ul>
                        <li><a id="hvd-proc" href="#0">Conference</a></li>
                        <li><a id="hvd-journal" href="#0">Journal</a></li>
                    </ul>
               </li>
              <li class="has-children">
                    <input type="checkbox" name="sub-group-4" id="sub-group-4">
                    <label for="sub-group-4">IEEE</label>
                    <ul>
                        <li><a id="ieee-proc" href="#0">Conference</a></li>
                        <li><a id="ieee-journal" href="#0">Journal</a></li>
                    </ul>
              </li>
			</ul>
		  </li>
		</ul>
	</div><?php
}

add_action( 'created_academizer_reftype', 'academizer_reftype_save_meta', 10, 2);
add_action( 'edited_academizer_reftype', 'academizer_reftype_save_meta', 10, 2);
function academizer_reftype_save_meta( $term_id, $tag_id ){
	if( isset( $_POST['entry_type'])) {
		update_term_meta( $term_id, 'academizer_entry_type', sanitize_text_field( $_POST['entry_type'] ) );
	}
	
    if( isset( $_POST['format-full'] ) ) {
		update_term_meta( $term_id, 'academizer_format_full', sanitize_textarea_field(esc_attr($_POST['format-full'])));
    }

    if( isset( $_POST['format-short'] ) ) {
        update_term_meta( $term_id, 'academizer_format_short', sanitize_textarea_field(esc_attr($_POST['format-short'])));
    }
}

add_filter('pre_insert_term', 'academizer_reftype_check_term', 20, 2);

function academizer_reftype_check_term($term, $taxonomy) {
	if ($taxonomy!='academizer_reftype')
		return $term;
	$entry_type = $_POST['entry_type'];
	$args = array(
		'taxonomy'	=> $taxonomy,
		'hide_empty'=> false,
		'fields'	=> 'all',
		'count'		=> 'true',
		'meta_value'	=> $entry_type,
	);
	$term_query = new WP_Term_Query( $args );
	if (sizeof($term_query->terms)>0)
		$term = new WP_Error( 'invalid_entry_type', 'There is already a reference type using that Bibtex entry type.');
	
	return $term;
}

add_action( 'academizer_reftype_edit_form_fields', 'academizer_reftype_edit_meta_fields', 10, 2);
function academizer_reftype_edit_meta_fields( $term, $taxonomy ) {
	$format_full = get_term_meta($term->term_id, 'academizer_format_full', true);
    $format_short= get_term_meta($term->term_id, 'academizer_format_short', true);
	$entry_type = get_term_meta($term->term_id, 'academizer_entry_type', true);
	?>
	<tr class="form-field form-required term-group-wrap">
        <th scope="row">
            <label for="entry_type"><?php _e( 'Entry type', 'academizer' ); ?></label>
        </th>
        <td>
			<select name="entry_type" id="entry_type">
				<option value="article" <?php selected( $entry_type, 'article' ); ?> >Article</option>
				<option value="book" <?php selected( $entry_type, 'book' ); ?> >Book</option>
                <option value="booklet" <?php selected( $entry_type, 'booklet' ); ?> >Booklet</option>
                <option value="inbook" <?php selected( $entry_type, 'inbook' ); ?> >InBook</option>
                <option value="incollection" <?php selected( $entry_type, 'incollection' ); ?> >InCollection</option>
                <option value="inproceedings" <?php selected( $entry_type, 'inproceedings' ); ?> >InProceedings</option>
                <option value="manual" <?php selected( $entry_type, 'manual' ); ?> >Manual</option>
                <option value="mastersthesis" <?php selected( $entry_type, 'mastersthesis' ); ?> >MastersThesis</option>
                <option value="misc" <?php selected( $entry_type, 'misc' ); ?> >Misc</option>
                <option value="phdthesis" <?php selected( $entry_type, 'phdthesis' ); ?> >PhdThesis</option>
                <option value="proceedings" <?php selected( $entry_type, 'proceedings' ); ?> >Proceedings</option>
                <option value="techreport" <?php selected( $entry_type, 'techreport' ); ?> >TechReport</option>
                <option value="unpublished" <?php selected( $entry_type, 'unpublished' ); ?> >Unpublished</option>
			</select>
			<p class="description">The Bibtex keyword used to differentiate entries.</p>
        </td>
    </tr>
    <tr class="form-field term-group-wrap">
        <th scope="row">
            <label for="format"><?php _e( 'Short citation format', 'academizer' ); ?></label>
        </th>
        <td>
            <textarea id="format-short" name="format-short" class="ref-format-short" rows="3" width="100%"><?php echo $format_short; ?></textarea>
            <p class="description">The format to use to render the publication name of this type.</p>
        </td>
    </tr>
	<tr class="form-field form-required term-group-wrap">
        <th scope="row">
            <label for="format"><?php _e( 'Full citation format', 'academizer' ); ?></label>
        </th>
        <td>
            <textarea id="format-full" name="format-full" class="ref-format" rows="3" width="100%"><?php echo $format_full; ?></textarea>
			<p class="description">The format to use to render references of this type.</p>
			<?php academizer_print_reference_preview_area($taxonomy, $entry_type, $format_full);?>
        </td>
    </tr>
	<?php
}