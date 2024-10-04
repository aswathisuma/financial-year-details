<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use HolidayAPI\Client as HolidayAPIClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function home()
    {
        $years = $this->tenYears();
       
        foreach ($years as $year) {
            $this->cacheHolidays('ireland', $year);
            $this->cacheHolidays('uk', $year);
        }

        return view('home', compact('years'));
    }

    protected function tenYears()
    {
        $currentYear = Carbon::now()->year;
        $years = [];
        $count = 10;
        while ($count >= 1) {
            $years[] = $currentYear--;
            $count--;
        }
        sort($years); 
        return $years;
    }

    protected function cacheHolidays($country, $year)
    {

        $holidays = Cache::get("holidays", []);
        if (!isset($holidays[$country][$year])) {
            $holidays[$country][$year] = $this->getHolidays2($country, $year);
            Cache::put("holidays", $holidays, now()->addYear());
        }
    }

    protected function getHolidays($country, $year)
    {
        $key = '35d12797-9fb4-4608-8df2-1c5e70614706';
        $code = ['ireland' => 'IE', 'uk' => 'GB'];
        $client = new Client([
            'base_uri' => 'https://holidayapi.com/v1/holidays',
            'verify' => false
        ]);

        try {
            $response = $client->get('holidays', [
                'query' => [
                    'key' => $key,
                    'country' => $code[$country],
                    'year' => $year
                ]
            ]);

            $holidays = json_decode($response->getBody(), true);
            foreach ($holidays['holidays'] as $holiday) {
                $result[$holiday['date']] = $holiday['name'];
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getHolidays2($country, $year)
    {
        $key = '13d2584c50934611bb0a634c6312cbd8';
        $code = ['ireland' => 'IE', 'uk' => 'GB'];
        $client = new Client([
            'base_uri' => 'https://holidays.abstractapi.com/v1',
            'verify' => false
        ]);

        try {
            $response = $client->get('', [
                'query' => [
                    'api_key' => $key,
                    'country' => $code[$country],
                    'year' => $year
                ]
            ]);

            $holidays = json_decode($response->getBody(), true);
            foreach ($holidays as $holiday) {
                $date = Carbon::createFromFormat('d/m/Y', $holiday['date'])->format('Y-m-d');
                $result[$date] = $holiday['name'];
            }
            return  $result;
        } catch (\Exception $e) {
            return [];
        }
    }


    public function financialYear(Request $request)
    {
        $request->validate([
            'country' => 'required',
            'year' => 'required',
        ]);
    
        $country = $request->input('country');
        $year = $request->input('year');
        $holidays = Cache::get('holidays', []);

        $holidayDetails = $holidays[$country][$year] ?? [];
    
       
        if ($country == 'ireland') {
            $financialYearStart = Carbon::createFromFormat('Y-m-d', "{$year}-01-01");
            $financialYearEnd = Carbon::createFromFormat('Y-m-d', "{$year}-12-31");
        } else { 
            $financialYearStart = Carbon::createFromFormat('Y-m-d', "{$year}-04-06");
            $financialYearEnd = $financialYearStart->copy()->addYear()->subDay(); 
        }
    
        $financialYearStartHolidays = [];
        $financialYearEndHolidays = [];
    
       
        while (isset($holidayDetails[$financialYearStart->format('Y-m-d')])) {
            $financialYearStartHolidays[] = $this->formatDate2($financialYearStart)." is ". $holidayDetails[$financialYearStart->format('Y-m-d')];
            $financialYearStart->addDay(); 
        }
    
        
        while (isset($holidayDetails[$financialYearEnd->format('Y-m-d')])) {
            $financialYearEndHolidays[] = $this->formatDate2($financialYearEnd)." is ". $holidayDetails[$financialYearEnd->format('Y-m-d')];
            $financialYearEnd->subDay(); 
        }
        
        $startHolidays = "";
        $yearEndHolidays = "";

        if($financialYearStartHolidays){
            $startHolidays = " (" . implode(", ", $financialYearStartHolidays) . ")";
        }

        if($financialYearEndHolidays){
            $yearEndHolidays =  " (" . implode(", ", $financialYearEndHolidays) . ")";
        }
    
        return response()->json([
            'financial_year_start' => $this->formatDate($financialYearStart) . $startHolidays,
            'financial_year_end' => $this->formatDate($financialYearEnd) . $yearEndHolidays
        ]);
    }

    protected function formatDate(Carbon $date)
    {
        $day = $date->day;
        $suffix = 'th';

        if ($day % 10 == 1 && $day != 11) {
            $suffix = 'st';
        } elseif ($day % 10 == 2 && $day != 12) {
            $suffix = 'nd';
        } elseif ($day % 10 == 3 && $day != 13) {
            $suffix = 'rd';
        }

        return $day . $suffix . ' ' . $date->format('F Y');
    }

    protected function formatDate2(Carbon $date)
    {
        $day = $date->day;
        $suffix = 'th';

        if ($day % 10 == 1 && $day != 11) {
            $suffix = 'st';
        } elseif ($day % 10 == 2 && $day != 12) {
            $suffix = 'nd';
        } elseif ($day % 10 == 3 && $day != 13) {
            $suffix = 'rd';
        }

        return $day . $suffix . ' ' . $date->format('M');
    }
    
}
