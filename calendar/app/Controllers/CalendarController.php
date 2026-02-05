<?php
/**
 * Calendar Controller - BASIC
 * Loads events for the month and renders the calendar view.
 */

class CalendarController {

    private $eventModel;

    public function __construct() {
        $this->eventModel = new Event();
    }

    /**
     * Display the calendar for a given month/year.
     */
    public function index($month, $year) {
        $events = $this->eventModel->getByMonth($year, $month);

        $data = [
            'month'      => $month,
            'year'       => $year,
            'monthName'  => date('F', strtotime("$year-$month-01")),
            'firstDay'   => getFirstDayOfMonth($month, $year),
            'daysInMonth'=> getDaysInMonth($month, $year),
            'events'     => $events,
            'currentUser'=> getCurrentUser(),
            'flash'      => getFlashMessage(),
        ];

        // Navigation
        $data['prevMonth'] = $month - 1;
        $data['prevYear']  = $year;
        $data['nextMonth'] = $month + 1;
        $data['nextYear']  = $year;

        if ($data['prevMonth'] < 1)  { $data['prevMonth'] = 12; $data['prevYear']--; }
        if ($data['nextMonth'] > 12) { $data['nextMonth'] = 1;  $data['nextYear']++; }

        // Render
        extract($data);
        $pageTitle = e($monthName) . ' ' . $year . ' | Calendar';
        require VIEWS_PATH . '/calendar/index.php';
    }
}
