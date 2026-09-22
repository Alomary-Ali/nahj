<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'الطلاب المسجلون (Enrolled Students)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('الطالب')
                    ->required(),
                TextInput::make('price_paid')
                    ->label('المبلغ المدفوع')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Select::make('status')
                    ->label('حالة التسجيل')
                    ->options([
                        'active' => 'نشط (Active)',
                        'completed' => 'مكتمل (Completed)',
                        'cancelled' => 'ملغي (Cancelled)',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('user.email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),
                TextColumn::make('price_paid')
                    ->label('المبلغ')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'info' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('enrolled_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }
}
