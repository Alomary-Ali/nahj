<?php

namespace App\Filament\Resources\CourseReviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CourseReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')
                    ->label('المقرر الدراسي')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->lineClamp(1),

                TextColumn::make('user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('التقييم')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state) . str_repeat('☆', 5 - $state) . " ({$state}/5)")
                    ->color('warning')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('review_text')
                    ->label('نص المراجعة')
                    ->searchable()
                    ->lineClamp(2)
                    ->placeholder('لا يوجد تعليق نصي'),

                IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('حالة النشر'),

                SelectFilter::make('rating')
                    ->label('عدد النجوم')
                    ->options([
                        5 => '★★★★★ (5 نجوم)',
                        4 => '★★★★☆ (4 نجوم)',
                        3 => '★★★☆☆ (3 نجوم)',
                        2 => '★★☆☆☆ (نجمتان)',
                        1 => '★☆☆☆☆ (نجمة واحدة)',
                    ]),

                SelectFilter::make('course_id')
                    ->label('المقرر')
                    ->relationship('course', 'title'),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
