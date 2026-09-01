<?php

if (! function_exists('viewer_time')) {
    function viewer_time(\Carbon\Carbon $date, string $format = 'd M Y, H:i'): string
    {
        $tz    = auth()->user()?->timezone ?: 'UTC';
        $local = $date->copy()->setTimezone($tz);
        return $local->format($format) . ' ' . $local->format('T');
    }
}

if (! function_exists('site_time')) {
    function site_time(\Carbon\Carbon $date, ?string $timezone = null, string $format = 'd M Y, H:i'): string
    {
        $tz    = $timezone ?: 'UTC';
        $local = $date->copy()->setTimezone($tz);
        return $local->format($format) . ' ' . $local->format('T');
    }
}

if (! function_exists('role_label')) {
    function role_label(string $roleName): string
    {
        return match ($roleName) {
            'system-administrator' => 'System Administrator',
            'manager'              => 'Manager',
            'field-technician'     => 'Field Technician',
            'client-user'          => 'Client',
            default                => ucwords(str_replace('-', ' ', $roleName)),
        };
    }
}
