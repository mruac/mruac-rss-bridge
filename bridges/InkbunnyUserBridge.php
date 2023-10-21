<?php

class InkbunnyUserBridge extends BridgeAbstract
{
    const NAME = 'Inkbunny User Bridge';
    const URI = 'https://inkbunny.net';
    const CACHE_TIMEOUT = 900; // 15mn
    const MAINTAINER = 'mruac';
    const DESCRIPTION = 'Bridges for Inkbunny.net <br> For Submissions search bridge: Leave "Type" unchecked for all submission types.';
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
                'name' => '(Default if none selected) Search in: Keywords',
                'type' => 'checkbox'
            ],
            'title' => [
                'name' => '(Default if none selected) Search in: Title',
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
                'name' => '(Default if none selected) Maturity: General',
                'type' => 'checkbox'
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
            'username' => [
                'name' => 'Submissions from username',
                'type' => 'text'
            ],
            'favs_username' => [
                'name' => 'Favourites by username',
                'type' => 'text'
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
            // 'dayslimit' => ['name' => 'In the last number of Days (0 = all time)','type' => 'number','defaultValue' => 0], //if 0, return nothing to the api.
            // 'random' => ['name' => 'Sort results randomly','type' => 'checkbox'],
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
        'Newest submissions from your watching' => []
    ];
    const CONFIGURATION = [ // If no username/pass, get guest SID and disable user pages and search capabilities ("from your watching" contexts and "unread_datetime" search options).
        'username' => [
            'required' => false
        ],
        'password' => [
            'required' => false
        ]
    ];

    public function collectData()
    {
        if ($this->queriedContext === 'Submissions search') {
            /** @var array $opts */
            $opts = [
                'unread_submissions' => $this->getInput('unread_submissions') ? 'yes' : 'no', //Restrict to unread submissions inbox of user
                'text' => strlen($this->getInput('text')) === 0 ? null : $this->getInput('text'), //search string
                'string_join_type' => $this->getInput('string_join_type'), //AND, OR, EXACT
                'keywords' => $this->getInput('keywords') ? 'yes' : 'no', //search in keywords
                'title' => $this->getInput('title') ? 'yes' : 'no', //search in title
                'description' => $this->getInput('description') ? 'yes' : 'no', //search in desc
                'md5' => $this->getInput('md5') ? 'yes' : 'no', //search in [MD5 hash](https://wiki.inkbunny.net/wiki/MD5#Stored_MD5_Hashes)
                'field_join_type' => $this->getInput('field_join_type') ? 'and' : 'or',
                'keyword_id' => $this->getInput('keyword_id') > 0 ? strval($this->getInput('keyword_id')) : null, //SINGLE keyword search (overrides all text search options)
                'username' => strlen($this->getInput('username')) === 0 ? null : strtolower($this->getInput('username')),
                'favs_username' => strlen($this->getInput('favs_username')) === 0 ? null : strtolower($this->getInput('favs_username')),
                'type' => [ //type - Limit results to submissions with this type id. Multiple type ids are allowed, separated by commas, NO SPACES.
                    '1'  => $this->getInput('picture'),
                    '2'  => $this->getInput('sketch'),
                    '3'  => $this->getInput('picseries'),
                    '4'  => $this->getInput('comic'),
                    '5'  => $this->getInput('portfolio'),
                    '6'  => $this->getInput('flashanim'),
                    '7'  => $this->getInput('flashgame'),
                    '8'  => $this->getInput('vidfeat'),
                    '9'  => $this->getInput('vidanim'),
                    '10' => $this->getInput('mussingle'),
                    '11' => $this->getInput('musalbum'),
                    '12' => $this->getInput('writdoc'),
                    '13' => $this->getInput('charsheet'),
                    '14' => $this->getInput('photo')
                ],
                'pool_id' => $this->getInput('pool_id') > 0 ? strval($this->getInput('pool_id')) : null, //pool_id - Show only submissions from the Pool that has this Pool ID.
                'orderby' => $this->getInput('orderby'), //orderby - Order search results by selected criteria.
                'scraps' => $this->getInput('scraps'),
            ];

            $ratings = [
                $this->getInput('rating1') ? '0' : null, // General
                $this->getInput('rating2') ? '1' : null, // Nudity - Nonsexual nudity exposing breasts or genitals (must not show arousal)
                $this->getInput('rating3') ? '2' : null, // Violence - Mild violence
                $this->getInput('rating4') ? '3' : null, // Sexual Themes - Erotic imagery, sexual activity or arousal
                $this->getInput('rating5') ? '4' : null  // Strong Violence - Strong violence, blood, serious injury or death
            ];

            if ($opts['username']) {
                $res = $this->getData(self::URI . "/api_username_autosuggest.php?username={$opts['username']}", true);
                $index = array_search($opts['username'], array_map('strtolower', array_column($res['results'], 'value')));
                if ($index > -1) {
                    $this->saveCacheValue($opts['username'], $res['results'][$index]['value']);
                    $opts['user_id'] = $res['results'][$index]['id'];
                    $opts['username'] = $res['results'][$index]['value'];
                } else {
                    returnServerError('Username not found: ' . $opts['username']);
                }
            }

            if ($opts['favs_username']) {
                $res = $this->getData(self::URI . "/api_username_autosuggest.php?username={$opts['favs_username']}", true);
                $index = array_search($opts['favs_username'], array_map('strtolower', array_column($res['results'], 'value')));
                if ($index > -1) {
                    $this->saveCacheValue($opts['favs_username'], $res['results'][$index]['value']);
                    $opts['favs_user_id'] = $res['results'][$index]['id'];
                    $opts['favs_username'] = $res['results'][$index]['value'];
                } else {
                    returnServerError('Username not found: ' . $opts['favs_username']);
                }
            }

            $type_str = implode(',', array_keys(array_filter($opts['type'], function ($v) {
                return $v;
            })));
            $opts['type'] = strlen($type_str) > 0 ? $type_str : null;

            $opts = array_filter($opts, function ($v) {
                return $v !== null;
            });

            $ratings = array_filter($ratings, function ($v) {
                return $v !== null;
            });

            //set defaults
            if (
                sizeof(array_diff(['0', '1', '2', '3', '4'], $ratings)) === 5
            ) {
                $ratings = ['0'];
            }

            if (
                $opts['keywords'] === 'no'
                && $opts['title'] === 'no'
                && $opts['description'] === 'no'
                && $opts['md5'] === 'no'
            ) {
                $opts['keywords'] = 'yes';
                $opts['title'] = 'yes';
            }

            $url = "https://inkbunny.net/api_search.php?sid={$this->getSID()}&submissions_per_page=30";
            foreach ($opts as $key => $value) {
                $url .= "&{$key}={$value}";
            }
            $res = $this->getData($url, true);

            $submission_ids = implode(
                ',',
                array_reduce($res['submissions'], function ($acc, $v) use ($ratings) {
                    if (in_array($v['rating_id'], $ratings)) {
                        array_push($acc, $v['submission_id']);
                    }
                    return $acc;
                }, [])
            );
            $submission_opts = [
                'sort_keywords_by' => 'submissions_count',
                'show_description_bbcode_parsed' => 'yes', //up to 100,000 chars
                'show_pools' => 'yes'
                // 'show_writing_bbcode_parsed' => 'yes' //only if submission type is writing, and up to 100,000+ chars. Disabled for this reason.
            ];
            $url = self::URI . "/api_submissions.php?sid={$this->getSID()}&submission_ids={$submission_ids}";
            foreach ($submission_opts as $key => $value) {
                $url .= "&{$key}={$value}";
            }
            $data = $this->getData($url, true);

            if ($opts['keyword_id'] ?? null) {
                $index = array_search($opts['keyword_id'], array_column($data['submissions'][0]['keywords'], 'keyword_id'));
                $this->saveCacheValue($opts['keyword_id'], $data['submissions'][0]['keywords'][$index]['keyword_name']);
            }
            if ($opts['pool_id'] ?? null) {
                $index = array_search($opts['pool_id'], array_column($data['submissions'][0]['pools'], 'pool_id'));
                $this->saveCacheValue($opts['pool_id'], $data['submissions'][0]['pools'][$index]['name']);
                $this->saveCacheValue($opts['pool_id'] . '_username', $data['submissions'][0]['username']);
            }

            $this->parseSubmissions(array_reverse($data['submissions']));
        }

        if ($this->queriedContext === 'Newest submissions from your watching') {
            $url = self::URI . "/api_search.php?sid={$this->getSID()}&submissions_per_page=30&unread_submissions=yes&submission_ids_only=yes";
            $res = $this->getData($url, true);
            $submission_ids = implode(
                ',',
                array_reduce($res['submissions'], function ($acc, $v) {
                    array_push($acc, $v['submission_id']);
                    return $acc;
                }, [])
            );
            $opts = [
                'sort_keywords_by' => 'submissions_count',
                'show_description_bbcode_parsed' => 'yes', //up to 100,000 chars
                'show_pools' => 'yes'
                // 'show_writing_bbcode_parsed' => 'yes' //only if submission type is writing, and up to 100,000+ chars. Disabled for this reason.
            ];
            $url = self::URI . "/api_submissions.php?sid={$this->getSID()}&submission_ids={$submission_ids}";
            foreach ($opts as $key => $value) {
                $url .= "&{$key}={$value}";
            }
            $data = $this->getData($url, true);
            $this->parseSubmissions(array_reverse($data['submissions']));
        }
    }

    private function parseSubmissions(array $submissions)
    {
        foreach ($submissions as $submission) {
            $item = [];
            $content = '';

            foreach ($submission['files'] as $file) {
                preg_match('/\.(doc|rtf|txt|json|flv|mp4|swf|mp3|png|jpg|gif)$/', $file['file_url_full'], $matches);
                $ext = $matches[1];

                preg_match("/{$file['file_id']}_[[:alnum:]]+?_(.*)/", $file['file_name'], $matches);
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

            if ($submission['description_bbcode_parsed'] !== '<span style=\'word-wrap: break-word;\'></span>') {
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

    private function setReferrerPolicy(string $htmlstr)
    {
        $html = str_get_html($htmlstr);
        foreach ($html->find('img') as $img) {
            /* src:
             * Note: Without the no-referrer policy their CDN  denies requests.
             */
            $img->referrerpolicy = 'no-referrer';
        }

        foreach ($html->find('a') as $a) {
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
            returnServerError('Incorrectly parsed item. Check the code!<br>Type: ' . gettype($item) . "<br>var_dump($item):<br>" . var_dump($item));
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
            $res = $this->getData(self::URI . "/api_login.php?{$authstr}");
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

    public function getParameters(): array
    {
        //removes user options if not logged in and authenticated (eg. guest)
        $arr = static::PARAMETERS;
        if (
            Configuration::getConfig(get_class(), 'password')
            && Configuration::getConfig(get_class(), 'username') !== null
            && strlen(Configuration::getConfig(get_class(), 'username')) > 0
        ) {
            return $arr;
        } else {
            unset(
                $arr['Newest submissions from your watching'],
                $arr['Submissions search']['orderby']['values']['Newest unread submissions'],
                $arr['Submissions search']['orderby']['values']['Oldest unread submissions'],
                $arr['Submissions search']['unread_submissions']
            );
            return $arr;
        }
    }

    public function getName()
    {
        if ($this->queriedContext === 'Submissions search') {
            $unread_submissions = $this->getInput('unread_submissions');
            $text = strlen($this->getInput('text')) > 0 ? $this->getInput('text') : null;
            $username = strlen($this->getInput('username')) === 0 ? null : strtolower($this->getInput('username'));
            $favs_username = strlen($this->getInput('favs_username')) === 0 ? null : strtolower($this->getInput('favs_username'));
            $keyword_id = $this->getInput('keyword_id') > 0 ? strval($this->getInput('keyword_id')) : null;
            $pool_id = $this->getInput('pool_id') > 0 ? strval($this->getInput('pool_id')) : null;

            if ($text) {
                $name = "InkBunny Search for: {$text}";
            }

            if ($username) {
                $val = $this->loadCacheValue($username);
                if ($val) {
                    $name = "Submissions from InkBunny user: {$val}";
                } else {
                    $name = "Submissions from InkBunny user: {$username}";
                }
            }

            if ($favs_username) {
                $val = $this->loadCacheValue($username);
                if ($val) {
                    $name = "Favourites from InkBunny user: {$val}";
                } else {
                    $name = "Favourites from InkBunny user: {$favs_username}";
                }
            }

            if ($keyword_id) {
                if ($this->loadCacheValue($keyword_id)) {
                    $name = "InkBunny submissions for keyword: {$this->loadCacheValue($keyword_id)}";
                } else {
                    $name = "InkBunny submissions for keyword ID: {$keyword_id}";
                }
            }

            if ($pool_id) {
                if ($this->loadCacheValue($pool_id)) {
                    $name = "InkBunny pool: {$this->loadCacheValue($pool_id)} - by {$this->loadCacheValue($pool_id . '_username')}";
                } else {
                    $name = "In InkBunny pool ID: {$pool_id}";
                }
            }

            if ($unread_submissions) {
                $name .= ' from unread submissions';
            }
            return $name;
        }

        if (Configuration::getConfig(get_class(), 'password')) {
            if (
                $this->queriedContext === 'Newest submissions from your watching'
                && Configuration::getConfig(get_class(), 'username') !== null
                && strlen(Configuration::getConfig(get_class(), 'username')) > 0
            ) {
                return 'New Submissions from ' . Configuration::getConfig(get_class(), 'username') . '\'s watching';
            }

            return Configuration::getConfig(get_class(), 'username') . '\'s ' . static::NAME;
        }

        return static::NAME;
    }
}
