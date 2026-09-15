<?php
/**
 * Business rules for the turf-installation estimate scheduler
 * (/schedule-turf-installation-estimate). Plain config, no secrets — edit
 * these values directly to change hours or availability.
 */
return [
    // Appointment length, in minutes.
    'slot_minutes' => 60,

    // Business hours per weekday (0 = Sunday ... 6 = Saturday), as
    // ['open H:i', 'close H:i'] in 24-hour time. Omit a day entirely to
    // make it unavailable for booking.
    'hours' => [
        1 => ['9:00', '17:00'], // Monday
        2 => ['9:00', '17:00'], // Tuesday
        3 => ['9:00', '17:00'], // Wednesday
        4 => ['9:00', '17:00'], // Thursday
        5 => ['9:00', '17:00'], // Friday
        6 => ['9:00', '13:00'], // Saturday (shorter day)
    ],

    // Minimum hours of advance notice required before the next open slot.
    'lead_time_hours' => 24,

    // How many days out customers are allowed to book.
    'booking_window_days' => 21,

    // Dates closed entirely (holidays, vacation, etc.) — 'Y-m-d' strings.
    'blackout_dates' => [],

    'timezone' => 'America/Chicago',
];
