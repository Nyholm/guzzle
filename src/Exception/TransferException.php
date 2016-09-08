<?php
namespace GuzzleHttp\Exception;

use Psr\Http\Client\Exception;

class TransferException extends \RuntimeException implements GuzzleException, Exception {}
