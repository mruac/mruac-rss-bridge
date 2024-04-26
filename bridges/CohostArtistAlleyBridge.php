<?php

class CohostArtistAlleyBridge extends BridgeAbstract
{
    const NAME = 'Cohost\'s Artist Alley Bridge';
    const URI = 'https://cohost.org/rc/artist-alley';
    const DESCRIPTION = 'Timed posts from Cohost\'s Artist Alley page.';
    const MAINTAINER = 'mruac';
    const CACHE_TIMEOUT = 3600; //1hrs, ads are manually reviewed
    const PARAMETERS = [
        [
            'nsfw' => [
                'name' => 'Include NSFW',
                'type' => 'checkbox',
            ]
        ]
    ];
    const CONFIGURATION = [
        'cookie' => [
            'required' => true
        ]
    ];

    public function getName()
    {
        if ($this->getInput('nsfw')) {
            return parent::getName() . ', including 18+ posts';
        }
        return parent::getName();
    }

    public function collectData()
    {
        $nsfw = $this->getInput('nsfw') ? 'include' : 'hide';
        $query_str = rawurlencode('{"0":{"adultDisplayMode":"' . $nsfw . '","categories":[],"categoryMatch":"all","sortOrder":"newest"}}');
        $posts = $this->getData("https://cohost.org/api/v1/trpc/artistAlley.getListingsForDisplay?batch=1&input={$query_str}", true)
            or returnServerError('No Artist Alley posts could be found.');
        $ads = $posts ? $posts[0]['result']['data']['listings'] : null;
        $users = $posts ? $posts[0]['result']['data']['relevantProjects'] : null;
        if ($posts) {
            foreach ($ads as $ad) {
                $item = [];
                $user = $users[$ad['projectId']];
                $nsfw_str = $ad['adultContent'] ? 'a mature' : 'an';
                $content = "<p><b>{$user['displayName']}</b> <a href=\"https://cohost.org/{$user['handle']}\"><i>@{$user['handle']}</i></a> posted {$nsfw_str} ad:<br>";
                $content .= markdownToHtml($ad['body'], [
                    'breaksEnabled' => true,
                    'markupEscaped' => false,
                    'urlsLinked' => true
                ]) . '<br>';
                $content .= '<figure><img src="' . $ad['attachment']['fileURL'] . '"/><figcaption>' . $ad['attachment']['altText'] . '<figcaption/></figure><br>';
                $content .= '<b><em><h2><a href="' . $ad['cta']['link'] . '">' . $ad['cta']['text'] . '</a></h2></em></b>';

                $item['uri'] = $ad['cta']['link'];
                $item['title'] = $ad['cta']['text'];
                $item['timestamp'] = $ad['createdAt'];
                $item['author'] = $users[$ad['projectId']]['handle'];
                $item['content'] = $content;
                $item['enclosures'] = $ad['attachment']['fileURL'];
                $item['categories'] = $ad['categories'];
                $item['uid'] = $ad['id'];
                $this->items[] = $item;
            }
        }
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
            $data = $this->loadCacheValue($url, self::CACHE_TIMEOUT);
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
