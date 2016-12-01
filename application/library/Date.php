<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Date helper.
 *
 * @package    Elixir
 * @category   Helpers
 * @author     Elixir Team
 * @copyright  (c) 2007-2012 Elixir Team
 * @license
 */
class Date
{

    // Second amounts for various time increments
    const YEAR = 31556926;
    const MONTH = 2629744;
    const WEEK = 604800;
    const DAY = 86400;
    const HOUR = 3600;
    const MINUTE = 60;

    // Available formats for Date::months()
    const MONTHS_LONG = '%B';
    const MONTHS_SHORT = '%b';

    /**
     * Default timestamp format for formatted_time
     * @var  string
     */
    public static $timestamp_format = 'Y-m-d H:i:s';

    /**
     * Timezone for formatted_time
     * @link http://uk2.php.net/manual/en/timezones.php
     * @var  string
     */
    public static $timezone;

    /**
     * Returns the offset (in seconds) between two time zones. Use this to
     * display dates to users in different time zones.
     *
     *     $seconds = Date::offset('America/Chicago', 'GMT');
     *
     * [!!] A list of time zones that PHP supports can be found at
     * <http://php.net/timezones>.
     *
     * @param   string $remote timezone that to find the offset of
     * @param   string $local timezone used as the baseline
     * @param   mixed $now UNIX timestamp or date string
     * @return  integer
     */
    public static function offset($remote, $local = NULL, $now = NULL)
    {
        if ($local === NULL) {
            // Use the default timezone
            $local = date_default_timezone_get();
        }

        if (is_int($now)) {
            // Convert the timestamp into a string
            $now = date(DateTime::RFC2822, $now);
        }

        // Create timezone objects
        $zone_remote = new DateTimeZone($remote);
        $zone_local = new DateTimeZone($local);

        // Create date objects from timezones
        $time_remote = new DateTime($now, $zone_remote);
        $time_local = new DateTime($now, $zone_local);

        // Find the offset
        $offset = $zone_remote->getOffset($time_remote) - $zone_local->getOffset($time_local);

        return $offset;
    }

    /**
     * Number of seconds in a minute, incrementing by a step. Typically used as
     * a shortcut for generating a list that can used in a form.
     *
     *     $seconds = Date::seconds(); // 01, 02, 03, ..., 58, 59, 60
     *
     * @param   integer $step amount to increment each step by, 1 to 30
     * @param   integer $start start value
     * @param   integer $end end value
     * @return  array   A mirrored (foo => foo) array from 1-60.
     */
    public static function seconds($step = 1, $start = 0, $end = 60)
    {
        // Always integer
        $step = (int)$step;

        $seconds = array();

        for ($i = $start; $i < $end; $i += $step) {
            $seconds[$i] = sprintf('%02d', $i);
        }

        return $seconds;
    }

    /**
     * Number of minutes in an hour, incrementing by a step. Typically used as
     * a shortcut for generating a list that can be used in a form.
     *
     *     $minutes = Date::minutes(); // 05, 10, 15, ..., 50, 55, 60
     *
     * @uses    Date::seconds
     * @param   integer $step amount to increment each step by, 1 to 30
     * @return  array   A mirrored (foo => foo) array from 1-60.
     */
    public static function minutes($step = 5)
    {
        // Because there are the same number of minutes as seconds in this set,
        // we choose to re-use seconds(), rather than creating an entirely new
        // function. Shhhh, it's cheating! ;) There are several more of these
        // in the following methods.
        return Date::seconds($step);
    }

    /**
     * Number of hours in a day. Typically used as a shortcut for generating a
     * list that can be used in a form.
     *
     *     $hours = Date::hours(); // 01, 02, 03, ..., 10, 11, 12
     *
     * @param   integer $step amount to increment each step by
     * @param   boolean $long use 24-hour time
     * @param   integer $start the hour to start at
     * @return  array   A mirrored (foo => foo) array from start-12 or start-23.
     */
    public static function hours($step = 1, $long = FALSE, $start = NULL)
    {
        // Default values
        $step = (int)$step;
        $long = (bool)$long;
        $hours = array();

        // Set the default start if none was specified.
        if ($start === NULL) {
            $start = ($long === FALSE) ? 1 : 0;
        }

        $hours = array();

        // 24-hour time has 24 hours, instead of 12
        $size = ($long === TRUE) ? 23 : 12;

        for ($i = $start; $i <= $size; $i += $step) {
            $hours[$i] = (string)$i;
        }

        return $hours;
    }

    /**
     * Returns AM or PM, based on a given hour (in 24 hour format).
     *
     *     $type = Date::ampm(12); // PM
     *     $type = Date::ampm(1);  // AM
     *
     * @param   integer $hour number of the hour
     * @return  string
     */
    public static function ampm($hour)
    {
        // Always integer
        $hour = (int)$hour;

        return ($hour > 11) ? 'PM' : 'AM';
    }

    /**
     * Adjusts a non-24-hour number into a 24-hour number.
     *
     *     $hour = Date::adjust(3, 'pm'); // 15
     *
     * @param   integer $hour hour to adjust
     * @param   string $ampm AM or PM
     * @return  string
     */
    public static function adjust($hour, $ampm)
    {
        $hour = (int)$hour;
        $ampm = strtolower($ampm);

        switch ($ampm) {
            case 'am':
                if ($hour == 12) {
                    $hour = 0;
                }
                break;
            case 'pm':
                if ($hour < 12) {
                    $hour += 12;
                }
                break;
        }

        return sprintf('%02d', $hour);
    }

    /**
     * Number of days in a given month and year. Typically used as a shortcut
     * for generating a list that can be used in a form.
     *
     *     Date::days(4, 2010); // 1, 2, 3, ..., 28, 29, 30
     *
     * @param   integer $month number of month
     * @param   integer $year number of year to check month, defaults to the current year
     * @return  array   A mirrored (foo => foo) array of the days.
     */
    public static function days($month, $year = FALSE)
    {
        static $months;

        if ($year === FALSE) {
            // Use the current year by default
            $year = date('Y');
        }

        // Always integers
        $month = (int)$month;
        $year = (int)$year;

        // We use caching for months, because time functions are used
        if (empty($months[$year][$month])) {
            $months[$year][$month] = array();

            // Use date to find the number of days in the given month
            $total = date('t', mktime(1, 0, 0, $month, 1, $year)) + 1;

            for ($i = 1; $i < $total; $i++) {
                $months[$year][$month][$i] = (string)$i;
            }
        }

        return $months[$year][$month];
    }

    /**
     * Number of months in a year. Typically used as a shortcut for generating
     * a list that can be used in a form.
     *
     * By default a mirrored array of $month_number => $month_number is returned
     *
     *     Date::months();
     *     // aray(1 => 1, 2 => 2, 3 => 3, ..., 12 => 12)
     *
     * But you can customise this by passing in either Date::MONTHS_LONG
     *
     *     Date::months(Date::MONTHS_LONG);
     *     // array(1 => 'January', 2 => 'February', ..., 12 => 'December')
     *
     * Or Date::MONTHS_SHORT
     *
     *     Date::months(Date::MONTHS_SHORT);
     *     // array(1 => 'Jan', 2 => 'Feb', ..., 12 => 'Dec')
     *
     * @uses    Date::hours
     * @param   string $format The format to use for months
     * @return  array   An array of months based on the specified format
     */
    public static function months($format = NULL)
    {
        $months = array();

        if ($format === Date::MONTHS_LONG OR $format === Date::MONTHS_SHORT) {
            for ($i = 1; $i <= 12; ++$i) {
                $months[$i] = strftime($format, mktime(0, 0, 0, $i, 1));
            }
        } else {
            $months = Date::hours();
        }

        return $months;
    }

    /**
     * Returns an array of years between a starting and ending year. By default,
     * the the current year - 5 and current year + 5 will be used. Typically used
     * as a shortcut for generating a list that can be used in a form.
     *
     *     $years = Date::years(2000, 2010); // 2000, 2001, ..., 2009, 2010
     *
     * @param   integer $start starting year (default is current year - 5)
     * @param   integer $end ending year (default is current year + 5)
     * @return  array
     */
    public static function years($start = FALSE, $end = FALSE)
    {
        // Default values
        $start = ($start === FALSE) ? (date('Y') - 5) : (int)$start;
        $end = ($end === FALSE) ? (date('Y') + 5) : (int)$end;

        $years = array();

        for ($i = $start; $i <= $end; $i++) {
            $years[$i] = (string)$i;
        }

        return $years;
    }

    /**
     * Returns time difference between two timestamps, in human readable format.
     * If the second timestamp is not given, the current time will be used.
     * Also consider using [Date::fuzzy_span] when displaying a span.
     *
     *     $span = Date::span(60, 182, 'minutes,seconds'); // array('minutes' => 2, 'seconds' => 2)
     *     $span = Date::span(60, 182, 'minutes'); // 2
     *
     * @param   integer $remote timestamp to find the span of
     * @param   integer $local timestamp to use as the baseline
     * @param   string $output formatting string
     * @return  string   when only a single output is requested
     * @return  array    associative list of all outputs requested
     */
    public static function span($remote, $local = NULL, $output = 'years,months,weeks,days,hours,minutes,seconds')
    {
        // Normalize output
        $output = trim(strtolower((string)$output));

        if (!$output) {
            // Invalid output
            return FALSE;
        }

        // Array with the output formats
        $output = preg_split('/[^a-z]+/', $output);

        // Convert the list of outputs to an associative array
        $output = array_combine($output, array_fill(0, count($output), 0));

        // Make the output values into keys
        extract(array_flip($output), EXTR_SKIP);

        if ($local === NULL) {
            // Calculate the span from the current time
            $local = time();
        }

        // Calculate timespan (seconds)
        $timespan = abs($remote - $local);

        if (isset($output['years'])) {
            $timespan -= Date::YEAR * ($output['years'] = (int)floor($timespan / Date::YEAR));
        }

        if (isset($output['months'])) {
            $timespan -= Date::MONTH * ($output['months'] = (int)floor($timespan / Date::MONTH));
        }

        if (isset($output['weeks'])) {
            $timespan -= Date::WEEK * ($output['weeks'] = (int)floor($timespan / Date::WEEK));
        }

        if (isset($output['days'])) {
            $timespan -= Date::DAY * ($output['days'] = (int)floor($timespan / Date::DAY));
        }

        if (isset($output['hours'])) {
            $timespan -= Date::HOUR * ($output['hours'] = (int)floor($timespan / Date::HOUR));
        }

        if (isset($output['minutes'])) {
            $timespan -= Date::MINUTE * ($output['minutes'] = (int)floor($timespan / Date::MINUTE));
        }

        // Seconds ago, 1
        if (isset($output['seconds'])) {
            $output['seconds'] = $timespan;
        }

        if (count($output) === 1) {
            // Only a single output was requested, return it
            return array_pop($output);
        }

        // Return array
        return $output;
    }

    /**
     * 格式化 UNIX 时间戳为人易读的字符串
     *  $span = Date::fuzzy_span(time() - 10); // "10秒以前"
     * 设置 $locale 为 false， 除非你在 skyuc_date() 调用中动态设置  date() 和 strftime() 的格式
     *
     * @param    string $format 同PHP date函数格式
     * @param    integer    Unix 时间戳
     * @param    boolean    如果为 true, 使用 strftime() 生成指定日期
     * @param    boolean    如果为 true, 使用 gmstrftime() 、 gmdate() 替换 strftime() 和 date()
     *
     * @return    string    格式化的日期字符串
     */
    public static function fuzzy_span(string $format, int $timestamp, bool $locale = FALSE, bool $gmdate = FALSE): string
    {
        if ($locale)
            $datefunc = $gmdate ? 'gmstrftime' : 'strftime';
        else
            $datefunc = $gmdate ? 'gmdate' : 'date';
        $timestamp_adjusted = max(0, $timestamp);
        $timediff = $_SERVER['REQUEST_TIME'] - $timestamp;
        if ($timediff >= 0) {
            if ($timediff < 60)
                $ret = sprintf('%d秒前', $timediff);
            else if ($timediff < 120)
                $ret = '1分钟前';
            else if ($timediff < 3600)
                $ret = sprintf('%d分钟前', intval($timediff / 60));
            else if ($timediff < 7200)
                $ret = '1小时前';
            else if ($timediff < 86400)
                $ret = sprintf('%d小时前', intval($timediff / 3600));
            else if ($timediff < 172800)
                $ret = '昨天';
            else if ($timediff < 604800)
                $ret = sprintf('%d天前', intval($timediff / 86400));
            else if ($timediff < 1209600)
                $ret = '1周前';
            else if ($timediff < 3024000)
                $ret = sprintf('%d周前', intval($timediff / 604900));
            else
                $ret = $datefunc($format, $timestamp_adjusted);
        } else {
            $ret = $datefunc($format, $timestamp_adjusted);
        }
        return $ret;
    }

    /**
     * Converts a UNIX timestamp to DOS format. There are very few cases where
     * this is needed, but some binary formats use it (eg: zip files.)
     * Converting the other direction is done using {@link Date::dos2unix}.
     *
     *     $dos = Date::unix2dos($unix);
     *
     * @param   integer $timestamp UNIX timestamp
     * @return  integer
     */
    public static function unix2dos($timestamp = FALSE)
    {
        $timestamp = ($timestamp === FALSE) ? getdate() : getdate($timestamp);

        if ($timestamp['year'] < 1980) {
            return (1 << 21 | 1 << 16);
        }

        $timestamp['year'] -= 1980;

        // What voodoo is this? I have no idea... Geert can explain it though,
        // and that's good enough for me.
        return ($timestamp['year'] << 25 | $timestamp['mon'] << 21 |
            $timestamp['mday'] << 16 | $timestamp['hours'] << 11 |
            $timestamp['minutes'] << 5 | $timestamp['seconds'] >> 1);
    }

    /**
     * Converts a DOS timestamp to UNIX format.There are very few cases where
     * this is needed, but some binary formats use it (eg: zip files.)
     * Converting the other direction is done using {@link Date::unix2dos}.
     *
     *     $unix = Date::dos2unix($dos);
     *
     * @param   integer $timestamp DOS timestamp
     * @return  integer
     */
    public static function dos2unix($timestamp = FALSE)
    {
        $sec = 2 * ($timestamp & 0x1f);
        $min = ($timestamp >> 5) & 0x3f;
        $hrs = ($timestamp >> 11) & 0x1f;
        $day = ($timestamp >> 16) & 0x1f;
        $mon = ($timestamp >> 21) & 0x0f;
        $year = ($timestamp >> 25) & 0x7f;

        return mktime($hrs, $min, $sec, $mon, $day, $year + 1980);
    }

    /**
     * Returns a date/time string with the specified timestamp format
     *
     *     $time = Date::formatted_time('5 minutes ago');
     *
     * @link    http://www.php.net/manual/datetime.construct
     * @param   string $datetime_str datetime string
     * @param   string $timestamp_format timestamp format
     * @param   string $timezone timezone identifier
     * @return  string
     */
    public static function formatted_time($datetime_str = 'now', $timestamp_format = NULL, $timezone = NULL)
    {
        $timestamp_format = ($timestamp_format == NULL) ? Date::$timestamp_format : $timestamp_format;
        $timezone = ($timezone === NULL) ? Date::$timezone : $timezone;

        $tz = new DateTimeZone($timezone ? $timezone : date_default_timezone_get());
        $time = new DateTime($datetime_str, $tz);

        // Convert the time back to the expected timezone if required (in case the datetime_str provided a timezone,
        // offset or unix timestamp. This also ensures that the timezone reported by the object is correct on HHVM
        // (see https://github.com/facebook/hhvm/issues/2302).
        $time->setTimeZone($tz);

        return $time->format($timestamp_format);
    }

    /**
     * 友好格式化日期：已过去多久
     *
     * @param int $time 输入时间戳
     * @param string $format 时间格式
     * @param boolon $second 是否精确到秒
     * @return string
     */
    public static function time_format(int $time, string $format = 'Y年n月j日 G:i:s', bool $second = FALSE): string
    {
        $diff = TIMENOW - $time;
        if ($diff < 60 && $second) {
            return $diff ? $diff . '秒前' : '刚刚';
        }
        $diff = ceil($diff / 60);
        if ($diff < 60) {
            return $diff ? $diff . '分钟前' : '刚刚';
        }
        $d = date('Y,n,j', TIMENOW);
        list($year, $month, $day) = explode(',', $d);
        $today = mktime(0, 0, 0, $month, $day, $year);
        $diff = ($time - $today) / 86400;
        switch (TRUE) {
            case $diff < -2:
                break;
            case $diff < -1:
                $format = '前天 ' . ($second ? 'G:i:s' : 'G:i');
                break;
            case $diff < 0:
                $format = '昨天 ' . ($second ? 'G:i:s' : 'G:i');
                break;
            default:
                $format = '今天 ' . ($second ? 'G:i:s' : 'G:i');
        }
        return date($format, $time);
    }

    /**
     * 友好格式化日期：多久之后
     *
     * @param int $time 时间戳
     * @param string $full_format 超出指定天数范围后使用的时间戳
     * @param int $day_max 指定一个天数范围，当剩余天数大于该天数时，返回 $full_format 格式的时间
     * @return string 格式化结果
     */
    public static function time_format_after(int $time, string $full_format = 'Y-m-d H:i:s', int $day_max = 30): string
    {
        $diff = $time - TIMENOW;
        if ($diff == 0) {
            return '现在';
        }
        if ($diff < 0) {
            return self::time_format($time, $full_format, TRUE);
        }
        if ($diff < 60) {
            return $diff . '秒后';
        }
        $minute = ceil($diff / 60);
        if ($minute < 60) {
            return $minute . '分钟后';
        }
        $day = ceil($diff / 86400);
        if ($day_max && $day > $day_max) {
            return date($full_format, $time);
        }
        $time = date('G:i', $time);
        if ($day < 1) {
            return '今天 ' . $time;
        }
        if ($day < 2) {
            return '明天 ' . $time;
        }
        if ($day < 3) {
            return '后天 ' . $time;
        }
        return $day . '天后';
    }

    /**
     * 友好格式化时时：将转换为时分秒显示
     *
     * @param int $second 秒数
     * @return string
     */
    public static function second_format(int $second): string
    {
        $hour = $minute = 0;
        $str = '';
        if ($second > 3600) {
            $hour = floor($second / 3600);
            $second = $second % 3600;
        }
        if ($second > 60) {
            $minute = floor($second / 60);
            $second = $second % 60;
        }
        if ($hour) {
            $str .= $hour . "小时";
        }
        if ($minute) {
            $str .= $minute . "分";
        }
        if ($second) {
            $str .= $second . "秒";
        }
        return $str;
    }

    /**
     * 本周一凌晨00:00:00
     * @return int
     */
    public static function week_unix():int
    {
        static $result = NULL;
        if (is_null($result)) {
            list($year, $month, $day, $day_of_week) = explode(' ', date('Y m d N', $_SERVER['REQUEST_TIME']));
            $result = mktime(0, 0, 0, intval($month), intval($day - ($day_of_week - 1)), intval($year));
        }
        return date('Y-m-d 00:00:00', $result);
    }

    /**
     * 本周一凌晨时间戳
     * @return string
     */
    public static function week():string
    {
        static $result = NULL;
        if (is_null($result)) {
            list($year, $month, $day, $day_of_week) = explode(' ', date('Y m d N', $_SERVER['REQUEST_TIME']));
            $result = mktime(0, 0, 0, intval($month), intval($day - ($day_of_week - 1)), intval($year));
        }
        return $result;
    }
}
