jQuery(document).ready(function($) {
    function copyToClipboard(text, el) {
        var copyTest = document.queryCommandSupported("copy");
        var elOriginalText = el.attr("data-original-title");

        if (copyTest === true) {
            var copyTextArea = document.createElement("textarea");
            copyTextArea.value = text;
            document.body.appendChild(copyTextArea);
            copyTextArea.select();
            try {
                var successful = document.execCommand("copy");
                var msg = successful ? "Copied!" : "Whoops, not copied!";
                el.attr("data-original-title", msg).tooltip('show');
            } catch (err) {
                console.log("Oops, unable to copy");
            }
            document.body.removeChild(copyTextArea);
            el.attr("data-original-title", elOriginalText);
        } else {
            // Fallback if browser doesn't support .execCommand('copy')
            window.prompt("Copy to clipboard: Ctrl+C or Command+C, Enter", text);
        }
    }

    $('[data-toggle="tooltip"]').tooltip();

    $("#references").on("click", ".ref-btn-bib", function() {
        var text = bibtexParse.toBibtex($(this).data('bibtex'), false).replace("\n\n","");
        var el = $(this);
        copyToClipboard(text, el);
    });

    $("#references").on("click", ".ref-btn-citation", function() {
        var text = $(this).data('citation');
        var el = $(this);
        copyToClipboard(text, el);
    });

});