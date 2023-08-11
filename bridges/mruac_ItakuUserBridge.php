<?php

class mruac_ItakuUserBridge extends BridgeAbstract
{
    const NAME = 'Itaku.ee User Bridge';
    const URI = 'https://itaku.ee';
    const CACHE_TIMEOUT = 900; // 15mn
    const MAINTAINER = 'mruac';
    const DESCRIPTION = 'Bridges for Authenticated Itaku.ee User pages';
    const PARAMETERS = [
        'Home feed of your following' => [
            'shares' => [
                'name' => 'Include reshares',
                'type' => 'checkbox',
            ],
            'rating_s' => [
                'name' => 'Include SFW',
                'type' => 'checkbox',
            ],
            'rating_q' => [
                'name' => 'Include Questionable',
                'type' => 'checkbox',
            ],
            'rating_e' => [
                'name' => 'Include NSFW',
                'type' => 'checkbox',
            ]
        ]
    ];
    const CONFIGURATION = [
        'auth' => [
            'required' => true
        ]
    ];
    private $token;

    public function collectData()
    {
        $this->token = $this->getOption('auth');

        if ($this->queriedContext === 'Home feed of your following') {

            $opt = [
                'order' => $this->getInput('order'),
                'range' => $this->getInput('range'),
                'reshares' => $this->getInput('reshares'),
                'rating_s' => $this->getInput('rating_s'),
                'rating_q' => $this->getInput('rating_q'),
                'rating_e' => $this->getInput('rating_e')
            ];

            $data = $this->getFeed($opt);

            foreach ($data['results'] as $record) {
                switch ($record['content_type']) {
                    case "reshare": {
                            //get type of reshare and its id
                            $id = $record['content_object']['content_object']['id'];
                            switch ($record['content_object']['content_type']) {
                                case "galleryimage": {
                                        $item = $this->getImage($id);
                                        $item['title'] = "{$record['owner_username']} shared: {$item['title']}";
                                        break;
                                    }
                                case "commission": {
                                        $item = $this->getCommission($id, $record['content_object']['content_object']);
                                        $item['title'] = "{$record['owner_username']} shared: {$item['title']}";
                                        break;
                                    }
                                case "post": {
                                        $item = $this->getPost($id, $record['content_object']['content_object']);
                                        $item['title'] = "{$record['owner_username']} shared: {$item['title']}";
                                        break;
                                    }
                            }
                            break;
                        }
                    case "galleryimage": {
                            $item = $this->getImage($record['content_object']['id']);
                            break;
                        }
                    case "commission": {
                            $item = $this->getCommission($record['content_object']['id'], $record['content_object']);
                            break;
                        }
                    case "post": {
                            $item = $this->getPost($record['content_object']['id'], $record['content_object']);
                            break;
                        }
                }

                $this->addItem($item);
            }
        }
    }

    private function getFeed(array $opt)
    {
        $url = self::URI . "/api/feed/?date_range={$opt['range']}&ordering={$opt['order']}&page=1&page_size=30&format=json&visibility=PUBLIC&visibility=PROFILE_ONLY&by_following=true";

        if (!$opt['reshares']) {
            $url .= "&hide_reshares=true";
        }
        if ($opt['rating_s']) {
            $url .= "&maturity_rating=SFW";
        }
        if ($opt['rating_q']) {
            $url .= "&maturity_rating=Questionable";
        }
        if ($opt['rating_e']) {
            $url .= "&maturity_rating=NSFW";
        }

        return $this->getData($url, false, true);
    }

    private function getPost($id, array $metadata = null)
    {
        //FIXME: Error if 404 due to either deleted or non-existent post. (Do same for images, comments and commissions.)
        $uri = self::URI . '/posts/' . $id;
        $url = self::URI . '/api/posts/' . $id . '/?format=json';
        try {
            $data = $metadata ?? $this->getData($url, true, true)
                or returnServerError("Could not load $url");
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                return [
                    'uri' => $uri,
                    'title' => "Deleted post",
                    'timestamp' => '@0',
                    'author' =>  'deleted',
                    'content' => 'Deleted post',
                    'categories' => ['post', 'deleted'],
                    'uid' => $uri
                ];
            } else {
                returnServerError(var_dump($e));
            }
        }

        $content_str = nl2br($data['content']);
        $content = "<p>{$content_str}</p><br/>"; //TODO: Add link and itaku user mention detection and convert into links.

        if (sizeof($data['tags']) > 0) {
            $content .= "🏷 Tag(s): ";
            foreach ($data['tags'] as $tag) {
                $url = self::URI . '/home/images?tags=' . $tag['name'];
                $content .= "<a href=\"{$url}\">#{$tag['name']}</a> ";
            }
            $content .= "<br/>";
        }

        if (sizeof($data['folders']) > 0) {
            $content .= "📁 In Folder(s): ";
            foreach ($data['folders'] as $folder) {
                $url = self::URI . '/profile/' . $data['owner_username'] . '/posts/' . $folder['id'];
                $content .= "<a href=\"{$url}\">#{$folder['title']}</a> ";
            }
        }

        $content .= "<hr/>";
        if (sizeof($data['gallery_images']) > 0) {
            foreach ($data['gallery_images'] as $media) {
                $title = $media['title'];
                $url = self::URI . '/images/' . $media['id'];
                $src = $media['image_xl'];
                $content .= "<p>";
                $content .= "<a href=\"{$url}\"><b>{$title}</b></a><br/>";
                if ($media['is_thumbnail_for_video']) {
                    $url = self::URI . '/api/galleries/images/' . $media['id'] . '/?format=json';
                    $media_data = $this->getData($url, true, true)
                        or returnServerError("Could not load $url");
                    $content .= "<video controls src=\"{$media_data['video']['video']}\" poster=\"{$media['image_xl']}\"/>";
                } else {
                    $content .= "<a href=\"{$url}\"><img src=\"{$src}\"></a>";
                }
                $content .= "</p><br/>";
            }
        }

        return [
            'uri' => $uri,
            'title' => $data['title'],
            'timestamp' => $data['date_added'],
            'author' =>  $data['owner_username'],
            'content' => $content,
            'categories' => ['post'],
            'uid' => $uri
        ];
    }

    private function getCommission($id, array $metadata = null)
    {
        $url = self::URI . '/api/commissions/' . $id . '/?format=json';
        $uri = self::URI . '/commissions/' . $id;

        try {
            $data = $metadata ?? $this->getData($url, true, true)
                or returnServerError("Could not load $url");
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                return [
                    'uri' => $uri,
                    'title' => "Deleted commission",
                    'timestamp' => '@0',
                    'author' =>  'deleted',
                    'content' => 'Deleted commission',
                    'categories' => ['commission', 'deleted'],
                    'uid' => $uri
                ];
            } else {
                returnServerError(var_dump($e));
            }
        }

        $content_str = nl2br($data['description']);
        $content = "<p>{$content_str}</p><br>"; //TODO: Add link and itaku user mention detection and convert into links.

        if (array_key_exists('tags', $data) && sizeof($data['tags']) > 0) {
            $content .= "🏷 Tag(s): ";
            foreach ($data['tags'] as $tag) {
                $url = self::URI . '/home/images?tags=' . $tag['name'];
                $content .= "<a href=\"{$url}\">#{$tag['name']}</a> ";
            }
            $content .= "<br/>";
        }

        if (array_key_exists('reference_gallery_sections', $data) && sizeof($data['reference_gallery_sections']) > 0) {
            $content .= "📁 Example folder(s): ";
            foreach ($data['folders'] as $folder) {
                $url = self::URI . '/profile/' . $data['owner_username'] . '/gallery/' . $folder['id'];
                $folder_name = $folder['title'];
                if (!is_null($folder['group'])) {
                    $folder_name = $folder['group']['title'] . '/' . $folder_name;
                }
                $content .= "<a href=\"{$url}\">#{$folder_name}</a> ";
            }
        }

        $content .= "<hr/>";
        if (!is_null($data['thumbnail_detail'])) {
            $content .= "<p>";
            $content .= "<a href=\"{$uri}\"><b>{$data['thumbnail_detail']['title']}</b></a><br/>";
            if ($data['thumbnail_detail']['is_thumbnail_for_video']) {
                $url = self::URI . '/api/galleries/images/' . $data['thumbnail_detail']['id'] . '/?format=json';
                $media_data = $this->getData($url, true, true)
                    or returnServerError("Could not load $url");
                $content .= "<video controls src=\"{$media_data['video']['video']}\" poster=\"{$data['thumbnail_detail']['image_lg']}\"/>";
            } else {
                $content .= "<a href=\"{$uri}\"><img src=\"{$data['thumbnail_detail']['image_lg']}\"></a>";
            }

            $content .= "</p>";
        }

        return [
            'uri' => $uri,
            'title' => "{$data['comm_type']} - {$data['title']}",
            'timestamp' => $data['date_added'],
            'author' =>  $data['owner_username'],
            'content' => $content,
            'categories' => ['commission', $data['comm_type']],
            'uid' => $uri
        ];
    }

    private function getImage($id /* array $metadata = null */) //$metadata disabled due to no essential information available in ./api/feed/ or ./api/galleries/images/ results.
    {
        $uri = self::URI . '/images/' . $id;
        $url = self::URI . '/api/galleries/images/' . $id . '/?format=json';
        try {
            $data = /* $metadata ?? */ $this->getData($url, true, true)
                or returnServerError("Could not load $url");
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                return [
                    'uri' => $uri,
                    'title' => "Deleted Image",
                    'timestamp' => '@0',
                    'author' =>  'deleted',
                    'content' => 'Deleted image',
                    'categories' => ['image', 'deleted'],
                    'uid' => $uri
                ];
            } else {
                returnServerError(var_dump($e));
            }
        }

        $content_str = nl2br($data['description']);
        $content = "<p>{$content_str}</p><br/>"; //TODO: Add link and itaku user mention detection and convert into links.

        if (array_key_exists('tags', $data) && sizeof($data['tags']) > 0) {
            $tag_types = [
                'ARTIST' => '',
                'COPYRIGHT' => '',
                'CHARACTER' => '',
                'SPECIES' => '',
                'GENERAL' => '',
                'META' => ''
            ];
            foreach ($data['tags'] as $tag) {
                $url = self::URI . '/home/images?tags=' . $tag['name'];
                $str = "<a href=\"{$url}\">#{$tag['name']}</a> ";
                $tag_types[$tag['tag_type']] .= $str;
            }

            foreach ($tag_types as $type => $str) {
                if (strlen($str) > 0) {
                    $content .= "🏷 <b>{$type}:</b>: {$str}<br/>";
                }
            }
        }

        if (array_key_exists('sections', $data) && sizeof($data['sections']) > 0) {
            $content .= "📁 In Folder(s): ";
            foreach ($data['sections'] as $folder) {
                $url = self::URI . '/profile/' . $data['owner_username'] . '/gallery/' . $folder['id'];
                $folder_name = $folder['title'];
                if (!is_null($folder['group'])) {
                    $folder_name = $folder['group']['title'] . '/' . $folder_name;
                }
                $content .= "<a href=\"{$url}\">#{$folder_name}</a> ";
            }
        }

        $content .= "<hr/>";

        if (array_key_exists('is_thumbnail_for_video', $data)) {
            $url = self::URI . '/api/galleries/images/' . $data['id'] . '/?format=json';
            $media_data = $this->getData($url, true, true)
                or returnServerError("Could not load $url");
            $content .= "<video controls src=\"{$media_data['video']['video']}\" poster=\"{$data['image_xl']}\"/>";
        } else {
            if (array_key_exists('video', $data) && is_null($data['video'])) {
                $content .= "<a href=\"{$uri}\"><img src=\"{$data['image_xl']}\"></a>";
            } else {
                $content .= "<video controls src=\"{$data['video']['video']}\" poster=\"{$data['image_xl']}\"/>";
            }
        }

        return [
            'uri' => $uri,
            'title' => $data['title'],
            'timestamp' => $data['date_added'],
            'author' =>  $data['owner_username'],
            'content' => $content,
            'categories' => ['image'],
            'uid' => $uri
        ];
    }

    private function getData(string $url, bool $cache = false, bool $getJSON = false, array $httpHeaders = [], array $curlOptions = [])
    {
        $httpHeaders[] = 'Authorization: Token ' . $this->token;
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

    public function getName()
    {
        return self::NAME;
    }

    public function getURI()
    {
        return self::URI . '/user/' . $this->getInput('searchUsername');
    }
}
