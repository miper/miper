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
}