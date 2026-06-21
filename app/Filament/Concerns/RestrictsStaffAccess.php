<?php

namespace App\Filament\Concerns;

trait RestrictsStaffAccess
{
    protected static function staffNavigation(): bool
    {
        return false;
    }

    protected static function staffViewOnly(): bool
    {
        return false;
    }

    protected static function staffFullAccess(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (! $user?->isAdmin()) {
            return false;
        }
        if ($user->isFullAdmin()) {
            return parent::shouldRegisterNavigation();
        }

        return static::staffNavigation() || static::staffViewOnly() || static::staffFullAccess();
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (! $user?->isAdmin()) {
            return false;
        }
        if ($user->isFullAdmin()) {
            return true;
        }

        return static::staffNavigation() || static::staffViewOnly() || static::staffFullAccess();
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (! $user?->isAdmin()) {
            return false;
        }
        if ($user->isFullAdmin()) {
            return true;
        }

        return static::staffFullAccess();
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (! $user?->isAdmin()) {
            return false;
        }
        if ($user->isFullAdmin()) {
            return true;
        }

        return static::staffFullAccess();
    }

    public static function canDelete($record): bool
    {
        return static::canEdit($record);
    }
}
