<?php

namespace App\Filament\Resources\Certificates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CertificateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الاعتماد والشهادة (Certificate Information)')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('certificate_code')
                                    ->label('كود الشهادة الفريد (Code)')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('grade_percentage')
                                    ->label('الدرجة / نسبة النجاح (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->required(),

                                Select::make('user_id')
                                    ->label('الطالب الحاصل على الشهادة')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('course_id')
                                    ->label('المقرر الأكاديمي')
                                    ->relationship('course', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('instructor_id')
                                    ->label('المدرب المعتمد')
                                    ->relationship('instructor', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                DatePicker::make('issue_date')
                                    ->label('تاريخ الإصدار')
                                    ->required(),
                            ]),

                        TextInput::make('verification_hash')
                            ->label('بصمة التحقق المشفرة (SHA-256 Hash)')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
