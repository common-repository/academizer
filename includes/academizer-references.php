<?php
add_shortcode('academizer', 'academizer_shortcode');
include_once('academizer-bibtex-parser.php');

function academizer_shortcode($atts, $content = null) {
    wp_enqueue_style('academizer');
    $attrs = shortcode_atts( array(
        'type'  => array(),
        'tags'  => array(),
        'excludetags' => '',
        'style' => '',
        'theme' => '',
        ), $atts );
        
    $query_args = array(
        'post_type' => 'academizer_reference',
        'order'    => 'DESC',
        'orderby'  => 'academizer_date',
        'meta_key' => 'academizer_date'
    );

    if (!empty($attrs['type'])) {
        $terms = get_terms(array(
            'taxonomy'  => 'academizer_reftype',
            'meta_key'  => 'academizer_entry_type',
            'meta_value'=> $attrs['type'],
            'fields'    => 'ids'
        ));

        $query_args['tax_query'] = array(
            array(
                'taxonomy'  => 'academizer_reftype',
                'terms'     => $terms,
            ),
        );
    }

    if(!empty($attrs['tags'])) {
        $query_args['meta_query'] = array(
            array(
                'key'      => 'academizer_tags',
                'value'    => $attrs['tags'],
                'compare'  => 'REGEXP'
            )
        );
    }

    $options = get_option('academizer_options');
    if (empty($attrs['style'])) {
        $attrs['style'] = $options['style'];
    }
    if (empty($attrs['theme'])) {
        $attrs['theme'] = $options['theme'];
    }

    $parser = new AcademizerBibtexParser();
    $query = new WP_Query($query_args);
    echo '<div id="references" class="' . $attrs['theme'] . '">';
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $tags = get_post_meta($post_id, 'academizer_tags', true);
        $tag_array = explode(',', $tags);

        if (!empty($tag_array) || !empty($attrs['excludetags'])) {
            $tag_array = array_map('trim', $tag_array);
            $tag_array = array_unique($tag_array);

            if (false !== array_search($attrs['excludetags'], $tag_array))
                continue;
        }

        $term_ids = wp_get_post_terms($post_id, 'academizer_reftype', array('fields' => 'ids'));
        $entry_type = $term_ids[0];

        $ref = AcademizerReference::Create(
            $options['name'],
            get_post_meta($post_id, 'academizer_bibtex_json', true),
            array(get_term_meta($entry_type, 'academizer_format_full', true), get_term_meta($entry_type, 'academizer_format_short', true)),
            get_post_meta($post_id, 'academizer_links', true)
        );

        switch ($attrs['style']) {
            case "thumbnail":
            case "thumbnail_left":
                academizer_references_thumbnail($parser, $ref);
                break;

            case "detailed":
                wp_enqueue_script('popper');
                wp_enqueue_script('bootstrap-js');
                wp_enqueue_script('academizer_bibtexParser');
                wp_enqueue_script('academizer_clientReferences');
                academizer_detailed_bootstrap($parser, $ref);
                break;

            case "simple":
            default:
                academizer_references_simple($parser, $ref);
                break;
        }
    }
    ?>
        <div id="academizer-credits">
            <p>Reference list created with <a target="_blank" href="https://wordpress.org/plugins/academizer/">Academizer</a> v<?php echo ACADEMIZER_VERSION?>.</p>
        </div>
    </div>
    <?php
}

function academizer_references_simple($parser, $ref) {
    $parser->set($ref);
    $reference = $parser->fullCitation();
    ?><div class="reference">
        <p class="ref-citation">
            <?php echo $reference; ?>
        </p>
    </div>
    <?php
}

function academizer_references_thumbnail($parser, $ref) {
    $parser->set($ref);
    $reference = $parser->fullCitation();

    ?><div class="reference">
        <div class="ref-thumbnail">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php if (!empty($ref->paper_url)) : ?>
                    <a href="<?php echo $ref->paper_url?>"><?php the_post_thumbnail('thumbnail'); ?></a>
                <?php else : ?>
                    <?php the_post_thumbnail('thumbnail'); ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="ref-content">
        <p class="ref-citation"><?php echo $reference; ?></p>
        </div>
    </div><?php
}

function academizer_detailed_bootstrap($parser, $ref) {
    if (empty($ref->format))
        return;

    $parser->set($ref);
    preg_match('/(<authors:{.*}>\.+)(.*)/', htmlspecialchars_decode($ref->format[0]), $matches);
    $citation = htmlspecialchars_decode($ref->format[1]);
    $authors = '';
    $entryType = $parser->getCurrentElement('<entryType>');
    $series = $parser->getCurrentElement('<series>');

    if (!empty($matches))
        $authors = $parser->getCurrentElement($matches[1]);
    ?>
    <div class="reference">
        <div class="ref-thumbnail">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php if ( !empty($ref->links['paper_url'])) : ?>
                    <a href=" <?php echo $ref->links['paper_url'] ?>" target="_blank\">
                        <?php the_post_thumbnail('thumbnail');?>
                    </a>
                <?php else : ?>
                    <?php the_post_thumbnail('thumbnail');?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if (!empty($series)) : ?>
        <a href="<?php  if(!empty($ref->links['event_url'])) echo $ref->links['event_url'];?>" target="_blank" role="button"
           title="Link to event or publisher website" data-toggle="tooltip" data-placement="bottom"
           class="ref-label btn ref-entry_<?php echo $entryType;
                if (empty($ref->links['event_url'])) echo " ref-label-disabled"; ?>">
            <?php echo $parser->getCurrentElement('<series>', true); ?>
        </a>
        <?php endif;?>
        <div class="ref-content">
            <h4 class="ref-title"><?php echo $parser->getCurrentElement('<title>');?></h4>
            <div class="ref-citation">
                <p><span class="authors"><?php echo $authors;?></span></p>
                <p><?php echo $parser->shortCitation() ?></p>
            </div>
        </div>
        <div class="ref-cmds">
            <?php if (!empty($ref->bibtex_json))
                academizer_create_button("#", 'ref-tooltip ref-btn-citation btn-secondary', 'quotes', 'Citation', 'Click to copy the citation text to the clipboard.',
                    'data-citation="' . wp_strip_all_tags($parser->fullCitation()) . '" ' . 'data-toggle="tooltip" data-placement="top" ');?>
            <?php if (!empty($ref->bibtex_json))
                academizer_create_button("#", 'ref-tooltip ref-btn-bib btn-secondary', 'bib', 'Bibtex', 'Click to copy the Bibtex code to the clipboard.',
                    'data-bibtex="' . htmlspecialchars($ref->bibtex_json) . '" ' . 'data-toggle="tooltip" data-placement="top" ');?>
            <?php if (!empty($parser->getCurrentElement('<doi>'))) academizer_create_link("http://dx.doi.org/{$parser->getCurrentElement('<doi>')}", "ref-btn-doi btn-dark", 'link', 'DOI Link');?>
            <?php if (!empty($ref->links['video_url'])) academizer_create_link($ref->links['video_url'], 'ref-btn-video btn-dark', 'video', 'Video');?>
            <?php if (!empty($ref->links['talk_url'])) academizer_create_link($ref->links['video_url'], 'ref-btn-talk btn-dark', 'talk', 'Talk Video');?>
            <?php if (!empty($ref->links['git_url'])) academizer_create_link($ref->links['git_url'], 'ref-btn-git btn-dark', 'git', 'GitHub');?>
            <?php if (!empty($ref->links['slides_url'])) academizer_create_link($ref->links['slides_url'], 'ref-btn-slides btn-dark', 'slide', 'Slides');?>
            <?php if (!empty($ref->links['paper_url'])) academizer_create_link($ref->links['paper_url'], 'ref-btn-paper btn-danger', 'file', 'Read PDF' );?>
        </div>
    </div>
    <?php
}

function academizer_get_reftypes() {
    $terms = get_terms( array (
        'taxonomy' => 'academizer_reftype',
        'hide_empty' => false,
    ) );
    
    $termNames = array();
    foreach ($terms as &$term)
    {
        $termNames[] = $term->name;
    }
    
    return $termNames;
}

function academizer_create_link($url, $classes, $icon, $text, $tooltip=null, $other=null) {
    ?><a href="<?php echo $url;?>" target="_blank" role="button" class="btn btn-labeled <?php echo $classes; ?>" title="<?php echo $tooltip;?>" <?php echo $other;?>>
        <div class="ref-icon-container">
            <div class="ref-icon ref-icon-<?php echo $icon; ?>"></div>
            <div class="ref-btn-label"><span class="btn-text"><?php echo $text; ?></span></div>
        </div></a><?php
}

function academizer_create_button($url, $classes, $icon, $text, $tooltip=null, $other=null) {
    ?><button type="button" class="btn btn-labeled <?php echo $classes; ?>" title="<?php echo $tooltip;?>" <?php echo $other;?>>
    <div class="ref-icon-container">
        <div class="ref-icon ref-icon-<?php echo $icon; ?>"></div>
        <div class="ref-btn-label"><span class="btn-text"><?php echo $text; ?></span></div>
    </div></button><?php
}