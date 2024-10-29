<?php
class AcademizerReference {
    public $me_full_name;
    public $format;
    public $bibtex_json;
    public $links;

    function __construct() {
    }

    public static function Create($me_full_name, $bibtex_json, $format, $links=null) {
        $ref = new AcademizerReference();
        $ref->me_full_name = $me_full_name;
        $ref->bibtex_json = $bibtex_json;
        $ref->format = $format;
        $ref->links =$links;
        return $ref;
    }
}

class AcademizerBibtexParser {
    private $ref;
    private $json_obj;
    private static $regex_format =  '/\<([\w:,\{\}\s+]*)\>/';
    private static $regex_authors = '/<authors:{([a-zA-Z,\s*]*)}\,*\{*([a-zA-Z,]*)\}*>/';
    private $current_entry;
    
    function render($ref) {
        echo $this->parse($ref);
    }

    function checkFormat($format)    {
        return preg_match_all(self::$regex_format, $format, $matches, PREG_SET_ORDER, 0) > 0;
    }

    function set($ref)
    {
        $this->ref = $ref;
        if (!$this->isJson($ref->bibtex_json)) {
            throw new Exception("JSON is invalid.");
        }
        if (empty($ref->format)) {
            throw new Exception("No format specified.");
        }
        $this->json_obj = json_decode($ref->bibtex_json);
        if (!empty($this->json_obj))
            $this->current_entry=$this->json_obj[0];
    }

    function parse($spanTags=false, $formatIdx=0) {
        if(empty($this->ref))
            throw new Exception("No reference set.");

        $format = htmlspecialchars_decode($this->ref->format[$formatIdx]);

        preg_match_all(self::$regex_format, $format, $matches, PREG_SET_ORDER, 0);

        $out = "";
        foreach ($this->json_obj as &$entry) {
            $this->current_entry = $entry;
            $this_ref = $format;
            foreach ($matches as &$tag) {
                $tag = $tag[0];
                $tagvalue = $this->getElement($entry, $tag);
                if ($spanTags && $this->isFormatTag($tag))
                    $tagvalue = $this->span($tag, $tagvalue);

                $this_ref = str_replace($tag, $tagvalue, $this_ref);
            }
            $out .= $this_ref;
        }
        
        return stripslashes($this_ref);
    }

    function fullCitation() {
        return $this->parse(false, 0);
    }

    function shortCitation() {
        return $this->parse(false, 1);
    }

    private function span($tag, $tagvalue)
    {
        preg_match("/<(\w+)>/", $tag, $out);
        return"<span class=\"ref-{$out[1]}\">{$tagvalue}</span>";
    }

    function isJson($string) {
        json_decode($string);
        
        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ""; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = "The maximum stack depth has been exceeded->";
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = "Invalid or malformed JSON->";
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = "Control character error, possibly incorrectly encoded->";
                break;
            case JSON_ERROR_SYNTAX:
                $error = "Syntax error, malformed JSON->";
                break;
            // PHP >= 5->3->3
            case JSON_ERROR_UTF8:
                $error = "Malformed UTF-8 characters, possibly incorrectly encoded->";
                break;
            // PHP >= 5->5->0
            case JSON_ERROR_RECURSION:
                $error = "One or more recursive references in the value to be encoded->";
                break;
            // PHP >= 5->5->0
            case JSON_ERROR_INF_OR_NAN:
                $error = "One or more NAN or INF values in the value to be encoded->";
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = "A value of a type that cannot be encoded was given->";
                break;
            default:
                $error = "Unknown JSON error occured->";
                break;
        }
        return (json_last_error() == JSON_ERROR_NONE);
    }
    
    function debugVar($var) {
        echo "<pre>";
        var_dump($var);
        echo "</pre>";
    }

    function formatInitials($names, $options, $skip_first=FALSE) {
        $initials = "";
        $nameIdx = 0;
        foreach ($names as &$this_name) {
            if ($skip_first==TRUE && $nameIdx==0) {
                $nameIdx++;
                continue;
            }
            $initials .= substr($this_name, 0, 1);
            if (!preg_match("/NoDot/i", $options))
                $initials .= ".";
            if (!preg_match("/NoSpace/i", $options) && $nameIdx < (sizeof($names)-1))
                $initials .= " ";
            $nameIdx++;
        }
        return $initials;
    }
    
    function formatAuthors($pattern, $authors) {
        preg_match_all(self::$regex_authors, $pattern, $commands, PREG_SET_ORDER, 0);
        $pattern = $commands[0][1];
        $options = $commands[0][2];
        $out ="";
        $authorIdx = 0;
        $me_array = explode(",", $this->ref->me_full_name);

        foreach ($authors as &$author) {
            $authorFullName = explode(",", $author);
            $surname = trim($authorFullName[0]);
            $names = explode(' ', trim($authorFullName[1]));
            preg_match_all('/[a-zA-Z]+/', $pattern, $matches, PREG_SET_ORDER, 0);
            $thisAuthor = $pattern;
            
            foreach ($matches as &$match) {
                $tag = strtolower($match[0]);
                switch ($tag) {
                    case "surname":
                        $thisAuthor = str_replace($tag, $surname, $thisAuthor);
                        break;
                    
                    case "name":
                        $name = $names[0];
                        if (sizeof($names) > 1)
                            $name .= ' ' . $this->formatInitials($names, $options, TRUE);
                        $thisAuthor = preg_replace('/\bname\b/i', $name, $thisAuthor);
                        break;
                        
                    case "initial":
                        $thisAuthor = str_replace($tag, $this->formatInitials($names, $options), $thisAuthor);
                        break;
                }
            }

            if (sizeof($me_array) > 1) {
                $my_surname = $me_array[0];
                $my_names = explode(" ", trim($me_array[1]));
                if ($my_surname == $surname and $my_names[0] == $names[0])
                    $thisAuthor = '<span class="ref-me">' . $thisAuthor . '</span>';
            }
            
            if ($authorIdx == sizeof($authors)-2 && preg_match("/and/i", $options))
                $thisAuthor .= ", and ";
            else if ($authorIdx == sizeof($authors)-2 && preg_match("/amp/i", $options))
                $thisAuthor .= " & ";
            else if ($authorIdx < sizeof($authors)-1)
                $thisAuthor .= ", ";
            $authorIdx++;
            $out .= $thisAuthor;
        }
        
        return $out;
    }

    public function getCurrentElement($tag, $spanTags = false)
    {
        $tagValue = $this->getElement($this->current_entry, $tag);
        return $spanTags ? $this->span($tag, $tagValue) : $tagValue;
    }

    function isFormatTag($tag) {
        $tag = strtolower($tag);
        switch ($tag) {
            case "<year>":
            case "<title>":
            case "<booktitle>":
            case "<journal>":
            case "<organization>":
            case "<series>":
            case "<address>":
            case "<doi>":
            case "<pages>":
            case "<volume>":
            case "<number>":
            case "<issue_date>":
                return true;

            default:
                return false;
        }
    }

    function getElement($entry, $tag) {
        $entryTags = $entry->entryTags;
        
        $tag = strtolower($tag);
        switch ($tag) {
            case "<entrytype>":
                return $entry->entryType;

            case "<year>":
                return $entryTags->year;
            
            case "<title>":
                return $entryTags->title;
            
            case "<booktitle>":
                return $entryTags->booktitle;
                
            case "<journal>":
                return $entryTags->journal;
                
            case "<organization>":
                return $entryTags->organization;
            
            case "<series>":
                return $entryTags->series;
            
            case "<address>":
                return $entryTags->address;
                
            case "<doi>":
                return empty($entryTags-> doi) ? "" : $entryTags->doi;
            
            case "<pages>":
                return str_replace("--", "-", $entryTags->pages);
                
            case "<volume>":
                return $entryTags->volume;
                
            case "<number>":
                return $entryTags->number;
                
            case "<issue_date>":
                return $entryTags->issue_date;
                
            case "<paper_url>":
                return $this->ref->links['paper_url'];
                
            case "<pub_url>":
                return $this->ref->links['pub_url'];
                
            default:
            if (preg_match("/<authors:/", $tag)) {
                 return $this->formatAuthors($tag, explode(" and ",$entryTags->author));
            }
            else return $tag;
        }
    }
}