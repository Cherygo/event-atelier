<?php

namespace App;

enum EventRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Planner / admin',
            self::Editor => 'Editor',
            self::Viewer => 'Viewer',
        };
    }
}
