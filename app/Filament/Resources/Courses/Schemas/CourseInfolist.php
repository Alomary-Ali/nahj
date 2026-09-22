<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Models\Course;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CourseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('ulid'),
                TextEntry::make('title'),
                TextEntry::make('slug'),
                TextEntry::make('headline')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('thumbnail')
                    ->placeholder('-'),
                TextEntry::make('preview_video_url')
                    ->placeholder('-'),
                TextEntry::make('instructor.name')
                    ->label('Instructor'),
                TextEntry::make('category.name')
                    ->label('Category'),
                TextEntry::make('specialization.title')
                    ->label('Specialization')
                    ->placeholder('-'),
                TextEntry::make('level'),
                TextEntry::make('language'),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('duration_hours')
                    ->numeric(),
                IconEntry::make('is_published')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Course $record): bool => $record->trashed()),
            ]);
    }
}
