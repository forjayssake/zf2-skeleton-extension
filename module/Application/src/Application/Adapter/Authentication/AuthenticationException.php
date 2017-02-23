<?php
namespace Application\Adapter\Authentication;

use Zend\Authentication\Adapter\Exception\ExceptionInterface;
use RuntimeException;

class AuthenticationException extends RuntimeException implements ExceptionInterface
{
}