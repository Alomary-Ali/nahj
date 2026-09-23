<?php

namespace App\Filament\Resources\Specializations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SpecializationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('الغلاف')
                    ->circular()
                    ->defaultImageUrl('/backblue.gif'),

                TextColumn::make('title')
                    ->label('عنوان التخصص / المسار')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): string => $record->headline ?? ''),

                TextColumn::make('instructor.name')
                    ->label('المدرب المشرف')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('التصنيف')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('courses_count')
                    ->counts('courses')
                    ->label('عدد المقررات')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('level')
                    ->label('المستوى')
                    ->badge()
                    ->colors([
                        'info' => 'beginner',
                        'warning' => 'intermediate',
                        'danger' => 'advanced',
                        'success' => 'all_levels',
                    ]),

                IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name'),

                SelectFilter::make('level')
                    ->label('المستوى')
                    ->options([
                        'beginner' => 'مبتدئ (Beginner)',
                        'intermediate' => 'متوسط (Intermediate)',
                        'advanced' => 'متقدم (Advanced)',
                        'all_levels' => 'كافة المستويات (All Levels)',
                    ]),

                TernaryFilter::make('is_published')
                    ->label('حالة النشر'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
