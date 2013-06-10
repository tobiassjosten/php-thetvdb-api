<?php

/*
 * This file is part of the TheTVDB.
 *
 * (c) 2010-2012 Fabien Pennequin <fabien@pennequin.me>
 * (c) 2012 Tobias Sjösten <tobias.sjosten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FPN\TheTVDB\Test;

use FPN\TheTVDB\Api;
use FPN\TheTVDB\Model\TvShow;
use FPN\TheTVDB\Model\Episode;
use FPN\TheTVDB\Model\Banner;
use FPN\TheTVDB\HttpClient\HttpClientInterface;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    private $httpClient;
    private $api;

    public function setUp()
    {
        $this->httpClient = new MockHttpClient();
        $this->api = new Api($this->httpClient, '123', 'http://www.test.com/');
    }

    public function testConstructor()
    {
        $api = new Api($this->httpClient, uniqid());
        $this->assertInstanceOf('FPN\TheTVDB\Api', $api);

        $key = uniqid();
        $api = new Api($this->httpClient, $key, 'http://www.test.com/');
        $this->assertInstanceOf('FPN\TheTVDB\Api', $api);
        $this->assertEquals('http://www.test.com/', $api->getMirrorUrl());
        $this->assertEquals('http://www.test.com/api/', $api->getBaseUrl());
        $this->assertEquals('http://www.test.com/api/'.$key.'/', $api->getBaseUrlWithKey());
    }

    public function testSearchTvShow()
    {
        $this->httpClient->mockRequestBody('searchTvShow');

        $tvshow1 = new TvShow();
        $tvshow1->fromArray(array(
            'id'            => 72218,
            'name'          => 'Smallville',
            'bannerUrl'     => $this->api->getMirrorUrl().'banners/graphical/72218-g22.jpg',
            'overview'      => 'Smallville is an american tv serie.',
            'firstAired'    => new \DateTime('2001-10-16'),
            'language'      => 'en',
            'theTvDbId'     => 72218,
            'imdbId'        => 'tt0279600',
            'zap2itId'      => 'SH462144',
        ));
        $this->assertEquals(array($tvshow1), $this->api->searchTvShow('Smallville'));
        $this->assertEquals('http://www.test.com/api/GetSeries.php?seriesname=Smallville', $this->httpClient->requestUrl);

        $this->httpClient->mockRequestBody('searchTvShow', 2);
        $tvshow2 = new TvShow();
        $tvshow2->fromArray(array(
            'id'            => 71394,
            'language'      => 'en',
            'name'          => 'The Cape',
            'bannerUrl'     => $this->api->getMirrorUrl().'banners/graphical/71394-g.jpg',
            'firstAired'    => new \DateTime('1996-09-01'),
            'theTvDbId'     => 71394,
            'zap2itId'      => 'SH189638',
        ));
        $tvshow3 = new TvShow();
        $tvshow3->fromArray(array(
            'id'            => 160671,
            'name'          => 'The Cape (2011)',
            'bannerUrl'     => $this->api->getMirrorUrl().'banners/graphical/160671-g.jpg',
            'overview'      => 'The Cape follows an innocent cop who has been framed for a crime he did not commit...',
            'firstAired'    => new \DateTime('2011-01-09'),
            'language'      => 'en',
            'theTvDbId'     => 160671,
            'imdbId'        => 'tt1593823',
            'zap2itId'      => 'SH01279165',
        ));
        $this->assertEquals(array($tvshow2,$tvshow3), $this->api->searchTvShow('The Cape'));
        $this->assertEquals('http://www.test.com/api/GetSeries.php?seriesname='.urlencode('The Cape'), $this->httpClient->requestUrl);
    }

    public function testSearchTvShowFail()
    {
        $this->httpClient->mockRequestBody('notFound');
        $this->assertEquals(array(), $this->api->searchTvShow('Smallville'));
    }

    public function testGetTvShow()
    {
        $this->httpClient->mockRequestBody('getTvShow');

        $tvshow = new TvShow();
        $tvshow->fromArray(array(
            'id'            => 72218,
            'name'          => 'Smallville',
            'firstAired'    => new \DateTime('2001-10-16'),
            'overview'      => 'Smallville revolves around Clark Kent...',
            'network'       => 'The CW',
            'language'      => 'en',
            'genres'        => array('Drama', 'Science-Fiction'),

            'bannerUrl'     => $this->api->getMirrorUrl().'banners/graphical/72218-g22.jpg',
            'fanartUrl'     => $this->api->getMirrorUrl().'banners/fanart/original/72218-82.jpg',
            'posterUrl'     => $this->api->getMirrorUrl().'banners/posters/72218-16.jpg',

            'theTvDbId'     => 72218,
            'imdbId'        => 'tt0279600',
            'zap2itId'      => 'SH462144',
        ));

        $this->assertEquals($tvshow, $this->api->getTvShow(72218));
        $this->assertEquals('http://www.test.com/api/123/series/72218/en.xml', $this->httpClient->requestUrl);
    }

    public function testGetTvShowFail()
    {
        $this->httpClient->mockRequestBody('notFound');
        $this->assertNull($this->api->getTvShow(72218));
    }

    public function testGetEpisode()
    {
        $this->httpClient->mockRequestBody('getEpisode');

        $episode = new Episode();
        $episode->fromArray(array(
            'id'            => 77817,
            'tvshowId'      => 72218,
            'seasonId'      => 3707,
            'episodeNumber' => 1,
            'seasonNumber'  => 1,
            'name'          => 'Pilot',
            'firstAired'    => new \DateTime('2001-10-16'),
            'overview'      => 'The first episode tells the story of the meteor shower that hit Smallville and changed life in the Kansas town forever.',
            'language'      => 'en',
        ));

        $this->assertEquals($episode, $this->api->getEpisode(77817));
        $this->assertEquals('http://www.test.com/api/123/episodes/77817/en.xml', $this->httpClient->requestUrl);
    }

    public function testGetUnairedEpisode()
    {
        $this->httpClient->mockRequestBody('getEpisode', 2);

        $episode = new Episode();
        $episode->fromArray(array(
            'id'            => 77817,
            'tvshowId'      => 72218,
            'seasonId'      => 3707,
            'episodeNumber' => 1,
            'seasonNumber'  => 1,
            'name'          => 'Pilot',
            'firstAired'    => null,
            'overview'      => 'The first episode tells the story of the meteor shower that hit Smallville and changed life in the Kansas town forever.',
            'language'      => 'en',
        ));

        $this->assertEquals($episode, $this->api->getEpisode(77817));
    }

    public function testGetEpisodeFail()
    {
        $this->httpClient->mockRequestBody('notFound');
        $this->assertNull($this->api->getEpisode(77817));
    }

    public function testGetTvShowAndEpisodes()
    {
        $this->httpClient->mockRequestBody('getTvShowAndEpisodes');

        $tvshow = new TvShow();
        $tvshow->fromArray(array(
            'id'            => 72218,
            'name'          => 'Smallville',
            'firstAired'    => new \DateTime('2001-10-16'),
            'overview'      => 'Smallville revolves around Clark Kent...',
            'network'       => 'The CW',
            'language'      => 'en',
            'genres'        => array('Drama', 'Science-Fiction'),

            'bannerUrl'     => $this->api->getMirrorUrl().'banners/graphical/72218-g22.jpg',
            'fanartUrl'     => $this->api->getMirrorUrl().'banners/fanart/original/72218-82.jpg',
            'posterUrl'     => $this->api->getMirrorUrl().'banners/posters/72218-16.jpg',

            'theTvDbId'     => 72218,
            'imdbId'        => 'tt0279600',
            'zap2itId'      => 'SH462144',
        ));

        $episode = new Episode();
        $episode->fromArray(array(
            'id'            => 77817,
            'tvshowId'      => 72218,
            'seasonId'      => 3707,
            'episodeNumber' => 1,
            'seasonNumber'  => 1,
            'name'          => 'Pilot',
            'firstAired'    => new \DateTime('2001-10-16'),
            'overview'      => 'The first episode tells the story of the meteor shower that hit Smallville and changed life in the Kansas town forever...',
            'language'      => 'en',
        ));

        $data = array(
            'tvshow'    => $tvshow,
            'episodes'  => array($episode),
        );
        $this->assertEquals($data, $this->api->getTvShowAndEpisodes(72218));
        $this->assertEquals('http://www.test.com/api/123/series/72218/all/en.xml', $this->httpClient->requestUrl);

        $this->httpClient->mockRequestBody('getTvShowAndEpisodes', 2);
        $data = $this->api->getTvShowAndEpisodes(72218);
        $this->assertInstanceOf('FPN\TheTVDB\Model\TvShow', $data['tvshow']);
        $this->assertEquals(5, sizeof($data['episodes']));
    }

    public function testGetTvShowAndUnairedEpisodes()
    {
        $this->httpClient->mockRequestBody('getTvShowAndEpisodes', 3);

        $tvshow = new TvShow();
        $tvshow->fromArray(array(
            'id'            => 72218,
            'name'          => 'Smallville',
            'firstAired'    => null,
            'overview'      => 'Smallville revolves around Clark Kent...',
            'network'       => 'The CW',
            'language'      => 'en',
            'genres'        => array('Drama', 'Science-Fiction'),

            'bannerUrl'     => $this->api->getMirrorUrl().'banners/graphical/72218-g22.jpg',
            'fanartUrl'     => $this->api->getMirrorUrl().'banners/fanart/original/72218-82.jpg',
            'posterUrl'     => $this->api->getMirrorUrl().'banners/posters/72218-16.jpg',

            'theTvDbId'     => 72218,
            'imdbId'        => 'tt0279600',
            'zap2itId'      => 'SH462144',
        ));

        $episode = new Episode();
        $episode->fromArray(array(
            'id'            => 77817,
            'tvshowId'      => 72218,
            'seasonId'      => 3707,
            'episodeNumber' => 1,
            'seasonNumber'  => 1,
            'name'          => 'Pilot',
            'firstAired'    => null,
            'overview'      => 'The first episode tells the story of the meteor shower that hit Smallville and changed life in the Kansas town forever...',
            'language'      => 'en',
        ));

        $data = array(
            'tvshow'    => $tvshow,
            'episodes'  => array($episode),
        );
        $this->assertEquals($data, $this->api->getTvShowAndEpisodes(72218));
    }

    public function testGetTvShowAndEpisodesFail()
    {
        $this->httpClient->mockRequestBody('notFound');
        $this->assertNull($this->api->getTvShowAndEpisodes(72218));
    }

    public function testGetUpdates()
    {
        $this->httpClient->mockRequestBody('getUpdates');

        // We can't access constants from objects when they are members of
        // another object.
        // @see https://gist.github.com/3892865
        $api = $this->api;

        $tvshow = new TvShow();
        $tvshow->fromArray(array(
            'id'         => 70355,
            'theTvDbId'  => 70355,
            'name'       => '',
            'overview'   => '',
            'firstAired' => null,
        ));

        $episode = new Episode();
        $episode->fromArray(array(
            'id'            => 396057,
            'seasonId'      => 0,
            'tvshowId'      => 0,
            'episodeNumber' => 0,
            'seasonNumber'  => 0,
            'name'          => '',
            'firstAired'    => null,
            'overview'      => '',
            'language'      => '',
        ));

        $banner = new Banner();
        $banner->fromArray(array(
            'id'            => 0,
            'language'      => '',
            'bannerUrl'     => $this->api->getMirrorUrl().'banners/',
            'bannerType'    => '',
            'bannerSize'    => null,
            'thumbnailUrl'  => null,
        ));

        $data = array(
            'tvshows'  => array($tvshow),
            'episodes' => array($episode),
            'banners'  => array($banner),
        );

        $this->assertEquals($data, $api->getUpdates($api::UPDATES_DAY));
        $this->assertEquals($data, $api->getUpdates($api::UPDATES_WEEK));
        $this->assertEquals($data, $api->getUpdates($api::UPDATES_MONTH));
        $this->assertEquals($data, $api->getUpdates($api::UPDATES_ALL));
    }

    public function testGetBanners()
    {
        $this->httpClient->mockRequestBody('getBanners');

        $banner_1 = new Banner();
        $banner_1->fromArray(array(
            'id'            => 490471,
            'bannerUrl'     => $this->api->getMirrorUrl().'banners/fanart/original/72218-82.jpg',
            'bannerType'    => 'fanart',
            'bannerSize'    => '1280x720',
            'language'      => 'en',
            'thumbnailUrl'  => $this->api->getMirrorUrl().'banners/_cache/fanart/original/72218-82.jpg',
        ));

        $banner_2 = new Banner();
        $banner_2->fromArray(array(
            'id'            => 489821,
            'bannerUrl'     => $this->api->getMirrorUrl().'banners/posters/72218-16.jpg',
            'bannerType'    => 'poster',
            'bannerSize'    => '680x1000',
            'language'      => 'en',
        ));

        $banner_3 = new Banner();
        $banner_3->fromArray(array(
            'id'            => 487411,
            'bannerUrl'     => $this->api->getMirrorUrl().'banners/seasons/72218-9-5.jpg',
            'bannerType'    => 'season',
            'language'      =>  'en',
        ));

        $banner_4 = new Banner();
        $banner_4->fromArray(array(
            'id'            => 619931,
            'bannerUrl'     => $this->api->getMirrorUrl().'banners/graphical/72218-g22.jpg',
            'bannerType'    => 'series',
            'language'      => 'en',
        ));

        $this->assertEquals(array($banner_1,$banner_2,$banner_3,$banner_4), $this->api->getBanners(71394));
        $this->assertEquals('http://www.test.com/api/123/series/71394/banners.xml', $this->httpClient->requestUrl);
    }

    public function testGetBannersFail()
    {
        $this->httpClient->mockRequestBody('notFound');
        $this->assertEquals(array(), $this->api->getBanners(71394));
    }
}
