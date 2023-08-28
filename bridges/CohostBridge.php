<?php

class CohostBridge extends BridgeAbstract
{
    const NAME = 'Cohost Bridge';
    const URI = 'https://cohost.org';
    const DESCRIPTION = 'User pages from Cohost.org<br>If a user is inaccessible from public view, please set the cookie.';
    const MAINTAINER = 'mruac';
    const CACHE_TIMEOUT = 1800; //30 mins
    const PARAMETERS = [
        'User page' => [
            'page_name' => [
                'name' => 'Username',
                'type' => 'text',
                'title' => 'Username as it appears in the url.',
                'defaultValue' => 'staff',
            ]
        ]
    ];
    const CONFIGURATION = [
        'cookie' => [
            'required' => false
        ]
    ];


    public function getName()
    {
        switch ($this->queriedContext) {
            case 'User page':
                $handle = strtolower($this->getInput('page_name'));
                return "@{$handle}'s Cohost page";
            default:
                return parent::getName();
        }
    }

    public function collectData()
    {
        /*
      Cohost already has RSS feeds for individual users, however it has the following caveats:
           - [] RSS feeds are unavailable for private or non-public (account-only) users (users who must be following or logged in to view) (privacy)
           - [] No feed of a user's timeline (personal)
           - [x] Asks are not included in a feed item. (Truncated) //TESTME:
           - [X] Previous posts in a repost are not included (truncated) //TESTME:
        */
        $handle = strtolower($this->getInput('page_name'));
        $query_str = '{"options":{"hideAsks":false,"hideReplies":false,"hideShares":false},"page":0,"projectHandle":"' . $handle . '"}';
        $posts = $this->getData(self::URI . "/api/v1/trpc/posts.profilePosts?input={rawurlencode($query_str}", true)
            or returnServerError($handle . ' could not be found.');
        $posts = $posts ? $posts['result']['data']['posts'] : null;
        if ($posts) {
            foreach ($posts as $post) {
                //skip pinned post
                if ($post['pinned']) {
                    continue;
                }

                $item = [];
                $content = '';
                $post_type = is_null($post['responseToAskId']) ? 'post' : 'answer';
                if (sizeof($post['shareTree']) > 0) {
                    $post_type = 'repost';
                    foreach ($post['shareTree'] as $share_post) {
                        $content .= $this->parsePost($share_post)['content'];
                        $content .= '<hr><hr>';
                    }
                }
                $post_data = $this->parsePost($post);
                $content .= $post_data['content'];

                $item['uri'] = $post['singlePostPageUrl'];
                $item['title'] = "@{$post['postingProject']['handle']} {$post_type}ed";
                $item['timestamp'] = $post['publishedAt'];
                $item['author'] = $post['postingProject']['handle'];
                $item['content'] = $content;
                $item['enclosures'] = $post_data['enclosures'];
                $item['categories'] = $post['tags'];
                $item['categories'][] = $post_type;
                $item['uid'] = $post['filename'];
                $this->items[] = $item;
            }
        } else {
            returnServerError($handle . '\'s data could not be found. Please check the name and try again later.');
        }
    }

    private function parsePost(array $post)
    {
        $cw_string = '';
        $attach_str = '';
        $ask_str = '';
        $post_tags = '';
        //content warnings
        if ($post['effectiveAdultContent']) {
            $cw_string .= '18+';
        }
        if (sizeof($post['cws']) > 0) {
            if ($post['effectiveAdultContent']) {
                $cw_string .= ', ';
            }
            foreach ($post['cws'] as $index => $warning) {
                $cw_string .= $warning;
                if ($index < sizeof($post['cws']) - 1) {
                    $cw_string .= ', ';
                }
            }
        }
        $cw_string = strlen($cw_string) > 0 ? "<p><em>Content Warning(s): {$cw_string}</em></p>" : '';
        //attachments
        if (sizeof($post['blocks']) > 0) {
            foreach ($post['blocks'] as $block) {
                switch ($block['type']) {
                    case 'attachment':
                        $block = $block[$block['type']];
                        switch ($block['kind']) {
                            case 'image':
                                $alt_text = strlen($block['altText']) > 0 ? "<figcaption>{$block['altText']}</figcaption>" : '';
                                $attach_str .= "<figure><img src=\"{$block['fileURL']}\">{$alt_text}</figure>";
                                $enclosures[] = $block['fileURL'];
                                break;
                            case 'audio':
                                $title = $block['title'];
                                $title = strlen($block['artist']) > 0 ? "{$title} by {$block['artist']}" : $title;
                                $alt_text = strlen($title) > 0 ? "<figcaption>{$title}</figcaption>" : '';
                                $attach_str .= "<figure>{$alt_text}<audio controls src=\"{$block['fileURL']}\"><a href=\"{$block['fileURL']}\">";
                                $attach_str .= "Download audio: {$title} </a></audio></figure>";
                                $enclosures[] = $block['fileURL'];
                                break;
                        }

                        break;
                    case 'ask':
                        $ask_user = $block['anon'] ? 'Anonymous' : "@{$block['askingProject']['handle']}";
                        $ask_str = "<figure><figcaption><b>{$ask_user} asked:</b></figcaption><blockquote>{$block['content']}</blockquote></figure>";
                }
            }
        }

        //tags
        foreach ($post['tags'] as $tag) {
            $post_tags .= '<a href="' . self::URI . "/{$post['postingProject']['handle']}/tagged/{$tag}\">$tag</a> ";
        }
        if (strlen($post_tags) > 0) {
            $post_tags = "<p>🏷 Tag(s): {$post_tags}</p>";
        }

        //assemble: page name, CWs, title, attachments, body, tags.
        $post_title = strlen($post['headline']) > 0 ? "<h1>{$post['headline']}</h1>" : '';
        $post_contents = strlen($post['plainTextBody']) > 0 ? "{$ask_str}<p>{$post['plainTextBody']}</p>" : '';
        $post_type = is_null($post['responseToAskId']) ? 'posted' : 'answered';
        $post_header = "<p><b>{$post['postingProject']['displayName']}</b> @{$post['postingProject']['handle']}> {$post_type}:</p>";
        $content = $post_header . $cw_string . $post_title . $attach_str . $post_contents . $post_tags;
        return ['content' => $content, 'enclosures' => $enclosures];
    }


    private function checkCookie(array $headers)
    {
        if (array_key_exists('set-cookie', $headers)) {
            foreach ($headers['set-cookie'] as $value) {
                if (str_starts_with($value, 'connect.sid=')) {
                    parse_str(strtr($value, ['&' => '%26', '+' => '%2B', ';' => '&']), $cookie);
                    if ($cookie['connect.sid'] != $this->getCookie()) {
                        $this->saveCacheValue('cookie', $cookie['connect.sid']);
                    }
                    break;
                }
            }
        }
    }

    private function getCookie()
    {
        // checks if cookie is set, if not initialise it with the cookie from the config
        $value = $this->loadCacheValue('cookie', 691200 /* 7 days + 1 day to let cookie chance to renew */);
        if (!isset($value)) {
            $value = $this->saveCacheValue('cookie', $this->getOption('cookie'));
        }
        return $value;
    }

    private function getData(string $url, bool $cache = false, bool $getJSON = true, array $httpHeaders = [], array $curlOptions = [])
    {
        $cookie_str = $this->getCookie();
        if ($cookie_str) {
            $curlOptions[CURLOPT_COOKIE] = 'connect.sid=' . $cookie_str;
        }

        if ($cache) {
            $data = $this->loadCacheValue($url, 86400); // 24 hours
            if (!$data) {
                $data = getContents($url, $httpHeaders, $curlOptions, true) or returnServerError("Could not load $url");
                $this->saveCacheValue($url, $data);
            }
        } else {
            $data = getContents($url, $httpHeaders, $curlOptions, true) or returnServerError("Could not load $url");
        }

        $this->checkCookie($data['headers']);

        if ($getJSON) {
            return json_decode($data['content'], true);
        } else {
            return str_get_html($data['content']);
        }
    }
}
