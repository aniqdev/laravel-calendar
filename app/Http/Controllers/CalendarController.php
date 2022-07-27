<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Calendar;
use App\Models\Event;


// Google API configuration 
define('GOOGLE_CLIENT_ID', '456913800922-qj0d3htnd0laodc4tnslvvncqa78gvce.apps.googleusercontent.com'); 
define('GOOGLE_CLIENT_SECRET', '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'); 
define('GOOGLE_OAUTH_SCOPE', 'https://www.googleapis.com/auth/calendar'); 
define('REDIRECT_URI', 'http://localhost:8000/google-callback');
 

class CalendarController extends Controller
{
    public function calendar()
    {   
        $events = Event::all();
        return view('calendar', [
            'events' => $events,
        ]);
    }

    public function calendar_update()
    {
        $events = [];
        if (session('google_access_token') && session('calendar_id')) {
            $GoogleCalendarApi = new GoogleCalendarApi();
            $events = $GoogleCalendarApi->GetEventsList(session('calendar_id'));
        }
        if (isset($events['items']) && is_array($events['items'])) {
            foreach ($events['items'] as $key => $event) {
                Event::updateOrCreate([
                    'event_id' => $event['id']
                ], [
                    'title' => $event['summary'] ?? '',
                    'summary' => $event['summary'] ?? '',
                    'description' => $event['description'] ?? '',
                    'location' => $event['location'] ?? '',
                    'start' => date('Y-m-d h:i:s', strtotime($event['start']['dateTime'])),
                    'end' => date('Y-m-d h:i:s', strtotime($event['end']['dateTime'])),
                    'link' => $event['htmlLink'] ?? '',
                    'status' => $event['status'] ?? '',
                ]);
            }
        }
        return redirect('/calendar');
    }

    public function add_event(Request $request)
    {
        $calendar_event = array( 
            'summary' => $request->get('title'), 
            'location' => $request->get('location'), 
            'description' => $request->get('description'),
        ); 
         
        $event_datetime = array( 
            'event_date' => $request->get('date'), 
            'start_time' => $request->get('time_from'), 
            'end_time' => $request->get('time_to'),
        );

        $GoogleCalendarApi = new GoogleCalendarApi();
        $user_timezone = $GoogleCalendarApi->GetUserCalendarTimezone(session('google_access_token')); 
        // Create an event on the primary calendar 
        $data = $GoogleCalendarApi->CreateCalendarEvent('primary', $calendar_event, 0, $event_datetime, $user_timezone);

        dd($data);
    }

    public function google_auth()
    {
        // Google OAuth URL 
        $googleOauthURL = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode(GOOGLE_OAUTH_SCOPE) . '&redirect_uri=' . REDIRECT_URI . '&response_type=code&client_id=' . GOOGLE_CLIENT_ID . '&access_type=online'; 
        return redirect($googleOauthURL);
    }

    public function google_callback()
    {
        if (request('code')) {
            $GoogleCalendarApi = new GoogleCalendarApi(); 
            // dd(request('code'));
            $data = $GoogleCalendarApi->GetAccessToken(request('code'));
            // $data = $GoogleCalendarApi->GetAccessToken(GOOGLE_CLIENT_ID, REDIRECT_URI, GOOGLE_CLIENT_SECRET, $_GET['code']);  
            $access_token = $data['access_token']; 
            session(['google_access_token' => $access_token]);

            $GoogleCalendarApi = new GoogleCalendarApi();
            $data = $GoogleCalendarApi->GetCalendarsList($access_token);
            if (isset($data['items'][0]['id'])) {
                session(['calendar_id' => $data['items'][0]['id']]);
            }
        }
        return redirect('/calendar');
    }

    public function google_logout()
    {
        session()->forget('google_access_token');
        return redirect('/calendar');
    }
}






class GoogleCalendarApi { 
    const OAUTH2_TOKEN_URI = 'https://accounts.google.com/o/oauth2/token'; 
    const CALENDAR_TIMEZONE_URI = 'https://www.googleapis.com/calendar/v3/users/me/settings/timezone'; 
    const CALENDAR_LIST = 'https://www.googleapis.com/calendar/v3/users/me/calendarList'; 
    const CALENDAR_EVENT = 'https://www.googleapis.com/calendar/v3/calendars/'; 
     
    function __construct($params = array()) { 
        if (count($params) > 0){ 
            $this->initialize($params);         
        } 
    } 
     
    function initialize($params = array()) { 
        if (count($params) > 0){ 
            foreach ($params as $key => $val){ 
                if (isset($this->$key)){ 
                    $this->$key = $val; 
                } 
            }         
        } 
    }


    public function GetAccessToken_($client_id, $redirect_uri, $client_secret, $code) { 
        $curlPost = 'client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&client_secret=' . $client_secret . '&code='. $code . '&grant_type=authorization_code'; 
        $ch = curl_init();         
        curl_setopt($ch, CURLOPT_URL, self::OAUTH2_TOKEN_URI);         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
        curl_setopt($ch, CURLOPT_POST, 1);         
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);     
        $data = json_decode(curl_exec($ch), true); 
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
         dd($data);
        if ($http_code != 200) { 
            $error_msg = 'Failed to receieve access token'; 
            if (curl_errno($ch)) { 
                $error_msg = curl_error($ch); 
            } 
            throw new Exception('Error '.$http_code.': '.$error_msg); 
        } 
             
        return $data; 
    } 

    public function GetAccessToken($code) { 
        // $curlPost = 'client_id=' . GOOGLE_CLIENT_ID . '&redirect_uri=' . REDIRECT_URI . '&client_secret=' . GOOGLE_CLIENT_SECRET . '&code='. $code . '&grant_type=authorization_code';

        // $ch = curl_init();         
        // curl_setopt($ch, CURLOPT_URL, self::OAUTH2_TOKEN_URI);         
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
        // curl_setopt($ch, CURLOPT_POST, 1);         
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);     
        // $data = json_decode(curl_exec($ch), true); 
        // $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
        //  dd($data);
        // if ($http_code != 200) { 
        //     $error_msg = 'Failed to receieve access token'; 
        //     if (curl_errno($ch)) { 
        //         $error_msg = curl_error($ch); 
        //     } 
        //     throw new Exception('Error '.$http_code.': '.$error_msg); 
        // } 
             
        $curlPost = [
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => REDIRECT_URI,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];

        $data = self::curlPost(self::OAUTH2_TOKEN_URI, $curlPost);
 
        return $data; 
    } 
 
    public function GetUserCalendarTimezone($access_token) { 
        $data = self::curlGet(self::CALENDAR_TIMEZONE_URI, $access_token);
        return $data['value']; 
    } 
 
    public function GetCalendarsList($access_token) { 
        $url_parameters = []; 
 
        $url_parameters['fields'] = 'items(id,summary,timeZone)';
        $url_parameters['minAccessRole'] = 'owner'; 
 
        $url_calendars = self::CALENDAR_LIST.'?'. http_build_query($url_parameters);
         
        $data = self::curlGet($url_calendars, $access_token);

        return $data; 
    } 

    public function GetEventsList($calendar_id)
    {
        $url = 'https://www.googleapis.com/calendar/v3/calendars/'.$calendar_id.'/events';

        $data = self::curlGet($url, session('google_access_token'));

        return $data; 
    }
 
    public function CreateCalendarEvent($calendar_id, $event_data, $all_day, $event_datetime, $event_timezone) { 
                 
        $curlPost = array(); 
         
        if(!empty($event_data['summary'])){ 
            $curlPost['summary'] = $event_data['summary']; 
        } 
         
        if(!empty($event_data['location'])){ 
            $curlPost['location'] = $event_data['location']; 
        } 
         
        if(!empty($event_data['description'])){ 
            $curlPost['description'] = $event_data['description']; 
        } 
         
        $event_date = !empty($event_datetime['event_date'])?$event_datetime['event_date']:date("Y-m-d"); 
        $start_time = !empty($event_datetime['start_time'])?$event_datetime['start_time']:date("H:i:s"); 
        $end_time = !empty($event_datetime['end_time'])?$event_datetime['end_time']:date("H:i:s"); 
 
        if($all_day == 1){ 
            $curlPost['start'] = array('date' => $event_date); 
            $curlPost['end'] = array('date' => $event_date); 
        }else{ 
            $timezone_offset = $this->getTimezoneOffset($event_timezone); 
            $timezone_offset = !empty($timezone_offset)?$timezone_offset:'07:00'; 
            $dateTime_start = $event_date.'T'.$start_time.$timezone_offset; 
            $dateTime_end = $event_date.'T'.$end_time.$timezone_offset; 
             
            $curlPost['start'] = array('dateTime' => $dateTime_start, 'timeZone' => $event_timezone); 
            $curlPost['end'] = array('dateTime' => $dateTime_end, 'timeZone' => $event_timezone); 
        }

        $url = self::CALENDAR_EVENT . $calendar_id . '/events';
        $data = self::curlPostJson($url, $curlPost, session('google_access_token'));
 
        return $data; 
    } 
     
    private function getTimezoneOffset($timezone = 'America/Los_Angeles'){ 
        $current   = timezone_open($timezone); 
        $utcTime  = new \DateTime('now', new \DateTimeZone('UTC')); 
        $offsetInSecs =  timezone_offset_get($current, $utcTime); 
        $hoursAndSec = gmdate('H:i', abs($offsetInSecs)); 
        return stripos($offsetInSecs, '-') === false ? "+{$hoursAndSec}" : "-{$hoursAndSec}"; 
    } 

    private static function curlGet($url, $access_token = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($access_token) curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $access_token)); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        
        if ($http_code == 401) {
            session()->forget('google_access_token');
            return [];
        }elseif ($http_code != 200) {
            $error_msg = 'Failed to get';
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }
            throw new \Exception('Error '.$http_code.': '.$error_msg);
        }
        curl_close($ch);
        return $data;
    }

    public static function curlPost($url, $post_data, $token = false)
    {
        // dump($post_data);
        $ch = curl_init();         
        curl_setopt($ch, CURLOPT_URL, $url);         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
        curl_setopt($ch, CURLOPT_POST, 1);         
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        if($token) curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '. $token]);     
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        $data = json_decode(curl_exec($ch), true); 
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);        
         // dd($data);
        if ($http_code == 401) {
            session()->forget('google_access_token');
            return [];
        }elseif ($http_code != 200) { 
            $error_msg = 'Failed'; 
            if (curl_errno($ch)) { 
                $error_msg = curl_error($ch); 
            } 
            throw new \Exception('Error '.$http_code.': '.$error_msg); 
        }
        return $data;
    }

    public static function curlPostJson($url, $post_data, $token = false)
    {
        dump($post_data);
        dump($token);
        $ch = curl_init();         
        curl_setopt($ch, CURLOPT_URL, $url);         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
        curl_setopt($ch, CURLOPT_POST, 1);         
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        if($token) curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '. $token, 'Content-Type: application/json']);     
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        $data = json_decode(curl_exec($ch), true); 
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);        
         dd($data);
        if ($http_code == 401) {
            session()->forget('google_access_token');
            return [];
        }elseif ($http_code != 200) { 
            $error_msg = 'Failed'; 
            if (curl_errno($ch)) { 
                $error_msg = curl_error($ch); 
            } 
            throw new \Exception('Error '.$http_code.': '.$error_msg); 
        }
        return $data;
    }
} 
