<?php

namespace App\Models\Utility;

class DateRange
{

    public $date_from;
    public $date_to;

    protected $year;
    protected $month;

    public function __construct(int $year = null, int $month = null)
    {

        $this->year = $year;
        $this->month = sprintf('%02d', $month);

        if($year == null){
            $this->year = date('Y');
        }

        if($month == null){
            // date('Y-m-01') -> first day of the month
            // date('Y-m-t') -> last day of the month
            $this->month = date('m');
            // first day of the month -1
            //$this->date_from = date($this->year.'-m-t', strtotime('-1 month'));
            $this->date_from = date('Y-m-01');
            $this->date_to = date($this->year.'-m-t');
        }else if ($year != null && $month != null){
            // first day of the month -1
            //$this->date_from = date($this->year.'-m-t', strtotime('-1 month', strtotime($this->year.'-'.sprintf('%02d', $this->month).'-01')));
            $this->date_from = date($this->year.'-m-01', strtotime($this->year.'-'.sprintf('%02d', $this->month).'-01'));
            $this->date_to = date($this->year.'-m-t', strtotime($this->year.'-'.sprintf('%02d', $this->month).'-01'));
        }
    }

}
