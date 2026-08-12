<?php

if (! function_exists('role_label')) {
    function role_label(string $roleName): string
    {
        return match ($roleName) {
            'system-administrator' => 'System Administrator',
            'manager'              => 'Manager',
            'field-technician'     => 'Field Technician',
            'client-user'          => 'Client User',
            default                => ucwords(str_replace('-', ' ', $roleName)),
        };
    }
}
