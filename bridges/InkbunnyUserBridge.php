<?php

class InkbunnyUserBridge extends BridgeAbstract
{
    const NAME = 'Inkbunny User Bridge';
    const URI = 'https://inkbunny.net';
    const CACHE_TIMEOUT = 900; // 15mn
    const MAINTAINER = 'mruac';
    const DESCRIPTION = 'Bridges for Inkbunny.net <br> For Submissions search bridge: Leave "Type" blank for all submission types.';
    const PARAMETERS = [
        'Submissions search' => [
            // 'output_mode' => ['name'=>'','type'=>'checkbox'],
            // 'rid' => ['name'=>'','type'=>'checkbox'],
            // 'submission_ids_only' => ['name'=>'','type'=>'checkbox'],
            // 'submissions_per_page' => ['name'=>'','type'=>'checkbox'],
            // 'page' => ['name'=>'','type'=>'checkbox'],
            // 'keywords_list' => ['name'=>'','type'=>'checkbox'],
            // 'no_submissions' => ['name'=>'','type'=>'checkbox'],
            // 'get_rid' => ['name'=>'','type'=>'checkbox'],
            'unread_submissions' => [
                'name' => 'Unread submissions',
                'type' => 'checkbox'
            ],
            'text' => [
                'name' => 'Search string (space seperated)',
                'type' => 'text',
                'exampleValue' => 'included_word -excluded_word'
            ],
            'string_join_type' => [
                'name' => 'Search string type',
                'type' => 'list',
                'values' => [
                    'Find any one of the words' => 'or',
                    'Find all the words together' => 'and',
                    'Contains the exact phrase' => 'exact'
                ],
                'defaultValue' => 'and'
            ],
            'keywords' => [
                'name' => 'Search in: Keywords',
                'type' => 'checkbox',
                'defaultValue' => true
            ],
            'title' => [
                'name' => 'Search in: Title',
                'type' => 'checkbox'
            ],
            'description' => [
                'name' => 'Search in: Description/Story',
                'type' => 'checkbox'
            ],
            'md5' => [
                'name' => 'Search in: MD5 hash',
                'type' => 'checkbox'
            ],
            'field_join_type' => [
                'name' => 'Match all (AND) of "Search in" selections (Keywords / Title / Desc / MD5)',
                'type' => 'checkbox'
            ],
            'keyword_id' => [
                'name' => 'Keyword ID (overrides above text search)',
                'type' => 'number',
                'title' => 'Get ID from end of "keyword_id" in link. e.g. inkbunny.net/search_process.php?keyword_id=1114'
            ],
            // 'username' => ['name' => 'From username','type' => 'text'],
            'rating1' => [
                'name' => 'Maturity: General',
                'type' => 'checkbox',
                'defaultValue' => true
            ],
            'rating2' => [
                'name' => 'Maturity: Mature - Nudity',
                'type' => 'checkbox'
            ],
            'rating3' => [
                'name' => 'Maturity: Mature - Violence',
                'type' => 'checkbox'
            ],
            'rating4' => [
                'name' => 'Maturity: Adult - Sexual Themes',
                'type' => 'checkbox'
            ],
            'rating5' => [
                'name' => 'Maturity: Adult - Strong Violence',
                'type' => 'checkbox'
            ],
            'user_id' => [
                'name' => 'Submissions From user ID',
                'type' => 'number',
                'defaultValue' => 0,
                'title' => 'Get ID from end of "user_id" in link. e.g. inkbunny.net/userfavorites_process.php?favs_user_id=1'
            ],
            'favs_user_id' => [
                'name' => 'Favourites by user ID',
                'type' => 'number',
                'defaultValue' => 0,
                'title' => 'Get ID from end of "user_id" in link. e.g. inkbunny.net/userfavorites_process.php?favs_user_id=1'

            ],
            'picture' => [ // 1
                'name' => 'Type: Picture/Pinup',
                'type' => 'checkbox'
            ],
            'sketch' => [ // 2
                'name' => 'Type: Sketch',
                'type' => 'checkbox'
            ],
            'picseries' => [ // 3
                'name' => 'Type: Picture Series',
                'type' => 'checkbox'
            ],
            'comic' => [ // 4
                'name' => 'Type: Comic',
                'type' => 'checkbox'
            ],
            'portfolio' => [ // 5
                'name' => 'Type: Portfolio',
                'type' => 'checkbox'
            ],
            'flashanim' => [ // 6
                'name' => 'Type: Shockwave/Flash - Animation',
                'type' => 'checkbox'
            ],
            'flashgame' => [ // 7
                'name' => 'Type: Shockwave/Flash - Interactive',
                'type' => 'checkbox'
            ],
            'vidfeat' => [ // 8
                'name' => 'Type: Video - Feature Length',
                'type' => 'checkbox'
            ],
            'vidanim' => [ // 9
                'name' => 'Type: Video - Animation/3D/CGI',
                'type' => 'checkbox'
            ],
            'mussingle' => [ // 10
                'name' => 'Type: Music - Single Track',
                'type' => 'checkbox'
            ],
            'musalbum' => [ // 11
                'name' => 'Type: Music - Album',
                'type' => 'checkbox'
            ],
            'writdoc' => [ // 12
                'name' => 'Type: Writing - Document',
                'type' => 'checkbox'
            ],
            'charsheet' => [ // 13
                'name' => 'Type: Character Sheet',
                'type' => 'checkbox'
            ],
            'photo' => [ // 14
                'name' => 'Type: Photography - Fursuit/Sculpture/Jewelry/etc',
                'type' => 'checkbox'
            ],
            //'sales' => ['name'=>'Sale type','type'=>'list','values'=>[]], //[Removed](https://wiki.inkbunny.net/wiki/Prints)
            'pool_id' => [
                'name' => 'Pool ID',
                'type' => 'number',
                'defaultValue' => 0,
                'title' => 'Get ID from end of "pool_id" in link. e.g. inkbunny.net/poolview_process.php?pool_id=1'
            ],
            'orderby' => [
                'name' => 'Sort results by',
                'type' => 'list',
                'values' => [
                    'Date Created' => 'create_datetime',
                    'Date Modified' => 'last_file_update_datetime',
                    'Newest unread submissions' => 'unread_datetime',
                    'Oldest unread submissions' => 'unread_datetime_reverse',
                    'Views' => 'views',
                    // '' => 'total_print_sales',
                    // '' => 'total_digital_sales',
                    // '' => 'total_sales',
                    'Username' => 'username',
                    'Date favourited (only if "Favourites from username" is set)' => 'fav_datetime',
                    'Number of Stars given (only if "Favourites from username" is set)' => 'fav_stars',
                    'Pool order (Only if "Pool ID" is set, sorts as specified by pool)' => 'pool_order'
                ],
                'defaultValue' => 'create_datetime'
            ],
            'dayslimit' => [ //if 0, return nothing to the api.
                'name' => 'In the last number of Days (0 = all time)',
                'type' => 'number',
                'defaultValue' => 0
            ],
            'random' => [
                'name' => 'Sort results randomly',
                'type' => 'checkbox',
            ],
            'scraps' => [
                'name' => 'Include scraps?',
                'type' => 'list',
                'values' => [
                    'Main and Scraps galleries' => 'both',
                    'Main gallery only' => 'no',
                    'Scraps gallery only' => 'only'
                ],
                'defaultValue' => 'both'
            ],
            // 'count_limit' => ['name' => '', 'type' => 'checkbox'],
        ],
        'Newest submissions from your watching' => [],
        'Newest Favourites from your watching' => [],
    ];
    const CONFIGURATION = [ // If no username/pass, get guest SID and disable user pages and search capabilities ("from your watching" contexts and "unread_datetime" search options).
        'username' => [
            'required' => false
        ],
        'password' => [
            'required' => false
        ]
    ];

    private $sid;

    public function collectData()
    {
        if ($this->queriedContext === 'Submissions search') {
            $opts = [
                'unread_submissions' =>     $this->getInput('unread_submissions') ? 'yes' : 'no', //Restrict to unread submissions inbox of user
                'text' =>      strlen($this->getInput('text')) === 0 ? null : $this->getInput('text'), //search string
                'string_join_type' =>   $this->getInput('string_join_type'), //AND, OR, EXACT
                'keywords' => $this->getInput('keywords') ? 'yes' : 'no', //search in keywords
                'title' => $this->getInput('title') ? 'yes' : 'no', //search in title
                'description' => $this->getInput('description') ? 'yes' : 'no', //search in desc
                'md5' => $this->getInput('md5') ? 'yes' : 'no', //search in [MD5 hash](https://wiki.inkbunny.net/wiki/MD5#Stored_MD5_Hashes)
                'field_join_type' =>   $this->getInput('field_join_type') ? 'and' : 'or',
                'keyword_id' =>     $this->getInput('keyword_id') === 0 ? null : $this->getInput('keyword_id'), //SINGLE keyword search (overrides all text search options)
                'user_id' =>   $this->getInput('user_id') === 0 ? null : $this->getInput('user_id'),
                'favs_user_id' =>    $this->getInput('favs_user_id') === 0 ? null :  $this->getInput('favs_user_id'),
                //TESTME: try 'content_tags[]' in the param? - opts obtained from search UI pagea
                'content_tags[1]' => $this->getInput('rating1') ? 'on' : null, // General
                'content_tags[2]' => $this->getInput('rating2') ? 'on' : null, // Nudity - Nonsexual nudity exposing breasts or genitals (must not show arousal)
                'content_tags[3]' => $this->getInput('rating3') ? 'on' : null, // Violence - Mild violence
                'content_tags[4]' => $this->getInput('rating4') ? 'on' : null, // Sexual Themes - Erotic imagery, sexual activity or arousal
                'content_tags[5]' => $this->getInput('rating5') ? 'on' : null,  // Strong Violence - Strong violence, blood, serious injury or death
                'type' =>       [ //type - Limit results to submissions with this type id. Multiple type ids are allowed, separated by commas, NO SPACES.
                    '1' => $this->getInput('picture'),
                    '2' => $this->getInput('sketch'),
                    '3' => $this->getInput('picseries'),
                    '4' => $this->getInput('comic'),
                    '5' => $this->getInput('portfolio'),
                    '6' => $this->getInput('flashanim'),
                    '7' => $this->getInput('flashgame'),
                    '8' => $this->getInput('vidfeat'),
                    '9' => $this->getInput('vidanim'),
                    '10' => $this->getInput('mussingle'),
                    '11' => $this->getInput('musalbum'),
                    '12' => $this->getInput('writdoc'),
                    '13' => $this->getInput('charsheet'),
                    '14' => $this->getInput('photo')
                ],
                'pool_id' =>    $this->getInput('pool_id') === 0 ? null : $this->getInput('pool_id'), //pool_id - Show only submissions from the Pool that has this Pool ID.
                'orderby' =>    $this->getInput('orderby'), //orderby - Order search results by selected criteria.
                'dayslimit' =>  $this->getInput('dayslimit') === 0 ? null : $this->getInput('dayslimit'), //dayslimit - Limit results to those uploaded in the last X number of days. (if 0, then n/a)
                'random' =>     $this->getInput('random')  ? 'yes' : 'no',
                'scraps' =>     $this->getInput('scraps'),
            ];

            $type_str = implode(',', array_keys(array_filter($opts['type'], function ($v) {
                return $v;
            })));
            $opts['type'] = strlen($type_str) > 0 ? $type_str : null;

            $opts = array_filter($opts, function ($v) {
                return $v !== null;
            });

            if (!array_key_exists('content_tags[1]', $opts)) {
                $opts['content_tags[1]'] = 'on';
            }


            //now actually perform the search and parse it.
            $url = "https://inkbunny.net/api_search.php?sid={$this->getSID()}&submission_ids_only=yes";
            foreach ($opts as $key => $value) {
                $url .= "{$key}={$value}&";
            }
            $res = $this->getData($url, true);
            // foreach ($res['submissions'] as $submissions){}
            $submission_ids = implode(',', array_reduce($res['submissions'], function ($acc, $v) {
                array_push($acc, $v['submission_id']);
                return $acc;
            }, []));
            $opts = [
                'sort_keywords_by' => 'submissions_count',
                'show_description_bbcode_parsed' => 'yes', //up to 100,000 chars
                'show_pools' => 'yes'
                // 'show_writing_bbcode_parsed' => 'yes' //only if submission type is writing, and up to 100,000+ chars. Disabled for this reason.
            ];
            $url = "https://inkbunny.net/api_submissions.php?sid={$this->getSID()}&submission_ids={$submission_ids}";
            foreach ($opts as $key => $value) {
                $url .= "{$key}={$value}&";
            }
            //DEBUG:
            $url = 'https://inkbunny.net/api_submissions.php?sid=,DB-zXrWi1R62-3JxHkrMBixoJ&submission_ids=3099200,3098904,3099828,3099825,3099617,3099216,3097260,3095214,2559416,3059911,3055417&sort_keywords_by=submissions_count&show_description_bbcode_parsed=yes&show_pools=yes';
            $data = $this->getData($url, true);

            //note: test content parse here? https://inkbunny.net/bbcode.php
            foreach ($data['submissions'] as $submission) {
                /* 
                $item['uri']        // URI to reach the subject ("https://...")
                $item['title']      // Title of the item
                $item['timestamp']  // Timestamp of the item in numeric or text format (compatible for strtotime())
                $item['author']     // Name of the author for this item
                $item['content']    // Content in HTML format
                $item['enclosures'] // Array of URIs to an attachments (pictures, files, etc...)
                $item['categories'] // Array of categories / tags / topics
                $item['uid']        // A unique ID to identify the current item
                */

                $item = [];
                $content = '';

                foreach ($submission['files'] as $file) {
                    preg_match('/\.(doc|rtf|txt|json|flv|mp4|swf|mp3|png|jpg|gif)$/', $file['file_url_full'], $matches);
                    $ext = $matches[1];

                    preg_match("/{$file['file_id']}_{$submission['username']}_(.*)/", $file['file_url_full'], $matches);
                    $filename = $matches[1];

                    //if no previewable "screen" size of the file, get thumbnail (or default thumbnail if no thumbnail).
                    if ($file['screen_size_x'] === null) { 
                        if (array_key_exists('thumbnail_url_huge', $file)) {
                            $src = $file['thumbnail_url_huge'];
                        } else {
                            $src = $this->getThumb($ext);
                        }
                    } else {
                        $src = $file['file_url_screen'];
                    }
                    //check filetype and add accordingly. If Flash use img.
                    switch ($ext) {
                        case 'flv':
                        case 'mp4':
                            $content .= "<p>Download video: <a href=\"{$file['file_url_full']}\">{$filename}<br><img src=\"{$src}\"></a></p><br>";
                            break;
                        case 'mp3':
                            $content .= "<p>Download audio: <a href=\"{$file['file_url_full']}\">{$filename}<br><img src=\"{$src}\"></a></p><br>";
                            break;
                        case 'swf':
                            $content .= "<p>Download flash: <a href=\"{$file['file_url_full']}\">{$filename}<br><img src=\"{$src}\"></a></p><br>";
                            break;
                        case 'doc':
                        case 'rtf':
                        case 'txt':
                        case 'json':
                            $content .= "<p>Download document: <a href=\"{$file['file_url_full']}\">{$filename}<br><img src=\"{$src}\"></a></p><br>";
                            break;
                        case 'png':
                        case 'jpg':
                        case 'gif':
                        default:
                            $content .= "<p><img src=\"{$src}\"/></p><br>";
                            break;
                    }
                }
                if($submission['description_bbcode_parsed'] !== '<span style=\'word-wrap: break-word;\'></span>'){
                $content .= '<hr>' . $submission['description_bbcode_parsed'];
                }

                if (sizeof($submission['pools']) > 0) {
                    $content .= '<hr>📁 In Pool(s): ';
                    foreach ($submission['pools'] as $pool) {
                        $pool_url = self::URI . "/poolview_process.php?pool_id={$pool['pool_id']}";
                        $content .= "<a href=\"{$pool_url}\">{$pool['name']}</a> ";
                    }
                }

                $content .= '<hr>🏷 Keywords: ';
                foreach ($submission['keywords'] as $keyword) {
                    $keyword_url = self::URI . "/search_process.php?keyword_id={$keyword['keyword_id']}";
                    $content .= "<a href=\"{$keyword_url}\">{$keyword['keyword_name']}</a> ";
                }

                $item['uri'] = self::URI . '/s/' . $submission['submission_id'];
                $item['title'] = $submission['title'];
                $item['timestamp'] = $submission['last_file_update_datetime'];
                $item['author'] = $submission['username'];
                $item['enclosures'] = array_reduce($submission['files'], function ($acc, $file) {
                    array_push($acc, $file['file_url_full']);
                    return $acc;
                }, []);
                $item['categories']  = [$submission['type_name']];
                $item['content'] = $this->setReferrerPolicy($content);

                $item['uid'] = self::URI . '/s/' . $submission['submission_id'];

                $this->addItem($item);
            }
        }
        if ($this->queriedContext === 'Newest submissions from your watching') {
        }
        if ($this->queriedContext === 'Newest Favourites from your watching') {
        }
    }

    private function setReferrerPolicy(string $htmlstr)
    {
        $html = str_get_html($htmlstr);
        foreach ($html->find('img') as $img) {
            /* src:
             * Note: Without the no-referrer policy their CDN  denies requests.
             */
            $img->referrerpolicy = 'no-referrer';
        }

        foreach ($html->find('a') as $a){
            $a->rel = 'noreferrer';
        }

        return $html;
    }

    private function getThumb(string $type = null)
    {
        switch ($type) {
            case 'doc':
            case 'rtf':
            case 'txt':
            case 'json':
                return 'https://au.ib.metapix.net/images78/overlays/writing.png';
                break;
            case 'flv':
            case 'mp4':
                return 'https://au.ib.metapix.net/images78/overlays/video.png';
                break;
            case 'swf':
                return 'https://au.ib.metapix.net/images78/overlays/shockwave.png';
                break;
            case 'mp3':
                return 'https://au.ib.metapix.net/images78/overlays/audio.png';
                break;
            case 'png':
            case 'jpg':
            case 'gif':
            default:
                return 'https://au.ib.metapix.net/images78/overlays/nofile.png';
                break;
        }
    }

    private function getData(string $url, bool $cache = false, array $httpHeaders = [], array $curlOptions = [], bool $getJSON = true)
    {
        Debug::log($url);
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

    private function addItem($item)
    {
        if (is_null($item)) {
            return;
        }

        if (is_array($item) || is_object($item)) {
            $this->items[] = $item;
        } else {
            returnServerError("Incorrectly parsed item. Check the code!<br>Type: " . gettype($item) . "<br>var_dump($item):<br>" . var_dump($item));
        }
    }

    private function getSID()
    {
        /*
        Members can still choose to block their work from Guest users, regardless of the Guests' rating choice, so some work may still not appear for Guests even with all rating options turned on.
        */
        //if sid exists, renew it. else get sid.
        $sid = $this->loadCacheValue('sid');
        if (!isset($sid)) {
            $user = Configuration::getConfig(get_class(), 'username', 'guest');
            $pass = Configuration::getConfig(get_class(), 'password');

            $authstr = "username={$user}";
            if ($pass) {
                $authstr .= "&password={$pass}";
            }
            $res = $this->getData(self::URI . "/api_login.php?{$authstr}", true);
            if (array_key_exists('sid', $res)) {
                if ($user === 'guest') {
                    $res = $this->getData(self::URI . '/api_userrating.php?' . implode('&', [
                        'sid=' . $res['sid'],
                        // 'tag[1]=yes', // General - on by default for guests
                        'tag[2]=yes', // Nudity - Nonsexual nudity exposing breasts or genitals (must not show arousal)
                        'tag[3]=yes', // Violence - Mild violence
                        'tag[4]=yes', // Sexual Themes - Erotic imagery, sexual activity or arousal
                        'tag[5]=yes', // Strong Violence - Strong violence, blood, serious injury or death
                    ]));

                    if (array_key_exists('sid', $res)) {
                        $this->saveCacheValue('sid', $res['sid']);
                        $this->sid = $res['sid'];
                    }
                }
            } else {
                return returnServerError('Error from InkBunny: ' . $res['error_message']);
            }
            $sid = $res['sid'];
        }

        $this->saveCacheValue('sid', $sid);
        return $sid;
    }

    public function getParameters()
    {
        //removes user options if not logged in and authenticated (eg. guest)
        $arr = static::PARAMETERS;
        if (Configuration::getConfig(get_class(), 'password') && $this->sid) {
            return $arr;
        } else {
            unset(
                $arr['Newest submissions from your watching'],
                $arr['Newest Favourites from your watching'],
                $arr['Submissions search']['orderby']['values']['Newest unread submissions'],
                $arr['Submissions search']['orderby']['values']['Oldest unread submissions'],
                $arr['Submissions search']['unread_submissions']
            );
            return $arr;
        }
    }

    public function getName()
    {
        $this->getSID();
        if (Configuration::getConfig(get_class(), 'password') && $this->sid) {
            return Configuration::getConfig(get_class(), 'username') . '\'s ' . static::NAME;
        } else {
            return static::NAME;
        }
    }
}
