<?php

namespace Virdiggg\SeederCi3\Utils;

class Str
{
  /**
   * Emoticons.
   *
   * @param array $OwO
   */
  public $OwO;

  public function __construct()
  {
    $this->OwO = [
      '╰(*°▽°*)╯',
      '(❁´◡`❁)',
      "(*/ω＼*)",
      '(^///^)',
      '☆*: .｡. o(≧▽≦)o .｡.:*☆',
      "(●'◡'●)",
      'ヾ(≧▽≦*)o',
      'ψ(｀∇´)ψ',
      'φ(*￣0￣)',
      '（￣︶￣）↗',
      'q(≧▽≦q)',
      '*^____^*',
      '(～￣▽￣)～',
      '( •̀ ω •́ )✧',
      '[]~(￣▽￣)~*',
      'O(∩_∩)O',
      'o(*^＠^*)o',
      'φ(゜▽゜*)♪',
      '(*^▽^*)',
      "`(*>﹏<*)′",
      '(✿◡‿◡)',
      '(●ˇ∀ˇ●)',
      '(´▽`ʃ♡ƪ)',
      '(≧∇≦)ﾉ',
      '(*^_^*)',
      '（*＾-＾*）',
      '\^o^/',
      '(￣y▽￣)╭ Ohohoho.....',
      '○( ＾皿＾)っ Hehehe…',
      '(‾◡◝)',
      '(o゜▽゜)o☆',
      '(〃￣︶￣)人(￣︶￣〃)',
      '(^_-)db(-_^)',
      'o(*￣▽￣*)ブ',
      'o(*^▽^*)┛',
      '(≧∀≦)ゞ',
      '♪(^∇^*)',
      'o(*￣▽￣*)ブ',
      '(oﾟvﾟ)ノ',
      'o(*￣︶￣*)o',
      '( $ _ $ )',
      '(/≧▽≦)/',
      'o(*≧▽≦)ツ┏━┓',
      'ㄟ(≧◇≦)ㄏ',
      'ヾ(＠⌒ー⌒＠)ノ',
      '(☆▽☆)',
      'ヾ(≧ ▽ ≦)ゝ',
      'o((>ω< ))o',
      '( *︾▽︾)',
      '(((o(*ﾟ▽ﾟ*)o)))',
      '＼(((￣(￣(￣▽￣)￣)￣)))／',
      '( *^-^)ρ(^0^* )',
      '♪(´▽｀)',
      "~~~///(^v^)\\\\\\\~~~",
      'o(*￣▽￣*)o',
      '(p≧w≦q)',
      'ƪ(˘⌣˘)ʃ',
      '( •̀ ω •́ )y'
    ];
  }

  /**
   * Determine if a given string starts with a given substring. Case sensitive.
   * Stolen from laravel helper.
   *
   * @param  string  $haystack
   * @param  string|string[]  $needles
   * @return bool
   */
  public function startsWith($haystack, $needles)
  {
    foreach ((array) $needles as $needle) {
      if ((string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Get the portion of a string before the first occurrence of a given value.
   * Stolen from laravel helper.
   *
   * @param  string  $subject
   * @param  string  $search
   * @return string
   */
  public function before($subject, $search)
  {
    if ($search === '') {
      return $subject;
    }

    $result = strstr($subject, (string) $search, TRUE);

    return $result === FALSE ? $subject : $result;
  }

  /**
   * Get the portion of a string before the last occurrence of a given value.
   * Stolen from laravel helper.
   *
   * @param  string  $subject
   * @param  string  $search
   * @return string
   */
  public function beforeLast($subject, $search)
  {
    if ($search === '') {
      return $subject;
    }

    $pos = mb_strrpos($subject, $search);

    if ($pos === FALSE) {
      return $subject;
    }

    return substr($subject, 0, $pos);
  }

  /**
   * Return the remainder of a string after the last occurrence of a given value.
   * Stolen from laravel helper.
   *
   * @param  string  $subject
   * @param  string  $search
   * @return string
   */
  public function afterLast($subject, $search)
  {
    if ($search === '') {
      return $subject;
    }

    $position = strrpos($subject, (string) $search);

    if ($position === FALSE) {
      return $subject;
    }

    return substr($subject, $position + strlen($search));
  }

  /**
   * Count latest migration. Return 3 digit number by default.
   * 
   * @param int $latest
   * 
   * @return int
   */
  public function countLatest($latest)
  {
    // To verify if the next number digit is increased or not.
    // Ex. strlen(800 + 1) = 3
    $nextNumber = strlen($latest + 1);
    // Count the next digit.
    // Ex. strlen(800) + 1 = 4
    $nextDigit = strlen($latest) + 1;
    $result = $nextNumber === $nextDigit ? $nextDigit : $nextNumber;
    return $result < 3 ? 3 : $result;
  }

  /**
   * Get latest migration order.
   * Default is sequential, if there is no migration file exist.
   *
   * @param object $config
   *
   * @return string
   */
  public function latest($config)
  {
    $latest = $config->migrationVersion;
    if ($config->migrationType === 'timestamp') {
      $today = date('YmdHis');
      return $today == $latest ? $today + 1 : $today;
    }

    echo $this->redText('WARNING: CODEIGNITER 3 MIGRATION CANNOT HANDLE MIGRATION NUMBER 1000 OR ABOVE, PLEASE USE TIMESTAMP INSTEAD ╰(*°▽°*)╯');

    $count = '001';
    $latest = (int) $latest;
    $count = str_pad($latest + 1, $this->countLatest($latest), '0', STR_PAD_LEFT);

    return $count;
  }

  /**
   * Parse title page.
   *
   * @param string $text
   *
   * @return string
   */
  public function parseTitle($text)
  {
    return ucwords($this->parseWhiteSpace($text));
  }

  /**
   * Parse special character to whitespace.
   *
   * @param string $text
   *
   * @return string
   */
  public function parseWhiteSpace($text)
  {
    return preg_replace("/[^a-zA-Z0-9\s]/", ' ', $text);
  }

  /**
   * Random string.
   *
   * @param string|int $length
   *
   * @return string
   */
  public function rand($length = 4)
  {
    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle(str_repeat($pool, ceil($length / strlen($pool)))), 0, $length);
  }

  /**
   * Platform-dependent string escape
   *
   * @param	string
   * @return	string
   */
  public function escape(string $str): string
  {
    return "'" . str_replace("'", "''", $this->removeInvisibleCharacters($str, FALSE)) . "'";
  }

  /**
   * Remove Invisible Characters
   *
   * This prevents sandwiching null characters
   * between ascii characters, like Java\0script.
   *
   * @param	string
   * @param	bool
   * @return	string
   */
  public function removeInvisibleCharacters(string $str, bool $url_encoded = TRUE): string
  {
    $non_displayables = [];

    // every control character except newline (dec 10),
    // carriage return (dec 13) and horizontal tab (dec 09)
    if ($url_encoded) {
      $non_displayables[] = '/%0[0-8bcef]/i';    // url encoded 00-08, 11, 12, 14, 15
      $non_displayables[] = '/%1[0-9a-f]/i';    // url encoded 16-31
      $non_displayables[] = '/%7f/i';    // url encoded 127
    }

    $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';    // 00-08, 11, 12, 14-31, 127

    do {
      $str = preg_replace($non_displayables, '', $str, -1, $count);
    } while ($count);

    return $str;
  }

  /**
   * Normalize name field.
   *
   * @param string $text
   *
   * @return string
   */
  public function normalizeName($text)
  {
    return strip_tags(trim($this->removeInvisibleCharacters(preg_replace('/\xc2\xa0/', '', $text))));
  }

  /**
   * Normalize slash.
   *
   * @param string $text
   *
   * @return string
   */
  public function normalizeSlash($text)
  {
    return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $text);
  }

  /**
   * Normalize migration fields.
   *
   * @param string $text
   *
   * @return string
   */
  public function normalizeField($text)
  {
    return str_replace("'", '', preg_replace('/[^a-zA-Z0-9_\']/', '', trim($text)));
  }

  /**
   * Normalize migration fields.
   *
   * @param array $fields
   *
   * @return string
   */
  public function normalizeArrayField($fields)
  {
    $res = [];
    foreach ($fields as $key => $f) {
      $res[$this->normalizeField($key)] = $f;
    }
    return $res;
  }

  /**
   * Stringify value to normalize data.
   * 
   * @param mixed $value
   * 
   * @return string JSON
   */
  private function stringify($value)
  {
    if (is_array($value)) {
      return json_encode(
        $value,
        JSON_UNESCAPED_UNICODE |
          JSON_UNESCAPED_SLASHES
      );
    }

    if (is_object($value)) {
      return json_encode(
        $value,
        JSON_UNESCAPED_UNICODE |
          JSON_UNESCAPED_SLASHES
      );
    }

    if (is_bool($value)) {
      return $value ? 'true' : 'false';
    }

    if ($value === null) {
      return 'null';
    }

    return (string) $value;
  }

  /**
   * Render table for CLI.
   * 
   * @param array $rows
   * 
   * @return void
   */
  public function renderTable($rows)
  {
    if (count($rows) === 0) {
      return;
    }

    $headers = array_keys($rows[0]);

    $widths = [];

    /*
        |--------------------------------------------------------------------------
        | Header width
        |--------------------------------------------------------------------------
        */
    foreach ($headers as $header) {
      $widths[$header] = strlen($header);
    }

    /*
        |--------------------------------------------------------------------------
        | Detect max width
        |--------------------------------------------------------------------------
        */
    foreach ($rows as $row) {

      foreach ($row as $key => $value) {
        $length = strlen($this->stringify($value));

        if ($length > $widths[$key]) {
          $widths[$key] = $length;
        }
      }
    }

    /*
        |--------------------------------------------------------------------------
        | Separator
        |--------------------------------------------------------------------------
        */
    $separator = '+';

    foreach ($headers as $header) {
      $separator .= str_repeat('-', $widths[$header] + 2) . '+';
    }

    echo $separator . PHP_EOL;

    /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */
    echo '|';

    foreach ($headers as $header) {

      echo ' ' .
        str_pad(
          strtoupper($header),
          $widths[$header]
        ) .
        ' |';
    }

    echo PHP_EOL;

    echo $separator . PHP_EOL;

    /*
        |--------------------------------------------------------------------------
        | Rows
        |--------------------------------------------------------------------------
        */
    foreach ($rows as $row) {

      echo '|';

      foreach ($headers as $header) {

        echo ' ' .
          str_pad(
            $this->stringify($row[$header]),
            $widths[$header]
          ) .
          ' |';
      }

      echo PHP_EOL;
    }

    echo $separator . PHP_EOL;
  }

  /**
   * Parse returned text with red color.
   *
   * @param string $text
   *
   * @return string
   */
  public function redText($text)
  {
    return "\e[31m" . $text . "\033[0m" . "\n";
  }

  /**
   * Parse returned text with green color.
   *
   * @param string $text
   * @param bool   $withEmoticon
   *
   * @return string
   */
  public function greenText($text, $withEmoticon = true)
  {
    return "\033[92m" . ($withEmoticon ? $this->emoticon($text) : $text) . "\033[0m" . "\n";
  }

  /**
   * Parse returned text with yellow color.
   *
   * @param string $text
   *
   * @return string
   */
  public function yellowText($text)
  {
    return "\033[93m" . $text . "\033[0m" . "\n";
  }

  /**
   * Parse returned text with emoticon for fun h3h3.
   *
   * @param string $text
   *
   * @return string
   */
  public function emoticon($text)
  {
    return $text . ' ' . $this->OwO[array_rand($this->OwO, 1)];
  }
}
