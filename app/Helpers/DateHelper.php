<?php

use Carbon\Carbon as Date;
use Illuminate\Support\Facades\Cache;

function date_formats()
{
    return [
        'm/d/Y' => [
            'setting' => 'm/d/Y',
            'datepicker' => 'mm/dd/yyyy',
        ],
        'm-d-Y' => [
            'setting' => 'm-d-Y',
            'datepicker' => 'mm-dd-yyyy',
        ],
        'm.d.Y' => [
            'setting' => 'm.d.Y',
            'datepicker' => 'mm.dd.yyyy',
        ],
        'Y/m/d' => [
            'setting' => 'Y/m/d',
            'datepicker' => 'yyyy/mm/dd',
        ],
        'Y-m-d' => [
            'setting' => 'Y-m-d',
            'datepicker' => 'yyyy-mm-dd',
        ],
        'Y.m.d' => [
            'setting' => 'Y.m.d',
            'datepicker' => 'yyyy.mm.dd',
        ],
        'd/m/Y' => [
            'setting' => 'd/m/Y',
            'datepicker' => 'dd/mm/yyyy',
        ],
        'd-m-Y' => [
            'setting' => 'd-m-Y',
            'datepicker' => 'dd-mm-yyyy',
        ],
        'd.m.Y' => [
            'setting' => 'd.m.Y',
            'datepicker' => 'dd.mm.yyyy',
        ],
    ];
}

function dbDate($date, $withSec = false)
{
    $withSec = strlen($date) > 10 ? true : $withSec;

    return dateParser($date, null, $withSec)->toDateTimeString();
}

function dateElapsed($date)
{
    $cdate = Date::parse($date);

    return $cdate->diffForHumans();
}

function dateString($date)
{
    $cdate = Date::parse($date);

    return $cdate->formatLocalized(get_option('date_format'));

    //return $cdate->toDateString();
}

function dateTimeString($date)
{
    $cdate = Date::parse($date);

    return $cdate->formatLocalized(get_option('date_format').' %l:%M %p');

    // return $cdate->toDateTimeString();
}

function dateFormatted($date)
{
    $cdate = Date::parse($date);

    return $cdate->formatLocalized(get_option('date_format'));
}

function dateIso8601String($date)
{
    return Date::parse($date)->toIso8601String();
}

function dateTimeFormatted($date)
{
    $cdate = Date::parse($date);

    return $cdate->toDayDateTimeString();
}

function timelog($str_time)
{
    sscanf($str_time, '%d:%d:%d', $hours, $minutes, $seconds);

    return isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
}

function dateParser($date, $tz = null, $withSec = false)
{
    $withSec = strlen($date) > 10 ? true : $withSec;
    $format = $withSec ? config('system.preferred_date_format').' h:i A' : config('system.preferred_date_format');
    $tz = new \DateTimeZone(is_null($tz) ? get_option('timezone') : $tz);
    if ($date instanceof \Carbon\Carbon) {
        return $date;
    }

    return \Carbon\Carbon::instance(\DateTime::createFromFormat($format, $date, $tz));
}

function datesMonth($month, $year)
{
    $num = daysInMonth($month, $year);
    $dates_month = [];

    for ($i = 1; $i <= $num; $i++) {
        $mktime = mktime(0, 0, 0, $month, $i, $year);
        $date = date('d-m-Y', $mktime);
        $dates_month[$i] = $date;
    }

    return $dates_month;
}

/*
 * Returns the number of days in a given month and year, taking into account leap years.
 *
 * $month: numeric month (integers 1-12)
 * $year: numeric year (any integer)
 */
function daysInMonth($month, $year)
{
    return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
}

/**
 * Adds interval to yyyy-mm-dd date and returns in same format.
 *
 * @param string $date
 * @param int    $days
 *
 * @return string
 */
function incrementDate($date, $days)
{
    $cdate = Date::parse($date);

    return $cdate->addDays($days)->toDateTimeString();
}

function nextRecurringDate($date, $frequency)
{
    $cdate = Date::parse($date);
    $nextDate = checkFutureDate($cdate->addDays($frequency), $frequency);

    return $nextDate->toDateTimeString();
}

function checkFutureDate($cdate, $frequency)
{
    if ($cdate->timestamp < now()->timestamp) {
        return checkFutureDate($cdate->addDays($frequency), $frequency);
    }

    return $cdate;
}

function dateFromUnix($timestamp)
{
    $cdate = Date::createFromTimestamp($timestamp);

    return $cdate->formatLocalized('%b %d, %Y %l:%M %p');
}

function dueInDays($due_date)
{
    $created = Date::parse(date('Y-m-d'));
    $due_date = Date::parse($due_date);

    return $created->diffInDays($due_date);
}

function timePickerFormat($attr)
{
    return Date::parse($attr)->format(config('system.preferred_date_format').' h:i A');
}

function datePickerFormat($date)
{
    return Date::parse($date)->format(config('system.preferred_date_format'));
}

function getMonth($date)
{
    $cdate = Date::parse($date);

    return $cdate->month;
}

function lastMonth()
{
    $cdate = new Date('last month');

    return $cdate;
}
function diffDays($from, $to = null)
{
    if (is_null($to)) {
        $to = today();
    }
    $cdate = Date::parse($from);

    return $cdate->diffInDays($to, false);
}

function gmsec($seconds)
{
    $t = round($seconds);

    return sprintf('%02d:%02d:%02d', ($t / 3600), ($t / 60 % 60), $t % 60);
}

/**
 * Timezones list with GMT offset
 *
 * @return array
 */
function timezones()
{
    return Cache::remember(
        'workice-timezones',
        now()->addDays(5),
        function () {
            $zones_array = [];
            $timestamp = time();
            foreach (timezone_identifiers_list() as $key => $zone) {
                date_default_timezone_set($zone);
                $zones_array[$zone] = 'UTC/GMT '.date('P', $timestamp).' - '.$zone;
            }

            return $zones_array;
        }
    );
}

function tz_list()
{
    $timezoneIdentifiers = \DateTimeZone::listIdentifiers();
    $utcTime = new \DateTime('now', new \DateTimeZone('UTC'));

    $tempTimezones = [];
    foreach ($timezoneIdentifiers as $timezoneIdentifier) {
        $currentTimezone = new \DateTimeZone($timezoneIdentifier);

        $tempTimezones[] = [
            'offset' => (int) $currentTimezone->getOffset($utcTime),
            'identifier' => $timezoneIdentifier,
        ];
    }

    $timezoneList = [];
    foreach ($tempTimezones as $tz) {
        $sign = ($tz['offset'] > 0) ? '+' : '-';
        $offset = gmdate('H:i', abs($tz['offset']));
        $timezoneList[$tz['identifier']] = 'UTC '.$sign.$offset.' - '.
            $tz['identifier'];
    }

    return $timezoneList;
}

function validateDate($x)
{
    return date('Y-m-d H:i:s', strtotime($x)) == $x;
}

// Get number of days

function numDays($frequency)
{
    switch ($frequency) {
        case '7D':
            return 7;
            break;
        case '1M':
            return 31;
            break;
        case '3M':
            return 90;
            break;
        case '6M':
            return 182;
            break;
        case '1Y':
            return 365;
            break;
    }
}
