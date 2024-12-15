<?php
//https://rss-bridge.github.io/rss-bridge/Bridge_API/BridgeAbstract.html
class mruac_TestBridge extends BridgeAbstract
{
    const NAME = 'test Bridge';
    const URI = 'https://crouton.net';
    // const CACHE_TIMEOUT = 900; // 15mn
    const CACHE_TIMEOUT = 0;
    const MAINTAINER = 'mruac';
    const DESCRIPTION = 'test bridge playground';
    const PARAMETERS = [
        'parameter group' => [
            'text_var' => [
                'name' => 'input text tag',
                'type' => 'text',
                // 'required' => true
            ],
            'num_var' => [
                'name' => 'input number tag',
                'type' => 'number',
                'title' => 'hint on hover'
            ],
            'bool_var' => [
                'name' => 'input checkbox tag',
                'type' => 'checkbox'
            ]
        
        ]
    ];

    //collectData() is run before any other "getX()" is called. useful to gather info then create the dynamic titles / feed uris after.
    //eg. set private $profile and then get it in collectData(), then use $this->profile in getName, getIcon, getURI, etc.
    public function collectData()
    {
        $table = <<<EOD
<video width="320" height="240" poster="https://www.w3schools.com/images/w3schools_green.jpg" controls>
   <source src="https://www.w3schools.com/TAgs/movie.mp4" type="video/mp4">
   Your browser does not support the video tag.
</video>
EOD;

        $this->addItem(
            [
            'uri' => 'https://crouton.net',
            'title' => 'le title2',
            'timestamp' => 'now',
            'author' => 'me',          
            'content' => '<b><i>behold the crouton</i></b>' . '<br>' . $table,
            'enclosures' => ['https://crouton.net/crouton.png'],
            'categories' => ['crouton', 'bread'],
            'uid' => '00002'             
            ]
        );

    }

    //function to generate rss feed name - recommended if dynamic depending on input vars
    public function getName()
    {
        return self::NAME;
    }

    //function to generate rss feed URI - recommended if dynamic depending on input vars
    public function getURI()
    {
        return self::URI;
    }

    //function to fetch and cache requests
    private function getData(string $url, bool $cache = false, bool $getJSON = false, array $httpHeaders = [], array $curlOptions = [])
    {
        // Debug::log($url);
        if ($getJSON) { //get JSON object
            if ($cache) {
                $data = $this->loadCacheValue($url, 86400); // 24 hours
                if (is_null($data)) {
                    $data = getContents($url, $httpHeaders, $curlOptions) or returnServerError("Could not load $url");
                    $this->saveCacheValue($url, $data);
                }
            } else {
                $data = getContents($url, $httpHeaders, $curlOptions) or returnServerError("Could not load $url");
            }
            return json_decode($data, true);
        } else { //get simpleHTMLDOM object
            if ($cache) {
                $html = getSimpleHTMLDOMCached($url, 86400); // 24 hours
            } else {
                $html = getSimpleHTMLDOM($url);
            }
            $html = defaultLinkTo($html, $url);
            return $html;
        }
    }

    /*
    */
        
    /**
    * function to add string to rss feed if not null (use null to veto an item)
    * $item = [
    * 'uri' => string             // URI to reach the subject ("https://...")
    * 'title' => string           // Title of the item
    * 'timestamp' => strtotime()  // Timestamp of the item in numeric or text format (compatible for strtotime())
    * 'author' => string          // Name of the author for this item
    * 'content' => html           // Content in HTML format
    * 'enclosures' => string[]    // Array of URIs to an attachments (pictures, files, etc...)
    * 'categories' => string[]    // Array of categories / tags / topics
    * 'uid' => string             // A unique ID to identify the current item
    * ]
     */
    private function addItem($item)
    {
        if (is_null($item)) {
            return;
        }

        if (is_array($item) || is_object($item)) {
            $this->items[] = $item;
        } else {
            returnServerError("Incorrectly parsed item. Check the code!\nType: " . gettype($item) . "\nprint_r(item:)\n" . print_r($item));
        }
    }
}
