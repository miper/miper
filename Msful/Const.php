<?php
/**
 * 通用常量
 * @author ronnie<comdeng@live.com>
 * @since    2015-07-07 16:39:46
 * @version 1.0.0
 */

class Msful_Const
{
  const METHOD_GET = 'get';
  const METHOD_POST = 'post';
  const METHOD_PUT = 'put';
  const METHOD_DELETE = 'delete';

  static $methods = array(
    self::METHOD_GET,
    self::METHOD_POST,
    self::METHOD_PUT,
    self::METHOD_DELETE,
  );

  const FORMAT_JSON = 'json';
  const FORMAT_HTML = 'html';
  const FORMAT_TEXT = 'text';
  const FORMAT_JAVASCRIPT = 'js';

  static $formats = array(
    self::FORMAT_JSON,
    self::FORMAT_TEXT,
    self::FORMAT_JAVASCRIPT,
    self::FORMAT_HTML,
  );

  const TERMINAL_PC = 'pc';
  const TERMINAL_WAP = 'wap';
  const TERMINAL_APP = 'app';

  const HTTP_CODE_OK = 200;
  const HTTP_CODE_NOT_FOUND = 404;
  const HTTP_CODE_FORBIDDEN = 403;
  const HTTP_CODE_UNAUTHORIZED = 401;
  const HTTP_CODE_SERVICE_UNAVAILABLE = 503;

  static $httpCodes = array(
    self::HTTP_CODE_OK => 'ok',
    self::HTTP_CODE_UNAUTHORIZED => 'Unauthried',
    self::HTTP_CODE_FORBIDDEN => 'Forbidden',
    self::HTTP_CODE_NOT_FOUND => 'Not Found',
    self::HTTP_CODE_SERVICE_UNAVAILABLE => 'Service Unavailable',
    );
}