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

    public function getURI()
    {
        switch ($this->queriedContext) {
            case 'User page':
                return self::URI . '/' . strtolower($this->getInput('page_name'));
            default:
                return self::URI;
        }
    }


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
           - [X] RSS feeds are unavailable for private or non-public (account-only) users (users who must be following or logged in to view) (privacy)
           - [] No feed of a user's timeline (personal)
           - [x] Asks are not included in a feed item. (Truncated) //TESTME:
           - [X] Previous posts in a repost are not included (truncated) //TESTME:
        */
        $handle = strtolower($this->getInput('page_name'));
        $query_str = rawurlencode('{"options":{"hideAsks":false,"hideReplies":false,"hideShares":false},"page":0,"projectHandle":"' . $handle . '"}');
        $posts = $this->getData(self::URI . "/api/v1/trpc/posts.profilePosts?input={$query_str}", true)
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
                $post_type_title = is_null($post['responseToAskId']) ? 'posted' : 'answered';
                if (sizeof($post['shareTree']) > 0) {
                    $post_type = 'share';
                    $post_type_title = 'shared';
                    foreach ($post['shareTree'] as $share_post) {
                        $content .= $this->parsePost($share_post, true)['content'];
                    }
                }
                $post_type_title .= ($post_type === 'share') ? " from @{$post['shareTree'][(sizeof($post['shareTree']) - 1)]['postingProject']['handle']}" : '';
                $post_data = $this->parsePost($post);
                $content .= $post_data['content'];

                $item['uri'] = $post['singlePostPageUrl'];
                $item['title'] = "@{$post['postingProject']['handle']} {$post_type_title}";
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

    private function parsePost(array $post, bool $isShare = false)
    {

        $cw_string = '';
        $attach_str = '';
        $ask_str = '';
        $post_tags = '';
        $content = '';
        $enclosures = [];

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
                                $alt_text = $block['altText'] ?? '';
                                $attach_str .= "<figure><img src=\"{$block['fileURL']}\"><figcaption>{$alt_text}</figcaption></figure><br>";
                                $enclosures[] = $block['fileURL'];
                                break;
                            case 'audio':
                                $title = $block['title'];
                                $title = strlen($block['artist']) > 0 ? "{$title} by {$block['artist']}" : $title;
                                $alt_text = strlen($title) > 0 ? "<figcaption>{$title}</figcaption>" : '';
                                $attach_str .= "<figure>{$alt_text}<audio controls src=\"{$block['fileURL']}\"><a href=\"{$block['fileURL']}\">";
                                $attach_str .= "Download audio: {$title} </a></audio></figure><br>";
                                $enclosures[] = $block['fileURL'];
                                break;
                        }
                        break;
                    case 'ask':
                        $block = $block[$block['type']];
                        $ask_user = $block['anon'] ? 'Anonymous' : "<i>@{$block['askingProject']['handle']}</i>";
                        $ask_str = "<figure><figcaption><b>{$ask_user} asked:</b></figcaption><blockquote>{$block['content']}</blockquote></figure>";
                }
            }
        }

        //tags
        foreach ($post['tags'] as $tag) {
            $post_tags .= '<a href="' . self::URI . "/{$post['postingProject']['handle']}/tagged/{$tag}\">#{$tag}</a> ";
        }
        if (strlen($post_tags) > 0) {
            $post_tags = "<p>🏷 Tag(s): {$post_tags}</p>";
        }

        //assemble: page name, CWs, title, attachments, body, tags, repost divider.
        $post_type = is_null($post['responseToAskId']) ? 'posted' : 'answered';
        $post_header = "<p><b>{$post['postingProject']['displayName']}</b> <i>@{$post['postingProject']['handle']}</i> {$post_type}:</p>";
        $post_title = strlen($post['headline']) > 0 ? "<h1><b>{$post['headline']}</b></h1>" : '';

        $plainTextBody = $post['plainTextBody'];
        if (strlen($plainTextBody) > 0) {
            //remove whitespace before html tags to prevent them being parsed as markdown code blocks.
            //poster should use fenced codeblocks to indicate markdown code instead of prepending with whitespace
            //https://cohost.org/a2aaron/post/77004-announcing-cohoard
            //$plainTextBody = preg_replace('/^\s+(<\/?\w+)/m', '$1', $plainTextBody);

            //posts can use a mixture of markdown and html
            //https://cohost.org/ohnoproblems/post/2660150-touhou-grotto
            $plainTextBody = markdownToHtml($plainTextBody, [
                'breaksEnabled' => true,
                'markupEscaped' => false,
                'urlsLinked' => true
            ]);
            $plainTextBody = $this->extractAltText(str_get_html($plainTextBody));
        }
        $post_contents = $ask_str . $plainTextBody;
        $repost_divider = $isShare ? '<hr><hr>' : '';
        $body = $cw_string . $post_title . $attach_str . $post_contents . $post_tags;

        //TODO: Be more verbose for each case. Currently found: "log-in-first"
        if (strlen($body) === 0 && $post['limitedVisibilityReason'] !== 'none') {
            $body = '🛈 This post is visible only to users who are logged in.';
        }
        $content = $post_header . $body . $repost_divider;

        //remove $content if repost has no content. This prevents showing the long share tree in feed item.
        if (strlen($body) === 0) {
            $content = '';
        }
        return ['content' => $content, 'enclosures' => $enclosures];
    }

    private function extractAltText(simple_html_dom $html)
    {
        $imgs = $html->find('img');
        foreach ($imgs as $img) {
            $src = $img->getAttribute('src') ?? '';
            $altText = $img->getAttribute('alt') ?? '';
            $img->outertext = "<figure><img src=\"{$src}\"><figcaption>{$altText}</figcaption></figure>";
        }

        return $html;
    }


    private function checkCookie(array $headers)
    {
        if (array_key_exists('set-cookie', $headers)) {
            foreach ($headers['set-cookie'] as $value) {
                if (str_starts_with($value, 'connect.sid=')) {
                    parse_str(strtr($value, ['&' => '%26', '+' => '%2B', ';' => '&']), $cookie);
                    if ($cookie['connect_sid'] != $this->getCookie()) {
                        $this->saveCacheValue('cookie', $cookie['connect_sid']);
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
            $value = $this->getOption('cookie');
            $this->saveCacheValue('cookie', $this->getOption('cookie'));
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
