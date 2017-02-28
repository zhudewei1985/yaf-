<?php

/**
 * NotAcceptableHttpException.
 *
 * @author 知名不具
 */
class Exception_NotAcceptableHttpException extends Exception_HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     */
    public function __construct($message = null, Exception $previous = null, $code = 0)
    {
        parent::__construct(406, $message, $previous, array(), $code);
    }
}