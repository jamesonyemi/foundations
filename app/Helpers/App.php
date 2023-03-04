<?php

use App\Entities\Country;
use App\Entities\Hook;
use App\Entities\Language;
use App\Entities\Local;
use App\Services\SvgFactory;
use Facades\App\Helpers\CurrencyConverter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Modules\Settings\Entities\Options;
use Modules\Tickets\Entities\Ticket;
use Modules\Timetracking\Entities\TimeEntry;
use Modules\Users\Entities\Profile;
use Modules\Users\Entities\User;

function getLangCode($name)
{
    return \App\Entities\Language::whereName($name)->first()->code;
}

function langapp($key, $params = [], $locale = 'en')
{
    return trans("app.{$key}", $params, $locale == 'en' ? app()->getLocale() : $locale);
}

function langdate($key, $params = [], $locale = 'en')
{
    return trans("date.{$key}", $params, $locale == 'en' ? app()->getLocale() : $locale);
}

function langactivity($key, $params = [], $locale = 'en')
{
    return trans("activity.{$key}", $params, $locale == 'en' ? app()->getLocale() : $locale);
}

function langinstall($key, $params = [], $locale = 'en')
{
    return trans("install.{$key}", $params, $locale);
}

function langmail($key, $params = [], $locale = 'en')
{
    return trans("mail.{$key}", $params, $locale == 'en' ? app()->getLocale() : $locale);
}
/**
 * Get App configuration.
 *
 * @param string $key
 */
function config_item($key)
{
    return get_option($key);
}

if (! function_exists('getAsset')) {
    function getAsset($path, $secure = null)
    {
        $timestamp = @filemtime(public_path($path)) ?: 0;

        return asset($path, $secure).'?'.$timestamp;
    }
}

if (! function_exists('activeCalendar')) {
    function activeCalendar($cal = null)
    {
        if (! is_null($cal)) {
            session(['active_cal' => $cal]);
        }

        return session('active_cal', get_option('default_calendar'));
    }
}

if (! function_exists('parsedown')) {
    /**
     * @param  string $text
     * @return string
     */
    function parsedown($text)
    {
        if ($text !== null && (get_option('htmleditor') == 'markdownEditor' || get_option('htmleditor') == 'easyMDE')) {
            return app('parsedown')->convertToHtml(e($text));
        }

        return $text;
    }
}

if (! function_exists('toastr')) {
    /**
     * Return the instance of toastr.
     *
     * @return App\Services\Toastr
     */
    function toastr()
    {
        return app('toastr');
    }
}

if (! function_exists('get_option')) {
    /**
     * Get App configuration.
     *
     * @param string $option
     * @param string $default
     */
    function get_option($option, $default = null)
    {
        $settings = cache(settingsCacheName());

        return isset($settings[$option]) ? $settings[$option] : $default;
    }
}

if (! function_exists('slugAppName')) {
    function slugAppName()
    {
        return \Illuminate\Support\Str::slug(config('app.name'), '_');
    }
}

if (! function_exists('settingsCacheName')) {
    function settingsCacheName()
    {
        return slugAppName().'-'.'settings';
    }
}

if (! function_exists('isDemo')) {
    function isDemo()
    {
        return settingEnabled('demo_mode') ? true : false;
    }
}

if (! function_exists('trackEmail')) {
    function trackEmail($id)
    {
        if (config('system.track_emails')) {
            return '![]('.route('tracker.email', ['mail' => $id]).' "")';
        }

        return '';
    }
}

if (! function_exists('settingEnabled')) {
    function settingEnabled($setting)
    {
        return get_option($setting) === 'TRUE' ? true : false;
    }
}

if (! function_exists('isActive')) {
    /**
     * Set the active class to the current opened menu.
     *
     * @param  string|array $route
     * @param  string       $className
     * @return string
     */
    function isActive($route, $className = 'active')
    {
        if (is_array($route)) {
            return in_array(Route::currentRouteName(), $route) ? $className : '';
        }
        if (Route::currentRouteName() == $route) {
            return $className;
        }
        if (strpos(URL::current(), $route)) {
            return $className;
        }
    }
}

if (! function_exists('isCommentLocked')) {
    function isCommentLocked($type, $id)
    {
        if (get_class(classByName($type)) === get_class(new Ticket())) {
            $ticket = Ticket::find($id);
            if ($ticket->reporter == Auth::id()) {
                return false;
            }

            return ($ticket->is_locked && $ticket->locked_by != Auth::id()) ? true : false;
        }

        return false;
    }
}

if (! function_exists('genUnique')) {
    function genUnique()
    {
        return uniqid('crm_');
    }
}

if (! function_exists('genNumber')) {
    function genNumber()
    {
        return mt_rand(10000000, 99999999);
    }
}

if (! function_exists('classByName')) {
    function classByName($name)
    {
        switch ($name) {
            case 'deals':
                return new \Modules\Deals\Entities\Deal();
                break;
            case 'leads':
                return new \Modules\Leads\Entities\Lead();
                break;
            case 'events':
                return new \Modules\Calendar\Entities\Calendar();
                break;
            case 'issues':
                return new \Modules\Issues\Entities\Issue();
                break;
            case 'tasks':
                return new \Modules\Tasks\Entities\Task();
                break;
            case 'tickets':
                return new \Modules\Tickets\Entities\Ticket();
                break;
            case 'clients':
                return new \Modules\Clients\Entities\Client();
                break;
            case 'projects':
                return new \Modules\Projects\Entities\Project();
                break;
            case 'users':
                return new \Modules\Users\Entities\User();
                break;
            case 'invoices':
                return new \Modules\Invoices\Entities\Invoice();
                break;
            case 'estimates':
                return new \Modules\Estimates\Entities\Estimate();
                break;
            case 'credits':
                return new \Modules\Creditnotes\Entities\CreditNote();
                break;
            case 'expenses':
                return new \Modules\Expenses\Entities\Expense();
                break;
            case 'messages':
                return new \Modules\Messages\Entities\Message();
                break;
            case 'payments':
                return new \Modules\Payments\Entities\Payment();
                break;
            case 'knowledgebase':
                return new \Modules\Knowledgebase\Entities\Knowledgebase();
                break;
            case 'contracts':
                return new \Modules\Contracts\Entities\Contract();
                break;
            case 'comments':
                return new \Modules\Comments\Entities\Comment();
                break;
            case 'emails':
                return new \Modules\Messages\Entities\Emailing();
                break;

            default:
                break;
        }
    }
}

if (! function_exists('trCode')) {
    /**
     * Generate a unique transaction number.
     *
     * @param string $prefix
     * @param bool   $entropy
     *
     * @return string
     */
    function trCode(string $prefix = null, $entropy = false)
    {
        $s = uniqid('', $entropy);
        if (! $entropy) {
            $uuid = mb_strtoupper(base_convert($s, 16, 36));
        } else {
            $hex = substr($s, 0, 13);
            $dec = $s[13].substr($s, 15); // skip the dot
            $uuid = mb_strtoupper(base_convert($hex, 16, 36).base_convert($dec, 10, 36));
        }

        return $prefix ? $prefix.'-'.$uuid : $uuid;
    }
}

if (! function_exists('getCurrentVersion')) {
    /*
     * Return current version (as plain text).
     */
    function getCurrentVersion()
    {
        return json_decode(file_get_contents(storage_path('app/version.json')), true);
    }
}

if (! function_exists('getLastVersion')) {
    function getLastVersion()
    {
        return json_decode(file_get_contents(storage_path('app/updates/update.json')), true);
    }
}

if (! function_exists('getLastReminder')) {
    function getLastReminder()
    {
        return json_decode(file_get_contents(storage_path('app/updates/last_reminder.json')), true);
    }
}

if (! function_exists('update_option')) {
    /**
     * Update App configuration.
     *
     * @param string $key
     * @param string $value
     */
    function update_option($key, $value)
    {
        return Options::updateOrCreate(['config_key' => $key], ['value' => $value]);
    }
}

if (! function_exists('renderButton')) {
    function renderButton($key, $icon = 'fas fa-paper-plane')
    {
        return '<button type="submit" class="btn '.themeButton().' formSaving submit"><i class="'.$icon.'"></i> '.$key.'</button>';
    }
}
if (! function_exists('themeButton')) {
    function themeButton($attributes = '')
    {
        return config('theme.button.'.get_option('theme_color')).' '.$attributes;
    }
}
if (! function_exists('themeText')) {
    function themeText($attributes = '')
    {
        return config('theme.text.'.get_option('theme_color')).' '.$attributes;
    }
}
if (! function_exists('themeLinks')) {
    function themeLinks($attributes = '')
    {
        return config('theme.links.'.get_option('theme_color')).' '.$attributes;
    }
}
if (! function_exists('themeBg')) {
    function themeBg($attributes = '')
    {
        return config('theme.bg.'.get_option('theme_color')).' '.$attributes;
    }
}

if (! function_exists('languages')) {
    function languages()
    {
        return Cache::remember(
            'active-lang',
            now()->addDays(1),
            function () {
                return Language::where('active', 1)->get()->toArray();
            }
        );
    }
}
if (! function_exists('defaultLanguage')) {
    function defaultLanguage()
    {
        return Cache::remember(
            'default-lang',
            now()->addDays(1),
            function () {
                return Language::where('name', get_option('default_language'))->first()->code;
            }
        );
    }
}

if (! function_exists('locales')) {
    function locales()
    {
        return Cache::remember(
            'workice-locales',
            now()->addDays(1),
            function () {
                return App\Entities\Local::groupBy('language')->get()->toArray();
            }
        );
    }
}

if (! function_exists('currencies')) {
    function currencies()
    {
        return Cache::remember(
            'workice-currencies',
            now()->addDays(1),
            function () {
                return App\Entities\Currency::get()->toArray();
            }
        );
    }
}

if (! function_exists('countries')) {
    function countries()
    {
        return Cache::remember(
            'workice-countries',
            now()->addDays(1),
            function () {
                return \App\Entities\Country::select('name')->get()->toArray();
            }
        );
    }
}

if (! function_exists('generateCode')) {
    function generateCode($module = 'invoices')
    {
        return classByName($module)->nextCode();
    }
}

if (! function_exists('referenceFormatted')) {

    /**
     * Modify reference number
     *
     * @param  string $str
     * @param  string $ref
     * @return string
     */
    function referenceFormatted($str, $ref)
    {
        $str = str_replace('[yyyy]', (string) now()->year, $str);
        $str = str_replace('[mm]', date('m'), $str);
        $str = str_replace('[dd]', date('d'), $str);
        $str = str_replace('[i]', $ref, $str);

        return $str;
    }
}

if (! function_exists('formatPhoneNumber')) {
    function formatPhoneNumber($number)
    {
        $number = preg_replace("/[^\d]/", '', $number);
        $length = strlen($number);
        if ($length == 10) {
            $number = preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", '$1-$2-$3', $number);
        }

        return $number;
    }
}

if (! function_exists('getIcon')) {
    function getIcon($ext)
    {
        return \Modules\Files\Helpers\MimeIcon::getIcon($ext);
    }
}

if (! function_exists('genToken')) {
    function genToken()
    {
        return md5(rand().microtime());
    }
}

if (! function_exists('getLocaleUsingLanguage')) {
    function getLocaleUsingLanguage($language = 'english')
    {
        switch ($language) {
            case 'portuguese-brazilian':
                return 'pt_BR';
                break;
        }

        return Local::where('language', $language)->first()->code;
    }
}

if (! function_exists('languageUsingLocale')) {
    function languageUsingLocale($locale = 'en')
    {
        switch ($locale) {
            case 'pt-br':
                return 'portuguese-brazilian';
                break;
        }

        return Local::where('code', $locale)->first()->language;
    }
}

if (! function_exists('stripAccents')) {
    function stripAccents($string)
    {
        $chars = ['Ά' => 'Α', 'ά' => 'α', 'Έ' => 'Ε', 'έ' => 'ε', 'Ή' => 'Η', 'ή' => 'η', 'Ί' => 'Ι', 'ί' => 'ι', 'Ό' => 'Ο', 'ό' => 'ο', 'Ύ' => 'Υ', 'ύ' => 'υ', 'Ώ' => 'Ω', 'ώ' => 'ω'];
        foreach ($chars as $find => $replace) {
            $string = str_replace($find, $replace, $string);
        }

        return $string;
    }
}

if (! function_exists('chartYear')) {
    function chartYear()
    {
        $year = date('Y');
        if (request()->has('setyear')) {
            cache(['chart-year' => request('setyear')], now()->addHours(12));
        }

        return cache('chart-year', $year);
    }
}

if (! function_exists('fullname')) {
    function fullname($id = null)
    {
        if (is_null($id)) {
            return Auth::user()->name;
        }

        return optional(User::find($id))->name;
    }
}

if (! function_exists('can')) {
    function can($permission)
    {
        return Auth::user()->hasPermissionTo($permission);
    }
}
if (! function_exists('randomPassword')) {
    function randomPassword($length)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        return substr(str_shuffle($chars), 0, $length);
    }
}
if (! function_exists('splitNames')) {
    /**
     * Split a Name to first name and last name
     *
     * @param [string] $name
     * @return array
     */
    function splitNames($name)
    {
        $parts = explode(' ', $name);
        if (count($parts) > 1) {
            $lastname = array_pop($parts);
            $firstname = implode(' ', $parts);
        } else {
            $firstname = $name;
            $lastname = ' ';
        }

        return [
            'firstName' => $firstname,
            'lastName' => $lastname,
        ];
    }
}

if (! function_exists('runningTimers')) {
    function runningTimers()
    {
        return Cache::remember(
            'running-timers-'.Auth::id(),
            now()->addMinutes(2),
            function () {
                return TimeEntry::select(['user_id', 'timeable_type', 'timeable_id', 'start', 'task_id'])->with('timeable:id,name', 'user:id,email,username,name')->running()->orderBy('id', 'desc')->get()->toArray();
            }
        );
    }
}

if (! function_exists('convertCurrency')) {
    function convertCurrency($from, $amount, $to = null, $xrate = null)
    {
        return CurrencyConverter::convert($from, $amount, $to, $xrate);
    }
}

if (! function_exists('formatCurrency')) {
    function formatCurrency($currency, $amount)
    {
        return CurrencyConverter::toCurrency(is_null($currency) ? 'USD' : $currency, $amount);
    }
}

if (! function_exists('formatQuantity')) {
    function formatQuantity($amount)
    {
        $dec = get_option('quantity_decimals');
        $dec_sep = get_option('decimal_separator');
        $thou_sep = get_option('thousand_separator');

        return number_format($amount, $dec, $dec_sep, $thou_sep);
    }
}

if (! function_exists('formatTax')) {
    // Format tax amount to decimal
    function formatTax($amount)
    {
        $dec = get_option('tax_decimals');
        $dec_sep = get_option('decimal_separator');
        $thou_sep = get_option('thousand_separator');

        return number_format($amount, $dec, $dec_sep, $thou_sep);
    }
}

if (! function_exists('toWords')) {
    function toWords($amount, $currency)
    {
        $transformer = new App\Helpers\ToWords($amount * 100, $currency);

        return $transformer->words();
    }
}

if (! function_exists('getImageStorageUrl')) {
    function getImageStorageUrl($fileName)
    {
        return Storage::disk('public')->url('images/'.$fileName);
    }
}
if (! function_exists('getImageUrl')) {
    function getImageUrl($imagePath)
    {
        if (config('filesystems.default') == 'local') {
            return '../storage/app/'.$imagePath;
        } else {
            $image = getStorageUrl($imagePath);

            return filter_var($image, FILTER_VALIDATE_URL) ? $image : config('app.url').$image;
        }
    }
}

if (! function_exists('getDropboxUrl')) {
    function getDropboxUrl($path)
    {
        return Storage::disk('dropbox')->getAdapter()->getTemporaryLink($path);
    }
}

if (! function_exists('getStorageUrl')) {
    function getStorageUrl($path)
    {
        if (config('filesystems.default') == 'dropbox') {
            return getDropboxUrl($path);
        }

        return Storage::url($path);
    }
}

if (! function_exists('parseCurrency')) {
    function parseCurrency($money)
    {
        $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);
        $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;
        $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
        $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '', $stringWithCommaOrDot);

        return (float) str_replace(',', '.', $removedThousandSeparator);
    }
}

if (! function_exists('formatDecimal')) {

    /**
     * Format decimal
     *
     * @param  mixed $num
     * @param  int   $dec
     * @return string
     */
    function formatDecimal($num, $dec = 2)
    {
        $number = str_replace(',', '.', $num);

        return number_format((float) $number, $dec, '.', '');
    }
}

if (! function_exists('xchangeRate')) {
    function xchangeRate($currency = null)
    {
        $currency = is_null($currency) ? get_option('default_currency') : $currency;

        return optional(App\Entities\Currency::select('xrate')->whereCode($currency)->first())->xrate;
    }
}

if (! function_exists('supportEmail')) {
    function supportEmail()
    {
        return [
            'email' => empty(get_option('support_email')) ? get_option('company_email') : get_option('support_email'),
            'name' => empty(get_option('support_email_name')) ? get_option('company_name') : get_option('support_email_name'),
        ];
    }
}

if (! function_exists('leadsEmail')) {
    function leadsEmail()
    {
        return [
            'email' => empty(get_option('leads_email')) ? get_option('company_email') : get_option('leads_email'),
            'name' => empty(get_option('leads_email_name')) ? get_option('company_name') : get_option('leads_email_name'),
        ];
    }
}

if (! function_exists('percent')) {
    function percent($num)
    {
        return round($num);
    }
}

if (! function_exists('userCalendarToken')) {
    function userCalendarToken()
    {
        if (is_null(Auth::user()->calendar_token)) {
            Auth::user()->update(['calendar_token' => str_random(60)]);
        }

        return Auth::user()->calendar_token;
    }
}

if (! function_exists('calendarLocale')) {
    function calendarLocale()
    {
        return Cache::remember(
            'calendarLocale',
            now()->addDays(10),
            function () {
                return get_option('locale', 'en-gb');
            }
        );
    }
}

if (! function_exists('datePickerLocale')) {
    function datePickerLocale()
    {
        return Cache::remember(
            'datepickerLocale',
            now()->addDays(10),
            function () {
                if (get_option('locale') == 'en') {
                    return 'en-GB';
                }

                return get_option('locale');
            }
        );
    }
}

if (! function_exists('editorLocale')) {
    function editorLocale()
    {
        return Cache::remember(
            'editorLocale',
            now()->addDays(10),
            function () {
                return get_option('locale');
            }
        );
    }
}

if (! function_exists('mainMenu')) {
    function mainMenu()
    {
        return Cache::remember(
            'workice-main-menu-'.Auth::id(),
            now()->addDays(5),
            function () {
                $collection = Hook::with('children')->where('visible', 1)->whereParent('')->whereHook('main_menu')->orderBy('order')->get();

                return $collection->filter(
                    function ($item) {
                        if (Auth::user()->can($item['module'])) {
                            return $item;
                        }
                    }
                )->toArray();
            }
        );
    }
}

if (! function_exists('projectMenu')) {
    function projectMenu()
    {
        return Hook::where(['access' => 1, 'visible' => 1, 'hook' => 'projects_menu_admin'])->orderBy('order')->get();
    }
}

if (! function_exists('settingsMenu')) {
    function settingsMenu()
    {
        return Hook::where(['hook' => 'settings_menu_admin', 'visible' => 1])->orderBy('order', 'asc')->get();
    }
}

if (! function_exists('avatar')) {
    function avatar($id = null)
    {
        // $avatarPhoto = getAsset('avatar/default_avatar.png');
        if (is_null($id)) {
            return Auth::user()->profile->photo;
        }

        return Profile::whereUserId($id)->first()->photo;
    }
}

if (! function_exists('getAvatarImage')) {
    function getAvatarImage($name)
    {
        return \Avatar::create($name)->toBase64();
    }
}

if (! function_exists('thousandsCurrencyFormat')) {
    function thousandsCurrencyFormat($num)
    {
        if ($num > 1000) {
            $x = round($num);
            $x_number_format = number_format($x);
            $x_array = explode(',', $x_number_format);
            $x_parts = ['k', 'm', 'b', 't'];
            $x_count_parts = count($x_array) - 1;
            $x_display = $x;
            $x_display = $x_array[0].((int) $x_array[1][0] !== 0 ? '.'.$x_array[1][0] : '');
            $x_display .= $x_parts[$x_count_parts - 1];

            return $x_display;
        }

        return $num;
    }
}

if (! function_exists('getCalculated')) {
    function getCalculated($key)
    {
        return optional(App\Entities\Computed::select('value')->where('key', $key)->first())->value;
    }
}

if (! function_exists('clean')) {
    function clean($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
}

if (! function_exists('renderAjaxButton')) {
    function renderAjaxButton($text = 'save', $icon = 'fas fa-check', $overwriteText = false)
    {
        return '<button type="submit" class="btn '.themeButton().' formSaving submit"><i class="'.$icon.'"></i> '.langapp($text, [], app()->getLocale()).'</button>';
    }
}

if (! function_exists('closeModalButton')) {
    function closeModalButton()
    {
        return '<a href="#" class="btn '.themeButton().'" data-dismiss="modal"><i class="fas fa-times"></i> '.langapp('close', [], app()->getLocale()).'</a>';
    }
}

if (! function_exists('okModalButton')) {
    function okModalButton()
    {
        return '<button type="submit" class="btn '.themeButton().'"><i class="fas fa-check"></i> '.langapp('ok').'</button>';
    }
}

if (! function_exists('ajaxResponse')) {
    function ajaxResponse($data, $success = true, $code = 200)
    {
        $data['success'] = $success;

        return response()->json($data, $code);
    }
}

if (! function_exists('failedRequestJson')) {
    /**
     * Error processing request
     */
    function failedRequestJson($msg = null)
    {
        $msg = is_null($msg) ? langapp('request_failed') : $msg;

        return ajaxResponse(['exception' => true, 'message' => $msg], false, 500);
    }
}

if (! function_exists('isJson')) {
    function isJson($str)
    {
        return is_string($str) && is_array(json_decode($str, true))
        && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
}

if (! function_exists('moduleActive')) {
    /**
     * Check if a module is active
     */
    function moduleActive($module)
    {
        return Module::find($module)->isEnabled();
    }
}

if (! function_exists('strBetween')) {
    function strBetween($start, $end, $str)
    {
        $str = new \Str\Str($str);

        return (string) $str->between($start, $end);
    }
}

if (! function_exists('getMentions')) {
    function getMentions($content)
    {
        $mention_regex = '/(^|\s)@([\w_\.]+)/'; //mention regrex to get all @texts
        preg_match_all($mention_regex, $content, $matches);

        return $matches[2];
    }
}

if (! function_exists('getUserIdExisting')) {
    function getUserIdExisting($username)
    {
        $select = ['id', 'username'];

        return is_null($username) ? Auth::id()
        : (User::select($select)->whereUsername($username)->count() > 0
            ? User::select($select)->whereUsername($username)->first()->id : Auth::id());
    }
}

if (! function_exists('humanize')) {
    /**
     * Humanize.
     *
     * Takes multiple words separated by the separator and changes them to spaces
     *
     * @param string $str       Input string
     * @param string $separator Input separator
     *
     * @return string
     */
    function humanize($str, $separator = '_')
    {
        return ucwords(preg_replace('/['.preg_quote($separator).']+/', ' ', trim(extension_loaded('mbstring') ? mb_strtolower($str) : strtolower($str))));
    }
}

/**
 * Underscore.
 *
 * Takes multiple words separated by spaces and underscores them
 *
 * @param string $str Input string
 *
 * @return string
 */
function underscore($str)
{
    return preg_replace('/[\s]+/', '_', trim(extension_loaded('mbstring') ? mb_strtolower($str) : strtolower($str)));
}

if (! function_exists('isAdmin')) {
    function isAdmin()
    {
        return Auth::check() ? Auth::user()->hasRole('admin') : false;
    }
}
if (! function_exists('toHours')) {
    /**
     * Convert seconds to hours
     *
     * @param  string $seconds
     * @return float
     */
    function toHours($seconds)
    {
        return $seconds > 0 ? formatDecimal($seconds / 3600, 3) : 0;
    }
}

if (! function_exists('secToHours')) {
    function secToHours($seconds)
    {
        $minutes = $seconds / 60;
        $hours = $minutes / 60;
        if ($minutes >= 60) {
            return round($hours, 2).''.langapp('hours');
        } elseif ($seconds > 60) {
            return round($minutes, 2).' '.langapp('minutes');
        } else {
            return $seconds.' '.langapp('seconds');
        }
    }
}

if (! function_exists('priorityColor')) {
    function priorityColor($priority)
    {
        switch ($priority) {
            case 'Low':
                return 'primary';
                break;
            case 'High':
                return 'dracula';
                break;
            case 'Urgent':
                return 'danger';
                break;
            default:
                return 'info';
        }
    }
}

if (! function_exists('toMarkdown')) {
    function toMarkdown($html)
    {
        $converter = new \League\HTMLToMarkdown\HtmlConverter();
        $converter->getConfig()->setOption('strip_tags', true);

        return $converter->convert($html);
    }
}

function closeMatch($str1, $str2)
{
    return levenshtein($str1, $str2) < 3 ? true : false;
}

if (! function_exists('quickAccess')) {
    function quickAccess()
    {
        return Cache::remember(
            'quick-access-'.Auth::id(),
            now()->addMinutes(5),
            function () {
                return Auth::user()->quickAccess()->get()->toArray();
            }
        );
    }
}
if (! function_exists('itemUnit')) {
    function itemUnit()
    {
        return settingEnabled('dynamic_units') ? get_option('custom_item_unit') : stripAccents(langapp('unit_price'));
    }
}

if (! function_exists('slugify')) {
    function slugify($str)
    {
        $search = ['Ș', 'Ț', 'ş', 'ţ', 'Ş', 'Ţ', 'ș', 'ț', 'î', 'â', 'ă', 'Î', 'Â', 'Ă', 'ë', 'Ë'];
        $replace = ['s', 't', 's', 't', 's', 't', 's', 't', 'i', 'a', 'a', 'i', 'a', 'a', 'e', 'E'];
        $str = str_ireplace($search, $replace, strtolower(trim($str)));
        $str = preg_replace('/[^\w\d\-\ ]/', '', $str);
        $str = str_replace(' ', '-', $str);

        return preg_replace('/\-{2,}/', '-', $str);
    }
}

if (! function_exists('svg_spritesheet')) {
    function svg_spritesheet()
    {
        return app(SvgFactory::class)->spritesheet();
    }
}

if (! function_exists('svg_image')) {
    function svg_image($icon, $class = '', $attrs = [])
    {
        return app(SvgFactory::class)->svg($icon, $class, $attrs);
    }
}

if (! function_exists('svg_icon')) {
    /**
     * @deprecated Use `svg_image`
     */
    function svg_icon($icon, $class = '', $attrs = [])
    {
        return app(SvgFactory::class)->svg($icon, $class, $attrs);
    }
}

if (! function_exists('metrics')) {
    function metrics($str)
    {
        return formatCurrency(get_option('default_currency'), getCalculated($str));
    }
}

if (! function_exists('getArrFromJson')) {
    function getArrFromJson($path)
    {
        return json_decode(file_get_contents($path), true);
    }
}
/**
 * Prepares task array gantt data to be used in the gantt chart
 * @param  array $task task array
 * @return array
 */
function prepareGanttData($task, $dep_id = null, $defaultEnd = null)
{
    $data = [];

    $data['id'] = (string) $task['id'];
    $data['desc'] = $task['name'];

    $data['start'] = strftime('%Y-%m-%d', strtotime($task['start_date']));

    if ($task['due_date']) {
        $data['end'] = strftime('%Y-%m-%d', strtotime($task['due_date']));
    } else {
        $data['end'] = $defaultEnd;
    }

    $data['desc'] = $task['name'];
    $data['label'] = $task['name'];

    $data['name'] = $task['name'];
    $data['task_id'] = $task['id'];
    $data['progress'] = (int) $task['progress'];
    $data['project'] = $task['project_id'];

    //for task in single project gantt
    if ($dep_id) {
        $data['dependencies'] = $dep_id;
    }

    return $data;
}
if (! function_exists('leadRatingClr')) {
    function leadRatingClr($status)
    {
        $status = strtolower($status);
        if ($status === 'hot') {
            return 'danger';
        }
        if ($status === 'warm') {
            return 'success';
        }

        return 'warning';
    }
}
if (! function_exists('firstAdminId')) {
    function firstAdminId()
    {
        if (cache('installed') != false) {
            return optional(User::role(User::ADMIN_ROLE)->first())->id;
        }
    }
}
if (! function_exists('urlAccessible')) {
    function urlAccessible($url)
    {
        $timeout = 10;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $http_respond = curl_exec($ch);
        $http_respond = trim(strip_tags($http_respond));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (($http_code == '200') || ($http_code == '302')) {
            return true;
        } else {
            // return $http_code;, possible too
            return false;
        }
        curl_close($ch);
    }
}

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return URL::to($uri);
    }
}

if (! function_exists('isEmptyArray')) {
    function isEmptyArray($arr)
    {
        if (is_array($arr)) {
            foreach ($arr as $value) {
                if (! empty($value)) {
                    return false;
                }
            }
        }

        return true;
    }
}

if (! function_exists('formatClientAddress')) {
    /**
     * Format customer address info
     * @param  object  $data        customer object from database
     * @param  string  $for         where this format will be used? Eq statement invoice etc
     * @param  string  $type        billing/shipping
     * @param  bool $companyLink company link to be added on customer company/name, this is used in admin area only
     * @return string
     */
    function formatClientAddress($data, $for, $type, $companyLink = false)
    {
        $format = get_option('company_address_format');
        $clientId = '';
        $clientId = $data->client_id;
        $companyName = $data->company->name;
        $street = '';
        if ($type == 'billing') {
            $street = $data->billing_street;
        } elseif ($type == 'shipping') {
            $street = $data->shipping_street;
        }

        $city = '';
        if ($type == 'billing') {
            $city = $data->billing_city;
        } elseif ($type == 'shipping') {
            $city = $data->shipping_city;
        }
        $state = '';
        if ($type == 'billing') {
            $state = $data->billing_state;
        } elseif ($type == 'shipping') {
            $state = $data->shipping_state;
        }
        $zipCode = '';
        if ($type == 'billing') {
            $zipCode = $data->billing_zip;
        } elseif ($type == 'shipping') {
            $zipCode = $data->shipping_zip;
        }

        $countryCode = '';
        $countryName = $data->company->country;
        $country = null;
        if ($type == 'billing') {
            $country = Country::where('name', $data->billing_country)->first();
        } elseif ($type == 'shipping') {
            $country = Country::where('name', $data->shipping_country)->first();
        } else {
            $country = Country::where('name', $data->company->country)->first();
        }

        if ($country) {
            $countryCode = $country->code;
            $countryName = $country->name;
        }

        $phone = '';
        if ($type == 'billing' && isset($data->company->phone)) {
            $phone = 'Phone: '.$data->company->phone;
        }

        $vat = '';
        if ($type == 'billing' && isset($data->company->tax_number)) {
            $vat = $data->company->tax_number;
        }

        if ($companyLink) {
            $companyName = '<a href="'.route('clients.view', $clientId).'" class="'.themeLinks('font-semibold uppercase').'">'.$companyName.'</a>';
        } elseif ($companyName != '') {
            $companyName = '<b class="'.themeLinks('font-semibold uppercase').'">'.$companyName.'</b>';
        }
        $format = addressFormatReplace('company_name', $companyName, $format);
        $format = addressFormatReplace('customer_id', $clientId, $format);
        $format = addressFormatReplace('street', $street, $format);
        $format = addressFormatReplace('city', $city, $format);
        $format = addressFormatReplace('state', $state, $format);
        $format = addressFormatReplace('zip', $zipCode, $format);
        $format = addressFormatReplace('country_code', $countryCode, $format);
        $format = addressFormatReplace('country_name', $countryName, $format);
        $format = addressFormatReplace('phone', $phone, $format);
        $format = addressFormatReplace('tax_number', $vat == '' ? '' : langapp('tax_number').': '.$vat, $format);
        $format = nl2br($format);
        $format = removeFirstLastBr($format);
        // Remove multiple white spaces
        $format = preg_replace('/\s+/', ' ', $format);
        $format = trim($format);

        return $format;
    }
}
if (! function_exists('formatCompanyAddress')) {
    /**
     * Format company address info
     * @param  object  $data        client object from database
     * @return string
     */
    function formatCompanyAddress($data)
    {
        $format = get_option('company_address_format');
        $l = languageUsingLocale($data['company']->locale);
        $companyName = (get_option('company_legal_name_'.$l) ? get_option('company_legal_name_'.$l) : get_option('company_legal_name'));
        $street = (get_option('company_address_'.$l) ? get_option('company_address_'.$l) : get_option('company_address'));
        $city = (get_option('company_city_'.$l) ? get_option('company_city_'.$l) : get_option('company_city'));
        $state = (get_option('company_state_'.$l) ? get_option('company_state_'.$l) : get_option('company_state'));
        $zipCode = (get_option('company_zip_code_'.$l) ? get_option('company_zip_code_'.$l) : get_option('company_zip_code'));
        $countryName = (get_option('company_country_'.$l) ? get_option('company_country_'.$l) : get_option('company_country'));
        $countryCode = '';
        $country = null;
        $country = Country::where('name', $countryName)->first();
        if ($country) {
            $countryCode = $country->code;
            $countryName = $country->name;
        }

        $phone = 'Phone: '.(get_option('company_phone_'.$l) ? get_option('company_phone_'.$l) : get_option('company_phone'));
        $vat = (get_option('company_vat_'.$l) ? get_option('company_vat_'.$l) : get_option('company_vat'));
        $companyName = '<b class="'.themeLinks('font-semibold uppercase').'">'.$companyName.'</b>';
        $format = addressFormatReplace('company_name', $companyName, $format);
        $format = addressFormatReplace('street', $street, $format);
        $format = addressFormatReplace('city', $city, $format);
        $format = addressFormatReplace('state', $state, $format);
        $format = addressFormatReplace('zip', $zipCode, $format);
        $format = addressFormatReplace('country_code', $countryCode, $format);
        $format = addressFormatReplace('country_name', $countryName, $format);
        $format = addressFormatReplace('phone', $phone, $format);
        $format = addressFormatReplace('tax_number', $vat == '' ? '' : langapp('tax_number').': '.$vat, $format);
        $format = nl2br($format);
        $format = removeFirstLastBr($format);
        // Remove multiple white spaces
        $format = preg_replace('/\s+/', ' ', $format);
        $format = trim($format);

        return $format;
    }
}
/**
 * Replace system address format fields
 * @param  string $mergeCode merge field to check
 * @param  mixed $val       value to replace
 * @param  string $txt       from format
 * @return string
 */
function addressFormatReplace($mergeCode, $val, $txt)
{
    $tmpVal = strip_tags($val);
    if ($tmpVal != '') {
        $result = preg_replace('/({'.$mergeCode.'})/i', $val, $txt);
    } else {
        $re = '/\s{0,}{'.$mergeCode.'}(<br ?\/?>(\n))?/i';
        $result = preg_replace($re, '', $txt);
    }

    return $result;
}

/**
 * This function replace <br /> only nothing exists in the line and first line other then <br />
 *  Replace first <br /> lines to prevent new spaces
 * @param  string $text The text to perform the action
 * @return string
 */
function removeFirstLastBr($text)
{
    $text = preg_replace('/^<br ?\/?>/is', '', $text);
    // Replace last <br /> lines to prevent new spaces while there is new line
    while (preg_match('/<br ?\/?>$/', $text)) {
        $text = preg_replace('/<br ?\/?>$/is', '', $text);
    }

    return $text;
}

function hideString($string)
{
    return preg_replace('/(?!^).(?!$)/', '*', $string);
}

function setEnvironmentValue(array $values)
{
    $envFile = app()->environmentFilePath();
    $str = file_get_contents($envFile);

    if (count($values) > 0) {
        foreach ($values as $envKey => $envValue) {
            $str .= "\n"; // In case the searched variable is in the last line without \n
            $keyPosition = strpos($str, "{$envKey}=");
            $endOfLinePosition = strpos($str, "\n", $keyPosition);
            $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

            // If key does not exist, add it
            if (! $keyPosition || ! $endOfLinePosition || ! $oldLine) {
                $str .= "{$envKey}={$envValue}\n";
            } else {
                $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
            }
        }
    }

    $str = substr($str, 0, -1);
    if (! file_put_contents($envFile, $str)) {
        return false;
    }

    return true;
}

if (! function_exists('_dd')) {
    function _dd($args)
    {
        http_response_code(500);
        dd($args);
    }
}
