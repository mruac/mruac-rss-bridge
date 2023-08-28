<?php

class CohostBridge extends BridgeAbstract
{
    const NAME = 'Cohost Bridge';
    const URI = 'https://cohost.org';
    const DESCRIPTION = 'User pages from Cohost.org';
    const MAINTAINER = 'mruac';
    const CACHE_TIMEOUT = 1800;
    //30 mins
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
           - [] RSS feeds are unavailable for private users (users who must be logged in to view) (privacy)
           - [] No feed of a user's timeline (personal)
           - [x] Asks are not included in a feed item. (Truncated) //TESTME:
           - [X] Previous posts in a repost are not included (truncated) //TESTME:
        */
        $data = getSimpleHTMLDOMCached(self::URI . "/{$this->getInput('page_name')}");
        $data = $data->find('#trpc-dehydrated-state', 0);
        $data = $data ? json_decode($data->innertext, true) : null;
        $data = $data ? array_reduce($data['queries'], function ($acc, $v) {
            if (str_contains($v['queryHash'], '["posts","profilePosts"]')) {
                return $acc[] = $v;
            }
        }, []) : null;
        $posts = sizeof($data) > 0 ? $data['state']['data']['posts'] : null;
        if ($posts !== null) {
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
            $post_tags .= "<a href=\"{self::URI}/{$post['postingProject']['handle']}/tagged/{$tag}\">$tag</a> ";
        }
        if (strlen($post_tags) > 0) {
            $post_tags = "<p>🏷 Tag(s): {$post_tags}</p>";
        }

        //assemble: page name, CWs, title, attachments, body.
        $post_title = strlen($post['headline']) > 0 ? "<h1>{$post['headline']}</h1>" : '';
        $post_contents = strlen($post['plainTextBody']) > 0 ? "{$ask_str}<p>{$post['plainTextBody']}</p>" : '';
        $post_type = is_null($post['responseToAskId']) ? 'posted' : 'answered';
        $post_header = "<p><b>{$post['postingProject']['displayName']}</b> @{$post['postingProject']['handle']}> {$post_type}:</p>";
        $content = $post_header . $cw_string . $post_title . $attach_str . $post_contents . $post_tags;
        return ['content' => $content, 'enclosures' => $enclosures];
    }
}
