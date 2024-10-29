<?php
/** Reference meta box
 *
 */

function academizer_add_reference_meta_boxes() {
    add_meta_box('academizer_reference_meta_box', __('Reference Data', 'academizer'), 'academizer_reference_build_meta_box', 'academizer_reference', 'normal', 'low');
}

function academizer_metabox_enqueue_styles(){
    if( 'academizer_reference' !== get_current_screen()->post_type )
		return;
    wp_enqueue_style('academizer_settings', ACADEMIZER_PLUGIN_PATH . 'css/academizer-settings.css');
}

function academizer_metabox_enqueue_scripts() {
    if( 'academizer_reference' !== get_current_screen()->post_type )
		return;

    wp_enqueue_style('academizer_css_settings', ACADEMIZER_PLUGIN_PATH . 'css/academizer-settings.css');
    wp_enqueue_script('academizer_bibtexParser', ACADEMIZER_PLUGIN_PATH . 'js/bibtexParse.js');
    wp_enqueue_script('academizer_clientMetabox', ACADEMIZER_PLUGIN_PATH . 'js/clientMetabox.js');
    wp_localize_script('academizer_clientMetabox', 'academizer', array('pluginPath' => ACADEMIZER_PLUGIN_PATH, 'ajax_url' => admin_url('admin-ajax.php')));
}

add_action('admin_enqueue_scripts', 'academizer_metabox_enqueue_scripts');
add_action('add_meta_boxes_academizer_reference', 'academizer_add_reference_meta_boxes');
add_action('save_post_academizer_reference', 'academizer_save_reference_meta_box_data');
add_action('wp_ajax_academizer_ajax_render_bibtex', 'academizer_ajax_render_bibtex');
add_action('wp_ajax_academizer_ajax_query_terms', 'academizer_ajax_query_terms');

function academizer_ajax_query_terms()
{
    $terms = academizer_retrieve_terms($_POST['entry_type']);
    echo sizeof($terms);
}

function academizer_ajax_render_bibtex()
{
    $parser = new AcademizerBibtexParser();
    $json_string = stripslashes($_POST['bibtex']);
    $json_obj = json_decode($json_string);
    if (empty($json_obj)) {
        wp_die(json_encode(array(
            'message' => "Bibtex is invalid.",
            'result' => -1)));
    }

    $entry_type = $json_obj[0]->entryType;
    $terms = academizer_retrieve_terms($entry_type);
    $term = $terms[0];
    $term_id = $term->term_id;

    if (sizeof($term_id) == 0 && empty($_POST['format'])) {
        wp_die(json_encode(array(
            'message' => "Bibtex type: <strong>{$entry_type}</strong> not found. Please add a <em>Reference Type</em> first.",
            'result' => -1)));
    }
    if (empty($_POST['format'])) {
        $format = array(get_term_meta($term_id, 'academizer_format_full', true));
        if (empty($format))
            wp_die(json_encode(array(
                'message' => "No format specified for <strong>{$json_obj[0]->entryType}</strong>.",
                'result' => -1)));
    } else
        $format = array($_POST['format']);

    $options = get_option('academizer_options');
    $ref = AcademizerReference::Create(
        $options['name'],
        $json_string,
        $format
    );

    $parser->set($ref);

    wp_die(json_encode(array(
        'message' => $parser->fullCitation(),
        'result' => 1)));
}

function academizer_reference_build_meta_box($post)
{
    global $pagenow;
    wp_nonce_field(basename(__FILE__), 'academizer_meta_box_nonce');
    $options = get_option('academizer_options');

    if (($pagenow == 'post.php') && ($_GET['action'] == 'edit')) {
        $terms = get_the_terms($post->ID, 'academizer_reftype');
        $entry_term = $terms[0];
        $ref = AcademizerReference::Create(
            $options['name'],
            get_post_meta($post->ID, 'academizer_bibtex_json', true),
            get_term_meta($entry_term->term_id, 'academizer_format', true),
            get_post_meta($post->ID, 'academizer_links', true)
        );
    } else {
        $ref = new AcademizerReference();
        $ref->me_full_name = $options['name'];
    }

    ?>
    <div class='inside'>
        <div>
            <h3><?php _e('Bibtex', 'academizer'); ?></h3>
            <p>
                <textarea name="bibtex" class="input" rows="12" width="100%"></textarea>
                <input name="bibtex_json" type="hidden" value="<?php echo htmlspecialchars($ref->bibtex_json); ?>"/>
            </p>
            <p class="description"><?php _e('Single Bibtex entry, e.g. @article{...}.', 'academizer'); ?></p>
        </div>
        <div>
            <h3><?php _e('Reference preview', 'academizer'); ?></h3>
            <div id="output"/>
        </div>
        <div><?php academizer_create_metaTable($ref->links);?>
            <div id="academizer-metaTable-validation"></div>
            <button id="academizer-addNew" type="button" class="button plus">New row</button>
        </div>
        <div>
            <h3><?php _e('Tags', 'academizer'); ?></h3>
            <input name="tags" id="tags" class="input" type="text" value="<?php echo get_post_meta($post->ID, 'academizer_tags', true); ?>"/>
            <p clas="description"><?php _e('A list of user-defined tags, separated by commas.', 'academizer'); ?></p>
        </div>
    </div>
    <?php
}

function academizer_create_metaTable($links) {
    $idx = 1;
    ?><div>
    <h3><?php _e('Reference Metadata', 'academizer'); ?></h3>
    <table id="academizer-metaTable"><?php
    if (!empty($links)) {
        foreach (array_keys($links) as &$key) {
            academizer_create_metaData($idx, $key, $links[$key]);
            $idx++;
        }
    } else {
        academizer_create_metaData(0, "", "");
    }
    ?>
    </table>
    <?php
}

function academizer_create_metaData($idx, $key, $value) {
    $index = sprintf('%02d', $idx);
    ?><tr class="academizer-metaData" data-index="<?php echo $index;?>">
        <td class="academizer-metaValue">
            <input name="academizer_metaValue<?php echo $index; ?>" type="text" value="<?php echo $value; ?>"/></td>
        <td class="academizer-metaKey">
            <select name="academizer_metaKey<?php echo $index; ?>">
                <option value="none" <?php selected( $key, 'none' );?>>Select a category</option>
                <option value="paper_url" <?php selected( $key, 'paper_url' );?>>Paper URL</option>
                <option value="event_url" <?php selected( $key, 'event_url' );?>>Event URL</option>
                <option value="git_url" <?php selected( $key, 'git_url' );?>>GitHub Repo URL</option>
                <option value="slides_url" <?php selected( $key, 'slides_url' );?>>Slides URL</option>
                <option value="talk_url" <?php selected( $key, 'talk_url' );?>>Talk video URL</option>
                <option value="video_url" <?php selected( $key, 'video_url' );?>>Video URL</option>
            </select>
        </td>
        <td class="academizer-metaCmd">
            <button type="button" class="button minus" disabled/>
        </td>
    </tr>
    <?php
}

function academizer_retrieve_terms($entry_type) {
    $args = array(
        'taxonomy' => 'academizer_reftype',
        'hide_empty' => false,
        'fields' => 'all',
        'count' => 'true',
        'meta_value' => $entry_type,
    );
    $term_query = new WP_Term_Query($args);
    return $term_query->terms;
}

function academizer_save_reference_meta_box_data($post_id)
{
    $taxonomy = 'academizer_reftype';

    // verify taxonomies meta box nonce
    if (!isset($_POST['academizer_meta_box_nonce']) || !wp_verify_nonce($_POST['academizer_meta_box_nonce'], basename(__FILE__))) {
        return;
    }
    // return if autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // Check the user's permissions.
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_REQUEST['bibtex_json'])) {
        $bibtex_json = stripslashes($_REQUEST['bibtex_json']);
        if (!empty($bibtex_json))
            update_post_meta($post_id, 'academizer_bibtex_json', $bibtex_json);
        else
            delete_post_meta($post_id, 'academizer_bibtex_json');

        $json_obj = json_decode($bibtex_json);
        if (!empty($json_obj) && !empty($json_obj[0])) {
            $entry = $json_obj[0];
            $terms = academizer_retrieve_terms($entry->entryType);
            wp_set_object_terms($post_id, array($terms[0]->term_id), $taxonomy);
            $entryTags = $entry->entryTags;
            $year = $entryTags->year;
            $month =$entryTags->month;
            if (empty($month))
                $month = 'Jan';
            $date = date_parse("{$month} {$year}");
            $date_string = date('Y-m-d', mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']));

            if (!empty($date_string))
                update_post_meta($post_id, 'academizer_date', $date_string);
            else delete_post_meta($post_id, 'academizer_date');
        }
    }

    $links = array();

    $arrayValues = array_values(array_filter_key($_REQUEST, function($key) {
        return strpos($key, 'academizer_metaValue') === 0;
    }));

    $arrayKeys = array_values(array_filter_key($_POST, function($key) {
        return strpos($key, 'academizer_metaKey') === 0;
    }));

    $idx = 0;
    foreach ($arrayKeys as &$key) {
        $value = esc_url_raw($arrayValues[$idx]);
        if ($key=="none" || empty($value))
            continue;
        $links += [ sanitize_text_field($key) => esc_url_raw($arrayValues[$idx])];
        $idx++;
    }

    if(!empty($links))
        update_post_meta($post_id,'academizer_links', $links);
    else delete_post_meta($post_id, 'academizer_links');

    if (isset ($_REQUEST['tags'])) {
        $tags = $_REQUEST['tags'];
        $tag_array = explode(',', $tags);
        $tag_array = array_map('trim', $tag_array);
        $tag_array = array_unique($tag_array);
        $tags = implode(', ', $tag_array);
        update_post_meta($post_id, 'academizer_tags', sanitize_text_field($tags));
    } else delete_post_meta($post_id, 'academizer_tags');
}

function array_filter_key(array $array, $callback)
{
    $matchedKeys = array_filter(array_keys($array), $callback);
    return array_intersect_key($array, array_flip($matchedKeys));
}