<?php

namespace AppBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::makeClient();

        $client->request('GET', '/search');
        $this->assertStatusCode(302, $client);

        $content = $this->fetchContent('/');
        $this->assertContains(
            'Frühneuhochdeutsches Wörterbuch',
            $content
        );
    }

    /**
     * @dataProvider urlProvider
     */
    public function testUrls($url)
    {
        $client = static::makeClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlProvider()
    {
        return [
            ['/'],
            ['/search?q=imbis'],
            ['/lemma/imbis.s.*'],
            ['/lemma/imbis.s.0m'],
            ['/lemma/imbis.s.0m?q=imbis'],
            ['/lemma/imbis.s.0m?q=imbis&start=1'],
            ['/lemma/imbis.s.0m?start=1'],
            ['/source/source_5'],
        ];
    }
}
