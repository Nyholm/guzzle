<?php
namespace GuzzleHttp\Exception;

use Psr\Http\Client\Exception\HttpException;

/**
 * Exception when an HTTP error occurs (4xx or 5xx error)
 */
class BadResponseException extends RequestException implements HttpException {}
