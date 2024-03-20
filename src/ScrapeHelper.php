<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeHelper
{
    /**
     * fetchDocument
     *
     * @param  mixed $url
     * @return Crawler
     */
    public static function fetchDocument(string $url): Crawler
    {
        $client = new Client();

        $response = $client->get($url);

        return new Crawler($response->getBody()->getContents(), $url);
    }
    
    /**
     * extractDateFromString
     *
     * @param  mixed $string
     * @return void
     */
    public static function extractDateFromString(string $string) {
        // Month Name Array
        $monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November",
            "December"
        ];
        
        /*
        Shorthand Month Names array ex - January - Jan |
        February - Feb, etc.
        */
        $shortMonthNames = [];
        
        foreach($monthNames as $monthName) {
            $month = substr($monthName, 0, 3);
            array_push($shortMonthNames, $month);
        }

        // Day name array
        $dayNames = [
            "Monday", "Tuesday", "Wednesday", "Thursday", "Friday",
            "Saturday", "Sunday"
        ];

        /*
        Shorthand Day Names array ex - Monday - Mon |
        Tuesday - Tue, etc.
        */
        $shortDayNames = [];
        
        foreach($dayNames as $dayName) {
            $day = substr($dayName, 0, 3);
            array_push($shortDayNames, $day);
        }

        /*
            $prefixForMonthDay donates the dates which is
            shown in following manner ex - 21st, 22nd, 23rd, 24th
        */
        $prefixForMonthDay = [
            "st", "nd", "rd", "th"
        ];

        $day = "";
        $month = "";
        $year = "";

        /*
        $checkdates store the following types of dates given below:
        20/03/2024 or 20-24-03 or 1 2 1990
        */
        $checkDates = preg_match( '/([0-9]?[0-9])[\.\-\/ ]+([0-1]?[0-9])[\.\-\/ ]+([0-9]{2,4})/', $string);

        if ($checkDates) {
            if ($checkDates[1]) {
                $day = $checkDates[1];
            }

            if ($checkDates[2]) {
                $month = $checkDates[2];
            }

            if ($checkDates[3]) {
                $year = $checkDates[3];
            }
        }

        /*
        $checkdates store the following types of dates given below:
        Sunday 20th March 2024; Sunday, 20 March 2024;
        Sun 20 Mar 2024; Sun-20-March-2024
        */
        $checkDates = preg_match('/(?:(?:' . implode( '|', $dayNames ) . '|' . implode( '|', $shortDayNames ) . ')[ ,\-_\/]*)?([0-9]?[0-9])[ ,\-_\/]*(?:' . implode( '|', $prefixForMonthDay ) . ')?[ ,\-_\/]*(' . implode( '|', $monthNames ) . '|' . implode( '|', $shortMonthNames ) . ')[ ,\-_\/]+([0-9]{4})/i', $string);
        if ($checkDates) {
            if (empty($day) && $checkDates[1]) {
                $day = $checkDates[1];
            }

            if (empty($month) && $checkDates[2]) {
                $month = array_search(strtolower($checkDates[2] ), $shortMonthNames);

                if (!$month) {
                    $month = array_search(strtolower($checkDates[2]), $monthNames);
                }

                $month = $month + 1;
            }

            if (empty($year) && $checkDates[3]) {
                $year = $checkDates[3];
            }
        }

        /*
        $checkdates store the following types of dates given below:
        March 20th 2024; March 1 2024; March-20th-2024
        */
        $checkdates = preg_match('/(' . implode( '|', $monthNames ) . '|' . implode( '|', $shortMonthNames ) . ')[ ,\-_\/]*([0-9]?[0-9])[ ,\-_\/]*(?:' . implode( '|', $prefixForMonthDay ) . ')?[ ,\-_\/]+([0-9]{4})/i', $string);
        if ($checkdates) {
            if (empty($month) && $checkdates[1]) {
                $month = array_search(strtolower($checkdates[1]),  $shortMonthNames);

                if (!$month) {
                    $month = array_search(strtolower($checkdates[1]),  $monthNames);
                }

                $month = $month + 1;
            }

            if (empty($day) && $checkdates[2]) {
                $day = $checkdates[2];
            }

            if (empty($year) && $checkdates[3]) {
                $year = $checkdates[3];
            }
        }

        // Match Month Name
        if (empty($month)) {
            $matchMonthWord = preg_match( '/(' . implode( '|', $monthNames ) . ')/i', $string);
            if ($matchMonthWord && $matchMonthWord[1]) {
                $month = array_search( strtolower($matchMonthWord[1] ), $monthNames);
            }

            // Match short month names
            if (empty($month)) {
                $matchMonthWord = preg_match( '/(' . implode( '|', $shortMonthNames ) . ')/i', $string);
                if ($matchMonthWord && $matchMonthWord[1]) {
                    $month = array_search(strtolower( $matchMonthWord[1]), $shortMonthNames);
                }
            }

            if (!empty($month))
            $month = $month + 1;
        }

        // Match 5th 1st day:
        if (empty($day)) {
            $matchDay = preg_match( '/([0-9]?[0-9])(' . implode( '|', $prefixForMonthDay ) . ')/', $string);
            if ($matchDay && $matchDay[1]) {
                $day = $matchDay[1];
            }
        }

        /*
        Match year if not already matched
        */
        if (empty($year)) {
            $matchYear = preg_match( '/[0-9]{4}/', $string);
            if ($matchYear && $matchYear[0]) {
                $year = $matchYear[0];
            }
        }

        if (!empty($day) && !empty($month) && empty($year)) {
            $matchYear = preg_match( '/[0-9]{2}/', $string);
            if ($matchYear && $matchYear[0]) {
                $year = $matchYear[0];
            }
        }

        /*
        Making single digit day number to start with 0
        ex - 1 will change 01, 9 will change 09
        */ 
        if (strlen($day) == 1) {
            $day = '0' . $day;
        }

        /*
        Making single digit month number to start with 0
        ex - 5 will change 05, 8 will change 08
        */
        if (strlen($month) == 1) {
            $month = '0' . $month;
        }

        /*
        Check year for 20th century or 21st century
        */
        if (strlen($year) == 2 && $year > 20) {
            $year = '19' . $year;
        }
        else if (strlen($year) == 2 && $year < 20 ) {
            $year = '20' . $year;
        }

        // If date not found then return null value
        if (empty($year) || empty($month) || empty($day)) {
            return null;
        } else {
            return $year."-".$month."-".$day;
        }
    }
}
