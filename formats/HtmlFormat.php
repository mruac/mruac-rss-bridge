<?php

class HtmlFormat extends FormatAbstract
{
    const MIME_TYPE = 'text/html';

    public function render(?Request $request): string
    {
        // This query string is url encoded
        $queryString = $_SERVER['QUERY_STRING'];

        $feedArray = $this->getFeed();
        $formatFactory = new FormatFactory();
        $formats = [];

        if (false !== stripos($queryString, UrlEncryptionService::PARAMETER_NAME . '=')) {
            $encryptionToken = 'yes';
        } else {
            $encryptionToken = null;
        }

        // Create all formats (except HTML)
        $formatNames = $formatFactory->getFormatNames();
        foreach ($formatNames as $formatName) {
            if ($formatName === 'Html') {
                continue;
            }
            // The format url is relative, but should be absolute in order to help feed readers.
            if (str_contains(strtolower($queryString), 'format=html')) {
                $formatUrl = '?' . str_ireplace('format=Html', 'format=' . $formatName, $queryString);
            } else {
                // If we're viewing the HtmlFormat and the 'format' GET parameter isn't here, this is likely an
                //   encrypted URL being viewed. Handle this by reconstructing the raw URL with the new format.
                $formatUrl = '?' . http_build_query($request->toArray());
                $formatUrl = str_ireplace('format=Html', 'format=' . $formatName, $formatUrl);
            }
            $formatObject = $formatFactory->create($formatName);
            $formats[] = [
                'url'       => $formatUrl,
                'name'      => $formatName,
                'type'      => $formatObject->getMimeType(),
            ];
        }

        $items = [];
        foreach ($this->getItems() as $item) {
            $items[] = [
                'url'           => $item->getURI() ?: $feedArray['uri'],
                'title'         => $item->getTitle() ?? '(no title)',
                'timestamp'     => $item->getTimestamp(),
                'author'        => $item->getAuthor(),
                'content'       => $item->getContent() ?? '',
                'enclosures'    => $item->getEnclosures(),
                'categories'    => $item->getCategories(),
            ];
        }

        $donationUri = null;
        if (Configuration::getConfig('admin', 'donations') && $feedArray['donationUri']) {
            $donationUri = $feedArray['donationUri'];
        }

        $html = render_template(__DIR__ . '/../templates/html-format.html.php', [
            'bridge_name'      => $request->get('bridge'),
            'title'            => $feedArray['name'],
            'formats'          => $formats,
            'uri'              => $feedArray['uri'],
            'items'            => $items,
            'donation_uri'     => $donationUri,
            'encryption_token' => $encryptionToken,
        ]);
        return $html;
    }
}

