<?php

namespace App\Filament\Resources\Certificates\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CertificatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('certificate_code')
                    ->label('كود الشهادة')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ كود الشهادة')
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('course.title')
                    ->label('المقرر الدراسي')
                    ->searchable()
                    ->sortable()
                    ->lineClamp(1),

                TextColumn::make('instructor.name')
                    ->label('المدرب')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('grade_percentage')
                    ->label('الدرجة')
                    ->suffix('%')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('issue_date')
                    ->label('تاريخ الإصدار')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('course_id')
                    ->label('المقرر')
                    ->relationship('course', 'title'),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('verify')
                    ->label('رابط التحقق')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn ($record): string => route('certificates.verify', $record->certificate_code))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
