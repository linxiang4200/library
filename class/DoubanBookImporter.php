<?php
class DoubanBookImporter {

    const ISBN_PREG = '/[^\d|^x|^X]+/';

    protected $isbn = '';
    protected $xmlFileName = '';
    protected $xmlData = '';
    protected $bookData = '';

    /**
     * 强制从豆瓣更新数据
     * @var bool 
     */
    protected $forceRefresh = false;

    /**
     * 数据库字段
     * @var array 
     */
    protected $bulidinAttributes = array(
        'title', 'author', 'translator', 'publisher', 'pubdate',
        'isbn10', 'isbn13', 'binding', 'price', 'pages', 'summary',
        'author-intro'
    );

    /**
     * 临时文件夹
     */
    protected $tmpDir;

    /**
     * 检索URL
     */
    protected $doubanApiUrl = 'http://api.douban.com/book/';

    // 豆瓣Apikey
    protected $doubanApiKey = '';

    public function setForceRefresh($forceRefresh) {
        $this->forceRefresh = $forceRefresh;
    }

    public function setDoubanApiUrl($url) {
        $this->doubanApiUrl = $url;
    }

    public function setDoubanApiKey($apikey) {
        $this->doubanApiKey = $apikey;
    }

    function __construct() {

    }

    function getBookArray() {
        $book = array();
        $bookData = $this->getBookData();
        if ($bookData) {
            $data = $bookData['entry'];
            $otherAttributes = array();
            foreach ($this->bulidinAttributes as $item) {
                if (!empty($data[$item])) {
                    $_data = is_array($data[$item]) ? implode(',', $data[$item]) : $data[$item];
                    $book[$item] = addslashes(trim($_data));
                }
            }

            $_attributes = $data['db:attribute'];
            $attributes = array();
            $attributesArray = array();

            foreach ($_attributes as $_attributeKey => $_attributeValue) {
                if (!is_int($_attributeKey)) {
                    if (key_exists($_attributeValue['name'], $attributes)) {
                        $attributesArray[$_attributeValue['name']][] = $attributes[$_attributeValue['name']];
                        $attributesArray[$_attributeValue['name']][] = $_attributes[intval($_attributeKey)];
                    } else {
                        $attributes[$_attributeValue['name']] = $_attributes[intval($_attributeKey)];
                    }
                }
            }
            foreach ($attributesArray as $key => $value) {
                $attributes[$key] = implode(',', $value);
            }
            foreach ($attributes as $key => $value) {
                if (in_array($key, $this->bulidinAttributes)) {
                    $_data = is_array($value) ? json_encode($value) : $value;
                    $book[$key] = addslashes(trim($_data));
                } else {
                    $_data = is_array($value) ? json_encode($value) : $value;
                    $otherAttributes[$key] = addslashes(trim($_data));
                }
            }

            $_links = $data['link'];
            foreach ($_links as $key => $_link) {
                if (!is_int($key)) {
                    //转换 douban_image
                    if ('image' == $_link['rel']) {
                        preg_match('/s[0-9]+.jpg/i', $_link['href'], $image);
                        $book['douban_image'] = $image[0];
                    }
                    //转换  douban_id
                    'self' == $_link['rel'] && $book['douban_id'] = preg_replace('/[^0-9]/', '', $_link['href']);
                }
            }

            //转换 Tag
            $_tags = $data['db:tag'];
            $tags = array();
            foreach ($_tags as $key => $_tag) {
                if (!is_int($key)) {
                    $tags[$_tag['count']] = trim($_tag['name']);
                }
            }
            $book['tags'] = implode(',', $tags);
            //其它属性
            !empty($otherAttributes) && $book['attribute'] = json_encode($otherAttributes);
        }
        return $book;
    }

    public function setIsbn($isbn) {
        $this->isbn = $isbn;
    }

    function getBookData() {
        empty($this->bookData) && $this->getXmlData();
        return $this->bookData;
    }

    function getXmlData() {
        if (empty($this->isbn)) {
            throw new Exception('isbn null');
        }
        if (!empty($this->xmlData)) {
            return $this->xmlData;
        }
        $bookxml = $this->_getXmlDataFromDisk();
        if (!$bookxml) {
            $bookxml = $this->_getXmlDataFromDouban();
        }
        $bookxml && $bookData = $this->getBookDataFromXml($bookxml);
        if ($bookData) {
            $this->xmlData = $bookxml;
            $this->bookData = $bookData;
            return $bookxml;
        }
    }

    function _getXmlDataFromDisk() {
        if ($this->forceRefresh == true) {
            if (file_exists($this->getXmlFileName())) {
                @unlink($this->getXmlFileName());
            }
            return false;
        }
        if (file_exists($this->getXmlFileName())) {
            $bookxml = file_get_contents($this->getXmlFileName());
            return $bookxml;
        }
        return false;
    }

    function _getXmlDataFromDouban() {

        try {
            $_url = $this->doubanApiUrl . 'subject/isbn/' . $this->isbn;
            if ($this->doubanApiKey) {
                $_url .= '?apikey=' . $this->doubanApiKey;
            }
            $bookxml = file_get_contents($_url);
        } catch (Exception $exc) {
            throw new Exception('Error when get from Douban,please check URL:' . $_url);
            echo $exc->getTraceAsString();
            return false;
        }

        if ($bookxml) {
            file_put_contents($this->getXmlFileName(), $bookxml);
            return $bookxml;
        }
        return false;
    }

    public function getBookDataFromXml($bookXml) {
        try {
            $bookData = $this->xml2array($bookXml);
        } catch (Exception $exc) {
            echo 'XML ERROR:' . $bookXml;
            echo $exc->getTraceAsString();
            exit;
        }
        $this->bookData = $bookData;
        return $bookData;
    }

    public function getXmlFileName() {
        $_i = preg_replace(self::ISBN_PREG, '', $this->isbn);
        // $_i = sprintf("%013d", $_i);
        $dir1 = substr($_i, 0, 5);
        $dir2 = substr($_i, 5, 4);
        $datadir = './data/library/doubandata/' . $dir1 . '/' . $dir2 . '/';
        if (!is_dir($datadir)) {
            dmkdir($datadir);
        }
        $xmlFileName = $datadir . $this->isbn . '.xml';
        //$this->xmlFileName = $this->tmpDir . '/' . $this->isbn . '.xml';
        $this->xmlFileName = $xmlFileName;
        return $this->xmlFileName;
    }

    /**
     * 建立文件夹
     * @param type $dir
     * @param type $mode
     * @param type $makeindex
     * @return boolean 
     */
    function dmkdir($dir, $mode = 0777, $makeindex = TRUE) {
        if (!is_dir($dir)) {
            $this->dmkdir(dirname($dir), $mode, $makeindex);
            @mkdir($dir, $mode);
            if (!empty($makeindex)) {
                @touch($dir . '/index.html');
                @chmod($dir . '/index.html', 0777);
            }
        }
        return true;
    }

    /**
     * xml2array() will convert the given XML text to an array in the XML structure. 
     * Link: http://www.bin-co.com/php/scripts/xml2array/ 
     * Arguments : $contents - The XML text 
     *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
     *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
     * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure. 
     * Examples: $array =  xml2array(file_get_contents('feed.xml')); 
     *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute')); 
     */
    function xml2array($contents, $get_attributes = 1, $priority = 'tag') {
        if (!$contents)
            return array();

        if (!function_exists('xml_parser_create')) {
            return array();
        }

        //Get the XML parser of PHP - PHP must have this module for the parser to work 
        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);

        if (!$xml_values)
            return;
        //Initializations 
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();

        $current = &$xml_array; //Refference 
        //Go through the tags. 
        $repeated_tag_index = array(); //Multiple tags with same name will be turned into an array 
        foreach ($xml_values as $data) {
            unset($attributes, $value); //Remove existing values, or there will be trouble 
            //This command will extract these variables into the foreach scope 
            // tag(string), type(string), level(int), attributes(array). 
            extract($data); //We could use the array by itself, but this cooler. 

            $result = array();
            $attributes_data = array();

            if (isset($value)) {
                if ($priority == 'tag')
                    $result = $value;
                else
                    $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode 
            }

            //Set the attributes too. 
            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) {
                    if ($priority == 'tag')
                        $attributes_data[$attr] = $val;
                    else
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr' 
                }
            }

            //See tag status and do the needed. 
            if ($type == "open") {//The starting of the tag '<tag>' 
                $parent[$level - 1] = &$current;
                if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag 
                    $current[$tag] = $result;
                    if ($attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                    $repeated_tag_index[$tag . '_' . $level] = 1;

                    $current = &$current[$tag];
                } else { //There was another element with the same tag name 
                    if (isset($current[$tag][0])) {//If there is a 0th element it is already an array 
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {//This section will make the value an array if multiple tags with the same name appear together
                        $current[$tag] = array($current[$tag], $result); //This will combine the existing item and the new item together to make an array
                        $repeated_tag_index[$tag . '_' . $level] = 2;

                        if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well 
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = &$current[$tag][$last_item_index];
                }
            } elseif ($type == "complete") { //Tags that ends in 1 line '<tag />' 
                //See if the key is already taken. 
                if (!isset($current[$tag])) { //New Key 
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                } else { //If taken, put all things inside a list(array) 
                    if (isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array... 
                        // ...push the new element into that array. 
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;

                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else { //If it is not an array... 
                        $current[$tag] = array($current[$tag], $result); //...Make it an array using using the existing value and the new value
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well 
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }

                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken 
                    }
                }
            } elseif ($type == 'close') { //End of tag '</tag>' 
                $current = &$parent[$level - 1];
            }
        }

        return($xml_array);
    }

}
