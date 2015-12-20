<?php

namespace Http\Adapter\Tests;

use GuzzleHttp\Exception as GuzzleExceptions;
use Http\Adapter\Guzzle5HttpAdapter;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Guzzle5ExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetException()
    {
        $request = $this->getMock('Psr\Http\Message\RequestInterface');
        $guzzleRequest = $this->getMock('GuzzleHttp\Message\RequestInterface');
        $body = $this->getMock('GuzzleHttp\Stream\StreamInterface');
        $body->expects($this->any())->method('detach')->willReturn('message body');
        $guzzleResponse = $this->getMock('GuzzleHttp\Message\ResponseInterface');
        $guzzleResponse->expects($this->any())->method('getStatusCode')->willReturn('400');
        $guzzleResponse->expects($this->any())->method('getHeaders')->willReturn(array());
        $guzzleResponse->expects($this->any())->method('getProtocolVersion')->willReturn('1.1');
        $guzzleResponse->expects($this->any())->method('getBody')->willReturn($body);


        $adapter = new Guzzle5HttpAdapter();
        $method  = new \ReflectionMethod('Http\Adapter\Guzzle5HttpAdapter', 'handleException');
        $method->setAccessible(true);

        $outputException = $method->invoke($adapter, new GuzzleExceptions\ConnectException('foo', $guzzleRequest), $request);
        $this->assertInstanceOf('Http\Client\Exception\NetworkException', $outputException, "Guzzle's ConnectException should be converted to a NetworkException");

        $outputException = $method->invoke($adapter, new GuzzleExceptions\TooManyRedirectsException('foo', $guzzleRequest), $request);
        $this->assertInstanceOf('Http\Client\Exception\RequestException', $outputException, "Guzzle's TooManyRedirectsException should be converted to a RequestException");

        $outputException = $method->invoke($adapter, new GuzzleExceptions\RequestException('foo', $guzzleRequest, $guzzleResponse), $request);
        $this->assertInstanceOf('Http\Client\Exception\HttpException', $outputException, "Guzzle's RequestException should be converted to a HttpException");

        $outputException = $method->invoke($adapter, new GuzzleExceptions\BadResponseException('foo', $guzzleRequest, $guzzleResponse), $request);
        $this->assertInstanceOf('Http\Client\Exception\HttpException', $outputException, "Guzzle's BadResponseException should be converted to a HttpException");

        $outputException = $method->invoke($adapter, new GuzzleExceptions\ClientException('foo', $guzzleRequest, $guzzleResponse), $request);
        $this->assertInstanceOf('Http\Client\Exception\HttpException', $outputException, "Guzzle's ClientException should be converted to a HttpException");

        $outputException = $method->invoke($adapter, new GuzzleExceptions\ServerException('foo', $guzzleRequest, $guzzleResponse), $request);
        $this->assertInstanceOf('Http\Client\Exception\HttpException', $outputException, "Guzzle's ServerException should be converted to a HttpException");

        $outputException = $method->invoke($adapter, new GuzzleExceptions\TransferException('foo'), $request);
        $this->assertInstanceOf('Http\Client\Exception\TransferException', $outputException, "Guzzle's TransferException should be converted to a TransferException");

        $outputException = $method->invoke($adapter, new GuzzleExceptions\ParseException('foo', $guzzleResponse), $request);
        $this->assertInstanceOf('Http\Client\Exception\TransferException', $outputException, "Guzzle's ParseException should be converted to a TransferException");

        /*
         * Test RequestException without response
         */
        $outputException = $method->invoke($adapter, new GuzzleExceptions\RequestException('foo', $guzzleRequest), $request);
        $this->assertInstanceOf('Http\Client\Exception\RequestException', $outputException, "Guzzle's RequestException with no response should be converted to a RequestException");

        $outputException = $method->invoke($adapter, new GuzzleExceptions\BadResponseException('foo', $guzzleRequest), $request);
        $this->assertInstanceOf('Http\Client\Exception\RequestException', $outputException, "Guzzle's BadResponseException with no response should be converted to a RequestException");

        $outputException = $method->invoke($adapter, new GuzzleExceptions\ClientException('foo', $guzzleRequest), $request);
        $this->assertInstanceOf('Http\Client\Exception\RequestException', $outputException, "Guzzle's ClientException with no response should be converted to a RequestException");

        $outputException = $method->invoke($adapter, new GuzzleExceptions\ServerException('foo', $guzzleRequest), $request);
        $this->assertInstanceOf('Http\Client\Exception\RequestException', $outputException, "Guzzle's ServerException with no response should be converted to a RequestException");
    }
}