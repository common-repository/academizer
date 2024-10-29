var activeBib = -1;
var formName = '#edittag';

jQuery(document).ready(function( $ ) {

    function changeFormat(data) {
        return function() {
            $('#format-full').val(data.formatFull);
            $('#format-short').val(data.formatShort);

            if (!data.isJournal)
                $('#tab-1').trigger('click');
            else
                $('#tab-2').trigger('click');
        };
    }

    function requestRender(bibtex, format, output) {
        var data = {
            'action': 'academizer_ajax_render_bibtex',
            'bibtex': JSON.stringify(bibtexParse.toJSON(bibtex.val())),
            'format': format.val()
        };

        jQuery.post(academizer.ajax_url, data, function(response) {
            response = JSON.parse(response);
            if (response.result > 0) {
                output.html(response.message);
            }
            else
                output.html('<div id="message" class="notice notice-error"><p>' + response.message + '</p></div>');
        });
    }

	var format = $('#format-full');
	
	if ($('body').hasClass('edit-tags-php')) {
		$('#acm-proc').on('click', changeFormat({
            formatFull: "<authors:{name surname},{and}>. <year>. <title>. In <i><booktitle></i> (<series>). <organization>, <address>, <pages>.",
            formatShort: "In <i><booktitle></i> (<series>). <organization>, <address>, <pages>."}));
		$('#acm-journal').on('click', changeFormat({
            formatFull: "<authors:{name surname},{and}>. <year>. <title>. <i><journal></i> <volume>, <number> (<issue_date>), <pages>.",
            formatShort: "<i><journal></i> <volume>, <number> (<issue_date>), <pages>.",
            isJournal: true}));
		$('#apa-proc').on('click', changeFormat({
            formatFull: "<authors:{surname, initial},{amp}> (<year>). <title>. In <i><booktitle></i> (pp. <pages>). <organization>.",
            formatShort: "In <i><booktitle></i> (pp. <pages>). <organization>."
		}));
		$('#apa-journal').on('click', changeFormat({
            formatFull: "<authors:{surname, initial},{amp}> (<year>). <title>. <i><journal>, <volume></i>(<number>), <pages>.",
            formatShort: "<i><journal>, <volume></i>(<number>), <pages>.",
            isJournal: true}));
		$('#hvd-proc').on('click', changeFormat({
            formatFull: "<authors:{surname, initial},{and}>, <year>, <title>. In <i><booktitle></i> (pp. <pages>). <organization>.",
            formatShort: "In <i><booktitle></i> (pp. <pages>). <organization>."
        }));
        $('#hvd-journal').on('click', changeFormat({
            formatFull:"<authors:{surname, initial},{and}>, <year>. <title>. <i><journal>, <volume></i>(<number>), pp. <pages>.",
            formatShort: "<i><journal>, <volume></i>(<number>), pp. <pages>.",
            isJournal: true}));
        $('#ieee-proc').on('click', changeFormat({
            formatFull: "<authors:{initial surname},{and}>, \"<title>\" <i><booktitle></i>, <address>, <year>, pp. <pages>.",
            formatShort: "In <i><booktitle></i>, <address>, <year>, pp. <pages>."
        }));
        $('#ieee-journal').on('click', changeFormat({
            formatFull:"<authors:{initial surname},{and}>, \"<title>\" in <i><journal></i>, vol. <volume>, no. <number>, pp. <pages>, <year>.",
            formatShort: "In <i><journal></i>, vol. <volume>, no. <number>, pp. <pages>, <year>.",
            isJournal: true}));
		
		formName = '#addtag';
	}
	
	activeBib = $('input[name=radio-set]:checked', formName).data('index');
	
	format.on('change', function() {
		if (format.val())
			requestRender($('[name=bibtex-'+activeBib+']'),format,$('#preview-'+activeBib));
	});
	
	$('[id^=tab]').on('click', function() {
		activeBib = $(this).data('index');
		if (format.val())
			requestRender($('[name=bibtex-'+activeBib+']'),format,$('#preview-'+activeBib));
	});
	
	$('[name=bibtex-1]').val(`@inproceedings{Proceedings,
	  title={A Conference Paper},
	  author={One, Author and Two, Author and Three, Author},
	  booktitle={Proceedings of a Conference},
	  pages={1--8},
	  year={2018},
	  series={Conf. 2018},
	  address={City, Country},
	  organization={Publisher},
	  doi={10.1234/123456.123456}
	}`);
	
	$('[name=bibtex-2]').val(`@article{Article,
	  title={A Journal Paper},
	  author={One, Author and Two, Author and Three, Author},
	  journal={Transactions of a Journal},
	  pages={1--8},
	  volume={10},
	  number={1},
	  issue_date={January 2018},
	  year={2018},
	  doi={10.1234/123456.123456}
	}`);

	if (format.val())
		requestRender($('[name=bibtex-'+activeBib+']'),format,$('#preview-'+activeBib));
	
});