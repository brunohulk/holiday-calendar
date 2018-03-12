<?php

namespace SampleApplication;

use HolidayAPI\v1;
use Predis\Client;

final class Calendar
{
    private $currentYear;
    private $holidays;

    public function __construct(v1 $holidayApi, int $year, Client $redis, string $country = 'BR')
    {
        $cacheHoliday = $redis->get('holidays' . $country);
        $cacheHoliday = null;
        $this->currentYear = $year;

        if (isset($cacheHoliday)) {
            $this->holidays = json_decode($cacheHoliday, true);
            return;
        }

        $response = $holidayApi->holidays([
            'country' => $country,
            'year' => $year
        ]);

        if ($response['status'] != 200) {
            throw new \ErrorException("It was possible to generate the calendar");
        }

        $this->holidays = $response['holidays'];
        $redis->set('holidays' . $country, json_encode($this->holidays));
    }

    public function draw() :string
    {
        $calendar = "<table>";
        $calendar .= $this->drawHeader();
        $calendar .= $this->buildTheMonths();
        $calendar .= "</table>";

        return $calendar;
    }

    protected function buildTheMonths(): string
    {
        $monthResult = '<tbody>';
        for ($month = 1; $month <=12; $month++) {
            $monthResult .= '<tr>'
                . '<td>' . date("F", mktime(0, 0, 0, $month, 1, $this->currentYear)) . '</td>';
            $monthResult .= $this->daysInTheMonth($month);
            $monthResult .='</tr>';
        }
        $monthResult .= '</tbody>';

        return $monthResult;
    }

    private function daysInTheMonth(int $month): string
    {
        $days = "";
        $total = cal_days_in_month(CAL_GREGORIAN, $month, $this->currentYear);
        for ($day = 1; $day <= $total; $day++) {
            $holiday = $this->isHoliday($month, $day);

            if (!$holiday) {
                $days .= "<td>$day</td>";
                continue;
            }
            $days .= sprintf('<td title="%s" class="holiday">%s</td>', $holiday['name'], $day);
        }

        return $days;
    }

    private function isHoliday(int $month, int $day)
    {
        $month = $month < 10? "0$month" : $month;
        $day = $day < 10? "0$day" : $day;

        $date = sprintf("%d-%s-%s", $this->currentYear, $month, $day);

        if (array_key_exists($date, $this->holidays)) {
            return $this->holidays[$date][0];
        }

        return false;
    }

    private function drawHeader(): string
    {
        $header = '<tr>';
        $header .= "<th>{$this->currentYear}</th>";
        for ($day = 1; $day <= 31; $day++) {
            $header .= "<th class='day'>$day</th>";
        }
        $header .= '</tr>';

        return $header;
    }
}
