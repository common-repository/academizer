=== Academizer ===
Contributors: thewand3rer
Tags: Bibtex, publications, bibliography, references, academia, paper, article, research
Donate link: http://www.adalsimeone.me/academizer-donate
Requires at least: 4.8.4
Tested up to: 4.9.1
Requires PHP: 5.3
Stable tag: 1.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin can help you manage Bibtex references. It automatically parses Bibtex notation and renders HTML using user-defined citation styles.

== Description ==

Academizer allows you to manage Bibtex entries and define how you want them to be rendered.

* Each Bibtex entry can be complemented with links to the paper, publisher website, thumbnails, which can be used in the citation style to automatically set links.

* Three rendering styles are provided in this release: `simple`, `thumbnail`, and `detailed` (see below). You can customise them by editing the related css file.

* References can be queried and included in any page through a shortcode. You can display all references, or select them by entry type, or user-defined tags.

= Academizer needs your support =
If you find Academizer useful, please consider awarding a mini-research grant by [__making a donation__](http://www.adalsimeone.me/academizer-donate). Your donation will encourage the continued development and support of the plugin. Thank you!

= How does it work? =

You first need to define a `Reference Type` through the corresponding page, for each Bibtex entry you plan to use (e.g., `article`, `book`, `inproceedings`, etc.), by choosing the appropriate type through the dropdown list. You can select how you wish to render each reference of that type by either choosing a pre-defined style or creating your own (see the Format notation in the `Installation` tab).

You add new references as custom posts through the related menu. You simply need to paste the Bibtex code in the corresponding field in the page. If the Bibtex is well-formed, the appropriate reference type will be detected. If a format is defined for that type, the preview will render the citation in that style. You can also associate to each reference a set of metadata (such as the paper URL, video URL, etc.).

You can display a (filtered) list of references by using the `[academizer <options>]` shortcode.

= Source Code =
You can browse the source code on the [GitHub repository](https://github.com/AvengerDr/academizer).

== Installation ==

1. Upload the `academizer` folder to the `/wp-content/plugins` directory.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Before adding your Bibtex entries, add a `Reference Type` (and corresponding format) for each Bibtex entry type you plan to use (e.g., `article`, `book`, `inproceedings`, etc.)
4. Add each reference through the `Add New` menu command.
5. Include the `[academizer]` shortcode in the page you want to display the references.

= Shortcode =

Simply type `[academizer option="value" ...]` in any page and the rendering engine will be called. Typing `[academizer]` will display all references. If you want to select a subset, use the following commands.

* **type**: selects only those reference of a specific Bibtex entry type. `[academizer type="inproceedings"]` will select only conference papers.

* **tags**: selects only those references that have a specific user-defined tag. Only one tag is accepted at this time. `[academizer tags="project"]` will only display those references tagged with `project`. 

* **excludeTags**: only those references that do not have the supplied tag will be displayed. Only one tag is accepted as argument at this time.

= Format =

Academizer will parse any correctly formed Bibtex entry. It is essential that you define the corresponding citation style for each Bibtex entry type (e.g., `article`, `book`, `inproceedings`, etc.). Only one style per entry type is allowed. Some of the most common styles are included. You can select them by choosing them from the menu in the Add New Type page.

The following is the list of recognised keywords you can use if you want to create your own citation style.

* **`<authors:{style},{options}>`** defines how to render the authors of a paper. 
 * `style` can be any combination of 'surname', 'name', and 'initial' (displays only the first letter of each name). Any other literal character will be copied verbatim in the resulting string.

 * `options` can be any combination of the following commands: `NoDot` (does not display a dot at the end of an initial), `NoSpace` (does not add a space between initials), `and` (separates the last author with **`, and`**), `amp` (separates the last author with **`, &`**). These options can be combined together by separating them with a comma.

 For example `<authors:{surname, initial}, {and}>` will output: *Smith, J., Doe, J., and Joe, A.* whereas `<authors:{name surname}>` will output the full name for each author.

* **`<title>`**, **`<booktitle>`**, **`<title>`**, **`<volume>`**, **`<number>`**, **`<series>`**, **`<issue_date>`**, **`<publisher>`**, **`<address>`**, **`<pages>`**, **`<doi>`** all refer to the corresponding Bibtex fields.

* **`<paper_url>`** a user-defined link to a local copy of the pdf version. For example, in your format string you can automatically add a link from the title to the PDF by writing: `<a href="<paper_url>"><title></a>`

* **`<pub_url>`** a user-defined link to the publisher page for a paper.

* **`<a>`**, **`<b>`**, **`<i>`**, **`<strong>`**, **`<em>`** can all be added to a format string. Remember to include the closing tag as well.

= Style =

In this release of Academizer, there are three supplied rendering styles. In the default one, each reference is formatted according to the associated style. The `thumbnail` style uses the *Featured Image* associated to each post to display a thumbnail. The `detailed` style requires Bootstrap 4 to be loaded. Academizer will enqueue the required scripts and styles on its own. However, make sure there is only one copy of the Bootstrap scripts/styles loaded (you might have to edit your child-theme).

Under `Settings|Academizer` you can choose two themes (Dark and Light) that come with predefined background and font colors. You can also customise the CSS classes contained in the file `academizer/css/academizer.css`. 

You can style your own name differently from other authors. Simply type your full name in the settings page of the plugin. Be sure to use the Bibtex name format: `Surname, Name1 Name2 ...`.

= Metadata =

The `detailed` style will automatically show two buttons for each reference. One button will copy the full citation in plain text to the clipboard. The other will copy the Bibtex entry to the clipboard.

If the reference has some metadata values defined, these will be used to create a wide range of buttons. The available types are:

* **Paper URL**: indicates the location of your locally stored PDF copy of an article.

* **Event URL**: a link to the conference or journal website.

* **GitHub Repo URL**: a link to a GitHub repository.

* **Slides URL**: a link to a copy of your presentation slides. 

* **Talk Video URL**: a link to a video of your talk.

* **Video URL**: a link to a video associated to the paper.

You can of course use those links for other purposes, but the plugin will associate to each button a related icon (which you can find as SVG icons in the css file).

== Screenshots ==

1. An example of the plugin's output, using the `detailed` style.
2. The currently available reference formats that are provided by the plugin.
3. An example of the plugin's format.
4. The interface used to add new Bibtex references.

== Changelog ==

= 1.1 =

* Added a `detailed` style.

* Added the possibility of choosing between a *dark* and *light* theme.

= 1.0 =

* First released version.