<?php

namespace App\Filament\Resources\Specializations\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SpecializationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات التخصص والمسار المهني (Specialization Details)')
                    ->description('تحديد عنوان المسار، الشعار التعريفي، والوصف الأكاديمي الشامل.')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان التخصص / المسار (Specialization Title)')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('headline')
                            ->label('العنوان الترويجي الفرعي (Headline)')
                            ->placeholder('مثال: مسار متكامل لاحتراف هندسة البرمجيات والذكاء الاصطناعي')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        RichEditor::make('description')
                            ->label('الوصف الشامل للمسار والمهارات المكتسبة')
                            ->columnSpanFull(),
                    ]),

                Section::make('المدرب والتصنيف والمستوى (Instructor & Metadata)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('instructor_id')
                                    ->label('المدرب المشرف على المسار')
                                    ->relationship('instructor', 'name')
                                    ->default(fn () => auth()->id())
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('category_id')
                                    ->label('التصنيف الأكاديمي')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('level')
                                    ->label('المستوى')
                                    ->options([
                                        'beginner' => 'مبتدئ (Beginner)',
                                        'intermediate' => 'متوسط (Intermediate)',
                                        'advanced' => 'متقدم (Advanced)',
                                        'all_levels' => 'كافة المستويات (All Levels)',
                                    ])
                                    ->default('beginner')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_published')
                                    ->label('نشر المسار في المنصة (Published)')
                                    ->helperText('عند التفعيل، سيظهر المسار لجميع الطلاب في الكتالوج الأكاديمي')
                                    ->default(false),
                            ]),

                        FileUpload::make('thumbnail')
                            ->label('صورة غلاف التخصص (16:9 Banner)')
                            ->image()
                            ->directory('specializations/thumbnails')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
