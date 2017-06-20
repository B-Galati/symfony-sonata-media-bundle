<?php

namespace MediaMonks\SonataMediaBundle\Tests\Unit\Provider;

use MediaMonks\SonataMediaBundle\Client\HttpClientInterface;
use MediaMonks\SonataMediaBundle\Exception\InvalidProviderUrlException;
use MediaMonks\SonataMediaBundle\Provider\AbstractProvider;
use MediaMonks\SonataMediaBundle\Provider\YouTubeProvider;
use Mockery as m;

class YoutubeProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testParseProviderReference()
    {
        $youtube = new YouTubeProvider();
        $this->assertEquals('uN2nqdRLhQ8', $youtube->parseProviderReference('https://www.youtube.com/watch?v=uN2nqdRLhQ8'));
        $this->assertEquals('uN2nqdRLhQ8', $youtube->parseProviderReference('https://www.youtube.com/watch?v=uN2nqdRLhQ8&foo=bar'));
        $this->assertEquals('uN2nqdRLhQ8', $youtube->parseProviderReference('https://youtu.be/uN2nqdRLhQ8'));
        $this->assertEquals('uN2nqdRLhQ8', $youtube->parseProviderReference('https://youtu.be/uN2nqdRLhQ8?foo=bar'));
        $this->assertEquals('uN2nqdRLhQ8', $youtube->parseProviderReference('uN2nqdRLhQ8'));
    }

    public function testParseInvalidProviderReference()
    {
        $this->setExpectedException(InvalidProviderUrlException::class);
        $youtube = new YouTubeProvider();
        $youtube->parseProviderReference('https://www.youtube.com/watch?foo=bar');
    }

    public function testParseInvalidProviderReference2()
    {
        $this->setExpectedException(InvalidProviderUrlException::class);
        $youtube = new YouTubeProvider();
        $youtube->parseProviderReference('https://www.youtube.com');
    }

    public function testParseInvalidProviderReferenceShort()
    {
        $this->setExpectedException(InvalidProviderUrlException::class);
        $youtube = new YouTubeProvider();
        $youtube->parseProviderReference('https://youtu.be');
    }

    public function testSupports()
    {
        $provider = new YouTubeProvider();
        $this->assertFalse($provider->supports(AbstractProvider::SUPPORT_DOWNLOAD));
        $this->assertTrue($provider->supports(AbstractProvider::SUPPORT_EMBED));
        $this->assertTrue($provider->supports(AbstractProvider::SUPPORT_IMAGE));
        $this->assertFalse($provider->supports('foo'));
    }

    public function testGetImageUrlMaxRes()
    {
        $httpClient = m::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('exists')->andReturn(true);

        $provider = new YouTubeProvider();
        $provider->setHttpClient($httpClient);
        $this->assertEquals('https://i.ytimg.com/vi/id/maxresdefault.jpg', $provider->getImageUrl('id'));
    }

    public function testGetImageUrlLowRes()
    {
        $httpClient = m::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('exists')->andReturn(false);

        $provider = new YouTubeProvider();
        $provider->setHttpClient($httpClient);
        $this->assertEquals('https://i.ytimg.com/vi/id/hqdefault.jpg', $provider->getImageUrl('id'));
    }
}
