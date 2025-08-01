<?php

class BlueskyBridge extends BridgeAbstract
{
    //Initial PR by [RSSBridge contributors](https://github.com/RSS-Bridge/rss-bridge/issues/4058).
    //Modified from [©DIYgod and contributors at RSSHub](https://github.com/DIYgod/RSSHub/tree/master/lib/routes/bsky), MIT License';
    const NAME = 'Bluesky Bridge';
    const URI = 'https://bsky.app';
    const DESCRIPTION = 'Fetches posts from Bluesky';
    const MAINTAINER = 'mruac';
    const PARAMETERS = [
        'Posts from a user' => [
            'user_id' => [
                'name' => 'User Handle or DID',
                'type' => 'text',
                'required' => true,
                'exampleValue' => 'did:plc:z72i7hdynmk6r22z27h6tvur',
                'title' => 'ATProto / Bsky.app handle or DID'
            ],
            'feed_filter' => [
                'name' => 'Feed type',
                'type' => 'list',
                'defaultValue' => 'posts_and_author_threads',
                'values' => [
                    'Authored posts / threads and reposts' => 'posts_and_author_threads',
                    'All posts, replies and reposts' => 'posts_with_replies',
                    'Root posts only' => 'posts_no_replies',
                    'Media only' => 'posts_with_media',
                ]
            ],

            'include_reposts' => [
                'name' => 'Include Reposts?',
                'type' => 'checkbox',
                'defaultValue' => 'checked'
            ],

            'include_reply_context' => [
                'name' => 'Include Reply context?',
                'type' => 'checkbox'
            ],

            'verbose_title' => [
                'name' => 'Use verbose feed item titles?',
                'type' => 'checkbox'
            ]
        ],

        'Posts from User List, Starter Pack or Feed Generator' => [
            'url' => [
                'name' => 'URL',
                'type' => 'text',
                'required' => true,
                'title' => 'URL will be validated.'
            ],
            'feed_filter' => [
                'name' => 'Feed type',
                'type' => 'list',
                'defaultValue' => 'posts_with_replies',
                'values' => [
                    'All posts' => 'posts_with_replies',
                    'Root posts only' => 'posts_no_replies',
                    'Media only' => 'posts_with_media',
                ],
            ],

            'include_reply_context' => [
                'name' => 'Include Reply context?',
                'type' => 'checkbox',
                'defaultValue' => 'checked'
            ],

            'verbose_title' => [
                'name' => 'Use verbose feed item titles?',
                'type' => 'checkbox'
            ]
        ],


    ];

    private $profile;
    private $starterPack;

    public function getName()
    {
        if (isset($this->profile)) {
            $profile = $this->profile;
            if (isset($profile['uri'])) {
                if (str_contains($profile['uri'], 'app.bsky.feed.generator')) {
                    return 'Bluesky Feed - ' . $profile['displayName'];
                } else if (str_contains($profile['uri'], 'app.bsky.graph.list')) {
                    if ($profile['purpose'] === 'app.bsky.graph.defs#curatelist') {
                        return 'Bluesky User list - ' . $profile['name'];
                    } else if ($profile['purpose'] === 'app.bsky.graph.defs#referencelist') {
                        return 'Bluesky Starter Pack - ' . $profile['name'];
                    } else {
                        return 'Unknown Bluesky List';
                    }
                } else if (str_contains($profile['uri'], 'app.bsky.graph.starterpack')) {
                    return 'Bluesky Starter Pack - ' . $profile['name'];
                } else {
                    return 'Unknown Bluesky Feed/List';
                }
            } else {
                if ($profile['handle'] === 'handle.invalid') {
                    return sprintf('Bluesky - %s', $profile['displayName']);
                } else {
                    return sprintf('Bluesky - %s (@%s)', $profile['displayName'], $profile['handle']);
                }
            }
        }
        return parent::getName();
    }

    public function getURI()
    {
        if (isset($this->profile)) {
            $profile = $this->profile;
            if (isset($profile['uri'])) {
                if (str_contains($profile['uri'], 'app.bsky.feed.generator')) {
                    return preg_replace('/^at:\/\/(did:plc:\w+)\/app\.bsky\.feed\.generator\/(\w+)$/', self::URI . '/profile/$1/feed/$2', $profile['uri']);
                } else if (str_contains($profile['uri'], 'app.bsky.graph.list')) {
                    if ($profile['purpose'] === 'app.bsky.graph.defs#referencelist' && isset($this->starterPack)) {
                        return preg_replace('/^at:\/\/(did:plc:\w+)\/app\.bsky\.graph\.starterpack\/(\w+)$/', self::URI . '/starter-pack/$1/$2', $this->starterPack['uri']);
                    } else {
                        return preg_replace('/^at:\/\/(did:plc:\w+)\/app\.bsky\.graph\.list\/(\w+)$/', self::URI . '/profile/$1/lists/$2', $profile['uri']);
                    }
                } else if (str_contains($profile['uri'], 'app.bsky.graph.starterpack')) {
                    return preg_replace('/^at:\/\/(did:plc:\w+)\/app\.bsky\.graph\.starterpack\/(\w+)$/', self::URI . '/starter-pack/$1/$2', $this->starterPack['uri']);
                }
            } else {
                if ($profile['handle'] === 'handle.invalid') {
                    return self::URI . '/profile/' . $profile['did'];
                } else {
                    return self::URI . '/profile/' . $profile['handle'];
                }
            }
        }
        return parent::getURI();
    }

    public function getIcon()
    {
        if (isset($this->profile) && isset($this->profile['avatar'])) {
            return $this->profile['avatar'];
        }
        return parent::getIcon();
    }

    public function getDescription()
    {
        if (isset($this->profile)) {
            $profile = $this->profile;
            if (
                str_contains($profile['uri'], 'app.bsky.graph.list') &&
                $profile['purpose'] === 'app.bsky.graph.defs#referencelist' &&
                isset($this->starterPack)
            ) {
                return $this->starterPack['record']['description'];
            } else {
                return $profile['description'];
            }
        }
        return parent::getDescription();
    }

    public function collectData()
    {
        if (
            $this->queriedContext === 'Posts from User List, Starter Pack or Feed Generator'
        ) {
            $url = $this->getInput('url');
            $filter = $this->getInput('feed_filter') ?: 'posts_with_replies';
            $replyContext = $this->getInput('include_reply_context');

            $feedMatch =        preg_match('/^http(?:s|):\/\/bsky.app\/profile\/(?:(?<domain>[\w\-\.]+\.[\w\-]+)|(?<did>did:plc:\w+))\/feed\/(?<key>[\w\-]+)$/', $url, $feedMatches);
            $listMatch =        preg_match('/^http(?:s|):\/\/bsky.app\/profile\/(?:(?<domain>[\w\-\.]+\.[\w\-]+)|(?<did>did:plc:\w+))\/lists\/(?<key>[\w\-]+)$/', $url, $listMatches);
            $starterPackMatch = preg_match('/^http(?:s|):\/\/bsky.app\/starter-pack\/(?:(?<domain>[\w\-\.]+\.[\w\-]+)|(?<did>did:plc:\w+))\/(?<key>[\w\-]+)$/', $url, $starterPackMatches);

            if (!$feedMatch && !$listMatch && !$starterPackMatch) {
                returnClientError(<<<EOD
If it is a User list, it must match this format: https://bsky.app/profile/IDENTIFIER/lists/KEY; 
If it is a generated Feed, it must match this format: https://bsky.app/profile/IDENTIFIER/feed/KEY; 
If it is a starter pack, it must match this format: https://bsky.app/starter-pack/IDENTIFIER/KEY; 
An IDENTIFIER can be an ATProto domain name, or a did:plc identifier.
EOD);
            }

            if ($feedMatch) {
                if (strlen($feedMatches['domain']) > 0) {
                    $did = $this->resolveHandle($feedMatches['domain']);
                } else {
                    $did = $feedMatches['did'];
                }
                $key = $feedMatches['key'];
                $uri = 'at://' . $did . '/app.bsky.feed.generator/' . $key;
                $this->profile = $this->getFeedGeneratorDetails($uri)['view'];
                $feed = $this->getFeedGeneratorFeed($uri);
            } else if ($listMatch) {
                if (strlen($listMatches['domain']) > 0) {
                    $did = $this->resolveHandle($listMatches['domain']);
                } else {
                    $did = $listMatches['did'];
                }
                $key = $listMatches['key'];
                $uri = 'at://' . $did . '/app.bsky.graph.list/' . $key;
                $this->profile = $this->getListDetails($uri)['list'];
                $feed = $this->getListFeed($uri);
            } else if ($starterPackMatch) {
                if (strlen($starterPackMatches['domain']) > 0) {
                    $did = $this->resolveHandle($starterPackMatches['domain']);
                } else {
                    $did = $starterPackMatches['did'];
                }
                $key = $starterPackMatches['key'];
                $uri = 'at://' . $did . '/app.bsky.graph.starterpack/' . $key;
                $starterpackMetadata = $this->getStarterPack($uri);
                $this->starterPack = $starterpackMetadata['starterPack'];
                $listUri = $starterpackMetadata['starterPack']['record']['list'];
                $starterpackMetadata['starterPack']['record']['uri'] = $starterpackMetadata['starterPack']['uri'];
                $this->profile = $starterpackMetadata['starterPack']['record'];
                $feed = $this->getListFeed($listUri);
            }
            foreach ($feed['feed'] as $post) {
                if (!$this->checkPostFilter($post, $filter)) {
                    continue;
                }
                $this->items[] = $this->createFeedItem($post, $replyContext);
            }
        }
        // if ($this->queriedContext === 'Posts from a user') { //fallback to the first context implementation to support those already subscribed.
        else {
            $filter = $this->getInput('feed_filter') ?: 'posts_and_author_threads';
            $includeReposts = $this->getInput('include_reposts');
            $replyContext = $this->getInput('include_reply_context');

            $user_id = $this->getInput('user_id');
            $handle_match = preg_match('/(?:[a-zA-Z]*\.)+([a-zA-Z](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)/', $user_id, $handle_res); //gets the TLD in $handle_match[1]
            $did_match = preg_match('/did:plc:[a-z2-7]{24}/', $user_id); //https://github.com/did-method-plc/did-method-plc#identifier-syntax
            $exclude = ['alt', 'arpa', 'example', 'internal', 'invalid', 'local', 'localhost', 'onion']; //https://en.wikipedia.org/wiki/Top-level_domain#Reserved_domains
            if ($handle_match == true && array_search($handle_res[1], $exclude) == false) {
                //valid bsky handle
                $did = $this->resolveHandle($user_id);
            } else if ($did_match == true) {
                //valid DID
                $did = $user_id;
            } else {
                returnClientError('Invalid ATproto handle or DID provided.');
            }

            $this->profile = $this->getProfile($did);
            $authorFeed = $this->getAuthorFeed($did, $filter);

            foreach ($authorFeed['feed'] as $post) {
                if (!$includeReposts && isset($post['reason']) && str_contains($post['reason']['$type'], 'reasonRepost')) {
                    continue;
                }
                $this->items[] = $this->createFeedItem($post, $replyContext);
            }
        }
    }

    private function createFeedItem($post, $replyContext)
    {
        $postRecord = $post['post']['record'];

        $item = [];
        $item['uri'] = self::URI . '/profile/' . $this->fallbackAuthor($post['post']['author'], 'url') . '/post/' . explode('app.bsky.feed.post/', $post['post']['uri'])[1];
        $item['title'] = $this->getInput('verbose_title') ? $this->generateVerboseTitle($post) : strtok($postRecord['text'], "\n");
        $item['timestamp'] = strtotime($postRecord['createdAt']);
        $item['author'] = $this->fallbackAuthor($post['post']['author'], 'display');

        $postAuthorDID = $post['post']['author']['did'];
        $postAuthorHandle = $post['post']['author']['handle'] !== 'handle.invalid' ? '<i>@' . $post['post']['author']['handle'] . '</i> ' : '';
        $postDisplayName = $post['post']['author']['displayName'] ?? '';
        $postDisplayName = e($postDisplayName);
        $postUri = $item['uri'];

        if (Debug::isEnabled()) {
            $url = explode('/', $post['post']['uri']);
            $this->logger->debug('https://bsky.app/profile/' . $url[2] . '/post/' . $url[4]);
        }

        $description = '';
        $description .= '<p>';
        //post
        $description .= $this->getPostDescription(
            $postDisplayName,
            $postAuthorHandle,
            $postUri,
            $postRecord,
            'post'
        );

        if (isset($postRecord['embed']['$type'])) {
            //post link embed
            if ($postRecord['embed']['$type'] === 'app.bsky.embed.external') {
                $description .= $this->parseExternal($postRecord['embed']['external'], $postAuthorDID);
            } elseif (
                $postRecord['embed']['$type'] === 'app.bsky.embed.recordWithMedia' &&
                $postRecord['embed']['media']['$type'] === 'app.bsky.embed.external'
            ) {
                $description .= $this->parseExternal($postRecord['embed']['media']['external'], $postAuthorDID);
            }

            //post images
            if (
                $postRecord['embed']['$type'] === 'app.bsky.embed.images' ||
                (
                    $postRecord['embed']['$type'] === 'app.bsky.embed.recordWithMedia' &&
                    $postRecord['embed']['media']['$type'] === 'app.bsky.embed.images'
                )
            ) {
                $images = $post['post']['embed']['images'] ?? $post['post']['embed']['media']['images'];
                foreach ($images as $image) {
                    $description .= $this->getPostImageDescription($image);
                }
            }

            //post video
            if (
                $postRecord['embed']['$type'] === 'app.bsky.embed.video' ||
                (
                    $postRecord['embed']['$type'] === 'app.bsky.embed.recordWithMedia' &&
                    $postRecord['embed']['media']['$type'] === 'app.bsky.embed.video'
                )
            ) {
                $description .= $this->getPostVideoDescription(
                    $postRecord['embed']['video'] ?? $postRecord['embed']['media']['video'],
                    $postAuthorDID
                );
            }
        }
        $description .= '</p>';

        //quote post
        if (
            isset($postRecord['embed']) &&
            (
                $postRecord['embed']['$type'] === 'app.bsky.embed.record' ||
                $postRecord['embed']['$type'] === 'app.bsky.embed.recordWithMedia'
            ) &&
            isset($post['post']['embed']['record'])
        ) {
            $description .= '<p>';
            $quotedRecord = $post['post']['embed']['record']['record'] ?? $post['post']['embed']['record'];

            if (isset($quotedRecord['notFound']) && $quotedRecord['notFound']) { //deleted post
                $description .= 'Quoted post deleted.';
            } elseif (isset($quotedRecord['detached']) && $quotedRecord['detached']) { //detached quote
                $uri_explode = explode('/', $quotedRecord['uri']);
                $uri_reconstructed = self::URI . '/profile/' . $uri_explode[2] . '/post/' . $uri_explode[4];
                $description .= '<a href="' . $uri_reconstructed . '">Quoted post detached.</a>';
            } elseif (isset($quotedRecord['blocked']) && $quotedRecord['blocked']) { //blocked by quote author
                $description .= 'Author of quoted post has blocked OP.';
            } elseif (
                ($quotedRecord['$type'] ?? '') === 'app.bsky.feed.defs#generatorView' ||
                ($quotedRecord['$type'] ?? '') === 'app.bsky.graph.defs#listView'
            ) {
                $description .= $this->getListFeedDescription($quotedRecord);
            } elseif (
                ($quotedRecord['$type'] ?? '') === 'app.bsky.graph.starterpack' ||
                ($quotedRecord['$type'] ?? '') === 'app.bsky.graph.defs#starterPackViewBasic'
            ) {
                $description .= $this->getStarterPackDescription($post['post']['embed']['record']);
            } else {
                $quotedAuthorDid = $quotedRecord['author']['did'];
                $quotedDisplayName = $quotedRecord['author']['displayName'] ?? '';
                $quotedDisplayName = e($quotedDisplayName);
                $quotedAuthorHandle = $quotedRecord['author']['handle'] !== 'handle.invalid' ? '<i>@' . $quotedRecord['author']['handle'] . '</i>' : '';

                $parts = explode('/', $quotedRecord['uri']);
                $quotedPostId = end($parts);
                $quotedPostUri = self::URI . '/profile/' . $this->fallbackAuthor($quotedRecord['author'], 'url') . '/post/' . $quotedPostId;

                //quoted post - post
                $description .= $this->getPostDescription(
                    $quotedDisplayName,
                    $quotedAuthorHandle,
                    $quotedPostUri,
                    $quotedRecord,
                    'quote'
                );

                if (isset($quotedRecord['value']['embed']['$type'])) {
                    //quoted post - post link embed
                    if ($quotedRecord['value']['embed']['$type'] === 'app.bsky.embed.external') {
                        $description .= $this->parseExternal($quotedRecord['value']['embed']['external'], $quotedAuthorDid);
                    }

                    //quoted post - post video
                    if (
                        $quotedRecord['value']['embed']['$type'] === 'app.bsky.embed.video' ||
                        (
                            $quotedRecord['value']['embed']['$type'] === 'app.bsky.embed.recordWithMedia' &&
                            $quotedRecord['value']['embed']['media']['$type'] === 'app.bsky.embed.video'
                        )
                    ) {
                        $description .= $this->getPostVideoDescription(
                            $quotedRecord['value']['embed']['video'] ?? $quotedRecord['value']['embed']['media']['video'],
                            $quotedAuthorDid
                        );
                    }

                    //quoted post - post images
                    if (
                        $quotedRecord['value']['embed']['$type'] === 'app.bsky.embed.images' ||
                        (
                            $quotedRecord['value']['embed']['$type'] === 'app.bsky.embed.recordWithMedia' &&
                            $quotedRecord['value']['embed']['media']['$type'] === 'app.bsky.embed.images'
                        )
                    ) {
                        foreach ($quotedRecord['embeds'] as $embed) {
                            if (
                                $embed['$type'] === 'app.bsky.embed.images#view' ||
                                ($embed['$type'] === 'app.bsky.embed.recordWithMedia#view' && $embed['media']['$type'] === 'app.bsky.embed.images#view')
                            ) {
                                $images = $embed['images'] ?? $embed['media']['images'];
                                foreach ($images as $image) {
                                    $description .= $this->getPostImageDescription($image);
                                }
                            }
                        }
                    }
                }
            }
            $description .= '</p>';
        }

            //reply
            if ($replyContext && isset($post['reply']) && isset($post['reply']['parent'])) {
                $replyPost = $post['reply']['parent'];
                $description .= '<hr/>';
                $description .= '<p>';

                if (isset($replyPost['notFound']) && $replyPost['notFound']) { //deleted post
                    $description .= 'Replied to post was deleted.';
                } elseif (isset($replyPost['blocked']) && $replyPost['blocked']) { //blocked by quote author
                    $description .= 'Author of replied to post has blocked OP.';
                } else {
                    $replyPostRecord = $replyPost['record'];
                    $replyPostAuthorDID = $replyPost['author']['did'];
                    $replyPostAuthorHandle = $replyPost['author']['handle'] !== 'handle.invalid' ? '<i>@' . $replyPost['author']['handle'] . '</i> ' : '';
                    $replyPostDisplayName = $replyPost['author']['displayName'] ?? '';
                    $replyPostDisplayName = e($replyPostDisplayName);
                    $replyPostUri = self::URI . '/profile/' . $this->fallbackAuthor($replyPost['author'], 'url') . '/post/' . explode('app.bsky.feed.post/', $replyPost['uri'])[1];

                    // reply post
                    $description .= $this->getPostDescription(
                        $replyPostDisplayName,
                        $replyPostAuthorHandle,
                        $replyPostUri,
                        $replyPostRecord,
                        'reply'
                    );

                    if (isset($replyPostRecord['embed']['$type'])) {
                        //post link embed
                        if ($replyPostRecord['embed']['$type'] === 'app.bsky.embed.external') {
                            $description .= $this->parseExternal($replyPostRecord['embed']['external'], $replyPostAuthorDID);
                        } elseif (
                            $replyPostRecord['embed']['$type'] === 'app.bsky.embed.recordWithMedia' &&
                            $replyPostRecord['embed']['media']['$type'] === 'app.bsky.embed.external'
                        ) {
                            $description .= $this->parseExternal($replyPostRecord['embed']['media']['external'], $replyPostAuthorDID);
                        }

                        //post images
                        if (
                            $replyPostRecord['embed']['$type'] === 'app.bsky.embed.images' ||
                            (
                                $replyPostRecord['embed']['$type'] === 'app.bsky.embed.recordWithMedia' &&
                                $replyPostRecord['embed']['media']['$type'] === 'app.bsky.embed.images'
                            )
                        ) {
                            $images = $replyPost['embed']['images'] ?? $replyPost['embed']['media']['images'];
                            foreach ($images as $image) {
                                $description .= $this->getPostImageDescription($image);
                            }
                        }

                        //post video
                        if (
                            $replyPostRecord['embed']['$type'] === 'app.bsky.embed.video' ||
                            (
                                $replyPostRecord['embed']['$type'] === 'app.bsky.embed.recordWithMedia' &&
                                $replyPostRecord['embed']['media']['$type'] === 'app.bsky.embed.video'
                            )
                        ) {
                            $description .= $this->getPostVideoDescription(
                                $replyPostRecord['embed']['video'] ?? $replyPostRecord['embed']['media']['video'],
                                $replyPostAuthorDID
                            );
                        }
                    }
                    $description .= '</p>';

                    //quote post
                    if (
                        isset($replyPostRecord['embed']) &&
                        ($replyPostRecord['embed']['$type'] === 'app.bsky.embed.record' || $replyPostRecord['embed']['$type'] === 'app.bsky.embed.recordWithMedia') &&
                        isset($replyPost['embed']['record'])
                    ) {
                        $description .= '<p>';
                        $replyQuotedRecord = $replyPost['embed']['record']['record'] ?? $replyPost['embed']['record'];

                        if (isset($replyQuotedRecord['notFound']) && $replyQuotedRecord['notFound']) { //deleted post
                            $description .= 'Quoted post deleted.';
                        } elseif (isset($replyQuotedRecord['detached']) && $replyQuotedRecord['detached']) { //detached quote
                            $uri_explode = explode('/', $replyQuotedRecord['uri']);
                            $uri_reconstructed = self::URI . '/profile/' . $uri_explode[2] . '/post/' . $uri_explode[4];
                            $description .= '<a href="' . $uri_reconstructed . '">Quoted post detached.</a>';
                        } elseif (isset($replyQuotedRecord['blocked']) && $replyQuotedRecord['blocked']) { //blocked by quote author
                            $description .= 'Author of quoted post has blocked OP.';
                        } elseif (
                            ($replyQuotedRecord['$type'] ?? '') === 'app.bsky.feed.defs#generatorView' ||
                            ($replyQuotedRecord['$type'] ?? '') === 'app.bsky.graph.defs#listView'
                        ) {
                            $description .= $this->getListFeedDescription($replyQuotedRecord);
                        } elseif (
                            ($replyQuotedRecord['$type'] ?? '') === 'app.bsky.graph.starterpack' ||
                            ($replyQuotedRecord['$type'] ?? '') === 'app.bsky.graph.defs#starterPackViewBasic'
                        ) {
                            $description .= $this->getStarterPackDescription($replyPost['embed']['record']);
                        } else {
                            $quotedAuthorDid = $replyQuotedRecord['author']['did'];
                            $quotedDisplayName = $replyQuotedRecord['author']['displayName'] ?? '';
                            $quotedDisplayName = e($quotedDisplayName);
                            $quotedAuthorHandle = $replyQuotedRecord['author']['handle'] !== 'handle.invalid' ? '<i>@' . $replyQuotedRecord['author']['handle'] . '</i>' : '';

                            $parts = explode('/', $replyQuotedRecord['uri']);
                            $quotedPostId = end($parts);
                            $quotedPostUri = self::URI . '/profile/' . $this->fallbackAuthor($replyQuotedRecord['author'], 'url') . '/post/' . $quotedPostId;

                            //quoted post - post
                            $description .= $this->getPostDescription(
                                $quotedDisplayName,
                                $quotedAuthorHandle,
                                $quotedPostUri,
                                $replyQuotedRecord,
                                'quote'
                            );

                            if (isset($replyQuotedRecord['value']['embed']['$type'])) {
                                //quoted post - post link embed
                                if ($replyQuotedRecord['value']['embed']['$type'] === 'app.bsky.embed.external') {
                                    $description .= $this->parseExternal($replyQuotedRecord['value']['embed']['external'], $quotedAuthorDid);
                                }

                                //quoted post - post video
                                if (
                                    $replyQuotedRecord['value']['embed']['$type'] === 'app.bsky.embed.video' ||
                                    (
                                        $replyQuotedRecord['value']['embed']['$type'] === 'app.bsky.embed.recordWithMedia' &&
                                        $replyQuotedRecord['value']['embed']['media']['$type'] === 'app.bsky.embed.video'
                                    )
                                ) {
                                    $description .= $this->getPostVideoDescription(
                                        $replyQuotedRecord['value']['embed']['video'] ?? $replyQuotedRecord['value']['embed']['media']['video'],
                                        $quotedAuthorDid
                                    );
                                }

                                //quoted post - post images
                                if (
                                    $replyQuotedRecord['value']['embed']['$type'] === 'app.bsky.embed.images' ||
                                    (
                                        $replyQuotedRecord['value']['embed']['$type'] === 'app.bsky.embed.recordWithMedia' &&
                                        $replyQuotedRecord['value']['embed']['media']['$type'] === 'app.bsky.embed.images'
                                    )
                                ) {
                                    foreach ($replyQuotedRecord['embeds'] as $embed) {
                                        if (
                                            $embed['$type'] === 'app.bsky.embed.images#view' ||
                                            ($embed['$type'] === 'app.bsky.embed.recordWithMedia#view' && $embed['media']['$type'] === 'app.bsky.embed.images#view')
                                        ) {
                                            $images = $embed['images'] ?? $embed['media']['images'];
                                            foreach ($images as $image) {
                                                $description .= $this->getPostImageDescription($image);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $description .= '</p>';
                    }
                }
            }

        $item['content'] = $description;
        return $item;
    }

    private function getPostVideoDescription(array $video, $authorDID)
    {
        //https://video.bsky.app/watch/$did/$cid/thumbnail.jpg
        $videoCID = $video['ref']['$link'];
        $videoMime = $video['mimeType'];
        $thumbnail = "poster=\"https://video.bsky.app/watch/$authorDID/$videoCID/thumbnail.jpg\"" ?? '';
        $videoURL = "https://bsky.social/xrpc/com.atproto.sync.getBlob?did=$authorDID&cid=$videoCID";
        return "<figure><video loop $thumbnail preload=\"none\" controls src=\"$videoURL\" type=\"$videoMime\"/></figure>";
    }

    private function getPostImageDescription(array $image)
    {
        $thumbnailUrl = $image['thumb'];
        $fullsizeUrl = $image['fullsize'];
        $alt = strlen($image['alt']) > 0 ? '<figcaption>' . e($image['alt']) . '</figcaption>' : '';
        return "<figure><a href=\"$fullsizeUrl\"><img src=\"$thumbnailUrl\"></a>$alt</figure>";
    }

    private function getPostDescription(
        string $postDisplayName,
        string $postAuthorHandle,
        string $postUri,
        array $postRecord,
        string $type
    ) {
        $description = '';
        if ($type === 'quote') {
            // Quoted post/reply from bbb @bbb.com:
            $postType = isset($postRecord['reply']) ? 'reply' : 'post';
            $description .= "<a href=\"$postUri\">Quoted $postType</a> from <b>$postDisplayName</b> $postAuthorHandle:<br>";
        } elseif ($type === 'reply') {
            // Replying to aaa @aaa.com's post/reply:
            $postType = isset($postRecord['reply']) ? 'reply' : 'post';
            $description .= "Replying to <b>$postDisplayName</b> $postAuthorHandle's <a href=\"$postUri\">$postType</a>:<br>";
        } else {
            // aaa @aaa.com posted:
            $description .= "<b>$postDisplayName</b> $postAuthorHandle <a href=\"$postUri\">posted</a>:<br>";
        }
        $description .= $this->textToDescription($postRecord);
        return $description;
    }

    private function parseExternal($external, $did)
    {
        $description = '';
        $externalUri = $external['uri'];
        $externalTitle = e($external['title']);
        $externalDescription = e($external['description']);
        $thumb = $external['thumb'] ?? null;

        if (preg_match('/http(|s):\/\/media\.tenor\.com/', $externalUri)) {
            //tenor gif embed
            $tenorInterstitial = str_replace('media.tenor.com', 'media1.tenor.com/m', $externalUri);
            $description .= "<figure><a href=\"$tenorInterstitial\"><img src=\"$externalUri\"/></a><figcaption>$externalTitle</figcaption></figure>";
        } else {
            //link embed preview
            $host = parse_url($externalUri)['host'];
            $thumbDesc = $thumb ? ('<img src="https://cdn.bsky.app/img/feed_thumbnail/plain/' . $did . '/' . $thumb['ref']['$link'] . '@jpeg"/>') : '';
            $externalDescription = strlen($externalDescription) > 0 ? "<figcaption>($host) $externalDescription</figcaption>" : '';
            $description .= '<br><blockquote><b><a href="' . $externalUri . '">' . $externalTitle . '</a></b>';
            $description .= '<figure>' . $thumbDesc . $externalDescription . '</figure></blockquote>';
        }
        return $description;
    }

    private function textToDescription($record)
    {
        if (isset($record['value'])) {
            $record = $record['value'];
        }
        $text = $record['text'];
        $text_copy = $text;
        $text = nl2br(e($text));
        if (isset($record['facets'])) {
            $facets = $record['facets'];
            foreach ($facets as $facet) {
                if ($facet['features'][0]['$type'] === 'app.bsky.richtext.facet#link') {
                    $substring = substr($text_copy, $facet['index']['byteStart'], $facet['index']['byteEnd'] - $facet['index']['byteStart']);
                    $text = str_replace($substring, '<a href="' . $facet['features'][0]['uri'] . '">' . $substring . '</a>', $text);
                }
            }
        }
        return $text;
    }

    //used if handle verification fails, fallsback to displayName or DID depending on context.
    private function fallbackAuthor($author, $reason)
    {
        if ($author['handle'] === 'handle.invalid') {
            switch ($reason) {
                case 'url':
                    return $author['did'];
                case 'display':
                    $displayName = $author['displayName'] ?? '';
                    return e($displayName);
            }
        }
        return $author['handle'];
    }

    private function generateVerboseTitle($post)
    {
        //use "Post by A, replying to B, quoting C" instead of post contents
        $title = '';
        if (isset($post['reason']) && str_contains($post['reason']['$type'], 'reasonRepost')) {
            $title .= 'Repost by ' . $this->fallbackAuthor($post['reason']['by'], 'display') . ', post by ' . $this->fallbackAuthor($post['post']['author'], 'display');
        } else {
            $title .= 'Post by ' . $this->fallbackAuthor($post['post']['author'], 'display');
        }

        if (isset($post['reply'])) {
            if (isset($post['reply']['parent']['blocked'])) {
                $replyAuthor = 'blocked user';
            } elseif (isset($post['reply']['parent']['notFound'])) {
                $replyAuthor = 'deleted post';
            } else {
                $replyAuthor = $this->fallbackAuthor($post['reply']['parent']['author'], 'display');
            }
            $title .= ', replying to ' . $replyAuthor;
        }

        if (
            isset($post['post']['embed']) &&
            isset($post['post']['embed']['record']) &&
            //if not starter pack, feed or list
            ($post['post']['embed']['record']['$type'] ?? '') !== 'app.bsky.feed.defs#generatorView' &&
            ($post['post']['embed']['record']['$type'] ?? '') !== 'app.bsky.graph.defs#listView' &&
            ($post['post']['embed']['record']['$type'] ?? '') !== 'app.bsky.graph.defs#starterPackViewBasic'
        ) {
            if (isset($post['post']['embed']['record']['blocked'])) {
                $quotedAuthor = 'blocked user';
            } elseif (isset($post['post']['embed']['record']['notFound'])) {
                $quotedAuthor = 'deleted psost';
            } elseif (isset($post['post']['embed']['record']['detached'])) {
                $quotedAuthor = 'detached post';
            } else {
                $quotedAuthor = $this->fallbackAuthor($post['post']['embed']['record']['record']['author'] ?? $post['post']['embed']['record']['author'], 'display');
            }
            $title .= ', quoting ' . $quotedAuthor;
        }
        return $title;
    }

    private function resolveHandle($handle)
    {
        $uri = 'https://public.api.bsky.app/xrpc/com.atproto.identity.resolveHandle?handle=' . urlencode($handle);
        $response = json_decode(getContents($uri), true);
        return $response['did'];
    }

    private function getProfile($did)
    {
        $uri = 'https://public.api.bsky.app/xrpc/app.bsky.actor.getProfile?actor=' . urlencode($did);
        $response = json_decode(getContents($uri), true);
        return $response;
    }

    private function getAuthorFeed($did, $filter)
    {
        $url = 'https://public.api.bsky.app/xrpc/app.bsky.feed.getAuthorFeed?actor=' . urlencode($did) . '&filter=' . urlencode($filter) . '&limit=30';
        if (Debug::isEnabled()) {
            $this->logger->debug($url);
        }
        $response = json_decode(getContents($url), true);
        return $response;
    }

    private function getStarterPack($uri)
    {
        $url = 'https://public.api.bsky.app/xrpc/app.bsky.graph.getStarterPack?starterPack=' . urlencode($uri);
        if (Debug::isEnabled()) {
            $this->logger->debug($url);
        }
        $response = json_decode(getContents($url), true);
        return $response;
    }

    private function getListFeed($uri)
    {
        $url = 'https://public.api.bsky.app/xrpc/app.bsky.feed.getListFeed?list=' . urlencode($uri) . '&limit=100';
        if (Debug::isEnabled()) {
            $this->logger->debug($url);
        }
        $response = json_decode(getContents($url), true);
        return $response;
    }

    private function getListDetails($uri)
    {
        $url = 'https://public.api.bsky.app/xrpc/app.bsky.graph.getList?list=' . urlencode($uri) . '&limit=1';
        if (Debug::isEnabled()) {
            $this->logger->debug($url);
        }
        $response = json_decode(getContents($url), true);
        return $response;
    }

    private function getFeedGeneratorFeed($uri)
    {
        $url = 'https://public.api.bsky.app/xrpc/app.bsky.feed.getFeed?feed=' . urlencode($uri) . '&limit=100';
        if (Debug::isEnabled()) {
            $this->logger->debug($url);
        }
        $response = json_decode(getContents($url), true);
        return $response;
    }

    private function getFeedGeneratorDetails($uri)
    {
        $url = 'https://public.api.bsky.app/xrpc/app.bsky.feed.getFeedGenerator?feed=' . urlencode($uri);
        if (Debug::isEnabled()) {
            $this->logger->debug($url);
        }
        $response = json_decode(getContents($url), true);
        return $response;
    }

    //Manual filter check for posts from feed generators and lists.
    private function checkPostFilter($post, $filter)
    {
        //posts_and_author_threads (Authored posts / threads and reposts) only supported in getAuthorFeed - no need to implement for list and feed generator feeds.
        if ($filter === 'posts_with_replies') { //All posts and reposts
            return true;
        } else if (
            $filter === 'posts_no_replies' && //Root posts only
            (!isset($post['reply']) && !isset($post['reason']))
        ) {
            return true;
        } else if (
            $filter === 'posts_with_media' && //Media only
            (
                (
                    isset($post['post']['record']['embed']['$type']) &&
                    (
                        $post['post']['record']['embed']['$type'] === 'app.bsky.embed.video' ||
                        $post['post']['record']['embed']['$type'] === 'app.bsky.embed.images'
                    )
                ) ||
                (
                    isset($post['post']['record']['embed']['media']['$type']) &&
                    (
                        $post['post']['record']['embed']['media']['$type'] === 'app.bsky.embed.images' ||
                        $post['post']['record']['embed']['media']['$type'] === 'app.bsky.embed.video'
                    )
                )
            )
        ) {
            return true;
        }
        return false;
    }

    //Embed for generated feeds and lists
    private function getListFeedDescription(array $record): string
    {
        $feedViewAvatar = isset($record['avatar']) ? '<img src="' . preg_replace('/\/img\/avatar\//', '/img/avatar_thumbnail/', $record['avatar']) . '">' : '';
        $feedViewName = e($record['displayName'] ?? $record['name']);
        $feedViewDescription = e($record['description'] ?? '');
        $authorDisplayName = e($record['creator']['displayName']);
        $authorHandle = e($record['creator']['handle']);
        $likeCount = isset($record['likeCount']) ? '<br>Liked by ' . e($record['likeCount']) . ' users' : '';
        preg_match('/\/([^\/]+)$/', $record['uri'], $matches);
        if (($record['purpose'] ?? '') === 'app.bsky.graph.defs#modlist') {
            $typeURL = '/lists/';
            $typeDesc = 'moderation list';
        } elseif (($record['purpose'] ?? '') === 'app.bsky.graph.defs#curatelist') {
            $typeURL = '/lists/';
            $typeDesc = 'list';
        } else {
            $typeURL = '/feed/';
            $typeDesc = 'feed';
        }
        $uri = e('https://bsky.app/profile/' . $record['creator']['did'] . $typeURL . $matches[1]);

        return <<<END
<blockquote>
<b><a href="{$uri}">{$feedViewName}</a></b><br/>
Bluesky {$typeDesc} by <b>{$authorDisplayName}</b> <i>@{$authorHandle}</i>
<figure>
{$feedViewAvatar}
<figcaption>{$feedViewDescription}{$likeCount}</figcaption>
</figure>
</blockquote>
END;
    }

    private function getStarterPackDescription(array $record): string
    {
        if (!isset($record['record'])) {
            return 'Failed to get starter pack information.';
        }
        $starterpackRecord = $record['record'];
        $starterpackName = e($starterpackRecord['name']);
        $starterpackDescription = e($starterpackRecord['description']);
        $creatorDisplayName = e($record['creator']['displayName']);
        $creatorHandle = e($record['creator']['handle']);
        preg_match('/\/([^\/]+)$/', $starterpackRecord['list'], $matches);
        $uri = e('https://bsky.app/starter-pack/' . $record['creator']['did'] . '/' . $matches[1]);
        return <<<END
<blockquote>
<b><a href="{$uri}">{$starterpackName}</a></b><br/>
Bluesky starter pack by <b>{$creatorDisplayName}</b> <i>@{$creatorHandle}</i><br/>
{$starterpackDescription}
</blockquote>
END;
    }
}
