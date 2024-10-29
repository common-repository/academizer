jQuery(document).ready(function( $ ) {
    var rowCount = $('#academizer-metaTable .academizer-metaData').length;

    $('#academizer-metaTable .button.minus').prop('disabled', rowCount == 1 );

    function isEmpty(str) {
        return (!str || 0 === str.length);
    }

    function pad (str, max) {
        str = str.toString();
        return str.length < max ? pad("0" + str, max) : str;
    }

    function sendNotice(noticeClass, message, target='#output') {
        $(target).html('<div id="message" class="notice ' + noticeClass + '"><p>' + message + '</p></div>');
    }

    function isUrlValid(url) {
        return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
    }

    function requestRender(json) {
        var jsonBibtex = JSON.stringify(json);
        $('[name=bibtex_json]').val(jsonBibtex);
            
        var data = {
            'action'	:	'academizer_ajax_render_bibtex',
            'bibtex'	:	jsonBibtex,
        };
        
        jQuery.post(academizer.ajax_url, data, function(response) {
            response = JSON.parse(response);
            if (response.result > 0) {
                sendNotice('notice-info', 'Bibtex type: <strong>' + json[0].entryType + '</strong>');
                $('#output').append(response.message);
            }
            else
                sendNotice('notice-error', response.message);
        });
    }
   
    var $ = jQuery.noConflict();
	var bibtex = $('[name=bibtex]');
	var bibtexJSON = $('[name=bibtex_json]') ;
	
	var jsonText = bibtexJSON.val();

	bibtex.on('change', function() {
	    try {
            var json = bibtexParse.toJSON(bibtex.val());
            if (json.length == 0) {
                sendNotice('notice-error', 'No Bibtex entered.');
                return;
            }
        }
        catch(e)
        {
            sendNotice('notice-error', 'Bibtex is invalid.');
        }
        requestRender(json);
	});

    if (!isEmpty(jsonText))	{
        try {
            var json = JSON.parse(jsonText);
            var bibtexRef = bibtexParse.toBibtex(json, false).replace("\n\n","");
            bibtex.text(bibtexRef);
        }
        catch (e)
        {
            sendNotice('notice-error', 'Bibtex is invalid.');
        }

        bibtex.trigger('change');
    }

    $('#academizer-addNew').on('click', function () {
        var newRow = "<tr class=\"academizer-metaData\" data-index=\"XX\">\n" +
            "                    <td class=\"academizer-metaValue\">\n" +
            "                        <input name=\"academizer_metaValueXX\" class=\"form-invalid\" type=\"text\"/></td>\n" +
            "                    <td class=\"academizer-metaKey\">\n" +
            "                        <select name=\"academizer_metaKeyXX\" class=\"form-invalid\" >\n" +
            "                        </select>\n" +
            "                    </td>\n" +
            "                    <td class=\"academizer-metaCmd\">\n" +
            "                        <button type=\"button\" class=\"button minus\" disabled/>\n" +
            "                    </td>\n" +
            "                </tr>";
        newRow = newRow.replace(/XX/g, pad(++rowCount,2));
        $('#academizer-metaTable .academizer-metaData').parent().append(newRow);

        var newSelect = 'select[name=academizer_metaKey' + pad(rowCount,2)+']';
        $('.academizer-metaKey > select').first().find('option').clone().appendTo(newSelect);
        $(newSelect).val("none");

        if ($('#academizer-metaTable .button.minus').length == 1)
            $('#academizer-metaTable .button.minus').prop('disabled', true);
        else
            $('#academizer-metaTable .button.minus').prop('disabled', false);

        sendNotice('notice-error', 'You must enter a valid URL and choose an appropriate category.', '#academizer-metaTable-validation');
    });

    $('#academizer-metaTable').on('click', '.button.minus', function () {
        $(this).closest('tr').remove();
        if ($('#academizer-metaTable .button.minus').length == 1)
            $('#academizer-metaTable .button.minus').prop('disabled', true);
        else
            $('#academizer-metaTable .button.minus').prop('disabled', false);
    });

    $('#academizer-metaTable').on('change', 'input', function () {
        if (validateAll())
            $('#academizer-metaTable-validation').html("");
        else
            sendNotice('notice-error', 'You must enter a valid URL and choose an appropriate category.', '#academizer-metaTable-validation');
    });

    $('#academizer-metaTable').on('change', 'select', function () {
        if (validateAll())
            $('#academizer-metaTable-validation').html("");
        else
            sendNotice('notice-error', 'You must enter a valid URL and choose an appropriate category.', '#academizer-metaTable-validation');
    });

    function validateAll() {
        var testInput = true;
        $('#academizer-metaTable input').each(function() {
            if (isEmpty($(this).val()) || !isUrlValid($(this).val())) {
                $(this).addClass('form-invalid');
                return testInput = false;
            }
            else $(this).removeClass('form-invalid');
        });

        var testSelect = true;
        $('#academizer-metaTable select').each(function() {
            if ($(this).val() == "none") {
                $(this).addClass('form-invalid');
                return testSelect = false;
            }
            else $(this).removeClass('form-invalid');
        });

        return testInput && testSelect;
    }
    console.log (".");
});