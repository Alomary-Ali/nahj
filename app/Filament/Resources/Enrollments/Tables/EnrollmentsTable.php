<?php

namespace App\Filament\Resources\Enrollments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('user.email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('course.title')
                    ->label('المقرر المسجل فيه')
                    ->searchable()
                    ->sortable()
                    ->lineClamp(1),

                TextColumn::make('status')
                    ->label('حالة التسجيل')
                    ->badge()
                    ->colors([
                        'info' => 'active',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'warning' => 'expired',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشط ومستمر (Active)',
                        'completed' => 'مكتمل (Completed)',
                        'cancelled' => 'ملغي (Cancelled)',
                        'expired' => 'منتهي (Expired)',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('price_paid')
                    ->label('المبلغ المدفوع')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('enrolled_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->dateTime('Y-m-d')
                    ->placeholder('وصول دائم (Lifetime)')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط (Active)',
                        'completed' => 'مكتمل (Completed)',
                        'cancelled' => 'ملغي (Cancelled)',
                        'expired' => 'منتهي (Expired)',
                    ]),

                SelectFilter::make('course_id')
                    ->label('المقرر')
                    ->relationship('course', 'title'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
